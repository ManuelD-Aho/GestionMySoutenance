-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : jeu. 03 juil. 2025 à 13:15
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
                                                                             ('ACCES_ASSET_ECHEC', 'Accès Asset Échec', 'Sécurité'),
                                                                             ('ACCES_ASSET_SUCCES', 'Accès Asset Succès', 'Sécurité'),
                                                                             ('ACCES_DASHBOARD_REFUSE', 'Accès Dashboard Refusé', 'Sécurité'),
                                                                             ('ACCES_DASHBOARD_REUSSI', 'Accès Dashboard Réussi', 'Sécurité'),
                                                                             ('ACCES_REFUSE', 'Accès Refusé', 'Sécurité'),
                                                                             ('ACTIVATION_2FA', 'Activation 2FA', 'Sécurité'),
                                                                             ('ACTIVATION_COMPTE', 'Activation Compte', 'Gestion Utilisateur'),
                                                                             ('ADMIN_PASSWORD_RESET', 'Réinitialisation MDP par Admin', 'Sécurité'),
                                                                             ('APPROBATION_PV', 'Approbation PV', 'Workflow'),
                                                                             ('ARCHIVAGE_CONVERSATIONS', 'Archivage Conversations', 'Communication'),
                                                                             ('CHANGEMENT_ANNEE_ACTIVE', 'Changement Année Active', 'Configuration'),
                                                                             ('CHANGEMENT_MDP', 'Changement Mot de Passe', 'Sécurité'),
                                                                             ('CHANGEMENT_STATUT_COMPTE', 'Changement Statut Compte', 'Gestion Utilisateur'),
                                                                             ('CHANGEMENT_STATUT_RAPPORT', 'Changement Statut Rapport', 'Workflow'),
                                                                             ('COMPTE_BLOQUE', 'Compte Bloqué', 'Sécurité'),
                                                                             ('COMPTE_VALIDE', 'Compte Validé', 'Gestion Utilisateur'),
                                                                             ('CORRECTIONS_REQUISES', 'Corrections Requises', 'Workflow'),
                                                                             ('CREATE_ADMIN_USER', 'Création Utilisateur Admin', 'Gestion Utilisateur'),
                                                                             ('CREATE_ANNEE_ACADEMIQUE', 'Création Année Académique', 'Configuration'),
                                                                             ('CREATE_DOC_TEMPLATE', 'Création Modèle Document', 'Documents'),
                                                                             ('CREATE_ENTITE', 'Création Entité', 'Gestion Utilisateur'),
                                                                             ('CREATE_REFERENTIEL', 'Création Référentiel', 'Configuration'),
                                                                             ('CREATION_DELEGATION', 'Création Délégation', 'Gestion Utilisateur'),
                                                                             ('DELETE_DOC_TEMPLATE', 'Suppression Modèle Document', 'Documents'),
                                                                             ('DELETE_FICHIER', 'Suppression Fichier', 'Documents'),
                                                                             ('DELETE_REFERENTIEL', 'Suppression Référentiel', 'Configuration'),
                                                                             ('DELETE_USER_HARD', 'Suppression Définitive Utilisateur', 'Gestion Utilisateur'),
                                                                             ('DESACTIVATION_2FA', 'Désactivation 2FA', 'Sécurité'),
                                                                             ('ECHEC_ACTIVATION_2FA', 'Échec Activation 2FA', 'Sécurité'),
                                                                             ('ECHEC_GENERATION_ID_UNIQUE', 'Échec Génération ID Unique', 'Système'),
                                                                             ('ECHEC_LOGIN', 'Échec Connexion', 'Sécurité'),
                                                                             ('ENREGISTREMENT_DECISION_PASSAGE', 'Enregistrement Décision Passage', 'Parcours Académique'),
                                                                             ('ENVOI_EMAIL_ECHEC', 'Envoi Email Échec', 'Communication'),
                                                                             ('ENVOI_EMAIL_SUCCES', 'Envoi Email Succès', 'Communication'),
                                                                             ('FORCER_CHANGEMENT_STATUT_RAPPORT', 'Forcer Changement Statut Rapport', 'Workflow'),
                                                                             ('FORCER_VALIDATION_PV', 'Forcer Validation PV', 'Workflow'),
                                                                             ('GENERATION_2FA_SECRET', 'Génération Secret 2FA', 'Sécurité'),
                                                                             ('GENERATION_DOCUMENT', 'Génération Document', 'Documents'),
                                                                             ('GENERATION_ID_UNIQUE', 'Génération ID Unique', 'Système'),
                                                                             ('IMPERSONATION_START', 'Début Impersonation', 'Sécurité'),
                                                                             ('IMPERSONATION_STOP', 'Fin Impersonation', 'Sécurité'),
                                                                             ('IMPORT_ETUDIANTS', 'Import Étudiants', 'Gestion Utilisateur'),
                                                                             ('LOGOUT', 'Déconnexion', 'Sécurité'),
                                                                             ('MISE_AJOUR_PARAMETRES', 'Mise à Jour Paramètres', 'Configuration'),
                                                                             ('NOUVEAU_RAPPORT_A_VERIFIER', 'Nouveau Rapport à Vérifier', 'Workflow'),
                                                                             ('NOUVEAU_STAGE_VALIDE', 'Nouveau Stage Validé', 'Parcours Académique'),
                                                                             ('NOUVEAU_TOUR_VOTE', 'Nouveau Tour de Vote', 'Workflow'),
                                                                             ('NOUVELLE_DELEGATION', 'Nouvelle Délégation', 'Gestion Utilisateur'),
                                                                             ('NOUVELLE_RECLAMATION', 'Nouvelle Réclamation', 'Communication'),
                                                                             ('PAIEMENT_INSCRIPTION_ATTENTE', 'Paiement Inscription en Attente', 'Scolarité'),
                                                                             ('PV_APPROUVE_DIFFUSE', 'PV Approuvé et Diffusé', 'Workflow'),
                                                                             ('RAPPORT_CONFORME_A_EVALUER', 'Rapport Conforme à Évaluer', 'Workflow'),
                                                                             ('RAPPORT_REFUSE', 'Rapport Refusé', 'Workflow'),
                                                                             ('RAPPORT_SOUMIS_SUCCES', 'Rapport Soumis avec Succès', 'Workflow'),
                                                                             ('RAPPORT_VALID', 'Rapport Validé', 'Workflow'),
                                                                             ('RECLAMATION_REPONDU', 'Réclamation Répondu', 'Communication'),
                                                                             ('RECUSATION_MEMBRE_COMMISSION', 'Récusation Membre Commission', 'Workflow'),
                                                                             ('RESEND_VALIDATION_EMAIL', 'Renvoyer Email Validation', 'Gestion Utilisateur'),
                                                                             ('REVOCATION_DELEGATION', 'Révocation Délégation', 'Gestion Utilisateur'),
                                                                             ('SOUMISSION_CORRECTIONS', 'Soumission Corrections Rapport', 'Workflow'),
                                                                             ('SOUMISSION_RAPPORT', 'Soumission Rapport', 'Workflow'),
                                                                             ('STATUT_RAPPORT_FORCE', 'Statut Rapport Forcé', 'Workflow'),
                                                                             ('STATUT_RAPPORT_MAJ', 'Statut Rapport Mis à Jour', 'Workflow'),
                                                                             ('SUCCES_LOGIN', 'Connexion Réussie', 'Sécurité'),
                                                                             ('SYNCHRONISATION_RBAC', 'Synchronisation RBAC', 'Sécurité'),
                                                                             ('TRANSITION_ROLE', 'Transition Rôle', 'Gestion Utilisateur'),
                                                                             ('UPDATE_DOC_TEMPLATE', 'Mise à Jour Modèle Document', 'Documents'),
                                                                             ('UPDATE_MENU_STRUCTURE', 'Mise à Jour Structure Menu', 'Configuration'),
                                                                             ('UPDATE_REFERENTIEL', 'Mise à Jour Référentiel', 'Configuration'),
                                                                             ('UPLOAD_FICHIER', 'Upload Fichier', 'Documents'),
                                                                             ('UPLOAD_PROFILE_PICTURE', 'Upload Photo Profil', 'Gestion Utilisateur'),
                                                                             ('VALIDATION_EMAIL_SUCCES', 'Validation Email Succès', 'Sécurité'),
                                                                             ('VALIDATION_STAGE', 'Validation Stage', 'Parcours Académique');

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
                                                                                                                               ('ANNEE-2023-2024', '2023-2024', '2023-09-01', '2024-08-31', 0),
                                                                                                                               ('ANNEE-2024-2025', '2024-2025', '2024-09-01', '2025-08-31', 0),
                                                                                                                               ('ANNEE-2025-2026', '2025-2026', '2025-09-01', '2026-08-31', 1),
                                                                                                                               ('ANNEE-2026-2027', '2026-2027', '2026-09-01', '2027-08-31', 0);

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
                                                                                                       ('ANNEXES_REF', 'Annexes référencées', 'Les annexes sont-elles correctement référencées dans le corps du texte ?', 1),
                                                                                                       ('BIBLIO_FORMAT', 'Bibliographie formatée', 'La bibliographie respecte-t-elle la norme APA 7ème édition ?', 1),
                                                                                                       ('FORMAT_GLOBAL', 'Formatage global', 'Le rapport respecte-t-il les marges, la police et l\'interligne définis ?', 1),
