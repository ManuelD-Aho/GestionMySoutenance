-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : mar. 20 mai 2025 à 01:21
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
                          `lib_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `description_action` text COLLATE utf8mb4_general_ci,
                          `categorie_action` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
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
                                    `lib_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `date_debut` date DEFAULT NULL,
                                    `date_fin` date DEFAULT NULL,
                                    `est_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--

CREATE TABLE `approuver` (
                             `id_personnel_administratif` int NOT NULL,
                             `id_rapport_etudiant` int NOT NULL,
                             `statut_conformite` enum('Conforme','Non Conforme') COLLATE utf8mb4_general_ci NOT NULL,
                             `date_verification_conformite` datetime NOT NULL
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
                                `id_rapport_etudiant` int NOT NULL,
                                `lib_compte_rendu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donner`
--

CREATE TABLE `donner` (
                          `id_enseignant` int NOT NULL,
                          `id_niveau_approbation` int NOT NULL,
                          `date_decision` datetime NOT NULL,
                          `commentaire_decision` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--

CREATE TABLE `ecue` (
                        `id_ecue` int NOT NULL,
                        `lib_ecue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                        `id_ue` int NOT NULL,
                        `credits_ecue` int DEFAULT NULL,
                        `description_ecue` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer`
--

CREATE TABLE `enregistrer` (
                               `id_utilisateur` int NOT NULL,
                               `id_action` int NOT NULL,
                               `date_action` datetime NOT NULL,
                               `adresse_ip` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `user_agent` text COLLATE utf8mb4_general_ci,
                               `id_entite_concernee` int DEFAULT NULL,
                               `type_entite_concernee` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `details_action` json DEFAULT NULL,
                               `session_id_utilisateur` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

CREATE TABLE `enseignant` (
                              `id_enseignant` int NOT NULL,
                              `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `date_naissance` date DEFAULT NULL,
                              `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `email_professionnel` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `id_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
                              `id_entreprise` int NOT NULL,
                              `lib_entreprise` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `secteur_activite` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `adresse_entreprise` text COLLATE utf8mb4_general_ci,
                              `contact_nom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `contact_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `contact_telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
                            `id_etudiant` int NOT NULL,
                            `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_naissance` date DEFAULT NULL,
                            `lieu_naissance` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `pays_naissance` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `nationalite` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `sexe` enum('Masculin','Féminin','Autre') COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `adresse_postale` text COLLATE utf8mb4_general_ci,
                            `ville` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `code_postal` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `photo_profil_chemin` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `id_utilisateur` int NOT NULL,
                            `contact_urgence_nom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_relation` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
                           `id_etudiant` int NOT NULL,
                           `id_enseignant` int NOT NULL,
                           `id_ecue` int NOT NULL,
                           `date_evaluation` datetime NOT NULL,
                           `note` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faire_stage`
--

