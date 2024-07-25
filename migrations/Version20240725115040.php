<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240725115040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__cart_content AS SELECT id, cart_id FROM cart_content');
        $this->addSql('DROP TABLE cart_content');
        $this->addSql('CREATE TABLE cart_content (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id INTEGER NOT NULL, product_id INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_51FF8AE1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_51FF8AE4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cart_content (id, cart_id) SELECT id, cart_id FROM __temp__cart_content');
        $this->addSql('DROP TABLE __temp__cart_content');
        $this->addSql('CREATE INDEX IDX_51FF8AE1AD5CDBF ON cart_content (cart_id)');
        $this->addSql('CREATE INDEX IDX_51FF8AE4584665A ON cart_content (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__cart_content AS SELECT id, cart_id FROM cart_content');
        $this->addSql('DROP TABLE cart_content');
        $this->addSql('CREATE TABLE cart_content (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id INTEGER NOT NULL, CONSTRAINT FK_51FF8AE1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cart_content (id, cart_id) SELECT id, cart_id FROM __temp__cart_content');
        $this->addSql('DROP TABLE __temp__cart_content');
        $this->addSql('CREATE INDEX IDX_51FF8AE1AD5CDBF ON cart_content (cart_id)');
    }
}
