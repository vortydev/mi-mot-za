-- Description: Script de génération de la BD Web de Mi-Mot-Za
-- Auteur: Étienne Ménard

-- Date de création:        05/04/2022
-- Dernière modification:   07/04/2022

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- 
-- Création de la base de données
-- 
CREATE DATABASE IF NOT EXISTS `mimotza` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mimotza`;

-- Table Role
CREATE TABLE `tbl_role` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `role` varchar(32) NOT NULL,

  CONSTRAINT PK_Role PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion Rôles
INSERT INTO `tbl_role` (`id`, `role`) VALUES
  (1, 'Usager'),
  (2, 'Administrateur');

-- Table Statut
CREATE TABLE `tbl_statut` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `statut` varchar(32) NOT NULL,

  CONSTRAINT PK_Statut PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion Statuts
INSERT INTO `tbl_statut` (`id`, `statut`) VALUES
  (1, 'Inactif'),
  (2, 'Actif'),
  (3, 'Banni');

  -- Table Utilisateur
CREATE TABLE `tbl_utilisateur` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(32) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `avatar` varchar(255),
  `idRole` int(8) NOT NULL,
  `idStatut` int(8) NOT NULL,
  `dateCreation` DATETIME NOT NULL,

  CONSTRAINT PK_User PRIMARY KEY (`id`),
  CONSTRAINT FK_UserRole FOREIGN KEY (`idRole`) REFERENCES `tbl_role` (`id`),
  CONSTRAINT FK_UserStatut FOREIGN KEY (`idStatut`) REFERENCES `tbl_statut` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Table Thread
CREATE TABLE `tbl_thread` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `idUser` int(8) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `idMessage` int(8) NOT NULL, 
  `dateEmission` DATETIME NOT NULL,

  CONSTRAINT PK_Thread PRIMARY KEY (`id`),
  CONSTRAINT FK_ThreadMessage FOREIGN KEY (`idUser`) REFERENCES `tbl_thread` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Message
CREATE TABLE `tbl_message` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `idUser` int(8) NOT NULL,
  `idParent` int(8),
  `contenu` TEXT NOT NULL,
  `dateEmission` DATETIME NOT NULL,

  CONSTRAINT PK_Message PRIMARY KEY (`id`),
  CONSTRAINT FK_MessageParent FOREIGN KEY (`idParent`) REFERENCES `tbl_utilisateur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Table Langue
CREATE TABLE `tbl_langue` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `langue` varchar(255) NOT NULL,
  `dateAjout` DATETIME NOT NULL,

  CONSTRAINT PK_Langue PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Mot
CREATE TABLE `tbl_mot` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `mot` varchar(5) NOT NULL,
  `idLangue` int(8) NOT NULL,
  `dateAjout` DATETIME NOT NULL,

  CONSTRAINT PK_Mot PRIMARY KEY (`id`),
  CONSTRAINT FK_MotLangue FOREIGN KEY (`idLangue`) REFERENCES `tbl_langue` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Etat Suggestion
CREATE TABLE `tbl_etat_suggestion` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `etat` varchar(32) NOT NULL,
  
  CONSTRAINT PK_EtatSuggestion PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des états
INSERT INTO `tbl_etat_suggestion` (`id`, `etat`) VALUES
  (1, 'En attente'),
  (2, 'Refusé'),
  (3, 'Accepté');

-- Table Suggestion
CREATE TABLE `tbl_suggestion` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `idUser` int(8) NOT NULL,
  `motSuggere` varchar(5) NOT NULL,
  `idLangue` int(8) NOT NULL,
  `idEtat` int(8) NOT NULL,
  `dateEmission` DATETIME NOT NULL,

  CONSTRAINT PK_Suggestion PRIMARY KEY (`id`),
  CONSTRAINT FK_SuggestionUser FOREIGN KEY (`idUser`) REFERENCES `tbl_utilisateur` (`id`),
  CONSTRAINT FK_SuggestionLangue FOREIGN KEY (`idLangue`) REFERENCES `tbl_langue` (`id`),
  CONSTRAINT FK_SuggestionEtat FOREIGN KEY (`idEtat`) REFERENCES `tbl_etat_suggestion` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Table Partie
CREATE TABLE `tbl_partie` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `idUser` int(8) NOT NULL,
  `win` BIT NOT NULL,
  `score` TINYINT NOT NULL,
  `temps` TIME NOT NULL,
  `dateEmission` DATETIME NOT NULL,

  CONSTRAINT PK_Partie PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Table Évènement
CREATE TABLE `tbl_evenement` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `event` varchar(64) NOT NULL,

  CONSTRAINT PK_Event PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion Évènement
INSERT INTO `tbl_evenement` (`id`, `event`) VALUES
  (1, 'Inscription Utilisateur'),     -- inscription d'un utilisateur
  (2, 'Activation Utilisateur'),      -- activation d'un utilisateur
  (3, 'Désactivation Utilisateur'),   -- désactivation d'un utilisateur
  (4, 'Bannissement Utilisateur'),    -- bannissement d'un utilisateur
  (5, 'Ajout Thread'),                -- publication d'une thread
  (6, 'Suppression Thread'),          -- suppression d'une thread (admin)
  (7, 'Ajout Message'),               -- publication d'un message
  (8, 'Suppression Message'),         -- suppression d'un message (admin)
  (9, 'Partie'),                      -- émission d'une partie
  (10, 'Ajout Langue'),               -- ajout d'une langue (admin)
  (11, 'Suppression Langue'),         -- suppression d'une langue (admin)
  (12, 'Ajout Mot'),                  -- ajout d'un mot (admin)
  (13, 'Suppression Mot'),            -- suppression d'un mot (admin)
  (14, 'Suggestion Mot');             -- émission d'une suggestion de mot

-- Table Historique
CREATE TABLE `tbl_historique` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `idUser` int(8) NOT NULL,
  `typeEvent` int(8) NOT NULL,
  `detail` TEXT,
  `dateEmission` DATETIME NOT NULL,

  CONSTRAINT PK_Log PRIMARY KEY (`id`),
  CONSTRAINT FK_LogUser FOREIGN KEY (`idUser`) REFERENCES `tbl_utilisateur` (`id`),
  CONSTRAINT FK_LogType FOREIGN KEY (`typeEvent`) REFERENCES `tbl_evenement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
