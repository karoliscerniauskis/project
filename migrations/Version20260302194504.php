<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302194504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE provider (id UUID NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE provider_user (id UUID NOT NULL, provider_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_provider_user_user_id ON provider_user (user_id)');
        $this->addSql('ALTER TABLE voucher ADD provider_id UUID NOT NULL');
        $this->addSql('ALTER TABLE voucher ADD issued_to_user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD issued_to_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher ADD claimed_by_user_id UUID DEFAULT NULL');

        $this->addSql('ALTER TABLE provider_user ADD CONSTRAINT fk_provider_user_provider_id FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE provider_user ADD CONSTRAINT fk_provider_user_user_id FOREIGN KEY (user_id) REFERENCES auth_user (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT fk_voucher_provider_id FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT fk_voucher_issued_to_user_id FOREIGN KEY (issued_to_user_id) REFERENCES auth_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT fk_voucher_claimed_by_user_id FOREIGN KEY (claimed_by_user_id) REFERENCES auth_user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE provider');
        $this->addSql('DROP TABLE provider_user');
        $this->addSql('ALTER TABLE voucher DROP provider_id');
        $this->addSql('ALTER TABLE voucher DROP issued_to_user_id');
        $this->addSql('ALTER TABLE voucher DROP issued_to_email');
        $this->addSql('ALTER TABLE voucher DROP claimed_by_user_id');

        $this->addSql('ALTER TABLE provider_user DROP CONSTRAINT fk_provider_user_user_id');
        $this->addSql('ALTER TABLE provider_user DROP CONSTRAINT fk_provider_user_provider_id');
        $this->addSql('ALTER TABLE voucher DROP CONSTRAINT fk_voucher_claimed_by_user_id');
        $this->addSql('ALTER TABLE voucher DROP CONSTRAINT fk_voucher_issued_to_user_id');
        $this->addSql('ALTER TABLE voucher DROP CONSTRAINT fk_voucher_provider_id');
    }
}
