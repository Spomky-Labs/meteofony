<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SecurityEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<SecurityEvent>
 *
 * @method SecurityEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecurityEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecurityEvent[]    findAll()
 * @method SecurityEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecurityEventRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, SecurityEvent::class);
    }

    public function save(SecurityEvent $entity, bool $flush = true): void
    {
        $this->getEntityManager()
            ->persist($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function remove(SecurityEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->remove($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    /**
     * @return SecurityEvent[]
     */
    public function paginateForUser(User $user, int $page): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.occurredAt', Criteria::DESC)
            ->where('o.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute()
        ;
    }
}
