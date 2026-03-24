<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class NotCompromisedBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(#[\SensitiveParameter] private ?string $password)
    {
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function markAsResolved(): void
    {
        $this->isResolved = true;
        $this->password = null;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
