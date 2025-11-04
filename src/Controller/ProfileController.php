<?php

declare(strict_types=1);

namespace App\Controller;

//use App\Repository\WebauthnCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProfileController extends AbstractController
{
    /*public function __construct(
        private readonly WebauthnCredentialRepository $webauthnCredentialRepository,
    )
    {
    }*/

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        assert($user instanceof UserInterface);
        //$credentials = $this->webauthnCredentialRepository->findAllForUserHandle($user->getId());
        return $this->render('profile/index.html.twig',[
            //'credentials' => $credentials,
        ]);
    }
}
