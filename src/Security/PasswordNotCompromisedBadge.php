<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class PasswordNotCompromisedBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(
        public readonly bool $skipOnError = true,
        public readonly int $threshold = 1,
    ) {
    }

    public function markResolved(): void
    {
        $this->isResolved = true;
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }
}
