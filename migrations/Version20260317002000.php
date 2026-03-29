<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317002000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE outbox_message (
            id UUID NOT NULL,
            event_name VARCHAR(255) NOT NULL,
            payload JSON NOT NULL,
            occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            processing_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX idx_outbox_message_processing_at ON outbox_message (processing_at)');
        $this->addSql('CREATE INDEX idx_outbox_message_processed_at ON outbox_message (processed_at)');
        $this->addSql('CREATE INDEX idx_outbox_message_occurred_at ON outbox_message (occurred_at)');
        $this->addSql('CREATE INDEX idx_outbox_message_event_name ON outbox_message (event_name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_outbox_message_processing_at');
        $this->addSql('DROP INDEX idx_outbox_message_processed_at');
        $this->addSql('DROP INDEX idx_outbox_message_occurred_at');
        $this->addSql('DROP INDEX idx_outbox_message_event_name');

        $this->addSql('DROP TABLE outbox_message');
    }
}
