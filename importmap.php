<?php

declare(strict_types=1);
return [
    'app' => [
        'path' => 'app.js',
        'preload' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => '@symfony/stimulus-bundle/loader.js',
    ],
    '@hotwired/stimulus' => [
        'url' => 'https://cdn.jsdelivr.net/npm/@hotwired/stimulus@3.2.2/+esm',
    ],
    'leaflet' => [
        'url' => 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/+esm',
    ],
    '@symfony/stimulus-bridge' => [
        'url' => 'https://cdn.jsdelivr.net/npm/@symfony/stimulus-bridge@3.2.2/+esm',
    ],
];
