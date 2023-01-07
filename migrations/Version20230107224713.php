<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230107224713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location_type DROP FOREIGN KEY FK_CDAE269C54C8C93');
        $this->addSql('ALTER TABLE location_type DROP FOREIGN KEY FK_CDAE26964D218E');
        $this->addSql('DROP TABLE location_type');
        $this->addSql('ALTER TABLE location ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_5E9E89CB12469DE2 ON location (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB12469DE2');
        $this->addSql('CREATE TABLE location_type (location_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_CDAE269C54C8C93 (type_id), INDEX IDX_CDAE26964D218E (location_id), PRIMARY KEY(location_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE location_type ADD CONSTRAINT FK_CDAE269C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location_type ADD CONSTRAINT FK_CDAE26964D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP INDEX IDX_5E9E89CB12469DE2 ON location');
        $this->addSql('ALTER TABLE location DROP category_id');
    }
}
