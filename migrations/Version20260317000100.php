<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE provider_user ADD role VARCHAR(32) NOT NULL');
        $this->addSql('DROP INDEX uniq_provider_user_user_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_provider_user_provider_id_user_id ON provider_user (provider_id, user_id)');
        $this->addSql('CREATE INDEX idx_provider_user_provider_id ON provider_user (provider_id)');
        $this->addSql('CREATE INDEX idx_provider_user_user_id ON provider_user (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_provider_user_user_id');
        $this->addSql('DROP INDEX IF EXISTS idx_provider_user_provider_id');
        $this->addSql('DROP INDEX IF EXISTS uniq_provider_user_provider_id_user_id');
        $this->addSql('ALTER TABLE provider_user DROP role');
        $this->addSql('CREATE UNIQUE INDEX uniq_provider_user_user_id ON provider_user (user_id)');
    }
}
