<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: '`departments`')]
class Department
{
    /**
     * @var Collection<int, City>|City[]
     */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: City::class, fetch: 'EXTRA_LAZY')]
    private Collection $cities;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id,
        #[ORM\ManyToOne(inversedBy: 'departments')]
        #[ORM\JoinColumn(nullable: false)]
        private Region $region,
        #[ORM\Column(length: 10)]
        private string $code,
        #[ORM\Column(length: 100)]
        private string $name,
        #[ORM\Column(length: 100)]
        private string $slug
    ) {
        $this->cities = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }
}
