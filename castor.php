<?php

declare(strict_types=1);

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;
use Castor\Context;
use function Castor\context;
use function Castor\io;
use function Castor\notify;
use function Castor\run;

#[AsTask(description: 'Run tests')]
function test(
    bool $coverageHtml = false,
    bool $coverageText = false,
    bool $gitlab = false,
    null|string $coverageCobertura = null,
    null|string $logJunit = null,
    null|string $group = null
): void {
    io()->title('Running tests');
    if (! $gitlab) {
        run(
            [
                'docker', 'exec', '-it', 'chp-database-1', 'mariadb', '-p!ChangeMe!', '-e', 'CREATE DATABASE  IF NOT EXISTS app_test; GRANT ALL ON app_test.* TO \'app\'@\'%\'; flush privileges;']
        );
        console(['doctrine:migrations:migrate', '--env=test', '--no-interaction']);
    }
    $command = ['vendor/bin/phpunit', '--color'];
    if ($coverageText) {
        $command = ['vendor/bin/phpunit'];
    }
    $xdebugMode = 'Off';
    if ($coverageHtml) {
        $command[] = '--coverage-html=build/coverage';
        $xdebugMode = 'coverage';
    }
    if ($coverageText) {
        $command[] = '--coverage-text';
        $xdebugMode = 'coverage';
    }
    if ($coverageCobertura) {
        $command[] = sprintf('--coverage-cobertura=%s', $coverageCobertura);
        $xdebugMode = 'coverage';
    }
    if ($logJunit) {
        $command[] = sprintf('--log-junit=%s', $logJunit);
        $xdebugMode = 'coverage';
    }
    if ($group !== null) {
        $command[] = sprintf('--group=%s', $group);
    }
    if ($gitlab) {
        $command[] = '--stop-on-defect';
        run($command, context: context()->withEnvironment([
            'XDEBUG_MODE' => $xdebugMode,
        ]));
        return;
    }
    php($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => $xdebugMode,
    ]));
}

