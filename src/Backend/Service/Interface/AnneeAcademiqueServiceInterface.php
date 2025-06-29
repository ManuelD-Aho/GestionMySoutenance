<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface AnneeAcademiqueServiceInterface
{
    /**
     * Crée une nouvelle année académique.
     *
     * @param array $donnees Données de l'année académique (libelle, date_debut, date_fin, est_active).
     * @return string L'ID de la nouvelle année académique.
     * @throws DoublonException Si une année avec le même libellé existe déjà.
     */
    public function creerAnneeAcademique(array $donnees): string;

    /**
     * Met à jour les informations d'une année académique existante.
     *
     * @param string $idAnnee L'ID de l'année à mettre à jour.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'année n'existe pas.
     */
    public function mettreAJourAnneeAcademique(string $idAnnee, array $donnees): bool;

    /**
     * Supprime une année académique, si elle n'a pas de dépendances.
     *
     * @param string $idAnnee L'ID de l'année à supprimer.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'année n'existe pas.
     * @throws OperationImpossibleException Si l'année a des inscriptions ou des rapports liés.
     */
    public function supprimerAnneeAcademique(string $idAnnee): bool;

    /**
     * Récupère les détails d'une année académique par son ID.
     *
     * @param string $idAnnee L'ID de l'année.
     * @return array|null Les données de l'année ou null si non trouvée.
     */
    public function recupererAnneeAcademiqueParId(string $idAnnee): ?array;

    /**
     * Liste toutes les années académiques, avec des filtres optionnels.
     *
     * @param array $filtres Critères de filtrage.
     * @return array La liste des années académiques.
     */
    public function listerAnneesAcademiques(array $filtres = []): array;

    /**
     * Définit une année académique comme étant l'année active.
     *
     * @param string $idAnnee L'ID de l'année à activer.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'année n'existe pas.
     */
    public function definirAnneeAcademiqueActive(string $idAnnee): bool;

    /**
     * Récupère l'année académique actuellement active.
     *
     * @return array|null Les données de l'année active ou null si aucune n'est active.
     */
    public function getAnneeAcademiqueActive(): ?array;

    /**
     * Archive une année académique passée.
     *
     * @param string $idAnnee L'ID de l'année à archiver.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'année n'existe pas.
     * @throws OperationImpossibleException Si l'année est toujours active.
     */
    public function archiverAnneeAcademique(string $idAnnee): bool;
}