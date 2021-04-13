<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191103042122 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS setting (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE my_job_afterhours CHANGE type type ENUM(\'spent\', \'made\')');
        $this->addSql('ALTER TABLE achievement CHANGE type type ENUM(\'simple\', \'medium\', \'hard\', \'hardcore\')');
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
