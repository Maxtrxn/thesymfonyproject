<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403154717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_cart AS SELECT id FROM l3_cart');
        $this->addSql('DROP TABLE l3_cart');
        $this->addSql('CREATE TABLE l3_cart (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('INSERT INTO l3_cart (id) SELECT id FROM __temp__l3_cart');
        $this->addSql('DROP TABLE __temp__l3_cart');
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_cart_item AS SELECT id, product_id, cart_id, quantity FROM l3_cart_item');
        $this->addSql('DROP TABLE l3_cart_item');
        $this->addSql('CREATE TABLE l3_cart_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, cart_id INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_B9ED26B74584665A FOREIGN KEY (product_id) REFERENCES l3_product (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B9ED26B71AD5CDBF FOREIGN KEY (cart_id) REFERENCES l3_cart (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO l3_cart_item (id, product_id, cart_id, quantity) SELECT id, product_id, cart_id, quantity FROM __temp__l3_cart_item');
        $this->addSql('DROP TABLE __temp__l3_cart_item');
        $this->addSql('CREATE INDEX IDX_B9ED26B71AD5CDBF ON l3_cart_item (cart_id)');
        $this->addSql('CREATE INDEX IDX_B9ED26B74584665A ON l3_cart_item (product_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_user AS SELECT id, username, roles, password, name, surname, country FROM l3_user');
        $this->addSql('DROP TABLE l3_user');
        $this->addSql('CREATE TABLE l3_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id INTEGER NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, country VARCHAR(2) NOT NULL, CONSTRAINT FK_4500DDA31AD5CDBF FOREIGN KEY (cart_id) REFERENCES l3_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO l3_user (id, username, roles, password, name, surname, country) SELECT id, username, roles, password, name, surname, country FROM __temp__l3_user');
        $this->addSql('DROP TABLE __temp__l3_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON l3_user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4500DDA31AD5CDBF ON l3_user (cart_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_cart AS SELECT id FROM l3_cart');
        $this->addSql('DROP TABLE l3_cart');
        $this->addSql('CREATE TABLE l3_cart (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, CONSTRAINT FK_C330835D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES l3_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO l3_cart (id) SELECT id FROM __temp__l3_cart');
        $this->addSql('DROP TABLE __temp__l3_cart');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C330835D7E3C61F9 ON l3_cart (owner_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_cart_item AS SELECT id, product_id, cart_id, quantity FROM l3_cart_item');
        $this->addSql('DROP TABLE l3_cart_item');
        $this->addSql('CREATE TABLE l3_cart_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, cart_id INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_B9ED26B74584665A FOREIGN KEY (product_id) REFERENCES l3_product (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B9ED26B71AD5CDBF FOREIGN KEY (cart_id) REFERENCES l3_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO l3_cart_item (id, product_id, cart_id, quantity) SELECT id, product_id, cart_id, quantity FROM __temp__l3_cart_item');
        $this->addSql('DROP TABLE __temp__l3_cart_item');
        $this->addSql('CREATE INDEX IDX_B9ED26B74584665A ON l3_cart_item (product_id)');
        $this->addSql('CREATE INDEX IDX_B9ED26B71AD5CDBF ON l3_cart_item (cart_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__l3_user AS SELECT id, username, roles, password, name, surname, country FROM l3_user');
        $this->addSql('DROP TABLE l3_user');
        $this->addSql('CREATE TABLE l3_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, country VARCHAR(2) NOT NULL)');
        $this->addSql('INSERT INTO l3_user (id, username, roles, password, name, surname, country) SELECT id, username, roles, password, name, surname, country FROM __temp__l3_user');
        $this->addSql('DROP TABLE __temp__l3_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON l3_user (username)');
    }
}
