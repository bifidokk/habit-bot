<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210508100631 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE habit ADD next_remind_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP creation_state');
        $this->addSql('CREATE INDEX IDX_44FE2172FC6FD890 ON habit (next_remind_at)');
        $this->addSql('ALTER TABLE user DROP state');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_44FE2172FC6FD890 ON habit');
        $this->addSql('ALTER TABLE habit ADD creation_state VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:creation_habit_state)\', DROP next_remind_at');
        $this->addSql('ALTER TABLE user ADD state VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT \'start\' NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:user_state)\'');
    }
}
