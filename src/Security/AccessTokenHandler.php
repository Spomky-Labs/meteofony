<?php

namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
    )
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $accessToken = $this->accessTokenRepository->findOneBy(['value' => $accessToken]);
        if (!$accessToken) {
            throw new UserNotFoundException('User not found');
        }

        return new UserBadge(
            $accessToken->getOwner()->getUsername(),
            fn() => $accessToken->getOwner()
        );
    }
}
