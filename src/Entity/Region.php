<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: '`regions`')]
class Region
{
    /**
     * @var Collection<int, Department>|Department[]
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class, fetch: 'EXTRA_LAZY')]
    private Collection $departments;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id,
        #[ORM\Column(length: 5)]
        private string $code,
        #[ORM\Column(length: 100)]
        private string $name,
        #[ORM\Column(length: 100)]
        private string $slug
    ) {
        $this->departments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }
}
