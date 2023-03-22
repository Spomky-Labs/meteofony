<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\AccessToken;
use App\Repository\AccessTokenRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccessTokenRepository $accessTokenRepository
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $accessToken = $this->accessTokenRepository->findOneBy([
            'value' => $accessToken,
        ]);
        if (! $accessToken instanceof AccessToken) {
            throw new AuthenticationException('Invalid access token');
        }

        return new UserBadge($accessToken->getOwner() ->getUserIdentifier());
    }
}
