<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410212346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Drop existing constraint if it exists
        $this->addSql("ALTER TABLE offer_travel DROP FOREIGN KEY FK_F45E1C6F139DF194");
        
        // Add new constraint with a different name
        $this->addSql("ALTER TABLE offer_travel ADD CONSTRAINT FK_OFFER_TRAVEL_PROMOTION FOREIGN KEY (promotion_id) REFERENCES promotion (id)");
    }
    
    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE offer_travel DROP FOREIGN KEY FK_OFFER_TRAVEL_PROMOTION");
    }
}
