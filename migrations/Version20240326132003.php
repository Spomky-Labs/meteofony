<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240326132003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE webauthn_credentials (id VARCHAR(255) NOT NULL, public_key_credential_id TEXT NOT NULL, type VARCHAR(255) NOT NULL, transports TEXT NOT NULL, attestation_type VARCHAR(255) NOT NULL, trust_path JSON NOT NULL, aaguid TEXT NOT NULL, credential_public_key TEXT NOT NULL, user_handle VARCHAR(255) NOT NULL, counter INT NOT NULL, other_ui TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.public_key_credential_id IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.transports IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.trust_path IS \'(DC2Type:trust_path)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.aaguid IS \'(DC2Type:aaguid)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.credential_public_key IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.other_ui IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE reset_password_request ALTER requested_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE reset_password_request ALTER expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE users ALTER last_password_change TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN users.last_password_change IS \'(DC2Type:datetime_immutable)\'');
    }
}
