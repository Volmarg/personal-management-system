<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190717144649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS my_travels_ideas (id INT AUTO_INCREMENT NOT NULL, location VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, map LONGTEXT DEFAULT NULL, category LONGTEXT DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_job_afterhours (id INT AUTO_INCREMENT NOT NULL, date VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, minutes INT NOT NULL, type ENUM(\'spent\', \'made\'), goal VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_shopping_plans (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, information VARCHAR(255) DEFAULT NULL, example VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_contacts_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_contacts (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, contact VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_112803CFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_goals_subgoals (id INT AUTO_INCREMENT NOT NULL, my_goal_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, completed TINYINT(1) NOT NULL, INDEX IDX_31320F73EC84B6D2 (my_goal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_goals (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, completed TINYINT(1) NOT NULL, display_on_dashboard TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_goals_payments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, deadline VARCHAR(255) DEFAULT NULL, money_goal INT NOT NULL, money_collected INT NOT NULL, deleted TINYINT(1) NOT NULL, collection_start_date VARCHAR(255) NOT NULL, display_on_dashboard TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_notes_categories (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, color VARCHAR(255) NOT NULL, parent_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_notes (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_6DDD144812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_car (id INT AUTO_INCREMENT NOT NULL, schedule_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date VARCHAR(255) DEFAULT NULL, information VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_EA781FE24826A022 (schedule_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_car_schedules_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_passwords_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_passwords (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_BD32582FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS achievement (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, type ENUM(\'simple\', \'medium\', \'hard\', \'hardcore\'), deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_payments_monthly (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, date VARCHAR(255) DEFAULT NULL, money DOUBLE PRECISION NOT NULL, description VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_6D04DFFAC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_payments_settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS my_payments_product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, market VARCHAR(255) DEFAULT NULL, products VARCHAR(255) DEFAULT NULL, information VARCHAR(255) DEFAULT NULL, rejected TINYINT(1) DEFAULT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS app_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', avatar VARCHAR(255) DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_88BDF3E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_88BDF3E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_88BDF3E9C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE my_contacts ADD CONSTRAINT FK_112803CFE54D947 FOREIGN KEY (group_id) REFERENCES my_contacts_groups (id)');
        $this->addSql('ALTER TABLE my_goals_subgoals ADD CONSTRAINT FK_31320F73EC84B6D2 FOREIGN KEY (my_goal_id) REFERENCES my_goals (id)');
        $this->addSql('ALTER TABLE my_notes ADD CONSTRAINT FK_6DDD144812469DE2 FOREIGN KEY (category_id) REFERENCES my_notes_categories (id)');
        $this->addSql('ALTER TABLE my_car ADD CONSTRAINT FK_EA781FE24826A022 FOREIGN KEY (schedule_type_id) REFERENCES my_car_schedules_types (id)');
        $this->addSql('ALTER TABLE my_passwords ADD CONSTRAINT FK_BD32582FE54D947 FOREIGN KEY (group_id) REFERENCES my_passwords_groups (id)');
        $this->addSql('ALTER TABLE my_payments_monthly ADD CONSTRAINT FK_6D04DFFAC54C8C93 FOREIGN KEY (type_id) REFERENCES my_payments_settings (id)');
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
