<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303223021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_user ADD email_verification_slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_user ADD email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3B536FD52A277A2 ON auth_user (email_verification_slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A3B536FD52A277A2');
        $this->addSql('ALTER TABLE auth_user DROP email_verification_slug');
        $this->addSql('ALTER TABLE auth_user DROP email_verified_at');
    }
}
