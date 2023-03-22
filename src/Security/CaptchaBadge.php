<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class CaptchaBadge implements BadgeInterface
{
    private bool $isResolved = false;

    public function __construct(
        private readonly string $identifier,
        private readonly string $captcha,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getCaptcha(): string
    {
        return $this->captcha;
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
