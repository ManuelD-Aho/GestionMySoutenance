<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class DashboardController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
    }

    /**
     * Point d'entrée après la connexion.
     * Affiche le tableau de bord avec le menu dynamique selon le rôle.
     */
    public function index(): void
    {
        error_log("DEBUG DashboardController: Accès au tableau de bord. ID de Session actuel: " . session_id());
        error_log("DEBUG DashboardController: Données de session à l'accès du tableau de bord: " . json_encode($_SESSION));

        if (!$this->securiteService->estUtilisateurConnecte()) {
            error_log("DEBUG DashboardController: Utilisateur NON connecté, redirection vers la connexion. ID de Session: " . session_id());
            $this->redirect('/login');
            return;
        }

        $user = $this->securiteService->getUtilisateurConnecte();
        error_log("DEBUG DashboardController: Utilisateur connecté: " . ($user['numero_utilisateur'] ?? 'N/A'));

        // NOUVEAU : Afficher le tableau de bord unifié au lieu de rediriger
        try {
            // Récupérer les éléments de menu basés sur les permissions
            $menuItems = $this->getMenuItemsForUserPermissions();

            // Données pour la vue du tableau de bord
            $data = [
                'page_title' => 'Tableau de Bord',
                'user' => $user,
                'menu_items' => $menuItems,
                'user_permissions' => $_SESSION['user_group_permissions'] ?? [],
                'user_role' => $this->mapGroupToRole($user['id_groupe_utilisateur'] ?? ''),
                'dashboard_content' => $this->getDashboardContentForGroup($user['id_groupe_utilisateur'] ?? '')
            ];

            error_log("DEBUG DashboardController: Menu items générés: " . count($menuItems));
            error_log("DEBUG DashboardController: Permissions utilisateur: " . json_encode($_SESSION['user_group_permissions'] ?? []));

            $this->render('common/dashboard', $data);

        } catch (\Exception $e) {
            error_log("ERROR DashboardController: Erreur lors du rendu du tableau de bord: " . $e->getMessage());
            $this->addFlashMessage('error', 'Erreur lors du chargement du tableau de bord.');
            $this->redirect('/login');
        }
    }

    /**
     * CORRECTION : Récupère les éléments de menu basés sur les permissions utilisateur
     */
    private function getMenuItemsForUserPermissions(): array
    {
        $menu = [];

        // CORRECTION : Utiliser la bonne variable de session
        $userPermissions = $_SESSION['user_group_permissions'] ?? [];

        error_log("DEBUG DashboardController: Permissions trouvées: " . json_encode($userPermissions));

        // Menu de base pour tous les utilisateurs connectés
        $menu['dashboard'] = [
            'label' => 'Tableau de Bord',
            'url' => '/dashboard',
            'icon' => 'dashboard',
            'active' => true
        ];

        // Menus basés sur les permissions
        foreach ($userPermissions as $permission) {
            switch ($permission) {
                case 'MENU_ADMINISTRATION':
                    $adminChildren = $this->getAdminMenuItems($userPermissions);
                    if (!empty($adminChildren)) {
                        $menu['administration'] = [
                            'label' => 'Administration',
                            'icon' => 'admin_panel_settings',
                            'children' => $adminChildren
                        ];
                    }
                    break;

                case 'MENU_ETUDIANT':
                    $etudiantChildren = $this->getEtudiantMenuItems($userPermissions);
                    if (!empty($etudiantChildren)) {
                        $menu['etudiant'] = [
                            'label' => 'Espace Étudiant',
                            'icon' => 'school',
                            'children' => $etudiantChildren
                        ];
                    }
                    break;

                case 'MENU_COMMISSION':
                    $commissionChildren = $this->getCommissionMenuItems($userPermissions);
                    if (!empty($commissionChildren)) {
                        $menu['commission'] = [
                            'label' => 'Commission',
                            'icon' => 'gavel',
                            'children' => $commissionChildren
                        ];
                    }
                    break;

                case 'MENU_PERSONNEL':
                    $personnelChildren = $this->getPersonnelMenuItems($userPermissions);
                    if (!empty($personnelChildren)) {
                        $menu['personnel'] = [
                            'label' => 'Personnel',
                            'icon' => 'work',
                            'children' => $personnelChildren
                        ];
                    }
                    break;
            }
        }

        error_log("DEBUG DashboardController: Menu final généré: " . json_encode($menu));
        return $menu;
    }

    /**
     * Génère les sous-menus pour l'administration
     */
    private function getAdminMenuItems(array $userPermissions): array
    {
        $adminItems = [];

        if (in_array('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', $userPermissions)) {
            $adminItems[] = [
                'label' => 'Gestion Utilisateurs',
                'url' => '/admin/utilisateurs',
                'icon' => 'people'
            ];
        }

        if (in_array('TRAIT_ADMIN_CONFIG_ACCEDER', $userPermissions)) {
            $adminItems[] = [
                'label' => 'Configuration',
                'url' => '/admin/config',
                'icon' => 'settings'
            ];
        }

        if (in_array('TRAIT_ADMIN_SUPERVISION_ACCEDER', $userPermissions)) {
            $adminItems[] = [
                'label' => 'Supervision',
                'url' => '/admin/supervision',
                'icon' => 'visibility'
            ];
        }

        if (in_array('TRAIT_ADMIN_REPORTING_ACCEDER', $userPermissions)) {
            $adminItems[] = [
                'label' => 'Reporting',
                'url' => '/admin/reporting',
                'icon' => 'assessment'
            ];
        }

        return $adminItems;
    }

    /**
     * Génère les sous-menus pour l'étudiant
     */
    private function getEtudiantMenuItems(array $userPermissions): array
    {
        $etudiantItems = [];

        if (in_array('TRAIT_ETUDIANT_PROFIL_GERER', $userPermissions)) {
            $etudiantItems[] = [
                'label' => 'Mon Profil',
                'url' => '/etudiant/profil',
                'icon' => 'account_circle'
            ];
        }

        if (in_array('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', $userPermissions)) {
            $etudiantItems[] = [
                'label' => 'Mes Rapports',
                'url' => '/etudiant/rapports',
                'icon' => 'description'
            ];
        }

        if (in_array('TRAIT_ETUDIANT_RAPPORT_SUIVRE', $userPermissions)) {
            $etudiantItems[] = [
                'label' => 'Suivi des Rapports',
                'url' => '/etudiant/suivi',
                'icon' => 'timeline'
            ];
        }

        return $etudiantItems;
    }

    /**
     * Génère les sous-menus pour la commission
     */
    private function getCommissionMenuItems(array $userPermissions): array
    {
        $commissionItems = [];

        if (in_array('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER', $userPermissions)) {
            $commissionItems[] = [
                'label' => 'Validation Rapports',
                'url' => '/commission/validation',
                'icon' => 'check_circle'
            ];
        }

        return $commissionItems;
    }

    /**
     * Génère les sous-menus pour le personnel
     */
    private function getPersonnelMenuItems(array $userPermissions): array
    {
        $personnelItems = [];

        if (in_array('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', $userPermissions)) {
            $personnelItems[] = [
                'label' => 'Scolarité',
                'url' => '/personnel/scolarite',
                'icon' => 'school'
            ];
        }

        if (in_array('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER', $userPermissions)) {
            $personnelItems[] = [
                'label' => 'Conformité',
                'url' => '/personnel/conformite',
                'icon' => 'verified'
            ];
        }

        if (in_array('TRAIT_PERS_ADMIN_RECLAMATIONS_GERER', $userPermissions)) {
            $personnelItems[] = [
                'label' => 'Réclamations',
                'url' => '/personnel/reclamations',
                'icon' => 'report_problem'
            ];
        }

        return $personnelItems;
    }

    /**
     * Mappe le groupe utilisateur vers un rôle simple
     */
    private function mapGroupToRole(string $groupeUtilisateur): string
    {
        return match($groupeUtilisateur) {
            'GRP_ADMIN_SYS' => 'admin',
            'GRP_ETUDIANT' => 'etudiant',
            'GRP_ENSEIGNANT', 'GRP_COMMISSION' => 'enseignant',
            'GRP_PERS_ADMIN', 'GRP_RS', 'GRP_AGENT_CONFORMITE' => 'personnel',
            default => 'guest'
        };
    }

    /**
     * Récupère le contenu spécifique du tableau de bord selon le groupe
     */
    private function getDashboardContentForGroup(string $groupeUtilisateur): array
    {
        return match($groupeUtilisateur) {
            'GRP_ADMIN_SYS' => [
                'type' => 'admin',
                'widgets' => ['stats_users', 'stats_system', 'recent_actions']
            ],
            'GRP_ETUDIANT' => [
                'type' => 'etudiant',
                'widgets' => ['my_reports', 'notifications', 'calendar']
            ],
            'GRP_ENSEIGNANT', 'GRP_COMMISSION' => [
                'type' => 'enseignant',
                'widgets' => ['reports_to_validate', 'my_students', 'calendar']
            ],
            'GRP_PERS_ADMIN', 'GRP_RS', 'GRP_AGENT_CONFORMITE' => [
                'type' => 'personnel',
                'widgets' => ['pending_tasks', 'documents_review', 'statistics']
            ],
            default => [
                'type' => 'default',
                'widgets' => ['welcome']
            ]
        };
    }
}