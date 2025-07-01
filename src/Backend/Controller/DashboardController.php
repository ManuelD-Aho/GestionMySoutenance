<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Config\Container;

class DashboardController extends BaseController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function index(): void
    {
        // 1. Vérifier si l'utilisateur est connecté. Si non, rediriger vers la page de connexion.
        if (!$this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/login');
        }

        // 2. Récupérer les données de l'utilisateur connecté pour déterminer son groupe.
        $user = $this->securiteService->getUtilisateurConnecte();
        $dashboardUrl = null; // Initialiser à null pour détecter si une URL est trouvée

        // 3. Déterminer l'URL du tableau de bord en fonction du groupe de l'utilisateur.
        switch ($user['id_groupe_utilisateur']) {
            case 'GRP_ADMIN_SYS':
                $dashboardUrl = '/admin/dashboard';
                break;
            case 'GRP_ETUDIANT':
                $dashboardUrl = '/etudiant/dashboard';
                break;
            case 'GRP_ENSEIGNANT': // Rôle de base enseignant, peut être un dashboard générique
            case 'GRP_COMMISSION': // Membre de commission
                $dashboardUrl = '/commission/dashboard';
                break;
            case 'GRP_PERS_ADMIN': // Personnel administratif de base
            case 'GRP_RS': // Responsable Scolarité
            case 'GRP_AGENT_CONFORMITE': // Agent de Conformité
                $dashboardUrl = '/personnel/dashboard';
                break;
            default:
                // Si le groupe de l'utilisateur n'est pas reconnu ou n'a pas de dashboard spécifique.
                // L'utilisateur est connecté mais n'a pas de destination claire.
                $this->addFlashMessage('error', 'Votre rôle ne vous donne pas accès à un tableau de bord spécifique.');
                // Enregistrer l'action d'accès refusé pour audit
                $this->supervisionService->enregistrerAction(
                    $user['numero_utilisateur'],
                    'ACCES_DASHBOARD_REFUSE',
                    null,
                    null,
                    ['reason' => 'Groupe utilisateur non géré pour le dashboard', 'group' => $user['id_groupe_utilisateur']]
                );
                // Rendre une page d'erreur 403 (Accès Interdit)
                $this->renderError(403, 'Accès non autorisé à un tableau de bord.');
                break; // Le `exit()` dans `renderError` termine l'exécution ici.
        }

        // 4. Si une URL de tableau de bord a été déterminée, rediriger l'utilisateur.
        if ($dashboardUrl) {
            $this->supervisionService->enregistrerAction(
                $user['numero_utilisateur'],
                'ACCES_DASHBOARD_REUSSI',
                null,
                $dashboardUrl,
                ['group' => $user['id_groupe_utilisateur']]
            );
            $this->redirect($dashboardUrl);
        }
    }
}