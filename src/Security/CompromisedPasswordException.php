<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CompromisedPasswordException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'The password has been compromised. Please choose another one.';
    }
}
