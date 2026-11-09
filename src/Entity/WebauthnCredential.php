<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\TrustPath\TrustPath;

/**
 * A WebAuthn authenticator registered by a user. Every field but the identifier comes from the mapped
 * superclass the bundle ships for CredentialRecord; `user_handle` is the id of the User it belongs to
 * (WebauthnUserRepository), hence the index.
 */
#[Table(name: 'webauthn_credentials')]
#[Entity(repositoryClass: WebauthnCredentialRepository::class)]
#[Index(name: 'idx_webauthn_credentials_user_handle', columns: ['user_handle'])]
class WebauthnCredential extends CredentialRecord
{
    #[Id]
    #[Column(unique: true)]
    #[GeneratedValue(strategy: 'NONE')]
    private string $id;

    /**
     * @param string[] $transports
     * @param array<string, mixed>|null $otherUI
     */
    public function __construct(
        string $publicKeyCredentialId,
        string $type,
        array $transports,
        string $attestationType,
        TrustPath $trustPath,
        Uuid $aaguid,
        string $credentialPublicKey,
        string $userHandle,
        int $counter,
        ?array $otherUI = null,
        ?bool $backupEligible = null,
        ?bool $backupStatus = null,
        ?bool $uvInitialized = null,
    ) {
        $this->id = Ulid::generate();
        parent::__construct(
            $publicKeyCredentialId,
            $type,
            $transports,
            $attestationType,
            $trustPath,
            $aaguid,
            $credentialPublicKey,
            $userHandle,
            $counter,
            $otherUI,
            $backupEligible,
            $backupStatus,
            $uvInitialized,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
}
