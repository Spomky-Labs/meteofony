<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WebauthnCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\Bundle\Repository\CanSaveCredentialRecord;
use Webauthn\Bundle\Repository\CredentialRecordRepositoryInterface;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Where the WebAuthn bundle stores and finds the authenticators (config/packages/webauthn.yaml).
 *
 * It does not extend the DoctrineCredentialSourceRepository of the bundle, which is deprecated since 5.2.
 *
 * @extends ServiceEntityRepository<WebauthnCredential>
 */
final class WebauthnCredentialRepository extends ServiceEntityRepository implements CredentialRecordRepositoryInterface, CanSaveCredentialRecord
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    /**
     * A newly registered authenticator arrives as a plain CredentialRecord, which Doctrine cannot persist,
     * so it is copied into the entity first. One loaded from here already is the entity.
     */
    public function saveCredentialRecord(CredentialRecord $credentialRecord): void
    {
        if (! $credentialRecord instanceof WebauthnCredential) {
            $credentialRecord = new WebauthnCredential(
                $credentialRecord->publicKeyCredentialId,
                $credentialRecord->type,
                $credentialRecord->transports,
                $credentialRecord->attestationType,
                $credentialRecord->trustPath,
                $credentialRecord->aaguid,
                $credentialRecord->credentialPublicKey,
                $credentialRecord->userHandle,
                $credentialRecord->counter,
                $credentialRecord->otherUI,
                $credentialRecord->backupEligible,
                $credentialRecord->backupStatus,
                $credentialRecord->uvInitialized,
            );
        }

        $this->getEntityManager()
            ->persist($credentialRecord);
        $this->getEntityManager()
            ->flush();
    }

    /**
     * @return array<WebauthnCredential>
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return $this->findAllForUserHandle($publicKeyCredentialUserEntity->id);
    }

    /**
     * @return array<WebauthnCredential>
     */
    public function findAllForUserHandle(string $userHandle): array
    {
        return $this->findBy([
            'userHandle' => $userHandle,
        ]);
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?WebauthnCredential
    {
        // The column holds the identifier base64-encoded, through the `base64` DBAL type of the bundle.
        // findOneBy() binds the criterion with the type of its field, so the raw identifier goes in as is;
        // a query builder parameter would need the base64_encode() by hand.
        return $this->findOneBy([
            'publicKeyCredentialId' => $publicKeyCredentialId,
        ]);
    }
}
