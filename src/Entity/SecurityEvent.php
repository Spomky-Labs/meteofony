<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SecurityEventRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: SecurityEventRepository::class)]
class SecurityEvent
{
    final public const EVENT_LOGOUT = 'logout';

    final public const EVENT_LOGIN_SUCCESS = 'login_success';

    final public const EVENT_LOGIN_REMEMBERME_SUCCESS = 'login_rememberme_success';

    final public const EVENT_LOGIN_FAILURE = 'login_failure';

    final public const EVENT_MFA_SUCCESS = 'mfa_success';

    final public const EVENT_MFA_FAILURE = 'mfa_failure';

    final public const EVENT_MFA_ENABLE = 'mfa_enable';

    final public const EVENT_MFA_DISABLE = 'mfa_disable';

    final public const EVENT_PASSWORD_RESET_REQUEST = 'password_reset_request';

    final public const EVENT_PASSWORD_RESET = 'password_reset';

    final public const EVENT_PASSWORD_CHANGED = 'password_changed';

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $geoip = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        private readonly string $id,
        #[ORM\Column(length: 255)]
        private readonly string $type,
        #[ORM\ManyToOne(inversedBy: 'securityEvents')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly User $owner,
        #[ORM\Column]
        private readonly DateTimeImmutable $occurredAt,
        #[ORM\Column(length: 100, nullable: true)]
        private readonly ?string $ipAddress,
        #[ORM\Column(length: 255, nullable: true)]
        private readonly ?string $browser
    ) {
    }

    public static function create(
        string $type,
        User $owner,
        DateTimeImmutable $occurredAt,
        ?string $ipAddress,
        ?string $browser
    ): self {
        return new self(Ulid::generate(), $type, $owner, $occurredAt, $ipAddress, $browser);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGeoip(): ?array
    {
        return $this->geoip;
    }

    /**
     * @param array<string, mixed>|null $geoip
     */
    public function setGeoip(?array $geoip): void
    {
        $this->geoip = $geoip;
    }
}
