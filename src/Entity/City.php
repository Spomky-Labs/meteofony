<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: '`cities`')]
class City
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id,
        #[ORM\ManyToOne(inversedBy: 'cities')]
        private Department $department,
        #[ORM\Column(length: 10, nullable: true)]
        private ?string $insee_code,
        #[ORM\Column(length: 10, nullable: true)]
        private ?string $zip_code,
        #[ORM\Column(length: 200)]
        private string $name,
        #[ORM\Column(length: 200)]
        private string $slug,
        #[ORM\Column]
        private float $gps_lat,
        #[ORM\Column]
        private float $gps_lng
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDepartmentCode(): Department
    {
        return $this->department;
    }

    public function setDepartmentCode(Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getInseeCode(): ?string
    {
        return $this->insee_code;
    }

    public function setInseeCode(?string $insee_code): self
    {
        $this->insee_code = $insee_code;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function setZipCode(?string $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getGpsLat(): float
    {
        return $this->gps_lat;
    }

    public function setGpsLat(float $gps_lat): self
    {
        $this->gps_lat = $gps_lat;

        return $this;
    }

    public function getGpsLng(): float
    {
        return $this->gps_lng;
    }

    public function setGpsLng(float $gps_lng): self
    {
        $this->gps_lng = $gps_lng;

        return $this;
    }
}
