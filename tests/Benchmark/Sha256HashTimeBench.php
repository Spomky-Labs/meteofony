<?php

declare(strict_types=1);

namespace App\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Revs;
use const PASSWORD_BCRYPT;

final class Sha256HashTimeBench
{
    /**
     * @Revs(100)
     */
    public function benchBcrypt4(): void
    {
        password_hash('this is a very secret password', PASSWORD_BCRYPT, [
            'cost' => 4,
        ]);
    }

    /**
     * @Revs(100)
     */
    public function benchBcryptDefault(): void
    {
        password_hash('this is a very secret password', PASSWORD_BCRYPT);
    }

    public function benchBcrypt15(): void
    {
        password_hash('this is a very secret password', PASSWORD_BCRYPT, [
            'cost' => 13,
        ]);
    }
}
