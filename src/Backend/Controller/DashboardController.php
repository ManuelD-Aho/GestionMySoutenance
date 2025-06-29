<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Config\Container;

class DashboardController extends BaseController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->checkAuthentication();
    }

    public function index(): void
    {
        $user = $this->authService->getUtilisateurConnecte();

        if (!$user) {
            $this->authService->logout();
            $this->redirect('/login');
            return;
        }

        $userRole = $user['id_groupe_utilisateur'] ?? null;

        $redirectMap = [
            'GRP_ADMIN_SYS' => '/dashboard/admin',
            'GRP_ETUDIANT' => '/dashboard/etudiant',
            'GRP_COMMISSION' => '/dashboard/commission',
            'GRP_RS' => '/dashboard/personnel-admin',
            'GRP_AGENT_CONFORMITE' => '/dashboard/personnel-admin',
            'GRP_PERS_ADMIN' => '/dashboard/personnel-admin',
            'GRP_ENSEIGNANT' => '/dashboard/commission',
        ];

        if (isset($redirectMap[$userRole])) {
            $this->redirect($redirectMap[$userRole]);
            return;
        }

        $this->addFlashMessage('error', 'Votre rôle utilisateur n\'est pas configuré pour accéder à un tableau de bord.');
        $this->authService->logout();
        $this->redirect('/login');
    }
}