<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191110042650 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE my_contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, contacts VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, name_background_color VARCHAR(255) NOT NULL, description_background_color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_contact_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image_path VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE my_contacts');
        $this->addSql('DROP TABLE my_contact_groups');

        $this->addSql('CREATE TABLE my_contact_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_929F246B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DCD489F05E237E06 ON my_contact_type (name)');

        $this->addSql('ALTER TABLE my_contact ADD group_id INT NOT NULL');
        $this->addSql('ALTER TABLE my_contact ADD CONSTRAINT FK_C69B4A14647145D0 FOREIGN KEY (group_id) REFERENCES my_contact_group (id)');
        $this->addSql('CREATE INDEX IDX_C69B4A14647145D0 ON my_contact (group_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE my_contact');
        $this->addSql('DROP TABLE my_contact_type');
    }
}
