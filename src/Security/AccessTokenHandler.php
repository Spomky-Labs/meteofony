<?php

namespace App\Security;

use App\Entity\AccessToken;
use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{

    public function __construct(private readonly AccessTokenRepository $accessTokenRepository)
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $accessToken = $this->accessTokenRepository->findOneBy(['value' => $accessToken]);

        if (!$accessToken instanceof AccessToken) {
            throw new BadCredentialsException();
        }

        $user = $accessToken->getOwner();
        return new UserBadge(
            $user->getUserIdentifier(),
            fn() => $user
        );
    }
}
