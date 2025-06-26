<?php

namespace App\Backend\Service\Reclamation;

interface ServiceReclamationInterface
{
    /**
     * Soumet une nouvelle réclamation par un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant soumettant la réclamation.
     * @param string $sujetReclamation Le sujet de la réclamation.
     * @param string $descriptionReclamation La description détaillée de la réclamation.
     * @return string L'ID de la réclamation créée.
     * @throws \Exception En cas d'erreur.
     */
    public function soumettreReclamation(string $numeroCarteEtudiant, string $sujetReclamation, string $descriptionReclamation): string;

    /**
     * Récupère les détails d'une réclamation spécifique par son ID.
     * @param string $idReclamation L'ID de la réclamation.
     * @return array|null Les détails de la réclamation ou null si non trouvée.
     */
    public function getDetailsReclamation(string $idReclamation): ?array;

    /**
     * Récupère toutes les réclamations pour un étudiant donné.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @return array Liste des réclamations de l'étudiant.
     */
    public function recupererReclamationsEtudiant(string $numeroCarteEtudiant): array;

    /**
     * Récupère toutes les réclamations du système, avec filtres et pagination (pour le personnel).
     * @param array $criteres Critères de recherche.
     * @param int $page Numéro de page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Liste des réclamations.
     */
    public function recupererToutesReclamations(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;

    /**
     * Traite une réclamation par un membre du personnel administratif.
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $numeroPersonnelTraitant Le numéro du personnel qui traite la réclamation.
     * @param string $newStatut L'ID du nouveau statut de la réclamation.
     * @param string|null $reponseReclamation La réponse textuelle à la réclamation.
     * @return bool Vrai si le traitement a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function traiterReclamation(string $idReclamation, string $numeroPersonnelTraitant, string $newStatut, ?string $reponseReclamation): bool;
}