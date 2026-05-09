<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_user ADD email_breach_check_enabled BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE auth_user ADD email_breach_checked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_user ADD email_breached_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_user ADD email_breach_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_user DROP email_breach_check_enabled');
        $this->addSql('ALTER TABLE auth_user DROP email_breach_checked_at');
        $this->addSql('ALTER TABLE auth_user DROP email_breached_at');
        $this->addSql('ALTER TABLE auth_user DROP email_breach_count');
    }
}
