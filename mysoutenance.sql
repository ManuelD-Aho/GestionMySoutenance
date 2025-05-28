-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : mer. 28 mai 2025 à 02:01
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
                            `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `date_acquisition` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
                          `id_action` int NOT NULL,
                          `lib_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `description_action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                          `categorie_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--

CREATE TABLE `affecter` (
                            `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `id_rapport_etudiant` int NOT NULL,
                            `id_statut_jury` int NOT NULL,
                            `directeur_memoire` tinyint(1) NOT NULL DEFAULT '0',
                            `date_affectation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
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
                             `numero_personnel_administratif` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                             `id_rapport_etudiant` int NOT NULL,
                             `id_statut_conformite` int NOT NULL,
                             `commentaire_conformite` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                             `date_verification_conformite` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attribuer`
--

CREATE TABLE `attribuer` (
                             `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                             `id_specialite` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

CREATE TABLE `compte_rendu` (
                                `id_compte_rendu` int NOT NULL,
                                `id_rapport_etudiant` int DEFAULT NULL,
                                `type_pv` enum('Individuel','Session') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Individuel',
                                `lib_compte_rendu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `date_creation_pv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `id_statut_pv` int NOT NULL,
                                `id_redacteur` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversation`
--

CREATE TABLE `conversation` (
                                `id_conversation` int NOT NULL,
                                `nom_conversation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                `date_creation_conv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `type_conversation` enum('Direct','Groupe') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Direct'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_passage_ref`
--

CREATE TABLE `decision_passage_ref` (
                                        `id_decision_passage` int NOT NULL,
                                        `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                        `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_passage_ref`
--

INSERT INTO `decision_passage_ref` (`id_decision_passage`, `libelle`, `description`) VALUES
                                                                                         (1, 'Admis', NULL),
                                                                                         (2, 'Ajourné', NULL),
                                                                                         (3, 'Redoublement autorisé', NULL),
                                                                                         (4, 'Exclu', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `decision_validation_pv_ref`
--

CREATE TABLE `decision_validation_pv_ref` (
                                              `id_decision_validation_pv` int NOT NULL,
                                              `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_validation_pv_ref`
--

INSERT INTO `decision_validation_pv_ref` (`id_decision_validation_pv`, `libelle`) VALUES
                                                                                      (1, 'Approuvé'),
                                                                                      (2, 'Modif Demandée');

-- --------------------------------------------------------

--
-- Structure de la table `decision_vote_ref`
--

CREATE TABLE `decision_vote_ref` (
                                     `id_decision_vote` int NOT NULL,
                                     `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_vote_ref`
--

INSERT INTO `decision_vote_ref` (`id_decision_vote`, `libelle`) VALUES
                                                                    (1, 'Approuvé'),
                                                                    (2, 'Refusé'),
                                                                    (3, 'Discussion');

-- --------------------------------------------------------

--
-- Structure de la table `document_soumis`
--

CREATE TABLE `document_soumis` (
                                   `id_document` int NOT NULL,
                                   `id_rapport_etudiant` int NOT NULL,
                                   `chemin_fichier` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `nom_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `type_mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                   `taille_fichier` int DEFAULT NULL,
                                   `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `version` int NOT NULL DEFAULT '1',
                                   `id_type_document` int NOT NULL,
                                   `numero_utilisateur_upload` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donner`
--

CREATE TABLE `donner` (
                          `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                          `id_niveau_approbation` int NOT NULL,
                          `date_decision` datetime NOT NULL,
                          `commentaire_decision` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
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
                        `description_ecue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer`
--

CREATE TABLE `enregistrer` (
                               `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                               `id_action` int NOT NULL,
                               `date_action` datetime NOT NULL,
                               `adresse_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                               `id_entite_concernee` int DEFAULT NULL,
                               `type_entite_concernee` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `details_action` json DEFAULT NULL,
                               `session_id_utilisateur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

CREATE TABLE `enseignant` (
                              `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                              `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `telephone_professionnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `email_professionnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                              `date_naissance` date DEFAULT NULL,
                              `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `pays_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `nationalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `sexe` enum('Masculin','Féminin','Autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `adresse_postale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                              `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `code_postal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `telephone_personnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `email_personnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `photo_profil_chemin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
                              `id_entreprise` int NOT NULL,
                              `lib_entreprise` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `secteur_activite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `adresse_entreprise` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                              `contact_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `contact_telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
                            `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_naissance` date DEFAULT NULL,
                            `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `pays_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `nationalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `sexe` enum('Masculin','Féminin','Autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `adresse_postale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                            `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `code_postal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `photo_profil_chemin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `contact_urgence_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_relation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
                           `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                           `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
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
                               `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                               `date_debut_stage` date NOT NULL,
                               `date_fin_stage` date DEFAULT NULL,
                               `sujet_stage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                               `nom_tuteur_entreprise` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

CREATE TABLE `fonction` (
                            `id_fonction` int NOT NULL,
                            `lib_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `description_fonction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
                         `id_grade` int NOT NULL,
                         `lib_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `abreviation_grade` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` int NOT NULL,
                                      `lib_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `description_groupe_utilisateur` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                      `code_groupe_utilisateur` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Code unique optionnel pour référence programmatique du groupe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_utilisateur`
--

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `lib_groupe_utilisateur`, `description_groupe_utilisateur`, `code_groupe_utilisateur`) VALUES
                                                                                                                                                      (1, 'Adminstrateur_systeme', NULL, NULL),
                                                                                                                                                      (2, 'Etudiants', 'Groupe pour tous les étudiants', NULL),
                                                                                                                                                      (3, 'Personnel_Admin', 'Groupe pour le personnel administratif', NULL),
                                                                                                                                                      (4, 'Enseignants', 'Groupe pour tous les enseignants', NULL),
                                                                                                                                                      (5, 'Commission_Membres', 'Groupe pour les membres de la commission', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `historique_mot_de_passe`
--

CREATE TABLE `historique_mot_de_passe` (
                                           `id_historique_mdp` int NOT NULL,
                                           `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                           `mot_de_passe_hache` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
                                           `date_changement` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

CREATE TABLE `inscrire` (
                            `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `id_niveau_etude` int NOT NULL,
                            `id_annee_academique` int NOT NULL,
                            `montant_inscription` decimal(10,2) NOT NULL,
                            `date_inscription` datetime NOT NULL,
                            `id_statut_paiement` int NOT NULL,
                            `date_paiement` datetime DEFAULT NULL,
                            `numero_recu_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `id_decision_passage` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lecture_message`
--

CREATE TABLE `lecture_message` (
                                   `id_message_chat` int NOT NULL,
                                   `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                   `date_lecture` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
                           `id_message` int NOT NULL,
                           `code_message` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `sujet_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                           `lib_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `type_message` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_chat`
--

CREATE TABLE `message_chat` (
                                `id_message_chat` int NOT NULL,
                                `id_conversation` int NOT NULL,
                                `numero_utilisateur_expediteur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                `contenu_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `date_envoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--

CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` int NOT NULL,
                                      `lib_niveau_acces_donne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `description_niveau_acces_donne` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                      `code_niveau_acces` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Code unique optionnel pour référence programmatique du niveau d''accès'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `niveau_acces_donne`
--

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `lib_niveau_acces_donne`, `description_niveau_acces_donne`, `code_niveau_acces`) VALUES
                                                                                                                                                (1, 'Total', 'Accès complet', NULL),
                                                                                                                                                (2, 'Restreint', 'Accès limité à certaines données', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `niveau_approbation`
--

CREATE TABLE `niveau_approbation` (
                                      `id_niveau_approbation` int NOT NULL,
                                      `lib_niveau_approbation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `ordre_workflow` int DEFAULT NULL,
                                      `description_niveau_approb` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` int NOT NULL,
                                `lib_niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `code_niveau_etude` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
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
                           `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                           `date_debut_occupation` date NOT NULL,
                           `date_fin_occupation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participant_conversation`
--

CREATE TABLE `participant_conversation` (
                                            `id_conversation` int NOT NULL,
                                            `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

CREATE TABLE `personnel_administratif` (
                                           `numero_personnel_administratif` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                           `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `telephone_professionnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `email_professionnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `date_affectation_service` date DEFAULT NULL,
                                           `responsabilites_cles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                           `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                           `date_naissance` date DEFAULT NULL,
                                           `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `pays_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `nationalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `sexe` enum('Masculin','Féminin','Autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `adresse_postale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                           `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `code_postal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `telephone_personnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `email_personnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `photo_profil_chemin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

CREATE TABLE `pister` (
                          `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                          `id_traitement` int NOT NULL,
                          `date_pister` datetime NOT NULL,
                          `acceder` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pv_session_rapport`
--

CREATE TABLE `pv_session_rapport` (
                                      `id_compte_rendu` int NOT NULL,
                                      `id_rapport_etudiant` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

CREATE TABLE `rapport_etudiant` (
                                    `id_rapport_etudiant` int NOT NULL,
                                    `libelle_rapport_etudiant` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                    `resume` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                    `numero_attestation_stage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                    `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                    `nombre_pages` int DEFAULT NULL,
                                    `id_statut_rapport` int NOT NULL,
                                    `date_soumission` datetime DEFAULT NULL,
                                    `date_derniere_modif` datetime DEFAULT NULL
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
                            `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                            `id_notification` int NOT NULL,
                            `date_reception` datetime NOT NULL,
                            `lue` tinyint(1) NOT NULL DEFAULT '0',
                            `date_lecture` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
                               `id_reclamation` int NOT NULL,
                               `numero_carte_etudiant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                               `sujet_reclamation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `description_reclamation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `date_soumission` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `id_statut_reclamation` int NOT NULL,
                               `reponse_reclamation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                               `date_reponse` datetime DEFAULT NULL,
                               `numero_personnel_traitant` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

CREATE TABLE `rendre` (
                          `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
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
                              `description_specialite` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                              `code_specialite` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `numero_enseignant_specialite` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_conformite_ref`
--

CREATE TABLE `statut_conformite_ref` (
                                         `id_statut_conformite` int NOT NULL,
                                         `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_conformite_ref`
--

INSERT INTO `statut_conformite_ref` (`id_statut_conformite`, `libelle`) VALUES
                                                                            (1, 'Conforme'),
                                                                            (2, 'Non Conforme');

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--

CREATE TABLE `statut_jury` (
                               `id_statut_jury` int NOT NULL,
                               `lib_statut_jury` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `description_statut_jury` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_paiement_ref`
--

CREATE TABLE `statut_paiement_ref` (
                                       `id_statut_paiement` int NOT NULL,
                                       `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_paiement_ref`
--

INSERT INTO `statut_paiement_ref` (`id_statut_paiement`, `libelle`, `description`) VALUES
                                                                                       (1, 'Payé', NULL),
                                                                                       (2, 'Partiellement Payé', NULL),
                                                                                       (3, 'Non Payé', NULL),
                                                                                       (4, 'Exonéré', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `statut_pv_ref`
--

CREATE TABLE `statut_pv_ref` (
                                 `id_statut_pv` int NOT NULL,
                                 `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_pv_ref`
--

INSERT INTO `statut_pv_ref` (`id_statut_pv`, `libelle`) VALUES
                                                            (1, 'Brouillon'),
                                                            (2, 'Soumis Validation'),
                                                            (3, 'Validé');

-- --------------------------------------------------------

--
-- Structure de la table `statut_rapport_ref`
--

CREATE TABLE `statut_rapport_ref` (
                                      `id_statut_rapport` int NOT NULL,
                                      `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                      `etape_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_rapport_ref`
--

INSERT INTO `statut_rapport_ref` (`id_statut_rapport`, `libelle`, `description`, `etape_workflow`) VALUES
                                                                                                       (1, 'Brouillon', NULL, 1),
                                                                                                       (2, 'Soumis', NULL, 2),
                                                                                                       (3, 'Non Conforme', NULL, 3),
                                                                                                       (4, 'Conforme', NULL, 4),
                                                                                                       (5, 'En Commission', NULL, 5),
                                                                                                       (6, 'Corrections Demandées', NULL, 6),
                                                                                                       (7, 'Validé', NULL, 7),
                                                                                                       (8, 'Refusé', NULL, 8);

-- --------------------------------------------------------

--
-- Structure de la table `statut_reclamation_ref`
--

CREATE TABLE `statut_reclamation_ref` (
                                          `id_statut_reclamation` int NOT NULL,
                                          `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_reclamation_ref`
--

INSERT INTO `statut_reclamation_ref` (`id_statut_reclamation`, `libelle`, `description`) VALUES
                                                                                             (1, 'Reçue', NULL),
                                                                                             (2, 'En Cours', NULL),
                                                                                             (3, 'Répondue', NULL),
                                                                                             (4, 'Clôturée', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
                              `id_traitement` int NOT NULL,
                              `lib_trait` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `code_traitement` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Code unique pour identifier la permission en PHP (ex: USER_CREATE, REPORT_VALIDATE)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_document_ref`
--

CREATE TABLE `type_document_ref` (
                                     `id_type_document` int NOT NULL,
                                     `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `requis_ou_non` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_document_ref`
--

INSERT INTO `type_document_ref` (`id_type_document`, `libelle`, `requis_ou_non`) VALUES
                                                                                     (1, 'Rapport Principal', 1),
                                                                                     (2, 'Attestation', 1),
                                                                                     (3, 'Résumé', 0),
                                                                                     (4, 'Autre', 0);

-- --------------------------------------------------------

--
-- Structure de la table `type_utilisateur`
--

CREATE TABLE `type_utilisateur` (
                                    `id_type_utilisateur` int NOT NULL,
                                    `lib_type_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `description_type_utilisateur` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                    `code_type_utilisateur` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Code unique optionnel pour référence programmatique du type utilisateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_utilisateur`
--

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `lib_type_utilisateur`, `description_type_utilisateur`, `code_type_utilisateur`) VALUES
                                                                                                                                            (1, 'Administrateur', NULL, NULL),
                                                                                                                                            (2, 'Etudiant', NULL, NULL),
                                                                                                                                            (3, 'Personnel Administratif', NULL, NULL),
                                                                                                                                            (4, 'Enseignant', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

CREATE TABLE `ue` (
                      `id_ue` int NOT NULL,
                      `lib_ue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `credits_ue` int DEFAULT NULL,
                      `description_ue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
                               `numero_utilisateur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                               `login_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `email_principal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `derniere_connexion` datetime DEFAULT NULL,
                               `token_reset_mdp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                               `date_expiration_token_reset` datetime DEFAULT NULL,
                               `token_validation_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                               `email_valide` tinyint(1) NOT NULL DEFAULT '0',
                               `tentatives_connexion_echouees` int UNSIGNED NOT NULL DEFAULT '0',
                               `compte_bloque_jusqua` datetime DEFAULT NULL,
                               `preferences_2fa_active` tinyint(1) NOT NULL DEFAULT '0',
                               `secret_2fa` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `photo_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `statut_compte` enum('actif','inactif','bloque','en_attente_validation','archive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente_validation',
                               `actif` tinyint(1) NOT NULL DEFAULT '1',
                               `id_niveau_acces_donne` int NOT NULL,
                               `id_groupe_utilisateur` int NOT NULL,
                               `id_type_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`numero_utilisateur`, `login_utilisateur`, `email_principal`, `mot_de_passe`, `date_creation`, `derniere_connexion`, `token_reset_mdp`, `date_expiration_token_reset`, `token_validation_email`, `email_valide`, `tentatives_connexion_echouees`, `compte_bloque_jusqua`, `preferences_2fa_active`, `secret_2fa`, `photo_profil`, `statut_compte`, `actif`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    ('USER_ADMIN_001', 'Admin', NULL, 'admin111', '2025-05-14 22:53:29', NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 'en_attente_validation', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `validation_pv`
--

CREATE TABLE `validation_pv` (
                                 `id_compte_rendu` int NOT NULL,
                                 `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                 `id_decision_validation_pv` int NOT NULL,
                                 `date_validation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `commentaire_validation_pv` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `valider`
--

CREATE TABLE `valider` (
                           `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                           `id_rapport_etudiant` int NOT NULL,
                           `date_validation` date NOT NULL,
                           `commentaire_validation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vote_commission`
--

CREATE TABLE `vote_commission` (
                                   `id_vote` int NOT NULL,
                                   `id_rapport_etudiant` int NOT NULL,
                                   `numero_enseignant` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_decision_vote` int NOT NULL,
                                   `commentaire_vote` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                   `date_vote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `tour_vote` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `acquerir`
--
ALTER TABLE `acquerir`
    ADD PRIMARY KEY (`id_grade`,`numero_enseignant`),
  ADD KEY `idx_acquerir_enseignant` (`numero_enseignant`);

--
-- Index pour la table `action`
--
ALTER TABLE `action`
    ADD PRIMARY KEY (`id_action`);

--
-- Index pour la table `affecter`
--
ALTER TABLE `affecter`
    ADD PRIMARY KEY (`numero_enseignant`,`id_rapport_etudiant`,`id_statut_jury`),
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
    ADD PRIMARY KEY (`numero_personnel_administratif`,`id_rapport_etudiant`),
  ADD KEY `idx_approuver_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `fk_approuver_statut_conformite` (`id_statut_conformite`);

--
-- Index pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD PRIMARY KEY (`numero_enseignant`,`id_specialite`),
  ADD KEY `idx_attribuer_specialite` (`id_specialite`);

--
-- Index pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    ADD PRIMARY KEY (`id_compte_rendu`),
  ADD KEY `idx_compte_rendu_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `idx_compte_rendu_redacteur` (`id_redacteur`),
  ADD KEY `fk_compte_rendu_statut_pv` (`id_statut_pv`);

--
-- Index pour la table `conversation`
--
ALTER TABLE `conversation`
    ADD PRIMARY KEY (`id_conversation`);

--
-- Index pour la table `decision_passage_ref`
--
ALTER TABLE `decision_passage_ref`
    ADD PRIMARY KEY (`id_decision_passage`);

--
-- Index pour la table `decision_validation_pv_ref`
--
ALTER TABLE `decision_validation_pv_ref`
    ADD PRIMARY KEY (`id_decision_validation_pv`);

--
-- Index pour la table `decision_vote_ref`
--
ALTER TABLE `decision_vote_ref`
    ADD PRIMARY KEY (`id_decision_vote`);

--
-- Index pour la table `document_soumis`
--
ALTER TABLE `document_soumis`
    ADD PRIMARY KEY (`id_document`),
  ADD KEY `idx_doc_rapport` (`id_rapport_etudiant`),
  ADD KEY `idx_doc_user` (`numero_utilisateur_upload`),
  ADD KEY `fk_doc_type` (`id_type_document`);

--
-- Index pour la table `donner`
--
ALTER TABLE `donner`
    ADD PRIMARY KEY (`numero_enseignant`,`id_niveau_approbation`),
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
    ADD PRIMARY KEY (`numero_utilisateur`,`id_action`,`date_action`),
  ADD KEY `idx_enregistrer_action` (`id_action`);

--
-- Index pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD PRIMARY KEY (`numero_enseignant`),
  ADD UNIQUE KEY `uq_enseignant_numero_utilisateur` (`numero_utilisateur`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
    ADD PRIMARY KEY (`id_entreprise`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD PRIMARY KEY (`numero_carte_etudiant`),
  ADD UNIQUE KEY `uq_etudiant_numero_utilisateur` (`numero_utilisateur`);

--
-- Index pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD PRIMARY KEY (`numero_carte_etudiant`,`numero_enseignant`,`id_ecue`),
  ADD KEY `idx_evaluer_enseignant` (`numero_enseignant`),
  ADD KEY `idx_evaluer_ecue` (`id_ecue`);

--
-- Index pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD PRIMARY KEY (`id_entreprise`,`numero_carte_etudiant`),
  ADD KEY `idx_faire_stage_etudiant` (`numero_carte_etudiant`);

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
    ADD PRIMARY KEY (`id_groupe_utilisateur`),
  ADD UNIQUE KEY `code_groupe_utilisateur` (`code_groupe_utilisateur`),
  ADD KEY `idx_code_groupe_utilisateur` (`code_groupe_utilisateur`);

--
-- Index pour la table `historique_mot_de_passe`
--
ALTER TABLE `historique_mot_de_passe`
    ADD PRIMARY KEY (`id_historique_mdp`),
  ADD KEY `idx_hist_user_mdp` (`numero_utilisateur`);

--
-- Index pour la table `inscrire`
--
ALTER TABLE `inscrire`
    ADD PRIMARY KEY (`numero_carte_etudiant`,`id_niveau_etude`,`id_annee_academique`),
  ADD UNIQUE KEY `uq_inscrire_numero_recu` (`numero_recu_paiement`),
  ADD KEY `idx_inscrire_niveau_etude` (`id_niveau_etude`),
  ADD KEY `idx_inscrire_annee_academique` (`id_annee_academique`),
  ADD KEY `fk_inscrire_statut_paiement` (`id_statut_paiement`),
  ADD KEY `fk_inscrire_decision_passage` (`id_decision_passage`);

--
-- Index pour la table `lecture_message`
--
ALTER TABLE `lecture_message`
    ADD PRIMARY KEY (`id_message_chat`,`numero_utilisateur`),
  ADD KEY `idx_lm_user` (`numero_utilisateur`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
    ADD PRIMARY KEY (`id_message`),
  ADD UNIQUE KEY `uq_message_code` (`code_message`);

--
-- Index pour la table `message_chat`
--
ALTER TABLE `message_chat`
    ADD PRIMARY KEY (`id_message_chat`),
  ADD KEY `idx_mc_conv` (`id_conversation`),
  ADD KEY `idx_mc_user` (`numero_utilisateur_expediteur`);

--
-- Index pour la table `niveau_acces_donne`
--
ALTER TABLE `niveau_acces_donne`
    ADD PRIMARY KEY (`id_niveau_acces_donne`),
  ADD UNIQUE KEY `code_niveau_acces` (`code_niveau_acces`),
  ADD KEY `idx_code_niveau_acces` (`code_niveau_acces`);

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
    ADD PRIMARY KEY (`id_fonction`,`numero_enseignant`),
  ADD KEY `idx_occuper_enseignant` (`numero_enseignant`);

--
-- Index pour la table `participant_conversation`
--
ALTER TABLE `participant_conversation`
    ADD PRIMARY KEY (`id_conversation`,`numero_utilisateur`),
  ADD KEY `idx_pc_user` (`numero_utilisateur`);

--
-- Index pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD PRIMARY KEY (`numero_personnel_administratif`),
  ADD UNIQUE KEY `uq_personnel_numero_utilisateur` (`numero_utilisateur`);

--
-- Index pour la table `pister`
--
ALTER TABLE `pister`
    ADD PRIMARY KEY (`numero_utilisateur`,`id_traitement`,`date_pister`),
  ADD KEY `idx_pister_traitement` (`id_traitement`);

--
-- Index pour la table `pv_session_rapport`
--
ALTER TABLE `pv_session_rapport`
    ADD PRIMARY KEY (`id_compte_rendu`,`id_rapport_etudiant`),
  ADD KEY `idx_pvsr_rapport` (`id_rapport_etudiant`);

--
-- Index pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD PRIMARY KEY (`id_rapport_etudiant`),
  ADD KEY `idx_rapport_etudiant_etudiant` (`numero_carte_etudiant`),
  ADD KEY `fk_rapport_statut` (`id_statut_rapport`);

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
    ADD PRIMARY KEY (`numero_utilisateur`,`id_notification`,`date_reception`),
  ADD KEY `idx_recevoir_notification` (`id_notification`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
    ADD PRIMARY KEY (`id_reclamation`),
  ADD KEY `idx_reclam_etudiant` (`numero_carte_etudiant`),
  ADD KEY `idx_reclam_personnel` (`numero_personnel_traitant`),
  ADD KEY `fk_reclam_statut` (`id_statut_reclamation`);

--
-- Index pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD PRIMARY KEY (`numero_enseignant`,`id_compte_rendu`),
  ADD KEY `idx_rendre_compte_rendu` (`id_compte_rendu`);

--
-- Index pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD PRIMARY KEY (`id_specialite`),
  ADD UNIQUE KEY `uq_specialite_code` (`code_specialite`),
  ADD KEY `fk_specialite_enseignant` (`numero_enseignant_specialite`);

--
-- Index pour la table `statut_conformite_ref`
--
ALTER TABLE `statut_conformite_ref`
    ADD PRIMARY KEY (`id_statut_conformite`);

--
-- Index pour la table `statut_jury`
--
ALTER TABLE `statut_jury`
    ADD PRIMARY KEY (`id_statut_jury`);

--
-- Index pour la table `statut_paiement_ref`
--
ALTER TABLE `statut_paiement_ref`
    ADD PRIMARY KEY (`id_statut_paiement`);

--
-- Index pour la table `statut_pv_ref`
--
ALTER TABLE `statut_pv_ref`
    ADD PRIMARY KEY (`id_statut_pv`);

--
-- Index pour la table `statut_rapport_ref`
--
ALTER TABLE `statut_rapport_ref`
    ADD PRIMARY KEY (`id_statut_rapport`);

--
-- Index pour la table `statut_reclamation_ref`
--
ALTER TABLE `statut_reclamation_ref`
    ADD PRIMARY KEY (`id_statut_reclamation`);

--
-- Index pour la table `traitement`
--
ALTER TABLE `traitement`
    ADD PRIMARY KEY (`id_traitement`),
  ADD UNIQUE KEY `code_traitement` (`code_traitement`),
  ADD KEY `idx_code_traitement` (`code_traitement`);

--
-- Index pour la table `type_document_ref`
--
ALTER TABLE `type_document_ref`
    ADD PRIMARY KEY (`id_type_document`);

--
-- Index pour la table `type_utilisateur`
--
ALTER TABLE `type_utilisateur`
    ADD PRIMARY KEY (`id_type_utilisateur`),
  ADD UNIQUE KEY `code_type_utilisateur` (`code_type_utilisateur`),
  ADD KEY `idx_code_type_utilisateur` (`code_type_utilisateur`);

--
-- Index pour la table `ue`
--
ALTER TABLE `ue`
    ADD PRIMARY KEY (`id_ue`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD PRIMARY KEY (`numero_utilisateur`),
  ADD UNIQUE KEY `uq_utilisateur_login` (`login_utilisateur`),
  ADD UNIQUE KEY `email_principal` (`email_principal`),
  ADD KEY `idx_utilisateur_niveau_acces` (`id_niveau_acces_donne`),
  ADD KEY `idx_utilisateur_groupe` (`id_groupe_utilisateur`),
  ADD KEY `idx_utilisateur_type` (`id_type_utilisateur`),
  ADD KEY `idx_email_principal` (`email_principal`),
  ADD KEY `idx_token_reset_mdp` (`token_reset_mdp`),
  ADD KEY `idx_token_validation_email` (`token_validation_email`);

--
-- Index pour la table `validation_pv`
--
ALTER TABLE `validation_pv`
    ADD PRIMARY KEY (`id_compte_rendu`,`numero_enseignant`),
  ADD KEY `idx_valpv_enseignant` (`numero_enseignant`),
  ADD KEY `fk_valpv_decision` (`id_decision_validation_pv`);

--
-- Index pour la table `valider`
--
ALTER TABLE `valider`
    ADD PRIMARY KEY (`numero_enseignant`,`id_rapport_etudiant`),
  ADD KEY `idx_valider_rapport_etudiant` (`id_rapport_etudiant`);

--
-- Index pour la table `vote_commission`
--
ALTER TABLE `vote_commission`
    ADD PRIMARY KEY (`id_vote`),
  ADD KEY `idx_vote_rapport` (`id_rapport_etudiant`),
  ADD KEY `idx_vote_enseignant` (`numero_enseignant`),
  ADD KEY `fk_vote_decision` (`id_decision_vote`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `action`
--
ALTER TABLE `action`
    MODIFY `id_action` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    MODIFY `id_compte_rendu` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `conversation`
--
ALTER TABLE `conversation`
    MODIFY `id_conversation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `decision_passage_ref`
--
ALTER TABLE `decision_passage_ref`
    MODIFY `id_decision_passage` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `decision_validation_pv_ref`
--
ALTER TABLE `decision_validation_pv_ref`
    MODIFY `id_decision_validation_pv` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `decision_vote_ref`
--
ALTER TABLE `decision_vote_ref`
    MODIFY `id_decision_vote` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `document_soumis`
--
ALTER TABLE `document_soumis`
    MODIFY `id_document` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ecue`
--
ALTER TABLE `ecue`
    MODIFY `id_ecue` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entreprise`
--
ALTER TABLE `entreprise`
    MODIFY `id_entreprise` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fonction`
--
ALTER TABLE `fonction`
    MODIFY `id_fonction` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grade`
--
ALTER TABLE `grade`
    MODIFY `id_grade` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groupe_utilisateur`
--
ALTER TABLE `groupe_utilisateur`
    MODIFY `id_groupe_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `historique_mot_de_passe`
--
ALTER TABLE `historique_mot_de_passe`
    MODIFY `id_historique_mdp` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
    MODIFY `id_message` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message_chat`
--
ALTER TABLE `message_chat`
    MODIFY `id_message_chat` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `niveau_acces_donne`
--
ALTER TABLE `niveau_acces_donne`
    MODIFY `id_niveau_acces_donne` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `niveau_approbation`
--
ALTER TABLE `niveau_approbation`
    MODIFY `id_niveau_approbation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `niveau_etude`
--
ALTER TABLE `niveau_etude`
    MODIFY `id_niveau_etude` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
    MODIFY `id_notification` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    MODIFY `id_rapport_etudiant` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
    MODIFY `id_reclamation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `specialite`
--
ALTER TABLE `specialite`
    MODIFY `id_specialite` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `statut_conformite_ref`
--
ALTER TABLE `statut_conformite_ref`
    MODIFY `id_statut_conformite` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `statut_jury`
--
ALTER TABLE `statut_jury`
    MODIFY `id_statut_jury` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `statut_paiement_ref`
--
ALTER TABLE `statut_paiement_ref`
    MODIFY `id_statut_paiement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `statut_pv_ref`
--
ALTER TABLE `statut_pv_ref`
    MODIFY `id_statut_pv` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `statut_rapport_ref`
--
ALTER TABLE `statut_rapport_ref`
    MODIFY `id_statut_rapport` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `statut_reclamation_ref`
--
ALTER TABLE `statut_reclamation_ref`
    MODIFY `id_statut_reclamation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `traitement`
--
ALTER TABLE `traitement`
    MODIFY `id_traitement` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_document_ref`
--
ALTER TABLE `type_document_ref`
    MODIFY `id_type_document` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `type_utilisateur`
--
ALTER TABLE `type_utilisateur`
    MODIFY `id_type_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `ue`
--
ALTER TABLE `ue`
    MODIFY `id_ue` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `vote_commission`
--
ALTER TABLE `vote_commission`
    MODIFY `id_vote` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `acquerir`
--
ALTER TABLE `acquerir`
    ADD CONSTRAINT `fk_acquerir_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_acquerir_grade` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `affecter`
--
ALTER TABLE `affecter`
    ADD CONSTRAINT `fk_affecter_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_affecter_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_affecter_statut_jury` FOREIGN KEY (`id_statut_jury`) REFERENCES `statut_jury` (`id_statut_jury`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `approuver`
--
ALTER TABLE `approuver`
    ADD CONSTRAINT `fk_approuver_personnel` FOREIGN KEY (`numero_personnel_administratif`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approuver_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approuver_statut_conformite` FOREIGN KEY (`id_statut_conformite`) REFERENCES `statut_conformite_ref` (`id_statut_conformite`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `attribuer`
--
ALTER TABLE `attribuer`
    ADD CONSTRAINT `fk_attribuer_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attribuer_specialite` FOREIGN KEY (`id_specialite`) REFERENCES `specialite` (`id_specialite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `compte_rendu`
--
ALTER TABLE `compte_rendu`
    ADD CONSTRAINT `fk_compte_rendu_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compte_rendu_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compte_rendu_statut_pv` FOREIGN KEY (`id_statut_pv`) REFERENCES `statut_pv_ref` (`id_statut_pv`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `document_soumis`
--
ALTER TABLE `document_soumis`
    ADD CONSTRAINT `fk_doc_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_type` FOREIGN KEY (`id_type_document`) REFERENCES `type_document_ref` (`id_type_document`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_user` FOREIGN KEY (`numero_utilisateur_upload`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `donner`
--
ALTER TABLE `donner`
    ADD CONSTRAINT `fk_donner_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donner_niveau_approbation` FOREIGN KEY (`id_niveau_approbation`) REFERENCES `niveau_approbation` (`id_niveau_approbation`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `ecue`
--
ALTER TABLE `ecue`
    ADD CONSTRAINT `fk_ecue_ue` FOREIGN KEY (`id_ue`) REFERENCES `ue` (`id_ue`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `enregistrer`
--
ALTER TABLE `enregistrer`
    ADD CONSTRAINT `fk_enregistrer_action` FOREIGN KEY (`id_action`) REFERENCES `action` (`id_action`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enregistrer_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD CONSTRAINT `fk_enseignant_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `evaluer`
--
ALTER TABLE `evaluer`
    ADD CONSTRAINT `fk_evaluer_ecue` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evaluer_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evaluer_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `faire_stage`
--
ALTER TABLE `faire_stage`
    ADD CONSTRAINT `fk_faire_stage_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faire_stage_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `historique_mot_de_passe`
--
ALTER TABLE `historique_mot_de_passe`
    ADD CONSTRAINT `fk_hist_utilisateur_mdp` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscrire`
--
ALTER TABLE `inscrire`
    ADD CONSTRAINT `fk_inscrire_annee_academique` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_decision_passage` FOREIGN KEY (`id_decision_passage`) REFERENCES `decision_passage_ref` (`id_decision_passage`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_niveau_etude` FOREIGN KEY (`id_niveau_etude`) REFERENCES `niveau_etude` (`id_niveau_etude`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_statut_paiement` FOREIGN KEY (`id_statut_paiement`) REFERENCES `statut_paiement_ref` (`id_statut_paiement`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `lecture_message`
--
ALTER TABLE `lecture_message`
    ADD CONSTRAINT `fk_lm_message` FOREIGN KEY (`id_message_chat`) REFERENCES `message_chat` (`id_message_chat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_user` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `message_chat`
--
ALTER TABLE `message_chat`
    ADD CONSTRAINT `fk_mc_conv` FOREIGN KEY (`id_conversation`) REFERENCES `conversation` (`id_conversation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_user` FOREIGN KEY (`numero_utilisateur_expediteur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `occuper`
--
ALTER TABLE `occuper`
    ADD CONSTRAINT `fk_occuper_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_occuper_fonction` FOREIGN KEY (`id_fonction`) REFERENCES `fonction` (`id_fonction`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `participant_conversation`
--
ALTER TABLE `participant_conversation`
    ADD CONSTRAINT `fk_pc_conv` FOREIGN KEY (`id_conversation`) REFERENCES `conversation` (`id_conversation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_user` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD CONSTRAINT `fk_personnel_administratif_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `pister`
--
ALTER TABLE `pister`
    ADD CONSTRAINT `fk_pister_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pister_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `pv_session_rapport`
--
ALTER TABLE `pv_session_rapport`
    ADD CONSTRAINT `fk_pvsr_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pvsr_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD CONSTRAINT `fk_rapport_etudiant_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rapport_statut` FOREIGN KEY (`id_statut_rapport`) REFERENCES `statut_rapport_ref` (`id_statut_rapport`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `rattacher`
--
ALTER TABLE `rattacher`
    ADD CONSTRAINT `fk_rattacher_groupe_utilisateur` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rattacher_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `recevoir`
--
ALTER TABLE `recevoir`
    ADD CONSTRAINT `fk_recevoir_notification` FOREIGN KEY (`id_notification`) REFERENCES `notification` (`id_notification`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recevoir_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reclamation`
--
ALTER TABLE `reclamation`
    ADD CONSTRAINT `fk_reclam_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reclam_personnel` FOREIGN KEY (`numero_personnel_traitant`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reclam_statut` FOREIGN KEY (`id_statut_reclamation`) REFERENCES `statut_reclamation_ref` (`id_statut_reclamation`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `rendre`
--
ALTER TABLE `rendre`
    ADD CONSTRAINT `fk_rendre_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rendre_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD CONSTRAINT `fk_specialite_enseignant` FOREIGN KEY (`numero_enseignant_specialite`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD CONSTRAINT `fk_utilisateur_groupe` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_utilisateur_niveau_acces` FOREIGN KEY (`id_niveau_acces_donne`) REFERENCES `niveau_acces_donne` (`id_niveau_acces_donne`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_utilisateur_type` FOREIGN KEY (`id_type_utilisateur`) REFERENCES `type_utilisateur` (`id_type_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `validation_pv`
--
ALTER TABLE `validation_pv`
    ADD CONSTRAINT `fk_valpv_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_valpv_decision` FOREIGN KEY (`id_decision_validation_pv`) REFERENCES `decision_validation_pv_ref` (`id_decision_validation_pv`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_valpv_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `valider`
--
ALTER TABLE `valider`
    ADD CONSTRAINT `fk_valider_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_valider_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `vote_commission`
--
ALTER TABLE `vote_commission`
    ADD CONSTRAINT `fk_vote_decision` FOREIGN KEY (`id_decision_vote`) REFERENCES `decision_vote_ref` (`id_decision_vote`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
