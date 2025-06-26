<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Reclamation\ServiceReclamation; // Importer le service
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour les types de réclamation si besoin
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ReclamationEtudiantController extends BaseController
{
    private ServiceReclamation $reclamationService;
    private ServiceConfigurationSysteme $configService; // Pour les référentiels de réclamation

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceReclamation          $reclamationService,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->reclamationService = $reclamationService;
        $this->configService = $configService;
    }

    /**
     * Affiche la liste et le suivi des réclamations de l'étudiant.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RECLAMATION_LISTER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            $reclamations = $this->reclamationService->recupererReclamationsEtudiant($numeroCarteEtudiant);

            $data = [
                'page_title' => 'Mes Réclamations',
                'reclamations' => $reclamations
            ];
            $this->render('Etudiant/Reclamation/suivi_reclamations', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de vos réclamations: " . $e->getMessage());
            $this->redirect('/dashboard/etudiant');
        }
    }

    /**
     * Affiche le formulaire de soumission d'une nouvelle réclamation ou la traite.
     */
    public function create(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RECLAMATION_CREER'); // Exiger la permission

        if ($this->isPostRequest()) {
            $this->handleCreateReclamation();
        } else {
            try {
                // Charger les types/catégories de réclamations si vous avez un référentiel pour ça
                $statutsReclamationRef = $this->configService->listerStatutsReclamation(); // A ajouter au configService ou serviceReclamation

                $data = [
                    'page_title' => 'Soumettre une Réclamation',
                    'statuts_reclamation_ref' => $statutsReclamationRef,
                    'form_action' => '/dashboard/etudiant/reclamation/create'
                ];
                $this->render('Etudiant/Reclamation/soumettre_reclamation', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement formulaire: ' . $e->getMessage());
                $this->redirect('/dashboard/etudiant/reclamation');
            }
        }
    }

    /**
     * Traite la soumission d'une nouvelle réclamation.
     */
    private function handleCreateReclamation(): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect('/dashboard/etudiant/reclamation/create');
        }
        $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

        $sujet = $this->post('sujet_reclamation');
        $description = $this->post('description_reclamation');
        // Assurez-vous que la table `reclamation` peut stocker des pièces jointes si nécessaire.
        // Si oui, gérer l'upload ici.

        $rules = [
            'sujet_reclamation' => 'required|string|min:5|max:255',
            'description_reclamation' => 'required|string|min:10',
        ];
        $validationData = [
            'sujet_reclamation' => $sujet,
            'description_reclamation' => $description,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/etudiant/reclamation/create');
        }

        try {
            $this->reclamationService->soumettreReclamation($numeroCarteEtudiant, $sujet, $description);
            $this->setFlashMessage('success', 'Votre réclamation a été soumise avec succès.');
            $this->redirect('/dashboard/etudiant/reclamation');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de la soumission de votre réclamation: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/reclamation/create');
        }
    }

    // Les méthodes update($id) et delete($id) génériques du template initial sont à supprimer.
    // L'étudiant ne peut pas "modifier" une réclamation soumise, il peut en faire une nouvelle.
    // La suppression serait plutôt un archivage côté admin.
    /*
    public function update($id): void {}
    public function delete($id): void {}
    */
}