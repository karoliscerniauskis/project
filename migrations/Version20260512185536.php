<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512185536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voucher_usage (
            id UUID NOT NULL,
            voucher_id UUID NOT NULL,
            used_amount INT DEFAULT NULL,
            used_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY (id)
        )');
        $this->addSql('CREATE INDEX idx_voucher_usage_voucher_id ON voucher_usage (voucher_id)');
        $this->addSql('CREATE INDEX idx_voucher_usage_used_at ON voucher_usage (used_at)');
        $this->addSql('ALTER TABLE voucher_usage ADD CONSTRAINT fk_voucher_usage_voucher_id FOREIGN KEY (voucher_id) REFERENCES voucher (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voucher_usage DROP CONSTRAINT fk_voucher_usage_voucher_id');
        $this->addSql('DROP TABLE voucher_usage');
    }
}
