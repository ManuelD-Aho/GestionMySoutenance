<?php
namespace App\Backend\Service\GestionAcademique;

interface ServiceGestionAcademiqueInterface
{
    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, float $montantInscription, string $idStatutPaiement, ?string $numeroRecuPaiement = null): bool;
    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool;
    public function listerInscriptionsAdministratives(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;
    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique, float $note): bool;
    public function enregistrerInformationsStage(string $numeroCarteEtudiant, string $idEntreprise, string $dateDebutStage, ?string $dateFinStage = null, ?string $sujetStage = null, ?string $nomTuteurEntreprise = null): bool;
    public function appliquerPenalite(string $numeroCarteEtudiant, string $idAnneeAcademique, string $type, ?float $montant, string $motif): string;
    public function regulariserPenalite(string $idPenalite, string $numeroPersonnelAdministratif): bool;
    public function estEtudiantEligibleSoumission(string $numeroCarteEtudiant, string $idAnneeAcademique): bool;
    public function lierGradeAEnseignant(string $idGrade, string $numeroEnseignant, string $dateAcquisition): bool;
    public function lierFonctionAEnseignant(string $idFonction, string $numeroEnseignant, string $dateDebutOccupation, ?string $dateFinOccupation = null): bool;
    public function lierSpecialiteAEnseignant(string $idSpecialite, string $numeroEnseignant): bool;
}