<?php

declare(strict_types=1);

namespace App\Tests\Benchmark;

use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

final class Sha256HashTimeBench
{
    /**
     * @Revs(100000)
     */
    public function benchSha256(): void
    {
        hash('sha256', 'this is a very secret password');
    }

    /**
     * @Revs(100000)
     */
    public function benchSha512(): void
    {
        hash('sha512', 'this is a very secret password');
    }

    /**
     * @Revs(1)
     */
    public function benchPbkdf2(): void
    {
        hash_pbkdf2('sha256', 'this is a very secret password', random_bytes(16), 600_000);
    }

    /**
     * @Revs(100)
     */
    public function benchBcrypt(): void
    {
        password_hash('this is a very secret password', PASSWORD_BCRYPT);
    }

    /**
     * @Revs(100)
     */
    public function benchArgon2id(): void
    {
        password_hash('this is a very secret password', PASSWORD_ARGON2ID, [
            'memory_cost' => 32 * 1024,
            'time_cost' => 4,
            'threads' => 1,
        ]);
    }
}
