<?php

namespace App\Repository;

use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

class WebauthnUserRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{

    public function __construct(
        private readonly UserRepository $repository
    )
    {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->repository->findOneByUsername($username);

        return PublicKeyCredentialUserEntity::create(
            $user->getUsername(),
            $user->getId(),
            $user->getUsername()
        );
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->repository->findOneById($userHandle);

        return PublicKeyCredentialUserEntity::create(
            $user->getUsername(),
            $user->getId(),
            $user->getUsername()
        );
    }
}
