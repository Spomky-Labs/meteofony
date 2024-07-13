<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrivacyPolicyController extends AbstractController
{
    #[Route('/privacy', name: 'app_privacy_policy')]
    public function index(): Response
    {
        return $this->render('privacy_policy/index.html.twig', [
            'controller_name' => 'PrivacyPolicyController',
        ]);
    }
}
