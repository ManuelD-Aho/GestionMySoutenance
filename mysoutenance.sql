-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : jeu. 15 mai 2025 à 00:23
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.27

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

CREATE TABLE `acquerir` (
                            `id_grade` int NOT NULL,
                            `id_enseignant` int NOT NULL,
                            `date_acquisition` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
                          `id_action` int NOT NULL,
                          `lib_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--

CREATE TABLE `affecter` (
                            `id_enseignant` int NOT NULL,
                            `id_rapport_etudiant` int NOT NULL,
                            `id_statut_jury` int NOT NULL,
                            `oui` tinyint(1) NOT NULL,
                            `directeur_memoire` tinyint(1) NOT NULL,
                            `date_affectation` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--

CREATE TABLE `annee_academique` (
                                    `id_annee_academique` int NOT NULL,
                                    `lib_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--

CREATE TABLE `approuver` (
                             `id_personnel_administratif` int NOT NULL,
                             `id_rapport_etudiant` int NOT NULL,
                             `date_approbation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attribuer`
--

CREATE TABLE `attribuer` (
                             `id_enseignant` int NOT NULL,
                             `id_specialite` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

CREATE TABLE `compte_rendu` (
                                `id_compte_rendu` int NOT NULL,
                                `lib_compte_rendu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donner`
--

CREATE TABLE `donner` (
                          `id_enseignant` int NOT NULL,
                          `id_niveau_approbation` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--

CREATE TABLE `ecue` (
                        `id_ecue` int NOT NULL,
                        `lib_ecue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                        `id_ue` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer`
--

CREATE TABLE `enregistrer` (
                               `id_utilisateur` int NOT NULL,
                               `id_action` int NOT NULL,
                               `date_action` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

CREATE TABLE `enseignant` (
                              `id_enseignant` int NOT NULL,
                              `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `age` int DEFAULT NULL,
                              `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `id_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
                              `id_entreprise` int NOT NULL,
                              `lib_entreprise` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
                            `id_etudiant` int NOT NULL,
                            `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `age` int DEFAULT NULL,
                            `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `id_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
                           `id_etudiant` int NOT NULL,
                           `id_enseignant` int NOT NULL,
                           `id_ecue` int NOT NULL,
                           `date_evaluation` date NOT NULL,
                           `note` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faire_stage`
--

CREATE TABLE `faire_stage` (
                               `id_entreprise` int NOT NULL,
                               `id_etudiant` int NOT NULL,
                               `date_stage` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

CREATE TABLE `fonction` (
                            `id_fonction` int NOT NULL,
                            `lib_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
                         `id_grade` int NOT NULL,
                         `lib_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` int NOT NULL,
                                      `lib_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_utilisateur`
--

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `lib_groupe_utilisateur`) VALUES
    (1, 'Adminstrateur_systeme');

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

CREATE TABLE `inscrire` (
                            `id_etudiant` int NOT NULL,
                            `id_niveau_etude` int NOT NULL,
                            `id_annee_academique` int NOT NULL,
                            `montant_inscription` decimal(10,2) NOT NULL,
                            `date_inscription` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
                           `id_message` int NOT NULL,
                           `lib_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--

CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` int NOT NULL,
                                      `lib_niveau_acces_donne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `niveau_acces_donne`
--

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `lib_niveau_acces_donne`) VALUES
    (1, '1');

-- --------------------------------------------------------

--
-- Structure de la table `niveau_approbation`
--

CREATE TABLE `niveau_approbation` (
                                      `id_niveau_approbation` int NOT NULL,
                                      `lib_niveau_approbation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` int NOT NULL,
                                `lib_niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
                                `id_notification` int NOT NULL,
                                `lib_notification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `occuper`
--

CREATE TABLE `occuper` (
                           `id_fonction` int NOT NULL,
                           `id_enseignant` int NOT NULL,
                           `date_occupation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

CREATE TABLE `personnel_administratif` (
                                           `id_personnel_administratif` int NOT NULL,
                                           `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `age` int DEFAULT NULL,
                                           `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `id_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

CREATE TABLE `pister` (
                          `id_utilisateur` int NOT NULL,
                          `id_traitement` int NOT NULL,
                          `date_pister` datetime NOT NULL,
                          `acceder` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

CREATE TABLE `rapport_etudiant` (
                                    `id_rapport_etudiant` int NOT NULL,
                                    `libelle_rapport_etudiant` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `id_etudiant` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--

CREATE TABLE `rattacher` (
                             `id_groupe_utilisateur` int NOT NULL,
                             `id_traitement` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recevoir`
--

CREATE TABLE `recevoir` (
                            `id_utilisateur` int NOT NULL,
                            `id_notification` int NOT NULL,
                            `date_reception` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

CREATE TABLE `rendre` (
                          `id_enseignant` int NOT NULL,
                          `id_compte_rendu` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

CREATE TABLE `specialite` (
                              `id_specialite` int NOT NULL,
                              `lib_specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--

CREATE TABLE `statut_jury` (
                               `id_statut_jury` int NOT NULL,
                               `lib_statut_jury` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
                              `id_traitement` int NOT NULL,
                              `lib_trait` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_utilisateur`
--

CREATE TABLE `type_utilisateur` (
                                    `id_type_utilisateur` int NOT NULL,
                                    `lib_type_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_utilisateur`
--

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `lib_type_utilisateur`) VALUES
    (1, 'administrateur');

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

CREATE TABLE `ue` (
                      `id_ue` int NOT NULL,
                      `lib_ue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
                               `id_utilisateur` int NOT NULL,
                               `login_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `date_creation` datetime NOT NULL,
                               `actif` tinyint(1) NOT NULL DEFAULT '1',
                               `id_niveau_acces_donne` int NOT NULL,
                               `id_groupe_utilisateur` int NOT NULL,
                               `id_type_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `login_utilisateur`, `mot_de_passe`, `date_creation`, `actif`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    (1, 'Admin', 'admin111', '2025-05-14 22:53:29', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `valider`
--

CREATE TABLE `valider` (
                           `id_enseignant` int NOT NULL,
                           `id_rapport_etudiant` int NOT NULL,
                           `date_validation` date NOT NULL,
                           `commentaire_validation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `acquerir`
--
ALTER TABLE `acquerir`
    ADD PRIMARY KEY (`id_grade`,`id_enseignant`),
  ADD KEY `id_enseignant` (`id_enseignant`);

--
-- Index pour la table `action`
--
ALTER TABLE `action`
    ADD PRIMARY KEY (`id_action`);

--
-- Index pour la table `affecter`
--
ALTER TABLE `affecter`
    ADD PRIMARY KEY (`id_enseignant`,`id_rapport_etudiant`,`id_statut_jury`),
  ADD KEY `id_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `id_statut_jury` (`id_statut_jury`);

--
-- Index pour la table `annee_academique`
--
ALTER TABLE `annee_academique`
    ADD PRIMARY KEY (`id_annee_academique`);

--
-- Index pour la table `approuver`
--
ALTER TABLE `approuver`
    ADD PRIMARY KEY (`id_personnel_administratif`,`id_rapport_etudiant`),
  ADD KEY `id_rapport_etudiant` (`id_rapport_etudiant`);

--
-- Index pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD PRIMARY KEY (`id_enseignant`,`id_specialite`),
  ADD KEY `id_specialite` (`id_specialite`);

--
-- Index pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    ADD PRIMARY KEY (`id_compte_rendu`);

--
-- Index pour la table `donner`
--
ALTER TABLE `donner`
    ADD PRIMARY KEY (`id_enseignant`,`id_niveau_approbation`),
  ADD KEY `id_niveau_approbation` (`id_niveau_approbation`);

--
-- Index pour la table `ecue`
--
ALTER TABLE `ecue`
    ADD PRIMARY KEY (`id_ecue`),
  ADD KEY `id_ue` (`id_ue`);

--
-- Index pour la table `enregistrer`
--
ALTER TABLE `enregistrer`
    ADD PRIMARY KEY (`id_utilisateur`,`id_action`,`date_action`),
  ADD KEY `id_action` (`id_action`);

--
-- Index pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD PRIMARY KEY (`id_enseignant`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
    ADD PRIMARY KEY (`id_entreprise`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD PRIMARY KEY (`id_etudiant`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD PRIMARY KEY (`id_etudiant`,`id_enseignant`,`id_ecue`),
  ADD KEY `id_enseignant` (`id_enseignant`),
  ADD KEY `id_ecue` (`id_ecue`);

--
-- Index pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD PRIMARY KEY (`id_entreprise`,`id_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `fonction`
--
ALTER TABLE `fonction`
    ADD PRIMARY KEY (`id_fonction`);

--
-- Index pour la table `grade`
--
ALTER TABLE `grade`
    ADD PRIMARY KEY (`id_grade`);

--
-- Index pour la table `groupe_utilisateur`
--
ALTER TABLE `groupe_utilisateur`
    ADD PRIMARY KEY (`id_groupe_utilisateur`);

--
-- Index pour la table `inscrire`
--
ALTER TABLE `inscrire`
    ADD PRIMARY KEY (`id_etudiant`,`id_niveau_etude`,`id_annee_academique`),
  ADD KEY `id_niveau_etude` (`id_niveau_etude`),
  ADD KEY `id_annee_academique` (`id_annee_academique`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
    ADD PRIMARY KEY (`id_message`);

--
-- Index pour la table `niveau_acces_donne`
--
ALTER TABLE `niveau_acces_donne`
    ADD PRIMARY KEY (`id_niveau_acces_donne`);

--
-- Index pour la table `niveau_approbation`
--
ALTER TABLE `niveau_approbation`
    ADD PRIMARY KEY (`id_niveau_approbation`);

--
-- Index pour la table `niveau_etude`
--
ALTER TABLE `niveau_etude`
    ADD PRIMARY KEY (`id_niveau_etude`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
    ADD PRIMARY KEY (`id_notification`);

--
-- Index pour la table `occuper`
--
ALTER TABLE `occuper`
    ADD PRIMARY KEY (`id_fonction`,`id_enseignant`),
  ADD KEY `id_enseignant` (`id_enseignant`);

--
-- Index pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD PRIMARY KEY (`id_personnel_administratif`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `pister`
--
ALTER TABLE `pister`
    ADD PRIMARY KEY (`id_utilisateur`,`id_traitement`,`date_pister`),
  ADD KEY `id_traitement` (`id_traitement`);

--
-- Index pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD PRIMARY KEY (`id_rapport_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `rattacher`
--
ALTER TABLE `rattacher`
    ADD PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
  ADD KEY `id_traitement` (`id_traitement`);

--
-- Index pour la table `recevoir`
--
ALTER TABLE `recevoir`
    ADD PRIMARY KEY (`id_utilisateur`,`id_notification`,`date_reception`),
  ADD KEY `id_notification` (`id_notification`);

--
-- Index pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD PRIMARY KEY (`id_enseignant`,`id_compte_rendu`),
  ADD KEY `id_compte_rendu` (`id_compte_rendu`);

--
-- Index pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD PRIMARY KEY (`id_specialite`);

--
-- Index pour la table `statut_jury`
--
ALTER TABLE `statut_jury`
    ADD PRIMARY KEY (`id_statut_jury`);

--
-- Index pour la table `traitement`
--
ALTER TABLE `traitement`
    ADD PRIMARY KEY (`id_traitement`);

--
-- Index pour la table `type_utilisateur`
--
ALTER TABLE `type_utilisateur`
    ADD PRIMARY KEY (`id_type_utilisateur`);

--
-- Index pour la table `ue`
--
ALTER TABLE `ue`
    ADD PRIMARY KEY (`id_ue`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `login_utilisateur` (`login_utilisateur`),
  ADD KEY `id_niveau_acces_donne` (`id_niveau_acces_donne`),
  ADD KEY `id_groupe_utilisateur` (`id_groupe_utilisateur`),
  ADD KEY `id_type_utilisateur` (`id_type_utilisateur`);

--
-- Index pour la table `valider`
--
ALTER TABLE `valider`
    ADD PRIMARY KEY (`id_enseignant`,`id_rapport_etudiant`),
  ADD KEY `id_rapport_etudiant` (`id_rapport_etudiant`);

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
