<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Admin;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function sprintf;

/**
 * @implements UserProviderInterface<User>
 */
final readonly class AdminProvider/* implements UserProviderInterface*/
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new Admin($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return new Admin($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Admin::class;
    }
}
