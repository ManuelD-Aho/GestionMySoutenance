<?php

namespace App\Backend\Service\Rapport;

interface ServiceRapportInterface
{
    /**
     * Crée ou met à jour un brouillon de rapport étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $metadonnees Metadonnées du rapport.
     * @param array $sectionsContenu Tableau associatif des sections du rapport.
     * @return string L'ID du rapport créé ou mis à jour.
     * @throws \Exception En cas d'erreur.
     */
    public function creerOuMettreAJourBrouillonRapport(string $numeroCarteEtudiant, array $metadonnees, array $sectionsContenu): string;

    /**
     * Soumet un rapport (qui était en brouillon ou en corrections) pour la vérification de conformité.
     * @param string $idRapportEtudiant L'ID du rapport à soumettre.
     * @return bool Vrai si la soumission a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function soumettreRapportPourVerification(string $idRapportEtudiant): bool;

    /**
     * Enregistre les corrections soumises par un étudiant.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param array $sectionsContenuCorriges Tableau associatif des sections corrigées.
     * @param string $numeroUtilisateurUpload L'ID de l'utilisateur (étudiant) qui soumet les corrections.
     * @param string|null $noteExplicative Une note explicative des corrections.
     * @return bool Vrai si les corrections sont enregistrées.
     * @throws \Exception En cas d'erreur.
     */
    public function enregistrerCorrectionsSoumises(string $idRapportEtudiant, array $sectionsContenuCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative = null): bool;

    /**
     * Récupère toutes les informations complètes d'un rapport étudiant.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @return array|null Les données complètes du rapport ou null si non trouvé.
     */
    public function recupererInformationsRapportComplet(string $idRapportEtudiant): ?array;

    /**
     * Met à jour le statut d'un rapport étudiant.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $newStatutId Le nouvel ID de statut.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function mettreAJourStatutRapport(string $idRapportEtudiant, string $newStatutId): bool;

    /**
     * Permet de réactiver l'édition d'un rapport pour des corrections ou une reprise.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $motifActivation Un motif expliquant la réactivation.
     * @return bool Vrai si l'édition est réactivée.
     * @throws \Exception En cas d'erreur.
     */
    public function reactiverEditionRapport(string $idRapportEtudiant, string $motifActivation = 'Reprise demandée'): bool;

    /**
     * Liste des rapports étudiants en fonction de critères.
     * @param array $criteres Critères de filtre.
     * @param array $colonnes Les colonnes à sélectionner.
     * @param string $operateurLogique L'opérateur logique entre les critères ('AND' ou 'OR').
     * @param string|null $orderBy Colonne pour le tri.
     * @param int|null $limit Limite de résultats.
     * @param int|null $offset Offset pour la pagination.
     * @return array Liste des rapports trouvés.
     */
    public function listerRapportsParCriteres(array $criteres = [], array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}