<?php
/**
 * Script d'initialisation (seeding) complet pour la base de données GestionMySoutenance.
 *
 * Ce script est idempotent : il peut être exécuté plusieurs fois sans causer d'erreurs.
 * 1. Nettoie les tables critiques (utilisateurs, profils, permissions, etc.).
 * 2. Peuple les tables de référence (statuts, types, groupes).
 * 3. Crée la structure des permissions et des menus.
 * 4. Crée un jeu d'utilisateurs complets et DÉJÀ VALIDES pour chaque rôle majeur.
 * 5. Associe les permissions aux rôles.
 * 6. Initialise les configurations de base (année académique, séquences d'ID).
 *
 * USAGE : docker-compose -f docker-compose.dev.yml exec app php seeds.php
 */

declare(strict_types=1);

// Bootstrap de l'application (chargement de l'autoloader et des variables .env)
require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

use App\Config\Database;

class Seeder
{
    private ?PDO $pdo = null;

    public function __construct()
    {
        try {
            $this->pdo = Database::getInstance()->getConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("ERREUR CRITIQUE : Impossible de se connecter à la base de données. " . $e->getMessage() . "\n");
        }
    }

    /**
     * Point d'entrée principal pour exécuter toutes les étapes du seeding.
     */
    public function run(): void
    {
        echo "==================================================\n";
        echo "== Lancement du Seeding de GestionMySoutenance ==\n";
        echo "==================================================\n";

        $this->pdo->beginTransaction();
        try {
            $this->cleanup();
            $this->seedReferentiels();
            $this->seedPermissionsEtNavigation();
            $this->seedUsers();
            $this->seedMatricePermissions();
            $this->seedAnneeAcademique();

            $this->pdo->commit();
            echo "\n✅ Seeding terminé avec succès !\n";
        } catch (Exception $e) {
            $this->pdo->rollBack();
            die("\n❌ ERREUR LORS DU SEEDING : " . $e->getMessage() . "\n");
        }
    }

