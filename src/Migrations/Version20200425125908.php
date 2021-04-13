<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200425125908 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS my_issue_contact (id INT AUTO_INCREMENT NOT NULL, my_issue_id INT DEFAULT NULL, information VARCHAR(255) DEFAULT NULL, icon TINYTEXT DEFAULT NULL, date DATETIME NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_3BEFD3786C2830C3 (my_issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_issue (id INT AUTO_INCREMENT NOT NULL, deleted TINYINT(1) NOT NULL, show_on_dashboard TINYINT(1) NOT NULL, resolved TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, information VARCHAR(250) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_issue_progress (id INT AUTO_INCREMENT NOT NULL, my_issue_id INT DEFAULT NULL, information TEXT DEFAULT NULL, date DATETIME NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_54AA3EE36C2830C3 (my_issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE my_issue_contact ADD CONSTRAINT FK_3BEFD3786C2830C3 FOREIGN KEY (my_issue_id) REFERENCES my_issue (id)');
        $this->addSql('ALTER TABLE my_issue_progress ADD CONSTRAINT FK_54AA3EE36C2830C3 FOREIGN KEY (my_issue_id) REFERENCES my_issue (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
