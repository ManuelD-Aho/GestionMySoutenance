<?php
namespace App\Backend\Service\GestionAcademique;

interface ServiceGestionAcademiqueInterface
{
    /**
     * Crée une nouvelle inscription administrative pour un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param float $montantInscription Le montant des frais d'inscription.
     * @param string $idStatutPaiement Le statut initial du paiement.
     * @param string|null $numeroRecuPaiement Le numéro du reçu de paiement si payé.
     * @return bool Vrai si l'inscription a été créée avec succès.
     * @throws \Exception En cas d'erreur.
     */
    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, float $montantInscription, string $idStatutPaiement, ?string $numeroRecuPaiement = null): bool;

    /**
     * Met à jour une inscription administrative existante.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour réussit.
     * @throws \Exception En cas d'erreur.
     */
    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool;

    /**
     * Liste les inscriptions administratives, avec filtres et pagination.
     * @param array $criteres Critères de filtre.
     * @param int $page Numéro de page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Liste des inscriptions.
     */
    public function listerInscriptionsAdministratives(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;

    /**
     * Enregistre ou met à jour la note d'un étudiant pour un ECUE.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param float $note La note obtenue.
     * @return bool Vrai si l'opération a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $idEcue, float $note): bool;

    /**
     * Enregistre ou met à jour les informations d'un stage pour un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEntreprise L'ID de l'entreprise.
     * @param string $dateDebutStage Date de début du stage (YYYY-MM-DD).
     * @param string|null $dateFinStage Date de fin du stage (YYYY-MM-DD).
     * @param string|null $sujetStage Sujet du stage.
     * @param string|null $nomTuteurEntreprise Nom du tuteur en entreprise.
     * @return bool Vrai si l'opération a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function enregistrerInformationsStage(string $numeroCarteEtudiant, string $idEntreprise, string $dateDebutStage, ?string $dateFinStage = null, ?string $sujetStage = null, ?string $nomTuteurEntreprise = null): bool;

    /**
     * Applique une pénalité à un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param float $montantPenalite Le montant de la pénalité.
     * @param string $motif Le motif de la pénalité.
     * @return string L'ID de la pénalité créée.
     * @throws \Exception En cas d'erreur.
     */
    public function appliquerPenalite(string $numeroCarteEtudiant, float $montantPenalite, string $motif): string;

    /**
     * Régularise une pénalité pour un étudiant.
     * @param string $idPenalite L'ID de la pénalité à régulariser.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel qui régularise.
     * @return bool Vrai si la pénalité a été régularisée.
     * @throws \Exception En cas d'erreur.
     */
    public function regulariserPenalite(string $idPenalite, string $numeroPersonnelAdministratif): bool;

    /**
     * Vérifie si un étudiant est éligible à la soumission d'un rapport.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique actuelle.
     * @return bool Vrai si l'étudiant est éligible, faux sinon.
     */
    public function estEtudiantEligibleSoumission(string $numeroCarteEtudiant, string $idAnneeAcademique): bool;

    /**
     * Lie un grade à un enseignant (historise l'acquisition d'un grade).
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string $dateAcquisition Date d'acquisition (YYYY-MM-DD).
     * @return bool Vrai si l'opération a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function lierGradeAEnseignant(string $idGrade, string $numeroEnseignant, string $dateAcquisition): bool;

    /**
     * Lie une fonction à un enseignant (historise l'occupation d'une fonction).
     * @param string $idFonction L'ID de la fonction.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string $dateDebutOccupation Date de début de l'occupation (YYYY-MM-DD).
     * @param string|null $dateFinOccupation Date de fin de l'occupation (YYYY-MM-DD).
     * @return bool Vrai si l'opération a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function lierFonctionAEnseignant(string $idFonction, string $numeroEnseignant, string $dateDebutOccupation, ?string $dateFinOccupation = null): bool;

    /**
     * Lie une spécialité à un enseignant.
     * @param string $idSpecialite L'ID de la spécialité.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @return bool Vrai si l'opération a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function lierSpecialiteAEnseignant(string $idSpecialite, string $numeroEnseignant): bool;
}