<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809150808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_commande (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, product_id INT NOT NULL, qte INT NOT NULL, INDEX IDX_A55ACCEA82EA2E54 (commande_id), INDEX IDX_A55ACCEA4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_commande ADD CONSTRAINT FK_A55ACCEA82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE product_commande ADD CONSTRAINT FK_A55ACCEA4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE commande ADD total_price DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEA82EA2E54');
        $this->addSql('ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEA4584665A');
        $this->addSql('DROP TABLE product_commande');
        $this->addSql('ALTER TABLE commande DROP total_price');
    }
}