CREATE TABLE `faire_stage` (
                               `id_entreprise` int NOT NULL,
                               `id_etudiant` int NOT NULL,
                               `date_debut_stage` date NOT NULL,
                               `date_fin_stage` date DEFAULT NULL,
                               `sujet_stage` text COLLATE utf8mb4_general_ci,
                               `nom_tuteur_entreprise` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

CREATE TABLE `fonction` (
                            `id_fonction` int NOT NULL,
                            `lib_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `description_fonction` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
                         `id_grade` int NOT NULL,
                         `lib_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `abreviation_grade` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` int NOT NULL,
                                      `lib_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `description_groupe` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_utilisateur`
--

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `lib_groupe_utilisateur`, `description_groupe`) VALUES
    (1, 'Adminstrateur_systeme', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

CREATE TABLE `inscrire` (
                            `id_etudiant` int NOT NULL,
                            `id_niveau_etude` int NOT NULL,
                            `id_annee_academique` int NOT NULL,
                            `montant_inscription` decimal(10,2) NOT NULL,
                            `date_inscription` datetime NOT NULL,
                            `statut_paiement` enum('Payé','Partiellement Payé','Non Payé','Exonéré') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Non Payé',
                            `date_paiement` datetime DEFAULT NULL,
                            `numero_recu_paiement` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `decision_passage` enum('Admis','Ajourné','Redoublement autorisé','Exclu') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
                           `id_message` int NOT NULL,
                           `code_message` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                           `sujet_message` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                           `lib_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `type_message` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--

CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` int NOT NULL,
                                      `lib_niveau_acces_donne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `description_niveau_acces` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `niveau_acces_donne`
--

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `lib_niveau_acces_donne`, `description_niveau_acces`) VALUES
    (1, '1', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `niveau_approbation`
--

CREATE TABLE `niveau_approbation` (
                                      `id_niveau_approbation` int NOT NULL,
                                      `lib_niveau_approbation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `ordre_workflow` int DEFAULT NULL,
                                      `description_niveau_approb` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` int NOT NULL,
                                `lib_niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `code_niveau_etude` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
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
                           `date_debut_occupation` date NOT NULL,
                           `date_fin_occupation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

CREATE TABLE `personnel_administratif` (
                                           `id_personnel_administratif` int NOT NULL,
                                           `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `date_naissance` date DEFAULT NULL,
                                           `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `email_professionnel` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `date_affectation_service` date DEFAULT NULL,
                                           `responsabilites_cles` text COLLATE utf8mb4_general_ci,
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
                                    `id_etudiant` int NOT NULL,
                                    `nombre_pages` int DEFAULT NULL
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
                            `date_reception` datetime NOT NULL,
                            `lue` tinyint(1) NOT NULL DEFAULT '0',
                            `date_lecture` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

CREATE TABLE `rendre` (
                          `id_enseignant` int NOT NULL,
                          `id_compte_rendu` int NOT NULL,
                          `date_action_sur_pv` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

CREATE TABLE `specialite` (
                              `id_specialite` int NOT NULL,
                              `lib_specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `description_specialite` text COLLATE utf8mb4_general_ci,
                              `code_specialite` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `id_enseignant_specialite` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--

CREATE TABLE `statut_jury` (
                               `id_statut_jury` int NOT NULL,
                               `lib_statut_jury` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `description_statut_jury` text COLLATE utf8mb4_general_ci
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
                                    `lib_type_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `description_type_utilisateur` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_utilisateur`
--

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `lib_type_utilisateur`, `description_type_utilisateur`) VALUES
    (1, 'administrateur', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

CREATE TABLE `ue` (
                      `id_ue` int NOT NULL,
                      `lib_ue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `credits_ue` int DEFAULT NULL,
                      `description_ue` text COLLATE utf8mb4_general_ci
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
                               `photo_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `actif` tinyint(1) NOT NULL DEFAULT '1',
                               `id_niveau_acces_donne` int NOT NULL,
                               `id_groupe_utilisateur` int NOT NULL,
                               `id_type_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `login_utilisateur`, `mot_de_passe`, `date_creation`, `photo_profil`, `actif`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    (1, 'Admin', 'admin111', '2025-05-14 22:53:29', NULL, 1, 1, 1, 1);

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
  ADD KEY `idx_acquerir_enseignant` (`id_enseignant`);

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
  ADD KEY `idx_affecter_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `idx_affecter_statut_jury` (`id_statut_jury`);

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
  ADD KEY `idx_approuver_rapport_etudiant` (`id_rapport_etudiant`);

--
-- Index pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD PRIMARY KEY (`id_enseignant`,`id_specialite`),
  ADD KEY `idx_attribuer_specialite` (`id_specialite`);

--
-- Index pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    ADD PRIMARY KEY (`id_compte_rendu`),
  ADD KEY `fk_compte_rendu_rapport_etudiant` (`id_rapport_etudiant`);

--
-- Index pour la table `donner`
--
ALTER TABLE `donner`
    ADD PRIMARY KEY (`id_enseignant`,`id_niveau_approbation`),
  ADD KEY `idx_donner_niveau_approbation` (`id_niveau_approbation`);

--
-- Index pour la table `ecue`
--
ALTER TABLE `ecue`
    ADD PRIMARY KEY (`id_ecue`),
  ADD KEY `idx_ecue_ue` (`id_ue`);

--
-- Index pour la table `enregistrer`
--
ALTER TABLE `enregistrer`
    ADD PRIMARY KEY (`id_utilisateur`,`id_action`,`date_action`),
  ADD KEY `idx_enregistrer_action` (`id_action`);

--
-- Index pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD PRIMARY KEY (`id_enseignant`),
  ADD UNIQUE KEY `uq_enseignant_id_utilisateur` (`id_utilisateur`);

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
  ADD UNIQUE KEY `uq_etudiant_id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD PRIMARY KEY (`id_etudiant`,`id_enseignant`,`id_ecue`),
  ADD KEY `idx_evaluer_enseignant` (`id_enseignant`),
  ADD KEY `idx_evaluer_ecue` (`id_ecue`);

--
-- Index pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD PRIMARY KEY (`id_entreprise`,`id_etudiant`),
  ADD KEY `idx_faire_stage_etudiant` (`id_etudiant`);

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
  ADD UNIQUE KEY `uq_inscrire_numero_recu` (`numero_recu_paiement`),
  ADD KEY `idx_inscrire_niveau_etude` (`id_niveau_etude`),
  ADD KEY `idx_inscrire_annee_academique` (`id_annee_academique`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
    ADD PRIMARY KEY (`id_message`),
  ADD UNIQUE KEY `uq_message_code` (`code_message`);

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
    ADD PRIMARY KEY (`id_niveau_etude`),
  ADD UNIQUE KEY `uq_niveau_etude_code` (`code_niveau_etude`);

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
  ADD KEY `idx_occuper_enseignant` (`id_enseignant`);

--
-- Index pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD PRIMARY KEY (`id_personnel_administratif`),
  ADD UNIQUE KEY `uq_personnel_id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `pister`
--
ALTER TABLE `pister`
    ADD PRIMARY KEY (`id_utilisateur`,`id_traitement`,`date_pister`),
  ADD KEY `idx_pister_traitement` (`id_traitement`);

--
-- Index pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD PRIMARY KEY (`id_rapport_etudiant`),
  ADD KEY `idx_rapport_etudiant_etudiant` (`id_etudiant`);

--
-- Index pour la table `rattacher`
--
ALTER TABLE `rattacher`
    ADD PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
  ADD KEY `idx_rattacher_traitement` (`id_traitement`);

--
-- Index pour la table `recevoir`
--
ALTER TABLE `recevoir`
    ADD PRIMARY KEY (`id_utilisateur`,`id_notification`,`date_reception`),
  ADD KEY `idx_recevoir_notification` (`id_notification`);

--
-- Index pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD PRIMARY KEY (`id_enseignant`,`id_compte_rendu`),
  ADD KEY `idx_rendre_compte_rendu` (`id_compte_rendu`);

--
-- Index pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD PRIMARY KEY (`id_specialite`),
  ADD UNIQUE KEY `uq_specialite_code` (`code_specialite`),
  ADD KEY `fk_specialite_enseignant` (`id_enseignant_specialite`);

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
  ADD UNIQUE KEY `uq_utilisateur_login` (`login_utilisateur`),
  ADD KEY `idx_utilisateur_niveau_acces` (`id_niveau_acces_donne`),
  ADD KEY `idx_utilisateur_groupe` (`id_groupe_utilisateur`),
  ADD KEY `idx_utilisateur_type` (`id_type_utilisateur`);

--
-- Index pour la table `valider`
--
ALTER TABLE `valider`
    ADD PRIMARY KEY (`id_enseignant`,`id_rapport_etudiant`),
  ADD KEY `idx_valider_rapport_etudiant` (`id_rapport_etudiant`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `acquerir`
--
ALTER TABLE `acquerir`
    ADD CONSTRAINT `fk_acquerir_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_acquerir_grade` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`);

--
-- Contraintes pour la table `affecter`
--
ALTER TABLE `affecter`
    ADD CONSTRAINT `fk_affecter_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_affecter_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`),
  ADD CONSTRAINT `fk_affecter_statut_jury` FOREIGN KEY (`id_statut_jury`) REFERENCES `statut_jury` (`id_statut_jury`);

--
-- Contraintes pour la table `approuver`
--
ALTER TABLE `approuver`
    ADD CONSTRAINT `fk_approuver_personnel` FOREIGN KEY (`id_personnel_administratif`) REFERENCES `personnel_administratif` (`id_personnel_administratif`),
  ADD CONSTRAINT `fk_approuver_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`);

--
-- Contraintes pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD CONSTRAINT `fk_attribuer_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_attribuer_specialite` FOREIGN KEY (`id_specialite`) REFERENCES `specialite` (`id_specialite`);

--
-- Contraintes pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    ADD CONSTRAINT `fk_compte_rendu_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`);

--
-- Contraintes pour la table `donner`
--
ALTER TABLE `donner`
    ADD CONSTRAINT `fk_donner_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_donner_niveau_approbation` FOREIGN KEY (`id_niveau_approbation`) REFERENCES `niveau_approbation` (`id_niveau_approbation`);

--
-- Contraintes pour la table `ecue`
--
ALTER TABLE `ecue`
    ADD CONSTRAINT `fk_ecue_ue` FOREIGN KEY (`id_ue`) REFERENCES `ue` (`id_ue`);

--
-- Contraintes pour la table `enregistrer`
--
ALTER TABLE `enregistrer`
    ADD CONSTRAINT `fk_enregistrer_action` FOREIGN KEY (`id_action`) REFERENCES `action` (`id_action`),
  ADD CONSTRAINT `fk_enregistrer_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD CONSTRAINT `fk_enseignant_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD CONSTRAINT `fk_evaluer_ecue` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`),
  ADD CONSTRAINT `fk_evaluer_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_evaluer_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`);

--
-- Contraintes pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD CONSTRAINT `fk_faire_stage_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`),
  ADD CONSTRAINT `fk_faire_stage_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`);

--
-- Contraintes pour la table `inscrire`
--
ALTER TABLE `inscrire`
    ADD CONSTRAINT `fk_inscrire_annee_academique` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`),
  ADD CONSTRAINT `fk_inscrire_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`),
  ADD CONSTRAINT `fk_inscrire_niveau_etude` FOREIGN KEY (`id_niveau_etude`) REFERENCES `niveau_etude` (`id_niveau_etude`);

--
-- Contraintes pour la table `occuper`
--
ALTER TABLE `occuper`
    ADD CONSTRAINT `fk_occuper_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_occuper_fonction` FOREIGN KEY (`id_fonction`) REFERENCES `fonction` (`id_fonction`);

--
-- Contraintes pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD CONSTRAINT `fk_personnel_administratif_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `pister`
--
ALTER TABLE `pister`
    ADD CONSTRAINT `fk_pister_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`),
  ADD CONSTRAINT `fk_pister_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD CONSTRAINT `fk_rapport_etudiant_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`);

