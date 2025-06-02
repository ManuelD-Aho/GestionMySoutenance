<?php

namespace App\Backend\Service\GestionAcademique;

// Il n'est généralement pas nécessaire d'importer les modèles ici,
// mais vous pourriez vouloir ajouter des PHPDoc pour clarifier les types de retour si des objets complexes sont retournés.

interface ServiceGestionAcademiqueInterface
{
    /**
     * Crée une nouvelle inscription administrative pour un étudiant.
     *
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param int $idNiveauEtude L'ID du niveau d'étude.
     * @param int $idAnneeAcademique L'ID de l'année académique.
     * @param float $montantInscription Le montant de l'inscription.
     * @param string $dateInscription La date de l'inscription (format YYYY-MM-DD).
     * @param int $idStatutPaiement L'ID du statut du paiement.
     * @param string|null $datePaiement La date du paiement (format YYYY-MM-DD), optionnelle.
     * @param string|null $numeroRecuPaiement Le numéro du reçu de paiement, optionnel.
     * @param int|null $idDecisionPassage L'ID de la décision de passage, optionnel.
     * @return array|null Les données de l'inscription créée (ou une représentation) ou null en cas d'échec.
     */
    public function creerInscriptionAdministrative(
        string $numeroCarteEtudiant,
        int $idNiveauEtude,
        int $idAnneeAcademique,
        float $montantInscription,
        string $dateInscription,
        int $idStatutPaiement,
        ?string $datePaiement,
        ?string $numeroRecuPaiement,
        ?int $idDecisionPassage
    ): ?array;

    /**
     * Met à jour une inscription administrative existante.
     *
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param int $idNiveauEtude L'ID du niveau d'étude.
     * @param int $idAnneeAcademique L'ID de l'année académique.
     * @param array $donneesAMettreAJour Les données à mettre à jour.
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function mettreAJourInscriptionAdministrative(
        string $numeroCarteEtudiant,
        int $idNiveauEtude,
        int $idAnneeAcademique,
        array $donneesAMettreAJour
    ): bool;

    /**
     * Enregistre la note d'un ECUE pour un étudiant.
     *
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $numeroEnseignantEvaluateur Le numéro de l'enseignant évaluateur.
     * @param int $idEcue L'ID de l'ECUE.
     * @param float $note La note obtenue.
     * @param string $dateEvaluation La date de l'évaluation (format YYYY-MM-DD).
     * @return bool True si l'enregistrement a réussi, false sinon.
     */
    public function enregistrerNoteEcue(
        string $numeroCarteEtudiant,
        string $numeroEnseignantEvaluateur,
        int $idEcue,
        float $note,
        string $dateEvaluation
    ): bool;

    /**
     * Enregistre les informations d'un stage pour un étudiant.
     *
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param int $idEntreprise L'ID de l'entreprise d'accueil.
     * @param string $dateDebutStage La date de début du stage (format YYYY-MM-DD).
     * @param string|null $dateFinStage La date de fin du stage (format YYYY-MM-DD), optionnelle.
     * @param string|null $sujetStage Le sujet du stage, optionnel.
     * @param string|null $nomTuteurEntreprise Le nom du tuteur en entreprise, optionnel.
     * @return bool True si l'enregistrement a réussi, false sinon.
     */
    public function enregistrerInformationsStage(
        string $numeroCarteEtudiant,
        int $idEntreprise,
        string $dateDebutStage,
        ?string $dateFinStage,
        ?string $sujetStage,
        ?string $nomTuteurEntreprise
    ): bool;

    /**
     * Lie un grade à un enseignant.
     *
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param int $idGrade L'ID du grade.
     * @param string $dateAcquisition La date d'acquisition du grade (format YYYY-MM-DD).
     * @return bool True si la liaison a réussi, false sinon.
     */
    public function lierGradeAEnseignant(
        string $numeroEnseignant,
        int $idGrade,
        string $dateAcquisition
    ): bool;

    /**
     * Lie une fonction à un enseignant.
     *
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param int $idFonction L'ID de la fonction.
     * @param string $dateDebutOccupation La date de début d'occupation de la fonction (format YYYY-MM-DD).
     * @param string|null $dateFinOccupation La date de fin d'occupation (format YYYY-MM-DD), optionnelle.
     * @return bool True si la liaison a réussi, false sinon.
     */
    public function lierFonctionAEnseignant(
        string $numeroEnseignant,
        int $idFonction,
        string $dateDebutOccupation,
        ?string $dateFinOccupation
    ): bool;

    /**
     * Lie une spécialité à un enseignant.
     *
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param int $idSpecialite L'ID de la spécialité.
     * @return bool True si la liaison a réussi, false sinon.
     */
    public function lierSpecialiteAEnseignant(
        string $numeroEnseignant,
        int $idSpecialite
    ): bool;

    // Ajoutez ici d'autres signatures de méthodes si votre service est censé en avoir plus.
}
