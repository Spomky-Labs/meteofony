<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        #[ORM\Column]
        private readonly string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private readonly ?User $user,
        DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ) {
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): object
    {
        return $this->user;
    }
}