('INTRO_CONCLU', 'Introduction et Conclusion', 'Le rapport contient-il une introduction et une conclusion claires ?', 1),
('LANGUE_CORRECTE', 'Langue et orthographe', 'Le rapport est-il rédigé dans une langue correcte et sans fautes d\'orthographe majeures ?', 1),
                                                                                                       ('PAGE_GARDE', 'Respect de la page de garde', 'La page de garde contient-elle le logo, le titre, le nom de l\'étudiant, le nom du tuteur et l\'année académique ?', 1),
                                                                                                       ('PAGINATION', 'Pagination correcte', 'Le document est-il correctement paginé, en commençant après la page de garde ?', 1),
                                                                                                       ('PRESENCE_RESUME', 'Présence du résumé', 'Un résumé (abstract) en français et en anglais est-il présent au début du document ?', 1),
                                                                                                       ('TABLE_MATIERES', 'Table des matières', 'La table des matières est-elle présente et à jour ?', 1),
                                                                                                       ('VALIDITE_STAGE', 'Validité du stage associé', 'Le stage associé au rapport a-t-il été administrativement validé par la scolarité ?', 1);

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
                                                                                           ('DEC_ADMIS', 'Admis'),
                                                                                           ('DEC_AJOURNE', 'Ajourné'),
                                                                                           ('DEC_EXCLU', 'Exclu'),
                                                                                           ('DEC_REDOUBLANT', 'Redoublant');

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
                                                                                                             ('PV_APPROUVE', 'Approuvé'),
                                                                                                             ('PV_MODIF_DEMANDEE', 'Modification Demandée'),
                                                                                                             ('PV_REJETE', 'Rejeté');

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
                                                                                  ('VOTE_ABSTENTION', 'Abstention'),
                                                                                  ('VOTE_APPROUVE', 'Approuvé'),
                                                                                  ('VOTE_APPROUVE_RESERVE', 'Approuvé sous réserve'),
                                                                                  ('VOTE_REFUSE', 'Refusé');

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

--
-- Déchargement des données de la table `ecue`
--

INSERT INTO `ecue` (`id_ecue`, `libelle_ecue`, `id_ue`, `credits_ecue`) VALUES
                                                                            ('ECUE_AGILE', 'Méthodes Agiles', 'UE_MANAGEMENT', 2),
                                                                            ('ECUE_ALGO_AVANCE', 'Algorithmique Avancée', 'UE_ALGO', 3),
                                                                            ('ECUE_ANALYSE_DONNEES', 'Analyse de Données', 'UE_STAT_DATA', 2),
                                                                            ('ECUE_ANDROID', 'Développement Android', 'UE_MOBILE', 2),
                                                                            ('ECUE_AUDIT_SECURITE', 'Audit de Sécurité', 'UE_CYBER', 2),
                                                                            ('ECUE_BACKEND', 'Développement Backend', 'UE_DEV_WEB', 3),
                                                                            ('ECUE_CRYPTOGRAPHIE', 'Cryptographie', 'UE_CYBER', 3),
                                                                            ('ECUE_DEEP_LEARNING', 'Deep Learning', 'UE_IA', 3),
                                                                            ('ECUE_DEVOPS', 'DevOps', 'UE_CLOUD', 2),
                                                                            ('ECUE_FRONTEND', 'Développement Frontend', 'UE_DEV_WEB', 3),
                                                                            ('ECUE_GEST_PROJET', 'Gestion de Projet', 'UE_MANAGEMENT', 2),
                                                                            ('ECUE_INFRA_CLOUD', 'Infrastructure Cloud', 'UE_CLOUD', 3),
                                                                            ('ECUE_IOS', 'Développement iOS', 'UE_MOBILE', 2),
                                                                            ('ECUE_ML_BASES', 'Bases du Machine Learning', 'UE_IA', 3),
                                                                            ('ECUE_MODEL_BDD', 'Modélisation de Bases de Données', 'UE_BDD', 2),
                                                                            ('ECUE_PROTOCOLES', 'Protocoles Réseaux', 'UE_RESEAUX', 3),
                                                                            ('ECUE_SECURITE_RES', 'Sécurité des Réseaux', 'UE_RESEAUX', 2),
                                                                            ('ECUE_SQL_NO_SQL', 'SQL et NoSQL', 'UE_BDD', 3),
                                                                            ('ECUE_STAT_DESCRIPTIVE', 'Statistiques Descriptives', 'UE_STAT_DATA', 2),
                                                                            ('ECUE_STRUC_DONNEES', 'Structures de Données', 'UE_ALGO', 3);

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

--
-- Déchargement des données de la table `enregistrer`
--

