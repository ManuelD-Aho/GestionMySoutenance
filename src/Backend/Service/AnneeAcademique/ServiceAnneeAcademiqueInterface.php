<?php

namespace App\Backend\Service\AnneeAcademique;

interface ServiceAnneeAcademiqueInterface
{
    /**
     * Définit l'année académique active.
     * @param string $idAnneeAcademique L'ID de l'année académique à activer.
     * @return bool Vrai si l'année a été activée.
     * @throws \Exception En cas d'erreur.
     */
    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool;

    /**
     * Crée une nouvelle année académique.
     * @param string $idAnneeAcademique L'ID unique de l'année académique.
     * @param string $libelle Le libellé de l'année académique.
     * @param string $dateDebut La date de début (YYYY-MM-DD).
     * @param string $dateFin La date de fin (YYYY-MM-DD).
     * @param bool $estActive Indique si cette année est active.
     * @return bool Vrai si la création a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function creerAnneeAcademique(string $idAnneeAcademique, string $libelle, string $dateDebut, string $dateFin, bool $estActive): bool;

    /**
     * Modifie une année académique existante.
     * @param string $idAnneeAcademique L'ID de l'année académique à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierAnneeAcademique(string $idAnneeAcademique, array $donnees): bool;

    /**
     * Supprime une année académique.
     * @param string $idAnneeAcademique L'ID de l'année académique à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool;

    /**
     * Récupère l'année académique actuellement active.
     * @return array|null Les données de l'année académique active ou null.
     */
    public function recupererAnneeAcademiqueActive(): ?array;

    /**
     * Liste toutes les années académiques.
     * @return array
     */
    public function listerAnneesAcademiques(): array;

    /**
     * Définit les règles de transition des cohortes.
     * @param array $regles Tableau des règles (ex: ['M2' => 1] pour 1 an de délai pour le Master 2).
     * @return bool Vrai si les règles ont été sauvegardées.
     */
    public function definirReglesTransitionCohortes(array $regles): bool;

    /**
     * Vérifie si un étudiant est éligible pour une année donnée, en tenant compte des règles de cohorte.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return bool Vrai si l'étudiant est éligible.
     */
    public function verifierEligibiliteEtudiantPourAnnee(string $numeroEtudiant, string $idAnneeAcademique): bool;
}