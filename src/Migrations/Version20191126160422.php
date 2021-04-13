<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191126160422 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE my_payments_owed CHANGE amount amount DOUBLE PRECISION NOT NULL');
        $this->addSql('
            UPDATE my_contact_type mct
            SET mct.image_path = CONCAT("/", mct.image_path)
            
            WHERE 1
            AND mct.image_path LIKE "upload/images/system/contactIcons/%"
        ');

        $this->addSql('
            UPDATE my_contact mc
            SET mc.contacts = REPLACE(mc.contacts, \'"icon_path":"upload\\/images\\/system\\/contactIcons\\/\', \'"icon_path":"\/upload\/images\/system\/contactIcons\/\')
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
