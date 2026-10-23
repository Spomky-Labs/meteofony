<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\io;
use function Castor\run;

/*
 * The quality tools and the test suites, which is what the CI runs (.github/workflows/ci.yml): each one in
 * the phpqa image when Docker is available, and directly when the caller is already inside a container,
 * which is the CI. Nothing here needs the development stack to be up. The CI calls these tasks by name,
 * so `phpstan`, `ecs`, `rector`, `deptrac`, `lint`, `phpunit`, `infect` and `check-licenses` are a
 * contract with .github/workflows/ci.yml.
 */

const ALLOWED_LICENSES = ['Apache-2.0', 'BSD-2-Clause', 'BSD-3-Clause', 'ISC', 'MIT', 'MPL-2.0', 'OSL-3.0'];

/**
 * Packages allowed by name, against the reason they were allowed. A license decided on per package belongs
 * here, named, and not in ALLOWED_LICENSES, which would allow it for every package at once.
 *
 * @var array<string, string>
 */
const LICENSE_EXCEPTIONS = [];

/**
 * Runs a command in the phpqa image, or directly when already inside a container (CI) or without Docker.
 *
 * @param array<string> $command
 * @param array<string> $dockerOptions
 * @param string|null $phpIniDirectory a directory of the repository read after the image's own conf.d, for
 *                                     a task that needs what it turns on
 */
function phpqa(array $command, array $dockerOptions = [], ?string $phpIniDirectory = null): void
{
    $inContainer = file_exists('/.dockerenv') || getenv('CI') !== false;
    $hasDocker = ! $inContainer && trim(shell_exec('command -v docker') ?? '') !== '';

    if (! $hasDocker) {
        // Without a `docker run` to carry them, the `-e KEY=VALUE` of $dockerOptions were dropped on the
        // floor: this is what made `castor phpunit` produce no coverage in the CI until ci.yml set
        // XDEBUG_MODE on the job itself.
        $env = [];
        foreach ($dockerOptions as $i => $opt) {
            if ($opt === '-e' && isset($dockerOptions[$i + 1])) {
                [$key, $value] = explode('=', $dockerOptions[$i + 1], 2);
                $env[$key] = $value;
            }
        }
        // A leading colon keeps the directory PHP was compiled to scan and adds ours after it, so the
        // configuration of the image still applies and this only adds to it.
        if ($phpIniDirectory !== null) {
            $env['PHP_INI_SCAN_DIR'] = ':' . getcwd() . '/' . $phpIniDirectory;
        }
        run($command, context: context()->withEnvironment($env));

        return;
    }

    if ($phpIniDirectory !== null) {
        $dockerOptions = [
            ...$dockerOptions,
            '-e', 'PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d:/project/' . $phpIniDirectory,
        ];
    }

    $defaultDockerOptions = [
        '--rm',
        '--init',
        // A terminal only when there is one to attach: `docker run -it` aborts on "the input device is
        // not a TTY" wherever the output is a pipe, which is every hook, script and agent that calls a
        // task of this file.
        stream_isatty(\STDOUT) ? '-it' : '-i',
        '--user', sprintf('%s:%s', getmyuid(), getmygid()),
        '--pull', 'always',
        '-v', getcwd() . ':/project',
        '-v', getcwd() . '/tmp-phpqa:/tmp',
        '-w', '/project',
        '-e', 'XDEBUG_MODE=off',
    ];

    // The tag the CI jobs run their container on (.github/workflows/ci.yml), so the tools and the tests
    // run locally on the same PHP they will run on there. PHPQA_PHP_VERSION overrides it to try another.
    $phpVersion = getenv('PHPQA_PHP_VERSION') ?: '8.4';

    run([
        'docker', 'run',
        ...$defaultDockerOptions,
        ...$dockerOptions,
        'ghcr.io/spomky-labs/phpqa:' . $phpVersion,
        ...$command,
    ]);
}

#[AsTask(description: 'Run PHPUnit tests with coverage')]
function phpunit(): void
{
    phpqa(
        [
            'composer', 'exec', '--',
            'phpunit',
            '--coverage-xml', '.ci-tools/coverage',
            '--log-junit=.ci-tools/coverage/junit.xml',
            '--configuration', '.ci-tools/phpunit.xml.dist',
        ],
        ['-e', 'XDEBUG_MODE=coverage']
    );
}

#[AsTask(description: 'Run Easy Coding Standard (use --fix to automatically fix issues)')]
function ecs(bool $fix = false): void
{
    $command = ['composer', 'exec', '--', 'ecs', 'check', '--config', '.ci-tools/ecs.php'];
    if ($fix) {
        $command[] = '--fix';
    }
    phpqa($command);
}

