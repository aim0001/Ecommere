<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620102039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD city_id INT DEFAULT NULL, ADD phone VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D8BAC62AF ON commande (city_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498BAC62AF');
        $this->addSql('DROP INDEX IDX_8D93D6498BAC62AF ON user');
        $this->addSql('ALTER TABLE user DROP city_id, DROP phone, DROP adresse');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D8BAC62AF');
        $this->addSql('DROP INDEX IDX_6EEAA67D8BAC62AF ON commande');
        $this->addSql('ALTER TABLE commande DROP city_id, DROP phone, DROP adresse');
        $this->addSql('ALTER TABLE user ADD city_id INT DEFAULT NULL, ADD phone VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6498BAC62AF ON user (city_id)');
    }
}