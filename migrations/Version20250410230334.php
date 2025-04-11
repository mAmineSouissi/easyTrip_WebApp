<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410230334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE hotels CHANGE id_hotel id_hotel INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire DROP FOREIGN KEY FK_1B5D30753243BB18
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
            ALTER TABLE hotels CHANGE id_hotel id_hotel INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire DROP FOREIGN KEY FK_1B5D30753243BB18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire ADD CONSTRAINT FK_1B5D30753243BB18 FOREIGN KEY (hotel_id) REFERENCES hotels (id_hotel) ON DELETE CASCADE
        SQL);
    }
}
