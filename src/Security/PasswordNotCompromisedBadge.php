<?php

declare(strict_types=1);

namespace App\Security;

use SensitiveParameter;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class PasswordNotCompromisedBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(
        #[SensitiveParameter] private string $password
    ) {
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function markResolved()
    {
        $this->isResolved = true;
        $this->password = '';

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
