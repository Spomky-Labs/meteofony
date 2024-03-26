<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240326142746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD auth_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DFEA849072A8BD77 ON webauthn_credentials (public_key_credential_id)');
        $this->addSql('CREATE INDEX idx_webauthn_credentials_user_handle ON webauthn_credentials (user_handle)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_DFEA849072A8BD77');
        $this->addSql('DROP INDEX idx_webauthn_credentials_user_handle');
        $this->addSql('ALTER TABLE "users" DROP auth_code');
    }
}
