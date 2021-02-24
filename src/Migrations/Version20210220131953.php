<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210220131953 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // todo: replace the old my_schedule with this below + rename that one below
        $this->addSql('
            CREATE TABLE schedule (
                id INT AUTO_INCREMENT NOT NULL, 
                calendar_id INT NOT NULL, 
                title VARCHAR(100) NOT NULL, 
                all_day TINYINT(1) NOT NULL, 
                start DATETIME NOT NULL, 
                end DATETIME NOT NULL, 
                category VARCHAR(50) NOT NULL, 
                location VARCHAR(255) NOT NULL, 
                deleted TINYINT(1) NOT NULL DEFAULT 0,
                INDEX IDX_5A3811FBA40A2C8 (calendar_id), PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE my_schedule_calendar (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(100) NOT NULL, 
                color VARCHAR(100) NOT NULL, 
                background_color VARCHAR(100) NOT NULL, 
                drag_background_color VARCHAR(100) NOT NULL, 
                border_color VARCHAR(100) NOT NULL, 
                deleted TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBA40A2C8 FOREIGN KEY (calendar_id) REFERENCES my_schedule_calendar (id)
        ');

        // transform my_schedule_type into `my_schedule_calendar`
        $this->addSql("
            INSERT INTO my_schedule_calendar
            (
                SELECT
                NULL    AS id,
                name    AS name,
                CONCAT('#',LPAD(CONV(ROUND(RAND(md5(name)) * 16777215),10,16),6,0)) AS color,
                CONCAT('#',LPAD(CONV(ROUND(RAND(md5(name)) * 16777215),10,16),6,0)) AS background_color,
                CONCAT('#',LPAD(CONV(ROUND(RAND(md5(name)) * 16777215),10,16),6,0)) AS drag_background_color,
                CONCAT('#',LPAD(CONV(ROUND(RAND(md5(name)) * 16777215),10,16),6,0)) AS border_color,
                deleted AS deleted
                
                FROM my_schedule_type
            )
        ");

        // transform old `my_schedule` into new `my_schedule` with calendar usage
        $this->addSql("
            INSERT INTO schedule
            (
                SELECT 
                NULL    AS id,
                msc.id  AS calendar_id,
                ms.name AS title,
                0       AS all_day,
                CONCAT(ms.`date`, ' 06:00:00') AS `start`,
                CONCAT(ms.`date`, ' 10:00:00') AS `end`,
                'time'     AS category,
                ''         AS location,
                ms.deleted AS deleted
                
                FROM my_schedule ms

                -- currently related types
                JOIN my_schedule_type mst
                ON mst.id = ms.schedule_type_id
                
                -- now join by names to new calendar table since the id changed now
                JOIN my_schedule_calendar msc
                ON msc.name = mst.name 
            )           
        ");

        // drop old schedule

        // rename new schedule table name to old schedule table name
    }

    public function down(Schema $schema) : void
    {
        // no going back
    }
}
