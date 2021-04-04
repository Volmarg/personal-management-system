<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Exception;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191215120422 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws Exception
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_travel_idea','ALTER TABLE `my_travels_ideas` RENAME TO `my_travel_idea`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_shopping_plan','ALTER TABLE `my_shopping_plans` RENAME TO `my_shopping_plan`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_setting','ALTER TABLE `my_payments_settings` RENAME TO `my_payment_setting`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_product','ALTER TABLE `my_payments_product` RENAME TO `my_payment_product`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_owed','ALTER TABLE `my_payments_owed` RENAME TO `my_payment_owed`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_monthly','ALTER TABLE `my_payments_monthly` RENAME TO `my_payment_monthly`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_bill_item','ALTER TABLE `my_payments_bills_items` RENAME TO `my_payment_bill_item`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_payment_bill','ALTER TABLE `my_payments_bills` RENAME TO `my_payment_bill`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_password','ALTER TABLE `my_passwords` RENAME TO `my_password`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_password_group','ALTER TABLE `my_passwords_groups` RENAME TO `my_password_group`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_note','ALTER TABLE `my_notes` RENAME TO `my_note`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_note_category','ALTER TABLE `my_notes_categories` RENAME TO `my_note_category`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_job_setting','ALTER TABLE `my_job_settings` RENAME TO `my_job_setting`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_job_holiday_pool','ALTER TABLE `my_job_holidays_pool` RENAME TO `my_job_holiday_pool`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_job_holiday','ALTER TABLE `my_job_holidays` RENAME TO `my_job_holiday`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_job_afterhour','ALTER TABLE `my_job_afterhours` RENAME TO `my_job_afterhour`'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_goal_subgoal','ALTER TABLE `my_goals_subgoals` RENAME TO `my_goal_subgoal`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_goal_payment','ALTER TABLE `my_goals_payments` RENAME TO `my_goal_payment`'));
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfTableNotExist('my_goal','ALTER TABLE `my_goals` RENAME TO `my_goal`'));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
