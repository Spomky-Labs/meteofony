<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'user_sessions')]
class UserSession
{
    public function __construct(
        #[ORM\Id] #[ORM\Column] #[ORM\GeneratedValue(strategy: 'NONE')] private readonly string $id,
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly User $user,
        #[ORM\Column(type: Types::STRING)]
        private readonly string $sessionId,
    ) {
    }

    public static function create(User $user, string $sessionId): self
    {
        return new self(Ulid::generate(), $user, $sessionId);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
