<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231205145232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webauthn_credentials (id VARCHAR(255) NOT NULL, public_key_credential_id TEXT NOT NULL, type VARCHAR(255) NOT NULL, transports TEXT NOT NULL, attestation_type VARCHAR(255) NOT NULL, trust_path JSON NOT NULL, aaguid TEXT NOT NULL, credential_public_key TEXT NOT NULL, user_handle VARCHAR(255) NOT NULL, counter INT NOT NULL, other_ui TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.public_key_credential_id IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.transports IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.trust_path IS \'(DC2Type:trust_path)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.aaguid IS \'(DC2Type:aaguid)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.credential_public_key IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.other_ui IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE webauthn_credentials');
    }
}
