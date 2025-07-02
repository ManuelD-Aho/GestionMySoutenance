<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance

class DashboardController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService);
    }

    /**
     * Point d'entrée après la connexion.
     * Redirige l'utilisateur vers son tableau de bord spécifique en fonction de son groupe.
     */
    public function index(): void
    {
        // 1. Vérifier si l'utilisateur est connecté.
        if (!$this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/login');
            return;
        }

        // 2. Récupérer l'utilisateur et son groupe.
        $user = $this->securiteService->getUtilisateurConnecte();
        $dashboardUrl = null;

        // 3. Déterminer l'URL du tableau de bord.
        switch ($user['id_groupe_utilisateur']) {
            case 'GRP_ADMIN_SYS':
                $dashboardUrl = '/admin/dashboard';
                break;
            case 'GRP_ETUDIANT':
                $dashboardUrl = '/etudiant/dashboard';
                break;
            case 'GRP_ENSEIGNANT':
            case 'GRP_COMMISSION':
                $dashboardUrl = '/commission/dashboard';
                break;
            case 'GRP_PERS_ADMIN':
            case 'GRP_RS':
            case 'GRP_AGENT_CONFORMITE':
                $dashboardUrl = '/personnel/dashboard';
                break;
            default:
                // 6. Gérer les rôles non reconnus.
                $this->addFlashMessage('error', 'Votre rôle ne vous donne pas accès à un tableau de bord spécifique.');
                $this->supervisionService->enregistrerAction(
                    $user['numero_utilisateur'],
                    'ACCES_DASHBOARD_REFUSE',
                    null,
                    null,
                    ['reason' => 'Groupe utilisateur non géré', 'group' => $user['id_groupe_utilisateur']]
                );
                $this->renderError(403, 'Accès non autorisé à un tableau de bord.');
                return; // renderError contient un exit()
        }

        // 4. & 5. Enregistrer l'accès et rediriger.
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