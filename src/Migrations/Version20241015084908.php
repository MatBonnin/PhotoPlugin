<?php

declare(strict_types=1);

namespace Sylius\Plugin\PhotoPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015084908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Vérifiez d'abord si la contrainte existe avant de la supprimer
        $constraintExists = $this->connection->fetchOne("
            SELECT COUNT(*)
            FROM pg_constraint
            WHERE conname = 'fk_677b9b7453ec1a21'
        ");

        if ($constraintExists > 0) {
            $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT fk_677b9b7453ec1a21');
        }

        $this->addSql('DROP SEQUENCE photographer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE brille24_tierprice_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE sylius_photographer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sylius_photographer (id INT NOT NULL, username VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, encoder_name VARCHAR(255) DEFAULT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles JSONB NOT NULL, email VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, locale_code VARCHAR(12) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D2F572BE6B7BA4B6 ON sylius_photographer (password_reset_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D2F572BEC4995C67 ON sylius_photographer (email_verification_token)');

        $this->addSql('ALTER TABLE brille24_tierprice DROP CONSTRAINT fk_ba5254f872f5a1aa');
        $this->addSql('ALTER TABLE brille24_tierprice DROP CONSTRAINT fk_ba5254f8a80ef684');
        $this->addSql('ALTER TABLE brille24_tierprice DROP CONSTRAINT fk_ba5254f8d2919a68');
        $this->addSql('DROP TABLE photographer');
        $this->addSql('DROP TABLE brille24_tierprice');

        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers ADD CONSTRAINT FK_FFFB207053EC1A21 FOREIGN KEY (photographer_id) REFERENCES sylius_photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FFFB207071F7E88B ON event_photographers (event_id)');
        $this->addSql('CREATE INDEX IDX_FFFB207053EC1A21 ON event_photographers (photographer_id)');

        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7453EC1A21 FOREIGN KEY (photographer_id) REFERENCES sylius_photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207053EC1A21');
        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT FK_677B9B7453EC1A21');
        $this->addSql('DROP SEQUENCE sylius_photographer_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE photographer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE brille24_tierprice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE photographer (id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_16337a7fe7927c74 ON photographer (email)');
        $this->addSql('CREATE TABLE brille24_tierprice (id INT NOT NULL, channel_id INT DEFAULT NULL, product_variant_id INT DEFAULT NULL, customer_group_id INT DEFAULT NULL, price INT NOT NULL, qty INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX no_duplicate_prices ON brille24_tierprice (qty, channel_id, product_variant_id, customer_group_id)');
        $this->addSql('CREATE INDEX idx_ba5254f8d2919a68 ON brille24_tierprice (customer_group_id)');
        $this->addSql('CREATE INDEX idx_ba5254f8a80ef684 ON brille24_tierprice (product_variant_id)');
        $this->addSql('CREATE INDEX idx_ba5254f872f5a1aa ON brille24_tierprice (channel_id)');
        $this->addSql('ALTER TABLE brille24_tierprice ADD CONSTRAINT fk_ba5254f872f5a1aa FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE brille24_tierprice ADD CONSTRAINT fk_ba5254f8a80ef684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE brille24_tierprice ADD CONSTRAINT fk_ba5254f8d2919a68 FOREIGN KEY (customer_group_id) REFERENCES sylius_customer_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE sylius_photographer');
        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT fk_677b9b7453ec1a21');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT fk_677b9b7453ec1a21 FOREIGN KEY (photographer_id) REFERENCES photographer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_photographers DROP CONSTRAINT FK_FFFB207071F7E88B');
        $this->addSql('DROP INDEX IDX_FFFB207071F7E88B');
        $this->addSql('DROP INDEX IDX_FFFB207053EC1A21');
    }
}
