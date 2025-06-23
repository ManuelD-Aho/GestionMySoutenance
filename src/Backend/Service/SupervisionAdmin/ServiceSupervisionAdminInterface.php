<?php
namespace App\Backend\Service\SupervisionAdmin;

interface ServiceSupervisionAdminInterface
{
    public function enregistrerAction(string $numeroUtilisateur, string $idAction, string $detailsAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool;
    public function enregistrerPisteAcces(string $numeroUtilisateur, string $idTraitement): bool;
    public function obtenirStatistiquesGlobalesRapports(): array;
    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limit = 50, int $offset = 0): array;
    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limit = 50, int $offset = 0): array;
    public function listerPvEligiblesArchivage(int $anneesAnciennete = 1): array;
    public function archiverPv(string $idCompteRendu): bool;
}