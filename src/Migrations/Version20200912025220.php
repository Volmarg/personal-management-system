<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200912025220 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    // todo: add migration sql to transfer goals to `todo`
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS module (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS my_todo (id INT AUTO_INCREMENT NOT NULL, module_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, completed TINYINT(1) NOT NULL, display_on_dashboard TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS my_todo_element (id INT AUTO_INCREMENT NOT NULL, my_todo_id INT NOT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, completed TINYINT(1) NOT NULL, INDEX IDX_ECBCC86E60E7101F (my_todo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_ECBCC86E60E7101F',
            'ALTER TABLE my_todo_element ADD CONSTRAINT FK_ECBCC86E60E7101F FOREIGN KEY (my_todo_id) REFERENCES my_todo (id)'
        ));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_9A299FF4AFC2B591',
            'ALTER TABLE my_todo ADD CONSTRAINT FK_9A299FF4AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)'
        ));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnDoesNotExist('todo_id', 'my_issue', 'ALTER TABLE my_issue ADD todo_id INT DEFAULT NULL'));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_7E6B91FAEA1EBC33',
            'ALTER TABLE my_issue ADD CONSTRAINT FK_7E6B91FAEA1EBC33 FOREIGN KEY (todo_id) REFERENCES my_todo (id)'
        ));

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_UNIQUE,
            'UNIQ_7E6B91FAEA1EBC33',
            'CREATE UNIQUE INDEX UNIQ_7E6B91FAEA1EBC33 ON my_issue (todo_id)'
        ));

        $this->connection->executeQuery('
            INSERT INTO module (`name`, `active`)
            VALUES("My Goals", true),
                  ("My Issues", true)   
        ');

        // move goals/subgoals to todo
        $this->connection->executeQuery("
            INSERT INTO my_todo(`module_id`, `name`, `description`, `deleted`, `completed`, `display_on_dashboard`)
            (
                SELECT
                m.id,
                g.name,
                g.description,
                g.deleted,
                g.completed,
                g.display_on_dashboard
                
                FROM my_goal g

                JOIN module m
                ON m.name = 'My Goals'
                
                GROUP BY g.id
            )
        ");

        $this->connection->executeQuery("
            INSERT INTO my_todo_element(`my_todo_id`, `name`, `deleted`, `completed`)
            (
                SELECT
                t.id,
                mgs.name,
                mgs.deleted,
                mgs.completed
                
                FROM my_goal g
                
                JOIN my_todo t
                ON t.name                   = g.name
                AND t.deleted               = g.deleted
                AND t.display_on_dashboard  = g.display_on_dashboard
                AND t.completed             = g.completed
                
                JOIN my_goal_subgoal mgs
                ON mgs.my_goal_id = g.id
                
                GROUP BY g.id, mgs.id
            )
        ");

        // drop no longer used tables - in this order
        $this->connection->executeQuery("DROP TABLE my_goal_subgoal");
        $this->connection->executeQuery("DROP TABLE my_goal");

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
    }
}
