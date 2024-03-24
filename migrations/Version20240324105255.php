<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240324105255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE access_token ALTER owner_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE reset_password_request ALTER user_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE reset_password_request ALTER requested_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE reset_password_request ALTER expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'\'');
    }
}
