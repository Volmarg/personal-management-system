<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115052559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS module_data (
                id INT AUTO_INCREMENT NOT NULL, 
                record_type VARCHAR(50) NOT NULL, 
                module VARCHAR(75) NOT NULL, 
                record_identifier VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                INDEX module_data_index (
                 id, 
                 record_type, 
                 module, 
                 record_identifier
                ), 
                UNIQUE INDEX unique_record (
                  record_type, 
                  module, 
                  record_identifier
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // fix columns lengths
        $this->addSql('ALTER TABLE my_issue_contact CHANGE information information LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE my_issue_progress CHANGE information information LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
