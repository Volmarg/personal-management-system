<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610123903 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'ADD storage_file_2_module';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS `storage_file_2_module` (
                id INT AUTO_INCREMENT NOT NULL,
                related_module_id INT NOT NULL,
                storage_file_id INT NOT NULL,
                related_module_class VARCHAR(255) NOT NULL,
                created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                UNIQUE INDEX unique_record (
                    related_module_id,
                    related_module_class,
                    storage_file_id
                ),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE storage_file_2_module 
            ADD CONSTRAINT FK_20260610123903_storage_file
            FOREIGN KEY IF NOT EXISTS (storage_file_id)
            REFERENCES storage_file (id)
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE storage_file_2_module DROP FOREIGN KEY IF EXISTS `FK_20260610123903_storage_file`');
        $this->addSql('DROP TABLE IF EXISTS `storage_file_2_module`');
    }
}
