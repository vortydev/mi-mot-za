<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427191227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Langue: 1, Français, now
        //$this->addSql('INSERT INTO `langue` (`id`, `langue`, `date_ajout`) VALUES (1, "Français", "'.date("Y-m-d H:i:s").'")');

        //$this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3F85E0677 ON utilisateur (username)');
        $this->addSql('INSERT INTO `role` (`id`, `role`)
        VALUES (1, "Administrateur"),
                (2, "Utilisateur")');
        $this->addSql('INSERT INTO `statut` (`id`, `statut`)
        VALUES (1, "Inactif"),
                (2, "Actif"),
                (3, "Banni")');
        $this->addSql('INSERT INTO `utilisateur` (`id`, `id_role_id`, `id_statut_id`, `username`, `email`, `mdp`, `nom`, `prenom`, `avatar`, `date_creation`) VALUES (7, 2, 2, "admin", "admin@mimotza.ca", "'. (password_hash("admin", PASSWORD_DEFAULT)) .'", "Administrateur", "Admin", "https://imgur.com/E1wbuSQ.png", "'.date("Y-m-d H:i:s").'")');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
