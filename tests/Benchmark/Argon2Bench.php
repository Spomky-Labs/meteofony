<?php

declare(strict_types=1);

namespace App\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Revs;
use const PASSWORD_BCRYPT;

final class Argon2Bench
{
    /**
     * @Revs(100)
     */
    public function benchArgon2I(): void
    {
        password_hash('this is a very secret password', PASSWORD_ARGON2I);
    }

    /**
     * @Revs(100)
     */
    public function benchArgon2ID(): void
    {
        password_hash('this is a very secret password', PASSWORD_ARGON2ID);
    }
}
