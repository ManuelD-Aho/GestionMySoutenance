-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 10 mai 2025 à 18:11
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mysoutenance`
--

-- --------------------------------------------------------

--
-- Structure de la table `acquerir`
--

DROP TABLE IF EXISTS `acquerir`;
CREATE TABLE IF NOT EXISTS `acquerir` (
                                          `id_grade` int NOT NULL,
                                          `id_enseignant` int NOT NULL,
                                          `date_acquisition` date NOT NULL,
                                          PRIMARY KEY (`id_grade`,`id_enseignant`),
    KEY `id_enseignant` (`id_enseignant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

DROP TABLE IF EXISTS `action`;
CREATE TABLE IF NOT EXISTS `action` (
                                        `id_action` int NOT NULL,
                                        `lib_action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--

DROP TABLE IF EXISTS `affecter`;
CREATE TABLE IF NOT EXISTS `affecter` (
                                          `id_enseignant` int NOT NULL,
                                          `id_rapport_etudiant` int NOT NULL,
                                          `id_statut_jury` int NOT NULL,
                                          `oui` tinyint(1) NOT NULL,
    `directeur_memoire` tinyint(1) NOT NULL,
    `date_affectation` datetime NOT NULL,
    PRIMARY KEY (`id_enseignant`,`id_rapport_etudiant`,`id_statut_jury`),
    KEY `id_rapport_etudiant` (`id_rapport_etudiant`),
    KEY `id_statut_jury` (`id_statut_jury`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--

DROP TABLE IF EXISTS `annee_academique`;
CREATE TABLE IF NOT EXISTS `annee_academique` (
                                                  `id_annee_academique` int NOT NULL,
                                                  `lib_annee_academique` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_annee_academique`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--

DROP TABLE IF EXISTS `approuver`;
CREATE TABLE IF NOT EXISTS `approuver` (
                                           `id_personnel_administratif` int NOT NULL,
                                           `id_rapport_etudiant` int NOT NULL,
                                           `date_approbation` date NOT NULL,
                                           PRIMARY KEY (`id_personnel_administratif`,`id_rapport_etudiant`),
    KEY `id_rapport_etudiant` (`id_rapport_etudiant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attribuer`
--

DROP TABLE IF EXISTS `attribuer`;
CREATE TABLE IF NOT EXISTS `attribuer` (
                                           `id_enseignant` int NOT NULL,
                                           `id_specialite` int NOT NULL,
                                           PRIMARY KEY (`id_enseignant`,`id_specialite`),
    KEY `id_specialite` (`id_specialite`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

DROP TABLE IF EXISTS `compte_rendu`;
CREATE TABLE IF NOT EXISTS `compte_rendu` (
                                              `id_compte_rendu` int NOT NULL,
                                              `lib_compte_rendu` text COLLATE utf8mb4_general_ci NOT NULL,
                                              PRIMARY KEY (`id_compte_rendu`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donner`
--

DROP TABLE IF EXISTS `donner`;
CREATE TABLE IF NOT EXISTS `donner` (
                                        `id_enseignant` int NOT NULL,
                                        `id_niveau_approbation` int NOT NULL,
                                        PRIMARY KEY (`id_enseignant`,`id_niveau_approbation`),
    KEY `id_niveau_approbation` (`id_niveau_approbation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--

DROP TABLE IF EXISTS `ecue`;
CREATE TABLE IF NOT EXISTS `ecue` (
                                      `id_ecue` int NOT NULL,
                                      `lib_ecue` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `id_ue` int NOT NULL,
    PRIMARY KEY (`id_ecue`),
    KEY `id_ue` (`id_ue`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer`
--

DROP TABLE IF EXISTS `enregistrer`;
CREATE TABLE IF NOT EXISTS `enregistrer` (
                                             `id_utilisateur` int NOT NULL,
                                             `id_action` int NOT NULL,
                                             `date_action` datetime NOT NULL,
                                             PRIMARY KEY (`id_utilisateur`,`id_action`,`date_action`),
    KEY `id_action` (`id_action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

DROP TABLE IF EXISTS `enseignant`;
CREATE TABLE IF NOT EXISTS `enseignant` (
                                            `id_enseignant` int NOT NULL,
                                            `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `age` int DEFAULT NULL,
    `telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `id_utilisateur` int NOT NULL,
    PRIMARY KEY (`id_enseignant`),
    UNIQUE KEY `id_utilisateur` (`id_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
CREATE TABLE IF NOT EXISTS `entreprise` (
                                            `id_entreprise` int NOT NULL,
                                            `lib_entreprise` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_entreprise`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
                                          `id_etudiant` int NOT NULL,
                                          `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `age` int DEFAULT NULL,
    `telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `id_utilisateur` int NOT NULL,
    PRIMARY KEY (`id_etudiant`),
    UNIQUE KEY `id_utilisateur` (`id_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

DROP TABLE IF EXISTS `evaluer`;
CREATE TABLE IF NOT EXISTS `evaluer` (
                                         `id_etudiant` int NOT NULL,
                                         `id_enseignant` int NOT NULL,
                                         `id_ecue` int NOT NULL,
                                         `date_evaluation` date NOT NULL,
                                         `note` decimal(5,2) DEFAULT NULL,
    PRIMARY KEY (`id_etudiant`,`id_enseignant`,`id_ecue`),
    KEY `id_enseignant` (`id_enseignant`),
    KEY `id_ecue` (`id_ecue`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faire_stage`
--

DROP TABLE IF EXISTS `faire_stage`;
CREATE TABLE IF NOT EXISTS `faire_stage` (
                                             `id_entreprise` int NOT NULL,
                                             `id_etudiant` int NOT NULL,
                                             `date_stage` date NOT NULL,
                                             PRIMARY KEY (`id_entreprise`,`id_etudiant`),
    KEY `id_etudiant` (`id_etudiant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

DROP TABLE IF EXISTS `fonction`;
CREATE TABLE IF NOT EXISTS `fonction` (
                                          `id_fonction` int NOT NULL,
                                          `lib_fonction` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_fonction`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

DROP TABLE IF EXISTS `grade`;
CREATE TABLE IF NOT EXISTS `grade` (
                                       `id_grade` int NOT NULL,
                                       `lib_grade` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_grade`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

DROP TABLE IF EXISTS `groupe_utilisateur`;
CREATE TABLE IF NOT EXISTS `groupe_utilisateur` (
                                                    `id_groupe_utilisateur` int NOT NULL,
                                                    `lib_groupe_utilisateur` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_groupe_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

DROP TABLE IF EXISTS `inscrire`;
CREATE TABLE IF NOT EXISTS `inscrire` (
                                          `id_etudiant` int NOT NULL,
                                          `id_niveau_etude` int NOT NULL,
                                          `id_annee_academique` int NOT NULL,
                                          `montant_inscription` decimal(10,2) NOT NULL,
    `date_inscription` date NOT NULL,
    PRIMARY KEY (`id_etudiant`,`id_niveau_etude`,`id_annee_academique`),
    KEY `id_niveau_etude` (`id_niveau_etude`),
    KEY `id_annee_academique` (`id_annee_academique`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
                                         `id_message` int NOT NULL,
                                         `lib_message` text COLLATE utf8mb4_general_ci NOT NULL,
                                         PRIMARY KEY (`id_message`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--

DROP TABLE IF EXISTS `niveau_acces_donne`;
CREATE TABLE IF NOT EXISTS `niveau_acces_donne` (
                                                    `id_niveau_acces_donne` int NOT NULL,
                                                    `lib_niveau_acces_donne` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_niveau_acces_donne`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_approbation`
--

DROP TABLE IF EXISTS `niveau_approbation`;
CREATE TABLE IF NOT EXISTS `niveau_approbation` (
                                                    `id_niveau_approbation` int NOT NULL,
                                                    `lib_niveau_approbation` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_niveau_approbation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

DROP TABLE IF EXISTS `niveau_etude`;
CREATE TABLE IF NOT EXISTS `niveau_etude` (
                                              `id_niveau_etude` int NOT NULL,
                                              `lib_niveau_etude` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_niveau_etude`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
                                              `id_notification` int NOT NULL,
                                              `lib_notification` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_notification`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `occuper`
--

DROP TABLE IF EXISTS `occuper`;
CREATE TABLE IF NOT EXISTS `occuper` (
                                         `id_fonction` int NOT NULL,
                                         `id_enseignant` int NOT NULL,
                                         `date_occupation` date NOT NULL,
                                         PRIMARY KEY (`id_fonction`,`id_enseignant`),
    KEY `id_enseignant` (`id_enseignant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

DROP TABLE IF EXISTS `personnel_administratif`;
CREATE TABLE IF NOT EXISTS `personnel_administratif` (
                                                         `id_personnel_administratif` int NOT NULL,
                                                         `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `age` int DEFAULT NULL,
    `telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `id_utilisateur` int NOT NULL,
    PRIMARY KEY (`id_personnel_administratif`),
    UNIQUE KEY `id_utilisateur` (`id_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

DROP TABLE IF EXISTS `pister`;
CREATE TABLE IF NOT EXISTS `pister` (
                                        `id_utilisateur` int NOT NULL,
                                        `id_traitement` int NOT NULL,
                                        `date_pister` datetime NOT NULL,
                                        `acceder` tinyint(1) NOT NULL,
    PRIMARY KEY (`id_utilisateur`,`id_traitement`,`date_pister`),
    KEY `id_traitement` (`id_traitement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

DROP TABLE IF EXISTS `rapport_etudiant`;
CREATE TABLE IF NOT EXISTS `rapport_etudiant` (
                                                  `id_rapport_etudiant` int NOT NULL,
                                                  `libelle_rapport_etudiant` text COLLATE utf8mb4_general_ci NOT NULL,
                                                  `id_etudiant` int NOT NULL,
                                                  PRIMARY KEY (`id_rapport_etudiant`),
    KEY `id_etudiant` (`id_etudiant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--

DROP TABLE IF EXISTS `rattacher`;
CREATE TABLE IF NOT EXISTS `rattacher` (
                                           `id_groupe_utilisateur` int NOT NULL,
                                           `id_traitement` int NOT NULL,
                                           PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
    KEY `id_traitement` (`id_traitement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recevoir`
--

DROP TABLE IF EXISTS `recevoir`;
CREATE TABLE IF NOT EXISTS `recevoir` (
                                          `id_utilisateur` int NOT NULL,
                                          `id_notification` int NOT NULL,
                                          `date_reception` datetime NOT NULL,
                                          PRIMARY KEY (`id_utilisateur`,`id_notification`,`date_reception`),
    KEY `id_notification` (`id_notification`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

DROP TABLE IF EXISTS `rendre`;
CREATE TABLE IF NOT EXISTS `rendre` (
                                        `id_enseignant` int NOT NULL,
                                        `id_compte_rendu` int NOT NULL,
                                        PRIMARY KEY (`id_enseignant`,`id_compte_rendu`),
    KEY `id_compte_rendu` (`id_compte_rendu`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

DROP TABLE IF EXISTS `specialite`;
CREATE TABLE IF NOT EXISTS `specialite` (
                                            `id_specialite` int NOT NULL,
                                            `lib_specialite` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_specialite`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--

DROP TABLE IF EXISTS `statut_jury`;
CREATE TABLE IF NOT EXISTS `statut_jury` (
                                             `id_statut_jury` int NOT NULL,
                                             `lib_statut_jury` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_statut_jury`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

DROP TABLE IF EXISTS `traitement`;
CREATE TABLE IF NOT EXISTS `traitement` (
                                            `id_traitement` int NOT NULL,
                                            `lib_trait` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_traitement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_utilisateur`
--

DROP TABLE IF EXISTS `type_utilisateur`;
CREATE TABLE IF NOT EXISTS `type_utilisateur` (
                                                  `id_type_utilisateur` int NOT NULL,
                                                  `lib_type_utilisateur` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_type_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

DROP TABLE IF EXISTS `ue`;
CREATE TABLE IF NOT EXISTS `ue` (
                                    `id_ue` int NOT NULL,
                                    `lib_ue` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id_ue`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
                                             `id_utilisateur` int NOT NULL,
                                             `login_utilisateur` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `date_creation` datetime NOT NULL,
    `actif` tinyint(1) NOT NULL DEFAULT '1',
    `id_niveau_acces_donne` int NOT NULL,
    `id_groupe_utilisateur` int NOT NULL,
    `id_type_utilisateur` int NOT NULL,
    PRIMARY KEY (`id_utilisateur`),
    UNIQUE KEY `login_utilisateur` (`login_utilisateur`),
    KEY `id_niveau_acces_donne` (`id_niveau_acces_donne`),
    KEY `id_groupe_utilisateur` (`id_groupe_utilisateur`),
    KEY `id_type_utilisateur` (`id_type_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `valider`
--

DROP TABLE IF EXISTS `valider`;
CREATE TABLE IF NOT EXISTS `valider` (
                                         `id_enseignant` int NOT NULL,
                                         `id_rapport_etudiant` int NOT NULL,
                                         `date_validation` date NOT NULL,
                                         `commentaire_validation` text COLLATE utf8mb4_general_ci,
                                         PRIMARY KEY (`id_enseignant`,`id_rapport_etudiant`),
    KEY `id_rapport_etudiant` (`id_rapport_etudiant`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `acquerir`
--
ALTER TABLE `acquerir`
    ADD CONSTRAINT `acquerir_ibfk_1` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`),
  ADD CONSTRAINT `acquerir_ibfk_2` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`);

--
-- Contraintes pour la table `affecter`
--
ALTER TABLE `affecter`
    ADD CONSTRAINT `affecter_ibfk_1` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `affecter_ibfk_2` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`),
  ADD CONSTRAINT `affecter_ibfk_3` FOREIGN KEY (`id_statut_jury`) REFERENCES `statut_jury` (`id_statut_jury`);

--
-- Contraintes pour la table `approuver`
--
ALTER TABLE `approuver`
    ADD CONSTRAINT `approuver_ibfk_1` FOREIGN KEY (`id_personnel_administratif`) REFERENCES `personnel_administratif` (`id_personnel_administratif`),
  ADD CONSTRAINT `approuver_ibfk_2` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`);

--
-- Contraintes pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD CONSTRAINT `attribuer_ibfk_1` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `attribuer_ibfk_2` FOREIGN KEY (`id_specialite`) REFERENCES `specialite` (`id_specialite`);

--
-- Contraintes pour la table `donner`
--
ALTER TABLE `donner`
    ADD CONSTRAINT `donner_ibfk_1` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `donner_ibfk_2` FOREIGN KEY (`id_niveau_approbation`) REFERENCES `niveau_approbation` (`id_niveau_approbation`);

--
-- Contraintes pour la table `ecue`
--
ALTER TABLE `ecue`
    ADD CONSTRAINT `ecue_ibfk_1` FOREIGN KEY (`id_ue`) REFERENCES `ue` (`id_ue`);

--
-- Contraintes pour la table `enregistrer`
--
ALTER TABLE `enregistrer`
    ADD CONSTRAINT `enregistrer_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `enregistrer_ibfk_2` FOREIGN KEY (`id_action`) REFERENCES `action` (`id_action`);

--
-- Contraintes pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD CONSTRAINT `enseignant_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD CONSTRAINT `etudiant_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD CONSTRAINT `evaluer_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`),
  ADD CONSTRAINT `evaluer_ibfk_2` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `evaluer_ibfk_3` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`);

--
-- Contraintes pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD CONSTRAINT `faire_stage_ibfk_1` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`),
  ADD CONSTRAINT `faire_stage_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`);

--
-- Contraintes pour la table `inscrire`
--
ALTER TABLE `inscrire`
    ADD CONSTRAINT `inscrire_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`),
  ADD CONSTRAINT `inscrire_ibfk_2` FOREIGN KEY (`id_niveau_etude`) REFERENCES `niveau_etude` (`id_niveau_etude`),
  ADD CONSTRAINT `inscrire_ibfk_3` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`);

--
-- Contraintes pour la table `occuper`
--
ALTER TABLE `occuper`
    ADD CONSTRAINT `occuper_ibfk_1` FOREIGN KEY (`id_fonction`) REFERENCES `fonction` (`id_fonction`),
  ADD CONSTRAINT `occuper_ibfk_2` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`);

--
-- Contraintes pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD CONSTRAINT `personnel_administratif_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `pister`
--
ALTER TABLE `pister`
    ADD CONSTRAINT `pister_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `pister_ibfk_2` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`);

--
-- Contraintes pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD CONSTRAINT `rapport_etudiant_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`);

--
-- Contraintes pour la table `rattacher`
--
ALTER TABLE `rattacher`
    ADD CONSTRAINT `rattacher_ibfk_1` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`),
  ADD CONSTRAINT `rattacher_ibfk_2` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`);

--
-- Contraintes pour la table `recevoir`
--
ALTER TABLE `recevoir`
    ADD CONSTRAINT `recevoir_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `recevoir_ibfk_2` FOREIGN KEY (`id_notification`) REFERENCES `notification` (`id_notification`);

--
-- Contraintes pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD CONSTRAINT `rendre_ibfk_1` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `rendre_ibfk_2` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`id_niveau_acces_donne`) REFERENCES `niveau_acces_donne` (`id_niveau_acces_donne`),
  ADD CONSTRAINT `utilisateur_ibfk_2` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`),
  ADD CONSTRAINT `utilisateur_ibfk_3` FOREIGN KEY (`id_type_utilisateur`) REFERENCES `type_utilisateur` (`id_type_utilisateur`);

--
-- Contraintes pour la table `valider`
--
ALTER TABLE `valider`
    ADD CONSTRAINT `valider_ibfk_1` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `valider_ibfk_2` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
