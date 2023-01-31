<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly CityRepository $cityRepository
    ) {
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/dashboard/{id}', name: 'app_dashboard_city')]
    public function city(string $id): Response
    {
        $city = $this->cityRepository->findOneBy([
            'id' => $id,
        ]);
        $city !== null || throw $this->createNotFoundException();

        return $this->render('dashboard/city.html.twig', [
            'city' => $city,
        ]);
    }
}
