<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class OldPasswordBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function markAsResolved(): void
    {
        $this->isResolved = true;
    }
}
