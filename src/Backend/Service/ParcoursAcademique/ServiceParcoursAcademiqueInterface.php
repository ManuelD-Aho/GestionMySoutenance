<?php

namespace App\Backend\Service\ParcoursAcademique;

interface ServiceParcoursAcademiqueInterface
{
    /**
     * Inscrit un étudiant pour une année académique.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $donneesInscription Les données d'inscription.
     * @return string L'ID de l'inscription créée.
     */
    public function inscrireEtudiant(string $numeroEtudiant, string $idAnneeAcademique, array $donneesInscription): string;

    /**
     * Met à jour le parcours d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $donneesModification Les données à modifier.
     * @return bool Vrai si la modification a réussi.
     */
    public function mettreAJourParcours(string $numeroEtudiant, array $donneesModification): bool;

    /**
     * Récupère le parcours complet d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return array Le parcours de l'étudiant.
     */
    public function obtenirParcoursComplet(string $numeroEtudiant): array;

    /**
     * Enregistre les résultats d'évaluation d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param array $donneesEvaluation Les données d'évaluation.
     * @return bool Vrai si l'enregistrement a réussi.
     */
    public function enregistrerEvaluation(string $numeroEtudiant, string $idEcue, array $donneesEvaluation): bool;

    /**
     * Enregistre les informations de stage d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $donneesStage Les données du stage.
     * @return string L'ID du stage créé.
     */
    public function enregistrerStage(string $numeroEtudiant, array $donneesStage): string;

    /**
     * Gère l'acquisition de compétences par un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idCompetence L'ID de la compétence.
     * @param array $donneesAcquisition Les données d'acquisition.
     * @return bool Vrai si l'acquisition a été enregistrée.
     */
    public function gererAcquisitionCompetence(string $numeroEtudiant, string $idCompetence, array $donneesAcquisition): bool;

    /**
     * Calcule la progression académique d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string|null $idAnneeAcademique L'année académique (optionnel).
     * @return array Les statistiques de progression.
     */
    public function calculerProgression(string $numeroEtudiant, ?string $idAnneeAcademique = null): array;

    /**
     * Vérifie l'éligibilité d'un étudiant pour une soutenance.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return array Le résultat de la vérification.
     */
    public function verifierEligibiliteSoutenance(string $numeroEtudiant, string $idAnneeAcademique): array;

    /**
     * Génère le relevé de notes d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return array Les données du relevé.
     */
    public function genererReleveNotes(string $numeroEtudiant, string $idAnneeAcademique): array;

    /**
     * Applique des pénalités à un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $donneesPenalite Les données de la pénalité.
     * @return string L'ID de la pénalité créée.
     */
    public function appliquerPenalite(string $numeroEtudiant, array $donneesPenalite): string;

    /**
     * Liste les pénalités d'un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param bool $uniquementActives Filtrer uniquement les pénalités actives.
     * @return array La liste des pénalités.
     */
    public function listerPenalites(string $numeroEtudiant, bool $uniquementActives = false): array;

    /**
     * Valide le passage d'un étudiant au niveau supérieur.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idDecisionPassage L'ID de la décision de passage.
     * @param array $donneesValidation Les données de validation.
     * @return bool Vrai si la validation a réussi.
     */
    public function validerPassageNiveau(string $numeroEtudiant, string $idDecisionPassage, array $donneesValidation): bool;
}