<?php
namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\DoublonException;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Commission\ServiceCommission; // Importer le service
use App\Backend\Service\Rapport\ServiceRapport; // Pour les rapports
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour les décisions de vote
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ValidationRapportController extends BaseController
{
    private ServiceCommission $commissionService;
    private ServiceRapport $rapportService;
    private ServiceConfigurationSysteme $configService; // Pour lister les décisions de vote

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceCommission           $commissionService, // Injection
        ServiceRapport              $rapportService, // Injection
        ServiceConfigurationSysteme $configService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->commissionService = $commissionService;
        $this->rapportService = $rapportService;
        $this->configService = $configService;
    }

    /**
     * Affiche la liste des rapports en attente de traitement par la commission.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_LISTER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroEnseignant = $currentUser['numero_utilisateur'];

            // Rapports assignés à l'enseignant pour évaluation / vote
            $rapportsAssigned = $this->commissionService->recupererRapportsAssignedToJury($numeroEnseignant);
            // Ou lister les rapports dont le statut est 'RAP_EN_COMM' pour la commission
            $rapportsEnCommission = $this->rapportService->listerRapportsParCriteres(['id_statut_rapport' => 'RAP_EN_COMM']);

            $data = [
                'page_title' => 'Rapports à Traiter par la Commission',
                'rapports_assigned' => $rapportsAssigned,
                'rapports_en_commission' => $rapportsEnCommission, // Peut être une liste distincte ou fusionnée
            ];
            $this->render('Commission/Rapports/liste_rapports_a_traiter', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des rapports: " . $e->getMessage());
            $this->redirect('/dashboard/commission');
        }
    }

    /**
     * Affiche les détails d'un rapport et l'interface de vote.
     * @param string $idRapport L'ID du rapport étudiant.
     */
    public function showVoteInterface(string $idRapport): void
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroEnseignant = $currentUser['numero_utilisateur'];

            $rapport = $this->rapportService->recupererInformationsRapportComplet($idRapport);
            if (!$rapport) {
                throw new ElementNonTrouveException("Rapport non trouvé.");
            }

            // Vérifier que l'enseignant est affecté à ce rapport et peut voter
            // $isAssigned = $this->commissionService->isEnseignantAssignedToRapport($numeroEnseignant, $idRapport); // Méthode à créer
            // if (!$isAssigned) {
            //     throw new OperationImpossibleException("Vous n'êtes pas autorisé à voter pour ce rapport.");
            // }
            // Vérifier le statut du rapport pour autoriser le vote (ex: doit être 'RAP_EN_COMM')
            if ($rapport['id_statut_rapport'] !== 'RAP_EN_COMM') {
                throw new OperationImpossibleException("Ce rapport n'est pas dans un état permettant le vote.");
            }

            // Récupérer les décisions de vote possibles (DV_APPROUVE, DV_REFUSE, DV_DISCUSSION)
            $pdo = $this->authService->getUtilisateurModel()->getDb();
            $decisionVoteRefModel = new \App\Backend\Model\DecisionVoteRef($pdo);
            $decisionsVote = $decisionVoteRefModel->trouverTout();

            // Récupérer le vote existant de cet enseignant pour ce rapport et le tour actuel
            $currentTour = $rapport['votes'][0]['tour_vote'] ?? 1; // Le tour actuel du rapport (si le rapport contient cette info)
            $existingVote = $this->commissionService->getVoteByEnseignantRapportTour($numeroEnseignant, $idRapport, $currentTour); // Nouvelle méthode au service

            $data = [
                'page_title' => 'Évaluer et Voter pour le Rapport',
                'rapport' => $rapport,
                'decisions_vote' => $decisionsVote,
                'existing_vote' => $existingVote,
                'current_tour' => $currentTour,
                'form_action' => "/dashboard/commission/validation/rapports/{$idRapport}/vote"
            ];
            $this->render('Commission/Rapports/interface_vote', $data); // Vue qui inclura les détails du rapport
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement interface de vote: ' . $e->getMessage());
            $this->redirect('/dashboard/commission/validation/rapports');
        }
    }

    /**
     * Traite la soumission du vote pour un rapport.
     * @param string $idRapport L'ID du rapport étudiant.
     */
    public function submitVote(string $idRapport): void
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER');

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroEnseignant = $currentUser['numero_utilisateur'];

        $idDecisionVote = $this->post('id_decision_vote');
        $commentaireVote = $this->post('commentaire_vote');
        $tourVote = (int)$this->post('tour_vote', 1);

        $rules = [
            'id_decision_vote' => 'required|string|max:50',
            // Le commentaire est obligatoire pour certaines décisions, c'est géré dans le Service
            'commentaire_vote' => 'nullable|string',
            'tour_vote' => 'required|integer|min:1',
        ];
        $validationData = [
            'id_decision_vote' => $idDecisionVote,
            'commentaire_vote' => $commentaireVote,
            'tour_vote' => $tourVote,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
        }

        try {
            $this->commissionService->enregistrerVotePourRapport(
                $idRapport,
                $numeroEnseignant,
                $idDecisionVote,
                $commentaireVote,
                $tourVote,
                $this->post('id_session', null) // Si le vote est lié à une session spécifique
            );
            $this->setFlashMessage('success', 'Votre vote a été enregistré avec succès.');
            $this->redirect('/dashboard/commission/validation/rapports');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Vous avez déjà voté pour ce rapport lors de ce tour.');
            $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'enregistrement de votre vote: ' . $e->getMessage());
            $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
        }
    }

    /**
     * Déclenche un nouveau tour de vote pour un rapport.
     * Action réservée au président de la commission.
     * @param string $idRapport L'ID du rapport.
     */
    public function newVoteRound(string $idRapport): void
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_NOUVEAU_TOUR'); // Permission spécifique
        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
        }

        try {
            $this->commissionService->lancerNouveauTourVote($idRapport);
            $this->setFlashMessage('success', 'Un nouveau tour de vote a été initié pour ce rapport.');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'initialisation du nouveau tour de vote: ' . $e->getMessage());
        }
        $this->redirect("/dashboard/commission/validation/rapports/{$idRapport}/vote");
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer
    // car les fonctionnalités spécifiques sont traitées par des méthodes dédiées (showVoteInterface, submitVote).
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}