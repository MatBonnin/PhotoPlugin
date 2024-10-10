<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cocur\Slugify\Slugify;

final class Version20241009131856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute un champ slug aux événements existants et génère un slug pour les événements existants.';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne slug
        $this->addSql('ALTER TABLE event ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 ON event (slug)');

        // Générer un slug temporaire basé sur l'ID des événements existants
        $this->addSql("UPDATE event SET slug = CONCAT('event-', id) WHERE slug IS NULL");

        // Rendre le champ slug non-nullable une fois les slugs générés
        $this->addSql('ALTER TABLE event ALTER COLUMN slug SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revenir en arrière en cas de besoin
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7989D9B62');
        $this->addSql('ALTER TABLE event DROP slug');
    }
}
