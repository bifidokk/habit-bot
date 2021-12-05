<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205142414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE habit (id UUID NOT NULL, user_id UUID DEFAULT NULL, description VARCHAR(255) NOT NULL, state VARCHAR(32) DEFAULT \'draft\' NOT NULL, remind_week_days SMALLINT NOT NULL, remind_at TIME(0) WITHOUT TIME ZONE DEFAULT NULL, next_remind_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_44FE2172A76ED395 ON habit (user_id)');
        $this->addSql('CREATE INDEX IDX_44FE2172FC6FD890 ON habit (next_remind_at)');
        $this->addSql('COMMENT ON COLUMN habit.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN habit.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN habit.state IS \'(DC2Type:habit_state)\'');
        $this->addSql('COMMENT ON COLUMN habit.remind_at IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN habit.next_remind_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN habit.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, username VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, telegram_id INT NOT NULL, language_code VARCHAR(3) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, timezone VARCHAR(8) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649CC0B3066 ON "user" (telegram_id)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE habit ADD CONSTRAINT FK_44FE2172A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE habit DROP CONSTRAINT FK_44FE2172A76ED395');
        $this->addSql('DROP TABLE habit');
        $this->addSql('DROP TABLE "user"');
    }
}
