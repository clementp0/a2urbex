<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106170216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE locations DROP FOREIGN KEY FK_17E64ABAF92F3E70');
        $this->addSql('ALTER TABLE locations_type DROP FOREIGN KEY FK_D7C9A75AC54C8C93');
        $this->addSql('ALTER TABLE locations_type DROP FOREIGN KEY FK_D7C9A75AED775E23');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE locations_type');
        $this->addSql('ALTER TABLE location CHANGE pid pid BIGINT NOT NULL, CHANGE lon lon NUMERIC(14, 12) DEFAULT NULL, CHANGE lat lat NUMERIC(14, 12) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE locations (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, pid INT NOT NULL, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, lon VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lat VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_17E64ABAF92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE locations_type (locations_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_D7C9A75AED775E23 (locations_id), INDEX IDX_D7C9A75AC54C8C93 (type_id), PRIMARY KEY(locations_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE locations ADD CONSTRAINT FK_17E64ABAF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE locations_type ADD CONSTRAINT FK_D7C9A75AC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE locations_type ADD CONSTRAINT FK_D7C9A75AED775E23 FOREIGN KEY (locations_id) REFERENCES locations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location CHANGE pid pid INT NOT NULL, CHANGE lon lon VARCHAR(255) NOT NULL, CHANGE lat lat VARCHAR(255) NOT NULL');
    }
}
