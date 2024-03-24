<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\UserDtoProvider;

#[ApiResource(operations: [new Get('/me', provider: UserDtoProvider::class)],)]
final readonly class User
{
    public function __construct(
        public string $username,
        public string $email,
    ) {
    }

    public static function createFrom(\App\Entity\User $user)
    {
        return new self($user->getUsername(), $user->getEmail());
    }
}
