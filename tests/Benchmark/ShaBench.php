<?php

declare(strict_types=1);

namespace App\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Revs;

final class ShaBench
{
    /**
     * @Revs(100)
     */
    public function benchSha1(): void
    {
        hash_hmac('sha1', 'this is a very secret password', random_bytes(16), true);
    }

    /**
     * @Revs(100)
     */
    public function benchSha256(): void
    {
        hash_hmac('sha256', 'this is a very secret password', random_bytes(16), true);
    }

    /**
     * @Revs(100)
     */
    public function benchSha512(): void
    {
        hash_hmac('sha512', 'this is a very secret password', random_bytes(16), true);
    }

    /**
     * @Revs(100)
     */
    public function benchSha3_256(): void
    {
        hash_hmac('sha3-256', 'this is a very secret password', random_bytes(16), true);
    }

    /**
     * @Revs(100)
     */
    public function benchSha3_512(): void
    {
        hash_hmac('sha3-512', 'this is a very secret password', random_bytes(16), true);
    }
}
