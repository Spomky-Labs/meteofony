<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230203213445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE measures DROP CONSTRAINT fk_508a1c558bac62af');
        $this->addSql('DROP TABLE measures');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql(
            'CREATE TABLE measures (id INT NOT NULL, city_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature DOUBLE PRECISION DEFAULT NULL, humidity INT DEFAULT NULL, temperature_felt DOUBLE PRECISION DEFAULT NULL, wind_direction INT DEFAULT NULL, wind_speed INT DEFAULT NULL, precipitation INT DEFAULT NULL, comment TEXT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX idx_508a1c558bac62af ON measures (city_id)');
        $this->addSql('COMMENT ON COLUMN measures.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'ALTER TABLE measures ADD CONSTRAINT fk_508a1c558bac62af FOREIGN KEY (city_id) REFERENCES cities (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
