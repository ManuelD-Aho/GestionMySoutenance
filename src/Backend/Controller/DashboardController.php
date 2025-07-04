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
            $menuItems = $this->securiteService->construireMenuPourUtilisateurConnecte();
            error_log("DEBUG DashboardController: MenuItems reçus de ServiceSecurite: " . json_encode($menuItems));
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