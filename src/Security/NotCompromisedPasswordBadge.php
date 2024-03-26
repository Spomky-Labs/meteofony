<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class NotCompromisedPasswordBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(#[\SensitiveParameter] private readonly string $password)
    {
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function markAsResolved(): void
    {
        $this->isResolved = true;
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }
}
