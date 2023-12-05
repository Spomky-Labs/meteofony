<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserSession;
use App\UserSession\UserSessionInterface;
use App\UserSession\UserSessionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Clock\ClockInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<UserSession>
 *
 * @method UserSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSession[]    findAll()
 * @method UserSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserSessionRepository extends ServiceEntityRepository implements UserSessionRepositoryInterface
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly ClockInterface $clock
    ) {
        parent::__construct($registry, UserSession::class);
    }

    public function save(UserSessionInterface $userSession): void
    {
        $this->getEntityManager()
            ->persist($userSession);

        $this->getEntityManager()
            ->flush();
    }

    public function remove(UserSessionInterface $userSession): void
    {
        $this->getEntityManager()
            ->remove($userSession);

        $this->getEntityManager()
            ->flush();
    }

    public function findOneById(string $sessionId): ?UserSessionInterface
    {
        return $this->findOneBy([
            'id' => $sessionId,
        ]);
    }

    public function removeExpired(): void
    {
        $this->createQueryBuilder('user_session')
            ->delete()
            ->where('user_session.lifetime < :currentTime')
            ->setParameter('currentTime', $this->clock->now()->getTimestamp())
            ->getQuery()
            ->execute();
    }

    public function create(string $sessionId, string $data, int $maxLifetime, int $getTimestamp): UserSessionInterface
    {
        return new UserSession($sessionId, null, $data, $maxLifetime, $getTimestamp);
    }

    /**
     * @return array<UserSessionInterface>
     */
    public function findAllUserSessions(UserInterface $user): array
    {
        return $this->createQueryBuilder('user_session')
            ->where('user_session.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
