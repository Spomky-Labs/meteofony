<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;
use function Castor\io;

/*
 * What fills the development stack once it is up: the dependencies, the migrations, the compiled frontend
 * and the seed data of the French geography. Nothing here judges the code, which is qa.php, and nothing
 * here boots a container of its own, which is stack.php.
 */

#[AsTask(description: 'Compile the frontend.')]
function frontend(bool $watch = false): void
{
    // The command listing goes through the container, like the commands it is there to filter. Run on the
    // host it booted the kernel with the machine's PHP and compiled the DI container into var/cache/dev,
    // which is bind-mounted into the container: two different PHP binaries writing the same cache, and a
    // container compiled without an extension the image carries breaks the FrankenPHP worker at boot.
    $consoleOutput = phpProcess(['bin/console'], true);
    $commandsToRun = [
        'assets:install' => [],
        'importmap:install' => [],
    ];
    $commandsToRun['tailwind:build'] = $watch ? ['--watch'] : [];

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

    // phpProcess() and not php(): php() returns void, so the variable this used to assign was null and
    // the str_contains() below died on "Call to a member function getOutput() on null".
    $consoleOutput = phpProcess(['bin/console'], true);
    $commandsToRun = [
        'doctrine:migrations:migrate' => [],
        'doctrine:schema:validate' => [],
        'importmap:update' => [],
    ];

    foreach ($commandsToRun as $command => $arguments) {
        if (str_contains((string) $consoleOutput->getOutput(), $command)) {
            php(['bin/console', $command, ...$arguments]);
        }
    }
}

#[AsTask(description: 'Initialize the database with its seed data.')]
function init(): void
{
    io()->title('Initializing the project…');

    // The geography is a hierarchy — a Department belongs to a Region, a City to a Department — so these
    // three only make sense in this order. The users are independent of it.
    console(['app:init:regions']);
    console(['app:init:departments']);
    console(['app:init:cities']);
    console(['app:init:users']);

    io()->success('The database is initialized.');
}