INSERT INTO `enregistrer` (`id_enregistrement`, `numero_utilisateur`, `id_action`, `date_action`, `adresse_ip`, `user_agent`, `id_entite_concernee`, `type_entite_concernee`, `details_action`, `session_id_utilisateur`) VALUES
                                                                                                                                                                                                                              ('04bd53b6efd95dc71cc6f6a8e1cbf6d1', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 13:23:53', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', 'ca114f3355231bca6b37ca97e4e68d91'),
                                                                                                                                                                                                                              ('0a288223b23423beb474b7c09cf077df', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-02 23:27:14', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '90000bb20877e8ee1282475a352e61ec'),
                                                                                                                                                                                                                              ('29ad7a0882885ff68b5dbe6c53edf2ca', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 12:27:48', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '02304a588b0555b2b5328632acc520cf'),
                                                                                                                                                                                                                              ('34d1978ce741523ce52ddf058d1d82d5', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 05:53:29', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '90000bb20877e8ee1282475a352e61ec'),
                                                                                                                                                                                                                              ('48b596efcf054de865119f5611f0edd1', 'SYSTEM', 'ENVOI_EMAIL_SUCCES', '2025-07-02 22:48:20', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'Email', '{\"template\": \"RESET_PASSWORD\", \"destinataire\": \"ahopaul18@gmail.com\"}', '6af1485cc80971d5c16fd8e734f3b27d'),
                                                                                                                                                                                                                              ('658cb1a734f0c7b4ee34fb7bc6475e50', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 11:35:39', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '02304a588b0555b2b5328632acc520cf'),
                                                                                                                                                                                                                              ('67822174ffedf70ce55a9dbb2070e047', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 11:20:54', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '02304a588b0555b2b5328632acc520cf'),
                                                                                                                                                                                                                              ('6fa2c5f887acf4c81c108329df670c32', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 11:35:42', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '02304a588b0555b2b5328632acc520cf'),
                                                                                                                                                                                                                              ('8528f51cd28ebeb025f630c99efddad8', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 13:26:01', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', 'ca114f3355231bca6b37ca97e4e68d91'),
                                                                                                                                                                                                                              ('a412a58a39ecd1b6649c6f7f01940755', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 13:23:48', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', 'ca114f3355231bca6b37ca97e4e68d91'),
                                                                                                                                                                                                                              ('d6f5d883551ae6cea5b9cc6587818000', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-02 22:50:20', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '90000bb20877e8ee1282475a352e61ec'),
                                                                                                                                                                                                                              ('e4932f50789c14e18c30aa1f65f7af51', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 05:58:47', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '90000bb20877e8ee1282475a352e61ec'),
                                                                                                                                                                                                                              ('fbd4ffeb20ab39a119ba7341de4e2e52', 'SYS-2025-0001', 'ACCES_DASHBOARD_REUSSI', '2025-07-03 07:34:15', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, '/admin/dashboard', '{\"group\": \"GRP_ADMIN_SYS\"}', '90000bb20877e8ee1282475a352e61ec'),
                                                                                                                                                                                                                              ('febb17b11e26f67d5488c771b434999e', 'SYSTEM', 'ENVOI_EMAIL_SUCCES', '2025-07-02 22:42:38', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'Email', '{\"template\": \"RESET_PASSWORD\", \"destinataire\": \"ahopaul18@gmail.com\"}', '6af1485cc80971d5c16fd8e734f3b27d');

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
                              `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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

--
-- Déchargement des données de la table `entreprise`
--

INSERT INTO `entreprise` (`id_entreprise`, `libelle_entreprise`, `secteur_activite`, `adresse_entreprise`, `contact_nom`, `contact_email`, `contact_telephone`) VALUES
                                                                                                                                                                    ('ENT-001', 'Tech Solutions Inc.', 'Informatique', '123 Silicon Valley, CA', 'Alice Smith', 'alice.smith@techsol.com', '111-222-3333'),
                                                                                                                                                                    ('ENT-002', 'Global Finance Corp.', 'Finance', '45 Wall Street, NY', 'Bob Johnson', 'bob.johnson@globalfin.com', '444-555-6666'),
                                                                                                                                                                    ('ENT-003', 'Innovate Pharma', 'Pharmaceutique', '789 Bio Park, MA', 'Carol White', 'carol.white@innovate.com', '777-888-9999'),
                                                                                                                                                                    ('ENT-004', 'Green Energy Co.', 'Énergies Renouvelables', '10 Eco Lane, TX', 'David Green', 'david.green@greenenergy.com', '123-456-7890'),
                                                                                                                                                                    ('ENT-005', 'Creative Marketing Agency', 'Marketing', '20 Ad Street, CA', 'Eve Black', 'eve.black@creativemkt.com', '987-654-3210'),
                                                                                                                                                                    ('ENT-006', 'Future Robotics Ltd.', 'Robotique', '30 AI Drive, WA', 'Frank Blue', 'frank.blue@futurerobotics.com', '555-123-4567'),
                                                                                                                                                                    ('ENT-007', 'HealthCare Innovations', 'Santé', '40 Med Avenue, FL', 'Grace Red', 'grace.red@healthcare.com', '321-654-9870'),
                                                                                                                                                                    ('ENT-008', 'EduTech Solutions', 'Éducation', '50 Learning Road, IL', 'Henry Yellow', 'henry.yellow@edutech.com', '654-321-0987'),
                                                                                                                                                                    ('ENT-009', 'Logistics Masters', 'Logistique', '60 Supply Chain, GA', 'Ivy Purple', 'ivy.purple@logistics.com', '789-012-3456'),
                                                                                                                                                                    ('ENT-010', 'CyberSecure Systems', 'Cybersécurité', '70 Secure Blvd, VA', 'Jack Orange', 'jack.orange@cybersecure.com', '012-345-6789');

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
                            `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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

--
-- Déchargement des données de la table `fonction`
--

INSERT INTO `fonction` (`id_fonction`, `libelle_fonction`) VALUES
                                                               ('FCT_AGENT_CONF', 'Agent de Conformité'),
                                                               ('FCT_DIR_DEPT', 'Directeur de Département'),
                                                               ('FCT_DIR_ETUDES', 'Directeur des Études'),
                                                               ('FCT_ENSEIGNANT', 'Enseignant Chercheur'),
                                                               ('FCT_PRES_COMM', 'Président de Commission'),
                                                               ('FCT_RESP_SCO', 'Responsable Scolarité'),
                                                               ('FCT_RESP_STAGE', 'Responsable des Stages'),
                                                               ('FCT_SECRETAIRE', 'Secrétaire Administratif');

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
                         `id_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `libelle_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `abreviation_grade` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `grade`
--

INSERT INTO `grade` (`id_grade`, `libelle_grade`, `abreviation_grade`) VALUES
                                                                           ('GRD_ASS', 'Assistant', 'ASS'),
                                                                           ('GRD_DOC', 'Doctorant', 'DOC'),
                                                                           ('GRD_MCF', 'Maître de Conférences', 'MCF'),
                                                                           ('GRD_PR', 'Professeur des Universités', 'PR');

-- --------------------------------------------------------

--
-- Structure de la table `groupe_traitement`
--

CREATE TABLE `groupe_traitement` (
                                     `id_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_traitement`
--

INSERT INTO `groupe_traitement` (`id_groupe_utilisateur`, `id_traitement`) VALUES
                                                                               ('GRP_ADMIN_SYS', 'MENU_ADMINISTRATION'),
                                                                               ('COMMISSION', 'MENU_COMMISSION'),
                                                                               ('COMMISSION', 'MENU_DASHBOARDS'),
                                                                               ('ETUDIANT', 'MENU_DASHBOARDS'),
                                                                               ('GRP_ADMIN_SYS', 'MENU_DASHBOARDS'),
                                                                               ('PERSONNEL', 'MENU_DASHBOARDS'),
                                                                               ('ETUDIANT', 'MENU_ETUDIANT'),
                                                                               ('GRP_ADMIN_SYS', 'MENU_GESTION_COMPTES'),
                                                                               ('PERSONNEL', 'MENU_PERSONNEL'),
                                                                               ('ETUDIANT', 'MENU_RAPPORT_ETUDIANT'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_ACCEDER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_ANNEES_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_MENUS_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_NOTIFS_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_PARAMETRES_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_ACCEDER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER'),
                                                                               ('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER'),
                                                                               ('COMMISSION', 'TRAIT_COMMISSION_DASHBOARD_ACCEDER'),
                                                                               ('COMMISSION', 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER'),
                                                                               ('ETUDIANT', 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER'),
                                                                               ('ETUDIANT', 'TRAIT_ETUDIANT_PROFIL_GERER'),
                                                                               ('ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE'),
                                                                               ('ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SUIVRE'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_RECLAMATIONS_GERER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER'),
                                                                               ('PERSONNEL', 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER');

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_groupe` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_utilisateur`
--

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `libelle_groupe`) VALUES
                                                                                 ('GRP_ADMIN_SYS', 'Administrateur'),
                                                                                 ('COMMISSION', 'Commission'),
                                                                                 ('ENSEIGNANT', 'Enseignant'),
                                                                                 ('ETUDIANT', 'Étudiant'),
                                                                                 ('PERSONNEL', 'Personnel Administratif');

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

--
-- Déchargement des données de la table `historique_mot_de_passe`
--

INSERT INTO `historique_mot_de_passe` (`id_historique_mdp`, `numero_utilisateur`, `mot_de_passe_hache`, `date_changement`) VALUES
    ('HMP_68659b688e172', 'SYS-2025-0001', '$2y$10$Yz7cffYIpq574/BIed87R.UV85F.GG9VNF0JOX4bTcs/kTBOxeOQC', '2025-07-02 22:49:44');

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

--
-- Déchargement des données de la table `matrice_notification_regles`
--

INSERT INTO `matrice_notification_regles` (`id_regle`, `id_action_declencheur`, `id_groupe_destinataire`, `canal_notification`, `est_active`) VALUES
                                                                                                                                                  ('REGLE_ADMIN_COMPTE_BLOQUE', 'COMPTE_BLOQUE', 'GRP_ADMIN_SYS', 'Interne', 1),
                                                                                                                                                  ('REGLE_ADMIN_ERREUR_SYSTEME', 'ECHEC_GENERATION_ID_UNIQUE', 'GRP_ADMIN_SYS', 'Tous', 1),
                                                                                                                                                  ('REGLE_ADMIN_IMPORT_ETUDIANTS', 'IMPORT_ETUDIANTS', 'GRP_ADMIN_SYS', 'Tous', 1),
                                                                                                                                                  ('REGLE_ADMIN_LOGIN_ECHEC', 'ECHEC_LOGIN', 'GRP_ADMIN_SYS', 'Interne', 1),
                                                                                                                                                  ('REGLE_ADMIN_UPDATE_MENU', 'UPDATE_MENU_STRUCTURE', 'GRP_ADMIN_SYS', 'Interne', 1),
                                                                                                                                                  ('REGLE_AGENT_NOUVEAU_RAPPORT', 'NOUVEAU_RAPPORT_A_VERIFIER', 'GRP_AGENT_CONFORMITE', 'Tous', 1),
                                                                                                                                                  ('REGLE_COMM_NOUVEAU_TOUR_VOTE', 'NOUVEAU_TOUR_VOTE', 'GRP_COMMISSION', 'Interne', 1),
                                                                                                                                                  ('REGLE_COMM_RAPPORT_CONFORME', 'RAPPORT_CONFORME_A_EVALUER', 'GRP_COMMISSION', 'Tous', 1),
                                                                                                                                                  ('REGLE_COMM_RECUSATION_MEMBRE', 'RECUSATION_MEMBRE_COMMISSION', 'GRP_COMMISSION', 'Interne', 1),
                                                                                                                                                  ('REGLE_ENS_NOUVELLE_DELEGATION', 'NOUVELLE_DELEGATION', 'GRP_ENSEIGNANT', 'Interne', 1),
                                                                                                                                                  ('REGLE_ETUD_COMPTE_VALIDE', 'COMPTE_VALIDE', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_CORRECTIONS_REQUISES', 'CORRECTIONS_REQUISES', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_PAIEMENT_ATTENTE', 'PAIEMENT_INSCRIPTION_ATTENTE', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_PV_APPROUVE', 'PV_APPROUVE_DIFFUSE', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_RAPPORT_REFUSE', 'RAPPORT_REFUSE', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_RAPPORT_SOUMIS', 'RAPPORT_SOUMIS_SUCCES', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_RAPPORT_VALIDE', 'RAPPORT_VALID', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_RECLAMATION_REPONDU', 'RECLAMATION_REPONDU', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_STATUT_RAPPORT_FORCE', 'STATUT_RAPPORT_FORCE', 'GRP_ETUDIANT', 'Tous', 1),
                                                                                                                                                  ('REGLE_ETUD_STATUT_RAPPORT_MAJ', 'STATUT_RAPPORT_MAJ', 'GRP_ETUDIANT', 'Interne', 1),
                                                                                                                                                  ('REGLE_RS_COMPTE_ACTIVE', 'ACTIVATION_COMPTE', 'GRP_RS', 'Interne', 1),
                                                                                                                                                  ('REGLE_RS_NOUVEAU_STAGE_VALIDE', 'NOUVEAU_STAGE_VALIDE', 'GRP_RS', 'Interne', 1),
                                                                                                                                                  ('REGLE_RS_NOUVELLE_RECLAMATION', 'NOUVELLE_RECLAMATION', 'GRP_RS', 'Tous', 1);

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

--
-- Déchargement des données de la table `niveau_etude`
--

INSERT INTO `niveau_etude` (`id_niveau_etude`, `libelle_niveau_etude`) VALUES
                                                                           ('DOCTORAT', 'Doctorat'),
                                                                           ('L1', 'Licence 1'),
                                                                           ('L2', 'Licence 2'),
                                                                           ('L3', 'Licence 3'),
                                                                           ('M1', 'Master 1'),
                                                                           ('M2', 'Master 2');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
                                `id_notification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `libelle_notification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id_notification`, `libelle_notification`, `contenu`) VALUES
                                                                                      ('ACCES_REFUSE', 'Accès Refusé', 'Votre tentative d\'accès à une ressource protégée a été refusée.'),
('ADMIN_PASSWORD_RESET', 'Mot de passe réinitialisé par l\'administrateur', 'Votre mot de passe a été réinitialisé par un administrateur. Votre nouveau mot de passe est : {{nouveau_mdp}}. Veuillez le changer dès votre première connexion.'),
                                                                                      ('COMPTE_BLOQUE', 'Compte Bloqué', 'Votre compte a été temporairement bloqué suite à trop de tentatives de connexion infructueuses. Veuillez réessayer plus tard.'),
                                                                                      ('COMPTE_INACTIF', 'Compte Inactif', 'Votre compte est actuellement inactif. Veuillez contacter l\'administration.'),
('COMPTE_VALIDE', 'Compte Validé', 'Votre compte a été validé avec succès. Vous pouvez maintenant vous connecter.'),
('CORRECTIONS_REQUISES', 'Corrections Requises pour votre Rapport', 'Votre rapport a été évalué et nécessite des corrections. Veuillez consulter les commentaires dans votre espace personnel.'),
('NOUVEAU_RAPPORT_A_VERIFIER', 'Nouveau Rapport à Vérifier', 'Un nouveau rapport a été soumis et est en attente de votre vérification de conformité.'),
('NOUVEAU_STAGE_VALIDE', 'Nouveau Stage Validé', 'Votre enregistrement de stage a été validé par le service de scolarité.'),
('NOUVEAU_TOUR_VOTE', 'Nouveau Tour de Vote Commission', 'Un nouveau tour de vote a été lancé pour le rapport {{id_rapport}} dans la session {{id_session}}. Veuillez soumettre votre vote.'),
('NOUVELLE_DELEGATION', 'Nouvelle Délégation de Droits', 'Vous avez reçu une nouvelle délégation de droits. Veuillez consulter votre profil pour plus de détails.'),
('NOUVELLE_RECLAMATION', 'Nouvelle Réclamation Reçue', 'Une nouvelle réclamation a été soumise par un étudiant et nécessite votre attention.'),
('PAIEMENT_INSCRIPTION_ATTENTE', 'Paiement d\'Inscription en Attente', 'Votre paiement d\'inscription pour l\'année académique {{annee_academique}} est en attente. Veuillez régulariser votre situation.'),
                                                                                      ('PV_APPROUVE_DIFFUSE', 'PV Approuvé et Diffusé', 'Le procès-verbal de validation de votre rapport a été approuvé et est disponible dans votre espace personnel.'),
                                                                                      ('RAPPORT_CONFORME_A_EVALUER', 'Rapport Conforme à Évaluer', 'Un rapport a été jugé conforme et est prêt pour l\'évaluation par la commission.'),
('RAPPORT_CORRIGE_ET_VALIDE', 'Rapport Corrigé et Validé', 'Votre rapport a été corrigé et est maintenant définitivement validé.'),
('RAPPORT_REFUSE', 'Rapport Refusé', 'Votre rapport a été refusé par la commission. Veuillez consulter les motifs détaillés.'),
('RAPPORT_SOUMIS_SUCCES', 'Rapport Soumis avec Succès', 'Votre rapport a été soumis avec succès et est en cours de traitement.'),
('RECLAMATION_REPONDU', 'Réclamation Traitée', 'Votre réclamation concernant \"{{sujet_reclamation}}\" a été traitée. Veuillez consulter la réponse dans votre espace personnel.'),
('RESET_PASSWORD', 'Réinitialisation de votre mot de passe', 'Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur ce lien : {{reset_link}}'),
                                                                                       ('STATUT_RAPPORT_FORCE', 'Statut de Rapport Modifié Manuellement', 'Le statut de votre rapport (ID: {{id_rapport}}) a été modifié manuellement par l\'administration. Nouveau statut: {{nouveau_statut}}. Justification: {{justification}}'),
('STATUT_RAPPORT_MAJ', 'Statut de votre Rapport Mis à Jour', 'Le statut de votre rapport a été mis à jour. Nouveau statut : {{nouveau_statut}}.'),
('VALIDATE_EMAIL', 'Validez votre adresse email', 'Veuillez cliquer sur le lien suivant pour valider votre adresse email : {{validation_link}}');

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
('SMTP_HOST', 'mailhog', 'Hôte du serveur SMTP pour l\'envoi d\'emails.', 'string'),
('SMTP_PASS', 'password', 'Mot de passe pour l\'authentification SMTP.', 'string'),
                                                                                       ('SMTP_PORT', '1025', 'Port du serveur SMTP.', 'integer'),
                                                                                       ('SMTP_SECURE', '', 'Type de chiffrement SMTP (tls, ssl, ou vide).', 'string'),
                                                                                       ('SMTP_USER', 'user@example.com', 'Nom d\'utilisateur pour l\'authentification SMTP.', 'string'),
                                                                                       ('UPLOADS_PATH_BASE', '/var/www/html/Public/uploads/', 'Chemin de base pour tous les uploads de fichiers.', 'string'),
                                                                                       ('UPLOADS_PATH_DOCUMENTS_GENERES', 'documents_generes/', 'Sous-chemin pour les documents PDF générés.', 'string'),
                                                                                       ('UPLOADS_PATH_PROFILE_PICTURES', 'profile_pictures/', 'Sous-chemin pour les photos de profil.', 'string'),
                                                                                       ('UPLOADS_PATH_RAPPORT_IMAGES', 'rapport_images/', 'Sous-chemin pour les images insérées dans les rapports.', 'string');

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
                                           `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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

--
-- Déchargement des données de la table `rapport_modele`
--

INSERT INTO `rapport_modele` (`id_modele`, `nom_modele`, `description`, `version`, `statut`) VALUES
                                                                                                 ('TPL-2025-0001', 'Modèle Standard MIAGE', 'Modèle de rapport de stage standard pour les étudiants MIAGE.', '1.0', 'Publié'),
                                                                                                 ('TPL-2025-0002', 'Modèle Scientifique (IA/Data)', 'Modèle avec sections spécifiques pour les projets de recherche en IA et Data Science.', '1.0', 'Publié'),
                                                                                                 ('TPL-2025-0003', 'Modèle Court (L3)', 'Modèle simplifié pour les rapports de stage de Licence 3.', '1.0', 'Publié'),
                                                                                                 ('TPL-2025-0004', 'Modèle Cybersécurité', 'Modèle axé sur l\'analyse de vulnérabilités et les recommandations de sécurité.', '1.0', 'Publié');

-- --------------------------------------------------------

--
-- Structure de la table `rapport_modele_assignation`
--

CREATE TABLE `rapport_modele_assignation` (
  `id_modele` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_niveau_etude` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rapport_modele_assignation`
--

INSERT INTO `rapport_modele_assignation` (`id_modele`, `id_niveau_etude`) VALUES
('TPL-2025-0003', 'L3'),
('TPL-2025-0001', 'M2'),
('TPL-2025-0002', 'M2'),
('TPL-2025-0004', 'M2');

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

--
-- Déchargement des données de la table `rapport_modele_section`
--

INSERT INTO `rapport_modele_section` (`id_section_modele`, `id_modele`, `titre_section`, `contenu_par_defaut`, `ordre`) VALUES
('RMS-0001', 'TPL-2025-0001', 'Introduction', 'Présentation du contexte de l\'entreprise et des objectifs du stage.', 1),
                                                                                                 ('RMS-0002', 'TPL-2025-0001', 'Contexte de l\'Entreprise', 'Description détaillée de l\'entreprise d\'accueil, son secteur, sa structure.', 2),
('RMS-0003', 'TPL-2025-0001', 'Problématique et Objectifs', 'Définition de la problématique abordée et des objectifs du projet/mission.', 3),
('RMS-0004', 'TPL-2025-0001', 'Analyse et Conception', 'Description des phases d\'analyse et de conception (méthodes, outils, diagrammes).', 4),
                                                                                                 ('RMS-0005', 'TPL-2025-0001', 'Réalisation et Implémentation', 'Détail des étapes de développement, technologies utilisées, défis rencontrés.', 5),
                                                                                                 ('RMS-0006', 'TPL-2025-0001', 'Tests et Validation', 'Description des tests effectués et des résultats obtenus.', 6),
                                                                                                 ('RMS-0007', 'TPL-2025-0001', 'Conclusion et Perspectives', 'Bilan du stage, apports personnels, et pistes d\'amélioration/futur.', 7),
('RMS-0008', 'TPL-2025-0001', 'Bibliographie', 'Liste des références bibliographiques utilisées.', 8),
('RMS-0009', 'TPL-2025-0001', 'Annexes', 'Documents complémentaires (code source, captures d\'écran, etc.).', 9),
                                                                                                  ('RMS-0010', 'TPL-2025-0002', 'Abstract', 'Résumé du projet de recherche en anglais.', 1),
                                                                                                  ('RMS-0011', 'TPL-2025-0002', 'État de l\'Art', 'Revue des travaux existants et des technologies pertinentes.', 2),
('RMS-0012', 'TPL-2025-0002', 'Méthodologie de Recherche', 'Description des approches, algorithmes et modèles utilisés.', 3),
('RMS-0013', 'TPL-2025-0002', 'Collecte et Préparation des Données', 'Détail des sources de données, nettoyage, transformation.', 4),
('RMS-0014', 'TPL-2025-0002', 'Expérimentation et Résultats', 'Protocole expérimental, analyse des résultats, métriques.', 5),
('RMS-0015', 'TPL-2025-0002', 'Discussion et Interprétation', 'Interprétation des résultats, limites, implications.', 6),
('RMS-0016', 'TPL-2025-0002', 'Conclusion et Travaux Futurs', 'Synthèse des contributions et pistes pour la recherche future.', 7),
('RMS-0017', 'TPL-2025-0002', 'Références', 'Liste des publications scientifiques citées.', 8),
('RMS-0018', 'TPL-2025-0003', 'Présentation du Stage', 'Contexte du stage et objectifs principaux.', 1),
('RMS-0019', 'TPL-2025-0003', 'Activités Réalisées', 'Description des tâches et missions effectuées.', 2),
('RMS-0020', 'TPL-2025-0003', 'Apports Personnels', 'Ce que le stage a apporté en termes de compétences et d\'expérience.', 3),
                                                                                                  ('RMS-0021', 'TPL-2025-0003', 'Conclusion', 'Bilan rapide du stage.', 4),
                                                                                                  ('RMS-0022', 'TPL-2025-0004', 'Introduction à la Cybersécurité', 'Contexte et enjeux de la cybersécurité dans l\'entreprise.', 1),
('RMS-0023', 'TPL-2025-0004', 'Analyse des Risques et Vulnérabilités', 'Méthodologie d\'identification et d\'évaluation des risques.', 2),
('RMS-0024', 'TPL-2025-0004', 'Tests d\'Intrusion (Pentesting)', 'Description des tests réalisés, outils et techniques.', 3),
                                                                                                  ('RMS-0025', 'TPL-2025-0004', 'Résultats et Recommandations', 'Présentation des failles découvertes et des mesures correctives proposées.', 4),
                                                                                                  ('RMS-0026', 'TPL-2025-0004', 'Plan d\'Action et Suivi', 'Mise en œuvre des recommandations et perspectives.', 5),
('RMS-0027', 'TPL-2025-0004', 'Conclusion', 'Synthèse des travaux et apprentissages.', 6);

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
('GRP_ADMIN_SYS', 'MENU_COMMISSION'),
('GRP_COMMISSION', 'MENU_COMMISSION'),
('GRP_ADMIN_SYS', 'MENU_DASHBOARDS'),
('GRP_AGENT_CONFORMITE', 'MENU_DASHBOARDS'),
('GRP_COMMISSION', 'MENU_DASHBOARDS'),
('GRP_ENSEIGNANT', 'MENU_DASHBOARDS'),
('GRP_ETUDIANT', 'MENU_DASHBOARDS'),
('GRP_PERS_ADMIN', 'MENU_DASHBOARDS'),
('GRP_RS', 'MENU_DASHBOARDS'),
('GRP_ADMIN_SYS', 'MENU_ETUDIANT'),
('GRP_ETUDIANT', 'MENU_ETUDIANT'),
('GRP_ADMIN_SYS', 'MENU_GESTION_COMPTES'),
('GRP_ADMIN_SYS', 'MENU_PERSONNEL'),
('GRP_AGENT_CONFORMITE', 'MENU_PERSONNEL'),
('GRP_PERS_ADMIN', 'MENU_PERSONNEL'),
('GRP_RS', 'MENU_PERSONNEL'),
('GRP_ADMIN_SYS', 'MENU_RAPPORT_ETUDIANT'),
('GRP_ETUDIANT', 'MENU_RAPPORT_ETUDIANT'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_ACCEDER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_ANNEES_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_MENUS_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_NOTIFS_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_PARAMETRES_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_DASHBOARD_ACCEDER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER'),
('GRP_ADMIN_SYS', 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER'),
('GRP_COMMISSION', 'TRAIT_COMMISSION_DASHBOARD_ACCEDER'),
('GRP_ENSEIGNANT', 'TRAIT_COMMISSION_DASHBOARD_ACCEDER'),
('GRP_COMMISSION', 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER'),
('GRP_ETUDIANT', 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER'),
('GRP_ETUDIANT', 'TRAIT_ETUDIANT_PROFIL_GERER'),
('GRP_ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE'),
('GRP_ETUDIANT', 'TRAIT_ETUDIANT_RAPPORT_SUIVRE'),
('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER'),
('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER'),
('GRP_AGENT_CONFORMITE', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
('GRP_PERS_ADMIN', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
('GRP_RS', 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'),
('GRP_RS', 'TRAIT_PERS_ADMIN_RECLAMATIONS_GERER'),
('GRP_RS', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER'),
('GRP_RS', 'TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER'),
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
('ADM', 2025, 0),
('CONV', 2025, 0),
('CRD', 2025, 0),
('DEL', 2025, 0),
('DOC', 2025, 0),
('ENS', 2025, 0),
('ETU', 2025, 0),
('LOG', 2025, 0),
('MSG', 2025, 0),
('PEN', 2025, 0),
('PISTE', 2025, 0),
('PV', 2025, 0),
('RAP', 2025, 0),
('RECEP', 2025, 0),
('RECLA', 2025, 0),
('SESS', 2025, 0),
('SYS', 2025, 1),
('TPL', 2025, 0),
('VOTE', 2025, 0);

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
('02304a588b0555b2b5328632acc520cf', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534353139383b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d757365725f64656c65676174696f6e737c613a303a7b7d757365725f646174617c613a31353a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a363a2261686f2e7369223b733a31353a22656d61696c5f7072696e636970616c223b733a31393a2261686f7061756c313840676d61696c2e636f6d223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30372d30312032313a35353a3237223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30372d30322032323a35303a3230223b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b7d61646d696e5f64617368626f6172645f73746174737c613a323a7b733a393a2274696d657374616d70223b693a313735313534353139383b733a343a2264617461223b613a353a7b733a31323a227574696c6973617465757273223b613a323a7b733a353a226163746966223b693a323b733a353a22746f74616c223b693a323b7d733a383a22726170706f727473223b613a393a7b733a383a22417263686976c3a9223b693a303b733a393a2242726f75696c6c6f6e223b693a303b733a383a22436f6e666f726d65223b693a303b733a31333a22456e20436f7272656374696f6e223b693a303b733a31333a22456e20436f6d6d697373696f6e223b693a303b733a31323a224e6f6e20436f6e666f726d65223b693a303b733a373a225265667573c3a9223b693a303b733a363a22536f756d6973223b693a303b733a373a2256616c6964c3a9223b693a303b7d733a353a227175657565223b613a303a7b7d733a31363a2261637469766974655f726563656e7465223b613a323a7b733a32323a2241434345535f44415348424f4152445f524555535349223b693a31323b733a31383a22454e564f495f454d41494c5f535543434553223b693a323b7d733a31323a227265636c616d6174696f6e73223b613a343a7b733a31303a22436cc3b4747572c3a965223b693a303b733a32323a22456e20636f757273206465207472616974656d656e74223b693a303b733a373a224f757665727465223b693a303b733a383a2252c3a9736f6c7565223b693a303b7d7d7d, 1751545198, 3600, 'SYS-2025-0001'),
('18de35cdb1777954a60bad65aa580aad', 0x637372665f746f6b656e737c613a313a7b733a31303a226c6f67696e5f666f726d223b733a36343a2230323536386133353865636139366636383533363134656535646163643364363536363130316636643134313561336431383635663866623037373766393137223b7d, 1751529042, 3600, NULL),
('317c949a6596a0c1e4c42a4d47ef5059', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534363339353b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d666c6173685f6d657373616765737c613a363a7b693a303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a33353a22556e652065727265757220696e617474656e647565206573742073757276656e75652e223b7d693a313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d7d, 1751546395, 3600, 'SYS-2025-0001'),
('4g4pnr9fptj33o02n43tepdr2v', '', 1751417018, 3600, NULL),
('558b9fa904ed6a3f947e3494dbd11d18', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534373534343b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d666c6173685f6d657373616765737c613a343a7b693a303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a33353a22556e652065727265757220696e617474656e647565206573742073757276656e75652e223b7d693a313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37313a22566f7472652072c3b46c65206e6520766f757320646f6e6e652070617320616363c3a87320c3a020756e207461626c65617520646520626f7264207370c3a9636966697175652e223b7d693a333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d7d, 1751547544, 3600, 'SYS-2025-0001'),
('5abc96eaa7268841eabbb3f0ffcb1fe4', 0x637372665f746f6b656e737c613a313a7b733a31303a226c6f67696e5f666f726d223b733a36343a2239353336313339656239613034623639306236373035653665626466646262363635623064323438333962316134356461653830373531333534303161313834223b7d, 1751527487, 3600, NULL),
('8099ade8d83c285fb0b801456dc151e5', 0x637372665f746f6b656e737c613a323a7b733a32303a22666f72676f745f70617373776f72645f666f726d223b733a36343a2263316534653865363231666334343431666638626434316165633334316436363064386136356262636632373965623237323439656364373538616364636462223b733a31303a226c6f67696e5f666f726d223b733a36343a2232343165383533343735363436663430663438663232376537333333353562636363653131663430366334643265323165353566326462653535343634356566223b7d, 1751534060, 3600, NULL),
('90000bb20877e8ee1282475a352e61ec', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313532313731323b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d757365725f64656c65676174696f6e737c613a303a7b7d757365725f646174617c613a31353a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a363a2261686f2e7369223b733a31353a22656d61696c5f7072696e636970616c223b733a31393a2261686f7061756c313840676d61696c2e636f6d223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30372d30312032313a35353a3237223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30372d30312032313a35353a3237223b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b7d61646d696e5f64617368626f6172645f73746174737c613a323a7b733a393a2274696d657374616d70223b693a313735313532313437353b733a343a2264617461223b613a353a7b733a31323a227574696c6973617465757273223b613a323a7b733a353a226163746966223b693a323b733a353a22746f74616c223b693a323b7d733a383a22726170706f727473223b613a393a7b733a383a22417263686976c3a9223b693a303b733a393a2242726f75696c6c6f6e223b693a303b733a383a22436f6e666f726d65223b693a303b733a31333a22456e20436f7272656374696f6e223b693a303b733a31333a22456e20436f6d6d697373696f6e223b693a303b733a31323a224e6f6e20436f6e666f726d65223b693a303b733a373a225265667573c3a9223b693a303b733a363a22536f756d6973223b693a303b733a373a2256616c6964c3a9223b693a303b7d733a353a227175657565223b613a303a7b7d733a31363a2261637469766974655f726563656e7465223b613a323a7b733a31383a22454e564f495f454d41494c5f535543434553223b693a323b733a32323a2241434345535f44415348424f4152445f524555535349223b693a353b7d733a31323a227265636c616d6174696f6e73223b613a343a7b733a31303a22436cc3b4747572c3a965223b693a303b733a32323a22456e20636f757273206465207472616974656d656e74223b693a303b733a373a224f757665727465223b693a303b733a383a2252c3a9736f6c7565223b693a303b7d7d7d, 1751523212, 3600, 'SYS-2025-0001'),
('ca114f3355231bca6b37ca97e4e68d91', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534363338323b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d757365725f64656c65676174696f6e737c613a303a7b7d757365725f646174617c613a31353a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a363a2261686f2e7369223b733a31353a22656d61696c5f7072696e636970616c223b733a31393a2261686f7061756c313840676d61696c2e636f6d223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30372d30312032313a35353a3237223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30372d30332031313a32303a3534223b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b7d61646d696e5f64617368626f6172645f73746174737c613a323a7b733a393a2274696d657374616d70223b693a313735313534363138313b733a343a2264617461223b613a353a7b733a31323a227574696c6973617465757273223b613a323a7b733a353a226163746966223b693a323b733a353a22746f74616c223b693a323b7d733a383a22726170706f727473223b613a393a7b733a383a22417263686976c3a9223b693a303b733a393a2242726f75696c6c6f6e223b693a303b733a383a22436f6e666f726d65223b693a303b733a31333a22456e20436f7272656374696f6e223b693a303b733a31333a22456e20436f6d6d697373696f6e223b693a303b733a31323a224e6f6e20436f6e666f726d65223b693a303b733a373a225265667573c3a9223b693a303b733a363a22536f756d6973223b693a303b733a373a2256616c6964c3a9223b693a303b7d733a353a227175657565223b613a303a7b7d733a31363a2261637469766974655f726563656e7465223b613a323a7b733a32323a2241434345535f44415348424f4152445f524555535349223b693a31323b733a31383a22454e564f495f454d41494c5f535543434553223b693a323b7d733a31323a227265636c616d6174696f6e73223b613a343a7b733a31303a22436cc3b4747572c3a965223b693a303b733a32323a22456e20636f757273206465207472616974656d656e74223b693a303b733a373a224f757665727465223b693a303b733a383a2252c3a9736f6c7565223b693a303b7d7d7d, 1751546382, 3600, 'SYS-2025-0001'),
('e088d22c04363a74997d51aba6227430', '', 1751442969, 3600, NULL),
('ee92ef15cee952d4960bdec0ee26b9ea', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534383030373b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d666c6173685f6d657373616765737c613a363a7b693a303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a33353a22556e652065727265757220696e617474656e647565206573742073757276656e75652e223b7d693a313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d7d, 1751548007, 3600, 'SYS-2025-0001'),
('fb98d07d1a65615a491d0e7e85524472', 0x637372665f746f6b656e737c613a303a7b7d757365725f69647c733a31333a225359532d323032352d30303031223b6c6173745f61637469766974797c693a313735313534383139373b757365725f67726f75705f7065726d697373696f6e737c613a31383a7b693a303b733a31393a224d454e555f41444d494e495354524154494f4e223b693a313b733a31353a224d454e555f434f4d4d495353494f4e223b693a323b733a31353a224d454e555f44415348424f41524453223b693a333b733a31333a224d454e555f4554554449414e54223b693a343b733a32303a224d454e555f47455354494f4e5f434f4d50544553223b693a353b733a31343a224d454e555f504552534f4e4e454c223b693a363b733a32313a224d454e555f524150504f52545f4554554449414e54223b693a373b733a33353a2254524149545f41444d494e5f41434345535f46494348494552535f50524f5445474553223b693a383b733a32363a2254524149545f41444d494e5f434f4e4649475f41434345444552223b693a393b733a33313a2254524149545f41444d494e5f434f4e4649475f414e4e4545535f4745524552223b693a31303b733a33303a2254524149545f41444d494e5f434f4e4649475f4d454e55535f4745524552223b693a31313b733a33363a2254524149545f41444d494e5f434f4e4649475f4d4f44454c45535f444f435f4745524552223b693a31323b733a33313a2254524149545f41444d494e5f434f4e4649475f4e4f544946535f4745524552223b693a31333b733a33353a2254524149545f41444d494e5f434f4e4649475f504152414d45545245535f4745524552223b693a31343b733a33373a2254524149545f41444d494e5f434f4e4649475f5245464552454e5449454c535f4745524552223b693a31353b733a32393a2254524149545f41444d494e5f44415348424f4152445f41434345444552223b693a31363b733a33363a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4352454552223b693a31373b733a33373a2254524149545f41444d494e5f47455245525f5554494c49534154455552535f4c4953544552223b7d757365725f64656c65676174696f6e737c613a303a7b7d757365725f646174617c613a31353a7b733a31383a226e756d65726f5f7574696c69736174657572223b733a31333a225359532d323032352d30303031223b733a31373a226c6f67696e5f7574696c69736174657572223b733a363a2261686f2e7369223b733a31353a22656d61696c5f7072696e636970616c223b733a31393a2261686f7061756c313840676d61696c2e636f6d223b733a31333a22646174655f6372656174696f6e223b733a31393a22323032352d30372d30312032313a35353a3237223b733a31383a226465726e696572655f636f6e6e6578696f6e223b733a31393a22323032352d30372d30332031333a32333a3437223b733a32373a22646174655f65787069726174696f6e5f746f6b656e5f7265736574223b4e3b733a31323a22656d61696c5f76616c696465223b693a313b733a32393a2274656e746174697665735f636f6e6e6578696f6e5f6563686f75656573223b693a303b733a32303a22636f6d7074655f626c6f7175655f6a7573717561223b4e3b733a32323a22707265666572656e6365735f3266615f616374697665223b693a303b733a31323a2270686f746f5f70726f66696c223b4e3b733a31333a227374617475745f636f6d707465223b733a353a226163746966223b733a32313a2269645f6e69766561755f61636365735f646f6e6e65223b733a31313a2241434345535f544f54414c223b733a32313a2269645f67726f7570655f7574696c69736174657572223b733a31333a224752505f41444d494e5f535953223b733a31393a2269645f747970655f7574696c69736174657572223b733a31303a22545950455f41444d494e223b7d666c6173685f6d657373616765737c613a37313a7b693a303b613a323a7b733a343a2274797065223b733a373a2273756363657373223b733a373a226d657373616765223b733a32303a22436f6e6e6578696f6e2072c3a975737369652021223b7d693a313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a31393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a32393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a33393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a34393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a35393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36313b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36323b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36333b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36343b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36353b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36363b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36373b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36383b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a36393b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d693a37303b613a323a7b733a343a2274797065223b733a353a226572726f72223b733a373a226d657373616765223b733a37303a22566f7320696e666f726d6174696f6e732064652073657373696f6e20736f6e7420696e76616c696465732e20566575696c6c657a20766f7573207265636f6e6e65637465722e223b7d7d, 1751548197, 3600, 'SYS-2025-0001');

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

--
-- Déchargement des données de la table `specialite`
--

INSERT INTO `specialite` (`id_specialite`, `libelle_specialite`, `numero_enseignant_specialite`) VALUES
('CYBERSEC', 'Cybersécurité et Réseaux', NULL),
('E_SANTE', 'Informatique pour la Santé', NULL),
('FIN_TECH', 'Finance et Technologies (FinTech)', NULL),
('GENIE_LOG', 'Génie Logiciel', NULL),
('IA_DATA', 'Intelligence Artificielle et Science des Données', NULL),
('INFO_SCIENCES', 'Informatique et Sciences du Numérique', NULL),
('MIAGE', 'Méthodes Informatiques Appliquées à la Gestion des Entreprises', NULL),
('RESEAUX_TEL', 'Réseaux et Télécommunications', NULL);

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
('CONF_NA', 'Non Applicable'),
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

--
-- Déchargement des données de la table `statut_jury`
--

INSERT INTO `statut_jury` (`id_statut_jury`, `libelle_statut_jury`) VALUES
('JURY_DIRECTEUR', 'Directeur de Mémoire'),
('JURY_MEMBRE', 'Membre du Jury'),
('JURY_PRESIDENT', 'Président du Jury'),
('JURY_RAPPORTEUR', 'Rapporteur');

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
('PAIE_ATTENTE', 'En attente de paiement'),
('PAIE_OK', 'Payé'),
('PAIE_PARTIEL', 'Paiement partiel'),
('PAIE_RETARD', 'En retard de paiement');

-- --------------------------------------------------------

--
-- Structure de la table `statut_penalite_ref`
--

CREATE TABLE `statut_penalite_ref` (
  `id_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle_statut_penalite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut_penalite_ref`
--

INSERT INTO `statut_penalite_ref` (`id_statut_penalite`, `libelle_statut_penalite`) VALUES
('PEN_ANNULEE', 'Annulée'),
('PEN_DUE', 'Due'),
('PEN_REGLEE', 'Réglée');

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
('PV_ATTENTE_APPROBATION', 'En attente d\'approbation'),
                                                                                                  ('PV_BROUILLON', 'Brouillon'),
                                                                                                  ('PV_REJETE', 'Rejeté'),
                                                                                                  ('PV_VALIDE', 'Validé');

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
                                                                                                       ('RAP_ARCHIVE', 'Archivé', 9),
                                                                                                       ('RAP_BROUILLON', 'Brouillon', 1),
                                                                                                       ('RAP_CONF', 'Conforme', 4),
                                                                                                       ('RAP_CORRECT', 'En Correction', 6),
                                                                                                       ('RAP_EN_COMMISSION', 'En Commission', 5),
                                                                                                       ('RAP_NON_CONF', 'Non Conforme', 3),
                                                                                                       ('RAP_REFUSE', 'Refusé', 7),
                                                                                                       ('RAP_SOUMIS', 'Soumis', 2),
                                                                                                       ('RAP_VALID', 'Validé', 8);

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
                                                                                                 ('RECLA_CLOTUREE', 'Clôturée'),
                                                                                                 ('RECLA_EN_COURS', 'En cours de traitement'),
                                                                                                 ('RECLA_OUVERTE', 'Ouverte'),
                                                                                                 ('RECLA_RESOLUE', 'Résolue');

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
                              `id_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `id_parent_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ID du traitement parent pour la hiérarchie des menus',
                              `icone_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Classe CSS de l''icône associée à ce traitement (ex: fas fa-home)',
                              `url_associee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'URL ou route associée à ce traitement pour la navigation',
                              `ordre_affichage` int NOT NULL DEFAULT '0' COMMENT 'Ordre d''affichage dans le menu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `libelle_traitement`, `id_parent_traitement`, `icone_class`, `url_associee`, `ordre_affichage`) VALUES
                                                                                                                                               ('MENU_ADMINISTRATION', 'Administration', NULL, 'fas fa-cogs', NULL, 40),
                                                                                                                                               ('MENU_COMMISSION', 'Commission', NULL, 'fas fa-gavel', NULL, 30),
                                                                                                                                               ('MENU_DASHBOARDS', 'Tableaux de Bord', NULL, 'fas fa-tachometer-alt', NULL, 10),
                                                                                                                                               ('MENU_ETUDIANT', 'Espace Étudiant', NULL, 'fas fa-user-graduate', NULL, 20),
                                                                                                                                               ('MENU_GESTION_COMPTES', 'Gestion des Comptes', 'MENU_ADMINISTRATION', 'fas fa-users', NULL, 41),
                                                                                                                                               ('MENU_PERSONNEL', 'Espace Personnel', NULL, 'fas fa-user-tie', NULL, 35),
                                                                                                                                               ('MENU_RAPPORT_ETUDIANT', 'Rapports Étudiant', 'MENU_ETUDIANT', 'fas fa-file-alt', NULL, 21),
                                                                                                                                               ('TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES', 'Accéder Fichiers Protégés', NULL, NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_ACCEDER', 'Accéder Configuration', 'MENU_ADMINISTRATION', 'fas fa-sliders-h', '/../Administration/configuration', 42),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_ANNEES_GERER', 'Gérer Années Académiques', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_MENUS_GERER', 'Gérer Ordre Menus', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER', 'Gérer Modèles Documents', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_NOTIFS_GERER', 'Gérer Notifications', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_PARAMETRES_GERER', 'Gérer Paramètres Système', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER', 'Gérer Référentiels', 'TRAIT_ADMIN_CONFIG_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_ADMIN_DASHBOARD_ACCEDER', 'Accéder Dashboard Admin', 'MENU_DASHBOARDS', 'fas fa-chart-line', '/../Administration/dashboard', 11),
                                                                                                                                               ('TRAIT_ADMIN_GERER_UTILISATEURS_CREER', 'Créer Utilisateur', 'MENU_GESTION_COMPTES', 'fas fa-user-plus', '/../Administration/utilisateurs/creer', 411),
                                                                                                                                               ('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'Lister Utilisateurs', 'MENU_GESTION_COMPTES', 'fas fa-list', '/../Administration/utilisateurs/liste', 410),
                                                                                                                                               ('TRAIT_COMMISSION_DASHBOARD_ACCEDER', 'Accéder Dashboard Commission', 'MENU_DASHBOARDS', 'fas fa-clipboard-list', '/../commission/dashboard', 13),
                                                                                                                                               ('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER', 'Voter Rapport', 'MENU_COMMISSION', 'fas fa-check-circle', NULL, 31),
                                                                                                                                               ('TRAIT_ETUDIANT_DASHBOARD_ACCEDER', 'Accéder Dashboard Étudiant', 'MENU_DASHBOARDS', 'fas fa-user-graduate', '/../Etudiant/dashboard', 12),
                                                                                                                                               ('TRAIT_ETUDIANT_PROFIL_GERER', 'Gérer Profil Étudiant', 'MENU_ETUDIANT', 'fas fa-user-circle', '/../Etudiant/profil', 20),
                                                                                                                                               ('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', 'Soumettre Rapport', 'MENU_RAPPORT_ETUDIANT', 'fas fa-upload', '/../Etudiant/rapport/redaction', 211),
                                                                                                                                               ('TRAIT_ETUDIANT_RAPPORT_SUIVRE', 'Suivre Rapport', 'MENU_RAPPORT_ETUDIANT', 'fas fa-eye', '/../Etudiant/rapport/suivi', 212),
                                                                                                                                               ('TRAIT_PERS_ADMIN_CONFORMITE_LISTER', 'Lister Rapports Conformité', 'MENU_PERSONNEL', 'fas fa-clipboard-check', '/../PersonnelAdministractif/conformite/queue', 36),
                                                                                                                                               ('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER', 'Vérifier Conformité Rapport', 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', 'Accéder Dashboard Personnel', 'MENU_DASHBOARDS', 'fas fa-user-tie', '/personnel/dashboard', 14),
                                                                                                                                               ('TRAIT_PERS_ADMIN_RECLAMATIONS_GERER', 'Gérer Réclamations', 'MENU_PERSONNEL', 'fas fa-question-circle', '/personnel/reclamations', 38),
                                                                                                                                               ('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', 'Accéder Scolarité', 'MENU_PERSONNEL', 'fas fa-graduation-cap', '/personnel/scolarite/etudiants', 37),
                                                                                                                                               ('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER', 'Gérer Dossier Étudiant', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', NULL, NULL, 0),
                                                                                                                                               ('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER', 'Gérer Pénalités', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', NULL, NULL, 0);

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
                                                                                                   ('DOC_ATTESTATION', 'Attestation de Scolarité', 0),
                                                                                                   ('DOC_BULLETIN', 'Bulletin de Notes', 0),
                                                                                                   ('DOC_EXPORT', 'Export de Données', 0),
                                                                                                   ('DOC_PV', 'Procès-Verbal de Soutenance', 0),
                                                                                                   ('DOC_RAPPORT', 'Rapport de Soutenance', 1),
                                                                                                   ('DOC_RECU', 'Reçu de Paiement', 0);

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

--
-- Déchargement des données de la table `ue`
--

INSERT INTO `ue` (`id_ue`, `libelle_ue`, `credits_ue`) VALUES
                                                           ('UE_ALGO', 'Algorithmique et Structures de Données', 6),
                                                           ('UE_BDD', 'Bases de Données Avancées', 5),
                                                           ('UE_CLOUD', 'Cloud Computing et DevOps', 5),
                                                           ('UE_CYBER', 'Cybersécurité des Systèmes', 5),
                                                           ('UE_DEV_WEB', 'Développement Web Avancé', 6),
                                                           ('UE_IA', 'Intelligence Artificielle et Machine Learning', 6),
                                                           ('UE_MANAGEMENT', 'Management de Projet Informatique', 4),
                                                           ('UE_MOBILE', 'Développement Mobile', 4),
                                                           ('UE_RESEAUX', 'Réseaux et Sécurité', 5),
                                                           ('UE_STAT_DATA', 'Statistiques et Analyse de Données', 4);

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
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('SYS-2025-0001', 'aho.si', 'ahopaul18@gmail.com', '$2y$10$bghNcOSuOP.S.oxLmDMfJer/qJCFghXQreW7sbOLZgUHft2O.bjku', '2025-07-01 21:55:27', '2025-07-03 15:08:02', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                           ('SYSTEM', 'system_internal_user', 'system@gestionsoutenance.com', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '2025-07-02 20:42:27', NULL, NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN');

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
-- Index pour la table `groupe_traitement`
--
ALTER TABLE `groupe_traitement`
    ADD PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
  ADD KEY `id_traitement` (`id_traitement`);

--
-- Index pour la table `groupe_utilisateur`
--
ALTER TABLE `groupe_utilisateur`
    ADD PRIMARY KEY (`id_groupe_utilisateur`),
  ADD UNIQUE KEY `libelle_groupe` (`libelle_groupe`);

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
  ADD KEY `fk_parent_traitement` (`id_parent_traitement`);

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
-- Contraintes pour la table `groupe_traitement`
--
ALTER TABLE `groupe_traitement`
    ADD CONSTRAINT `groupe_traitement_ibfk_1` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groupe_traitement_ibfk_2` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Contraintes pour la table `traitement`
--
ALTER TABLE `traitement`
    ADD CONSTRAINT `fk_parent_traitement` FOREIGN KEY (`id_parent_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
