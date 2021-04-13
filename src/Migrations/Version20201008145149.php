<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        $this->addSql('DROP TABLE IF EXISTS my_travels_ideas');;
        $this->addSql('DROP TABLE IF EXISTS my_shopping_plans');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_settings');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_product');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_owed');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_monthly');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_bills_items');;
        $this->addSql('DROP TABLE IF EXISTS my_payments_bills');;
        $this->addSql('DROP TABLE IF EXISTS my_passwords');;
        $this->addSql('DROP TABLE IF EXISTS my_passwords_groups');;
        $this->addSql('DROP TABLE IF EXISTS my_notes');;
        $this->addSql('DROP TABLE IF EXISTS my_notes_categories');;
        $this->addSql('DROP TABLE IF EXISTS my_job_settings');;
        $this->addSql('DROP TABLE IF EXISTS my_job_holidays_pool');;
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
