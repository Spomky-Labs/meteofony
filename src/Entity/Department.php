<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: '`departments`')]
#[ApiResource(
    operations: [new GetCollection(), new Get()]
)]
class Department
{
    /**
     * @var Collection<int, City>|City[]
     */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: City::class, fetch: 'EXTRA_LAZY')]
    public Collection $cities;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public int $id,
        #[ORM\ManyToOne(inversedBy: 'departments')]
        #[ORM\JoinColumn(nullable: false)]
        public Region $region,
        #[ORM\Column(length: 10)]
        public string $code,
        #[ORM\Column(length: 100)]
        public string $name,
        #[ORM\Column(length: 100)]
        public string $slug
    ) {
        $this->cities = new ArrayCollection();
    }
}
