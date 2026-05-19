<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519121032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add storage_file table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS `storage_file` (
                `id` INT AUTO_INCREMENT NOT NULL, 
                `file_path` LONGTEXT NOT NULL, 
                `module_name` VARCHAR(75) NOT NULL, 
                PRIMARY KEY(`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "IF" EXISTS "storage_file"');
    }
}