--
-- Contraintes pour la table `rattacher`
--
ALTER TABLE `rattacher`
    ADD CONSTRAINT `fk_rattacher_groupe_utilisateur` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`),
  ADD CONSTRAINT `fk_rattacher_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`);

--
-- Contraintes pour la table `recevoir`
--
ALTER TABLE `recevoir`
    ADD CONSTRAINT `fk_recevoir_notification` FOREIGN KEY (`id_notification`) REFERENCES `notification` (`id_notification`),
  ADD CONSTRAINT `fk_recevoir_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD CONSTRAINT `fk_rendre_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`),
  ADD CONSTRAINT `fk_rendre_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`);

--
-- Contraintes pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD CONSTRAINT `fk_specialite_enseignant` FOREIGN KEY (`id_enseignant_specialite`) REFERENCES `enseignant` (`id_enseignant`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD CONSTRAINT `fk_utilisateur_groupe` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`),
  ADD CONSTRAINT `fk_utilisateur_niveau_acces` FOREIGN KEY (`id_niveau_acces_donne`) REFERENCES `niveau_acces_donne` (`id_niveau_acces_donne`),
  ADD CONSTRAINT `fk_utilisateur_type` FOREIGN KEY (`id_type_utilisateur`) REFERENCES `type_utilisateur` (`id_type_utilisateur`);

--
-- Contraintes pour la table `valider`
--
ALTER TABLE `valider`
    ADD CONSTRAINT `fk_valider_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`),
  ADD CONSTRAINT `fk_valider_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
