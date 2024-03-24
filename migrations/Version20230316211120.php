<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230316211120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE access_token (value VARCHAR(255) NOT NULL, owner_id VARCHAR(255) NOT NULL, PRIMARY KEY(value))'
        );
        $this->addSql('CREATE INDEX IDX_B6A2DD687E3C61F9 ON access_token (owner_id)');
        $this->addSql(
            'CREATE TABLE "cities" (id INT NOT NULL, department_id INT DEFAULT NULL, insee_code VARCHAR(10) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, name VARCHAR(200) NOT NULL, slug VARCHAR(200) NOT NULL, gps_lat DOUBLE PRECISION NOT NULL, gps_lng DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_D95DB16BAE80F5DF ON "cities" (department_id)');
        $this->addSql(
            'CREATE TABLE "departments" (id INT NOT NULL, region_id INT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_16AEB8D498260155 ON "departments" (region_id)');
        $this->addSql(
            'CREATE TABLE "regions" (id INT NOT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE TABLE reset_password_request (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE "users" (id VARCHAR(255) NOT NULL, roles JSON NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(200) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON "users" (username)');
        $this->addSql(
            'ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD687E3C61F9 FOREIGN KEY (owner_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "cities" ADD CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES "departments" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE "departments" ADD CONSTRAINT FK_16AEB8D498260155 FOREIGN KEY (region_id) REFERENCES "regions" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
