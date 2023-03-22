<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class PasswordMaximumLengthBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(
        public readonly int $maxLength = 64
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
