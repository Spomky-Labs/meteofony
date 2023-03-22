<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidCaptchaException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Captcha invalide.';
    }
}
