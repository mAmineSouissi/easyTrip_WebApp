<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508145234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, addresse VARCHAR(255) NOT NULL, profile_photo VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE agency (id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cars (id INT NOT NULL, model VARCHAR(255) NOT NULL, seats INT NOT NULL, location VARCHAR(255) NOT NULL, price_per_day DOUBLE PRECISION NOT NULL, image VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, travel_id INT DEFAULT NULL, hotel_id INT DEFAULT NULL, user_id INT NOT NULL, rating INT NOT NULL, message VARCHAR(50) NOT NULL, date DATE NOT NULL, INDEX IDX_D2294458700047D2 (ticket_id), INDEX IDX_D2294458ECAB15B3 (travel_id), INDEX IDX_D22944583243BB18 (hotel_id), INDEX IDX_D2294458A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE hotels (id_hotel INT NOT NULL, name VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, rating INT NOT NULL, description LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, type_room VARCHAR(255) NOT NULL, num_room INT NOT NULL, image VARCHAR(255) NOT NULL, promotion_id INT NOT NULL, agency_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id_hotel)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at VARCHAR(255) NOT NULL, available_at VARCHAR(255) NOT NULL, delivered_at VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE offer_travel (id INT NOT NULL, departure VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, departure_date DATE NOT NULL, arrival_date DATE NOT NULL, hotel_name VARCHAR(50) NOT NULL, flight_name VARCHAR(255) NOT NULL, discription VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, image VARCHAR(255) NOT NULL, agency_id INT NOT NULL, promotion_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `option` (id INT NOT NULL, question_id INT DEFAULT NULL, option_text VARCHAR(255) NOT NULL, INDEX IDX_5A8600B01E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE panier (id INT NOT NULL, user_id INT DEFAULT NULL, reservation_id INT DEFAULT NULL, coupon_id INT NOT NULL, quantity INT NOT NULL, total_price DOUBLE PRECISION NOT NULL, INDEX IDX_24CC0DF2A76ED395 (user_id), INDEX IDX_24CC0DF2B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotion (id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, discount_percentage DOUBLE PRECISION NOT NULL, valid_until DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE question (id INT NOT NULL, survey_id INT DEFAULT NULL, question_text LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_B6F7494EB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(15) NOT NULL, date DATE NOT NULL, issue VARCHAR(50) NOT NULL, category VARCHAR(20) NOT NULL, userId INT NOT NULL, INDEX IDX_CE60640464B64DCC (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE res_transport (id INT NOT NULL, user_id INT NOT NULL, car_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id_reservation INT NOT NULL, user_id INT DEFAULT NULL, travel_id INT NOT NULL, status VARCHAR(255) NOT NULL, order_date DATE NOT NULL, ticket_id INT NOT NULL, hotel_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, places VARCHAR(255) NOT NULL, INDEX IDX_42C84955A76ED395 (user_id), PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey (id INT NOT NULL, created_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, category VARCHAR(50) NOT NULL, INDEX IDX_AD5F9BFCDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_response (id INT NOT NULL, response_data LONGTEXT NOT NULL, recommendations LONGTEXT NOT NULL, completed_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tickets (id_ticket INT NOT NULL, flight_number INT NOT NULL, airline VARCHAR(255) NOT NULL, departure_city VARCHAR(255) NOT NULL, arrival_city VARCHAR(255) NOT NULL, departure_date DATE NOT NULL, departure_time VARCHAR(255) NOT NULL, arrival_date DATE NOT NULL, arrival_time VARCHAR(255) NOT NULL, ticket_class VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, ticket_type VARCHAR(50) NOT NULL, image_airline VARCHAR(255) NOT NULL, city_image VARCHAR(1000) NOT NULL, agency_id INT NOT NULL, promotion_id INT NOT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id_ticket)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE verification_codes (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, used INT DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE webinaire (id INT NOT NULL, hotel_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, debut_date_time DATETIME NOT NULL, finit_date_time DATETIME NOT NULL, link VARCHAR(255) NOT NULL, room_id VARCHAR(255) NOT NULL, INDEX IDX_1B5D30753243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id_ticket) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458ECAB15B3 FOREIGN KEY (travel_id) REFERENCES offer_travel (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D22944583243BB18 FOREIGN KEY (hotel_id) REFERENCES hotels (id_hotel) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `option` ADD CONSTRAINT FK_5A8600B01E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id_reservation) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640464B64DCC FOREIGN KEY (userId) REFERENCES User (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCDE12AB56 FOREIGN KEY (created_by) REFERENCES User (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire ADD CONSTRAINT FK_1B5D30753243BB18 FOREIGN KEY (hotel_id) REFERENCES hotels (id_hotel) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458700047D2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458ECAB15B3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D22944583243BB18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `option` DROP FOREIGN KEY FK_5A8600B01E27F6BF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2B83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EB3FE509D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640464B64DCC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFCDE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webinaire DROP FOREIGN KEY FK_1B5D30753243BB18
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE User
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agency
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cars
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE feedback
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hotels
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE offer_travel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `option`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE panier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE promotion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE res_transport
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_response
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tickets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE verification_codes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE webinaire
        SQL);
    }
}
