<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927073827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE photographer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_id_seq CASCADE');
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT fk_6686f1fb71f7e88b');
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT fk_6686f1fb53ec1a21');
        $this->addSql('DROP TABLE photographer');
        $this->addSql('DROP TABLE events_photographers');
        $this->addSql('DROP TABLE event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE photographer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE photographer (id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_16337a7fe7927c74 ON photographer (email)');
        $this->addSql('CREATE TABLE events_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX idx_6686f1fb53ec1a21 ON events_photographers (photographer_id)');
        $this->addSql('CREATE INDEX idx_6686f1fb71f7e88b ON events_photographers (event_id)');
        $this->addSql('CREATE TABLE event (id INT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT fk_6686f1fb71f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT fk_6686f1fb53ec1a21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
