<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260507000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE voucher ADD type VARCHAR(255) DEFAULT 'amount' NOT NULL");
        $this->addSql('ALTER TABLE voucher ADD initial_amount INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD remaining_amount INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD initial_usages INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD remaining_usages INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voucher DROP type');
        $this->addSql('ALTER TABLE voucher DROP initial_amount');
        $this->addSql('ALTER TABLE voucher DROP remaining_amount');
        $this->addSql('ALTER TABLE voucher DROP initial_usages');
        $this->addSql('ALTER TABLE voucher DROP remaining_usages');
    }
}
