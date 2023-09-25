<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 *
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function save(City $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->persist($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function remove(City $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()
            ->flush();
        $this->getEntityManager()
            ->clear();
    }

    /**
     * @return array<array-key, City>
     */
    public function findByNameLike(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('(LOWER(c.name) LIKE :query) OR (LOWER(c.zipCode) LIKE :query) OR (LOWER(c.inseeCode) LIKE :query)')
            ->setParameter('query', sprintf('%%%s%%', mb_strtolower($query)))
            ->orderBy('c.name', Criteria::ASC)
            ->setMaxResults(50)
            ->getQuery()
            ->getResult()
        ;
    }
}
