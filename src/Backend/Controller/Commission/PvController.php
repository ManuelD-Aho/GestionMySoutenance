<?php
namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Commission\ServiceCommission; // Importer le service
use App\Backend\Service\Rapport\ServiceRapport; // Pour les rapports
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Pour récupérer listes si nécessaire
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class PvController extends BaseController
{
    private ServiceCommission $commissionService;
    private ServiceRapport $rapportService;
    private ServiceGestionAcademique $gestionAcadService; // Pour lister etudiants, etc.

    public function __construct(
        ServiceAuthentication    $authService,
        ServicePermissions       $permissionService,
        FormValidator            $validator,
        ServiceCommission        $commissionService, // Injection
        ServiceRapport           $rapportService, // Injection
        ServiceGestionAcademique $gestionAcadService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->commissionService = $commissionService;
        $this->rapportService = $rapportService;
        $this->gestionAcadService = $gestionAcadService;
    }

    /**
     * Affiche la liste des PV existants.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_PV_LISTER'); // Exiger la permission

        try {
            $page = (int) $this->get('page', 1);
            $limit = 20;
            // Vous pouvez ajouter des filtres ici (par statut, par rédacteur, par rapport)
            $filtres = [];
            // $pvList = $this->commissionService->listerCompteRendus($filtres, $page, $limit); // Nouvelle méthode au service commission
            // Pour l'instant, listons tous les compte_rendu via le modèle directement (pour simplicité)
            $pdo = $this->authService->getUtilisateurModel()->getDb();
            $compteRenduModel = new \App\Backend\Model\CompteRendu($pdo);
            $pvList = $compteRenduModel->trouverTout();


            $data = [
                'page_title' => 'Gestion des Procès-Verbaux',
                'pv_list' => $pvList,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $this->commissionService->countCompteRendus($filtres), // A créer
            ];
            $this->render('Commission/PV/consulter_pv', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des PV: " . $e->getMessage());
            $this->redirect('/dashboard/commission');
        }
    }

    /**
     * Affiche le formulaire de rédaction d'un PV ou la traite.
     * @param string|null $id L'ID du PV à modifier, ou null pour une création.
     */
    public function create(string $id = null): void
    {
        $isEdit = ($id !== null);
        $permission = $isEdit ? 'TRAIT_COMMISSION_PV_MODIFIER' : 'TRAIT_COMMISSION_PV_REDIGER';
        $this->requirePermission($permission);

        if ($this->isPostRequest()) {
            $this->handleCreateEditPv($id);
        } else {
            try {
                $pv = null;
                if ($isEdit) {
                    $pdo = $this->authService->getUtilisateurModel()->getDb();
                    $compteRenduModel = new \App\Backend\Model\CompteRendu($pdo);
                    $pv = $compteRenduModel->trouverParIdentifiant($id);
                    if (!$pv) {
                        throw new ElementNonTrouveException("PV non trouvé.");
                    }
                }
                // Charger les rapports validés ou acceptés pour les lier au PV
                $rapportsEligibles = $this->rapportService->listerRapportsParCriteres(['id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_VALID', 'RAP_CORRECT', 'RAP_REFUSE']]]); // Rapports finalisés ou nécessitant un PV

                // Charger les membres de la commission (pour la liste des rédacteurs si on veut)
                // $membresCommission = $this->authService->listerUtilisateursAvecProfils(['id_groupe_utilisateur' => 'GRP_COMMISSION']);

                $data = [
                    'page_title' => ($isEdit ? 'Modifier' : 'Rédiger') . ' un Procès-Verbal',
                    'pv' => $pv,
                    'rapports_eligibles' => $rapportsEligibles,
                    'form_action' => $isEdit ? "/dashboard/commission/pv/edit/{$id}" : "/dashboard/commission/pv/create"
                ];
                $this->render('Commission/PV/rediger_pv', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement formulaire PV: ' . $e->getMessage());
                $this->redirect('/dashboard/commission/pv');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de rédaction/modification de PV.
     * @param string|null $id L'ID du PV à modifier, ou null pour une création.
     */
    private function handleCreateEditPv(?string $id): void
    {
        $isEdit = ($id !== null);
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroRedacteur = $currentUser['numero_utilisateur'];

        $libellePv = $this->post('libelle_pv');
        $typePv = $this->post('type_pv');
        $idRapportEtudiant = $this->post('id_rapport_etudiant'); // Pour PV individuel
        $idsRapportsSession = $this->post('ids_rapports_session', []); // Pour PV session (tableau d'IDs)

        $rules = [
            'libelle_pv' => 'required|string|min:10',
            'type_pv' => 'required|in:Individuel,Session',
        ];
        if ($typePv === 'Individuel') {
            $rules['id_rapport_etudiant'] = 'required|string|max:50';
        } elseif ($typePv === 'Session') {
            $rules['ids_rapports_session'] = 'required|array|min:1';
        }
        $validationData = [
            'libelle_pv' => $libellePv,
            'type_pv' => $typePv,
            'id_rapport_etudiant' => $idRapportEtudiant,
            'ids_rapports_session' => $idsRapportsSession,
        ];
        $this->validator->validate($validationData, $rules); // Utilise $this->requestData qui contient tout

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect($isEdit ? "/dashboard/commission/pv/edit/{$id}" : "/dashboard/commission/pv/create");
        }

        try {
            $pvId = $this->commissionService->redigerOuMettreAJourPv(
                $numeroRedacteur,
                $libellePv,
                $typePv,
                $idRapportEtudiant,
                $idsRapportsSession,
                $id
            );
            $this->setFlashMessage('success', 'Procès-Verbal ' . ($isEdit ? 'modifié' : 'rédigé') . ' avec succès.');
            $this->redirect('/dashboard/commission/pv');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur ' . ($isEdit ? 'modification' : 'rédaction') . ' PV: ' . $e->getMessage());
            $this->redirect($isEdit ? "/dashboard/commission/pv/edit/{$id}" : "/dashboard/commission/pv/create");
        }
    }

    /**
     * Traite la soumission d'un PV pour validation.
     * @param string $id L'ID du PV à soumettre.
     */
    public function submitForValidation(string $id): void
    {
        $this->requirePermission('TRAIT_COMMISSION_PV_SOUMETTRE_VALIDATION'); // Permission de soumettre un PV pour validation

        try {
            $this->commissionService->soumettrePvPourValidation($id);
            $this->setFlashMessage('success', 'PV soumis pour validation avec succès. Les membres de la commission ont été notifiés.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de soumettre ce PV pour validation : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/commission/pv');
    }

    /**
     * Affiche le formulaire de validation/rejet d'un PV ou la traite.
     * @param string $id L'ID du PV à valider.
     */
    public function validatePv(string $id): void
    {
        $this->requirePermission('TRAIT_COMMISSION_PV_VALIDER'); // Permission de valider un PV

        if ($this->isPostRequest()) {
            $this->handleValidatePv($id);
        } else {
            try {
                $pdo = $this->authService->getUtilisateurModel()->getDb();
                $compteRenduModel = new \App\Backend\Model\CompteRendu($pdo);
                $pv = $compteRenduModel->trouverParIdentifiant($id);
                if (!$pv) {
                    throw new ElementNonTrouveException("PV non trouvé.");
                }
                // Vous devrez peut-être récupérer des informations supplémentaires sur le PV
                // comme les rapports liés, les votes des membres de la commission (si le PV résume des votes)
                // et les décisions de validation_pv_ref.

                $data = [
                    'page_title' => 'Valider Procès-Verbal',
                    'pv' => $pv,
                    'form_action' => "/dashboard/commission/pv/validate/{$id}"
                ];
                $this->render('Commission/PV/valider_pv', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement formulaire validation PV: ' . $e->getMessage());
                $this->redirect('/dashboard/commission/pv');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de validation/rejet d'un PV.
     * @param string $id L'ID du PV.
     */
    private function handleValidatePv(string $id): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroEnseignantValidateur = $currentUser['numero_utilisateur'];

        $decisionValidation = $this->post('decision_validation_pv');
        $commentaire = $this->post('commentaire_validation_pv');

        $rules = [
            'decision_validation_pv' => 'required|string|max:50',
            'commentaire_validation_pv' => 'nullable|string', // Peut être obligatoire pour certains statuts
        ];
        $this->validator->validate(['decision_validation_pv' => $decisionValidation, 'commentaire_validation_pv' => $commentaire], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/commission/pv/validate/{$id}");
        }

        try {
            $this->commissionService->validerOuRejeterPv($id, $numeroEnseignantValidateur, $decisionValidation, $commentaire);
            $this->setFlashMessage('success', 'Avis sur le PV enregistré avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de valider ce PV : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/commission/pv');
    }

    /**
     * Supprime un PV.
     * @param string $id L'ID du PV à supprimer.
     */
    public function delete(string $id): void
    {
        $this->requirePermission('TRAIT_COMMISSION_PV_SUPPRIMER'); // Exiger la permission

        try {
            // Pour la suppression, on utilise le modèle CompteRendu directement ou une méthode du service
            $pdo = $this->authService->getUtilisateurModel()->getDb();
            $compteRenduModel = new \App\Backend\Model\CompteRendu($pdo);
            if (!$compteRenduModel->supprimerParIdentifiant($id)) {
                throw new OperationImpossibleException("Échec de la suppression du PV.");
            }
            $this->setFlashMessage('success', 'PV supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer ce PV : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/commission/pv');
    }
}