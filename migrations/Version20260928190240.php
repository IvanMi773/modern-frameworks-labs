<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260928190240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cars (id INT AUTO_INCREMENT NOT NULL, make VARCHAR(100) NOT NULL, model VARCHAR(100) NOT NULL, registration_number VARCHAR(32) NOT NULL, model_year SMALLINT NOT NULL, daily_rate NUMERIC(10, 2) NOT NULL, status VARCHAR(20) NOT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_95C71D1438CEDFBE (registration_number), INDEX IDX_95C71D1412469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE clients (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(40) DEFAULT NULL, driver_license_number VARCHAR(80) NOT NULL, UNIQUE INDEX UNIQ_C82E74E7927C74 (email), UNIQUE INDEX UNIQ_C82E747F5AA943 (driver_license_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, method VARCHAR(30) NOT NULL, status VARCHAR(20) NOT NULL, paid_at DATETIME DEFAULT NULL, rental_id INT NOT NULL, INDEX IDX_65D29B32A7CF2329 (rental_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rentals (id INT AUTO_INCREMENT NOT NULL, starts_at DATE NOT NULL, ends_at DATE NOT NULL, daily_rate NUMERIC(10, 2) NOT NULL, status VARCHAR(20) NOT NULL, car_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_35ACDB48C3C6F69F (car_id), INDEX IDX_35ACDB4819EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_BF61C9F55E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO vehicle_categories (name, description) VALUES ('Economy', 'Compact and fuel-efficient cars'), ('SUV', 'Sport utility vehicles'), ('Premium', 'Premium and luxury cars')");
        $this->addSql('ALTER TABLE cars ADD CONSTRAINT FK_95C71D1412469DE2 FOREIGN KEY (category_id) REFERENCES vehicle_categories (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A7CF2329 FOREIGN KEY (rental_id) REFERENCES rentals (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE rentals ADD CONSTRAINT FK_35ACDB48C3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE rentals ADD CONSTRAINT FK_35ACDB4819EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cars DROP FOREIGN KEY FK_95C71D1412469DE2');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A7CF2329');
        $this->addSql('ALTER TABLE rentals DROP FOREIGN KEY FK_35ACDB48C3C6F69F');
        $this->addSql('ALTER TABLE rentals DROP FOREIGN KEY FK_35ACDB4819EB6921');
        $this->addSql('DROP TABLE cars');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE rentals');
        $this->addSql('DROP TABLE vehicle_categories');
    }
}
