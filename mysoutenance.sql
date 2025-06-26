-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : jeu. 26 juin 2025 à 19:03
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

--
-- Déchargement des données de la table `action`
--

INSERT INTO `action` (`id_action`, `libelle_action`, `categorie_action`) VALUES
                                                                             ('ACCES_ASSET_ECHEC', 'ACCES_ASSET_ECHEC', 'Dynamique'),
                                                                             ('ATTRIB_PERM_GROUPE', 'ATTRIB_PERM_GROUPE', 'Dynamique'),
                                                                             ('CHANGEMENT_STATUT_COMPTE', 'CHANGEMENT_STATUT_COMPTE', 'Dynamique'),
                                                                             ('CREATION_ANNEE_ACADEMIQUE', 'CREATION_ANNEE_ACADEMIQUE', 'Dynamique'),
                                                                             ('CREATION_COMPTE', 'CREATION_COMPTE', 'Dynamique'),
                                                                             ('CREATION_GROUPE_UTILISATEUR', 'CREATION_GROUPE_UTILISATEUR', 'Dynamique'),
                                                                             ('CREATION_NIVEAU_ACCES', 'CREATION_NIVEAU_ACCES', 'Dynamique'),
                                                                             ('CREATION_TRAITEMENT', 'CREATION_TRAITEMENT', 'Dynamique'),
                                                                             ('CREATION_TYPE_UTILISATEUR', 'CREATION_TYPE_UTILISATEUR', 'Dynamique'),
                                                                             ('ECHEC_LOGIN', 'ECHEC_LOGIN', 'Dynamique'),
                                                                             ('GENERATION_ID_UNIQUE', 'GENERATION_ID_UNIQUE', 'Dynamique'),
                                                                             ('SUCCES_LOGIN', 'SUCCES_LOGIN', 'Dynamique'),
                                                                             ('SYNCHRONISATION_RBAC', 'SYNCHRONISATION_RBAC', 'Dynamique');

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

--
-- Déchargement des données de la table `annee_academique`
--

INSERT INTO `annee_academique` (`id_annee_academique`, `libelle_annee_academique`, `date_debut`, `date_fin`, `est_active`) VALUES
    ('ANNEE-2025-2026', '2025-2026', '2025-09-01', '2026-08-31', 1);

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
                                `id_redacteur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                `date_limite_approbation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conformite_rapport_details`
--

CREATE TABLE `conformite_rapport_details` (
                                              `id_conformite_detail` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `id_critere` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `statut_validation` enum('Conforme','Non Conforme','Non Applicable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                              `date_verification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
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
-- Structure de la table `critere_conformite_ref`
--

