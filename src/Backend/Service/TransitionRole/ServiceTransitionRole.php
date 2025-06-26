<?php

namespace App\Backend\Service\TransitionRole;

use App\Backend\Exception\PermissionException;
use PDO;
use App\Backend\Model\Delegation;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\Approuver;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\Reclamation;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\Permissions\ServicePermissionsInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceTransitionRole implements ServiceTransitionRoleInterface
{
    private Delegation $delegationModel;
    private Utilisateur $utilisateurModel;
    private VoteCommission $voteCommissionModel;
    private Approuver $approuverModel;
    private CompteRendu $compteRenduModel;
    private ValidationPv $validationPvModel;
    private Reclamation $reclamationModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceNotificationInterface $notificationService;
    private ServicePermissionsInterface $permissionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Delegation $delegationModel,
        Utilisateur $utilisateurModel,
        VoteCommission $voteCommissionModel,
        Approuver $approuverModel,
        CompteRendu $compteRenduModel,
        ValidationPv $validationPvModel,
        Reclamation $reclamationModel,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceNotificationInterface $notificationService,
        ServicePermissionsInterface $permissionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->delegationModel = $delegationModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->voteCommissionModel = $voteCommissionModel;
        $this->approuverModel = $approuverModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->validationPvModel = $validationPvModel;
        $this->reclamationModel = $reclamationModel;
        $this->supervisionService = $supervisionService;
        $this->notificationService = $notificationService;
        $this->permissionService = $permissionService;
        $this->idGenerator = $idGenerator;
    }

    public function detecterTachesOrphelines(string $numeroUtilisateur): array
    {
        $taches = [];
        $taches['votes_en_attente'] = $this->voteCommissionModel->trouverParCritere(['numero_enseignant' => $numeroUtilisateur, 'id_decision_vote' => 'VOTE_EN_ATTENTE']);
        $taches['validations_conformite_en_attente'] = $this->approuverModel->trouverParCritere(['numero_personnel_administratif' => $numeroUtilisateur, 'id_statut_conformite' => 'CONF_EN_ATTENTE']);
        $taches['pv_a_rediger'] = $this->compteRenduModel->trouverParCritere(['id_redacteur' => $numeroUtilisateur, 'id_statut_pv' => 'PV_BROUILLON']);
        $taches['pv_a_valider'] = $this->validationPvModel->trouverParCritere(['numero_enseignant' => $numeroUtilisateur, 'id_decision_validation_pv' => 'DV_PV_EN_ATTENTE']);
        $taches['reclamations_assignees'] = $this->reclamationModel->trouverParCritere(['numero_personnel_traitant' => $numeroUtilisateur, 'id_statut_reclamation' => 'RECLAM_EN_COURS']);
        return $taches;
    }

    public function reassignerTache(string $idTache, string $typeTache, string $nouveauResponsable): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $success = match ($typeTache) {
                'vote' => $this->voteCommissionModel->mettreAJourParIdentifiant($idTache, ['numero_enseignant' => $nouveauResponsable]),
                'validation_conformite' => $this->approuverModel->mettreAJourParIdentifiant($idTache, ['numero_personnel_administratif' => $nouveauResponsable]),
                'redaction_pv' => $this->compteRenduModel->mettreAJourParIdentifiant($idTache, ['id_redacteur' => $nouveauResponsable]),
                'validation_pv' => $this->validationPvModel->mettreAJourParIdentifiant($idTache, ['numero_enseignant' => $nouveauResponsable]),
                'reclamation' => $this->reclamationModel->mettreAJourParIdentifiant($idTache, ['numero_personnel_traitant' => $nouveauResponsable]),
                default => throw new \InvalidArgumentException("Type de tâche '{$typeTache}' non reconnu."),
            };

            if (!$success) {
                throw new OperationImpossibleException("Échec de la réassignation de la tâche.");
            }

            $this->utilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'REASSIGNATION_TACHE',
                "Tâche '{$idTache}' de type '{$typeTache}' réassignée à {$nouveauResponsable}."
            );
            $this->notificationService->envoyerNotificationUtilisateur($nouveauResponsable, 'NOUVELLE_TACHE_ASSIGNEE', "Une nouvelle tâche vous a été assignée.");
            return true;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string
    {
        if (!$this->permissionService->utilisateurPossedePermission($idTraitement)) {
            throw new PermissionException("Le délégant ne possède pas la permission qu'il tente de déléguer.");
        }

        $idDelegation = $this->idGenerator->genererIdentifiantUnique('DEL');
        $data = [
            'id_delegation' => $idDelegation,
            'id_delegant' => $idDelegant,
            'id_delegue' => $idDelegue,
            'id_traitement' => $idTraitement,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'statut' => 'Active',
            'contexte_id' => $contexteId,
            'contexte_type' => $contexteType
        ];

        $this->delegationModel->creer($data);
        $this->supervisionService->enregistrerAction($idDelegant, 'CREATION_DELEGATION', "Délégation de '{$idTraitement}' à {$idDelegue} créée.");
        return $idDelegation;
    }

    public function annulerDelegation(string $idDelegation): bool
    {
        $delegation = $this->delegationModel->trouverParIdentifiant($idDelegation);
        if (!$delegation) {
            throw new ElementNonTrouveException("Délégation non trouvée.");
        }

        $success = $this->delegationModel->mettreAJourParIdentifiant($idDelegation, ['statut' => 'Révoquée']);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ANNULATION_DELEGATION',
                "Délégation '{$idDelegation}' annulée."
            );
        }
        return $success;
    }
}