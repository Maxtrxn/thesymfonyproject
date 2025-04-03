<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403153113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE l3_cart (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, CONSTRAINT FK_C330835D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES l3_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C330835D7E3C61F9 ON l3_cart (owner_id)');
        $this->addSql('CREATE TABLE l3_cart_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, cart_id INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_B9ED26B74584665A FOREIGN KEY (product_id) REFERENCES l3_product (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B9ED26B71AD5CDBF FOREIGN KEY (cart_id) REFERENCES l3_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B9ED26B74584665A ON l3_cart_item (product_id)');
        $this->addSql('CREATE INDEX IDX_B9ED26B71AD5CDBF ON l3_cart_item (cart_id)');
        $this->addSql('CREATE TABLE l3_product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, stock INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE l3_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, country VARCHAR(2) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON l3_user (username)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE l3_cart');
        $this->addSql('DROP TABLE l3_cart_item');
        $this->addSql('DROP TABLE l3_product');
        $this->addSql('DROP TABLE l3_user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
