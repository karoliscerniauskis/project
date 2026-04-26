<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426140512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification (id UUID NOT NULL, user_id UUID NOT NULL, type VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, payload JSON NOT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification');
    }
}
