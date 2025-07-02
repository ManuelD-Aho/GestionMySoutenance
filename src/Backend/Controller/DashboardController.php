<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;

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
        if (!$this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/login');
            return; // Suppression de l'instruction inaccessible
        }

        $user = $this->securiteService->getUtilisateurConnecte();
        $dashboardUrl = null;

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
                $this->addFlashMessage('error', 'Votre rôle ne vous donne pas accès à un tableau de bord spécifique.');
                $this->supervisionService->enregistrerAction(
                    $user['numero_utilisateur'],
                    'ACCES_DASHBOARD_REFUSE',
                    null,
                    null,
                    ['reason' => 'Groupe utilisateur non géré', 'group' => $user['id_groupe_utilisateur']]
                );
                $this->renderError(403, 'Accès non autorisé à un tableau de bord.');
                return; // Suppression de l'instruction inaccessible
        }

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