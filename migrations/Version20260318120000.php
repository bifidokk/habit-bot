<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add show_animations field to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD show_animations BOOLEAN DEFAULT true NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP show_animations
        SQL);
    }
}
