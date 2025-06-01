-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : dim. 01 juin 2025 à 11:17
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
                            `id_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_acquisition` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
                          `id_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `libelle_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `categorie_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--

CREATE TABLE `affecter` (
                            `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_statut_jury` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `directeur_memoire` tinyint(1) NOT NULL DEFAULT '0',
                            `date_affectation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--

CREATE TABLE `annee_academique` (
                                    `id_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `libelle_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `date_debut` date DEFAULT NULL,
                                    `date_fin` date DEFAULT NULL,
                                    `est_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--

CREATE TABLE `approuver` (
                             `numero_personnel_administratif` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `commentaire_conformite` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                             `date_verification_conformite` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attribuer`
--

CREATE TABLE `attribuer` (
                             `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

CREATE TABLE `compte_rendu` (
                                `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                `type_pv` enum('Individuel','Session') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Individuel',
                                `libelle_compte_rendu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `date_creation_pv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `id_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `id_redacteur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversation`
--

CREATE TABLE `conversation` (
                                `id_conversation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `nom_conversation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                `date_creation_conv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `type_conversation` enum('Direct','Groupe') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Direct'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_passage_ref`
--

CREATE TABLE `decision_passage_ref` (
                                        `id_decision_passage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                        `libelle_decision_passage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_passage_ref`
--

INSERT INTO `decision_passage_ref` (`id_decision_passage`, `libelle_decision_passage`) VALUES
                                                                                           ('DP_ADMIS', 'Admis'),
                                                                                           ('DP_AJOURNE', 'Ajourné'),
                                                                                           ('DP_EXCLU', 'Exclu'),
                                                                                           ('DP_REDOUBLE', 'Redoublement autorisé');

-- --------------------------------------------------------

--
-- Structure de la table `decision_validation_pv_ref`
--

CREATE TABLE `decision_validation_pv_ref` (
                                              `id_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `libelle_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_validation_pv_ref`
--

INSERT INTO `decision_validation_pv_ref` (`id_decision_validation_pv`, `libelle_decision_validation_pv`) VALUES
                                                                                                             ('DV_PV_APPROUVE', 'Approuvé'),
                                                                                                             ('DV_PV_MODIF', 'Modif Demandée');

-- --------------------------------------------------------

--
-- Structure de la table `decision_vote_ref`
--

CREATE TABLE `decision_vote_ref` (
                                     `id_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decision_vote_ref`
--

INSERT INTO `decision_vote_ref` (`id_decision_vote`, `libelle_decision_vote`) VALUES
                                                                                  ('DV_APPROUVE', 'Approuvé'),
                                                                                  ('DV_DISCUSSION', 'Discussion'),
                                                                                  ('DV_REFUSE', 'Refusé');

-- --------------------------------------------------------

--
-- Structure de la table `document_soumis`
--

CREATE TABLE `document_soumis` (
                                   `id_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `chemin_fichier` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `nom_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `type_mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                   `taille_fichier` int DEFAULT NULL,
                                   `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `version` int NOT NULL DEFAULT '1',
                                   `id_type_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_utilisateur_upload` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donner`
--

CREATE TABLE `donner` (
                          `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `id_niveau_approbation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `date_decision` datetime NOT NULL,
                          `commentaire_decision` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--

CREATE TABLE `ecue` (
                        `id_ecue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                        `libelle_ecue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                        `id_ue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                        `credits_ecue` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer`
--

CREATE TABLE `enregistrer` (
                               `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `id_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `date_action` datetime NOT NULL,
                               `adresse_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                               `id_entite_concernee` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `type_entite_concernee` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `details_action` json DEFAULT NULL,
                               `session_id_utilisateur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

CREATE TABLE `enseignant` (
                              `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `telephone_professionnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `email_professionnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `date_naissance` date DEFAULT NULL,
                              `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `pays_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `nationalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `sexe` enum('Masculin','Féminin','Autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `adresse_postale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                              `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `code_postal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `telephone_personnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `email_personnel_secondaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
                              `id_entreprise` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_entreprise` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
                            `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
                            `email_contact_secondaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `contact_urgence_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `contact_urgence_relation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
                           `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `id_ecue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `date_evaluation` datetime NOT NULL,
                           `note` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faire_stage`
--

CREATE TABLE `faire_stage` (
                               `id_entreprise` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
                            `id_fonction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `libelle_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
                         `id_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `libelle_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `abreviation_grade` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_utilisateur`
--

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `libelle_groupe_utilisateur`) VALUES
                                                                                             ('GRP_ADMIN_SYS', 'Adminstrateur_systeme'),
                                                                                             ('GRP_COMMISSION', 'Commission_Membres'),
                                                                                             ('GRP_ENSEIGNANT', 'Enseignants'),
                                                                                             ('GRP_ETUDIANT', 'Etudiants'),
                                                                                             ('GRP_PERS_ADMIN', 'Personnel_Admin');

-- --------------------------------------------------------

--
-- Structure de la table `historique_mot_de_passe`
--

CREATE TABLE `historique_mot_de_passe` (
                                           `id_historique_mdp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `mot_de_passe_hache` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `date_changement` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--

CREATE TABLE `inscrire` (
                            `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_niveau_etude` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `montant_inscription` decimal(10,2) NOT NULL,
                            `date_inscription` datetime NOT NULL,
                            `id_statut_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_paiement` datetime DEFAULT NULL,
                            `numero_recu_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            `id_decision_passage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lecture_message`
--

CREATE TABLE `lecture_message` (
                                   `id_message_chat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `date_lecture` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
                           `id_message` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `sujet_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                           `libelle_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `type_message` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_chat`
--

CREATE TABLE `message_chat` (
                                `id_message_chat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `id_conversation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `numero_utilisateur_expediteur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `contenu_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `date_envoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--

CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_niveau_acces_donne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `niveau_acces_donne`
--

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `libelle_niveau_acces_donne`) VALUES
                                                                                             ('ACCES_RESTREINT', 'Restreint'),
                                                                                             ('ACCES_TOTAL', 'Total');

-- --------------------------------------------------------

--
-- Structure de la table `niveau_approbation`
--

CREATE TABLE `niveau_approbation` (
                                      `id_niveau_approbation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_niveau_approbation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `ordre_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--

CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `libelle_niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
                                `id_notification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `libelle_notification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `occuper`
--

CREATE TABLE `occuper` (
                           `id_fonction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `date_debut_occupation` date NOT NULL,
                           `date_fin_occupation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participant_conversation`
--

CREATE TABLE `participant_conversation` (
                                            `id_conversation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                            `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

CREATE TABLE `personnel_administratif` (
                                           `numero_personnel_administratif` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `telephone_professionnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `email_professionnel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `date_affectation_service` date DEFAULT NULL,
                                           `responsabilites_cles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                           `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                           `date_naissance` date DEFAULT NULL,
                                           `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `pays_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `nationalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `sexe` enum('Masculin','Féminin','Autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `adresse_postale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                           `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `code_postal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `telephone_personnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                           `email_personnel_secondaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

CREATE TABLE `pister` (
                          `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `date_pister` datetime NOT NULL,
                          `acceder` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pv_session_rapport`
--

CREATE TABLE `pv_session_rapport` (
                                      `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

CREATE TABLE `rapport_etudiant` (
                                    `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `libelle_rapport_etudiant` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                    `resume` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                    `numero_attestation_stage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                    `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `nombre_pages` int DEFAULT NULL,
                                    `id_statut_rapport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `date_soumission` datetime DEFAULT NULL,
                                    `date_derniere_modif` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--

CREATE TABLE `rattacher` (
                             `id_groupe_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rattacher`
--

INSERT INTO `rattacher` (`id_groupe_utilisateur`, `id_traitement`) VALUES
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_ACCES_MODULE'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_ACCES_SUSPECTS'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_BDD'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_PERF_SERVEUR'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_ETAT_PROCESSUS_AUTO'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_STATS_RAPPORTS'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_STATS_STOCKAGE'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_VOIR_STATS_UTILISATEURS'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_USER_VOIR_LISTE_TOUS'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_VOIR_DASHBOARD_PRINCIPAL');

-- --------------------------------------------------------

--
-- Structure de la table `recevoir`
--

CREATE TABLE `recevoir` (
                            `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_notification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_reception` datetime NOT NULL,
                            `lue` tinyint(1) NOT NULL DEFAULT '0',
                            `date_lecture` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
                               `id_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `sujet_reclamation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `description_reclamation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `date_soumission` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `id_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `reponse_reclamation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                               `date_reponse` datetime DEFAULT NULL,
                               `numero_personnel_traitant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--

CREATE TABLE `rendre` (
                          `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `date_action_sur_pv` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

CREATE TABLE `specialite` (
                              `id_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `numero_enseignant_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_conformite_ref`
--

CREATE TABLE `statut_conformite_ref` (
                                         `id_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                         `libelle_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_conformite_ref`
--

INSERT INTO `statut_conformite_ref` (`id_statut_conformite`, `libelle_statut_conformite`) VALUES
                                                                                              ('CONF_NOK', 'Non Conforme'),
                                                                                              ('CONF_OK', 'Conforme');

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--

CREATE TABLE `statut_jury` (
                               `id_statut_jury` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `libelle_statut_jury` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_paiement_ref`
--

CREATE TABLE `statut_paiement_ref` (
                                       `id_statut_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       `libelle_statut_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_paiement_ref`
--

INSERT INTO `statut_paiement_ref` (`id_statut_paiement`, `libelle_statut_paiement`) VALUES
                                                                                        ('PAIE_EXONERE', 'Exonéré'),
                                                                                        ('PAIE_NOK', 'Non Payé'),
                                                                                        ('PAIE_OK', 'Payé'),
                                                                                        ('PAIE_PARTIEL', 'Partiellement Payé');

-- --------------------------------------------------------

--
-- Structure de la table `statut_pv_ref`
--

CREATE TABLE `statut_pv_ref` (
                                 `id_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `libelle_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_pv_ref`
--

INSERT INTO `statut_pv_ref` (`id_statut_pv`, `libelle_statut_pv`) VALUES
                                                                      ('PV_BROUILLON', 'Brouillon'),
                                                                      ('PV_SOUMIS_VALID', 'Soumis Validation'),
                                                                      ('PV_VALID', 'Validé');

-- --------------------------------------------------------

--
-- Structure de la table `statut_rapport_ref`
--

CREATE TABLE `statut_rapport_ref` (
                                      `id_statut_rapport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_statut_rapport` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `etape_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_rapport_ref`
--

INSERT INTO `statut_rapport_ref` (`id_statut_rapport`, `libelle_statut_rapport`, `etape_workflow`) VALUES
                                                                                                       ('RAP_BROUILLON', 'Brouillon', 1),
                                                                                                       ('RAP_CONF', 'Conforme', 4),
                                                                                                       ('RAP_CORRECT', 'Corrections Demandées', 6),
                                                                                                       ('RAP_EN_COMM', 'En Commission', 5),
                                                                                                       ('RAP_NON_CONF', 'Non Conforme', 3),
                                                                                                       ('RAP_REFUSE', 'Refusé', 8),
                                                                                                       ('RAP_SOUMIS', 'Soumis', 2),
                                                                                                       ('RAP_VALID', 'Validé', 7);

-- --------------------------------------------------------

--
-- Structure de la table `statut_reclamation_ref`
--

CREATE TABLE `statut_reclamation_ref` (
                                          `id_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `libelle_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_reclamation_ref`
--

INSERT INTO `statut_reclamation_ref` (`id_statut_reclamation`, `libelle_statut_reclamation`) VALUES
                                                                                                 ('RECLAM_CLOTUREE', 'Clôturée'),
                                                                                                 ('RECLAM_EN_COURS', 'En Cours'),
                                                                                                 ('RECLAM_RECUE', 'Reçue'),
                                                                                                 ('RECLAM_REPONDUE', 'Répondue');

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
                              `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `code_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `libelle_traitement`, `code_traitement`) VALUES
                                                                                        ('TRAIT_ADMIN_ACCES_MODULE', 'Accès Module Administration Principal', 'ACCÈS_MODULE_ADMINISTRATION_PRINCIPAL'),
                                                                                        ('TRAIT_ADMIN_CONFIG_ACCES_SECTION', 'Accès Section Configuration Système', 'ACCÈS_SECTION_CONFIGURATION_SYSTÈME'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_ACTION_SYSTEME', 'Gérer Référentiel Types d´Action Système (Audit)', 'GÉRER_RÉFÉRENTIEL_TYPES_D´ACTION_SYSTÈME_(AUDIT)'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_ANNEE_ACADEMIQUE', 'Gérer Référentiel Années Académiques', 'GÉRER_RÉFÉRENTIEL_ANNÉES_ACADÉMIQUES'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_DECISION_PASSAGE', 'Gérer Référentiel Décisions de Passage', 'GÉRER_RÉFÉRENTIEL_DÉCISIONS_DE_PASSAGE'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_DECISION_VALIDATION_PV', 'Gérer Référentiel Décisions de Validation de PV', 'GÉRER_RÉFÉRENTIEL_DÉCISIONS_DE_VALIDATION_DE_PV'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_DECISION_VOTE', 'Gérer Référentiel Décisions de Vote Commission', 'GÉRER_RÉFÉRENTIEL_DÉCISIONS_DE_VOTE_COMMISSION'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_ECUE', 'Gérer Référentiel Éléments Constitutifs d´UE (ECUE)', 'GÉRER_RÉFÉRENTIEL_ÉLÉMENTS_CONSTITUTIFS_D´UE_(ECUE)'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_ENTREPRISE', 'Gérer Référentiel Entreprises', 'GÉRER_RÉFÉRENTIEL_ENTREPRISES'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_FONCTION', 'Gérer Référentiel Fonctions', 'GÉRER_RÉFÉRENTIEL_FONCTIONS'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_GRADE', 'Gérer Référentiel Grades', 'GÉRER_RÉFÉRENTIEL_GRADES'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_MESSAGE_MODELE', 'Gérer Référentiel Modèles de Message', 'GÉRER_RÉFÉRENTIEL_MODÈLES_DE_MESSAGE'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_MODELES_DOCUMENTS_PDF', 'Gérer Modèles de Documents PDF Générés', 'GÉRER_MODÈLES_DE_DOCUMENTS_PDF_GÉNÉRÉS'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_MODELES_NOTIFICATIONS_COMM', 'Gérer Modèles de Notifications (Emails, Internes)', 'GÉRER_MODÈLES_DE_NOTIFICATIONS_(EMAILS,_INTERNES)'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_NIVEAU_APPROBATION', 'Gérer Référentiel Niveaux d´Approbation', 'GÉRER_RÉFÉRENTIEL_NIVEAUX_D´APPROBATION'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_NIVEAU_ETUDE', 'Gérer Référentiel Niveaux d´Étude', 'GÉRER_RÉFÉRENTIEL_NIVEAUX_D´ÉTUDE'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_NOTIFICATION_TYPE', 'Gérer Référentiel Types de Notification', 'GÉRER_RÉFÉRENTIEL_TYPES_DE_NOTIFICATION'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_REFERENTIEL_SPECIFIQUE', 'Gérer (CRUD) un Référentiel Spécifique', 'GÉRER_(CRUD)_UN_RÉFÉRENTIEL_SPÉCIFIQUE'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_SPECIALITE', 'Gérer Référentiel Spécialités', 'GÉRER_RÉFÉRENTIEL_SPÉCIALITÉS'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_CONFORMITE', 'Gérer Référentiel Statuts de Conformité', 'GÉRER_RÉFÉRENTIEL_STATUTS_DE_CONFORMITÉ'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_JURY', 'Gérer Référentiel Statuts Jury', 'GÉRER_RÉFÉRENTIEL_STATUTS_JURY'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_PAIEMENT', 'Gérer Référentiel Statuts de Paiement', 'GÉRER_RÉFÉRENTIEL_STATUTS_DE_PAIEMENT'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_PV', 'Gérer Référentiel Statuts de PV', 'GÉRER_RÉFÉRENTIEL_STATUTS_DE_PV'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_RAPPORT', 'Gérer Référentiel Statuts de Rapport', 'GÉRER_RÉFÉRENTIEL_STATUTS_DE_RAPPORT'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_STATUT_RECLAMATION', 'Gérer Référentiel Statuts de Réclamation', 'GÉRER_RÉFÉRENTIEL_STATUTS_DE_RÉCLAMATION'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_TYPE_DOCUMENT', 'Gérer Référentiel Types de Document', 'GÉRER_RÉFÉRENTIEL_TYPES_DE_DOCUMENT'),
                                                                                        ('TRAIT_ADMIN_CONFIG_GERER_UE', 'Gérer Référentiel Unités d´Enseignement (UE)', 'GÉRER_RÉFÉRENTIEL_UNITÉS_D´ENSEIGNEMENT_(UE)'),
                                                                                        ('TRAIT_ADMIN_CONFIG_PARAM_ALERTES_SYSTEME', 'Configurer Paramètres Alertes Système', 'CONFIGURER_PARAMÈTRES_ALERTES_SYSTÈME'),
                                                                                        ('TRAIT_ADMIN_CONFIG_PARAM_CHAT', 'Configurer Paramètres Chat Intégré', 'CONFIGURER_PARAMÈTRES_CHAT_INTÉGRÉ'),
                                                                                        ('TRAIT_ADMIN_CONFIG_PARAM_DATES_LIMITES', 'Configurer Paramètres Dates Limites Système', 'CONFIGURER_PARAMÈTRES_DATES_LIMITES_SYSTÈME'),
                                                                                        ('TRAIT_ADMIN_CONFIG_PARAM_REGLES_VALIDATION', 'Configurer Paramètres Règles de Validation (Conformité, Fichiers)', 'CONFIGURER_PARAMÈTRES_RÈGLES_DE_VALIDATION_(CONFORMITÉ,_FICHIERS)'),
                                                                                        ('TRAIT_ADMIN_CONFIG_PARAM_VOTE_COMMISSION', 'Configurer Paramètres Vote en Ligne Commission', 'CONFIGURER_PARAMÈTRES_VOTE_EN_LIGNE_COMMISSION'),
                                                                                        ('TRAIT_ADMIN_CONFIG_VOIR_LISTE_REFERENTIELS', 'Voir Liste de Tous les Référentiels', 'VOIR_LISTE_DE_TOUS_LES_RÉFÉRENTIELS'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_ACCES_SUSPECTS', 'Voir Alertes Accès Suspects (Dashboard Admin)', 'VOIR_ALERTES_ACCÈS_SUSPECTS_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_BDD', 'Voir Alertes Base de Données (Dashboard Admin)', 'VOIR_ALERTES_BASE_DE_DONNÉES_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_ALERTES_PERF_SERVEUR', 'Voir Alertes Performance Serveur (Dashboard Admin)', 'VOIR_ALERTES_PERFORMANCE_SERVEUR_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_ETAT_PROCESSUS_AUTO', 'Voir État Processus Automatisés (Dashboard Admin)', 'VOIR_ÉTAT_PROCESSUS_AUTOMATISÉS_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_STATS_RAPPORTS', 'Voir Statistiques Rapports (Dashboard Admin)', 'VOIR_STATISTIQUES_RAPPORTS_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_STATS_STOCKAGE', 'Voir Statistiques Stockage (Dashboard Admin)', 'VOIR_STATISTIQUES_STOCKAGE_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_DASHBOARD_VOIR_STATS_UTILISATEURS', 'Voir Statistiques Utilisateurs (Dashboard Admin)', 'VOIR_STATISTIQUES_UTILISATEURS_(DASHBOARD_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_ACCES_SECTION', 'Accès Section Gestion Académique Admin', 'ACCÈS_SECTION_GESTION_ACADÉMIQUE_ADMIN'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONFIGURER_PARAM_EVALUATION', 'Configurer Paramètres des Évaluations/Notes (Admin)', 'CONFIGURER_PARAMÈTRES_DES_ÉVALUATIONS/NOTES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONFIGURER_PARAM_INSCRIPTION', 'Configurer Paramètres des Inscriptions (Admin)', 'CONFIGURER_PARAMÈTRES_DES_INSCRIPTIONS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONFIGURER_PARAM_STAGE', 'Configurer Paramètres des Stages (Admin)', 'CONFIGURER_PARAMÈTRES_DES_STAGES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONSULTER_GLOBAL_INSCRIPTIONS', 'Consulter Globalement les Inscriptions (Admin)', 'CONSULTER_GLOBALEMENT_LES_INSCRIPTIONS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONSULTER_GLOBAL_NOTES', 'Consulter Globalement les Notes (Admin)', 'CONSULTER_GLOBALEMENT_LES_NOTES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_CONSULTER_GLOBAL_STAGES', 'Consulter Globalement les Stages (Admin)', 'CONSULTER_GLOBALEMENT_LES_STAGES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_GERER_ASSOC_ENS_FONCTION', 'Gérer Associations Enseignant-Fonction (Admin)', 'GÉRER_ASSOCIATIONS_ENSEIGNANT-FONCTION_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_GERER_ASSOC_ENS_GRADE', 'Gérer Associations Enseignant-Grade (Admin)', 'GÉRER_ASSOCIATIONS_ENSEIGNANT-GRADE_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_GERER_ASSOC_ENS_SPECIALITE', 'Gérer Associations Enseignant-Spécialité (Admin)', 'GÉRER_ASSOCIATIONS_ENSEIGNANT-SPÉCIALITÉ_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_INTERVENTION_INSCRIPTION', 'Intervenir sur les Inscriptions (Modification/Suppression Admin)', 'INTERVENIR_SUR_LES_INSCRIPTIONS_(MODIFICATION/SUPPRESSION_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_INTERVENTION_NOTE', 'Intervenir sur les Notes (Modification/Suppression Admin)', 'INTERVENIR_SUR_LES_NOTES_(MODIFICATION/SUPPRESSION_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_GESTACAD_INTERVENTION_STAGE', 'Intervenir sur les Stages (Modification/Suppression Admin)', 'INTERVENIR_SUR_LES_STAGES_(MODIFICATION/SUPPRESSION_ADMIN)'),
                                                                                        ('TRAIT_ADMIN_HAB_ACCES_SECTION', 'Accès Section Gestion des Habilitations', 'ACCÈS_SECTION_GESTION_DES_HABILITATIONS'),
                                                                                        ('TRAIT_ADMIN_HAB_CREER_GROUPE', 'Créer Groupe Utilisateur', 'CRÉER_GROUPE_UTILISATEUR'),
                                                                                        ('TRAIT_ADMIN_HAB_CREER_NIVEAU_ACCES', 'Créer Niveau d´Accès aux Données', 'CRÉER_NIVEAU_D´ACCÈS_AUX_DONNÉES'),
                                                                                        ('TRAIT_ADMIN_HAB_CREER_TRAITEMENT', 'Créer Fonctionnalité Système (Traitement)', 'CRÉER_FONCTIONNALITÉ_SYSTÈME_(TRAITEMENT)'),
                                                                                        ('TRAIT_ADMIN_HAB_CREER_TYPE_UTILISATEUR', 'Créer Type Utilisateur (Rôle)', 'CRÉER_TYPE_UTILISATEUR_(RÔLE)'),
                                                                                        ('TRAIT_ADMIN_HAB_GERER_RATTACHEMENTS_GROUPE_TRAITEMENT', 'Gérer Assignations Permissions (Groupe <-> Traitement)', 'GÉRER_ASSIGNATIONS_PERMISSIONS_(GROUPE_<->_TRAITEMENT)'),
                                                                                        ('TRAIT_ADMIN_HAB_MODIFIER_GROUPE', 'Modifier Groupe Utilisateur', 'MODIFIER_GROUPE_UTILISATEUR'),
                                                                                        ('TRAIT_ADMIN_HAB_MODIFIER_NIVEAU_ACCES', 'Modifier Niveau d´Accès aux Données', 'MODIFIER_NIVEAU_D´ACCÈS_AUX_DONNÉES'),
                                                                                        ('TRAIT_ADMIN_HAB_MODIFIER_TRAITEMENT', 'Modifier Fonctionnalité Système (Traitement)', 'MODIFIER_FONCTIONNALITÉ_SYSTÈME_(TRAITEMENT)'),
                                                                                        ('TRAIT_ADMIN_HAB_MODIFIER_TYPE_UTILISATEUR', 'Modifier Type Utilisateur (Rôle)', 'MODIFIER_TYPE_UTILISATEUR_(RÔLE)'),
                                                                                        ('TRAIT_ADMIN_HAB_SUPPRIMER_GROUPE', 'Supprimer Groupe Utilisateur', 'SUPPRIMER_GROUPE_UTILISATEUR'),
                                                                                        ('TRAIT_ADMIN_HAB_SUPPRIMER_NIVEAU_ACCES', 'Supprimer Niveau d´Accès aux Données', 'SUPPRIMER_NIVEAU_D´ACCÈS_AUX_DONNÉES'),
                                                                                        ('TRAIT_ADMIN_HAB_SUPPRIMER_TRAITEMENT', 'Supprimer Fonctionnalité Système (Traitement)', 'SUPPRIMER_FONCTIONNALITÉ_SYSTÈME_(TRAITEMENT)'),
                                                                                        ('TRAIT_ADMIN_HAB_SUPPRIMER_TYPE_UTILISATEUR', 'Supprimer Type Utilisateur (Rôle)', 'SUPPRIMER_TYPE_UTILISATEUR_(RÔLE)'),
                                                                                        ('TRAIT_ADMIN_HAB_VOIR_LISTE_GROUPES', 'Voir Liste des Groupes Utilisateur', 'VOIR_LISTE_DES_GROUPES_UTILISATEUR'),
                                                                                        ('TRAIT_ADMIN_HAB_VOIR_LISTE_NIVEAUX_ACCES', 'Voir Liste des Niveaux d´Accès aux Données', 'VOIR_LISTE_DES_NIVEAUX_D´ACCÈS_AUX_DONNÉES'),
                                                                                        ('TRAIT_ADMIN_HAB_VOIR_LISTE_TRAITEMENTS', 'Voir Liste des Fonctionnalités Système (Traitements)', 'VOIR_LISTE_DES_FONCTIONNALITÉS_SYSTÈME_(TRAITEMENTS)'),
                                                                                        ('TRAIT_ADMIN_HAB_VOIR_LISTE_TYPES_UTILISATEUR', 'Voir Liste des Types Utilisateur (Rôles)', 'VOIR_LISTE_DES_TYPES_UTILISATEUR_(RÔLES)'),
                                                                                        ('TRAIT_ADMIN_REPORT_ACCES_SECTION', 'Accès Section Reporting & Analytique Admin', 'ACCÈS_SECTION_REPORTING_&_ANALYTIQUE_ADMIN'),
                                                                                        ('TRAIT_ADMIN_REPORT_AGENCER_DASHBOARD', 'Agencer Éléments sur Dashboard (Admin)', 'AGENCER_ÉLÉMENTS_SUR_DASHBOARD_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_CHOISIR_KPIS_DASHBOARD', 'Choisir KPIs pour Dashboard (Admin)', 'CHOISIR_KPIS_POUR_DASHBOARD_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_CHOISIR_VISUALISATIONS_DASHBOARD', 'Choisir Types de Visualisation pour Dashboard (Admin)', 'CHOISIR_TYPES_DE_VISUALISATION_POUR_DASHBOARD_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_CONFIGURER_DASHBOARD_ANALYTIQUE', 'Configurer Tableaux de Bord Analytiques (Admin)', 'CONFIGURER_TABLEAUX_DE_BORD_ANALYTIQUES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_EXPORTER_RESULTATS_RAPPORT', 'Exporter Résultats de Rapport Généré (Admin)', 'EXPORTER_RÉSULTATS_DE_RAPPORT_GÉNÉRÉ_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GENERER_RAPPORT_CONTENUS_CORRECTIONS', 'Générer Rapports Tendances Contenus/Corrections (Admin)', 'GÉNÉRER_RAPPORTS_TENDANCES_CONTENUS/CORRECTIONS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GENERER_RAPPORT_DELAIS', 'Générer Rapports Délais de Traitement (Admin)', 'GÉNÉRER_RAPPORTS_DÉLAIS_DE_TRAITEMENT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GENERER_RAPPORT_INSCRIPTIONS_NOTES', 'Générer Rapports Supervision Inscriptions/Notes (Admin)', 'GÉNÉRER_RAPPORTS_SUPERVISION_INSCRIPTIONS/NOTES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GENERER_RAPPORT_PERFORMANCE_ACTEURS', 'Générer Rapports Performance Acteurs (Admin)', 'GÉNÉRER_RAPPORTS_PERFORMANCE_ACTEURS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GENERER_RAPPORT_VALIDATION', 'Générer Rapports Taux de Validation/Conformité (Admin)', 'GÉNÉRER_RAPPORTS_TAUX_DE_VALIDATION/CONFORMITÉ_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_GERER_ACCES_DASHBOARDS', 'Gérer Accès aux Dashboards Analytiques (Admin)', 'GÉRER_ACCÈS_AUX_DASHBOARDS_ANALYTIQUES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_REPORT_SAUVEGARDER_CONFIG_RAPPORT', 'Sauvegarder Configuration de Rapport Personnalisée (Admin)', 'SAUVEGARDER_CONFIGURATION_DE_RAPPORT_PERSONNALISÉE_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_ACCES_SECTION', 'Accès Section Supervision & Maintenance Admin', 'ACCÈS_SECTION_SUPERVISION_&_MAINTENANCE_ADMIN'),
                                                                                        ('TRAIT_ADMIN_SUPERV_ANALYSER_CHARGE_TRAVAIL', 'Analyser Charge de Travail Services (Admin)', 'ANALYSER_CHARGE_DE_TRAVAIL_SERVICES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_APPLIQUER_MAJ_SYSTEME', 'Appliquer Mises à Jour Système/Application (Admin)', 'APPLIQUER_MISES_À_JOUR_SYSTÈME/APPLICATION_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_ARCHIVER_PV_OFFICIEL', 'Archiver Officiellement des PV (Admin)', 'ARCHIVER_OFFICIELLEMENT_DES_PV_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_CONSULTER_PV_VALIDES', 'Consulter Tous les PV Validés (Admin)', 'CONSULTER_TOUS_LES_PV_VALIDÉS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_EXECUTER_SCRIPTS_MAINTENANCE_BDD', 'Exécuter Scripts de Maintenance BDD (Admin)', 'EXÉCUTER_SCRIPTS_DE_MAINTENANCE_BDD_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_GERER_SAUVEGARDES', 'Gérer Sauvegardes de la Base de Données (Admin)', 'GÉRER_SAUVEGARDES_DE_LA_BASE_DE_DONNÉES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_LANCER_RESTAURATION', 'Lancer Restauration de la Base de Données (Admin Critique)', 'LANCER_RESTAURATION_DE_LA_BASE_DE_DONNÉES_(ADMIN_CRITIQUE)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_PURGER_ARCHIVER_NOTIFICATIONS', 'Purger/Archiver les Notifications Système en Masse', 'PURGER/ARCHIVER_LES_NOTIFICATIONS_SYSTÈME_EN_MASSE'),
                                                                                        ('TRAIT_ADMIN_SUPERV_RECHERCHER_JOURNAL_ACCES', 'Rechercher/Filtrer Journal des Accès (Admin)', 'RECHERCHER/FILTRER_JOURNAL_DES_ACCÈS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_RECHERCHER_JOURNAL_ACTIONS', 'Rechercher/Filtrer Journal des Actions (Admin)', 'RECHERCHER/FILTRER_JOURNAL_DES_ACTIONS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_SIGNALER_PV_PUBLICATION', 'Gérer Signalement PV pour Publication Externe (Admin)', 'GÉRER_SIGNALEMENT_PV_POUR_PUBLICATION_EXTERNE_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_STATS_NOTIFICATIONS', 'Voir Statistiques sur les Notifications Système', 'VOIR_STATISTIQUES_SUR_LES_NOTIFICATIONS_SYSTÈME'),
                                                                                        ('TRAIT_ADMIN_SUPERV_UTILISER_OUTIL_EXPORT', 'Utiliser Outil d´Export de Données (Admin)', 'UTILISER_OUTIL_D´EXPORT_DE_DONNÉES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_UTILISER_OUTIL_IMPORT', 'Utiliser Outil d´Import de Données (Admin)', 'UTILISER_OUTIL_D´IMPORT_DE_DONNÉES_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_VOIR_ETAT_SANTE_SERVEUR_APP', 'Voir Indicateurs Santé Serveur/Application (Admin)', 'VOIR_INDICATEURS_SANTÉ_SERVEUR/APPLICATION_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_VOIR_JOURNAL_ACCES', 'Consulter Journal des Accès Système (Admin)', 'CONSULTER_JOURNAL_DES_ACCÈS_SYSTÈME_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_VOIR_JOURNAL_ACTIONS', 'Consulter Journal des Actions Utilisateurs (Admin)', 'CONSULTER_JOURNAL_DES_ACTIONS_UTILISATEURS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_SUPERV_VOIR_TABLEAU_BORD_WORKFLOWS', 'Voir Tableaux de Bord Suivi des Workflows (Admin)', 'VOIR_TABLEAUX_DE_BORD_SUIVI_DES_WORKFLOWS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_CHANGER_STATUT_COMPTE_MASSE', 'Changer Statut de Comptes Utilisateurs en Masse', 'CHANGER_STATUT_DE_COMPTES_UTILISATEURS_EN_MASSE'),
                                                                                        ('TRAIT_ADMIN_USER_CREER_ENSEIGNANT', 'Créer Profil et Compte Enseignant (Admin)', 'CRÉER_PROFIL_ET_COMPTE_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_CREER_ETUDIANT', 'Créer Profil et Compte Étudiant (Admin)', 'CRÉER_PROFIL_ET_COMPTE_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_CREER_PERSADMIN', 'Créer Profil et Compte Personnel Administratif (Admin)', 'CRÉER_PROFIL_ET_COMPTE_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_ENVOYER_NOTIFICATION_MASSE', 'Envoyer Notification Système en Masse aux Utilisateurs', 'ENVOYER_NOTIFICATION_SYSTÈME_EN_MASSE_AUX_UTILISATEURS'),
                                                                                        ('TRAIT_ADMIN_USER_GERER_INSCRIPTIONS_ETUDIANT', 'Gérer Inscriptions d´un Étudiant (Admin)', 'GÉRER_INSCRIPTIONS_D´UN_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_COMPTE_ENSEIGNANT', 'Modifier Compte Utilisateur Enseignant (Admin)', 'MODIFIER_COMPTE_UTILISATEUR_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_COMPTE_ETUDIANT', 'Modifier Compte Utilisateur Étudiant (Admin)', 'MODIFIER_COMPTE_UTILISATEUR_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_COMPTE_PERSADMIN', 'Modifier Compte Utilisateur Personnel Administratif (Admin)', 'MODIFIER_COMPTE_UTILISATEUR_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_PROFIL_ENSEIGNANT', 'Modifier Profil Enseignant (Admin)', 'MODIFIER_PROFIL_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_PROFIL_ETUDIANT', 'Modifier Profil Étudiant (Admin)', 'MODIFIER_PROFIL_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_MODIFIER_PROFIL_PERSADMIN', 'Modifier Profil Personnel Administratif (Admin)', 'MODIFIER_PROFIL_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_RECHERCHER_FILTRER_TOUS', 'Rechercher/Filtrer Tous les Utilisateurs', 'RECHERCHER/FILTRER_TOUS_LES_UTILISATEURS'),
                                                                                        ('TRAIT_ADMIN_USER_REINITIALISER_MDP_ENSEIGNANT', 'Réinitialiser Mot de Passe Enseignant (Admin)', 'RÉINITIALISER_MOT_DE_PASSE_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_REINITIALISER_MDP_ETUDIANT', 'Réinitialiser Mot de Passe Étudiant (Admin)', 'RÉINITIALISER_MOT_DE_PASSE_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_REINITIALISER_MDP_PERSADMIN', 'Réinitialiser Mot de Passe Personnel Administratif (Admin)', 'RÉINITIALISER_MOT_DE_PASSE_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_SUPPRIMER_ENSEIGNANT', 'Supprimer/Désactiver Compte Enseignant (Admin)', 'SUPPRIMER/DÉSACTIVER_COMPTE_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_SUPPRIMER_ETUDIANT', 'Supprimer/Désactiver Compte Étudiant (Admin)', 'SUPPRIMER/DÉSACTIVER_COMPTE_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_SUPPRIMER_PERSADMIN', 'Supprimer/Désactiver Compte Personnel Administratif (Admin)', 'SUPPRIMER/DÉSACTIVER_COMPTE_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_ACTIVITES_ENSEIGNANT', 'Voir Activités d´un Enseignant (Admin)', 'VOIR_ACTIVITÉS_D´UN_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_DETAILS_ENSEIGNANT', 'Voir Détails d´un Enseignant (Admin)', 'VOIR_DÉTAILS_D´UN_ENSEIGNANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_DETAILS_ETUDIANT', 'Voir Détails d´un Étudiant (Admin)', 'VOIR_DÉTAILS_D´UN_ÉTUDIANT_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_DETAILS_PERSADMIN', 'Voir Détails d´un Personnel Administratif (Admin)', 'VOIR_DÉTAILS_D´UN_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_LISTE_ENSEIGNANTS', 'Voir Liste des Enseignants (Admin)', 'VOIR_LISTE_DES_ENSEIGNANTS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_LISTE_ETUDIANTS', 'Voir Liste des Étudiants (Admin)', 'VOIR_LISTE_DES_ÉTUDIANTS_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_LISTE_PERSADMIN', 'Voir Liste du Personnel Administratif (Admin)', 'VOIR_LISTE_DU_PERSONNEL_ADMINISTRATIF_(ADMIN)'),
                                                                                        ('TRAIT_ADMIN_USER_VOIR_LISTE_TOUS', 'Voir Liste de Tous les Utilisateurs', 'VOIR_LISTE_DE_TOUS_LES_UTILISATEURS'),
                                                                                        ('TRAIT_ADMIN_VOIR_DASHBOARD_PRINCIPAL', 'Voir Tableau de Bord Principal Admin', 'VOIR_TABLEAU_DE_BORD_PRINCIPAL_ADMIN');

-- --------------------------------------------------------

--
-- Structure de la table `type_document_ref`
--

CREATE TABLE `type_document_ref` (
                                     `id_type_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_type_document` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `requis_ou_non` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_document_ref`
--

INSERT INTO `type_document_ref` (`id_type_document`, `libelle_type_document`, `requis_ou_non`) VALUES
                                                                                                   ('DOC_ATTEST', 'Attestation', 1),
                                                                                                   ('DOC_AUTRE', 'Autre', 0),
                                                                                                   ('DOC_RAP_MAIN', 'Rapport Principal', 1),
                                                                                                   ('DOC_RESUME', 'Résumé', 0);

-- --------------------------------------------------------

--
-- Structure de la table `type_utilisateur`
--

CREATE TABLE `type_utilisateur` (
                                    `id_type_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `libelle_type_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_utilisateur`
--

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `libelle_type_utilisateur`) VALUES
                                                                                       ('TYPE_ADMIN', 'Administrateur'),
                                                                                       ('TYPE_ENS', 'Enseignant'),
                                                                                       ('TYPE_ETUD', 'Etudiant'),
                                                                                       ('TYPE_PERS_ADMIN', 'Personnel Administratif');

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

CREATE TABLE `ue` (
                      `id_ue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `libelle_ue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `credits_ue` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
                               `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `login_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `email_principal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
                               `secret_2fa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `photo_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `statut_compte` enum('actif','inactif','bloque','en_attente_validation','archive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente_validation',
                               `id_niveau_acces_donne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `id_groupe_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `id_type_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`numero_utilisateur`, `login_utilisateur`, `email_principal`, `mot_de_passe`, `date_creation`, `derniere_connexion`, `token_reset_mdp`, `date_expiration_token_reset`, `token_validation_email`, `email_valide`, `tentatives_connexion_echouees`, `compte_bloque_jusqua`, `preferences_2fa_active`, `secret_2fa`, `photo_profil`, `statut_compte`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    ('ADMIN001', 'Admin', 'admin@example.com', 'hashed_password_for_admin111', '2025-05-14 22:53:29', NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN');

-- --------------------------------------------------------

--
-- Structure de la table `validation_pv`
--

CREATE TABLE `validation_pv` (
                                 `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `id_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `date_validation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `commentaire_validation_pv` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `valider`
--

CREATE TABLE `valider` (
                           `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `date_validation` date NOT NULL,
                           `commentaire_validation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vote_commission`
--

CREATE TABLE `vote_commission` (
                                   `id_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
  ADD UNIQUE KEY `uq_libelle_groupe_utilisateur` (`libelle_groupe_utilisateur`);

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
    ADD PRIMARY KEY (`id_message`);

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
  ADD UNIQUE KEY `uq_libelle_niveau_acces_donne` (`libelle_niveau_acces_donne`);

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
  ADD KEY `fk_rendre_compte_rendu` (`id_compte_rendu`);

--
-- Index pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD PRIMARY KEY (`id_specialite`),
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
  ADD UNIQUE KEY `uq_libelle_type_utilisateur` (`libelle_type_utilisateur`);

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
  ADD UNIQUE KEY `uq_email_principal` (`email_principal`),
  ADD KEY `idx_utilisateur_niveau_acces` (`id_niveau_acces_donne`),
  ADD KEY `idx_utilisateur_groupe` (`id_groupe_utilisateur`),
  ADD KEY `idx_utilisateur_type` (`id_type_utilisateur`),
  ADD KEY `idx_token_reset_mdp` (`token_reset_mdp`),
  ADD KEY `idx_token_validation_email` (`token_validation_email`),
  ADD KEY `idx_statut_compte_utilisateur` (`statut_compte`);

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
