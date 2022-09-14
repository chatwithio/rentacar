<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220513083410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_photos ADD matricula_id INT NOT NULL');
        $this->addSql('ALTER TABLE car_photos ADD CONSTRAINT FK_101E0C2B15C84B52 FOREIGN KEY (matricula_id) REFERENCES car (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_101E0C2B15C84B52 ON car_photos (matricula_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE car_photos DROP CONSTRAINT FK_101E0C2B15C84B52');
        $this->addSql('DROP INDEX IDX_101E0C2B15C84B52');
        $this->addSql('ALTER TABLE car_photos DROP matricula_id');
    }
}
