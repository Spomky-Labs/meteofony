<?php

namespace App\Repository;

use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class WebauthnUserRepository/* implements PublicKeyCredentialUserEntityRepositoryInterface*/
{

    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepository->findOneByUsername($username);
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUserIdentifier(),
            $user->getId(),
            $user->getUsername(),
        );
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepository->findOneById($userHandle);
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUserIdentifier(),
            $user->getId(),
            $user->getUsername(),
        );
    }
}
