<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator;

class AdminDashboardController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
    }

    /**
     * Affiche le tableau de bord principal de l'administrateur.
     * Récupère et affiche les statistiques clés de la plateforme.
     */
    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            $stats = $this->serviceSupervision->genererStatistiquesDashboardAdmin();
            $this->render('Administration/dashboard_admin.php', [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'DASHBOARD_ADMIN_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les statistiques du tableau de bord."]);
        }
    }
}