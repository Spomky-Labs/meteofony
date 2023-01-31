<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        #[ORM\Column(length: 255)]
        private string $value,
        #[ORM\ManyToOne(inversedBy: 'accessTokens')]
        #[ORM\JoinColumn(nullable: false)]
        private User $owner
    ) {
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
