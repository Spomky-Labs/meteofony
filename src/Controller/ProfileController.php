<?php

declare(strict_types=1);

namespace App\Controller;

//use App\Repository\WebauthnCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\OidcUser;
use Symfony\Component\Security\Core\User\UserInterface;
use function assert;

final class ProfileController extends AbstractController
{
    /*public function __construct(
        private readonly WebauthnCredentialRepository $webauthnCredentialRepository,
    )
    {
    }*/

    #[Route('/profile', name: 'app_profile')]
    public function index(Security $security): Response
    {
        $user = $this->getUser();
        assert($user instanceof UserInterface);
        $token = $security->getToken();
        assert($token !== null);
        //$credentials = $this->webauthnCredentialRepository->findAllForUserHandle($user->getId());
        return $this->render('profile/index.html.twig', [
            // Twig cannot read a class name off an object, so the template cannot ask the token for
            // its own: which authenticator produced the token is told from here instead.
            'token_class' => $token::class,
            'display_name' => $user instanceof OidcUser ? ($user->getPreferredUsername() ?? $user->getUserIdentifier()) : $user->getUserIdentifier(),
            'claims' => $user instanceof OidcUser ? self::claims($user) : [],
            'access_token' => $token->hasAttribute('oidc_access_token') ? $token->getAttribute('oidc_access_token') : null,
            //'credentials' => $credentials,
        ]);
    }

    /**
     * @return array<string, mixed> the claims of the ID token the OIDC provider issued
     */
    private static function claims(OidcUser $user): array
    {
        $standard = [
            'sub' => $user->getSub(),
            'preferred_username' => $user->getPreferredUsername(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'email_verified' => $user->getEmailVerified(),
        ];

        return array_filter($standard, static fn ($v): bool => null !== $v) + $user->getAdditionalClaims();
    }
}
