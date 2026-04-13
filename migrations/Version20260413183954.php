<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413183954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voucher ADD created_by_provider_user_id UUID NOT NULL');
        $this->addSql('ALTER TABLE voucher DROP issued_to_user_id');
        $this->addSql('ALTER TABLE voucher ALTER issued_to_email SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voucher ADD issued_to_user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE voucher DROP created_by_provider_user_id');
        $this->addSql('ALTER TABLE voucher ALTER issued_to_email DROP NOT NULL');
    }
}
