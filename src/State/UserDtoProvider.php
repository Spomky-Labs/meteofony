<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class UserDtoProvider implements ProviderInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! $operation instanceof Get) {
            throw new InvalidArgumentException('Unsupported operation');
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (! $user instanceof User) {
            throw new InvalidArgumentException('Are you logged in?');
        }

        return \App\ApiResource\User::createFrom($user);
    }
}
