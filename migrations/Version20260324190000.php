<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase language_code column length to support locale codes like pt-br';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER COLUMN language_code TYPE VARCHAR(10)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER COLUMN language_code TYPE VARCHAR(3)
        SQL);
    }
}