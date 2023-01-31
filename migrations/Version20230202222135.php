<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202222135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "users_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "cities" (id INT NOT NULL, department_id INT DEFAULT NULL, insee_code VARCHAR(10) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, name VARCHAR(200) NOT NULL, slug VARCHAR(200) NOT NULL, gps_lat DOUBLE PRECISION NOT NULL, gps_lng DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_D95DB16BAE80F5DF ON "cities" (department_id)');
        $this->addSql(
            'CREATE TABLE "departments" (id INT NOT NULL, region_id INT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_16AEB8D498260155 ON "departments" (region_id)');
        $this->addSql(
            'CREATE TABLE "measures" (id INT NOT NULL, city_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature DOUBLE PRECISION DEFAULT NULL, humidity INT DEFAULT NULL, temperature_felt DOUBLE PRECISION DEFAULT NULL, wind_direction INT DEFAULT NULL, wind_speed INT DEFAULT NULL, precipitation INT DEFAULT NULL, comment TEXT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_508A1C558BAC62AF ON "measures" (city_id)');
        $this->addSql('COMMENT ON COLUMN "measures".date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE "regions" (id INT NOT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE TABLE "users" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql(
            'ALTER TABLE "cities" ADD CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES "departments" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "departments" ADD CONSTRAINT FK_16AEB8D498260155 FOREIGN KEY (region_id) REFERENCES "regions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "measures" ADD CONSTRAINT FK_508A1C558BAC62AF FOREIGN KEY (city_id) REFERENCES "cities" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "users_id_seq" CASCADE');
        $this->addSql('ALTER TABLE "cities" DROP CONSTRAINT FK_D95DB16BAE80F5DF');
        $this->addSql('ALTER TABLE "departments" DROP CONSTRAINT FK_16AEB8D498260155');
        $this->addSql('ALTER TABLE "measures" DROP CONSTRAINT FK_508A1C558BAC62AF');
        $this->addSql('DROP TABLE "cities"');
        $this->addSql('DROP TABLE "departments"');
        $this->addSql('DROP TABLE "measures"');
        $this->addSql('DROP TABLE "regions"');
        $this->addSql('DROP TABLE "users"');
    }
}
