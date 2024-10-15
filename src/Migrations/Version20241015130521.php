<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015130521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Photographer entity and link it with AdminUser, update related constraints.';
    }

    public function up(Schema $schema): void
    {
        // Vérifier et supprimer la contrainte fk_fffb207053ec1a21 si elle existe
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'fk_fffb207053ec1a21'
                    AND conrelid = 'event_photographers'::regclass
                ) THEN
                    ALTER TABLE event_photographers DROP CONSTRAINT fk_fffb207053ec1a21;
                END IF;
            END $$;
        ");

        // Vérifier et supprimer la contrainte fk_677b9b7453ec1a21 si elle existe
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'fk_677b9b7453ec1a21'
                    AND conrelid = 'sylius_product'::regclass
                ) THEN
                    ALTER TABLE sylius_product DROP CONSTRAINT fk_677b9b7453ec1a21;
                END IF;
            END $$;
        ");

        // Supprimer les anciennes séquences si elles existent
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM pg_class WHERE relkind='S' AND relname='sylius_photographer_id_seq') THEN
                    DROP SEQUENCE sylius_photographer_id_seq CASCADE;
                END IF;
            END $$;
        ");

        // Créer une nouvelle séquence pour Photographer
        $this->addSql('CREATE SEQUENCE photographer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        // Créer la nouvelle table photographer avec la relation admin_user_id
        $this->addSql('CREATE TABLE photographer (
            id INT NOT NULL DEFAULT nextval(\'photographer_id_seq\'),
            admin_user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            PRIMARY KEY(id)
        )');

        // Créer des index uniques
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16337A7FE7927C74 ON photographer (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16337A7F6352511C ON photographer (admin_user_id)');

        // Ajouter la contrainte de clé étrangère vers sylius_admin_user
        $this->addSql('ALTER TABLE photographer ADD CONSTRAINT FK_16337A7F6352511C FOREIGN KEY (admin_user_id) REFERENCES sylius_admin_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Supprimer l'ancienne table sylius_photographer si elle existe
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'sylius_photographer') THEN
                    DROP TABLE sylius_photographer;
                END IF;
            END $$;
        ");

        // Vérifier et supprimer la contrainte FK_FFFB207053EC1A21 avant de la recréer
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'FK_FFFB207053EC1A21'
                    AND conrelid = 'event_photographers'::regclass
                ) THEN
                    ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207053EC1A21;
                END IF;
            END $$;
        ");

        // Ajouter la nouvelle contrainte de clé étrangère vers photographer
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207053EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Vérifier et supprimer la contrainte FK_677B9B7453EC1A21 avant de la recréer
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'FK_677B9B7453EC1A21'
                    AND conrelid = 'sylius_product'::regclass
                ) THEN
                    ALTER TABLE sylius_product DROP CONSTRAINT FK_677B9B7453EC1A21;
                END IF;
            END $$;
        ");

        // Ajouter la nouvelle contrainte de clé étrangère vers photographer
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7453EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les contraintes de clé étrangère ajoutées
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207053EC1A21');
        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT FK_677B9B7453EC1A21');

        // Supprimer la table photographer
        $this->addSql('DROP TABLE IF EXISTS photographer');

        // Recréer l'ancienne séquence si nécessaire
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS sylius_photographer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        // Recréer l'ancienne table sylius_photographer avec ses contraintes
        $this->addSql('CREATE TABLE sylius_photographer (
            id INT NOT NULL,
            username VARCHAR(255) DEFAULT NULL,
            username_canonical VARCHAR(255) DEFAULT NULL,
            enabled BOOLEAN NOT NULL,
            salt VARCHAR(255) NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            encoder_name VARCHAR(255) DEFAULT NULL,
            last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            password_reset_token VARCHAR(255) DEFAULT NULL,
            password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            email_verification_token VARCHAR(255) DEFAULT NULL,
            verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            locked BOOLEAN NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            roles JSONB NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            email_canonical VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            first_name VARCHAR(255) DEFAULT NULL,
            last_name VARCHAR(255) DEFAULT NULL,
            locale_code VARCHAR(12) NOT NULL,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX uniq_d2f572bec4995c67 ON sylius_photographer (email_verification_token)');
        $this->addSql('CREATE UNIQUE INDEX uniq_d2f572be6b7ba4b6 ON sylius_photographer (password_reset_token)');

        // Supprimer l'ancienne contrainte FK_16337A7F6352511C
        $this->addSql('ALTER TABLE photographer DROP CONSTRAINT IF EXISTS FK_16337A7F6352511C');

        // Recréer l'ancienne table sylius_photographer si elle existe
        $this->addSql('DROP TABLE IF EXISTS photographer');

        // Recréer les contraintes originales sur event_photographers
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT IF EXISTS fk_fffb207053ec1a21');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT fk_fffb207053ec1a21 FOREIGN KEY (photographer_id) REFERENCES sylius_photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Recréer les contraintes originales sur sylius_product
        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT IF EXISTS fk_677b9b7453ec1a21');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT fk_677b9b7453ec1a21 FOREIGN KEY (photographer_id) REFERENCES sylius_photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
