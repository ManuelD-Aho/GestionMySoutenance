<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
// use App\Backend\Service\RessourcePartageeService; // Si vous avez un service pour des ressources dynamiques

class RessourcesEtudiantController extends BaseController
{
    // private RessourcePartageeService $ressourceService; // Si les ressources sont dynamiques

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
        // RessourcePartageeService $ressourceService = null // Injection optionnelle
    ) {
        parent::__construct($authService, $permissionService, $validator);
        // $this->ressourceService = $ressourceService; // Affectation
    }

    /**
     * Affiche la page des ressources utiles aux étudiants.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RESSOURCES_ACCEDER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }

            // Si les ressources étaient dynamiques:
            // $ressources = $this->ressourceService->listerRessourcesPourEtudiant();

            $data = [
                'page_title' => 'Ressources Utiles',
                'ressources_dynamiques' => [], // Remplacer par des données réelles si dynamiques
                // Pour l'instant, la vue peut contenir du contenu statique
            ];
            $this->render('Etudiant/ressources_etudiant', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des ressources: " . $e->getMessage());
            $this->redirect('/dashboard/etudiant');
        }
    }
}