<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417105047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE agency ADD user_id INT NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agency ADD CONSTRAINT FK_70C0C6E6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_70C0C6E6A76ED395 ON agency (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_OFFER_TRAVEL_PROMOTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_OFFER_TRAVEL_AGENCY
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_OFFER_TRAVEL_PROMOTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel CHANGE agency_id agency_id INT DEFAULT NULL, CHANGE hotel_name hotel_name VARCHAR(255) NOT NULL, CHANGE discription discription LONGTEXT NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_F45E1C6F139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_offer_travel_agency ON offer_travel
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F45E1C6FCDEADB2A ON offer_travel (agency_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_offer_travel_promotion ON offer_travel
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F45E1C6F139DF194 ON offer_travel (promotion_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_OFFER_TRAVEL_AGENCY FOREIGN KEY (agency_id) REFERENCES agency (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_OFFER_TRAVEL_PROMOTION FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE agency DROP FOREIGN KEY FK_70C0C6E6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_70C0C6E6A76ED395 ON agency
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agency DROP user_id, CHANGE image image VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_F45E1C6F139DF194
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_F45E1C6FCDEADB2A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel DROP FOREIGN KEY FK_F45E1C6F139DF194
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel CHANGE agency_id agency_id INT NOT NULL, CHANGE hotel_name hotel_name VARCHAR(50) NOT NULL, CHANGE discription discription VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_OFFER_TRAVEL_PROMOTION FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_f45e1c6fcdeadb2a ON offer_travel
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_OFFER_TRAVEL_AGENCY ON offer_travel (agency_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_f45e1c6f139df194 ON offer_travel
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_OFFER_TRAVEL_PROMOTION ON offer_travel (promotion_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_F45E1C6FCDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offer_travel ADD CONSTRAINT FK_F45E1C6F139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)
        SQL);
    }
}
