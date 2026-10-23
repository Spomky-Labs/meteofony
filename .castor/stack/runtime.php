<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\io;
use function Castor\run;

/*
 * How this checkout runs the application, which is not always Docker. Docker is not installable
 * everywhere, so `castor start` falls back to the Symfony CLI and then to the PHP built-in server, and
 * swaps the PostgreSQL of compose.yaml for a SQLite file it creates itself.
 *
 * What the fallbacks cost: the X509 client-certificate authentication of the ^/admin firewall
 * (config/packages/security.yaml) is handed to PHP by Caddy's `client_auth` (frankenphp/Caddyfile), so it
 * exists only under Docker. Nothing else of the application depends on the stack — the messenger routes
 * everything through `sync://` and MAILER_DSN is already `null://null`.
 */

const RUNTIME_DOCKER = 'docker';
const RUNTIME_SYMFONY = 'symfony';
const RUNTIME_PHP = 'php';

const RUNTIMES = [RUNTIME_DOCKER, RUNTIME_SYMFONY, RUNTIME_PHP];

/** Where the fallbacks keep the database, relative to the repository root. */
const LOCAL_DATABASE = 'var/app.db';

/**
 * Forces the runtime, for `castor start --runtime=` and for METEOFONY_RUNTIME. Set once, it wins over
 * detection for the whole run, which is the only way to exercise a fallback on a machine that does have
 * Docker.
 */
function runtimeOverride(?string $set = null): ?string
{
    static $override = null;

    if ($set !== null) {
        if (! in_array($set, RUNTIMES, true)) {
            io()->error(sprintf('Unknown runtime "%s". Expected one of: %s.', $set, implode(', ', RUNTIMES)));
            exit(1);
        }
        $override = $set;
    }

    return $override;
}

#[AsTask(name: 'runtime', description: 'Show which runtime castor would use, and what it costs.')]
function runtimeStatus(): void
{
    $runtime = runtime();
    io()->title('Runtime');

    io()
        ->definitionList(
            ['Selected' => $runtime],
            ['Forced' => runtimeOverride() ?? getenv('METEOFONY_RUNTIME') ?: 'no (detected)'],
            ['Docker usable' => dockerIsUsable() ? 'yes' : 'no'],
            ['Symfony CLI' => commandExists('symfony') ? 'yes' : 'no'],
            ['Database' => $runtime === RUNTIME_DOCKER ? 'PostgreSQL (compose.yaml)' : 'SQLite (' . LOCAL_DATABASE . ')'],
        );

    if ($runtime === RUNTIME_DOCKER) {
        io()->success('The full stack is available, X509 included.');

        return;
    }

    io()
        ->warning([
            'Running without Docker: the X509 client-certificate authentication of the ^/admin firewall is',
            'served by Caddy and is not available in this mode. Everything else works.',
        ]);
}

/**
 * The way this machine can run the application: Docker when its daemon answers, else the Symfony CLI, else
 * the PHP that runs Castor.
 */
function runtime(): string
{
    static $resolved = null;

    $override = runtimeOverride();
    if ($override !== null) {
        return $override;
    }

    if ($resolved !== null) {
        return $resolved;
    }

    // Already inside a container, which is the CI and `castor` run from the stack itself: there is no
    // Docker socket in here to `compose exec` through, and no server to start either.
    if (file_exists('/.dockerenv') || getenv('CI') !== false) {
        return $resolved = RUNTIME_PHP;
    }

    $forced = getenv('METEOFONY_RUNTIME');
    if (is_string($forced) && $forced !== '') {
        runtimeOverride($forced);

        return $resolved = $forced;
    }

    if (dockerIsUsable()) {
        return $resolved = RUNTIME_DOCKER;
    }

    if (commandExists('symfony')) {
        return $resolved = RUNTIME_SYMFONY;
    }

    return $resolved = RUNTIME_PHP;
}

/**
 * Docker counts as usable only when its daemon answers. An installed client whose daemon is down is the
 * shape this used to fail in: `command -v docker` said yes and every task then died inside
 * `docker compose exec`.
 */
function dockerIsUsable(): bool
{
    static $usable = null;

    if ($usable !== null) {
        return $usable;
    }

    if (! commandExists('docker')) {
        return $usable = false;
    }

    $silent = context()
        ->withQuiet()
        ->withAllowFailure();

    if (! run(['docker', 'compose', 'version'], context: $silent)->isSuccessful()) {
        return $usable = false;
    }

    return $usable = run(['docker', 'info'], context: $silent)->isSuccessful();
}

function commandExists(string $command): bool
{
    return trim((string) shell_exec(sprintf('command -v %s 2>/dev/null', escapeshellarg($command)))) !== '';
}

/**
 * Installs the dependencies when the checkout has none, so that a clone can go straight to `castor start`.
 */
function ensureDependencies(): void
{
    if (is_file('vendor/autoload_runtime.php')) {
        return;
    }

    io()->section('Installing the dependencies');
    install();
}

/**
 * Points DATABASE_URL at a SQLite file, since the PostgreSQL of compose.yaml is not running in the
 * fallbacks. A DATABASE_URL somebody already chose is left alone: this writes nothing it would overwrite.
 */
function ensureLocalDatabaseUrl(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        io()
            ->error([
                'Without Docker the database is a SQLite file, and the pdo_sqlite extension is missing.',
                sprintf('On Debian/Ubuntu: sudo apt install php%d.%d-sqlite3', \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION),
            ]);
        exit(1);
    }

    $contents = is_file('.env.local') ? (string) file_get_contents('.env.local') : '';

    if (preg_match('/^\s*DATABASE_URL=/m', $contents) === 1) {
        io()->note('.env.local already sets DATABASE_URL: leaving it as it is.');

        return;
    }

    // `resolve:` is already on the env var in config/packages/doctrine.yaml, which is what expands
    // %kernel.project_dir% here and keeps the path right whatever the working directory.
    $block = "\n"
        . "# Written by `castor start` when it found no usable Docker: the PostgreSQL of compose.yaml is\n"
        . "# not there to connect to, so the application runs on a SQLite file. Remove this to go back to\n"
        . "# what .env declares.\n"
        . 'DATABASE_URL="sqlite:///%kernel.project_dir%/' . LOCAL_DATABASE . "\"\n";

    file_put_contents('.env.local', $contents . $block);
    io()->success(sprintf('.env.local now points DATABASE_URL at %s.', LOCAL_DATABASE));
}

/**
 * Creates the SQLite database and fills it, once. An existing file is left untouched, so a restart keeps
 * whatever the previous run put in it.
 */
function ensureLocalDatabase(): void
{
    ensureLocalDatabaseUrl();

    if (is_file(LOCAL_DATABASE)) {
        return;
    }

    if (! is_dir('var')) {
        mkdir('var', 0o777, true);
    }

    io()->section('Creating the SQLite database');
    // Not `doctrine:migrations:migrate`: the migrations of this repository are PostgreSQL SQL, and
    // `COMMENT ON COLUMN` (Version20230316211120) and `ALTER ... TYPE` (Version20240324105255) have no
    // SQLite equivalent, so the very first one fails. The mapping of src/Entity describes the same schema
    // and builds it on either platform.
    php(['bin/console', 'doctrine:schema:create']);

    init();
}
