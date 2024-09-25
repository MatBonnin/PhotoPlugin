<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925092231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX IDX_6686F1FB71F7E88B ON events_photographers (event_id)');
        $this->addSql('CREATE INDEX IDX_6686F1FB53EC1A21 ON events_photographers (photographer_id)');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT FK_6686F1FB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events_photographers ADD CONSTRAINT FK_6686F1FB53EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT FK_6686F1FB71F7E88B');
        $this->addSql('ALTER TABLE events_photographers DROP CONSTRAINT FK_6686F1FB53EC1A21');
        $this->addSql('DROP TABLE events_photographers');
    }
}
