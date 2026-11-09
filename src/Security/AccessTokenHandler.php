<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\AccessTokenRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Turns the `Authorization: Bearer <token>` header of the `api` firewall into the user the token was
 * issued to (`app:access_token:create`).
 */
final readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $token = $this->accessTokenRepository->find($accessToken);
        if ($token === null) {
            throw new BadCredentialsException('Invalid access token.');
        }

        $owner = $token->getOwner();

        // The owner is already loaded with the token, so the badge hands it over rather than having the
        // user provider fetch it again by its identifier.
        return new UserBadge($owner->getUserIdentifier(), static fn (): User => $owner);
    }
}
