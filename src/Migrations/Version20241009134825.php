<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009134825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT fk_fffb207071f7e88b');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT fk_fffb207053ec1a21');
        $this->addSql('DROP TABLE event_photographers');
        $this->addSql('ALTER TABLE event ADD description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX idx_fffb207053ec1a21 ON event_photographers (photographer_id)');
        $this->addSql('CREATE INDEX idx_fffb207071f7e88b ON event_photographers (event_id)');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT fk_fffb207071f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT fk_fffb207053ec1a21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event DROP description');
    }
}