    /**
     * Vide les tables pour garantir un état propre avant le seeding.
     */
    private function cleanup(): void
    {
        echo "\n1. Nettoyage des tables...\n";
        $tablesToTruncate = [
            'rattacher', 'delegation', 'personnel_administratif', 'enseignant', 'etudiant',
            'utilisateur', 'sequences', 'annee_academique', 'traitement',
            'groupe_utilisateur', 'type_utilisateur', 'niveau_acces_donne', 'statut_rapport_ref'
        ];

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
        foreach ($tablesToTruncate as $table) {
            $this->pdo->exec("TRUNCATE TABLE `{$table}`;");
            echo "  - Table `{$table}` vidée.\n";
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
        echo "  ✔️ Nettoyage terminé.\n";
    }

    /**
     * Peuple toutes les tables de référence.
     */
    private function seedReferentiels(): void
    {
        echo "\n2. Seeding des tables de référence...\n";
        $this->executeInsert('groupe_utilisateur', [
            ['id_groupe_utilisateur' => 'GRP_ADMIN_SYS', 'libelle_groupe_utilisateur' => 'Administrateur Système'],
            ['id_groupe_utilisateur' => 'GRP_AGENT_CONFORMITE', 'libelle_groupe_utilisateur' => 'Agent de Conformité'],
            ['id_groupe_utilisateur' => 'GRP_COMMISSION', 'libelle_groupe_utilisateur' => 'Membre de Commission'],
            ['id_groupe_utilisateur' => 'GRP_ENSEIGNANT', 'libelle_groupe_utilisateur' => 'Enseignant'],
            ['id_groupe_utilisateur' => 'GRP_ETUDIANT', 'libelle_groupe_utilisateur' => 'Étudiant'],
            ['id_groupe_utilisateur' => 'GRP_RS', 'libelle_groupe_utilisateur' => 'Responsable Scolarité'],
            ['id_groupe_utilisateur' => 'GRP_PERS_ADMIN', 'libelle_groupe_utilisateur' => 'Personnel Administratif (Base)']
        ]);

        $this->executeInsert('type_utilisateur', [
            ['id_type_utilisateur' => 'TYPE_ADMIN', 'libelle_type_utilisateur' => 'Administrateur Système'],
            ['id_type_utilisateur' => 'TYPE_ENS', 'libelle_type_utilisateur' => 'Enseignant'],
            ['id_type_utilisateur' => 'TYPE_ETUD', 'libelle_type_utilisateur' => 'Étudiant'],
            ['id_type_utilisateur' => 'TYPE_PERS_ADMIN', 'libelle_type_utilisateur' => 'Personnel Administratif']
        ]);

        $this->executeInsert('niveau_acces_donne', [
            ['id_niveau_acces_donne' => 'ACCES_TOTAL', 'libelle_niveau_acces_donne' => 'Accès Total (Admin)'],
            ['id_niveau_acces_donne' => 'ACCES_DEPARTEMENT', 'libelle_niveau_acces_donne' => 'Accès Niveau Département'],
            ['id_niveau_acces_donne' => 'ACCES_PERSONNEL', 'libelle_niveau_acces_donne' => 'Accès Données Personnelles']
        ]);

        $this->executeInsert('statut_rapport_ref', [
            ['id_statut_rapport' => 'RAP_BROUILLON', 'libelle_statut_rapport' => 'Brouillon', 'etape_workflow' => 1],
            ['id_statut_rapport' => 'RAP_SOUMIS', 'libelle_statut_rapport' => 'Soumis', 'etape_workflow' => 2],
            ['id_statut_rapport' => 'RAP_NON_CONF', 'libelle_statut_rapport' => 'Non Conforme', 'etape_workflow' => 2],
            ['id_statut_rapport' => 'RAP_CONF', 'libelle_statut_rapport' => 'Conforme', 'etape_workflow' => 3],
            ['id_statut_rapport' => 'RAP_EN_COMMISSION', 'libelle_statut_rapport' => 'En Évaluation', 'etape_workflow' => 4],
            ['id_statut_rapport' => 'RAP_CORRECT', 'libelle_statut_rapport' => 'Corrections Demandées', 'etape_workflow' => 5],
            ['id_statut_rapport' => 'RAP_VALID', 'libelle_statut_rapport' => 'Validé', 'etape_workflow' => 6],
            ['id_statut_rapport' => 'RAP_REFUSE', 'libelle_statut_rapport' => 'Refusé', 'etape_workflow' => 6]
        ]);
        echo "  ✔️ Référentiels créés.\n";
    }

    /**
     * Crée la structure hiérarchique des permissions et des menus.
     */
    private function seedPermissionsEtNavigation(): void
    {
        echo "\n3. Seeding des permissions et menus (table `traitement`)...\n";
        $this->executeInsert('traitement', [
            ['id_traitement' => 'MENU_ADMIN', 'libelle_traitement' => 'Administration', 'id_parent_traitement' => null, 'icone_class' => 'fa-solid fa-cogs', 'url_associee' => '#', 'est_visible_menu' => 1, 'ordre_affichage' => 900],
            ['id_traitement' => 'MENU_ETUDIANT', 'libelle_traitement' => 'Mon Espace', 'id_parent_traitement' => null, 'icone_class' => 'fa-solid fa-user-graduate', 'url_associee' => '#', 'est_visible_menu' => 1, 'ordre_affichage' => 100],
            ['id_traitement' => 'MENU_COMMISSION', 'libelle_traitement' => 'Commission', 'id_parent_traitement' => null, 'icone_class' => 'fa-solid fa-gavel', 'url_associee' => '#', 'est_visible_menu' => 1, 'ordre_affichage' => 200],
            ['id_traitement' => 'MENU_PERSONNEL', 'libelle_traitement' => 'Scolarité', 'id_parent_traitement' => null, 'icone_class' => 'fa-solid fa-briefcase', 'url_associee' => '#', 'est_visible_menu' => 1, 'ordre_affichage' => 300],

            ['id_traitement' => 'TRAIT_ADMIN_DASHBOARD_ACCEDER', 'libelle_traitement' => 'Tableau de Bord Admin', 'id_parent_traitement' => 'MENU_ADMIN', 'icone_class' => null, 'url_associee' => '/admin/dashboard', 'est_visible_menu' => 1, 'ordre_affichage' => 10],
            ['id_traitement' => 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'libelle_traitement' => 'Gestion Utilisateurs', 'id_parent_traitement' => 'MENU_ADMIN', 'icone_class' => null, 'url_associee' => '/admin/utilisateurs', 'est_visible_menu' => 1, 'ordre_affichage' => 20],
            ['id_traitement' => 'TRAIT_ADMIN_CONFIG_ACCEDER', 'libelle_traitement' => 'Configuration', 'id_parent_traitement' => 'MENU_ADMIN', 'icone_class' => null, 'url_associee' => '/admin/configuration', 'est_visible_menu' => 1, 'ordre_affichage' => 30],
            ['id_traitement' => 'TRAIT_ADMIN_SUPERVISION_VOIR_LOGS', 'libelle_traitement' => 'Supervision', 'id_parent_traitement' => 'MENU_ADMIN', 'icone_class' => null, 'url_associee' => '/admin/supervision', 'est_visible_menu' => 1, 'ordre_affichage' => 40],
        ]);
        echo "  ✔️ Permissions et menus créés.\n";
    }

    /**
     * Crée les utilisateurs de base pour chaque rôle.
     */
    private function seedUsers(): void
    {
        echo "\n4. Seeding des utilisateurs et de leurs profils...\n";
        $defaultPassword = 'Password123!';
        echo "  - Mot de passe par défaut pour tous les utilisateurs : $defaultPassword\n";

        $this->creerUtilisateurComplet(
            ['id' => 'SYS-2025-0001', 'login' => 'Aho', 'email' => 'ahopaul18@gmail.com', 'password' => $defaultPassword, 'type' => 'TYPE_ADMIN', 'groupe' => 'GRP_ADMIN_SYS', 'acces' => 'ACCES_TOTAL'],
            ['nom' => 'D-Aho', 'prenom' => 'Manuel'],
            'personnel_administratif'
        );

        $this->creerUtilisateurComplet(
            ['id' => 'ETU-2025-0001', 'login' => 'sophie.martin', 'email' => 'sophie.martin@etu.dev', 'password' => $defaultPassword, 'type' => 'TYPE_ETUD', 'groupe' => 'GRP_ETUDIANT', 'acces' => 'ACCES_PERSONNEL'],
            ['nom' => 'Martin', 'prenom' => 'Sophie', 'date_naissance' => '2002-05-15'],
            'etudiant'
        );

        $this->creerUtilisateurComplet(
            ['id' => 'ENS-2025-0001', 'login' => 'jean.dupont', 'email' => 'jean.dupont@ens.dev', 'password' => $defaultPassword, 'type' => 'TYPE_ENS', 'groupe' => 'GRP_COMMISSION', 'acces' => 'ACCES_DEPARTEMENT'],
            ['nom' => 'Dupont', 'prenom' => 'Jean'],
            'enseignant'
        );

        $this->creerUtilisateurComplet(
            ['id' => 'ADM-2025-0001', 'login' => 'alain.terieur', 'email' => 'alain.terieur@adm.dev', 'password' => $defaultPassword, 'type' => 'TYPE_PERS_ADMIN', 'groupe' => 'GRP_RS', 'acces' => 'ACCES_DEPARTEMENT'],
            ['nom' => 'Térieur', 'prenom' => 'Alain'],
            'personnel_administratif'
        );

        $this->creerUtilisateurComplet(
            ['id' => 'ADM-2025-0002', 'login' => 'alex.terieur', 'email' => 'alex.terieur@adm.dev', 'password' => $defaultPassword, 'type' => 'TYPE_PERS_ADMIN', 'groupe' => 'GRP_AGENT_CONFORMITE', 'acces' => 'ACCES_DEPARTEMENT'],
            ['nom' => 'Térieur', 'prenom' => 'Alex'],
            'personnel_administratif'
        );

        echo "  ✔️ Utilisateurs créés.\n";
    }

    /**
     * Associe les permissions aux groupes d'utilisateurs.
     */
    private function seedMatricePermissions(): void
    {
        echo "\n5. Association des permissions aux rôles (table `rattacher`)...\n";
        $matrice = [
            'GRP_ADMIN_SYS' => ['MENU_ADMIN', 'TRAIT_ADMIN_DASHBOARD_ACCEDER', 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'TRAIT_ADMIN_CONFIG_ACCEDER', 'TRAIT_ADMIN_SUPERVISION_VOIR_LOGS'],
            'GRP_ETUDIANT' => ['MENU_ETUDIANT'],
            'GRP_COMMISSION' => ['MENU_COMMISSION'],
            'GRP_RS' => ['MENU_PERSONNEL'],
            'GRP_AGENT_CONFORMITE' => ['MENU_PERSONNEL']
        ];

        $dataToInsert = [];
        foreach ($matrice as $groupe => $permissions) {
            foreach ($permissions as $perm) {
                $dataToInsert[] = ['id_groupe_utilisateur' => $groupe, 'id_traitement' => $perm];
            }
        }
        $this->executeInsert('rattacher', $dataToInsert);
        echo "  ✔️ Permissions associées.\n";
    }

    /**
     * Crée une année académique active.
     */
    private function seedAnneeAcademique(): void
    {
        echo "\n6. Création de l'année académique active...\n";
        $this->executeInsert('annee_academique', [
            ['id_annee_academique' => 'ANNEE-2024-2025', 'libelle_annee_academique' => '2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-08-31', 'est_active' => 1]
        ]);
        echo "  ✔️ Année académique créée.\n";
    }

    // ==================================================================
    // == Méthodes Utilitaires
    // ==================================================================

    private function executeInsert(string $table, array $data): void
    {
        foreach ($data as $row) {
            $columns = '`' . implode('`, `', array_keys($row)) . '`';
            $placeholders = ':' . implode(', :', array_keys($row));
            $sql = "INSERT IGNORE INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($row);
        }
    }

    private function creerUtilisateurComplet(array $compte, array $profil, string $typeProfil): void
    {
        $passwordHash = password_hash($compte['password'], PASSWORD_DEFAULT);

        $this->executeInsert('utilisateur', [[
            'numero_utilisateur' => $compte['id'],
            'login_utilisateur' => $compte['login'],
            'email_principal' => $compte['email'],
            'mot_de_passe' => $passwordHash,
            'email_valide' => 1, // L'utilisateur est considéré comme valide
            'statut_compte' => 'actif', // Le compte est directement actif
            'id_niveau_acces_donne' => $compte['acces'],
            'id_groupe_utilisateur' => $compte['groupe'],
            'id_type_utilisateur' => $compte['type']
        ]]);

        $profil['numero_utilisateur'] = $compte['id'];
        $pkProfil = match($typeProfil) {
            'etudiant' => 'numero_carte_etudiant',
            'enseignant' => 'numero_enseignant',
            'personnel_administratif' => 'numero_personnel_administratif'
        };
        $profil[$pkProfil] = $compte['id'];
        $this->executeInsert($typeProfil, [$profil]);

        $prefixe = explode('-', $compte['id'])[0];
        $annee = date('Y');
        $stmt = $this->pdo->prepare("INSERT INTO sequences (nom_sequence, annee, valeur_actuelle) VALUES (:p, :a, 1) ON DUPLICATE KEY UPDATE valeur_actuelle = valeur_actuelle + 1");
        $stmt->execute(['p' => $prefixe, 'a' => $annee]);

        echo "  - Utilisateur '{$compte['login']}' créé.\n";
    }
}

// Exécution du seeder
$seeder = new Seeder();
$seeder->run();