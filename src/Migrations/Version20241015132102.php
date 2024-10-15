<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015132102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photographer DROP password');
        $this->addSql('ALTER TABLE photographer ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photographer ADD password VARCHAR(255) NOT NULL');
        $this->addSql('CREATE SEQUENCE photographer_id_seq');
        $this->addSql('SELECT setval(\'photographer_id_seq\', (SELECT MAX(id) FROM photographer))');
        $this->addSql('ALTER TABLE photographer ALTER id SET DEFAULT nextval(\'photographer_id_seq\')');
    }
}
