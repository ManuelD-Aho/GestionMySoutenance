<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface RapportServiceInterface
{
    /**
     * Crée un nouveau rapport à l'état de brouillon.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $metadonnees Métadonnées initiales (titre, thème, etc.).
     * @return string L'ID du nouveau rapport.
     */
    public function creerBrouillon(string $numeroEtudiant, array $metadonnees): string;

    /**
     * Met à jour le contenu d'une section du rapport.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $titreSection Le titre de la section (ex: 'Introduction').
     * @param string $contenu Le contenu textuel de la section.
     * @return bool True en cas de succès.
     */
    public function mettreAJourSection(string $idRapport, string $titreSection, string $contenu): bool;

    /**
     * Soumet un rapport pour vérification. Change son statut et le verrouille.
     *
     * @param string $idRapport L'ID du rapport.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si le rapport n'est pas à l'état de brouillon.
     */
    public function soumettrePourVerification(string $idRapport): bool;

    /**
     * Renvoie un rapport à l'étudiant pour corrections (après non-conformité ou demande de la commission).
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $motif Le motif du retour.
     * @return bool True en cas de succès.
     */
    public function retournerPourCorrection(string $idRapport, string $motif): bool;

    /**
     * Soumet à nouveau un rapport après y avoir apporté des corrections.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $noteExplicative Une note expliquant les corrections apportées.
     * @return bool True en cas de succès.
     */
    public function resoumettreApresCorrection(string $idRapport, string $noteExplicative): bool;

    /**
     * Récupère l'historique des changements de statut d'un rapport.
     *
     * @param string $idRapport L'ID du rapport.
     * @return array L'historique des statuts.
     */
    public function getHistoriqueStatuts(string $idRapport): array;

    /**
     * Récupère toutes les données d'un rapport (métadonnées, sections, statuts, etc.).
     *
     * @param string $idRapport L'ID du rapport.
     * @return array|null Les données complètes du rapport ou null.
     */
    public function recupererRapportComplet(string $idRapport): ?array;

    /**
     * Archive un rapport une fois son cycle de vie terminé (validé et noté).
     *
     * @param string $idRapport L'ID du rapport.
     * @return bool True en cas de succès.
     */
    public function archiverRapport(string $idRapport): bool;

    /**
     * Liste les rapports selon des critères de filtrage.
     *
     * @param array $filtres Critères (ex: statut, étudiant, année académique).
     * @return array La liste des rapports.
     */
    public function listerRapports(array $filtres = []): array;

    /**
     * Assigne un directeur de mémoire au rapport.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $idEnseignant L'ID de l'enseignant.
     * @return bool True en cas de succès.
     */
    public function assignerDirecteurMemoire(string $idRapport, string $idEnseignant): bool;
}