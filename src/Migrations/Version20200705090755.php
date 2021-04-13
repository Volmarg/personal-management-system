<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200705090755 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            ALTER TABLE `my_job_holiday_pool`
            CHANGE `days_left` `days_in_pool` varchar(255) COLLATE "utf8mb4_unicode_ci" NOT NULL AFTER `year`
        ');
    }

    public function down(Schema $schema) : void
    {
        //nothing
    }
}
