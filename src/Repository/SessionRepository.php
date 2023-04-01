<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Clock\ClockInterface;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class SessionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ClockInterface $clock
    ) {
        parent::__construct($registry, Session::class);
    }

    public function save(Session $entity): void
    {
        $this->getEntityManager()
            ->persist($entity);

        $this->getEntityManager()
            ->flush();
    }

    public function remove(Session $entity): void
    {
        $this->getEntityManager()
            ->remove($entity);

        $this->getEntityManager()
            ->flush();
    }

    /**
     * @return array<Session>
     */
    public function findAllUserSessions(User $user): array
    {
        $userSessions = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(UserSession::class, 'u')
            ->select('u.sessionId')
            ->where('u.user = :user')
        ;

        $qb = $this->createQueryBuilder('o');
        return $qb
            ->where($qb->expr()->in('o.id', $userSessions->getDQL()))
            ->setParameter('user', $user)
            ->getQuery()
            ->execute()
        ;
    }

    public function clearOtherSessions(User $user, string $currentSessionId): void
    {
        $userSessions = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(UserSession::class, 'u')
            ->select('u.sessionId')
            ->where('u.user = :user')
            ->andWhere('u.sessionId != :currentSessionId')
        ;

        $qb = $this->createQueryBuilder('o');
        $qb
            ->delete()
            ->where($qb->expr()->in('o.id', $userSessions->getDQL()))
            ->orWhere('o.lifetime < :currentTime')
            ->setParameter('user', $user)
            ->setParameter('currentSessionId', $currentSessionId)
            ->setParameter('currentTime', $this->clock->now()->getTimestamp())
            ->getQuery()
            ->execute()
        ;
    }
}
