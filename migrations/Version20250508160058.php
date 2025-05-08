<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508160058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cars ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, DROP availability, CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport ADD created_at DATETIME NOT NULL, ADD total_price DOUBLE PRECISION NOT NULL, ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport ADD CONSTRAINT FK_CA5048E2A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport ADD CONSTRAINT FK_CA5048E2C3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CA5048E2A76ED395 ON res_transport (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CA5048E2C3C6F69F ON res_transport (car_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cars ADD availability VARCHAR(255) NOT NULL, DROP latitude, DROP longitude, CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport DROP FOREIGN KEY FK_CA5048E2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport DROP FOREIGN KEY FK_CA5048E2C3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CA5048E2A76ED395 ON res_transport
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CA5048E2C3C6F69F ON res_transport
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE res_transport DROP created_at, DROP total_price, DROP latitude, DROP longitude, CHANGE id id INT NOT NULL
        SQL);
    }
}
