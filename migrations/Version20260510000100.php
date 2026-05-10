<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add voucher templates and voucher template snapshot fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voucher_template (
            id UUID NOT NULL,
            provider_id UUID NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            html_template TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('ALTER TABLE voucher ADD voucher_template_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD voucher_template_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD voucher_template_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD voucher_template_html TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voucher DROP voucher_template_id');
        $this->addSql('ALTER TABLE voucher DROP voucher_template_title');
        $this->addSql('ALTER TABLE voucher DROP voucher_template_description');
        $this->addSql('ALTER TABLE voucher DROP voucher_template_html');
        $this->addSql('DROP TABLE voucher_template');
    }
}
