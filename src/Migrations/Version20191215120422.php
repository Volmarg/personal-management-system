<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        $this->addSql('ALTER TABLE `my_travels_ideas` RENAME TO `my_travel_idea`');
        $this->addSql('ALTER TABLE `my_shopping_plans` RENAME TO `my_shopping_plan`');
        $this->addSql('ALTER TABLE `my_payments_settings` RENAME TO `my_payment_setting`');
        $this->addSql('ALTER TABLE `my_payments_product` RENAME TO `my_payment_product`');
        $this->addSql('ALTER TABLE `my_payments_owed` RENAME TO `my_payment_owed`');
        $this->addSql('ALTER TABLE `my_payments_monthly` RENAME TO `my_payment_monthly`');
        $this->addSql('ALTER TABLE `my_payments_bills_items` RENAME TO `my_payment_bill_item`');
        $this->addSql('ALTER TABLE `my_payments_bills` RENAME TO `my_payment_bill`');
        $this->addSql('ALTER TABLE `my_passwords` RENAME TO `my_password`');
        $this->addSql('ALTER TABLE `my_passwords_groups` RENAME TO `my_password_group`');
        $this->addSql('ALTER TABLE `my_notes` RENAME TO `my_note`');
        $this->addSql('ALTER TABLE `my_notes_categories` RENAME TO `my_note_category`');
        $this->addSql('ALTER TABLE `my_job_settings` RENAME TO `my_job_setting`');
        $this->addSql('ALTER TABLE `my_job_holidays_pool` RENAME TO `my_job_holiday_pool`');
        $this->addSql('ALTER TABLE `my_job_holidays` RENAME TO `my_job_holiday`');
        $this->addSql('ALTER TABLE `my_job_afterhours` RENAME TO `my_job_afterhour`');
        $this->addSql('ALTER TABLE `my_goals_subgoals` RENAME TO `my_goal_subgoal`');
        $this->addSql('ALTER TABLE `my_goals_payments` RENAME TO `my_goal_payment`');
        $this->addSql('ALTER TABLE `my_goals` RENAME TO `my_goal`');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
