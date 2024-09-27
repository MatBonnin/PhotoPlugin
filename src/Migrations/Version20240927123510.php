<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927123510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX IDX_FFFB207071F7E88B ON event_photographers (event_id)');
        $this->addSql('CREATE INDEX IDX_FFFB207053EC1A21 ON event_photographers (photographer_id)');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207053EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT fk_6686f1fb71f7e88b');
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT fk_6686f1fb53ec1a21');
        $this->addSql('DROP TABLE events_photographers');
        $this->addSql('ALTER TABLE event ADD startDate DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD endDate DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP start_date');
        $this->addSql('ALTER TABLE event DROP end_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX idx_6686f1fb53ec1a21 ON events_photographers (photographer_id)');
        $this->addSql('CREATE INDEX idx_6686f1fb71f7e88b ON events_photographers (event_id)');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT fk_6686f1fb71f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT fk_6686f1fb53ec1a21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207071F7E88B');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207053EC1A21');
        $this->addSql('DROP TABLE event_photographers');
        $this->addSql('ALTER TABLE event ADD start_date DATE NOT NULL');
        $this->addSql('ALTER TABLE event ADD end_date DATE NOT NULL');
        $this->addSql('ALTER TABLE event DROP startDate');
        $this->addSql('ALTER TABLE event DROP endDate');
    }
}
