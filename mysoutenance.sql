SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Suppression des tables qui seront recréées ou ne sont plus utilisées,
-- en s'assurant de l'ordre pour les dépendances de FK
DROP TABLE IF EXISTS `acquerir`;
DROP TABLE IF EXISTS `action`;
DROP TABLE IF EXISTS `affecter`;
DROP TABLE IF EXISTS `annee_academique`;
DROP TABLE IF EXISTS `approuver`;
DROP TABLE IF EXISTS `attribuer`;
DROP TABLE IF EXISTS `compte_rendu`;
DROP TABLE IF EXISTS `conversation`;
DROP TABLE IF EXISTS `decision_passage_ref`;
DROP TABLE IF EXISTS `decision_validation_pv_ref`;
DROP TABLE IF EXISTS `decision_vote_ref`;

-- Suppression de document_soumis et de ses FK
DROP TABLE IF EXISTS `document_soumis`;

-- Suppression des tables obsolètes
DROP TABLE IF EXISTS `donner`;
DROP TABLE IF EXISTS `ecue`;
DROP TABLE IF EXISTS `enregistrer`;
DROP TABLE IF EXISTS `enseignant`;
DROP TABLE IF EXISTS `entreprise`;
DROP TABLE IF EXISTS `etudiant`;

-- Suppression d'evaluer avant recréation si modification radicale,
-- sinon on peut faire un ALTER TABLE. Ici, DROP+CREATE est plus simple vu la PK.
DROP TABLE IF EXISTS `evaluer`;

DROP TABLE IF EXISTS `faire_stage`;
DROP TABLE IF EXISTS `fonction`;
DROP TABLE IF EXISTS `grade`;
DROP TABLE IF EXISTS `groupe_utilisateur`;
DROP TABLE IF EXISTS `historique_mot_de_passe`;
DROP TABLE IF EXISTS `inscrire`;
DROP TABLE IF EXISTS `lecture_message`;
DROP TABLE IF EXISTS `message`; -- Table à supprimer
DROP TABLE IF EXISTS `message_chat`;
DROP TABLE IF EXISTS `niveau_acces_donne`;
DROP TABLE IF EXISTS `niveau_approbation`; -- Table à supprimer
DROP TABLE IF EXISTS `niveau_etude`;
DROP TABLE IF EXISTS `notification`;
DROP TABLE IF EXISTS `occuper`;
DROP TABLE IF EXISTS `participant_conversation`;
DROP TABLE IF EXISTS `penalite`; -- Nouvelle table
DROP TABLE IF EXISTS `personnel_administratif`;
DROP TABLE IF EXISTS `pister`;
DROP TABLE IF EXISTS `pv_session_rapport`;
DROP TABLE IF EXISTS `rapport_etudiant`;
DROP TABLE IF EXISTS `rattacher`;
DROP TABLE IF EXISTS `recevoir`;
DROP TABLE IF EXISTS `reclamation`;
DROP TABLE IF EXISTS `rendre`;
DROP TABLE IF EXISTS `section_rapport`; -- Nouvelle table
DROP TABLE IF EXISTS `sequences`; -- Nouvelle table
DROP TABLE IF EXISTS `session_rapport`; -- Nouvelle table
DROP TABLE IF EXISTS `session_validation`; -- Nouvelle table
DROP TABLE IF EXISTS `sessions`; -- Nouvelle table
DROP TABLE IF EXISTS `specialite`;
DROP TABLE IF EXISTS `statut_conformite_ref`;
DROP TABLE IF EXISTS `statut_jury`;
DROP TABLE IF EXISTS `statut_paiement_ref`;
DROP TABLE IF EXISTS `statut_penalite_ref`; -- Nouvelle table
DROP TABLE IF EXISTS `statut_pv_ref`;
DROP TABLE IF EXISTS `statut_rapport_ref`;
DROP TABLE IF EXISTS `statut_reclamation_ref`;
DROP TABLE IF EXISTS `traitement`;
DROP TABLE IF EXISTS `type_document_ref`;
DROP TABLE IF EXISTS `type_utilisateur`;
DROP TABLE IF EXISTS `ue`;
DROP TABLE IF EXISTS `utilisateur`;
DROP TABLE IF EXISTS `validation_pv`;
DROP TABLE IF EXISTS `valider`; -- Table à supprimer
DROP TABLE IF EXISTS `vote_commission`;


