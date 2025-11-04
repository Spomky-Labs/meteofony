<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => '@symfony/stimulus-bundle/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@symfony/ux-leaflet-map' => [
        'path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bridge' => [
        'version' => '4.0.1',
    ],
    'chart.js/auto' => [
        'version' => '4.5.1',
    ],
    '@kurkle/color' => [
        'version' => '0.4.0',
    ],
    'chart.js' => [
        'version' => '4.5.1',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.20',
    ],
    'tailwindcss' => [
        'version' => '4.1.16',
    ],
    'tailwindcss/index.min.css' => [
        'version' => '4.1.16',
        'type' => 'css',
    ],
    'daisyui' => [
        'version' => '5.4.3',
    ],
    'daisyui/daisyui.min.css' => [
        'version' => '5.4.3',
        'type' => 'css',
    ],
    'daisyui/theme' => [
        'version' => '5.4.3',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
];
