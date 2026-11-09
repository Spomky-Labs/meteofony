<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Presents the users of the application to the WebAuthn bundle (config/packages/webauthn.yaml).
 *
 * The user handle is the ULID of the User: WebAuthn asks for an opaque handle that never changes, which
 * the username, editable and meaningful, is not.
 */
final readonly class WebauthnUserRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        return $this->toUserEntity($this->userRepository->findOneByUsername($username));
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        return $this->toUserEntity($this->userRepository->find($userHandle));
    }

    private function toUserEntity(?User $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        return PublicKeyCredentialUserEntity::create(
            $user->getUserIdentifier(),
            $user->getId(),
            $user->getUserIdentifier(),
        );
    }
}
