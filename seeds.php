<?php

/**
 * Script de Seeding Complet pour la base de données GestionMySoutenance.
 *
 * Ce script initialise :
 * 1. Les données de référence (types, groupes, niveaux d'accès).
 * 2. Une année académique active (essentiel pour la génération d'ID).
 * 3. Les permissions (traitements) de l'application.
 * 4. L'attribution des permissions aux groupes (rôles).
 * 5. Les utilisateurs par défaut pour chaque rôle.
 *
 * USAGE : Exécuter depuis la racine du projet via la commande :
 * > php seeds.php
 */

declare(strict_types=1);

// --- Bootstrap de l'application ---
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/vendor/autoload.php';

if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

use App\Config\Container;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Exception\DoublonException;

/**
 * Crée les données de référence (Types, Groupes, Niveaux d'accès).
 */
function seedReferenceData(ServicePermissions $permissionService): void
{
    echo "--- Début du seeding des données de référence ---\n";

    // 1. Types d'utilisateurs
    $types = [
        ['id' => 'TYPE_ADMIN', 'libelle' => 'Administrateur Système'],
        ['id' => 'TYPE_PERS_ADMIN', 'libelle' => 'Personnel Administratif'],
        ['id' => 'TYPE_ENS', 'libelle' => 'Enseignant'],
        ['id' => 'TYPE_ETUD', 'libelle' => 'Étudiant'],
    ];
    echo "\n[INFO] Seeding des Types d'Utilisateurs...\n";
    foreach ($types as $type) {
        try {
            $permissionService->creerTypeUtilisateur($type['id'], $type['libelle']);
            echo "  [SUCCÈS] Type '{$type['libelle']}' créé.\n";
        } catch (DoublonException $e) {
            echo "  [AVERTISSEMENT] Type '{$type['libelle']}' existe déjà.\n";
        }
    }

    // 2. Groupes d'utilisateurs (Rôles)
    $groupes = [
        ['id' => 'GRP_ADMIN_SYS', 'libelle' => 'Administrateur Système'],
        ['id' => 'GRP_RS', 'libelle' => 'Responsable Scolarité'],
        ['id' => 'GRP_AGENT_CONFORMITE', 'libelle' => 'Agent de Conformité'],
        ['id' => 'GRP_COMMISSION', 'libelle' => 'Membre de Commission'],
        ['id' => 'GRP_ETUDIANT', 'libelle' => 'Étudiant'],
        // <-- AJOUT DES GROUPES MANQUANTS -->
        ['id' => 'GRP_PERS_ADMIN', 'libelle' => 'Personnel Administratif (Rôle de base)'],
        ['id' => 'GRP_ENSEIGNANT', 'libelle' => 'Enseignant (Rôle de base)'],
    ];
    echo "\n[INFO] Seeding des Groupes d'Utilisateurs (Rôles)...\n";
    foreach ($groupes as $groupe) {
        try {
            $permissionService->creerGroupeUtilisateur($groupe['id'], $groupe['libelle']);
            echo "  [SUCCÈS] Groupe '{$groupe['libelle']}' créé.\n";
        } catch (DoublonException $e) {
            echo "  [AVERTISSEMENT] Groupe '{$groupe['libelle']}' existe déjà.\n";
        }
    }

    // 3. Niveaux d'accès aux données
    $niveaux = [
        ['id' => 'ACCES_TOTAL', 'libelle' => 'Accès Total (Admin)'],
        ['id' => 'ACCES_DEPARTEMENT', 'libelle' => 'Accès Niveau Département'],
        ['id' => 'ACCES_PERSONNEL', 'libelle' => 'Accès aux Données Personnelles Uniquement'],
    ];
    echo "\n[INFO] Seeding des Niveaux d'Accès...\n";
    foreach ($niveaux as $niveau) {
        try {
            $permissionService->creerNiveauAcces($niveau['id'], $niveau['libelle']);
            echo "  [SUCCÈS] Niveau d'accès '{$niveau['libelle']}' créé.\n";
        } catch (DoublonException $e) {
            echo "  [AVERTISSEMENT] Niveau d'accès '{$niveau['libelle']}' existe déjà.\n";
        }
    }
    echo "--- Fin du seeding des données de référence ---\n";
}

/**
 * Crée une année académique active, prérequis pour la création d'utilisateurs.
 */
