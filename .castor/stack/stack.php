<?php

declare(strict_types=1);

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\io;
use function Castor\notify;
use function Castor\run;
use Symfony\Component\Process\Process;

/*
 * The development stack and what runs inside it: the containers themselves, and the PHP, the console and
 * the consumer that are played in the `php` service. Every task of this file talks to a stack that is up,
 * or puts it up; what fills it is provision.php, and what judges the code is qa.php.
 *
 * Docker is not installable everywhere, so each of start, stop, destroy and php dispatches on runtime()
 * (runtime.php) and has a Symfony-CLI and a plain-PHP form.
 */

/**
 * Image tag for the dev stack. compose.yaml names the image `meteofony:${TAG:-latest}`, so tagging the dev
 * build keeps it from overwriting the `prod` one that `castor build --prod` produces.
 */
const DEV_ENV = [
    'TAG' => 'dev',
];

/** Where the two Docker-less servers listen. */
const LOCAL_SERVER_PORT = 8000;

#[AsTask(description: 'Restart the containers.')]
function restart(?string $runtime = null): void
{
    stop($runtime);
    start(runtime: $runtime);
}

#[AsTask(description: 'Clean the infrastructure (remove container, volume, networks).')]
function destroy(bool $force = false): void
{
    $docker = runtime() === RUNTIME_DOCKER;

    if (! $force) {
        io()->warning($docker
            ? 'This will permanently remove all containers, volumes, networks... created for this project.'
            : sprintf('This will permanently remove %s, which is the whole database in this mode.', LOCAL_DATABASE));
        io()
            ->comment('You can use the --force option to avoid this confirmation.');

        if (! io()->confirm('Are you sure?', false)) {
            io()->comment('Aborted.');

            return;
        }
    }

    if (! $docker) {
        if (is_file(LOCAL_DATABASE)) {
            unlink(LOCAL_DATABASE);
        }
        io()->success(sprintf('%s is gone; the next `castor start` will build and seed it again.', LOCAL_DATABASE));

        return;
    }

    // The v2 subcommand: `docker-compose`, the v1 Python binary this used to call, is not installed any
    // more. `-v` already removes the named volumes, so the `--volumes` that followed it was a duplicate.
    run(
        ['docker', 'compose', 'down', '-v', '--remove-orphans', '--rmi', 'local'],
        context: context()->withEnvironment(DEV_ENV)
    );
    notify('The infrastructure has been destroyed.');
}

#[AsTask(description: 'Stops and removes the containers.')]
function stop(?string $runtime = null): void
{
    if ($runtime !== null) {
        runtimeOverride($runtime);
    }

    match (runtime()) {
        RUNTIME_DOCKER => run(['docker', 'compose', 'down'], context: context()->withEnvironment(DEV_ENV)),
        RUNTIME_SYMFONY => run(['symfony', 'server:stop']),
        // `php -S` is the foreground process of `castor start` in this mode, so there is nothing running
        // in the background to stop: whoever started it stops it with Ctrl+C.
        RUNTIME_PHP => io()->note('Nothing to stop: in plain-PHP mode the server runs in the foreground.'),
    };
}

#[AsTask(description: 'Starts the application (--runtime=docker|symfony|php to force one).')]
function start(bool $pull = false, ?string $runtime = null): void
{
    if ($runtime !== null) {
        runtimeOverride($runtime);
    }

    match (runtime()) {
        RUNTIME_DOCKER => startDocker($pull),
        RUNTIME_SYMFONY => startSymfony(),
        RUNTIME_PHP => startPhp(),
    };
}

function startDocker(bool $pull = false): void
{
    if ($pull) {
        io()->note('Pulling latest images...');
        run(['docker', 'compose', 'pull'], context: context()->withEnvironment(DEV_ENV));
    }

    run(['docker', 'compose', 'up', '-d'], context: context()->withEnvironment(DEV_ENV));
    frontend(true);
}

/**
 * The Symfony CLI serves the application itself, so this starts it detached and then holds the terminal on
 * the Tailwind watcher, which is the shape of the Docker form too.
 */
function startSymfony(): void
{
    io()->title('Starting without Docker, on the Symfony CLI');
    announceFallback();

    ensureDependencies();
    ensureLocalDatabase();

    // Idempotent: a server already listening on this directory would make server:start fail.
    run(['symfony', 'server:stop'], context: context()->withQuiet()->withAllowFailure());
    run(['symfony', 'server:start', '--daemon', '--port=' . LOCAL_SERVER_PORT]);

    io()
        ->success(sprintf(
            'Listening on port %d. `symfony server:status` prints the exact URL: it is https:// once '
            . '`symfony server:ca:install` has been run, and http:// before that.',
            LOCAL_SERVER_PORT
        ));

    frontend(true);
}

