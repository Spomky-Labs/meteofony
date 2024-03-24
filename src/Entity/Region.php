<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: '`regions`')]
#[ApiResource(operations: [new GetCollection(), new Get()])]
class Region
{
    /**
     * @var Collection<int, Department>|Department[]
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class, fetch: 'EXTRA_LAZY')]
    public Collection $departments;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public int $id,
        #[ORM\Column(length: 5)]
        public string $code,
        #[ORM\Column(length: 100)]
        public string $name,
        #[ORM\Column(length: 100)]
        public string $slug
    ) {
        $this->departments = new ArrayCollection();
    }
}
