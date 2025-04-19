-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 04 avr. 2025 à 15:48
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `universite`
--

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

DROP TABLE IF EXISTS `action`;
CREATE TABLE IF NOT EXISTS `action` (
                                        `id_action` int NOT NULL,
                                        `lib_action` varchar(100) NOT NULL,
    PRIMARY KEY (`id_action`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--

DROP TABLE IF EXISTS `affecter`;
CREATE TABLE IF NOT EXISTS `affecter` (
                                          `id_ens` int NOT NULL,
                                          `id_rapport` int NOT NULL,
                                          `id_jury` int NOT NULL,
                                          PRIMARY KEY (`id_ens`,`id_rapport`,`id_jury`),
    KEY `id_rapport` (`id_rapport`),
    KEY `id_jury` (`id_jury`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--

DROP TABLE IF EXISTS `annee_academique`;
CREATE TABLE IF NOT EXISTS `annee_academique` (
                                                  `id_AC` int NOT NULL,
                                                  `lib_AC` varchar(50) NOT NULL,
    `date_debut_AC` date NOT NULL,
    `date_fin_AC` date NOT NULL,
    PRIMARY KEY (`id_AC`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--

DROP TABLE IF EXISTS `approuver`;
CREATE TABLE IF NOT EXISTS `approuver` (
                                           `id_ens` int NOT NULL,
                                           `id_rapport` int NOT NULL,
                                           `date_appr` date NOT NULL,
                                           `com_appr` text,
                                           PRIMARY KEY (`id_ens`,`id_rapport`),
    KEY `id_rapport` (`id_rapport`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avoir`
--

DROP TABLE IF EXISTS `avoir`;
CREATE TABLE IF NOT EXISTS `avoir` (
                                       `id_grad` int NOT NULL,
                                       `id_ens` int NOT NULL,
                                       `date_grad` date NOT NULL,
                                       PRIMARY KEY (`id_grad`,`id_ens`),
    KEY `id_ens` (`id_ens`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

DROP TABLE IF EXISTS `compte_rendu`;
CREATE TABLE IF NOT EXISTS `compte_rendu` (
                                              `id_CR` int NOT NULL,
                                              `nom_CR` varchar(100) NOT NULL,
    `date_CR` date NOT NULL,
    PRIMARY KEY (`id_CR`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `deposer`
--

DROP TABLE IF EXISTS `deposer`;
CREATE TABLE IF NOT EXISTS `deposer` (
                                         `num_etu` int NOT NULL,
                                         `id_rapport` int NOT NULL,
                                         `date_dep` date NOT NULL,
                                         PRIMARY KEY (`num_etu`,`id_rapport`),
    KEY `id_rapport` (`id_rapport`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--

DROP TABLE IF EXISTS `ecue`;
CREATE TABLE IF NOT EXISTS `ecue` (
                                      `id_ECUE` int NOT NULL,
                                      `lib_ECUE` varchar(100) NOT NULL,
    `Credit_ECUE` int NOT NULL,
    `id_UE` int NOT NULL,
    PRIMARY KEY (`id_ECUE`),
    KEY `id_UE` (`id_UE`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

DROP TABLE IF EXISTS `enseignant`;
CREATE TABLE IF NOT EXISTS `enseignant` (
                                            `id_ens` int NOT NULL,
                                            `nom_ens` varchar(100) NOT NULL,
    `prenoms_ens` varchar(100) NOT NULL,
    `email_ens` varchar(100) DEFAULT NULL,
    `login_ens` varchar(50) NOT NULL,
    `mdp_ens` varchar(255) NOT NULL,
    PRIMARY KEY (`id_ens`),
    UNIQUE KEY `login_ens` (`login_ens`),
    UNIQUE KEY `email_ens` (`email_ens`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
CREATE TABLE IF NOT EXISTS `entreprise` (
                                            `id_entr` int NOT NULL,
                                            `lib_entr` varchar(100) NOT NULL,
    PRIMARY KEY (`id_entr`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
                                          `num_etu` int NOT NULL,
                                          `nom_etu` varchar(100) NOT NULL,
    `prenoms_etu` varchar(100) NOT NULL,
    `date_naiss_etu` date DEFAULT NULL,
    `login_etu` varchar(50) NOT NULL,
    `mdp_etu` varchar(255) NOT NULL,
    PRIMARY KEY (`num_etu`),
    UNIQUE KEY `login_etu` (`login_etu`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

DROP TABLE IF EXISTS `evaluer`;
CREATE TABLE IF NOT EXISTS `evaluer` (
                                         `num_etu` int NOT NULL,
                                         `id_ECUE` int NOT NULL,
                                         `id_ens` int NOT NULL,
                                         `date_eval` date NOT NULL,
                                         `note` decimal(5,2) NOT NULL,
    PRIMARY KEY (`num_etu`,`id_ECUE`,`id_ens`),
    KEY `id_ECUE` (`id_ECUE`),
    KEY `id_ens` (`id_ens`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fairestage`
--

DROP TABLE IF EXISTS `fairestage`;
CREATE TABLE IF NOT EXISTS `fairestage` (
                                            `id_entr` int NOT NULL,
                                            `num_etu` int NOT NULL,
                                            `date_debut_stage` date NOT NULL,
                                            PRIMARY KEY (`id_entr`,`num_etu`),
    KEY `num_etu` (`num_etu`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

DROP TABLE IF EXISTS `fonction`;
CREATE TABLE IF NOT EXISTS `fonction` (
                                          `id_Fonct` int NOT NULL,
                                          `nom_Fonct` varchar(100) NOT NULL,
    PRIMARY KEY (`id_Fonct`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

DROP TABLE IF EXISTS `grade`;
CREATE TABLE IF NOT EXISTS `grade` (
                                       `id_grad` int NOT NULL,
                                       `nom_grad` varchar(100) NOT NULL,
    PRIMARY KEY (`id_grad`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

DROP TABLE IF EXISTS `groupe_utilisateur`;
CREATE TABLE IF NOT EXISTS `groupe_utilisateur` (
                                                    `id_GU` int NOT NULL,
                                                    `lib_GU` varchar(100) NOT NULL,
    `id_TU` int NOT NULL,
    PRIMARY KEY (`id_GU`),
    KEY `id_TU` (`id_TU`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

DROP TABLE IF EXISTS `inscrire`;
CREATE TABLE IF NOT EXISTS `inscrire` (
                                          `num_etu` int NOT NULL,
                                          `id_AC` int NOT NULL,
                                          `id_niv_etu` int NOT NULL,
                                          `id_ensc` int DEFAULT NULL,
                                          `montant_insc` decimal(10,2) NOT NULL,
    PRIMARY KEY (`num_etu`,`id_AC`,`id_niv_etu`),
    KEY `id_AC` (`id_AC`),
    KEY `id_niv_etu` (`id_niv_etu`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jury`
--

DROP TABLE IF EXISTS `jury`;
CREATE TABLE IF NOT EXISTS `jury` (
                                      `id_jury` int NOT NULL,
                                      `lib_jury` varchar(100) NOT NULL,
    PRIMARY KEY (`id_jury`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donnees`
--

DROP TABLE IF EXISTS `niveau_acces_donnees`;
CREATE TABLE IF NOT EXISTS `niveau_acces_donnees` (
                                                      `id_niv_acc` int NOT NULL,
                                                      `libniv_acc` varchar(100) NOT NULL,
    PRIMARY KEY (`id_niv_acc`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_appreciation`
--

DROP TABLE IF EXISTS `niveau_appreciation`;
CREATE TABLE IF NOT EXISTS `niveau_appreciation` (
                                                     `id_apprb` int NOT NULL,
                                                     `lib_apprb` varchar(100) NOT NULL,
    PRIMARY KEY (`id_apprb`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

DROP TABLE IF EXISTS `niveau_etude`;
CREATE TABLE IF NOT EXISTS `niveau_etude` (
                                              `id_niv_etu` int NOT NULL,
                                              `lib_niv_etu` varchar(100) NOT NULL,
    PRIMARY KEY (`id_niv_etu`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `occuper`
--

DROP TABLE IF EXISTS `occuper`;
CREATE TABLE IF NOT EXISTS `occuper` (
                                         `id_Fonct` int NOT NULL,
                                         `id_ens` int NOT NULL,
                                         `date_occup` date NOT NULL,
                                         PRIMARY KEY (`id_Fonct`,`id_ens`),
    KEY `id_ens` (`id_ens`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_admin`
--

DROP TABLE IF EXISTS `personnel_admin`;
CREATE TABLE IF NOT EXISTS `personnel_admin` (
                                                 `id_pers` int NOT NULL,
                                                 `nom_pers` varchar(100) NOT NULL,
    `prenoms_pers` varchar(100) NOT NULL,
    `email_pers` varchar(100) DEFAULT NULL,
    `login_pers` varchar(50) NOT NULL,
    `mdp_pers` varchar(255) NOT NULL,
    PRIMARY KEY (`id_pers`),
    UNIQUE KEY `login_pers` (`login_pers`),
    UNIQUE KEY `email_pers` (`email_pers`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

DROP TABLE IF EXISTS `pister`;
CREATE TABLE IF NOT EXISTS `pister` (
                                        `id_trait` int NOT NULL,
                                        `date_pist` date NOT NULL,
                                        `heure_pist` time NOT NULL,
                                        `acceder` char(1) DEFAULT NULL,
    PRIMARY KEY (`id_trait`,`date_pist`,`heure_pist`)
    ) ;

-- --------------------------------------------------------

--
-- Structure de la table `posseder`
--

DROP TABLE IF EXISTS `posseder`;
CREATE TABLE IF NOT EXISTS `posseder` (
                                          `id_util` int NOT NULL,
                                          `id_GU` int NOT NULL,
                                          `date_poss` date NOT NULL,
                                          PRIMARY KEY (`id_util`,`id_GU`),
    KEY `id_GU` (`id_GU`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

DROP TABLE IF EXISTS `rapport_etudiant`;
CREATE TABLE IF NOT EXISTS `rapport_etudiant` (
                                                  `id_rapport` int NOT NULL,
                                                  `nom_rapport` varchar(100) NOT NULL,
    `titre_rapport` varchar(255) NOT NULL,
    `theme_rapport` text,
    PRIMARY KEY (`id_rapport`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--

DROP TABLE IF EXISTS `rattacher`;
CREATE TABLE IF NOT EXISTS `rattacher` (
                                           `id_GU` int NOT NULL,
                                           `id_trait` int NOT NULL,
                                           PRIMARY KEY (`id_GU`,`id_trait`),
    KEY `id_trait` (`id_trait`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

DROP TABLE IF EXISTS `rendre`;
CREATE TABLE IF NOT EXISTS `rendre` (
                                        `id_CR` int NOT NULL,
                                        `id_ens` int NOT NULL,
                                        `date_CR` date NOT NULL,
                                        PRIMARY KEY (`id_CR`,`id_ens`),
    KEY `id_ens` (`id_ens`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

DROP TABLE IF EXISTS `specialite`;
CREATE TABLE IF NOT EXISTS `specialite` (
                                            `id_Spec` int NOT NULL,
                                            `lib_Spec` varchar(100) NOT NULL,
    PRIMARY KEY (`id_Spec`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

DROP TABLE IF EXISTS `traitement`;
CREATE TABLE IF NOT EXISTS `traitement` (
                                            `id_trait` int NOT NULL,
                                            `lib_trait` varchar(255) NOT NULL,
    PRIMARY KEY (`id_trait`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typeutilisateur`
--

DROP TABLE IF EXISTS `typeutilisateur`;
CREATE TABLE IF NOT EXISTS `typeutilisateur` (
                                                 `id_TU` int NOT NULL,
                                                 `lib_TU` varchar(50) NOT NULL,
    PRIMARY KEY (`id_TU`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

DROP TABLE IF EXISTS `ue`;
CREATE TABLE IF NOT EXISTS `ue` (
                                    `id_UE` int NOT NULL,
                                    `lib_UE` varchar(100) NOT NULL,
    `Credit_UE` int NOT NULL,
    PRIMARY KEY (`id_UE`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
                                             `id_util` int NOT NULL,
                                             `login_util` varchar(50) NOT NULL,
    `mdp_util` varchar(255) NOT NULL,
    PRIMARY KEY (`id_util`),
    UNIQUE KEY `login_util` (`login_util`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `valider`
--

DROP TABLE IF EXISTS `valider`;
CREATE TABLE IF NOT EXISTS `valider` (
                                         `id_ens` int NOT NULL,
                                         `id_rapport` int NOT NULL,
                                         `date_val` date NOT NULL,
                                         `com_val` text,
                                         PRIMARY KEY (`id_ens`,`id_rapport`),
    KEY `id_rapport` (`id_rapport`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
