<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SecurityEventRepository;
use App\Repository\WebauthnCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly SecurityEventRepository $securityEventRepository,
        private readonly WebauthnCredentialRepository $webauthnCredentialRepository
    ) {
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'credentials' => $this->webauthnCredentialRepository->findAllForUser($this->getUser()->getUserIdentifier()),
            'events' => $this->securityEventRepository->getUserEvents($this->getUser()),
        ]);
    }
}
