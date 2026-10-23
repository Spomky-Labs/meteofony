<?php

declare(strict_types=1);

/*
 * Router for PHP's built-in server, which `castor start` falls back to when neither Docker nor the Symfony
 * CLI is available (.castor/stack/stack.php, startPhp()).
 *
 * Two things it is here for. The built-in server answers 404 for any path that is not a file under the
 * document root, so every route of the application has to be handed to the front controller; and returning
 * false for the paths that are files keeps AssetMapper's /assets/* served by the server itself, without a
 * kernel boot per file.
 *
 * SCRIPT_NAME is rewritten because Symfony builds every generated URL from it. Left as the path of this
 * script, the application would link to /scripts/server-router.php/login.
 */

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), \PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? rawurldecode($path) : '/';

$public = \dirname(__DIR__) . '/public';
$front = $public . '/index.php';

// `..` is refused rather than resolved: this serves the document root and has no business reaching above
// it, whatever the request spells.
if ($path !== '/' && ! str_contains($path, '..') && is_file($public . $path)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $front;

require $front;
