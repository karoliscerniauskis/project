<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outbox_message ADD retry_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE outbox_message ADD failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outbox_message DROP retry_count');
        $this->addSql('ALTER TABLE outbox_message DROP failed_at');
    }
}
