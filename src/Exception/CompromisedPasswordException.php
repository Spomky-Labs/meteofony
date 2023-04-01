<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class CompromisedPasswordException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Mot de passe compromis.';
    }
}
