<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: '`cities`')]
#[ApiResource(
    operations: [new GetCollection(), new Get()]
)]
class City
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public int $id,
        #[ORM\ManyToOne(inversedBy: 'cities')]
        public Department $department,
        #[ORM\Column(length: 10, nullable: true)]
        public ?string $insee_code,
        #[ORM\Column(length: 10, nullable: true)]
        public ?string $zip_code,
        #[ORM\Column(length: 200)]
        public string $name,
        #[ORM\Column(length: 200)]
        public string $slug,
        #[ORM\Column]
        public float $gps_lat,
        #[ORM\Column]
        public float $gps_lng
    ) {
    }
}
