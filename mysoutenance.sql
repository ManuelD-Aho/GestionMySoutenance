-- Generation Time: Date et heure actuelles
-- Server version: Votre version
-- PHP Version: Votre version

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
CREATE TABLE `acquerir` (
                            `id_grade` varchar(50) NOT NULL,
                            `numero_enseignant` varchar(50) NOT NULL,
                            `date_acquisition` date NOT NULL,
                            PRIMARY KEY (`id_grade`,`numero_enseignant`),
                            KEY `idx_acquerir_enseignant` (`numero_enseignant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action`
--
DROP TABLE IF EXISTS `action`;
CREATE TABLE `action` (
                          `id_action` varchar(50) NOT NULL PRIMARY KEY,
                          `libelle_action` varchar(100) NOT NULL,
                          `categorie_action` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affecter`
--
DROP TABLE IF EXISTS `affecter`;
CREATE TABLE `affecter` (
                            `numero_enseignant` varchar(50) NOT NULL,
                            `id_rapport_etudiant` varchar(50) NOT NULL,
                            `id_statut_jury` varchar(50) NOT NULL,
                            `directeur_memoire` tinyint(1) NOT NULL DEFAULT '0',
                            `date_affectation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`numero_enseignant`,`id_rapport_etudiant`,`id_statut_jury`),
                            KEY `idx_affecter_rapport_etudiant` (`id_rapport_etudiant`),
                            KEY `idx_affecter_statut_jury` (`id_statut_jury`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--
DROP TABLE IF EXISTS `annee_academique`;
CREATE TABLE `annee_academique` (
                                    `id_annee_academique` varchar(50) NOT NULL PRIMARY KEY,
                                    `libelle_annee_academique` varchar(50) NOT NULL,
                                    `date_debut` date DEFAULT NULL,
                                    `date_fin` date DEFAULT NULL,
                                    `est_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `approuver`
--
DROP TABLE IF EXISTS `approuver`;
CREATE TABLE `approuver` (
                             `numero_personnel_administratif` varchar(50) NOT NULL,
                             `id_rapport_etudiant` varchar(50) NOT NULL,
                             `id_statut_conformite` varchar(50) NOT NULL,
                             `commentaire_conformite` text,
                             `date_verification_conformite` datetime NOT NULL,
                             PRIMARY KEY (`numero_personnel_administratif`,`id_rapport_etudiant`),
                             KEY `idx_approuver_rapport_etudiant` (`id_rapport_etudiant`),
                             KEY `fk_approuver_statut_conformite` (`id_statut_conformite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attribuer`
--
DROP TABLE IF EXISTS `attribuer`;
CREATE TABLE `attribuer` (
                             `numero_enseignant` varchar(50) NOT NULL,
                             `id_specialite` varchar(50) NOT NULL,
                             PRIMARY KEY (`numero_enseignant`,`id_specialite`),
                             KEY `idx_attribuer_specialite` (`id_specialite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--
DROP TABLE IF EXISTS `compte_rendu`;
CREATE TABLE `compte_rendu` (
                                `id_compte_rendu` varchar(50) NOT NULL PRIMARY KEY,
                                `id_rapport_etudiant` varchar(50) DEFAULT NULL,
                                `type_pv` enum('Individuel','Session') NOT NULL DEFAULT 'Individuel',
                                `libelle_compte_rendu` text NOT NULL,
                                `date_creation_pv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `id_statut_pv` varchar(50) NOT NULL,
                                `id_redacteur` varchar(50) DEFAULT NULL,
                                KEY `idx_compte_rendu_rapport_etudiant` (`id_rapport_etudiant`),
                                KEY `idx_compte_rendu_redacteur` (`id_redacteur`),
                                KEY `fk_compte_rendu_statut_pv` (`id_statut_pv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversation`
--
DROP TABLE IF EXISTS `conversation`;
CREATE TABLE `conversation` (
                                `id_conversation` varchar(50) NOT NULL PRIMARY KEY,
                                `nom_conversation` varchar(255) DEFAULT NULL,
                                `date_creation_conv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `type_conversation` enum('Direct','Groupe') NOT NULL DEFAULT 'Direct'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_passage_ref`
--
DROP TABLE IF EXISTS `decision_passage_ref`;
CREATE TABLE `decision_passage_ref` (
                                        `id_decision_passage` varchar(50) NOT NULL PRIMARY KEY,
                                        `libelle_decision_passage` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_validation_pv_ref`
--
DROP TABLE IF EXISTS `decision_validation_pv_ref`;
CREATE TABLE `decision_validation_pv_ref` (
                                              `id_decision_validation_pv` varchar(50) NOT NULL PRIMARY KEY,
                                              `libelle_decision_validation_pv` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `decision_vote_ref`
--
DROP TABLE IF EXISTS `decision_vote_ref`;
CREATE TABLE `decision_vote_ref` (
                                     `id_decision_vote` varchar(50) NOT NULL PRIMARY KEY,
                                     `libelle_decision_vote` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecue`
--
DROP TABLE IF EXISTS `ecue`;
CREATE TABLE `ecue` (
                        `id_ecue` varchar(50) NOT NULL PRIMARY KEY,
                        `libelle_ecue` varchar(100) NOT NULL,
                        `id_ue` varchar(50) NOT NULL,
                        `credits_ecue` int DEFAULT NULL,
                        KEY `idx_ecue_ue` (`id_ue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enregistrer` (Audit Log)
--
DROP TABLE IF EXISTS `enregistrer`;
CREATE TABLE `enregistrer` (
                               `id_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                               `numero_utilisateur` varchar(50) NOT NULL,
                               `id_action` varchar(50) NOT NULL,
                               `date_action` datetime NOT NULL,
                               `adresse_ip` varchar(45) DEFAULT NULL,
                               `user_agent` text,
                               `id_entite_concernee` varchar(50) DEFAULT NULL,
                               `type_entite_concernee` varchar(50) DEFAULT NULL,
                               `details_action` json DEFAULT NULL,
                               `session_id_utilisateur` varchar(255) DEFAULT NULL,
                               KEY `idx_enregistrer_action` (`id_action`),
                               KEY `idx_enregistrer_user_date` (`numero_utilisateur`, `date_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--
DROP TABLE IF EXISTS `enseignant`;
CREATE TABLE `enseignant` (
                              `numero_enseignant` varchar(50) NOT NULL PRIMARY KEY,
                              `nom` varchar(100) NOT NULL,
                              `prenom` varchar(100) NOT NULL,
                              `telephone_professionnel` varchar(20) DEFAULT NULL,
                              `email_professionnel` varchar(255) DEFAULT NULL,
                              `numero_utilisateur` varchar(50) NOT NULL,
                              `date_naissance` date DEFAULT NULL,
                              `lieu_naissance` varchar(100) DEFAULT NULL,
                              `pays_naissance` varchar(50) DEFAULT NULL,
                              `nationalite` varchar(50) DEFAULT NULL,
                              `sexe` enum('Masculin','Féminin','Autre') DEFAULT NULL,
                              `adresse_postale` text,
                              `ville` varchar(100) DEFAULT NULL,
                              `code_postal` varchar(20) DEFAULT NULL,
                              `telephone_personnel` varchar(20) DEFAULT NULL,
                              `email_personnel_secondaire` varchar(255) DEFAULT NULL,
                              UNIQUE KEY `uq_enseignant_numero_utilisateur` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--
DROP TABLE IF EXISTS `entreprise`;
CREATE TABLE `entreprise` (
                              `id_entreprise` varchar(50) NOT NULL PRIMARY KEY,
                              `libelle_entreprise` varchar(200) NOT NULL,
                              `secteur_activite` varchar(100) DEFAULT NULL,
                              `adresse_entreprise` text,
                              `contact_nom` varchar(100) DEFAULT NULL,
                              `contact_email` varchar(255) DEFAULT NULL,
                              `contact_telephone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--
DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE `etudiant` (
                            `numero_carte_etudiant` varchar(50) NOT NULL PRIMARY KEY,
                            `nom` varchar(100) NOT NULL,
                            `prenom` varchar(100) NOT NULL,
                            `date_naissance` date DEFAULT NULL,
                            `lieu_naissance` varchar(100) DEFAULT NULL,
                            `pays_naissance` varchar(50) DEFAULT NULL,
                            `nationalite` varchar(50) DEFAULT NULL,
                            `sexe` enum('Masculin','Féminin','Autre') DEFAULT NULL,
                            `adresse_postale` text,
                            `ville` varchar(100) DEFAULT NULL,
                            `code_postal` varchar(20) DEFAULT NULL,
                            `telephone` varchar(20) DEFAULT NULL,
                            `email_contact_secondaire` varchar(255) DEFAULT NULL,
                            `numero_utilisateur` varchar(50) NOT NULL,
                            `contact_urgence_nom` varchar(100) DEFAULT NULL,
                            `contact_urgence_telephone` varchar(20) DEFAULT NULL,
                            `contact_urgence_relation` varchar(50) DEFAULT NULL,
                            UNIQUE KEY `uq_etudiant_numero_utilisateur` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--
DROP TABLE IF EXISTS `evaluer`;
CREATE TABLE `evaluer` (
                           `numero_carte_etudiant` varchar(50) NOT NULL,
                           `id_ecue` varchar(50) NOT NULL,
                           `date_evaluation` datetime NOT NULL,
                           `note` decimal(5,2) DEFAULT NULL,
                           PRIMARY KEY (`numero_carte_etudiant`,`id_ecue`),
                           KEY `idx_evaluer_ecue` (`id_ecue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `faire_stage`
--
DROP TABLE IF EXISTS `faire_stage`;
CREATE TABLE `faire_stage` (
                               `id_entreprise` varchar(50) NOT NULL,
                               `numero_carte_etudiant` varchar(50) NOT NULL,
                               `date_debut_stage` date NOT NULL,
                               `date_fin_stage` date DEFAULT NULL,
                               `sujet_stage` text,
                               `nom_tuteur_entreprise` varchar(100) DEFAULT NULL,
                               PRIMARY KEY (`id_entreprise`,`numero_carte_etudiant`),
                               KEY `idx_faire_stage_etudiant` (`numero_carte_etudiant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--
DROP TABLE IF EXISTS `fonction`;
CREATE TABLE `fonction` (
                            `id_fonction` varchar(50) NOT NULL PRIMARY KEY,
                            `libelle_fonction` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--
DROP TABLE IF EXISTS `grade`;
CREATE TABLE `grade` (
                         `id_grade` varchar(50) NOT NULL PRIMARY KEY,
                         `libelle_grade` varchar(50) NOT NULL,
                         `abreviation_grade` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--
DROP TABLE IF EXISTS `groupe_utilisateur`;
CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` varchar(50) NOT NULL PRIMARY KEY,
                                      `libelle_groupe_utilisateur` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_mot_de_passe`
--
DROP TABLE IF EXISTS `historique_mot_de_passe`;
CREATE TABLE `historique_mot_de_passe` (
                                           `id_historique_mdp` varchar(50) NOT NULL PRIMARY KEY,
                                           `numero_utilisateur` varchar(50) NOT NULL,
                                           `mot_de_passe_hache` varchar(255) NOT NULL,
                                           `date_changement` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                           KEY `idx_hist_user_mdp` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscrire`
--
DROP TABLE IF EXISTS `inscrire`;
CREATE TABLE `inscrire` (
                            `numero_carte_etudiant` varchar(50) NOT NULL,
                            `id_niveau_etude` varchar(50) NOT NULL,
                            `id_annee_academique` varchar(50) NOT NULL,
                            `montant_inscription` decimal(10,2) NOT NULL,
                            `date_inscription` datetime NOT NULL,
                            `id_statut_paiement` varchar(50) NOT NULL,
                            `date_paiement` datetime DEFAULT NULL,
                            `numero_recu_paiement` varchar(50) DEFAULT NULL,
                            `id_decision_passage` varchar(50) DEFAULT NULL,
                            PRIMARY KEY (`numero_carte_etudiant`,`id_niveau_etude`,`id_annee_academique`),
                            UNIQUE KEY `uq_inscrire_numero_recu` (`numero_recu_paiement`),
                            KEY `idx_inscrire_niveau_etude` (`id_niveau_etude`),
                            KEY `idx_inscrire_annee_academique` (`id_annee_academique`),
                            KEY `fk_inscrire_statut_paiement` (`id_statut_paiement`),
                            KEY `fk_inscrire_decision_passage` (`id_decision_passage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lecture_message`
--
DROP TABLE IF EXISTS `lecture_message`;
CREATE TABLE `lecture_message` (
                                   `id_message_chat` varchar(50) NOT NULL,
                                   `numero_utilisateur` varchar(50) NOT NULL,
                                   `date_lecture` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   PRIMARY KEY (`id_message_chat`,`numero_utilisateur`),
                                   KEY `idx_lm_user` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_chat`
--
DROP TABLE IF EXISTS `message_chat`;
CREATE TABLE `message_chat` (
                                `id_message_chat` varchar(50) NOT NULL PRIMARY KEY,
                                `id_conversation` varchar(50) NOT NULL,
                                `numero_utilisateur_expediteur` varchar(50) NOT NULL,
                                `contenu_message` text NOT NULL,
                                `date_envoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                KEY `idx_mc_conv` (`id_conversation`),
                                KEY `idx_mc_user` (`numero_utilisateur_expediteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_acces_donne`
--
DROP TABLE IF EXISTS `niveau_acces_donne`;
CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` varchar(50) NOT NULL PRIMARY KEY,
                                      `libelle_niveau_acces_donne` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etude`
--
DROP TABLE IF EXISTS `niveau_etude`;
CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` varchar(50) NOT NULL PRIMARY KEY,
                                `libelle_niveau_etude` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--
DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
                                `id_notification` varchar(50) NOT NULL PRIMARY KEY,
                                `libelle_notification` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `occuper`
--
DROP TABLE IF EXISTS `occuper`;
CREATE TABLE `occuper` (
                           `id_fonction` varchar(50) NOT NULL,
                           `numero_enseignant` varchar(50) NOT NULL,
                           `date_debut_occupation` date NOT NULL,
                           `date_fin_occupation` date DEFAULT NULL,
                           PRIMARY KEY (`id_fonction`,`numero_enseignant`),
                           KEY `idx_occuper_enseignant` (`numero_enseignant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participant_conversation`
--
DROP TABLE IF EXISTS `participant_conversation`;
CREATE TABLE `participant_conversation` (
                                            `id_conversation` varchar(50) NOT NULL,
                                            `numero_utilisateur` varchar(50) NOT NULL,
                                            PRIMARY KEY (`id_conversation`,`numero_utilisateur`),
                                            KEY `idx_pc_user` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `penalite`
--
DROP TABLE IF EXISTS `penalite`;
CREATE TABLE `penalite` (
                            `id_penalite` varchar(50) NOT NULL PRIMARY KEY,
                            `numero_carte_etudiant` varchar(50) NOT NULL,
                            `id_statut_penalite` varchar(50) NOT NULL,
                            `montant_penalite` decimal(10,2) DEFAULT NULL,
                            `motif_penalite` text,
                            `date_creation` datetime NOT NULL,
                            `date_resolution` datetime DEFAULT NULL,
                            `numero_personnel_administratif_resolution` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--
DROP TABLE IF EXISTS `personnel_administratif`;
CREATE TABLE `personnel_administratif` (
                                           `numero_personnel_administratif` varchar(50) NOT NULL PRIMARY KEY,
                                           `nom` varchar(100) NOT NULL,
                                           `prenom` varchar(100) NOT NULL,
                                           `telephone_professionnel` varchar(20) DEFAULT NULL,
                                           `email_professionnel` varchar(255) DEFAULT NULL,
                                           `date_affectation_service` date DEFAULT NULL,
                                           `responsabilites_cles` text,
                                           `numero_utilisateur` varchar(50) NOT NULL,
                                           `date_naissance` date DEFAULT NULL,
                                           `lieu_naissance` varchar(100) DEFAULT NULL,
                                           `pays_naissance` varchar(50) DEFAULT NULL,
                                           `nationalite` varchar(50) DEFAULT NULL,
                                           `sexe` enum('Masculin','Féminin','Autre') DEFAULT NULL,
                                           `adresse_postale` text,
                                           `ville` varchar(100) DEFAULT NULL,
                                           `code_postal` varchar(20) DEFAULT NULL,
                                           `telephone_personnel` varchar(20) DEFAULT NULL,
                                           `email_personnel_secondaire` varchar(255) DEFAULT NULL,
                                           UNIQUE KEY `uq_personnel_numero_utilisateur` (`numero_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pister`
--
DROP TABLE IF EXISTS `pister`;
CREATE TABLE `pister` (
                          `numero_utilisateur` varchar(50) NOT NULL,
                          `id_traitement` varchar(50) NOT NULL,
                          `date_pister` datetime NOT NULL,
                          `acceder` tinyint(1) NOT NULL,
                          PRIMARY KEY (`numero_utilisateur`,`id_traitement`,`date_pister`),
                          KEY `idx_pister_traitement` (`id_traitement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pv_session_rapport`
--
DROP TABLE IF EXISTS `pv_session_rapport`;
CREATE TABLE `pv_session_rapport` (
                                      `id_compte_rendu` varchar(50) NOT NULL,
                                      `id_rapport_etudiant` varchar(50) NOT NULL,
                                      PRIMARY KEY (`id_compte_rendu`,`id_rapport_etudiant`),
                                      KEY `idx_pvsr_rapport` (`id_rapport_etudiant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_etudiant`
--
DROP TABLE IF EXISTS `rapport_etudiant`;
CREATE TABLE `rapport_etudiant` (
                                    `id_rapport_etudiant` varchar(50) NOT NULL PRIMARY KEY,
                                    `libelle_rapport_etudiant` text NOT NULL,
                                    `theme` varchar(255) DEFAULT NULL,
                                    `resume` text,
                                    `numero_attestation_stage` varchar(100) DEFAULT NULL,
                                    `numero_carte_etudiant` varchar(50) NOT NULL,
                                    `nombre_pages` int DEFAULT NULL,
                                    `id_statut_rapport` varchar(50) NOT NULL,
                                    `date_soumission` datetime DEFAULT NULL,
                                    `date_derniere_modif` datetime DEFAULT NULL,
                                    KEY `idx_rapport_etudiant_etudiant` (`numero_carte_etudiant`),
                                    KEY `fk_rapport_statut` (`id_statut_rapport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--
DROP TABLE IF EXISTS `rattacher`;
CREATE TABLE `rattacher` (
                             `id_groupe_utilisateur` varchar(50) NOT NULL,
                             `id_traitement` varchar(50) NOT NULL,
                             PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
                             KEY `idx_rattacher_traitement` (`id_traitement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recevoir`
--
DROP TABLE IF EXISTS `recevoir`;
CREATE TABLE `recevoir` (
                            `numero_utilisateur` varchar(50) NOT NULL,
                            `id_notification` varchar(50) NOT NULL,
                            `date_reception` datetime NOT NULL,
                            `lue` tinyint(1) NOT NULL DEFAULT '0',
                            `date_lecture` datetime DEFAULT NULL,
                            PRIMARY KEY (`numero_utilisateur`,`id_notification`,`date_reception`),
                            KEY `idx_recevoir_notification` (`id_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--
DROP TABLE IF EXISTS `reclamation`;
CREATE TABLE `reclamation` (
                               `id_reclamation` varchar(50) NOT NULL PRIMARY KEY,
                               `numero_carte_etudiant` varchar(50) NOT NULL,
                               `sujet_reclamation` varchar(255) NOT NULL,
                               `description_reclamation` text NOT NULL,
                               `date_soumission` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `id_statut_reclamation` varchar(50) NOT NULL,
                               `reponse_reclamation` text,
                               `date_reponse` datetime DEFAULT NULL,
                               `numero_personnel_traitant` varchar(50) DEFAULT NULL,
                               KEY `idx_reclam_etudiant` (`numero_carte_etudiant`),
                               KEY `idx_reclam_personnel` (`numero_personnel_traitant`),
                               KEY `fk_reclam_statut` (`id_statut_reclamation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendre`
--
DROP TABLE IF EXISTS `rendre`;
CREATE TABLE `rendre` (
                          `numero_enseignant` varchar(50) NOT NULL,
                          `id_compte_rendu` varchar(50) NOT NULL,
                          `date_action_sur_pv` datetime DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`numero_enseignant`,`id_compte_rendu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `section_rapport`
--
DROP TABLE IF EXISTS `section_rapport`;
CREATE TABLE `section_rapport` (
                                   `id_section_rapport` VARCHAR(50) NOT NULL PRIMARY KEY,
                                   `id_rapport_etudiant` VARCHAR(50) NOT NULL,
                                   `id_type_document` VARCHAR(50) NOT NULL COMMENT 'FK vers type_document_ref pour le type de section (ex: Introduction, Conclusion)',
                                   `contenu_textuel` LONGTEXT COMMENT 'Contenu textuel de la section saisi par l''étudiant',
                                   `version` INT UNSIGNED NOT NULL DEFAULT 1,
                                   `date_soumission_version` DATETIME NOT NULL,
                                   `numero_utilisateur_soumission` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sequences`
--
DROP TABLE IF EXISTS `sequences`;
CREATE TABLE `sequences` (
                             `nom_sequence` varchar(50) NOT NULL,
                             `annee` year NOT NULL,
                             `valeur_actuelle` int unsigned NOT NULL DEFAULT '0',
                             PRIMARY KEY (`nom_sequence`,`annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session_rapport`
--
DROP TABLE IF EXISTS `session_rapport`;
CREATE TABLE `session_rapport` (
                                   `id_session` varchar(50) NOT NULL,
                                   `id_rapport_etudiant` varchar(50) NOT NULL,
                                   PRIMARY KEY (`id_session`,`id_rapport_etudiant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session_validation`
--
DROP TABLE IF EXISTS `session_validation`;
CREATE TABLE `session_validation` (
                                      `id_session` varchar(50) NOT NULL PRIMARY KEY,
                                      `id_annee_academique` varchar(50) NOT NULL,
                                      `libelle_session` varchar(255) NOT NULL,
                                      `date_debut_session` date DEFAULT NULL,
                                      `date_fin_prevue` date DEFAULT NULL,
                                      `statut_session` enum('Planifiee','En cours','Cloturee','Archivee') NOT NULL DEFAULT 'Planifiee',
                                      `id_president_commission` varchar(50) DEFAULT NULL COMMENT 'FK vers enseignant.numero_enseignant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions` (pour les permissions dynamiques)
--
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
                            `session_id` VARCHAR(128) NOT NULL PRIMARY KEY,
                            `session_data` BLOB NOT NULL,
                            `session_lifetime` INT NOT NULL,
                            `session_time` INT UNSIGNED NOT NULL,
                            `user_id` VARCHAR(50) NULL,
                            KEY `idx_sessions_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--
DROP TABLE IF EXISTS `specialite`;
CREATE TABLE `specialite` (
                              `id_specialite` varchar(50) NOT NULL PRIMARY KEY,
                              `libelle_specialite` varchar(100) NOT NULL,
                              `numero_enseignant_specialite` varchar(50) DEFAULT NULL,
                              KEY `fk_specialite_enseignant` (`numero_enseignant_specialite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_conformite_ref`
--
DROP TABLE IF EXISTS `statut_conformite_ref`;
CREATE TABLE `statut_conformite_ref` (
                                         `id_statut_conformite` varchar(50) NOT NULL PRIMARY KEY,
                                         `libelle_statut_conformite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_jury`
--
DROP TABLE IF EXISTS `statut_jury`;
CREATE TABLE `statut_jury` (
                               `id_statut_jury` varchar(50) NOT NULL PRIMARY KEY,
                               `libelle_statut_jury` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_paiement_ref`
--
DROP TABLE IF EXISTS `statut_paiement_ref`;
CREATE TABLE `statut_paiement_ref` (
                                       `id_statut_paiement` varchar(50) NOT NULL PRIMARY KEY,
                                       `libelle_statut_paiement` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_penalite_ref`
--
DROP TABLE IF EXISTS `statut_penalite_ref`;
CREATE TABLE `statut_penalite_ref` (
                                       `id_statut_penalite` varchar(50) NOT NULL PRIMARY KEY,
                                       `libelle_statut_penalite` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_pv_ref`
--
DROP TABLE IF EXISTS `statut_pv_ref`;
CREATE TABLE `statut_pv_ref` (
                                 `id_statut_pv` varchar(50) NOT NULL PRIMARY KEY,
                                 `libelle_statut_pv` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_rapport_ref`
--
DROP TABLE IF EXISTS `statut_rapport_ref`;
CREATE TABLE `statut_rapport_ref` (
                                      `id_statut_rapport` varchar(50) NOT NULL PRIMARY KEY,
                                      `libelle_statut_rapport` varchar(100) NOT NULL,
                                      `etape_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_reclamation_ref`
--
DROP TABLE IF EXISTS `statut_reclamation_ref`;
CREATE TABLE `statut_reclamation_ref` (
                                          `id_statut_reclamation` varchar(50) NOT NULL PRIMARY KEY,
                                          `libelle_statut_reclamation` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--
DROP TABLE IF EXISTS `traitement`;
CREATE TABLE `traitement` (
                              `id_traitement` varchar(50) NOT NULL PRIMARY KEY,
                              `libelle_traitement` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_document_ref`
--
DROP TABLE IF EXISTS `type_document_ref`;
CREATE TABLE `type_document_ref` (
                                     `id_type_document` varchar(50) NOT NULL PRIMARY KEY,
                                     `libelle_type_document` varchar(100) NOT NULL,
                                     `requis_ou_non` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_utilisateur`
--
DROP TABLE IF EXISTS `type_utilisateur`;
CREATE TABLE `type_utilisateur` (
                                    `id_type_utilisateur` varchar(50) NOT NULL PRIMARY KEY,
                                    `libelle_type_utilisateur` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--
DROP TABLE IF EXISTS `ue`;
CREATE TABLE `ue` (
                      `id_ue` varchar(50) NOT NULL PRIMARY KEY,
                      `libelle_ue` varchar(100) NOT NULL,
                      `credits_ue` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--
DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
                               `numero_utilisateur` varchar(50) NOT NULL PRIMARY KEY,
                               `login_utilisateur` varchar(100) NOT NULL,
                               `email_principal` varchar(255) DEFAULT NULL,
                               `mot_de_passe` varchar(255) NOT NULL,
                               `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `derniere_connexion` datetime DEFAULT NULL,
                               `token_reset_mdp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                               `date_expiration_token_reset` datetime DEFAULT NULL,
                               `token_validation_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                               `email_valide` tinyint(1) NOT NULL DEFAULT '0',
                               `tentatives_connexion_echouees` int UNSIGNED NOT NULL DEFAULT '0',
                               `compte_bloque_jusqua` datetime DEFAULT NULL,
                               `preferences_2fa_active` tinyint(1) NOT NULL DEFAULT '0',
                               `secret_2fa` varchar(255) DEFAULT NULL,
                               `photo_profil` varchar(255) DEFAULT NULL,
                               `statut_compte` enum('actif','inactif','bloque','en_attente_validation','archive') NOT NULL DEFAULT 'en_attente_validation',
                               `id_niveau_acces_donne` varchar(50) NOT NULL,
                               `id_groupe_utilisateur` varchar(50) NOT NULL,
                               `id_type_utilisateur` varchar(50) NOT NULL,
                               UNIQUE KEY `uq_utilisateur_login` (`login_utilisateur`),
                               UNIQUE KEY `uq_email_principal` (`email_principal`),
                               KEY `idx_utilisateur_niveau_acces` (`id_niveau_acces_donne`),
                               KEY `idx_utilisateur_groupe` (`id_groupe_utilisateur`),
                               KEY `idx_utilisateur_type` (`id_type_utilisateur`),
                               KEY `idx_token_reset_mdp` (`token_reset_mdp`),
                               KEY `idx_token_validation_email` (`token_validation_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `validation_pv`
--
DROP TABLE IF EXISTS `validation_pv`;
CREATE TABLE `validation_pv` (
                                 `id_compte_rendu` varchar(50) NOT NULL,
                                 `numero_enseignant` varchar(50) NOT NULL,
                                 `id_decision_validation_pv` varchar(50) NOT NULL,
                                 `date_validation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `commentaire_validation_pv` text,
                                 PRIMARY KEY (`id_compte_rendu`,`numero_enseignant`),
                                 KEY `idx_valpv_enseignant` (`numero_enseignant`),
                                 KEY `fk_valpv_decision` (`id_decision_validation_pv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vote_commission`
--
DROP TABLE IF EXISTS `vote_commission`;
CREATE TABLE `vote_commission` (
                                   `id_vote` varchar(50) NOT NULL PRIMARY KEY,
                                   `id_session` varchar(50) NOT NULL,
                                   `id_rapport_etudiant` varchar(50) NOT NULL,
                                   `numero_enseignant` varchar(50) NOT NULL,
                                   `id_decision_vote` varchar(50) NOT NULL,
                                   `commentaire_vote` text,
                                   `date_vote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `tour_vote` int NOT NULL DEFAULT '1',
                                   KEY `idx_vote_session_rapport` (`id_session`, `id_rapport_etudiant`),
                                   KEY `idx_vote_enseignant` (`numero_enseignant`),
                                   KEY `fk_vote_decision` (`id_decision_vote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

ALTER TABLE `acquerir`
    ADD CONSTRAINT `fk_acquerir_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_acquerir_grade` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `affecter`
    ADD CONSTRAINT `fk_affecter_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_affecter_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_affecter_statut_jury` FOREIGN KEY (`id_statut_jury`) REFERENCES `statut_jury` (`id_statut_jury`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `approuver`
    ADD CONSTRAINT `fk_approuver_personnel` FOREIGN KEY (`numero_personnel_administratif`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approuver_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approuver_statut_conformite` FOREIGN KEY (`id_statut_conformite`) REFERENCES `statut_conformite_ref` (`id_statut_conformite`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `attribuer`
    ADD CONSTRAINT `fk_attribuer_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attribuer_specialite` FOREIGN KEY (`id_specialite`) REFERENCES `specialite` (`id_specialite`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `compte_rendu`
    ADD CONSTRAINT `fk_compte_rendu_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compte_rendu_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compte_rendu_statut_pv` FOREIGN KEY (`id_statut_pv`) REFERENCES `statut_pv_ref` (`id_statut_pv`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `ecue`
    ADD CONSTRAINT `fk_ecue_ue` FOREIGN KEY (`id_ue`) REFERENCES `ue` (`id_ue`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `enregistrer`
    ADD CONSTRAINT `fk_enregistrer_action` FOREIGN KEY (`id_action`) REFERENCES `action` (`id_action`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enregistrer_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `enseignant`
    ADD CONSTRAINT `fk_enseignant_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `etudiant`
    ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `evaluer`
    ADD CONSTRAINT `fk_evaluer_ecue` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evaluer_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `faire_stage`
    ADD CONSTRAINT `fk_faire_stage_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_faire_stage_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `historique_mot_de_passe`
    ADD CONSTRAINT `fk_hist_utilisateur_mdp` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE;

ALTER TABLE `inscrire`
    ADD CONSTRAINT `fk_inscrire_annee_academique` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique` (`id_annee_academique`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_decision_passage` FOREIGN KEY (`id_decision_passage`) REFERENCES `decision_passage_ref` (`id_decision_passage`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_niveau_etude` FOREIGN KEY (`id_niveau_etude`) REFERENCES `niveau_etude` (`id_niveau_etude`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscrire_statut_paiement` FOREIGN KEY (`id_statut_paiement`) REFERENCES `statut_paiement_ref` (`id_statut_paiement`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `lecture_message`
    ADD CONSTRAINT `fk_lm_message` FOREIGN KEY (`id_message_chat`) REFERENCES `message_chat` (`id_message_chat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_user` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `message_chat`
    ADD CONSTRAINT `fk_mc_conv` FOREIGN KEY (`id_conversation`) REFERENCES `conversation` (`id_conversation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_user` FOREIGN KEY (`numero_utilisateur_expediteur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `occuper`
    ADD CONSTRAINT `fk_occuper_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_occuper_fonction` FOREIGN KEY (`id_fonction`) REFERENCES `fonction` (`id_fonction`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `participant_conversation`
    ADD CONSTRAINT `fk_pc_conv` FOREIGN KEY (`id_conversation`) REFERENCES `conversation` (`id_conversation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_user` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `penalite`
    ADD CONSTRAINT `fk_penalite_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penalite_statut` FOREIGN KEY (`id_statut_penalite`) REFERENCES `statut_penalite_ref` (`id_statut_penalite`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_penalite_personnel` FOREIGN KEY (`numero_personnel_administratif_resolution`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE SET NULL;


ALTER TABLE `personnel_administratif`
    ADD CONSTRAINT `fk_personnel_administratif_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pister`
    ADD CONSTRAINT `fk_pister_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pister_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pv_session_rapport`
    ADD CONSTRAINT `fk_pvsr_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pvsr_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `rapport_etudiant`
    ADD CONSTRAINT `fk_rapport_etudiant_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rapport_statut` FOREIGN KEY (`id_statut_rapport`) REFERENCES `statut_rapport_ref` (`id_statut_rapport`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `rattacher`
    ADD CONSTRAINT `fk_rattacher_groupe_utilisateur` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rattacher_traitement` FOREIGN KEY (`id_traitement`) REFERENCES `traitement` (`id_traitement`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `recevoir`
    ADD CONSTRAINT `fk_recevoir_notification` FOREIGN KEY (`id_notification`) REFERENCES `notification` (`id_notification`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recevoir_utilisateur` FOREIGN KEY (`numero_utilisateur`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `reclamation`
    ADD CONSTRAINT `fk_reclam_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reclam_personnel` FOREIGN KEY (`numero_personnel_traitant`) REFERENCES `personnel_administratif` (`numero_personnel_administratif`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reclam_statut` FOREIGN KEY (`id_statut_reclamation`) REFERENCES `statut_reclamation_ref` (`id_statut_reclamation`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `rendre`
    ADD CONSTRAINT `fk_rendre_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rendre_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `section_rapport`
    ADD CONSTRAINT `fk_sr_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant`(`id_rapport_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sr_type` FOREIGN KEY (`id_type_document`) REFERENCES `type_document_ref`(`id_type_document`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_sr_user` FOREIGN KEY (`numero_utilisateur_soumission`) REFERENCES `utilisateur`(`numero_utilisateur`) ON DELETE RESTRICT;

ALTER TABLE `session_rapport`
    ADD CONSTRAINT `fk_session_rapport_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation`(`id_session`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_session_rapport_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant`(`id_rapport_etudiant`) ON DELETE CASCADE;

ALTER TABLE `session_validation`
    ADD CONSTRAINT `fk_sv_annee` FOREIGN KEY (`id_annee_academique`) REFERENCES `annee_academique`(`id_annee_academique`);
-- La clé étrangère pour id_president_commission est intentionnellement omise ici pour éviter une dépendance circulaire complexe, à gérer au niveau applicatif.

ALTER TABLE `sessions`
    ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`numero_utilisateur`) ON DELETE CASCADE;

ALTER TABLE `specialite`
    ADD CONSTRAINT `fk_specialite_enseignant` FOREIGN KEY (`numero_enseignant_specialite`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `utilisateur`
    ADD CONSTRAINT `fk_utilisateur_groupe` FOREIGN KEY (`id_groupe_utilisateur`) REFERENCES `groupe_utilisateur` (`id_groupe_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_utilisateur_niveau_acces` FOREIGN KEY (`id_niveau_acces_donne`) REFERENCES `niveau_acces_donne` (`id_niveau_acces_donne`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_utilisateur_type` FOREIGN KEY (`id_type_utilisateur`) REFERENCES `type_utilisateur` (`id_type_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `validation_pv`
    ADD CONSTRAINT `fk_valpv_compte_rendu` FOREIGN KEY (`id_compte_rendu`) REFERENCES `compte_rendu` (`id_compte_rendu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_valpv_decision` FOREIGN KEY (`id_decision_validation_pv`) REFERENCES `decision_validation_pv_ref` (`id_decision_validation_pv`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_valpv_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `vote_commission`
    ADD CONSTRAINT `fk_vote_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation`(`id_session`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant`(`id_rapport_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_decision` FOREIGN KEY (`id_decision_vote`) REFERENCES `decision_vote_ref` (`id_decision_vote`) ON DELETE RESTRICT;

--
-- Déchargement des données des tables
--

INSERT INTO `decision_passage_ref` (`id_decision_passage`, `libelle_decision_passage`) VALUES
                                                                                           ('DP_ADMIS', 'Admis'), ('DP_AJOURNE', 'Ajourné'), ('DP_REDOUBLE', 'Redoublement autorisé'), ('DP_EXCLU', 'Exclu');

INSERT INTO `decision_validation_pv_ref` (`id_decision_validation_pv`, `libelle_decision_validation_pv`) VALUES
                                                                                                             ('DV_PV_APPROUVE', 'Approuvé'), ('DV_PV_MODIF', 'Modif Demandée');

INSERT INTO `decision_vote_ref` (`id_decision_vote`, `libelle_decision_vote`) VALUES
                                                                                  ('DV_APPROUVE', 'Approuvé'), ('DV_REFUSE', 'Refusé'), ('DV_DISCUSSION', 'Discussion');

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `libelle_groupe_utilisateur`) VALUES
                                                                                             ('GRP_ADMIN_SYS', 'Adminstrateur Système'), ('GRP_ETUDIANT', 'Étudiants'), ('GRP_AGENT_CONF', 'Agent de Conformité'), ('GRP_GEST_SCOL', 'Gestionnaire Scolarité'), ('GRP_COMMISSION', 'Commission Membres');

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `libelle_niveau_acces_donne`) VALUES
                                                                                             ('ACCES_TOTAL', 'Total'), ('ACCES_PERSONNEL', 'Personnel'), ('ACCES_SERVICE', 'Service');

INSERT INTO `statut_conformite_ref` (`id_statut_conformite`, `libelle_statut_conformite`) VALUES
                                                                                              ('CONF_OK', 'Conforme'), ('CONF_NOK', 'Non Conforme');

INSERT INTO `statut_paiement_ref` (`id_statut_paiement`, `libelle_statut_paiement`) VALUES
                                                                                        ('PAIE_OK', 'Payé'), ('PAIE_PARTIEL', 'Partiellement Payé'), ('PAIE_NOK', 'Non Payé'), ('PAIE_EXONERE', 'Exonéré');

INSERT INTO `statut_penalite_ref` (`id_statut_penalite`, `libelle_statut_penalite`) VALUES
                                                                                        ('PEN_DUE', 'Due'), ('PEN_REGLEE', 'Réglée'), ('PEN_ANNULEE', 'Annulée');

INSERT INTO `statut_pv_ref` (`id_statut_pv`, `libelle_statut_pv`) VALUES
                                                                      ('PV_BROUILLON', 'Brouillon'), ('PV_SOUMIS_VALID', 'Soumis pour Validation'), ('PV_VALID', 'Validé'), ('PV_MODIF_REQ', 'Modification Requise');

INSERT INTO `statut_rapport_ref` (`id_statut_rapport`, `libelle_statut_rapport`, `etape_workflow`) VALUES
                                                                                                       ('RAP_BROUILLON', 'Brouillon', 1), ('RAP_SOUMIS', 'Soumis', 2), ('RAP_NON_CONF', 'Retourné Non Conforme', 3), ('RAP_CORRECT_SOUMISES_CONF', 'Corrections Soumises (Conf.)', 2), ('RAP_CONF', 'Conforme (Transmis Commission)', 4), ('RAP_EN_COMM', 'En Cours d''Évaluation Commission', 5), ('RAP_COMM_DISCUSSION', 'En Délibération Commission', 5), ('RAP_ATTENTE_PV_VALID', 'Décision Prise - Attente PV', 6), ('RAP_CORRECT', 'Validé avec Corrections Mineures', 7), ('RAP_VALID', 'Validé', 8), ('RAP_REFUSE', 'Refusé', 9), ('RAP_REFUSE_ARCHIVE', 'Refusé (Archivé)', 10);

INSERT INTO `statut_reclamation_ref` (`id_statut_reclamation`, `libelle_statut_reclamation`) VALUES
                                                                                                 ('RECLAM_RECUE', 'Reçue'), ('RECLAM_EN_COURS', 'En Cours'), ('RECLAM_REPONDUE', 'Répondue'), ('RECLAM_CLOTUREE', 'Clôturée');

INSERT INTO `type_document_ref` (`id_type_document`, `libelle_type_document`, `requis_ou_non`) VALUES
                                                                                                   ('DOC_RAP_MAIN', 'Corps Principal du Rapport', 1), ('DOC_INTRO', 'Introduction', 1), ('DOC_CONCLU', 'Conclusion', 1), ('DOC_BIBLIO', 'Bibliographie', 1), ('DOC_RESUME', 'Résumé', 0), ('DOC_NOTE_CORRECTION_CONF', 'Note sur les Corrections (Conformité)', 0), ('DOC_AUTRE', 'Autre', 0);

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `libelle_type_utilisateur`) VALUES
                                                                                       ('TYPE_ADMIN', 'Administrateur'), ('TYPE_ETUD', 'Étudiant'), ('TYPE_PERS_ADMIN', 'Personnel Administratif'), ('TYPE_ENS', 'Enseignant');

INSERT INTO `utilisateur` (`numero_utilisateur`, `login_utilisateur`, `email_principal`, `mot_de_passe`, `statut_compte`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    ('ADMIN_SYS_001', 'admin', 'admin@gestionsoutenance.dev', '$argon2id$v=19$m=65536,t=4,p=1$ekdCT0pYalFmLlVzZVJzaw$LhgGEYf3Yq2zY4l7M7g3pHoqRkb5LGDEs/g1A3NldHw', 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN');


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;