<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'sessions')]
class Session
{
    private function __construct(
        #[ORM\Id] #[ORM\Column(name: 'sess_id', type: Types::STRING, length: 128)] #[ORM\GeneratedValue(
            strategy: 'NONE'
        )] private readonly string $id,
        #[ORM\Column(name: 'sess_data', type: Types::BLOB)] private $data,
        #[ORM\Column(name: 'sess_lifetime', type: Types::INTEGER)] private readonly int $lifetime,
        #[ORM\Column(name: 'sess_time', type: Types::INTEGER)] private readonly int $time,
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

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}
