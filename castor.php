<?php

declare(strict_types=1);

use function Castor\guard_min_version;
use function Castor\import;

/*
 * The tasks live in .castor/, imported whole (Castor loads every PHP file of the directory); they share the
 * global namespace, so a task keeps the name of its function:
 *
 *   .castor/stack/   the development stack and what the CI runs: the stack itself (stack.php), what fills
 *                    it once it is up (provision.php), and the quality tools and test suites (qa.php).
 *
 * Nothing of .castor/ reaches a release archive (.gitattributes) or a Docker build context
 * (.dockerignore): these are the tasks of a checkout, and the image boots `bin/console`. The CI calls
 * these tasks by name (.github/workflows/ci.yml), so renaming a function renames a CI step.
 */

guard_min_version('v0.23.0');
// Every task reads the repository through paths relative to its root (.ci-tools/ecs.php, compose.yaml) and
// phpqa() mounts getcwd() as /project, so the working directory has to be that root and not wherever the
// caller stood. This is the default of Castor 2.0, declared here to say so and to settle the deprecation
// notice of 1.8.
defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

import(__DIR__ . '/.castor');
