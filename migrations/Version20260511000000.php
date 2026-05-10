<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create provider_links table for linking providers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE provider_link (id UUID NOT NULL, provider_id UUID NOT NULL, linked_provider_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_PROVIDER_LINKS_PROVIDER_ID ON provider_link (provider_id)');
        $this->addSql('CREATE INDEX IDX_PROVIDER_LINKS_LINKED_PROVIDER_ID ON provider_link (linked_provider_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PROVIDER_LINK ON provider_link (provider_id, linked_provider_id)');
        $this->addSql('ALTER TABLE provider_link ADD CONSTRAINT FK_PROVIDER_LINKS_PROVIDER FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE provider_link ADD CONSTRAINT FK_PROVIDER_LINKS_LINKED_PROVIDER FOREIGN KEY (linked_provider_id) REFERENCES provider (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE provider_link');
    }
}
