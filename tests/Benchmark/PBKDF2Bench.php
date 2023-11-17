<?php

declare(strict_types=1);

namespace App\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Revs;

final class PBKDF2Bench
{
    /**
     * @Revs(1)
     */
    public function benchPBKDF2_MinimumNISTRecommendation(): void
    {
        hash_pbkdf2('sha256', 'this is a very secret password', random_bytes(16), 1_000, 64, true);
    }

    /**
     * @Revs(1)
     */
    public function benchPBKDF2_UserPerceivedNotCriticalNISTRecommendation(): void
    {
        hash_pbkdf2('sha256', 'this is a very secret password', random_bytes(16), 10_000_000, 64, true);
    }

    /**
     * @Revs(1)
     * @see https://support.lastpass.com/s/document-item?language=en_US&bundleId=lastpass&topicId=LastPass%2Fchange-password-iterations.html&_LANG=enus
     */
    public function benchPBKDF2_Lastpass(): void
    {
        hash_pbkdf2('sha256', 'this is a very secret password', random_bytes(16), 600_000, 64, true);
    }
}