#[AsTask(description: 'Run Rector (use --fix to apply automatic refactorings)')]
function rector(bool $fix = false): void
{
    $command = ['composer', 'exec', '--', 'rector', 'process', '--config', '.ci-tools/rector.php'];
    if (! $fix) {
        $command[] = '--dry-run';
    }
    phpqa($command);
}

#[AsTask(description: 'Run PHPStan (use --baseline to generate baseline file)')]
function phpstan(bool $baseline = false): void
{
    if ($baseline) {
        phpqa([
            'composer', 'exec', '--',
            'phpstan', 'analyse',
            '--configuration=.ci-tools/phpstan.neon',
            '--generate-baseline=.ci-tools/phpstan-baseline.neon',
        ]);
    } else {
        phpqa(['phpstan', 'analyse', '--error-format=github', '--configuration=.ci-tools/phpstan.neon']);
    }
}

#[AsTask(description: 'Run Deptrac')]
function deptrac(): void
{
    phpqa([
        'composer', 'exec', '--',
        'deptrac',
        '--config-file', '.ci-tools/deptrac.yaml',
        '--report-uncovered',
        '--report-skipped',
        '--fail-on-uncovered',
    ]);
}

#[AsTask(description: 'Run PHP parallel linter')]
function lint(): void
{
    // castor.php and .castor/ are linted too: they are PHP of this repository, and a syntax error in them
    // takes down every task at once.
    phpqa(['composer', 'exec', '--', 'parallel-lint', 'src', 'tests', 'castor.php', '.castor']);
}

#[AsTask(description: 'Run Infection for mutation testing')]
function infect(int $minMsi = 0, int $minCoveredMsi = 0): void
{
    phpqa([
        'composer', 'exec', '--',
        'infection',
        '--coverage=coverage',
        sprintf('--min-msi=%d', $minMsi),
        sprintf('--min-covered-msi=%d', $minCoveredMsi),
        '--threads=max',
        '--logger-github',
        '-s',
        '--filter=src/',
        '--configuration=.ci-tools/infection.json.dist',
    ], ['-e', 'XDEBUG_MODE=coverage']);
}

/**
 * The licenses of the installed dependencies, which the CI reads on every pull request and every push.
 *
 * A package declares its licenses as a list, and Composer reads that list as a choice: the package is
 * allowed as soon as one of them is. Asking instead that every one of them be allowed rejects
 * "BSD-3-Clause or GPL-2.0-only or GPL-3.0-only", which is a package anybody may take under BSD-3-Clause.
 */
#[AsTask(description: 'Check the licenses of the installed dependencies.')]
function checkLicenses(): void
{
    io()->title('Checking licenses');

    // The context is immutable: a withEnvironment() whose result is dropped changes nothing, which is why
    // this used to print the whole JSON of every package before its verdict.
    $context = context()
        ->withEnvironment([
            'XDEBUG_MODE' => 'off',
        ])
        ->withQuiet();
    $result = run(['composer', 'licenses', '-f', 'json'], context: $context);
    if (! $result->isSuccessful()) {
        io()->error('Cannot determine licenses');
        exit(1);
    }

    $licenses = json_decode((string) $result->getOutput(), true);
    $dependencies = $licenses['dependencies'] ?? [];

    // Composer reports an empty list when it has nothing installed to report on, and a check that reads
    // that list as "nothing disallowed" passes without having looked at anything.
    if ($dependencies === []) {
        io()->error('Composer reported no dependency, so nothing was checked: install the dependencies first ("castor install").');
        exit(1);
    }

    $disallowed = [];
    foreach ($dependencies as $name => $info) {
        if (isset(LICENSE_EXCEPTIONS[$name])) {
            continue;
        }
        if (array_intersect($info['license'], ALLOWED_LICENSES) !== []) {
            continue;
        }
        $disallowed[$name] = $info['license'];
    }

    if ($disallowed !== []) {
        io()
            ->table(
                ['Package', 'Declares'],
                array_map(
                    static fn ($name, $declared) => [$name, implode(' or ', $declared)],
                    array_keys($disallowed),
                    $disallowed
                )
            );
        io()
            ->error(sprintf(
                '%d package(s) declare no allowed license. Add the license to ALLOWED_LICENSES if it is allowed everywhere, or the package to LICENSE_EXCEPTIONS with the reason.',
                count($disallowed)
            ));
        exit(1);
    }

    io()
        ->success(sprintf(
            '%d dependencies, all under an allowed license (%d named exception(s)).',
            count($dependencies),
            count(LICENSE_EXCEPTIONS)
        ));
}

#[AsTask(description: 'Fix code style and apply Rector rules, then run static analysis.')]
function prepare_pr(): void
{
    io()->title('Preparing code for pull request…');

    ecs(fix: true);
    rector(fix: true);

    io()
        ->section('Running static analysis…');
    phpstan();
    deptrac();
    lint();

    io()
        ->success('Code is ready. You may now commit and push your changes.');
}
