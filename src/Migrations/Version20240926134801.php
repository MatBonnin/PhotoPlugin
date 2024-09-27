<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926134801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photographer DROP CONSTRAINT fk_16337a7fa76ed395');
        $this->addSql('DROP INDEX uniq_16337a7fa76ed395');
        $this->addSql('ALTER TABLE photographer ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE photographer ADD password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE photographer ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE photographer DROP user_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16337A7FE7927C74 ON photographer (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_16337A7FE7927C74');
        $this->addSql('ALTER TABLE photographer ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photographer DROP email');
        $this->addSql('ALTER TABLE photographer DROP password');
        $this->addSql('ALTER TABLE photographer DROP roles');
        $this->addSql('ALTER TABLE photographer ADD CONSTRAINT fk_16337a7fa76ed395 FOREIGN KEY (user_id) REFERENCES sylius_admin_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_16337a7fa76ed395 ON photographer (user_id)');
    }
}
