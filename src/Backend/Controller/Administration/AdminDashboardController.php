<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\PermissionException;

class AdminDashboardController extends BaseController
{
    protected ServiceSupervisionInterface $supervisionService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->supervisionService = $container->get(ServiceSupervisionInterface::class);
    }

    /**
     * Affiche le tableau de bord principal de l'administrateur système.
     * Inclut des statistiques clés et des liens rapides vers les fonctionnalités d'administration.
     *
     * Permissions requises: TRAIT_ADMIN_DASHBOARD_ACCEDER
     *
     * @return void
     */
    public function index(): void
    {
        // Vérifier la permission d'accéder au tableau de bord administrateur
        $this->requirePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            // Récupérer les statistiques globales du système
            $stats = $this->supervisionService->genererStatistiquesDashboardAdmin();

            // Préparer les données pour la vue
            $data = [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats,
                // Vous pouvez ajouter ici d'autres données spécifiques au dashboard
                // comme des listes d'alertes récentes, des tâches en échec, etc.
            ];

            // Rendre la vue du tableau de bord administrateur
            $this->render('Administration/dashboard_admin', $data);

        } catch (PermissionException $e) {
            // Cette exception est déjà gérée par requirePermission qui appelle renderError(403)
            // Mais on la catch ici pour une gestion plus fine si nécessaire (ex: log spécifique)
            error_log("Accès non autorisé au dashboard admin pour l'utilisateur " . ($_SESSION['user_id'] ?? 'ANONYMOUS') . ": " . $e->getMessage());
            // Le renderError(403) est déjà appelé par requirePermission
        } catch (\Exception $e) {
            // Gérer toute autre exception inattendue
            $this->addFlashMessage('error', 'Une erreur est survenue lors du chargement du tableau de bord : ' . $e->getMessage());
            error_log("Erreur inattendue dans AdminDashboardController::index: " . $e->getMessage());
            $this->renderError(500, 'Impossible de charger le tableau de bord.');
        }
    }

    // Note: Les autres méthodes spécifiques à l'administration (gestion des utilisateurs,
    // configuration, supervision) seront implémentées dans leurs contrôleurs dédiés
    // (UtilisateurController, ConfigurationController, SupervisionController)
    // et non directement dans AdminDashboardController, qui sert de point d'entrée et de résumé.
}