<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191102073301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS my_recurring_payment_monthly (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, date DATE DEFAULT NULL, money DOUBLE PRECISION NOT NULL, description VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, hash VARCHAR(255) NOT NULL, INDEX IDX_4FC64C63C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_4FC64C63C54C8C93',
            'ALTER TABLE my_recurring_payment_monthly ADD CONSTRAINT FK_4FC64C63C54C8C93 FOREIGN KEY (type_id) REFERENCES my_payments_settings (id)'
        ));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('DROP TABLE my_recurring_payment_monthly');
    }
}
