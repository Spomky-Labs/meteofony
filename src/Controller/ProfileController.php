<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\WebauthnCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private WebauthnCredentialRepository $webauthnCredentialRepository
    )
    {
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        $webauthnCredentials = $this->webauthnCredentialRepository->findAllForUserHandle($user->getId());
        return $this->render('profile/index.html.twig', [
            'webauthnCredentials' => $webauthnCredentials,
        ]);
    }
}
