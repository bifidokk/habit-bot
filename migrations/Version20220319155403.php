<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220319155403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE metrics (id UUID NOT NULL, habit_id UUID DEFAULT NULL, type VARCHAR(32) NOT NULL, metric_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_228AAAE7E7AEB3B2 ON metrics (habit_id)');
        $this->addSql('COMMENT ON COLUMN metrics.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN metrics.habit_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN metrics.type IS \'(DC2Type:metric_type)\'');
        $this->addSql('COMMENT ON COLUMN metrics.metric_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN metrics.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE metrics ADD CONSTRAINT FK_228AAAE7E7AEB3B2 FOREIGN KEY (habit_id) REFERENCES habit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE metrics');
    }
}
