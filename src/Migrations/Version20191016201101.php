<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191016201101 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS my_payments_bills_items (id INT AUTO_INCREMENT NOT NULL, bill_id INT NOT NULL, amount INT NOT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_32A224731A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_payments_bills (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, name VARCHAR(255) NOT NULL, information VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, planned_amount INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE my_payments_bills_items ADD CONSTRAINT FK_32A224731A8C12F5 FOREIGN KEY (bill_id) REFERENCES my_payments_bills (id)');
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
