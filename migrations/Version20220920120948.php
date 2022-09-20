<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220920120948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "messages_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "messages" (id INT NOT NULL, message_content VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message_type VARCHAR(25) NOT NULL, message_to VARCHAR(255) NOT NULL, message_from VARCHAR(255) NOT NULL, license_number VARCHAR(255) DEFAULT NULL, sent BOOLEAN NOT NULL, delivered BOOLEAN DEFAULT NULL, read BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "messages".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE car ADD tel_from VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE car ALTER wa_id TYPE VARCHAR(15)');
        $this->addSql('ALTER TABLE car ALTER wa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE car_photos DROP CONSTRAINT fk_101e0c2b15c84b52');
        $this->addSql('DROP INDEX idx_101e0c2b15c84b52');
        $this->addSql('ALTER TABLE car_photos DROP matricula_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "messages_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE "messages"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER TABLE car DROP tel_from');
        $this->addSql('ALTER TABLE car ALTER wa_id TYPE INT');
        $this->addSql('ALTER TABLE car ALTER wa_id DROP DEFAULT');
        $this->addSql('ALTER TABLE car ALTER wa_id TYPE INT');
        $this->addSql('ALTER TABLE car_photos ADD matricula_id INT NOT NULL');
        $this->addSql('ALTER TABLE car_photos ADD CONSTRAINT fk_101e0c2b15c84b52 FOREIGN KEY (matricula_id) REFERENCES car (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_101e0c2b15c84b52 ON car_photos (matricula_id)');
    }
}
