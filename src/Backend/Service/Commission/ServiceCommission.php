<?php

namespace App\Backend\Service\Commission;

use App\Backend\Model\Affecter;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\VoteCommission;
use PDO;

class ServiceCommission
{
    private Affecter $modeleAffecter;
    private VoteCommission $modeleVoteCommission;
    private CompteRendu $modeleCompteRendu;
    private ValidationPv $modeleValidationPv;
    private RapportEtudiant $modeleRapportEtudiant;
    private PvSessionRapport $modelePvSessionRapport;
    private PDO $db;

    public function __construct(
        Affecter $modeleAffecter,
        VoteCommission $modeleVoteCommission,
        CompteRendu $modeleCompteRendu,
        ValidationPv $modeleValidationPv,
        RapportEtudiant $modeleRapportEtudiant,
        PvSessionRapport $modelePvSessionRapport,
        PDO $db
    ) {
        $this->modeleAffecter = $modeleAffecter;
        $this->modeleVoteCommission = $modeleVoteCommission;
        $this->modeleCompteRendu = $modeleCompteRendu;
        $this->modeleValidationPv = $modeleValidationPv;
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->modelePvSessionRapport = $modelePvSessionRapport;
        $this->db = $db;
    }

    public function affecterEnseignantAJuryRapport(string $numeroEnseignant, int $idRapportEtudiant, int $idStatutJury, bool $estDirecteurMemoire = false): bool
    {
        $donneesAffectation = [
            'numero_enseignant' => $numeroEnseignant,
            'id_rapport_etudiant' => $idRapportEtudiant,
            'id_statut_jury' => $idStatutJury,
            'directeur_memoire' => $estDirecteurMemoire ? 1 : 0,
            'date_affectation' => date('Y-m-d H:i:s')
        ];
        return (bool)$this->modeleAffecter->creer($donneesAffectation);
    }

    public function enregistrerVotePourRapport(int $idRapportEtudiant, string $numeroEnseignant, int $idDecisionVote, ?string $commentaireVote, int $tourVote = 1): bool
    {
        $donneesVote = [
            'id_rapport_etudiant' => $idRapportEtudiant,
            'numero_enseignant' => $numeroEnseignant,
            'id_decision_vote' => $idDecisionVote,
            'commentaire_vote' => $commentaireVote,
            'date_vote' => date('Y-m-d H:i:s'),
            'tour_vote' => $tourVote
        ];
        return (bool)$this->modeleVoteCommission->creer($donneesVote);
    }

    public function finaliserDecisionCommissionPourRapport(int $idRapportEtudiant, int $idStatutRapportFinal, array $recommandations = []): bool
    {
        return $this->modeleRapportEtudiant->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $idStatutRapportFinal]);
    }

    public function redigerOuMettreAJourPv(string $idRedacteur, string $libellePv, string $typePv = 'Individuel', ?int $idRapportEtudiant = null, array $idsRapportsSession = [], ?int $idCompteRenduExistant = null): ?int
    {
        $this->db->beginTransaction();
        try {
            $donneesPv = [
                'id_redacteur' => $idRedacteur,
                'lib_compte_rendu' => $libellePv,
                'type_pv' => $typePv,
                'id_statut_pv' => 1, // 1: Brouillon
            ];

            if ($typePv === 'Individuel' && $idRapportEtudiant) {
                $donneesPv['id_rapport_etudiant'] = $idRapportEtudiant;
            }

            $idPvCreeOuModifie = null;

            if ($idCompteRenduExistant) {
                $donneesPv['date_modification_pv'] = date('Y-m-d H:i:s'); // Suppose a column for modification date
                if ($this->modeleCompteRendu->mettreAJourParIdentifiant($idCompteRenduExistant, $donneesPv)) {
                    $idPvCreeOuModifie = $idCompteRenduExistant;
                }
            } else {
                $donneesPv['date_creation_pv'] = date('Y-m-d H:i:s');
                $idResultat = $this->modeleCompteRendu->creer($donneesPv);
                if ($idResultat) {
                    $idPvCreeOuModifie = (int)$idResultat;
                }
            }

            if (!$idPvCreeOuModifie) {
                $this->db->rollBack();
                return null;
            }

            if ($typePv === 'Session' && !empty($idsRapportsSession)) {
                // First, remove existing associations for this PV if it's an update
                $this->modelePvSessionRapport->executerRequete("DELETE FROM pv_session_rapport WHERE id_compte_rendu = :id_pv", [':id_pv' => $idPvCreeOuModifie]);
                foreach ($idsRapportsSession as $idRapport) {
                    if (!$this->modelePvSessionRapport->creer(['id_compte_rendu' => $idPvCreeOuModifie, 'id_rapport_etudiant' => $idRapport])) {
                        $this->db->rollBack();
                        return null;
                    }
                }
            }
            $this->db->commit();
            return $idPvCreeOuModifie;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function soumettrePvPourValidation(int $idCompteRendu): bool
    {
        return $this->modeleCompteRendu->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 2]); // 2: Soumis Validation
    }

    public function validerOuRejeterPv(int $idCompteRendu, string $numeroEnseignantValidateur, int $idDecisionValidationPv, ?string $commentaireValidation): bool
    {
        $this->db->beginTransaction();
        try {
            $donneesValidation = [
                'id_compte_rendu' => $idCompteRendu,
                'numero_enseignant' => $numeroEnseignantValidateur,
                'id_decision_validation_pv' => $idDecisionValidationPv,
                'commentaire_validation_pv' => $commentaireValidation,
                'date_validation' => date('Y-m-d H:i:s')
            ];
            if (!$this->modeleValidationPv->creer($donneesValidation)) {
                $this->db->rollBack();
                return false;
            }

            if ($idDecisionValidationPv == 1) { // 1: Approuvé
                if (!$this->modeleCompteRendu->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 3])) { // 3: Validé
                    $this->db->rollBack();
                    return false;
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}