#[AsTask(description: 'Coding standards check')]
function cs(bool $fix = false, bool $clearCache = false, bool $gitlab = false): void
{
    io()->title('Running coding standards check');
    $command = ['vendor/bin/ecs', 'check'];
    if ($fix) {
        $command[] = '--fix';
    }
    if ($clearCache) {
        $command[] = '--clear-cache';
    }
    if ($gitlab) {
        run($command, context: context()->withEnvironment([
            'XDEBUG_MODE' => 'off',
        ]));
        return;
    }
    php($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
}

#[AsTask(description: 'Running PHPStan')]
function stan(bool $baseline = false, bool $gitlab = false): void
{
    io()->title('Running PHPStan');
    $options = ['analyse'];
    if ($baseline) {
        $options[] = '--generate-baseline';
    }
    $command = ['vendor/bin/phpstan', ...$options];
    if ($gitlab) {
        run($command, context: context()->withEnvironment([
            'XDEBUG_MODE' => 'off',
        ]));
        return;
    }
    php($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
}

#[AsTask(description: 'Validate Composer configuration')]
function validate(): void
{
    io()->title('Validating Composer configuration');
    $command = ['composer', 'validate', '--strict'];
    run($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));

    $command = ['composer', 'dump-autoload', '--optimize', '--strict-psr'];
    run($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
}

#[AsTask(description: 'Composer audit')]
function audit(): void
{
    io()->title('Running composer audit');
    $command = ['composer', 'audit'];

    run($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
}

#[AsTask(description: 'Run Rector')]
function rector(bool $fix = false, bool $clearCache = false, bool $gitlab = false): void
{
    io()->title('Running Rector');
    $command = ['vendor/bin/rector', 'process', '--ansi'];
    if (! $fix) {
        $command[] = '--dry-run';
    }
    if ($clearCache) {
        $command[] = '--clear-cache';
    }
    if ($gitlab) {
        run($command, context: context()->withEnvironment([
            'XDEBUG_MODE' => 'off',
        ]));
        return;
    }
    php($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
}

#[AsTask(description: 'Run Deptrac')]
function deptrac(bool $show = false, bool $gitlab = false): void
{
    io()->title('Running Deptrac');
    $command = ['vendor/bin/deptrac', 'analyse', '--fail-on-uncovered', '--no-cache'];
    if ($show) {
        $command[] = '--report-uncovered';
    }
    if ($gitlab) {
        run($command, context: context()->withEnvironment([
            'XDEBUG_MODE' => 'off',
        ]));
        return;
    }
    php($command, context: context()->withEnvironment([
        'XDEBUG_MODE' => 'off',
    ]));
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
function down(): void
{
    run(['docker', 'compose', 'down']);
}

#[AsTask(description: 'Stops the containers.')]
function stop(): void
{
    run(['docker', 'compose', 'stop']);
}

#[AsTask(description: 'Wakes up the containers.')]
function up(string $xdebugMode = 'develop'): void
{
    run(['docker', 'compose', 'up', '-d', '--wait'], context: context()->withEnvironment([
        'XDEBUG_MODE' => $xdebugMode,
    ]));
}

#[AsTask(description: 'Starts the containers.')]
function start(string $xdebugMode = 'develop', bool $fixtures = false): void
{
    up($xdebugMode);
    console(['doctrine:migrations:migrate', '--no-interaction']);
    if ($fixtures) {
        console(['doctrine:fixtures:load', '--no-interaction']);
    }
    frontend();
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
        'assets:install' => [],
        'importmap:install' => [],
        'tailwind:build' => $watch ? ['--watch'] : [],
        'sass:build' => [],
    ];
    if ($watch === false) {
        $commandsToRun['asset-map:compile'] = [];
    }

    foreach ($commandsToRun as $command => $arguments) {
        if (str_contains($consoleOutput->getOutput(), $command)) {
            php(['bin/console', $command, ...$arguments]);
        }
    }
    if (file_exists('yarn.lock')) {
        run(['yarn', 'install']);
        run(['yarn', $watch ? 'watch' : 'build']);
    }
}

#[AsTask(description: 'Update the dependencies and other features.')]
function update(bool $fixtures = false): void
{
    run(['composer', 'update']);
    $consoleOutput = run(['bin/console'], context: context()->withQuiet());
    $commandsToRun = [
        'doctrine:migrations:migrate' => [],
        'doctrine:schema:validate' => [],
        'geoip2:update' => [],
        'app:browscap:update' => [],
        'importmap:update' => [],
    ];
    if ($fixtures) {
        $commandsToRun['doctrine:fixtures:load'] = [];
    }

    foreach ($commandsToRun as $command => $arguments) {
        if (str_contains($consoleOutput->getOutput(), $command)) {
            php(['bin/console', $command, ...$arguments]);
        }
    }
}

#[AsTask(description: 'Runs a Consumer from the Docker Container.')]
function consume(): void
{
    php(['bin/console', 'messenger:consume', '--all']);
}

#[AsTask(description: 'Runs a Symfony Console command from the Docker Container.', ignoreValidationErrors: true)]
function console(#[AsRawTokens] array $args = []): void
{
    php(['bin/console', ...$args]);
}

#[AsTask(description: 'Runs a PHP command from the Docker Container.', ignoreValidationErrors: true)]
function php(#[AsRawTokens] array $args = [], ?Context $context = null): void
{
    run(['docker', 'compose', 'exec', '-T', 'php', ...$args], context: $context);
}

#[AsTask(description: 'Initialize the database and fixtures.')]
function init(): void
{
    console(['d:d:c', '--if-not-exists']);
    console(['d:m:m', '-n']);
    console(['app:init:users']);
    console(['app:init:regions']);
    console(['app:init:departments']);
    console(['app:init:cities']);
}
