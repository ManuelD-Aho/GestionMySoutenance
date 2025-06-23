<?php
namespace App\Backend\Service\GestionAcademique;

use PDO;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Penalite;
use App\Backend\Model\Acquerir;
use App\Backend\Model\Occuper;
use App\Backend\Model\Attribuer;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;

class ServiceGestionAcademique implements ServiceGestionAcademiqueInterface
{
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private FaireStage $faireStageModel;
    private Penalite $penaliteModel;
    private Acquerir $acquerirModel;
    private Occuper $occuperModel;
    private Attribuer $attribuerModel;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->inscrireModel = new Inscrire($db);
        $this->evaluerModel = new Evaluer($db);
        $this->faireStageModel = new FaireStage($db);
        $this->penaliteModel = new Penalite($db);
        $this->acquerirModel = new Acquerir($db);
        $this->occuperModel = new Occuper($db);
        $this->attribuerModel = new Attribuer($db);
        $this->idGenerator = $idGenerator;
    }

    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, float $montantInscription, string $idStatutPaiement, ?string $numeroRecuPaiement = null): bool
    {
        return (bool) $this->inscrireModel->creer(compact('numeroCarteEtudiant', 'idNiveauEtude', 'idAnneeAcademique', 'montantInscription', 'idStatutPaiement', 'numeroRecuPaiement') + ['date_inscription' => date('Y-m-d H:i:s')]);
    }

    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool
    {
        return $this->inscrireModel->mettreAJourParClesInternes(compact('numeroCarteEtudiant', 'idNiveauEtude', 'idAnneeAcademique'), $donnees);
    }

    public function listerInscriptionsAdministratives(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        return $this->inscrireModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
    }

    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique, float $note): bool
    {
        return (bool) $this->evaluerModel->creer(compact('numeroCarteEtudiant', 'idEcue', 'idAnneeAcademique', 'note') + ['date_evaluation' => date('Y-m-d H:i:s')]);
    }

    public function enregistrerInformationsStage(string $numeroCarteEtudiant, string $idEntreprise, string $dateDebutStage, ?string $dateFinStage = null, ?string $sujetStage = null, ?string $nomTuteurEntreprise = null): bool
    {
        return (bool) $this->faireStageModel->creer(compact('numeroCarteEtudiant', 'idEntreprise', 'dateDebutStage', 'dateFinStage', 'sujetStage', 'nomTuteurEntreprise'));
    }

    public function appliquerPenalite(string $numeroCarteEtudiant, string $idAnneeAcademique, string $type, ?float $montant, string $motif): string
    {
        $idPenalite = $this->idGenerator->generate('penalite');
        $this->penaliteModel->creer([
            'id_penalite' => $idPenalite,
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_annee_academique' => $idAnneeAcademique,
            'type_penalite' => $type,
            'montant_du' => $montant,
            'motif' => $motif,
            'id_statut_penalite' => 'PEN_DUE'
        ]);
        return $idPenalite;
    }

    public function regulariserPenalite(string $idPenalite, string $numeroPersonnelAdministratif): bool
    {
        return $this->penaliteModel->mettreAJourParIdentifiant($idPenalite, [
            'id_statut_penalite' => 'PEN_REGLEE',
            'date_regularisation' => date('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $numeroPersonnelAdministratif
        ]);
    }

    public function estEtudiantEligibleSoumission(string $numeroCarteEtudiant, string $idAnneeAcademique): bool
    {
        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_annee_academique' => $idAnneeAcademique]);
        if (!$inscription || $inscription['id_statut_paiement'] !== 'PAIE_OK') return false;

        $stage = $this->faireStageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant]);
        if (!$stage) return false;

        $penalite = $this->penaliteModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_statut_penalite' => 'PEN_DUE']);
        if ($penalite) return false;

        return true;
    }

    public function lierGradeAEnseignant(string $idGrade, string $numeroEnseignant, string $dateAcquisition): bool
    {
        return (bool) $this->acquerirModel->creer(compact('idGrade', 'numeroEnseignant', 'dateAcquisition'));
    }

    public function lierFonctionAEnseignant(string $idFonction, string $numeroEnseignant, string $dateDebutOccupation, ?string $dateFinOccupation = null): bool
    {
        return (bool) $this->occuperModel->creer(compact('idFonction', 'numeroEnseignant', 'dateDebutOccupation', 'dateFinOccupation'));
    }

    public function lierSpecialiteAEnseignant(string $idSpecialite, string $numeroEnseignant): bool
    {
        return (bool) $this->attribuerModel->creer(compact('idSpecialite', 'numeroEnseignant'));
    }
}