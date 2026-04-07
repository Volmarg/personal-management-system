<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306091542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ADD my_payment_monthly_import_profile';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS my_payment_monthly_import_profile (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                date_field VARCHAR(255) DEFAULT NULL,
                money_field VARCHAR(255) DEFAULT NULL,
                description_field VARCHAR(255) DEFAULT NULL,
                currency_field VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE my_payment_monthly_import_profile');
    }
}
