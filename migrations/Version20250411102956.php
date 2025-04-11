<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411102956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets CHANGE departure_date departure_date DATE DEFAULT NULL, CHANGE departure_time departure_time VARCHAR(8) NOT NULL, CHANGE arrival_date arrival_date DATE DEFAULT NULL, CHANGE arrival_time arrival_time VARCHAR(8) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire ADD CONSTRAINT FK_1B5D30753243BB18 FOREIGN KEY (hotel_id) REFERENCES hotels (id_hotel)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets CHANGE departure_date departure_date DATE NOT NULL, CHANGE departure_time departure_time VARCHAR(255) NOT NULL, CHANGE arrival_date arrival_date DATE NOT NULL, CHANGE arrival_time arrival_time VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire DROP FOREIGN KEY FK_1B5D30753243BB18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire CHANGE id id INT NOT NULL
        SQL);
    }
}
