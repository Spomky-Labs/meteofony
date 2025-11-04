<?php

declare(strict_types=1);

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\guard_min_version;
use function Castor\io;
use function Castor\notify;
use function Castor\run;

guard_min_version('v0.23.0');

/**
 * @param array<string> $allowedLicenses
 */
#[AsTask(description: 'Check licenses.')]
function checkLicenses(
    array $allowedLicenses = ['Apache-2.0', 'BSD-2-Clause', 'BSD-3-Clause', 'ISC', 'MIT', 'MPL-2.0', 'OSL-3.0']
): void {
    io()->title('Checking licenses');
    $allowedExceptions = [];
    $command = ['composer', 'licenses', '-f', 'json'];
    $context = context();
    $context->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]);
    $context->withQuiet();
    $result = run($command, context: $context);
    if (! $result->isSuccessful()) {
        io()->error('Cannot determine licenses');
        exit(1);
    }
    $licenses = json_decode((string) $result->getOutput(), true);
    $disallowed = array_filter(
        $licenses['dependencies'],
        static fn (array $info, $name) => ! in_array($name, $allowedExceptions, true)
            && count(array_diff($info['license'], $allowedLicenses)) === 1,
        \ARRAY_FILTER_USE_BOTH
    );
    $allowed = array_filter(
        $licenses['dependencies'],
        static fn (array $info, $name) => in_array($name, $allowedExceptions, true)
            || count(array_diff($info['license'], $allowedLicenses)) === 0,
        \ARRAY_FILTER_USE_BOTH
    );
    if (count($disallowed) > 0) {
        io()
            ->table(
                ['Package', 'License'],
                array_map(
                    static fn ($name, $info) => [$name, implode(', ', $info['license'])],
                    array_keys($disallowed),
                    $disallowed
                )
            );
        io()
            ->error('Disallowed licenses found');
        exit(1);
    }
    io()
        ->table(
            ['Package', 'License'],
            array_map(
                static fn ($name, $info) => [$name, implode(', ', $info['license'])],
                array_keys($allowed),
                $allowed
            )
        );
    io()
        ->success('All licenses are allowed');
}

#[AsTask(description: 'Restart the containers.')]
function restart(): void
{
    stop();
    start();
}

#[AsTask(description: 'Clean the infrastructure (remove container, volume, networks).')]
function destroy(bool $force = false): void
{
    if (! $force) {
        io()->warning('This will permanently remove all containers, volumes, networks... created for this project.');
        io()
            ->comment('You can use the --force option to avoid this confirmation.');

        if (! io()->confirm('Are you sure?', false)) {
            io()->comment('Aborted.');

            return;
        }
    }

    run('docker-compose down -v --remove-orphans --volumes --rmi=local');
    notify('The infrastructure has been destroyed.');
}

#[AsTask(description: 'Stops and removes the containers.')]
function stop(): void
{
    run(['docker', 'compose', 'down']);
}

#[AsTask(description: 'Starts the containers.')]
function start(): void
{
    run(['docker', 'compose', 'up', '-d']);
    frontend(true);
}

#[AsTask(description: 'Build the images.')]
function build(): void
{
    run(['docker', 'compose', 'build', '--no-cache', '--pull']);
}

#[AsTask(description: 'Compile the frontend.')]
function frontend(bool $watch = false): void
{
    $consoleOutput = run(['bin/console'], context: context()->withQuiet());
    $commandsToRun = [
        'app:browscap:sync' => [],
        'app:browscap:update' => [],
        'assets:install' => [],
        'importmap:install' => [],
    ];
    if ($watch) {
        $commandsToRun['tailwind:build'] = ['--watch'];
    } else {
        $commandsToRun['tailwind:build'] = [];
    }

    foreach ($commandsToRun as $command => $arguments) {
        if (str_contains((string) $consoleOutput->getOutput(), $command)) {
            php(['bin/console', $command, ...$arguments]);
        }
    }
}

#[AsTask(description: 'Install the dependencies.')]
function install(): void
{
    php(['composer', 'install']);
}

#[AsTask(description: 'Update the dependencies and other features.')]
function update(): void
{
    php(['composer', 'update']);
    $consoleOutput = php(['bin/console']);
    $commandsToRun = [
        'doctrine:migrations:migrate' => [],
        'doctrine:schema:validate' => [],
        'doctrine:fixtures:load' => [],
        'geoip2:update' => [],
        'app:browscap:update' => [],
        'importmap:update' => [],
    ];

    foreach ($commandsToRun as $command => $arguments) {
        if (str_contains((string) $consoleOutput->getOutput(), $command)) {
            php(['bin/console', $command, ...$arguments]);
        }
    }
}

#[AsTask(description: 'Runs a Consumer from the Docket Container.')]
function consume(): void
{
    php(['bin/console', 'messenger:consume', '--all']);
}

#[AsTask(description: 'Runs a Symfony Console command from the Docket Container.', ignoreValidationErrors: true)]
function console(#[AsRawTokens] array $args = []): void
{
    php(['bin/console', ...$args]);
}

#[AsTask(description: 'Runs a PHP command from the Docket Container.', ignoreValidationErrors: true)]
function php(#[AsRawTokens] array $args = []): void
{
    $inContainer = file_exists('/.dockerenv');
    $hasDocker = trim(shell_exec('command -v docker') ?? '') !== '';

    if (! $hasDocker || $inContainer) {
        run($args);
        return;
    }

    run(['docker', 'compose', 'exec', '-T', 'php', ...$args]);
}

function phpqa(array $command, array $dockerOptions = []): void
{
    $inContainer = file_exists('/.dockerenv');
    $hasDocker = trim(shell_exec('command -v docker') ?? '') !== '';

    if (! $hasDocker || $inContainer) {
        run($command);
        return;
    }

    $defaultDockerOptions = [
        '--rm',
        '--init',
        '-it',
        '--user', sprintf('%s:%s', getmyuid(), getmygid()),
        '--pull', 'always',
        '-v', getcwd() . ':/project',
        '-v', getcwd() . '/tmp-phpqa:/tmp',
        '-w', '/project',
        '-e', 'XDEBUG_MODE=off',
    ];

    run([
        'docker', 'run',
        ...$defaultDockerOptions,
        ...$dockerOptions,
        'ghcr.io/spomky-labs/phpqa:8.4',
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
        ['-e', 'XDEBUG_MODE=coverage'] // Docker options supplémentaires
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
    phpqa(['composer', 'exec', '--', 'parallel-lint', 'src', 'tests']);
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

#[AsTask(description: 'Initialize the project.')]
function init(): void
{
    io()->title('Initializing the project…');
    console(['app:sites:create-from-smart']);
    console(['app:plants:create-from-smart']);
    console(['app:rooms:create-from-smart']);
    console(['app:attributes:import']);
    console(['app:penetrations:create-from-smart']);

    console(['app:maintenance-sheet:import']);
    console(['app:maintenance-step:verify-links']);

    console(['app:maintenance-program:import']);
    //console(['app:surfaces:create-from-smart']);
}