/**
 * The last resort: the server PHP ships with. It holds the terminal, so the frontend is compiled once
 * before it rather than watched after it.
 */
function startPhp(): void
{
    io()->title('Starting without Docker, on the PHP built-in server');
    announceFallback();

    ensureDependencies();
    ensureLocalDatabase();
    frontend();

    io()
        ->success(sprintf('Listening on http://127.0.0.1:%d — Ctrl+C to stop.', LOCAL_SERVER_PORT));
    io()
        ->note('Run `castor frontend --watch` in another terminal to rebuild Tailwind as you edit.');

    // The built-in server answers 404 for anything that is not a file on disk, so the routes of the
    // application only exist through this router script.
    run([
        'php',
        '-S', sprintf('127.0.0.1:%d', LOCAL_SERVER_PORT),
        '-t', 'public',
        'scripts/server-router.php',
    ]);
}

function announceFallback(): void
{
    io()
        ->warning([
            'No usable Docker here, so the database is a SQLite file and the X509 client-certificate',
            'authentication of the ^/admin firewall is unavailable: Caddy is what hands the certificate',
            'to PHP. `castor runtime` prints the details.',
        ]);
}

#[AsTask(description: 'Build the images (--prod for production, --no-cache to rebuild, --pull for latest base).')]
function build(bool $prod = false, bool $noCache = false, bool $pull = false): void
{
    // Building images is the one thing the fallbacks cannot stand in for, and it is also what the X509
    // setup is built for, so say so rather than fail inside `docker compose`.
    if (! dockerIsUsable()) {
        io()->error('Building the images needs a usable Docker: `castor runtime` says what is available here.');
        exit(1);
    }

    $command = ['docker', 'compose'];

    // compose.override.yaml is loaded on its own and targets frankenphp_dev, so naming the two files
    // explicitly is what leaves it out and lets the build reach the frankenphp_prod target.
    if ($prod) {
        $command[] = '-f';
        $command[] = 'compose.yaml';
        $command[] = '-f';
        $command[] = 'compose.prod.yaml';
    }

    $command[] = 'build';

    if ($noCache) {
        $command[] = '--no-cache';
    }

    if ($pull) {
        $command[] = '--pull';
    }

    io()->title($prod ? 'Building PRODUCTION image' : 'Building DEVELOPMENT image');
    io()
        ->note(sprintf('Target: %s', $prod ? 'frankenphp_prod' : 'frankenphp_dev'));

    run($command, context: context()->withEnvironment($prod ? [
        'TAG' => 'prod',
    ] : DEV_ENV));

    if ($prod) {
        io()->success('Production build complete.');
        io()
            ->section('To test the production image:');
        io()
            ->listing(['docker compose -f compose.yaml -f compose.prod.yaml up -d']);
    }
}

#[AsTask(description: 'Runs a Consumer from the Docker Container with auto-restart on cache rebuild.')]
function consume(): void
{
    // Wrap `messenger:consume` in a shell loop with `--time-limit`. In dev, FrankenPHP `watch` rebuilds
    // the DI cache on file changes, leaving the running worker holding stale
    // `Container<hash>/get_*HandlerDescriptor_*.php` paths whose files no longer exist on disk
    // (ErrorException "Failed to open stream"). Short time-limit + outer loop self-heals within 5 min.
    // `|| sleep 1` avoids a tight respawn loop if the consumer crashes early.
    php([
        'sh', '-c',
        'trap "exit 0" INT TERM; while true; do bin/console messenger:consume --all --time-limit=300 --memory-limit=256M -vv || sleep 1; done',
    ]);
}

#[AsTask(description: 'Runs a Symfony Console command.', ignoreValidationErrors: true)]
function console(#[AsRawTokens] array $args = []): void
{
    php(['bin/console', ...$args]);
}

#[AsTask(description: 'Runs a PHP command, in the container when there is one.', ignoreValidationErrors: true)]
function php(#[AsRawTokens] array $args = []): void
{
    phpProcess($args);
}

/**
 * Runs a PHP command and returns the process, for the callers that need its output. Under Docker that is
 * an exec into the `php` service; in the fallbacks the command runs here, on the PHP that runs Castor.
 *
 * @param array<string> $args
 */
function phpProcess(array $args, bool $quiet = false): Process
{
    $context = context()
        ->withEnvironment(DEV_ENV);
    if ($quiet) {
        $context = $context->withQuiet();
    }

    if (runtime() !== RUNTIME_DOCKER) {
        return run($args, context: $context);
    }

    return run(['docker', 'compose', 'exec', '-T', 'php', ...$args], context: $context);
}
