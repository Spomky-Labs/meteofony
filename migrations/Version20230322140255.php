<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230322140255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql(
            'CREATE TABLE sessions (sess_id VARCHAR(128) NOT NULL PRIMARY KEY,sess_data BYTEA NOT NULL,sess_lifetime INTEGER NOT NULL,sess_time INTEGER NOT NULL);'
        );
        $this->addSql('CREATE INDEX sessions_sess_lifetime_idx ON sessions (sess_lifetime);');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_sessions (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, session_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7AED7913A76ED395 ON user_sessions (user_id)');
        $this->addSql('ALTER TABLE user_sessions ADD CONSTRAINT FK_7AED7913A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_sessions DROP CONSTRAINT FK_7AED7913A76ED395');
        $this->addSql('DROP TABLE user_sessions');
    }
}
