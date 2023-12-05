<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserSessionRepository;
use App\UserSession\UserSessionInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'users__sessions')]
class UserSession implements UserSessionInterface
{
    public function __construct(
        #[ORM\Id] #[ORM\Column] #[ORM\GeneratedValue(strategy: 'NONE')] public string $id,
        #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'sessions')]
        #[ORM\JoinColumn(nullable: true)]
        public null|UserInterface $user,
        #[ORM\Column(type: Types::BLOB)] public $data,
        #[ORM\Column(type: Types::INTEGER)] public int $lifetime,
        #[ORM\Column(type: Types::INTEGER)] public int $time,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function setLifetime(int $lifetime): void
    {
        $this->lifetime = $lifetime;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        if ($this->user !== null) {
            throw new LogicException('User already set.');
        }
        $this->user = $user;
    }
}
