<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206111443 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        // required due to issue with executing queries for the same table
        return false;
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnDoesNotExist('day_of_month', 'my_recurring_payment_monthly', '
            ALTER TABLE my_recurring_payment_monthly
            ADD COLUMN day_of_month INT
        '));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnExist('date', 'my_recurring_payment_monthly', '
            UPDATE my_recurring_payment_monthly
            SET day_of_month = DATE_FORMAT(`date`, "%d");
        '));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnExist('date', 'my_recurring_payment_monthly', '
            ALTER TABLE my_recurring_payment_monthly
            DROP COLUMN `date`
        '));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnExist('hash', 'my_recurring_payment_monthly', '
            ALTER TABLE my_recurring_payment_monthly
            DROP COLUMN hash
        '));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
