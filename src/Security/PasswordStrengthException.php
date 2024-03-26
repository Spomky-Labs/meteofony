<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PasswordStrengthException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'The password strength is too low. Please use a stronger password.';
    }
}
