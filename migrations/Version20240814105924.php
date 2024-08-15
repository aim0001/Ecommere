<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814105924 extends AbstractMigration
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
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FE4584665A');
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FE82EA2E54');
        $this->addSql('DROP TABLE purchases');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
        $this->addSql('ALTER TABLE commande ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD pay_on_delivery TINYINT(1) NOT NULL, ADD total_price DOUBLE PRECISION NOT NULL, ADD is_completed TINYINT(1) DEFAULT NULL, DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchases (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, commande_id INT NOT NULL, quantity INT NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, INDEX IDX_AA6431FE4584665A (product_id), INDEX IDX_AA6431FE82EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEA82EA2E54');
        $this->addSql('ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEA4584665A');
        $this->addSql('DROP TABLE product_commande');
        $this->addSql('ALTER TABLE commande ADD user_id INT NOT NULL, DROP first_name, DROP last_name, DROP pay_on_delivery, DROP total_price, DROP is_completed');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
    }
}
