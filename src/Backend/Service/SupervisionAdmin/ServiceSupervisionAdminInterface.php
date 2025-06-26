<?php

namespace App\Backend\Service\SupervisionAdmin;

interface ServiceSupervisionAdminInterface
{
    /**
     * Enregistre une action système dans le journal d'audit.
     * @param string $numeroUtilisateur L'ID de l'utilisateur qui a effectué l'action.
     * @param string $libelleAction Le code ou libellé de l'action.
     * @param string $detailsAction Description détaillée de l'action.
     * @param string|null $idEntiteConcernee L'ID de l'entité principale concernée.
     * @param string|null $typeEntiteConcernee Le type de l'entité concernée.
     * @param array $detailsJson Données supplémentaires à stocker en JSON.
     * @return bool Vrai si l'action a été enregistrée.
     */
    public function enregistrerAction(string $numeroUtilisateur, string $libelleAction, string $detailsAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool;

    /**
     * Récupère ou crée l'ID d'une action.
     * @param string $libelleAction Le libellé de l'action.
     * @return string L'ID de l'action.
     */
    public function recupererOuCreerIdActionParLibelle(string $libelleAction): string;

    /**
     * Récupère les statistiques globales des rapports (nombre total, par statut).
     * @return array Statistiques agrégées.
     */
    public function obtenirStatistiquesGlobalesRapports(): array;

    /**
     * Consulte les journaux des actions utilisateurs.
     * @param array $filtres Critères de filtrage.
     * @param int $limit Limite de résultats.
     * @param int $offset Offset pour la pagination.
     * @return array Liste des actions journalisées.
     */
    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limit = 50, int $offset = 0): array;

    /**
     * Consulte les traces d'accès aux fonctionnalités (via la table `pister`).
     * @param array $filtres Critères de filtrage.
     * @param int $limit Limite de résultats.
     * @param int $offset Offset pour la pagination.
     * @return array Liste des traces d'accès.
     */
    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limit = 50, int $offset = 0): array;

    /**
     * Liste les PV éligibles à l'archivage.
     * @param int $anneesAnciennete Le nombre d'années après lequel un PV est éligible à l'archivage.
     * @return array Liste des PV éligibles.
     */
    public function listerPvEligiblesArchivage(int $anneesAnciennete = 1): array;

    /**
     * Implémente la logique d'archivage d'un PV.
     * @param string $idCompteRendu L'ID du PV à archiver.
     * @return bool Vrai si l'archivage a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function archiverPv(string $idCompteRendu): bool;
}