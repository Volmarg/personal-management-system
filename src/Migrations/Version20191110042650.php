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

        $this->addSql('CREATE TABLE IF NOT EXISTS my_contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, contacts LONGTEXT NOT NULL, deleted TINYINT(1) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, name_background_color VARCHAR(255) NOT NULL, description_background_color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_contact_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image_path VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE IF EXISTS my_contacts');
        $this->addSql('DROP TABLE IF EXISTS my_contacts_groups');

        $this->addSql('CREATE TABLE IF NOT EXISTS my_contact_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_929F246B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DCD489F05E237E06 ON my_contact_type (name)');
        $this->addSql('ALTER TABLE my_contact ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE my_contact ADD CONSTRAINT FK_C69B4A14647145D0 FOREIGN KEY (group_id) REFERENCES my_contact_group (id)');
        $this->addSql('CREATE INDEX IDX_C69B4A14647145D0 ON my_contact (group_id)');

        $this->addSql("
            INSERT INTO `my_contact_type` (`id`, `name`, `image_path`, `deleted`) VALUES
            (NULL,	'Discord',	'upload/images/system/contactIcons/discord.png',	0),
            (NULL,	'Steam',	'upload/images/system/contactIcons/steam.png',	0),
            (NULL,	'Facebook',	'upload/images/system/contactIcons/facebook.png',	0),
            (NULL,	'Linkedin',	'upload/images/system/contactIcons/linkedin.png',	0),
            (NULL,	'Endomondo',	'upload/images/system/contactIcons/endomondo.png',	0),
            (NULL,	'Github',	'upload/images/system/contactIcons/github.png',	0),
            (NULL,	'Instagram',	'upload/images/system/contactIcons/instagram.png',	0),
            (NULL,	'Location',	'upload/images/system/contactIcons/location.png',	0),
            (NULL,	'Email',	'upload/images/system/contactIcons/mail.png',	0),
            (NULL,	'Mobile',	'upload/images/system/contactIcons/mobile.png',	0),
            (NULL,	'Phone',	'upload/images/system/contactIcons/phone.png',	0),
            (NULL,	'Reddit',	'upload/images/system/contactIcons/reddit.png',	0),
            (NULL,	'Skype',	'upload/images/system/contactIcons/skype.png',	0),
            (NULL,	'Spotify',	'upload/images/system/contactIcons/spotify.png',	0),
            (NULL,	'Twitter',	'upload/images/system/contactIcons/twitter.png',	0),
            (NULL,	'Viber',	'upload/images/system/contactIcons/viber.png',	0),
            (NULL,	'Website',	'upload/images/system/contactIcons/website.png',	0),
            (NULL,	'WhatsApp',	'upload/images/system/contactIcons/whatsapp.png',	0);
        ");

        $this->addSql("
            INSERT INTO `my_contact_group` (`id`, `name`, `icon`, `color`, `deleted`) VALUES
            (NULL,	'Medic',	'far fa-medkit',	'399e05',	0),
            (NULL,	'Work',	'far fa-suitcase',	'be5e05',	0),
            (NULL,	'Service',	'far fa-cog',	'3f3b3b',	0),
            (NULL,	'Family',	'far fa-home',	'276ad7',	0),
            (NULL,	'Friend',	'far fa-male',	'cd2ecc',	0),
            (NULL,	'Vip',	'far fa-star',	'ffd000',	0),
            (NULL,	'Game fellow',	'far fa-gamepad',	'f23e4e',	0),
            (NULL,	'Archived',	'far fa-times-circle',	'fb5705',	0);
        ");
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
