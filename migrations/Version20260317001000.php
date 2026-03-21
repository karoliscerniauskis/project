<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE provider_invitation (
            id UUID NOT NULL,
            provider_id UUID NOT NULL,
            email VARCHAR(255) NOT NULL,
            role VARCHAR(32) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            status VARCHAR(32) NOT NULL,
            invited_by_user_id UUID NOT NULL,
            accepted_user_id UUID DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX uniq_provider_invitation_slug ON provider_invitation (slug)');
        $this->addSql('CREATE INDEX idx_provider_invitation_provider_id ON provider_invitation (provider_id)');
        $this->addSql('CREATE INDEX idx_provider_invitation_email ON provider_invitation (email)');
        $this->addSql('CREATE INDEX idx_provider_invitation_status ON provider_invitation (status)');
        $this->addSql('CREATE INDEX idx_provider_invitation_invited_by_user_id ON provider_invitation (invited_by_user_id)');
        $this->addSql('CREATE INDEX idx_provider_invitation_accepted_user_id ON provider_invitation (accepted_user_id)');

        $this->addSql('ALTER TABLE provider_invitation ADD CONSTRAINT fk_provider_invitation_provider_id FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE provider_invitation ADD CONSTRAINT fk_provider_invitation_invited_by_user_id FOREIGN KEY (invited_by_user_id) REFERENCES auth_user (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE provider_invitation ADD CONSTRAINT fk_provider_invitation_accepted_user_id FOREIGN KEY (accepted_user_id) REFERENCES auth_user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE provider_invitation DROP CONSTRAINT fk_provider_invitation_accepted_user_id');
        $this->addSql('ALTER TABLE provider_invitation DROP CONSTRAINT fk_provider_invitation_invited_by_user_id');
        $this->addSql('ALTER TABLE provider_invitation DROP CONSTRAINT fk_provider_invitation_provider_id');

        $this->addSql('DROP INDEX uniq_provider_invitation_slug');
        $this->addSql('DROP INDEX idx_provider_invitation_provider_id');
        $this->addSql('DROP INDEX idx_provider_invitation_email');
        $this->addSql('DROP INDEX idx_provider_invitation_status');
        $this->addSql('DROP INDEX idx_provider_invitation_invited_by_user_id');
        $this->addSql('DROP INDEX idx_provider_invitation_accepted_user_id');

        $this->addSql('DROP TABLE provider_invitation');
    }
}
