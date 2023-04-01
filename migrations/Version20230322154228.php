<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230322154228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE security_event (id VARCHAR(255) NOT NULL, owner_id VARCHAR(255) NOT NULL, geoip JSON DEFAULT NULL, type VARCHAR(255) NOT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ip_address VARCHAR(100) DEFAULT NULL, browser VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D712E90D7E3C61F9 ON security_event (owner_id)');
        $this->addSql('COMMENT ON COLUMN security_event.occurred_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE security_event ADD CONSTRAINT FK_D712E90D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD auth_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE security_event DROP CONSTRAINT FK_D712E90D7E3C61F9');
        $this->addSql('DROP TABLE security_event');
        $this->addSql('ALTER TABLE "users" DROP auth_code');
    }
}
