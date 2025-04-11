<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410214119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction des relations entre offer_travel, agency et promotion';
    }

    public function up(Schema $schema): void
{
    // Étape 1: Nettoyer les données avant d'ajouter la contrainte
    $this->addSql("UPDATE offer_travel SET promotion_id = NULL WHERE promotion_id IS NOT NULL AND promotion_id NOT IN (SELECT id FROM promotion)");
    
    // Étape 2: Ajouter la contrainte
    $this->addSql("ALTER TABLE offer_travel 
        ADD CONSTRAINT FK_OFFER_TRAVEL_PROMOTION FOREIGN KEY (promotion_id) 
        REFERENCES promotion (id) ON DELETE SET NULL");
}

    public function down(Schema $schema): void
    {
        
    // Étape 1: Nettoyer les données avant d'ajouter la contrainte
    $this->addSql("UPDATE offer_travel SET promotion_id = NULL WHERE promotion_id IS NOT NULL AND promotion_id NOT IN (SELECT id FROM promotion)");
    
    // Étape 2: Ajouter la contrainte
    $this->addSql("ALTER TABLE offer_travel 
        ADD CONSTRAINT FK_OFFER_TRAVEL_PROMOTION FOREIGN KEY (promotion_id) 
        REFERENCES promotion (id) ON DELETE SET NULL");
    }
}