<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629124206 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Make health entries soft-deletable';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE doctor ADD deleted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE doctor_appointment ADD deleted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE illness ADD deleted TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE doctor DROP deleted');
        $this->addSql('ALTER TABLE doctor_appointment DROP deleted');
        $this->addSql('ALTER TABLE illness DROP deleted');
    }
}
