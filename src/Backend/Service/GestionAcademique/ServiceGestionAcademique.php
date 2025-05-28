<?php

namespace App\Backend\Service\GestionAcademique;

use App\Backend\Model\Acquerir;
use App\Backend\Model\Attribuer;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Occuper;

class ServiceGestionAcademique
{
    private Inscrire $modeleInscrire;
    private Evaluer $modeleEvaluer;
    private FaireStage $modeleFaireStage;
    private Acquerir $modeleAcquerir;
    private Occuper $modeleOccuper;
    private Attribuer $modeleAttribuer;

    public function __construct(
        Inscrire $modeleInscrire,
        Evaluer $modeleEvaluer,
        FaireStage $modeleFaireStage,
        Acquerir $modeleAcquerir,
        Occuper $modeleOccuper,
        Attribuer $modeleAttribuer
    ) {
        $this->modeleInscrire = $modeleInscrire;
        $this->modeleEvaluer = $modeleEvaluer;
        $this->modeleFaireStage = $modeleFaireStage;
        $this->modeleAcquerir = $modeleAcquerir;
        $this->modeleOccuper = $modeleOccuper;
        $this->modeleAttribuer = $modeleAttribuer;
    }

    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, float $montantInscription, string $dateInscription, int $idStatutPaiement, ?string $datePaiement, ?string $numeroRecuPaiement, ?int $idDecisionPassage): ?array
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique,
            'montant_inscription' => $montantInscription,
            'date_inscription' => $dateInscription,
            'id_statut_paiement' => $idStatutPaiement,
            'date_paiement' => $datePaiement,
            'numero_recu_paiement' => $numeroRecuPaiement,
            'id_decision_passage' => $idDecisionPassage
        ];
        $resultat = $this->modeleInscrire->creer($donnees);
        return $resultat ? $donnees : null;
    }

    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, array $donneesAMettreAJour): bool
    {
        return $this->modeleInscrire->mettreAJourInscriptionParCles($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique, $donneesAMettreAJour);
    }

    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $numeroEnseignantEvaluateur, int $idEcue, float $note, string $dateEvaluation): bool
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'numero_enseignant' => $numeroEnseignantEvaluateur,
            'id_ecue' => $idEcue,
            'note' => $note,
            'date_evaluation' => $dateEvaluation
        ];
        return (bool)$this->modeleEvaluer->creer($donnees);
    }

    public function enregistrerInformationsStage(string $numeroCarteEtudiant, int $idEntreprise, string $dateDebutStage, ?string $dateFinStage, ?string $sujetStage, ?string $nomTuteurEntreprise): bool
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_entreprise' => $idEntreprise,
            'date_debut_stage' => $dateDebutStage,
            'date_fin_stage' => $dateFinStage,
            'sujet_stage' => $sujetStage,
            'nom_tuteur_entreprise' => $nomTuteurEntreprise
        ];
        return (bool)$this->modeleFaireStage->creer($donnees);
    }

    public function lierGradeAEnseignant(string $numeroEnseignant, int $idGrade, string $dateAcquisition): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_grade' => $idGrade,
            'date_acquisition' => $dateAcquisition
        ];
        return (bool)$this->modeleAcquerir->creer($donnees);
    }

    public function lierFonctionAEnseignant(string $numeroEnseignant, int $idFonction, string $dateDebutOccupation, ?string $dateFinOccupation): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_fonction' => $idFonction,
            'date_debut_occupation' => $dateDebutOccupation,
            'date_fin_occupation' => $dateFinOccupation
        ];
        return (bool)$this->modeleOccuper->creer($donnees);
    }

    public function lierSpecialiteAEnseignant(string $numeroEnseignant, int $idSpecialite): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_specialite' => $idSpecialite
        ];
        return (bool)$this->modeleAttribuer->creer($donnees);
    }

}