function seedAcademicYear(ServiceConfigurationSysteme $configService): void
{
    echo "\n--- Début du seeding de l'Année Académique ---\n";
    $year = date('Y');
    $nextYear = $year + 1;
    $academicYearId = "ANNEE-{$year}-{$nextYear}";
    $academicYearLabel = "{$year}-{$nextYear}";

    try {
        $configService->creerAnneeAcademique(
            $academicYearId,
            $academicYearLabel,
            "{$year}-09-01",
            "{$nextYear}-08-31",
            true // Marquer comme active
        );
        echo "  [SUCCÈS] Année académique '{$academicYearLabel}' créée et activée.\n";
    } catch (DoublonException $e) {
        echo "  [AVERTISSEMENT] L'année académique '{$academicYearLabel}' existe déjà.\n";
        // S'assurer qu'elle est bien active si elle existe déjà
        $configService->definirAnneeAcademiqueActive($academicYearId);
        echo "  [INFO] Année académique '{$academicYearLabel}' définie comme active.\n";
    }
    echo "--- Fin du seeding de l'Année Académique ---\n";
}

/**
 * Crée les permissions (traitements) de l'application.
 */
function seedTraitements(ServicePermissions $permissionService): void
{
    echo "\n--- Début du seeding des Traitements (Permissions) ---\n";
    $traitements = [
        ['id' => 'TRAIT_ADMIN_DASHBOARD_ACCEDER', 'libelle' => 'Accéder au Dashboard Admin'],
        ['id' => 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'libelle' => 'Lister les utilisateurs'],
        ['id' => 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER', 'libelle' => 'Créer un utilisateur'],
        ['id' => 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER', 'libelle' => 'Accéder au Dashboard Étudiant'],
        ['id' => 'TRAIT_ETUDIANT_RAPPORT_SUIVRE', 'libelle' => 'Suivre son rapport'],
        ['id' => 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', 'libelle' => 'Soumettre son rapport'],
        ['id' => 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', 'libelle' => 'Accéder au Dashboard Personnel Admin'],
        ['id' => 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER', 'libelle' => 'Lister les rapports à vérifier'],
        ['id' => 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER', 'libelle' => 'Vérifier la conformité d\'un rapport'],
        ['id' => 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', 'libelle' => 'Accéder à la gestion de la scolarité'],
        ['id' => 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER', 'libelle' => 'Gérer les pénalités'],
        ['id' => 'TRAIT_COMMISSION_DASHBOARD_ACCEDER', 'libelle' => 'Accéder au Dashboard Commission'],
        ['id' => 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER', 'libelle' => 'Voter pour un rapport'],
    ];

    foreach ($traitements as $traitement) {
        try {
            $permissionService->creerTraitement($traitement['id'], $traitement['libelle']);
            echo "  [SUCCÈS] Traitement '{$traitement['libelle']}' créé.\n";
        } catch (DoublonException $e) {
            echo "  [AVERTISSEMENT] Traitement '{$traitement['libelle']}' existe déjà.\n";
        }
    }
    echo "--- Fin du seeding des Traitements ---\n";
}

/**
 * Attribue les permissions aux groupes.
 */
function seedPermissions(ServicePermissions $permissionService): void
{
    echo "\n--- Début du seeding des Permissions (Rattachements) ---\n";
    $rattachements = [
        'GRP_ADMIN_SYS' => ['TRAIT_ADMIN_DASHBOARD_ACCEDER', 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER'],
        'GRP_ETUDIANT' => ['TRAIT_ETUDIANT_DASHBOARD_ACCEDER', 'TRAIT_ETUDIANT_RAPPORT_SUIVRE', 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE'],
        'GRP_AGENT_CONFORMITE' => ['TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER', 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER'],
        'GRP_RS' => ['TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER'],
        'GRP_COMMISSION' => ['TRAIT_COMMISSION_DASHBOARD_ACCEDER', 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER'],
    ];

    foreach ($rattachements as $groupeId => $permissions) {
        echo "[INFO] Attribution des permissions pour le groupe '{$groupeId}'...\n";
        foreach ($permissions as $permId) {
            try {
                $permissionService->attribuerPermissionGroupe($groupeId, $permId);
                echo "  [SUCCÈS] Permission '{$permId}' attribuée.\n";
            } catch (DoublonException $e) {
                echo "  [AVERTISSEMENT] Permission '{$permId}' déjà attribuée.\n";
            } catch (Exception $e) {
                echo "  [ERREUR] Échec de l'attribution de '{$permId}': " . $e->getMessage() . "\n";
            }
        }
    }
    echo "--- Fin du seeding des Permissions ---\n";
}

/**
 * Crée les utilisateurs par défaut.
 */
function seedUsers(ServiceAuthentication $authService): void
{
    echo "\n--- Début du seeding des utilisateurs par défaut ---\n";

    $defaultPassword = 'Password123!';
    echo "[INFO] Le mot de passe par défaut pour tous les utilisateurs est : {$defaultPassword}\n\n";

    $usersToCreate = [
        [
            'type_code' => 'TYPE_ADMIN',
            'user_data' => ['login_utilisateur' => 'admin_sys', 'email_principal' => 'admin.sys@gestionsoutenance.dev', 'mot_de_passe' => $defaultPassword, 'id_niveau_acces_donne' => 'ACCES_TOTAL', 'id_groupe_utilisateur' => 'GRP_ADMIN_SYS'],
            'profile_data' => ['nom' => 'Système', 'prenom' => 'Admin']
        ],
        [
            'type_code' => 'TYPE_PERS_ADMIN',
            'user_data' => ['login_utilisateur' => 'resp_sco', 'email_principal' => 'resp.sco@gestionsoutenance.dev', 'mot_de_passe' => $defaultPassword, 'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT', 'id_groupe_utilisateur' => 'GRP_RS'],
            'profile_data' => ['nom' => 'Scolarité', 'prenom' => 'Responsable', 'telephone_professionnel' => '0123456789']
        ],
        [
            'type_code' => 'TYPE_PERS_ADMIN',
            'user_data' => ['login_utilisateur' => 'agent_conf', 'email_principal' => 'agent.conf@gestionsoutenance.dev', 'mot_de_passe' => $defaultPassword, 'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT', 'id_groupe_utilisateur' => 'GRP_AGENT_CONFORMITE'],
            'profile_data' => ['nom' => 'Conformité', 'prenom' => 'Agent', 'telephone_professionnel' => '0123456788']
        ],
        [
            'type_code' => 'TYPE_ENS',
            'user_data' => ['login_utilisateur' => 'prof_dupont', 'email_principal' => 'prof.dupont@gestionsoutenance.dev', 'mot_de_passe' => $defaultPassword, 'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT', 'id_groupe_utilisateur' => 'GRP_COMMISSION'],
            'profile_data' => ['nom' => 'Dupont', 'prenom' => 'Jean', 'telephone_professionnel' => '0611223344']
        ],
        [
            'type_code' => 'TYPE_ETUD',
            'user_data' => ['login_utilisateur' => 'etu_martin', 'email_principal' => 'etu.martin@gestionsoutenance.dev', 'mot_de_passe' => $defaultPassword, 'id_niveau_acces_donne' => 'ACCES_PERSONNEL', 'id_groupe_utilisateur' => 'GRP_ETUDIANT'],
            'profile_data' => ['nom' => 'Martin', 'prenom' => 'Sophie', 'date_naissance' => '2002-05-15', 'telephone' => '0788990011']
        ]
    ];

    foreach ($usersToCreate as $user) {
        echo "Tentative de création de l'utilisateur : {$user['user_data']['login_utilisateur']}...\n";
        try {
            $userId = $authService->creerCompteUtilisateurComplet(
                $user['user_data'],
                $user['profile_data'],
                $user['type_code'],
                false // false pour ne pas envoyer d'email de validation
            );
            echo "  [SUCCÈS] Utilisateur '{$user['user_data']['login_utilisateur']}' créé avec l'ID : {$userId}\n";

            $authService->changerStatutDuCompte($userId, 'actif');
            echo "  [INFO] Compte '{$userId}' activé manuellement.\n";

        } catch (DoublonException $e) {
            echo "  [AVERTISSEMENT] L'utilisateur '{$user['user_data']['login_utilisateur']}' existe déjà.\n";
        } catch (Exception $e) {
            echo "  [ERREUR] Échec de la création de '{$user['user_data']['login_utilisateur']}': " . $e->getMessage() . "\n";
        }
    }
    echo "--- Fin du seeding des utilisateurs ---\n";
}

// --- Exécution du Script ---
try {
    $container = new Container();
    $permissionService = $container->get(ServicePermissions::class);
    $authService = $container->get(ServiceAuthentication::class);
    $configService = $container->get(ServiceConfigurationSysteme::class);

    // <-- ORDRE D'EXÉCUTION CORRIGÉ -->
    seedReferenceData($permissionService);
    seedAcademicYear($configService); // Doit être exécuté avant la création des utilisateurs
    seedTraitements($permissionService);
    seedPermissions($permissionService);
    seedUsers($authService);

    echo "\n[FIN] Le script de seeding s'est terminé avec succès.\n";

} catch (Exception $e) {
    echo "\n[ERREUR CRITIQUE] Le script de seeding a échoué : " . $e->getMessage() . "\n";
    exit(1);
}