<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class Admin implements UserInterface
{
    public function __construct(private string $identifier)
    {
    }

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function eraseCredentials()
    {
        // Nothing to do
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
