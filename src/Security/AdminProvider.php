<?php

namespace App\Security;

use App\Entity\Admin;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminProvider implements UserProviderInterface
{

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByIdentifier($user->getUsername());
    }

    public function supportsClass(string $class)
    {
        return Admin::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new Admin($identifier);
    }
}
