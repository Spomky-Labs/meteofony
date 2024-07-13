<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\Region;
use App\Repository\CityRepository;
use App\Repository\DepartmentRepository;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    public function __construct(
        private readonly RegionRepository $regionRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly CityRepository $cityRepository,
    ) {
    }

    #[Route('/api/regions', name: 'app_api_regions')]
    public function regions(): JsonResponse
    {
        $regions = $this->regionRepository->findAll();
        $data = array_map(static fn (Region $region): array => [
            'id' => $region->getId(),
            'code' => $region->getCode(),
            'name' => $region->getName(),
            'slug' => $region->getSlug(),
            'departments' => array_map(
                static fn (Department $department): int => $department->getId(),
                $region->getDepartments()
                    ->toArray()
            ),
        ], $regions);

        return new JsonResponse($data);
    }

    #[Route('/api/regions/{id}', name: 'app_api_region')]
    public function index(string $id): JsonResponse
    {
        $region = $this->regionRepository->findOneBy([
            'id' => $id,
        ]);
        $region !== null || throw $this->createNotFoundException();

        return new JsonResponse([
            'id' => $region->getId(),
            'code' => $region->getCode(),
            'name' => $region->getName(),
            'slug' => $region->getSlug(),
            'departments' => array_map(
                static fn (Department $department): int => $department->getId(),
                $region->getDepartments()
                    ->toArray()
            ),
        ]);
    }

    #[Route('/api/departments/{id}', name: 'app_api_department')]
    public function department(string $id): JsonResponse
    {
        $department = $this->departmentRepository->findOneBy([
            'id' => $id,
        ]);
        $department !== null || throw $this->createNotFoundException();

        return new JsonResponse([
            'id' => $department->getId(),
            'code' => $department->getCode(),
            'name' => $department->getName(),
            'slug' => $department->getSlug(),
            'cities' => array_map(static fn (City $city): array => [
                'id' => $city->getId(),
                'insee' => $city->getInseeCode(),
                'zip' => $city->getZipCode(),
                'name' => $city->getName(),
            ], $department->getCities()
                ->toArray()),
        ]);
    }

    #[Route('/api/cities/{id}', name: 'app_api_city')]
    public function city(string $id): JsonResponse
    {
        $city = $this->cityRepository->findOneBy([
            'id' => $id,
        ]);
        $city !== null || throw $this->createNotFoundException();

        return new JsonResponse([
            'id' => $city->getId(),
            'insee' => $city->getInseeCode(),
            'zip' => $city->getZipCode(),
            'name' => $city->getName(),
            'slug' => $city->getSlug(),
            'latitude' => $city->getGpsLat(),
            'longitude' => $city->getGpsLng(),
        ]);
    }
}
