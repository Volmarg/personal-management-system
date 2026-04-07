<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307081421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE my_payment_monthly_import_filter_rule';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS my_payment_monthly_import_filter_rule (
                id INT AUTO_INCREMENT NOT NULL, 
                field_name VARCHAR(255) NOT NULL, 
                rule VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        $this->addSql("
            ALTER TABLE my_payment_monthly_import_filter_rule
            ADD COLUMN IF NOT EXISTS import_profile_id INT DEFAULT NULL
        ");

        $this->addSql('
            ALTER TABLE my_payment_monthly_import_filter_rule 
            ADD CONSTRAINT FK_202603_profile
            FOREIGN KEY IF NOT EXISTS (import_profile_id)
            REFERENCES my_payment_monthly_import_profile (id)
        ');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE IF EXISTS my_payment_monthly_import_filter_rule');
    }
}
