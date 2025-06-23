<?php
namespace App\Backend\Service\Transition;

use PDO;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceTransition implements ServiceTransitionInterface
{
    private VoteCommission $voteCommissionModel;
    private ValidationPv $validationPvModel;
    private RapportEtudiant $rapportEtudiantModel;

    public function __construct(PDO $db)
    {
        $this->voteCommissionModel = new VoteCommission($db);
        $this->validationPvModel = new ValidationPv($db);
        $this->rapportEtudiantModel = new RapportEtudiant($db);
    }

    public function trouverTachesOrphelines(string $numeroUtilisateur): array
    {
        $taches = [];
        $taches['votes_en_attente'] = $this->voteCommissionModel->trouverParCritere(['numero_enseignant' => $numeroUtilisateur, 'id_decision_vote' => 'VOTE_EN_ATTENTE']); // Supposant un statut de vote en attente
        $taches['validations_pv_en_attente'] = $this->validationPvModel->trouverParCritere(['numero_enseignant' => $numeroUtilisateur, 'id_decision_validation_pv' => 'VALID_EN_ATTENTE']);
        $taches['rapports_conformite_en_attente'] = $this->rapportEtudiantModel->trouverParCritere(['id_agent_conformite_assigne' => $numeroUtilisateur, 'id_statut_rapport' => 'RAP_SOUMIS']); // Supposant une colonne d'assignation
        return $taches;
    }

    public function reassignerVote(string $idVote, string $nouveauNumeroEnseignant): bool
    {
        return $this->voteCommissionModel->mettreAJourParIdentifiant($idVote, ['numero_enseignant' => $nouveauNumeroEnseignant]);
    }

    public function reassignerValidationPv(string $idValidation, string $nouveauNumeroEnseignant): bool
    {
        // La clé de validation_pv est composite, il faut une méthode de mise à jour adaptée
        // $this->validationPvModel->mettreAJourParIdentifiant($idValidation, ['numero_enseignant' => $nouveauNumeroEnseignant]);
        return false; // A implémenter avec la bonne logique de mise à jour
    }

    public function reassignerRapportConformite(string $idRapport, string $nouveauNumeroPersonnel): bool
    {
        // return $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_agent_conformite_assigne' => $nouveauNumeroPersonnel]);
        return false; // A implémenter avec la bonne logique de mise à jour
    }
}