CREATE TABLE `critere_conformite_ref` (
                                          `id_critere` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `libelle_critere` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                          `est_actif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `critere_conformite_ref`
--

INSERT INTO `critere_conformite_ref` (`id_critere`, `libelle_critere`, `description`, `est_actif`) VALUES
                                                                                                       ('BIBLIO_FORMAT', 'Bibliographie formatée', 'La bibliographie respecte-t-elle la norme APA 7ème édition ?', 1),
                                                                                                       ('PAGE_GARDE', 'Respect de la page de garde', 'La page de garde contient-elle le logo, le titre, le nom de l\'étudiant, le nom du tuteur et l\'année académique ?', 1),
                                                                                                       ('PAGINATION', 'Pagination correcte', 'Le document est-il correctement paginé, en commençant après la page de garde ?', 1),
                                                                                                       ('PRESENCE_RESUME', 'Présence du résumé', 'Un résumé (abstract) en français et en anglais est-il présent au début du document ?', 1),
                                                                                                       ('VALIDITE_STAGE', 'Validité du stage associé', 'Le stage associé au rapport a-t-il été administrativement validé par la scolarité ?', 1);

-- --------------------------------------------------------

--
-- Structure de la table `decision_passage_ref`
--

CREATE TABLE `decision_passage_ref` (
                                        `id_decision_passage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                        `libelle_decision_passage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_validation_pv_ref`
--

CREATE TABLE `decision_validation_pv_ref` (
                                              `id_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `libelle_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_vote_ref`
--

CREATE TABLE `decision_vote_ref` (
                                     `id_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `delegation`
--

CREATE TABLE `delegation` (
                              `id_delegation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `id_delegant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `id_delegue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `date_debut` datetime NOT NULL,
                              `date_fin` datetime NOT NULL,
                              `statut` enum('Active','Inactive','Révoquée') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `contexte_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                              `contexte_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_genere`
--

CREATE TABLE `document_genere` (
                                   `id_document_genere` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_type_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `chemin_fichier` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `date_generation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `version` int NOT NULL DEFAULT '1',
                                   `id_entite_concernee` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `type_entite_concernee` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_utilisateur_concerne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
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
                               `id_enregistrement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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

--
-- Déchargement des données de la table `enseignant`
--

INSERT INTO `enseignant` (`numero_enseignant`, `nom`, `prenom`, `telephone_professionnel`, `email_professionnel`, `numero_utilisateur`, `date_naissance`, `lieu_naissance`, `pays_naissance`, `nationalite`, `sexe`, `adresse_postale`, `ville`, `code_postal`, `telephone_personnel`, `email_personnel_secondaire`) VALUES
    ('ENS-2025-0001', 'Dupont', 'Jean', '0611223344', NULL, 'ENS-2025-0001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`numero_carte_etudiant`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `pays_naissance`, `nationalite`, `sexe`, `adresse_postale`, `ville`, `code_postal`, `telephone`, `email_contact_secondaire`, `numero_utilisateur`, `contact_urgence_nom`, `contact_urgence_telephone`, `contact_urgence_relation`) VALUES
    ('ETU-2025-0001', 'Martin', 'Sophie', '2002-05-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0788990011', NULL, 'ETU-2025-0001', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
                           `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `id_ecue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `id_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
                                                                                             ('GRP_ADMIN_SYS', 'Administrateur Système'),
                                                                                             ('GRP_AGENT_CONFORMITE', 'Agent de Conformité'),
                                                                                             ('GRP_ENSEIGNANT', 'Enseignant (Rôle de base)'),
                                                                                             ('GRP_ETUDIANT', 'Étudiant'),
                                                                                             ('GRP_COMMISSION', 'Membre de Commission'),
                                                                                             ('GRP_PERS_ADMIN', 'Personnel Administratif (Rôle de base)'),
                                                                                             ('GRP_RS', 'Responsable Scolarité');

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
-- Structure de la table `matrice_notification_regles`
--

CREATE TABLE `matrice_notification_regles` (
                                               `id_regle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                               `id_action_declencheur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                               `id_groupe_destinataire` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                               `canal_notification` enum('Interne','Email','Tous') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                               `est_active` tinyint(1) NOT NULL DEFAULT '1'
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
                                                                                             ('ACCES_PERSONNEL', 'Accès aux Données Personnelles Uniquement'),
                                                                                             ('ACCES_DEPARTEMENT', 'Accès Niveau Département'),
                                                                                             ('ACCES_TOTAL', 'Accès Total (Admin)');

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
                                `libelle_notification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
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
-- Structure de la table `parametres_systeme`
--

CREATE TABLE `parametres_systeme` (
                                      `cle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `valeur` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                      `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                      `type` enum('string','integer','boolean','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parametres_systeme`
--

INSERT INTO `parametres_systeme` (`cle`, `valeur`, `description`, `type`) VALUES
                                                                              ('LOCKOUT_TIME_MINUTES', '30', 'Durée en minutes du blocage de compte après trop de tentatives.', 'integer'),
                                                                              ('MAX_LOGIN_ATTEMPTS', '5', 'Nombre maximum de tentatives de connexion avant blocage du compte.', 'integer'),
                                                                              ('PASSWORD_MIN_LENGTH', '8', 'Longueur minimale requise pour les mots de passe.', 'integer'),
                                                                              ('SMTP_FROM_EMAIL', 'no-reply@gestionsoutenance.com', 'Adresse email de l\'expéditeur par défaut.', 'string'),
                                                                              ('SMTP_FROM_NAME', 'GestionMySoutenance', 'Nom de l\'expéditeur par défaut.', 'string'),
                                                                              ('SMTP_HOST', 'smtp.example.com', 'Hôte du serveur SMTP pour l\'envoi d\'emails.', 'string'),
                                                                              ('SMTP_PASS', 'password', 'Mot de passe pour l\'authentification SMTP.', 'string'),
                                                                              ('SMTP_PORT', '587', 'Port du serveur SMTP.', 'integer'),
                                                                              ('SMTP_SECURE', 'tls', 'Type de chiffrement SMTP (tls, ssl, ou vide).', 'string'),
                                                                              ('SMTP_USER', 'user@example.com', 'Nom d\'utilisateur pour l\'authentification SMTP.', 'string'),
                                                                              ('UPLOADS_PATH_DOCUMENTS_GENERES', '/var/www/html/Public/uploads/documents_generes/', 'Chemin de stockage des documents PDF générés.', 'string'),
                                                                              ('UPLOADS_PATH_PROFILE_PICTURES', '/var/www/html/Public/uploads/profile_pictures/', 'Chemin de stockage des photos de profil.', 'string');

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
-- Structure de la table `penalite`
--

CREATE TABLE `penalite` (
                            `id_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_annee_academique` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `type_penalite` enum('Financière','Administrative') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `montant_du` decimal(10,2) DEFAULT NULL,
                            `motif` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                            `id_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            `date_regularisation` datetime DEFAULT NULL,
                            `numero_personnel_traitant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
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

--
-- Déchargement des données de la table `personnel_administratif`
--

INSERT INTO `personnel_administratif` (`numero_personnel_administratif`, `nom`, `prenom`, `telephone_professionnel`, `email_professionnel`, `date_affectation_service`, `responsabilites_cles`, `numero_utilisateur`, `date_naissance`, `lieu_naissance`, `pays_naissance`, `nationalite`, `sexe`, `adresse_postale`, `ville`, `code_postal`, `telephone_personnel`, `email_personnel_secondaire`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                       ('ADM-2025-0001', 'Scolarité', 'Responsable', '0123456789', NULL, NULL, NULL, 'ADM-2025-0001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                                                                       ('ADM-2025-0002', 'Conformité', 'Agent', '0123456788', NULL, NULL, NULL, 'ADM-2025-0002', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--

CREATE TABLE `pister` (
                          `id_piste` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
-- Structure de la table `queue_jobs`
--

CREATE TABLE `queue_jobs` (
                              `id` bigint UNSIGNED NOT NULL,
                              `job_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                              `status` enum('pending','processing','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
                              `attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
                              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              `started_at` timestamp NULL DEFAULT NULL,
                              `completed_at` timestamp NULL DEFAULT NULL,
                              `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--

CREATE TABLE `rapport_etudiant` (
                                    `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `libelle_rapport_etudiant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
-- Structure de la table `rapport_modele`
--

CREATE TABLE `rapport_modele` (
                                  `id_modele` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                  `nom_modele` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                  `version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                  `statut` enum('Brouillon','Publié','Archivé') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_modele_assignation`
--

CREATE TABLE `rapport_modele_assignation` (
                                              `id_modele` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `id_niveau_etude` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_modele_section`
--

CREATE TABLE `rapport_modele_section` (
                                          `id_section_modele` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `id_modele` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `titre_section` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `contenu_par_defaut` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                          `ordre` int NOT NULL
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
                                                                       ('GRP_ADMIN_SYS', 'MENU_ADMINISTRATION'),
                                                                       ('GRP_ADMIN_SYS', 'MENU_DASHBOARDS'),
                                                                       ('GRP_ADMIN_SYS', 'MENU_GESTION_COMPTES'),
                                                                       ('GRP_ADMIN_SYS', 'MENU_RAPPORT_ETUDIANT'),
                                                                       ('GRP_ETUDIANT', 'MENU_RAPPORT_ETUDIANT'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_ACCEDER'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER'),
                                                                       ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER'),
                                                                       ('GRP_COMMISSION', 'TRAIT_COMMISSION_DASHBOARD_ACCEDER'),
                                                                       ('GRP_COMMISSION', 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER'),
                                                                       ('GRP_ETUDIANT', 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER'),
                                                                       ('GRP_ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE'),
                                                                       ('GRP_ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SUIVRE'),
                                                                       ('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER'),
                                                                       ('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER'),
                                                                       ('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
                                                                       ('GRP_RS', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
                                                                       ('GRP_RS', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER'),
                                                                       ('GRP_RS', 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER');

-- --------------------------------------------------------

--
-- Structure de la table `recevoir`
--

CREATE TABLE `recevoir` (
                            `id_reception` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
-- Structure de la table `section_rapport`
--

CREATE TABLE `section_rapport` (
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `titre_section` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `contenu_section` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                   `ordre` int NOT NULL DEFAULT '0',
                                   `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `date_derniere_modif` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sequences`
--

CREATE TABLE `sequences` (
                             `nom_sequence` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `annee` int NOT NULL,
                             `valeur_actuelle` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sequences`
--

INSERT INTO `sequences` (`nom_sequence`, `annee`, `valeur_actuelle`) VALUES
                                                                         ('ADM', 2025, 2),
                                                                         ('ENS', 2025, 1),
                                                                         ('ETU', 2025, 1),
                                                                         ('SYS', 2025, 1);

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
                            `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `session_data` longblob NOT NULL,
                            `session_last_activity` int UNSIGNED NOT NULL,
                            `session_lifetime` int UNSIGNED NOT NULL,
                            `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`session_id`, `session_data`, `session_last_activity`, `session_lifetime`, `user_id`) VALUES
                                                                                                                  ('204fd9b600c6d41e21ee0348dd700e95', 0x757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735303935393830363b757365725f646174617c613a32303a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a393a2261646d696e5f737973223b733a31353a22656d61696c5f7072696e636970616c223b733a33313a2261646d696e2e7379734067657374696f6e736f7574656e616e63652e646576223b733a31323a226d6f745f64655f7061737365223b733a36303a2224327924313024597a37636666594970713537342f424965643837522e55563835462e474739564e46304a4f5834625463732f6b54424f78654f5143223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30362d32352031353a31343a3130223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30362d32362031383a31363a3432223b733a31353a22746f6b656e5f72657365745f6d6470223b4e3b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a32323a22746f6b656e5f76616c69646174696f6e5f656d61696c223b733a36343a2265386562303334356131656133663838353864326630333936613365666165383535326463623436396436613361366231633639613363643532333537376663223b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31303a227365637265745f326661223b4e3b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b733a363a2270726f66696c223b613a303a7b7d7d757365725f7065726d697373696f6e737c613a333a7b693a303b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a313b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a323b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d637372665f746f6b656e7c613a323a7b733a353a2276616c7565223b733a36343a2235623033643730643130303662386430343635633536633038373532356461383165326666663437356161333430626338613838316664653266366438396563223b733a31303a22657870697265735f6174223b693a313735303936323936313b7d, 1750959806, 3600, 'SYS-2025-0001'),
                                                                                                                  ('48a9c24bceac4bce2ede9384efa155f1', 0x637372665f746f6b656e7c613a323a7b733a353a2276616c7565223b733a36343a2266343661346134623339316565636434643732353065666162646166356566613939666639313236383735633766326232363837393031316438373361336639223b733a31303a22657870697265735f6174223b693a313735303839383134373b7d, 1750894547, 1440, NULL),
                                                                                                                  ('688dfce7e884b5d1ee845a341e52d902', '', 1750884748, 1440, NULL),
                                                                                                                  ('71d5286f7891ce152c6acbc696bd9da1', 0x637372665f746f6b656e7c613a323a7b733a353a2276616c7565223b733a36343a2238383636666535616164373838666231303838343863623730396232336563663665353466393732303534376534373737633034633232343538663435646234223b733a31303a22657870697265735f6174223b693a313735303836313031323b7d, 1750857665, 1440, NULL),
                                                                                                                  ('8541c295e774b21384373157918ec820', 0x637372665f746f6b656e7c613a323a7b733a353a2276616c7565223b733a36343a2230383931333134393836616630396637626433383064306166643537346566663737386638303139373239626662356335393762643561303632313662616637223b733a31303a22657870697265735f6174223b693a313735303837353939393b7d, 1750872399, 1440, NULL),
                                                                                                                  ('fd8a04fd03d33ebcb23f54661c4cccc1', 0x757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735303935343630323b757365725f646174617c613a32303a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a393a2261646d696e5f737973223b733a31353a22656d61696c5f7072696e636970616c223b733a33313a2261646d696e2e7379734067657374696f6e736f7574656e616e63652e646576223b733a31323a226d6f745f64655f7061737365223b733a36303a2224327924313024597a37636666594970713537342f424965643837522e55563835462e474739564e46304a4f5834625463732f6b54424f78654f5143223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30362d32352031353a31343a3130223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30362d32362031373a35313a3433223b733a31353a22746f6b656e5f72657365745f6d6470223b4e3b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a32323a22746f6b656e5f76616c69646174696f6e5f656d61696c223b733a36343a2265386562303334356131656133663838353864326630333936613365666165383535326463623436396436613361366231633639613363643532333537376663223b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31303a227365637265745f326661223b4e3b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b733a363a2270726f66696c223b613a303a7b7d7d757365725f7065726d697373696f6e737c613a333a7b693a303b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a313b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a323b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d637372665f746f6b656e7c613a323a7b733a353a2276616c7565223b733a36343a2232353032343734313530623539626538303434653566306333356361323830666561363139316532363438316631393663363533616132343361633235343566223b733a31303a22657870697265735f6174223b693a313735303935383230323b7d, 1750954603, 3600, 'SYS-2025-0001');

-- --------------------------------------------------------

--
-- Structure de la table `session_rapport`
--

CREATE TABLE `session_rapport` (
                                   `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session_validation`
--

CREATE TABLE `session_validation` (
                                      `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `nom_session` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `date_debut_session` datetime DEFAULT NULL,
                                      `date_fin_prevue` datetime DEFAULT NULL,
                                      `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                      `id_president_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `mode_session` enum('presentiel','en_ligne') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `statut_session` enum('planifiee','en_cours','cloturee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'planifiee',
                                      `nombre_votants_requis` int DEFAULT NULL
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

-- --------------------------------------------------------

--
-- Structure de la table `statut_penalite_ref`
--

CREATE TABLE `statut_penalite_ref` (
                                       `id_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       `libelle_statut_penalite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_pv_ref`
--

CREATE TABLE `statut_pv_ref` (
                                 `id_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `libelle_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_rapport_ref`
--

CREATE TABLE `statut_rapport_ref` (
                                      `id_statut_rapport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_statut_rapport` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `etape_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_reclamation_ref`
--

CREATE TABLE `statut_reclamation_ref` (
                                          `id_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `libelle_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
                              `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `id_parent_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ID du traitement parent pour la hiérarchie des menus',
                              `icone_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Classe CSS de l''icône associée à ce traitement (ex: fas fa-home)',
                              `url_associee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'URL ou route associée à ce traitement pour la navigation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `libelle_traitement`, `id_parent_traitement`, `icone_class`, `url_associee`) VALUES
                                                                                                                            ('MENU_ADMINISTRATION', 'Administration', NULL, 'fas fa-cogs', NULL),
                                                                                                                            ('MENU_DASHBOARDS', 'Tableaux de Bord', NULL, 'fas fa-tachometer-alt', NULL),
                                                                                                                            ('MENU_GESTION_COMPTES', 'Gestion des Comptes', 'MENU_ADMINISTRATION', 'fas fa-users', NULL),
                                                                                                                            ('MENU_RAPPORT_ETUDIANT', 'Rapports Étudiant', NULL, 'fas fa-file-alt', NULL),
                                                                                                                            ('TRAIT_ADMIN_DASHBOARD_ACCEDER', 'Accéder au Dashboard Admin', 'MENU_DASHBOARDS', 'fas fa-chart-line', 'admin/dashboard'),
                                                                                                                            ('TRAIT_ADMIN_GERER_UTILISATEURS_CREER', 'Créer un utilisateur', 'MENU_GESTION_COMPTES', 'fas fa-user-plus', 'admin/utilisateurs/creer'),
                                                                                                                            ('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'Lister les utilisateurs', 'MENU_GESTION_COMPTES', 'fas fa-list', 'admin/utilisateurs/liste'),
                                                                                                                            ('TRAIT_COMMISSION_DASHBOARD_ACCEDER', 'Accéder au Dashboard Commission', NULL, NULL, NULL),
                                                                                                                            ('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER', 'Voter pour un rapport', NULL, NULL, NULL),
                                                                                                                            ('TRAIT_ETUDIANT_DASHBOARD_ACCEDER', 'Accéder au Dashboard Étudiant', 'MENU_DASHBOARDS', 'fas fa-user-graduate', 'etudiant/dashboard'),
                                                                                                                            ('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', 'Soumettre son rapport', 'MENU_RAPPORT_ETUDIANT', 'fas fa-upload', 'etudiant/rapport/soumettre'),
                                                                                                                            ('TRAIT_ETUDIANT_RAPPORT_SUIVRE', 'Suivre son rapport', 'MENU_RAPPORT_ETUDIANT', 'fas fa-eye', 'etudiant/rapport/suivi'),
                                                                                                                            ('TRAIT_PERS_ADMIN_CONFORMITE_LISTER', 'Lister les rapports à vérifier', NULL, NULL, NULL),
                                                                                                                            ('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER', 'Vérifier la conformité d\'un rapport', NULL, NULL, NULL),
                                                                                                                            ('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', 'Accéder au Dashboard Personnel Admin', 'MENU_DASHBOARDS', 'fas fa-user-tie', 'personnel/dashboard'),
                                                                                                                            ('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', 'Accéder à la gestion de la scolarité', NULL, NULL, NULL),
                                                                                                                            ('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER', 'Gérer les pénalités', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `type_document_ref`
--

CREATE TABLE `type_document_ref` (
                                     `id_type_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_type_document` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `requis_ou_non` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
                                                                                       ('TYPE_ADMIN', 'Administrateur Système'),
                                                                                       ('TYPE_ENS', 'Enseignant'),
                                                                                       ('TYPE_ETUD', 'Étudiant'),
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('ADM-2025-0001', 'resp_sco', 'resp.sco@gestionsoutenance.dev', '$2y$10$LDuzMmrtuMbEqttVvPYU1uSktwQqdN90QDtHDupQ1Kl/I/8i0USbC', '2025-06-25 15:14:10', NULL, NULL, NULL, '97e6ef85e67dcd66490fa09ec9d4b8fc0ca078ace86f8e735473f2f1a3d4581d', 0, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_DEPARTEMENT', 'GRP_PERS_ADMIN', 'TYPE_PERS_ADMIN'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('ADM-2025-0002', 'agent_conf', 'agent.conf@gestionsoutenance.dev', '$2y$10$gBN1V1WXin91Xu.JCnIFEO6q1Arp59VBN74BzcFrAh1G2iaL.4DA2', '2025-06-25 15:14:10', NULL, NULL, NULL, '791236265f9efb7db230137992b3e235dbe8b33ef35a4bd0096fc0f971b8ccaf', 0, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_DEPARTEMENT', 'GRP_PERS_ADMIN', 'TYPE_PERS_ADMIN'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('ENS-2025-0001', 'prof_dupont', 'prof.dupont@gestionsoutenance.dev', '$2y$10$h9nEwZdBepRRQedg1tuvJujjrdmZ2jBgaAMc/r9dbDjjKMRg66dJq', '2025-06-25 15:14:10', NULL, NULL, NULL, 'daa4318f9c7a1322c4ec4840f02bbd590d1c26ba79baedc99dcd03d76bc3d0c6', 0, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_DEPARTEMENT', 'GRP_ENSEIGNANT', 'TYPE_ENS'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('ETU-2025-0001', 'etu_martin', 'etu.martin@gestionsoutenance.dev', '$2y$10$64kSHQbLa9cEe3hXJ0RDHOVdOrkW9CTQjOYaghSOHsziy16BXog2i', '2025-06-25 15:14:10', NULL, NULL, NULL, '977ec8fa3bee1590f68e02a3e5c2fb1212d2aac484ae1d4d11fb19d85cacd124', 0, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_PERSONNEL', 'GRP_ETUDIANT', 'TYPE_ETUD'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('SYS-2025-0001', 'admin_sys', 'admin.sys@gestionsoutenance.dev', '$2y$10$Yz7cffYIpq574/BIed87R.UV85F.GG9VNF0JOX4bTcs/kTBOxeOQC', '2025-06-25 15:14:10', '2025-06-26 19:36:01', NULL, NULL, 'e8eb0345a1ea3f8858d2f0396a3efae8552dcb469d6a3a6b1c69a3cd523577fc', 1, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN');

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
-- Structure de la table `vote_commission`
--

CREATE TABLE `vote_commission` (
                                   `id_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
-- Index pour la table `conformite_rapport_details`
--
ALTER TABLE `conformite_rapport_details`
    ADD PRIMARY KEY (`id_conformite_detail`),
    ADD UNIQUE KEY `uq_conformite_rapport_critere` (`id_rapport_etudiant`,`id_critere`),
    ADD KEY `fk_conformite_critere` (`id_critere`);

--
-- Index pour la table `conversation`
--
ALTER TABLE `conversation`
    ADD PRIMARY KEY (`id_conversation`);

--
-- Index pour la table `critere_conformite_ref`
--
ALTER TABLE `critere_conformite_ref`
    ADD PRIMARY KEY (`id_critere`);

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
-- Index pour la table `delegation`
--
ALTER TABLE `delegation`
    ADD PRIMARY KEY (`id_delegation`),
    ADD KEY `fk_delegation_delegant` (`id_delegant`),
    ADD KEY `fk_delegation_delegue` (`id_delegue`),
    ADD KEY `fk_delegation_traitement` (`id_traitement`);

--
-- Index pour la table `document_genere`
--
ALTER TABLE `document_genere`
    ADD PRIMARY KEY (`id_document_genere`),
    ADD KEY `idx_docgen_type` (`id_type_document`),
    ADD KEY `idx_docgen_entite` (`id_entite_concernee`,`type_entite_concernee`),
    ADD KEY `idx_docgen_user_concerne` (`numero_utilisateur_concerne`);

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
    ADD PRIMARY KEY (`id_enregistrement`),
    ADD KEY `idx_enregistrer_utilisateur` (`numero_utilisateur`),
    ADD KEY `idx_enregistrer_action` (`id_action`),
    ADD KEY `idx_enregistrer_date_action` (`date_action`);

--
-- Index pour la table `enseignant`
--
ALTER TABLE `enseignant`
    ADD PRIMARY KEY (`numero_enseignant`),
    ADD UNIQUE KEY `uq_enseignant_numero_utilisateur` (`numero_utilisateur`),
    ADD UNIQUE KEY `uq_enseignant_email_professionnel` (`email_professionnel`);

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
    ADD PRIMARY KEY (`numero_carte_etudiant`,`id_ecue`,`id_annee_academique`),
    ADD KEY `idx_evaluer_ecue` (`id_ecue`),
    ADD KEY `fk_evaluer_annee_academique` (`id_annee_academique`);

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
-- Index pour la table `matrice_notification_regles`
--
ALTER TABLE `matrice_notification_regles`
    ADD PRIMARY KEY (`id_regle`),
    ADD KEY `fk_matrice_action` (`id_action_declencheur`),
    ADD KEY `fk_matrice_groupe` (`id_groupe_destinataire`);

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
-- Index pour la table `parametres_systeme`
--
ALTER TABLE `parametres_systeme`
    ADD PRIMARY KEY (`cle`);

--
-- Index pour la table `participant_conversation`
--
ALTER TABLE `participant_conversation`
    ADD PRIMARY KEY (`id_conversation`,`numero_utilisateur`),
    ADD KEY `idx_pc_user` (`numero_utilisateur`);

--
-- Index pour la table `penalite`
--
ALTER TABLE `penalite`
    ADD PRIMARY KEY (`id_penalite`),
    ADD KEY `idx_penalite_etudiant` (`numero_carte_etudiant`),
    ADD KEY `idx_penalite_statut` (`id_statut_penalite`),
    ADD KEY `fk_penalite_annee` (`id_annee_academique`),
    ADD KEY `fk_penalite_personnel` (`numero_personnel_traitant`);

--
-- Index pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
    ADD PRIMARY KEY (`numero_personnel_administratif`),
    ADD UNIQUE KEY `uq_personnel_numero_utilisateur` (`numero_utilisateur`),
    ADD UNIQUE KEY `uq_personnel_email_professionnel` (`email_professionnel`);

--
-- Index pour la table `pister`
--
ALTER TABLE `pister`
    ADD PRIMARY KEY (`id_piste`),
    ADD KEY `idx_pister_utilisateur` (`numero_utilisateur`),
    ADD KEY `idx_pister_traitement` (`id_traitement`),
    ADD KEY `idx_pister_date` (`date_pister`);

--
-- Index pour la table `pv_session_rapport`
--
ALTER TABLE `pv_session_rapport`
    ADD PRIMARY KEY (`id_compte_rendu`,`id_rapport_etudiant`),
    ADD KEY `idx_pvsr_rapport` (`id_rapport_etudiant`);

--
-- Index pour la table `queue_jobs`
--
ALTER TABLE `queue_jobs`
    ADD PRIMARY KEY (`id`),
    ADD KEY `idx_status_created_at` (`status`,`created_at`);

--
-- Index pour la table `rapport_etudiant`
--
ALTER TABLE `rapport_etudiant`
    ADD PRIMARY KEY (`id_rapport_etudiant`),
    ADD KEY `idx_rapport_etudiant_etudiant` (`numero_carte_etudiant`),
    ADD KEY `fk_rapport_statut` (`id_statut_rapport`);

--
-- Index pour la table `rapport_modele`
--
ALTER TABLE `rapport_modele`
    ADD PRIMARY KEY (`id_modele`);

--
-- Index pour la table `rapport_modele_assignation`
--
ALTER TABLE `rapport_modele_assignation`
    ADD PRIMARY KEY (`id_modele`,`id_niveau_etude`),
    ADD KEY `fk_rma_niveau_etude` (`id_niveau_etude`);

--
-- Index pour la table `rapport_modele_section`
--
ALTER TABLE `rapport_modele_section`
    ADD PRIMARY KEY (`id_section_modele`),
    ADD KEY `fk_rms_modele` (`id_modele`);

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
    ADD PRIMARY KEY (`id_reception`),
    ADD KEY `idx_recevoir_utilisateur` (`numero_utilisateur`),
    ADD KEY `idx_recevoir_notification` (`id_notification`),
    ADD KEY `idx_recevoir_date_reception` (`date_reception`);

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
-- Index pour la table `section_rapport`
--
ALTER TABLE `section_rapport`
    ADD PRIMARY KEY (`id_rapport_etudiant`,`titre_section`);

--
-- Index pour la table `sequences`
--
ALTER TABLE `sequences`
    ADD PRIMARY KEY (`nom_sequence`,`annee`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`session_id`),
    ADD KEY `idx_session_last_activity` (`session_last_activity`),
    ADD KEY `idx_session_user_id` (`user_id`);

--
-- Index pour la table `session_rapport`
--
ALTER TABLE `session_rapport`
    ADD PRIMARY KEY (`id_session`,`id_rapport_etudiant`),
    ADD KEY `fk_sr_rapport` (`id_rapport_etudiant`);

--
-- Index pour la table `session_validation`
--
ALTER TABLE `session_validation`
    ADD PRIMARY KEY (`id_session`),
    ADD KEY `fk_session_president` (`id_president_session`);

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
-- Index pour la table `statut_penalite_ref`
--
ALTER TABLE `statut_penalite_ref`
    ADD PRIMARY KEY (`id_statut_penalite`);

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
    ADD KEY `fk_traitement_parent` (`id_parent_traitement`);

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
-- Index pour la table `vote_commission`
--
ALTER TABLE `vote_commission`
    ADD PRIMARY KEY (`id_vote`),
    ADD KEY `idx_vote_rapport` (`id_rapport_etudiant`),
    ADD KEY `idx_vote_enseignant` (`numero_enseignant`),
    ADD KEY `fk_vote_decision` (`id_decision_vote`),
    ADD KEY `fk_vote_session` (`id_session`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `queue_jobs`
--
ALTER TABLE `queue_jobs`
    MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Contraintes pour la table `conformite_rapport_details`
--
ALTER TABLE `conformite_rapport_details`
    ADD CONSTRAINT `fk_conformite_critere` FOREIGN KEY (`id_critere`) REFERENCES `critere_conformite_ref` (`id_critere`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_conformite_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `delegation`
--
ALTER TABLE `delegation`
    ADD CONSTRAINT `fk_delegation_delegant` FOREIGN KEY (`id_delegant`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_delegation_delegue` FOREIGN KEY (`id_delegue`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_delegation_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `document_genere`
--
ALTER TABLE `document_genere`
    ADD CONSTRAINT `fk_docgen_type` FOREIGN KEY (`id_type_document`) REFERENCES `type_document_ref` (`id_type_document`) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_docgen_user_concerne` FOREIGN KEY (`numero_utilisateur_concerne`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

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
    ADD CONSTRAINT `fk_evaluer_annee_academique` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_evaluer_ecue` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`) ON DELETE RESTRICT ON UPDATE CASCADE,
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
-- Contraintes pour la table `matrice_notification_regles`
--
ALTER TABLE `matrice_notification_regles`
    ADD CONSTRAINT `fk_matrice_action` FOREIGN KEY (`id_action_declencheur`) REFERENCES `action` (`id_action`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_matrice_groupe` FOREIGN KEY (`id_groupe_destinataire`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Contraintes pour la table `penalite`
--
ALTER TABLE `penalite`
    ADD CONSTRAINT `fk_penalite_annee` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_penalite_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_penalite_personnel` FOREIGN KEY (`numero_personnel_traitant`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_penalite_statut` FOREIGN KEY (`id_statut_penalite`) REFERENCES `statut_penalite_ref` (`id_statut_penalite`) ON DELETE RESTRICT ON UPDATE CASCADE;

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
-- Contraintes pour la table `rapport_modele_assignation`
--
ALTER TABLE `rapport_modele_assignation`
    ADD CONSTRAINT `fk_rma_modele` FOREIGN KEY (`id_modele`) REFERENCES `rapport_modele` (`id_modele`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_rma_niveau_etude` FOREIGN KEY (`id_niveau_etude`) REFERENCES `niveau_etude` (`id_niveau_etude`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `rapport_modele_section`
--
ALTER TABLE `rapport_modele_section`
    ADD CONSTRAINT `fk_rms_modele` FOREIGN KEY (`id_modele`) REFERENCES `rapport_modele` (`id_modele`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Contraintes pour la table `section_rapport`
--
ALTER TABLE `section_rapport`
    ADD CONSTRAINT `fk_section_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
    ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `session_rapport`
--
ALTER TABLE `session_rapport`
    ADD CONSTRAINT `fk_sr_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_sr_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation` (`id_session`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `session_validation`
--
ALTER TABLE `session_validation`
    ADD CONSTRAINT `fk_session_president` FOREIGN KEY (`id_president_session`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `specialite`
--
ALTER TABLE `specialite`
    ADD CONSTRAINT `fk_specialite_enseignant` FOREIGN KEY (`numero_enseignant_specialite`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `traitement`
--
ALTER TABLE `traitement`
    ADD CONSTRAINT `fk_traitement_parent` FOREIGN KEY (`id_parent_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Contraintes pour la table `vote_commission`
--
ALTER TABLE `vote_commission`
    ADD CONSTRAINT `fk_vote_decision` FOREIGN KEY (`id_decision_vote`) REFERENCES `decision_vote_ref` (`id_decision_vote`) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_vote_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_vote_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_vote_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation` (`id_session`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
