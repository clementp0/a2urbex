<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106142349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location_type (location_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_D7C9A75AED775E23 (location_id), INDEX IDX_D7C9A75AC54C8C93 (type_id), PRIMARY KEY(location_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location_type ADD CONSTRAINT FK_D7C9A75AED775E23 FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location_type ADD CONSTRAINT FK_D7C9A75AC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location ADD country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_17E64ABAF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_17E64ABAF92F3E70 ON location (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE location_type');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_17E64ABAF92F3E70');
        $this->addSql('DROP INDEX IDX_17E64ABAF92F3E70 ON location');
        $this->addSql('ALTER TABLE location DROP country_id');
    }
}
