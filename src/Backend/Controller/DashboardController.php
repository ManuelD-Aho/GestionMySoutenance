<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class DashboardController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
    }

    public function index(): void
    {
        if (!$this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $userGroupId = $user['id_groupe_utilisateur'] ?? null;

        switch ($userGroupId) {
            case 'GRP_ADMIN_SYS':
                $this->redirect('/admin/dashboard');
                break;
            case 'GRP_ETUDIANT':
                $this->redirect('/etudiant/dashboard');
                break;
            case 'GRP_COMMISSION':
                $this->redirect('/commission/dashboard');
                break;
            case 'GRP_AGENT_CONFORMITE':
            case 'GRP_RS':
            case 'GRP_PERS_ADMIN':
                $this->redirect('/personnel/dashboard');
                break;
            case 'GRP_ENSEIGNANT':
                $this->serviceSupervision->enregistrerAction($user['numero_utilisateur'], 'ECHEC_LOGIN', null, null, ['reason' => 'Rôle enseignant de base sans permissions de dashboard.']);
                $this->serviceSecurite->logout();
                $this->redirect('/login?error=access_denied_role');
                break;
            default:
                $this->serviceSupervision->enregistrerAction($user['numero_utilisateur'], 'ECHEC_LOGIN', null, null, ['reason' => 'Rôle utilisateur non défini ou inconnu.']);
                $this->serviceSecurite->logout();
                $this->redirect('/login?error=role_undefined');
                break;
        }
    }
}