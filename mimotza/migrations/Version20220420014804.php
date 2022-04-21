<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220420014804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etat_suggestion (id INT AUTO_INCREMENT NOT NULL, etat VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, event VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, id_user_id INT NOT NULL, id_event_id INT NOT NULL, detail LONGTEXT DEFAULT NULL, date_emission DATETIME NOT NULL, INDEX IDX_EDBFD5EC79F37AE5 (id_user_id), INDEX IDX_EDBFD5EC212C041E (id_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE langue (id INT AUTO_INCREMENT NOT NULL, langue VARCHAR(255) NOT NULL, date_ajout DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, id_user_id INT NOT NULL, id_parent_id INT DEFAULT NULL, contenu LONGTEXT NOT NULL, date_emission DATETIME NOT NULL, INDEX IDX_B6BD307F79F37AE5 (id_user_id), INDEX IDX_B6BD307FF24F7657 (id_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mot (id INT AUTO_INCREMENT NOT NULL, id_langue_id INT NOT NULL, mot VARCHAR(5) NOT NULL, date_ajout DATETIME NOT NULL, INDEX IDX_A43432CAA9806EA (id_langue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partie (id INT AUTO_INCREMENT NOT NULL, id_user_id INT NOT NULL, win TINYINT(1) NOT NULL, score SMALLINT NOT NULL, temps TIME NOT NULL, date_emission DATETIME NOT NULL, INDEX IDX_59B1F3D79F37AE5 (id_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE suggestion (id INT AUTO_INCREMENT NOT NULL, id_user_id INT NOT NULL, id_langue_id INT NOT NULL, id_etat_suggestion_id INT NOT NULL, mot_suggere VARCHAR(5) NOT NULL, date_emission DATETIME NOT NULL, INDEX IDX_DD80F31B79F37AE5 (id_user_id), INDEX IDX_DD80F31BAA9806EA (id_langue_id), INDEX IDX_DD80F31B314D1FFB (id_etat_suggestion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thread (id INT AUTO_INCREMENT NOT NULL, id_user_id INT NOT NULL, id_message_id INT NOT NULL, titre VARCHAR(255) NOT NULL, date_emission DATETIME NOT NULL, INDEX IDX_31204C8379F37AE5 (id_user_id), UNIQUE INDEX UNIQ_31204C83F6F093FE (id_message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, id_role_id INT NOT NULL, id_statut_id INT NOT NULL, username VARCHAR(32) NOT NULL, email VARCHAR(255) NOT NULL, mdp VARCHAR(32) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, INDEX IDX_1D1C63B389E8BDC (id_role_id), INDEX IDX_1D1C63B376158423 (id_statut_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5EC79F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5EC212C041E FOREIGN KEY (id_event_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F79F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF24F7657 FOREIGN KEY (id_parent_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE mot ADD CONSTRAINT FK_A43432CAA9806EA FOREIGN KEY (id_langue_id) REFERENCES langue (id)');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3D79F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31B79F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31BAA9806EA FOREIGN KEY (id_langue_id) REFERENCES langue (id)');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31B314D1FFB FOREIGN KEY (id_etat_suggestion_id) REFERENCES etat_suggestion (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C8379F37AE5 FOREIGN KEY (id_user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83F6F093FE FOREIGN KEY (id_message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B389E8BDC FOREIGN KEY (id_role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B376158423 FOREIGN KEY (id_statut_id) REFERENCES statut (id)');

        $this->addSql('INSERT INTO `role` (`id`, `role`) VALUES (1, \'Usager\'), (2, \'Administrateur\')');
        $this->addSql('INSERT INTO `statut` (`id`, `statut`) VALUES (1, \'Inactif\'), (2, \'Actif\'),(3, \'Banni\')');
        $this->addSql('INSERT INTO `etat_suggestion` (`id`, `etat`) VALUES (1, \'En attente\'), (2, \'Refusé\'), (3, \'Accepté\')');
        $this->addSql('INSERT INTO `evenement` (`id`, `event`) VALUES (1, \'Inscription Utilisateur\'), (2, \'Activation Utilisateur\'), (3, \'Désactivation Utilisateur\'), (4, \'Bannissement Utilisateur\'), (5, \'Ajout Thread\'), (6, \'Suppression Thread\'), (7, \'Ajout Message\'), (8, \'Suppression Message\'), (9, \'Partie\'), (10, \'Ajout Langue\'), (11, \'Suppression Langue\'), (12, \'Ajout Mot\'), (13, \'Suppression Mot\'), (14, \'Suggestion Mot\')');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suggestion DROP FOREIGN KEY FK_DD80F31B314D1FFB');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5EC212C041E');
        $this->addSql('ALTER TABLE mot DROP FOREIGN KEY FK_A43432CAA9806EA');
        $this->addSql('ALTER TABLE suggestion DROP FOREIGN KEY FK_DD80F31BAA9806EA');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF24F7657');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83F6F093FE');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B389E8BDC');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B376158423');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5EC79F37AE5');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F79F37AE5');
        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3D79F37AE5');
        $this->addSql('ALTER TABLE suggestion DROP FOREIGN KEY FK_DD80F31B79F37AE5');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C8379F37AE5');
        $this->addSql('DROP TABLE etat_suggestion');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE historique');
        $this->addSql('DROP TABLE langue');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE mot');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE suggestion');
        $this->addSql('DROP TABLE thread');
        $this->addSql('DROP TABLE utilisateur');
    }
}
