<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\UserDtoProvider;

/**
 * The authenticated user. Without one there is nothing to return, so the operation asks for an
 * authenticated request, and an anonymous one gets a 401 where the provider used to fail with a 500.
 */
#[ApiResource(operations: [new Get('/me', security: "is_granted('IS_AUTHENTICATED')", provider: UserDtoProvider::class)],)]
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