--
-- Structure de la table `acquerir`
--
CREATE TABLE `acquerir` (
                            `id_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `date_acquisition` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `action`
--
CREATE TABLE `action` (
                          `id_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `libelle_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `categorie_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Structure de la table `approuver`
--
CREATE TABLE `approuver` (
                             `numero_personnel_administratif` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `commentaire_conformite` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                             `date_verification_conformite` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `attribuer`
--
CREATE TABLE `attribuer` (
                             `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `conversation`
--
CREATE TABLE `conversation` (
                                `id_conversation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `nom_conversation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                `date_creation_conv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `type_conversation` enum('Direct','Groupe') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Direct'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `decision_passage_ref`
--
CREATE TABLE `decision_passage_ref` (
                                        `id_decision_passage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                        `libelle_decision_passage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `decision_validation_pv_ref`
--
CREATE TABLE `decision_validation_pv_ref` (
                                              `id_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                              `libelle_decision_validation_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `decision_vote_ref`
--
CREATE TABLE `decision_vote_ref` (
                                     `id_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la nouvelle table `document_genere` (remplace document_soumis)
--
CREATE TABLE `document_genere` (
                                   `id_document_genere` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `libelle_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `chemin_fichier` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `nom_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                   `type_mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                   `taille_fichier` int DEFAULT NULL,
                                   `date_generation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `version` int NOT NULL DEFAULT '1',
                                   `id_type_document_ref` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_utilisateur_concerne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_entite_source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL, -- Ex: id_rapport_etudiant, id_compte_rendu
                                   `type_entite_source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL, -- Ex: 'Rapport', 'PV'
                                   PRIMARY KEY (`id_document_genere`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE= InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Structure de la table `evaluer` (Modifiée)
--
CREATE TABLE `evaluer` (
                           `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `id_ecue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `date_evaluation` datetime NOT NULL,
                           `note` decimal(5,2) DEFAULT NULL,
                           PRIMARY KEY (`numero_carte_etudiant`, `id_ecue`) -- Nouvelle clé primaire
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `fonction`
--
CREATE TABLE `fonction` (
                            `id_fonction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `libelle_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `grade`
--
CREATE TABLE `grade` (
                         `id_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `libelle_grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                         `abreviation_grade` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `groupe_utilisateur`
--
CREATE TABLE `groupe_utilisateur` (
                                      `id_groupe_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_groupe_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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


CREATE TABLE jury (
                      id_jury INT AUTO_INCREMENT PRIMARY KEY,
                      id_session_rapport INT NOT NULL, -- Lié à la session de soutenance
                      date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                      FOREIGN KEY (id_session_rapport) REFERENCES session_rapport(id_session_rapport)
);

--
-- Structure de la table `lecture_message`
--
CREATE TABLE `lecture_message` (
                                   `id_message_chat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `date_lecture` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `niveau_acces_donne`
--
CREATE TABLE `niveau_acces_donne` (
                                      `id_niveau_acces_donne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_niveau_acces_donne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `niveau_etude`
--
CREATE TABLE `niveau_etude` (
                                `id_niveau_etude` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `libelle_niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `notification`
--
CREATE TABLE `notification` (
                                `id_notification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                `libelle_notification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `occuper`
--
CREATE TABLE `occuper` (
                           `id_fonction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                           `date_debut_occupation` date NOT NULL,
                           `date_fin_occupation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `participant_conversation`
--
CREATE TABLE `participant_conversation` (
                                            `id_conversation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                            `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_penalite_ref` (Nouvelle table)
--
CREATE TABLE `statut_penalite_ref` (
                                       `id_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       `libelle_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       PRIMARY KEY (`id_statut_penalite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `penalite` (Nouvelle table)
--
CREATE TABLE `penalite` (
                            `id_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `numero_carte_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `id_statut_penalite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `montant_penalite` decimal(10,2) DEFAULT NULL,
                            `date_application` date NOT NULL,
                            `date_regularisation` date DEFAULT NULL,
                            `motif` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                            PRIMARY KEY (`id_penalite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Structure de la table `pister`
--
CREATE TABLE `pister` (
                          `numero_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `id_traitement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `date_pister` datetime NOT NULL,
                          `acceder` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `pv_session_rapport`
--
CREATE TABLE `pv_session_rapport` (
                                      `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `rattacher`
--
CREATE TABLE `rattacher` (
                             `id_groupe_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `id_traitement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `rendre`
--
CREATE TABLE `rendre` (
                          `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `id_compte_rendu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                          `date_action_sur_pv` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `section_rapport` (Nouvelle table)
--
CREATE TABLE `section_rapport` (
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `nom_section` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, -- Ex: 'Introduction', 'Resume', 'Conclusion'
                                   `contenu` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                                   `ordre_affichage` INT DEFAULT NULL,
                                   PRIMARY KEY (`id_rapport_etudiant`, `nom_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `sequences` (Nouvelle table pour génération d'ID)
--
CREATE TABLE `sequences` (
                             `nom_sequence` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `annee` YEAR NOT NULL,
                             `valeur_actuelle` INT UNSIGNED NOT NULL DEFAULT '0',
                             PRIMARY KEY (`nom_sequence`, `annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `session_validation` (Nouvelle table)
--
CREATE TABLE `session_validation` (
                                      `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_session` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `date_debut_session` datetime NOT NULL,
                                      `date_fin_prevue` datetime DEFAULT NULL,
                                      `statut_session` enum('Planifiee','En cours','Cloturee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Planifiee',
                                      `numero_president_commission` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL, -- FK vers enseignant
                                      PRIMARY KEY (`id_session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `session_rapport` (Nouvelle table de liaison)
--
CREATE TABLE `session_rapport` (
                                   `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   PRIMARY KEY (`id_session`, `id_rapport_etudiant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `sessions` (Nouvelle table pour gestion des sessions PHP en DB)
--
CREATE TABLE `sessions` (
                            `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `session_data` LONGBLOB NOT NULL,
                            `session_last_activity` INT UNSIGNED NOT NULL,
                            `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL, -- FK vers utilisateur.numero_utilisateur
                            PRIMARY KEY (`session_id`),
                            INDEX `idx_session_last_activity` (`session_last_activity`),
                            INDEX `idx_session_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `specialite`
--
CREATE TABLE `specialite` (
                              `id_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `numero_enseignant_specialite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_conformite_ref`
--
CREATE TABLE `statut_conformite_ref` (
                                         `id_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                         `libelle_statut_conformite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_jury`
--
CREATE TABLE `statut_jury` (
                               `id_statut_jury` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               `libelle_statut_jury` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_paiement_ref`
--
CREATE TABLE `statut_paiement_ref` (
                                       `id_statut_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                       `libelle_statut_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_pv_ref`
--
CREATE TABLE `statut_pv_ref` (
                                 `id_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                 `libelle_statut_pv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_rapport_ref`
--
CREATE TABLE `statut_rapport_ref` (
                                      `id_statut_rapport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `libelle_statut_rapport` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                      `etape_workflow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `statut_reclamation_ref`
--
CREATE TABLE `statut_reclamation_ref` (
                                          `id_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                          `libelle_statut_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `traitement`
--
CREATE TABLE `traitement` (
                              `id_traitement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                              `libelle_traitement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `type_document_ref`
--
CREATE TABLE `type_document_ref` (
                                     `id_type_document` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `libelle_type_document` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                     `requis_ou_non` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `type_utilisateur`
--
CREATE TABLE `type_utilisateur` (
                                    `id_type_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                    `libelle_type_utilisateur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `ue`
--
CREATE TABLE `ue` (
                      `id_ue` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `libelle_ue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                      `credits_ue` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
                               `id_type_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                               PRIMARY KEY (`numero_utilisateur`),
                               UNIQUE KEY `uq_utilisateur_login` (`login_utilisateur`),
                               UNIQUE KEY `uq_email_principal` (`email_principal`),
                               KEY `idx_token_reset_mdp` (`token_reset_mdp`),
                               KEY `idx_token_validation_email` (`token_validation_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Structure de la table `vote_commission` (Colonne id_session ajoutée)
--
CREATE TABLE `vote_commission` (
                                   `id_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_rapport_etudiant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `numero_enseignant` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `id_decision_vote` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                   `commentaire_vote` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                                   `date_vote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `tour_vote` int NOT NULL DEFAULT '1',
                                   `id_session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL, -- Nouvelle colonne
                                   PRIMARY KEY (`id_vote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Index pour les tables
--
ALTER TABLE `acquerir`
    ADD PRIMARY KEY (`id_grade`,`numero_enseignant`),
  ADD KEY `idx_acquerir_enseignant` (`numero_enseignant`);

ALTER TABLE `action`
    ADD PRIMARY KEY (`id_action`);

ALTER TABLE `affecter`
    ADD PRIMARY KEY (`numero_enseignant`,`id_rapport_etudiant`,`id_statut_jury`),
  ADD KEY `idx_affecter_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `idx_affecter_statut_jury` (`id_statut_jury`);

ALTER TABLE `annee_academique`
    ADD PRIMARY KEY (`id_annee_academique`);

ALTER TABLE `approuver`
    ADD PRIMARY KEY (`numero_personnel_administratif`,`id_rapport_etudiant`),
  ADD KEY `idx_approuver_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `fk_approuver_statut_conformite` (`id_statut_conformite`);

ALTER TABLE `attribuer`
    ADD PRIMARY KEY (`numero_enseignant`,`id_specialite`),
  ADD KEY `idx_attribuer_specialite` (`id_specialite`);

ALTER TABLE `compte_rendu`
    ADD PRIMARY KEY (`id_compte_rendu`),
  ADD KEY `idx_compte_rendu_rapport_etudiant` (`id_rapport_etudiant`),
  ADD KEY `idx_compte_rendu_redacteur` (`id_redacteur`),
  ADD KEY `fk_compte_rendu_statut_pv` (`id_statut_pv`);

ALTER TABLE `conversation`
    ADD PRIMARY KEY (`id_conversation`);

ALTER TABLE `decision_passage_ref`
    ADD PRIMARY KEY (`id_decision_passage`);

ALTER TABLE `decision_validation_pv_ref`
    ADD PRIMARY KEY (`id_decision_validation_pv`);

ALTER TABLE `decision_vote_ref`
    ADD PRIMARY KEY (`id_decision_vote`);

-- Index pour la nouvelle table `document_genere`
ALTER TABLE `document_genere`
    ADD PRIMARY KEY (`id_document_genere`),
  ADD KEY `idx_doc_genere_rapport` (`id_entite_source`), -- Peut être adapté si entité source n'est pas toujours un rapport
  ADD KEY `idx_doc_genere_user_concerne` (`numero_utilisateur_concerne`),
  ADD KEY `fk_doc_genere_type_doc_ref` (`id_type_document_ref`);

ALTER TABLE `ecue`
    ADD PRIMARY KEY (`id_ecue`),
  ADD KEY `idx_ecue_ue` (`id_ue`);

ALTER TABLE `enregistrer`
    ADD PRIMARY KEY (`numero_utilisateur`,`id_action`,`date_action`),
  ADD KEY `idx_enregistrer_action` (`id_action`);

ALTER TABLE `enseignant`
    ADD PRIMARY KEY (`numero_enseignant`),
  ADD UNIQUE KEY `uq_enseignant_numero_utilisateur` (`numero_utilisateur`);

ALTER TABLE `entreprise`
    ADD PRIMARY KEY (`id_entreprise`);

ALTER TABLE `etudiant`
    ADD PRIMARY KEY (`numero_carte_etudiant`),
  ADD UNIQUE KEY `uq_etudiant_numero_utilisateur` (`numero_utilisateur`);

ALTER TABLE `faire_stage`
    ADD PRIMARY KEY (`id_entreprise`,`numero_carte_etudiant`),
  ADD KEY `idx_faire_stage_etudiant` (`numero_carte_etudiant`);

ALTER TABLE `fonction`
    ADD PRIMARY KEY (`id_fonction`);

ALTER TABLE `grade`
    ADD PRIMARY KEY (`id_grade`);

ALTER TABLE `groupe_utilisateur`
    ADD PRIMARY KEY (`id_groupe_utilisateur`);

ALTER TABLE `historique_mot_de_passe`
    ADD PRIMARY KEY (`id_historique_mdp`),
  ADD KEY `idx_hist_user_mdp` (`numero_utilisateur`);

ALTER TABLE `inscrire`
    ADD PRIMARY KEY (`numero_carte_etudiant`,`id_niveau_etude`,`id_annee_academique`),
  ADD UNIQUE KEY `uq_inscrire_numero_recu` (`numero_recu_paiement`),
  ADD KEY `idx_inscrire_niveau_etude` (`id_niveau_etude`),
  ADD KEY `idx_inscrire_annee_academique` (`id_annee_academique`),
  ADD KEY `fk_inscrire_statut_paiement` (`id_statut_paiement`),
  ADD KEY `fk_inscrire_decision_passage` (`id_decision_passage`);

ALTER TABLE `lecture_message`
    ADD PRIMARY KEY (`id_message_chat`,`numero_utilisateur`),
  ADD KEY `idx_lm_user` (`numero_utilisateur`);

ALTER TABLE `message_chat`
    ADD PRIMARY KEY (`id_message_chat`),
  ADD KEY `idx_mc_conv` (`id_conversation`),
  ADD KEY `idx_mc_user` (`numero_utilisateur_expediteur`);

ALTER TABLE `niveau_acces_donne`
    ADD PRIMARY KEY (`id_niveau_acces_donne`);

ALTER TABLE `niveau_etude`
    ADD PRIMARY KEY (`id_niveau_etude`);

ALTER TABLE `notification`
    ADD PRIMARY KEY (`id_notification`);

ALTER TABLE `occuper`
    ADD PRIMARY KEY (`id_fonction`,`numero_enseignant`),
  ADD KEY `idx_occuper_enseignant` (`numero_enseignant`);

ALTER TABLE `participant_conversation`
    ADD PRIMARY KEY (`id_conversation`,`numero_utilisateur`),
  ADD KEY `idx_pc_user` (`numero_utilisateur`);

-- Index pour la nouvelle table `penalite`
ALTER TABLE `penalite`
    ADD PRIMARY KEY (`id_penalite`),
  ADD KEY `idx_penalite_etudiant` (`numero_carte_etudiant`),
  ADD KEY `fk_penalite_statut` (`id_statut_penalite`);

ALTER TABLE `personnel_administratif`
    ADD PRIMARY KEY (`numero_personnel_administratif`),
  ADD UNIQUE KEY `uq_personnel_numero_utilisateur` (`numero_utilisateur`);

ALTER TABLE `pister`
    ADD PRIMARY KEY (`numero_utilisateur`,`id_traitement`,`date_pister`),
  ADD KEY `idx_pister_traitement` (`id_traitement`);

ALTER TABLE `pv_session_rapport`
    ADD PRIMARY KEY (`id_compte_rendu`,`id_rapport_etudiant`),
  ADD KEY `idx_pvsr_rapport` (`id_rapport_etudiant`);

ALTER TABLE `rapport_etudiant`
    ADD PRIMARY KEY (`id_rapport_etudiant`),
  ADD KEY `idx_rapport_etudiant_etudiant` (`numero_carte_etudiant`),
  ADD KEY `fk_rapport_statut` (`id_statut_rapport`);

ALTER TABLE `rattacher`
    ADD PRIMARY KEY (`id_groupe_utilisateur`,`id_traitement`),
  ADD KEY `idx_rattacher_traitement` (`id_traitement`);

ALTER TABLE `recevoir`
    ADD PRIMARY KEY (`numero_utilisateur`,`id_notification`,`date_reception`),
  ADD KEY `idx_recevoir_notification` (`id_notification`);

ALTER TABLE `reclamation`
    ADD PRIMARY KEY (`id_reclamation`),
  ADD KEY `idx_reclam_etudiant` (`numero_carte_etudiant`),
  ADD KEY `idx_reclam_personnel` (`numero_personnel_traitant`),
  ADD KEY `fk_reclam_statut` (`id_statut_reclamation`);

ALTER TABLE `rendre`
    ADD PRIMARY KEY (`numero_enseignant`,`id_compte_rendu`);

-- Index pour la nouvelle table `section_rapport`
ALTER TABLE `section_rapport`
    ADD PRIMARY KEY (`id_rapport_etudiant`, `nom_section`);

-- Index pour la nouvelle table `sequences`
ALTER TABLE `sequences`
    ADD PRIMARY KEY (`nom_sequence`, `annee`);

-- Index pour la nouvelle table `session_rapport`
ALTER TABLE `session_rapport`
    ADD PRIMARY KEY (`id_session`, `id_rapport_etudiant`);

-- Index pour la nouvelle table `session_validation`
ALTER TABLE `session_validation`
    ADD PRIMARY KEY (`id_session`),
  ADD KEY `idx_session_president` (`numero_president_commission`);

-- Index pour la nouvelle table `sessions`
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_session_last_activity` (`session_last_activity`),
  ADD KEY `idx_session_user_id` (`user_id`);

ALTER TABLE `specialite`
    ADD PRIMARY KEY (`id_specialite`),
  ADD KEY `fk_specialite_enseignant` (`numero_enseignant_specialite`);

ALTER TABLE `statut_conformite_ref`
    ADD PRIMARY KEY (`id_statut_conformite`);

ALTER TABLE `statut_jury`
    ADD PRIMARY KEY (`id_statut_jury`);

ALTER TABLE `statut_paiement_ref`
    ADD PRIMARY KEY (`id_statut_paiement`);

-- Index pour la nouvelle table `statut_penalite_ref`
ALTER TABLE `statut_penalite_ref`
    ADD PRIMARY KEY (`id_statut_penalite`);

ALTER TABLE `statut_pv_ref`
    ADD PRIMARY KEY (`id_statut_pv`);

ALTER TABLE `statut_rapport_ref`
    ADD PRIMARY KEY (`id_statut_rapport`);

ALTER TABLE `statut_reclamation_ref`
    ADD PRIMARY KEY (`id_statut_reclamation`);

ALTER TABLE `traitement`
    ADD PRIMARY KEY (`id_traitement`);

ALTER TABLE `type_document_ref`
    ADD PRIMARY KEY (`id_type_document`);

ALTER TABLE `type_utilisateur`
    ADD PRIMARY KEY (`id_type_utilisateur`);

ALTER TABLE `ue`
    ADD PRIMARY KEY (`id_ue`);

ALTER TABLE `utilisateur`
    ADD PRIMARY KEY (`numero_utilisateur`),
  ADD UNIQUE KEY `uq_utilisateur_login` (`login_utilisateur`),
  ADD UNIQUE KEY `uq_email_principal` (`email_principal`),
  ADD KEY `idx_utilisateur_niveau_acces` (`id_niveau_acces_donne`),
  ADD KEY `idx_utilisateur_groupe` (`id_groupe_utilisateur`),
  ADD KEY `idx_utilisateur_type` (`id_type_utilisateur`),
  ADD KEY `idx_token_reset_mdp` (`token_reset_mdp`),
  ADD KEY `idx_token_validation_email` (`token_validation_email`);

ALTER TABLE `validation_pv`
    ADD PRIMARY KEY (`id_compte_rendu`,`numero_enseignant`),
  ADD KEY `idx_valpv_enseignant` (`numero_enseignant`),
  ADD KEY `fk_valpv_decision` (`id_decision_validation_pv`);

ALTER TABLE `vote_commission`
    ADD PRIMARY KEY (`id_vote`),
  ADD KEY `idx_vote_rapport` (`id_rapport_etudiant`),
  ADD KEY `idx_vote_enseignant` (`numero_enseignant`),
  ADD KEY `fk_vote_decision` (`id_decision_vote`),
  ADD KEY `fk_vote_commission_session` (`id_session`); -- Nouvelle FK


--
-- Contraintes pour les tables
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

-- Contraintes pour la nouvelle table `document_genere`
ALTER TABLE `document_genere`
    ADD CONSTRAINT `fk_doc_genere_type_doc_ref` FOREIGN KEY (`id_type_document_ref`) REFERENCES `type_document_ref` (`id_type_document`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_genere_utilisateur` FOREIGN KEY (`numero_utilisateur_concerne`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE;
  -- Vous pouvez ajouter d'autres FKs si `id_entite_source` est une FK vers une table spécifique.

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
    ADD CONSTRAINT `fk_evaluer_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evaluer_ecue` FOREIGN KEY (`id_ecue`) REFERENCES `ecue` (`id_ecue`) ON DELETE RESTRICT ON UPDATE CASCADE; -- La FK vers enseignant est supprimée

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

-- Contraintes pour la nouvelle table `penalite`
ALTER TABLE `penalite`
    ADD CONSTRAINT `fk_penalite_etudiant` FOREIGN KEY (`numero_carte_etudiant`) REFERENCES `etudiant` (`numero_carte_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penalite_statut` FOREIGN KEY (`id_statut_penalite`) REFERENCES `statut_penalite_ref` (`id_statut_penalite`) ON DELETE RESTRICT ON UPDATE CASCADE;

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

-- Contraintes pour la nouvelle table `section_rapport`
ALTER TABLE `section_rapport`
    ADD CONSTRAINT `fk_section_rapport_etudiant` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Contraintes pour la nouvelle table `session_rapport`
ALTER TABLE `session_rapport`
    ADD CONSTRAINT `fk_sr_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation` (`id_session`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sr_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Contraintes pour la nouvelle table `session_validation`
ALTER TABLE `session_validation`
    ADD CONSTRAINT `fk_session_president` FOREIGN KEY (`numero_president_commission`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Contraintes pour la nouvelle table `sessions`
ALTER TABLE `sessions`
    ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`numero_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

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
    ADD CONSTRAINT `fk_vote_decision` FOREIGN KEY (`id_decision_vote`) REFERENCES `decision_vote_ref` (`id_decision_vote`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_enseignant` FOREIGN KEY (`numero_enseignant`) REFERENCES `enseignant` (`numero_enseignant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_rapport` FOREIGN KEY (`id_rapport_etudiant`) REFERENCES `rapport_etudiant` (`id_rapport_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_commission_session` FOREIGN KEY (`id_session`) REFERENCES `session_validation` (`id_session`) ON DELETE SET NULL ON UPDATE CASCADE; -- Nouvelle FK


--
-- Insertion des données de référence (existantes et nouvelles)
--
INSERT INTO `decision_passage_ref` (`id_decision_passage`, `libelle_decision_passage`) VALUES
                                                                                           ('DP_ADMIS', 'Admis'),
                                                                                           ('DP_AJOURNE', 'Ajourné'),
                                                                                           ('DP_REDOUBLE', 'Redoublement autorisé'),
                                                                                           ('DP_EXCLU', 'Exclu');

INSERT INTO `decision_validation_pv_ref` (`id_decision_validation_pv`, `libelle_decision_validation_pv`) VALUES
                                                                                                             ('DV_PV_APPROUVE', 'Approuvé'),
                                                                                                             ('DV_PV_MODIF', 'Modif Demandée');

INSERT INTO `decision_vote_ref` (`id_decision_vote`, `libelle_decision_vote`) VALUES
                                                                                  ('DV_APPROUVE', 'Approuvé'),
                                                                                  ('DV_REFUSE', 'Refusé'),
                                                                                  ('DV_DISCUSSION', 'Discussion');

INSERT INTO `groupe_utilisateur` (`id_groupe_utilisateur`, `libelle_groupe_utilisateur`) VALUES
                                                                                             ('GRP_ADMIN_SYS', 'Adminstrateur_systeme'),
                                                                                             ('GRP_ETUDIANT', 'Etudiants'),
                                                                                             ('GRP_PERS_ADMIN', 'Personnel_Admin'),
                                                                                             ('GRP_ENSEIGNANT', 'Enseignants'),
                                                                                             ('GRP_COMMISSION', 'Commission_Membres');

INSERT INTO `niveau_acces_donne` (`id_niveau_acces_donne`, `libelle_niveau_acces_donne`) VALUES
                                                                                             ('ACCES_TOTAL', 'Total'),
                                                                                             ('ACCES_RESTREINT', 'Restreint');

INSERT INTO `statut_conformite_ref` (`id_statut_conformite`, `libelle_statut_conformite`) VALUES
                                                                                              ('CONF_OK', 'Conforme'),
                                                                                              ('CONF_NOK', 'Non Conforme');

INSERT INTO `statut_paiement_ref` (`id_statut_paiement`, `libelle_statut_paiement`) VALUES
                                                                                        ('PAIE_OK', 'Payé'),
                                                                                        ('PAIE_PARTIEL', 'Partiellement Payé'),
                                                                                        ('PAIE_NOK', 'Non Payé'),
                                                                                        ('PAIE_EXONERE', 'Exonéré');

-- Insertion pour statut_penalite_ref (nouvelle table)
INSERT INTO `statut_penalite_ref` (`id_statut_penalite`, `libelle_statut_penalite`) VALUES
                                                                                        ('PEN_DUE', 'Due'),
                                                                                        ('PEN_REGLEE', 'Réglée'),
                                                                                        ('PEN_ANNULEE', 'Annulée');

INSERT INTO `statut_pv_ref` (`id_statut_pv`, `libelle_statut_pv`) VALUES
                                                                      ('PV_BROUILLON', 'Brouillon'),
                                                                      ('PV_SOUMIS_VALID', 'Soumis Validation'),
                                                                      ('PV_VALID', 'Validé');

INSERT INTO `statut_rapport_ref` (`id_statut_rapport`, `libelle_statut_rapport`, `etape_workflow`) VALUES
                                                                                                       ('RAP_BROUILLON', 'Brouillon', 1),
                                                                                                       ('RAP_SOUMIS', 'Soumis', 2),
                                                                                                       ('RAP_NON_CONF', 'Non Conforme', 3),
                                                                                                       ('RAP_CONF', 'Conforme', 4),
                                                                                                       ('RAP_EN_COMM', 'En Commission', 5),
                                                                                                       ('RAP_CORRECT', 'Corrections Demandées', 6),
                                                                                                       ('RAP_VALID', 'Validé', 7),
                                                                                                       ('RAP_REFUSE', 'Refusé', 8);

INSERT INTO `statut_reclamation_ref` (`id_statut_reclamation`, `libelle_statut_reclamation`) VALUES
                                                                                                 ('RECLAM_RECUE', 'Reçue'),
                                                                                                 ('RECLAM_EN_COURS', 'En Cours'),
                                                                                                 ('RECLAM_REPONDUE', 'Répondue'),
                                                                                                 ('RECLAM_CLOTUREE', 'Clôturée');

INSERT INTO `type_document_ref` (`id_type_document`, `libelle_type_document`, `requis_ou_non`) VALUES
                                                                                                   ('DOC_RAP_MAIN', 'Rapport Principal', 1),
                                                                                                   ('DOC_ATTEST', 'Attestation', 1),
                                                                                                   ('DOC_RESUME', 'Résumé', 0),
                                                                                                   ('DOC_AUTRE', 'Autre', 0);

INSERT INTO `type_utilisateur` (`id_type_utilisateur`, `libelle_type_utilisateur`) VALUES
                                                                                       ('TYPE_ADMIN', 'Administrateur'),
                                                                                       ('TYPE_ETUD', 'Etudiant'),
                                                                                       ('TYPE_PERS_ADMIN', 'Personnel Administratif'),
                                                                                       ('TYPE_ENS', 'Enseignant');

INSERT INTO `utilisateur` (`numero_utilisateur`, `login_utilisateur`, `email_principal`, `mot_de_passe`, `date_creation`, `statut_compte`, `id_niveau_acces_donne`, `id_groupe_utilisateur`, `id_type_utilisateur`) VALUES
    ('ADMIN001', 'Admin', 'admin@example.com', 'hashed_password_for_admin111', '2025-05-14 22:53:29', 'actif', 'ACCES_TOTAL', 'GRP_ADMIN_SYS', 'TYPE_ADMIN');

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;