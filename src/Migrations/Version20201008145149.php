<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201008145149 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        /**
         * According the migration: @see Version20191215120422 - tables below should no longer exist (may still be in DB
         * if schema create was executed and then migrations, since migrations followed step by step rename this tables)
         */
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_travels_ideas', 'DROP TABLE IF EXISTS my_travels_ideas'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_shopping_plans', 'DROP TABLE IF EXISTS my_shopping_plans'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_settings', 'DROP TABLE IF EXISTS my_payments_settings'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_product', 'DROP TABLE IF EXISTS my_payments_product'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_owed', 'DROP TABLE IF EXISTS my_payments_owed'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_monthly', 'DROP TABLE IF EXISTS my_payments_monthly'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_bills_items', 'DROP TABLE IF EXISTS my_payments_bills_items'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_payments_bills', 'DROP TABLE IF EXISTS my_payments_bills'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_passwords', 'DROP TABLE IF EXISTS my_passwords'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_passwords_groups', 'DROP TABLE IF EXISTS my_passwords_groups'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_notes', 'DROP TABLE IF EXISTS my_notes'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_notes_categories', 'DROP TABLE IF EXISTS my_notes_categories'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_job_settings', 'DROP TABLE IF EXISTS my_job_settings'));;
        $this->addSql(Migrations::buildSqlExecutedIfTableIsEmpty('my_job_holidays_pool', 'DROP TABLE IF EXISTS my_job_holidays_pool'));;
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
