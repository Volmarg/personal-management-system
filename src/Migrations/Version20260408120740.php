<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408120740 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Add description to filter rules';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql("
            ALTER TABLE my_payment_monthly_import_filter_rule
            ADD COLUMN IF NOT EXISTS description VARCHAR(255) DEFAULT NULL
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql("
            ALTER TABLE my_payment_monthly_import_filter_rule
            DROP COLUMN IF EXISTS description
        ");
    }
}
