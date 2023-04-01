<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';


$zxcvbn = new ZxcvbnPhp\Zxcvbn();

$strength = $zxcvbn->passwordStrength('meteofony-12R', [
    'meteofony',
    'robert',
    'DupoND',
    'robert.du@gmail.com'
]);
dump($strength);
