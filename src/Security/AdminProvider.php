<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Admin;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class AdminProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Admin::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new Admin($identifier);
    }
}
