<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PasswordIsTooOldException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Password is too old.';
    }
}
