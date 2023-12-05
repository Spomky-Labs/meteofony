<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231205104534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE users__sessions (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, data BYTEA NOT NULL, lifetime INT NOT NULL, time INT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_D2DDA292A76ED395 ON users__sessions (user_id)');
        $this->addSql(
            'ALTER TABLE users__sessions ADD CONSTRAINT FK_D2DDA292A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE users__sessions DROP CONSTRAINT FK_D2DDA292A76ED395');
        $this->addSql('DROP TABLE users__sessions');
    }
}
