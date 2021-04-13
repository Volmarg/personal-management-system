<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210325042731 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add my_schedule_reminder';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('
            CREATE TABLE IF NOT EXISTS my_schedule_reminder (
                id INT AUTO_INCREMENT NOT NULL, 
                schedule_id INT NOT NULL, 
                deleted TINYINT(1) NOT NULL, 
                date DATETIME NOT NULL, 
                INDEX IDX_676F8E0DA40BC2D5 (schedule_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraint(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_676F8E0DA40BC2D5',
            'ALTER TABLE my_schedule_reminder ADD CONSTRAINT FK_676F8E0DA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES my_schedule (id)',
            Migrations::CHECK_TYPE_IF_NOT_EXIST
        ));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraint(
            Migrations::CONSTRAINT_TYPE_UNIQUE,
            'UNIQ_676F8E0DAA9E377A',
            'CREATE UNIQUE INDEX UNIQ_676F8E0DAA9E377A ON my_schedule_reminder (date)',
            Migrations::CHECK_TYPE_IF_NOT_EXIST
        ));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        //no going back
    }
}
