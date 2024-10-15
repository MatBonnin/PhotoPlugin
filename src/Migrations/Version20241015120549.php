<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015120549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, startDate DATE DEFAULT NULL, endDate DATE DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 ON event (slug)');
        $this->addSql('CREATE TABLE event_photographers (event_id INT NOT NULL, photographer_id INT NOT NULL, PRIMARY KEY(event_id, photographer_id))');
        $this->addSql('CREATE INDEX IDX_FFFB207071F7E88B ON event_photographers (event_id)');
        $this->addSql('CREATE INDEX IDX_FFFB207053EC1A21 ON event_photographers (photographer_id)');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207053EC1A21 FOREIGN KEY (photographer_id) REFERENCES sylius_photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT FK_677B9B7471F7E88B');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207071F7E88B');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207053EC1A21');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_photographers');
    }
}
