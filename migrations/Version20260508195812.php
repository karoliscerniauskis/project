<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260508195812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voucher_reminder (id UUID NOT NULL, voucher_id UUID NOT NULL, type VARCHAR(255) NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_voucher_reminder_type ON voucher_reminder (voucher_id, type)');
        $this->addSql('ALTER TABLE provider ADD claim_reminder_after_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE provider ADD expiry_reminder_before_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE voucher ADD expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE voucher_reminder');
        $this->addSql('ALTER TABLE provider DROP claim_reminder_after_days');
        $this->addSql('ALTER TABLE provider DROP expiry_reminder_before_days');
        $this->addSql('ALTER TABLE voucher DROP created_at');
        $this->addSql('ALTER TABLE voucher DROP expires_at');
    }
}
