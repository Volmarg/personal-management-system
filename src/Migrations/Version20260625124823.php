<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625124823 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add doctor, doctor_appointment, illness tables';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE doctor (id INT AUTO_INCREMENT NOT NULL, specialisation VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, information LONGTEXT DEFAULT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX unique_record (specialisation, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doctor_appointment (id INT AUTO_INCREMENT NOT NULL, doctor_id INT DEFAULT NULL, illness_id INT DEFAULT NULL, date DATETIME NOT NULL, information LONGTEXT DEFAULT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_79074CBF87F4FB17 (doctor_id), INDEX IDX_79074CBF2E23A096 (illness_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE illness (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, information LONGTEXT DEFAULT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX unique_record (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doctor_appointment ADD CONSTRAINT FK_79074CBF87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id)');
        $this->addSql('ALTER TABLE doctor_appointment ADD CONSTRAINT FK_79074CBF2E23A096 FOREIGN KEY (illness_id) REFERENCES illness (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE doctor_appointment DROP FOREIGN KEY FK_79074CBF87F4FB17');
        $this->addSql('ALTER TABLE doctor_appointment DROP FOREIGN KEY FK_79074CBF2E23A096');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('DROP TABLE doctor_appointment');
        $this->addSql('DROP TABLE illness');
    }
}
