<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210402064914 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnDoesNotExist("processed", "my_schedule_reminder", "
            ALTER TABLE my_schedule_reminder ADD processed TINYINT(1) NOT NULL
        "));

        // remove previous unique on date
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraint(
            Migrations::CONSTRAINT_TYPE_UNIQUE,
            'UNIQ_676F8E0DAA9E377A',
            'ALTER TABLE my_schedule_reminder DROP CONSTRAINT UNIQ_676F8E0DAA9E377A',
            Migrations::CHECK_TYPE_IF_EXIST
        ));

        // add new unique on date + schedule
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraint(
            Migrations::CONSTRAINT_TYPE_UNIQUE,
            'unique_reminder',
            'CREATE UNIQUE INDEX unique_reminder ON my_schedule_reminder (schedule_id, date)',
            Migrations::CHECK_TYPE_IF_NOT_EXIST
        ));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
