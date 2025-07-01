<?php
// src/Backend/Service/Supervision/ServiceSupervisionInterface.php

namespace App\Backend\Service\Supervision;

interface ServiceSupervisionInterface
{
    // --- Audit & Piste ---
    public function enregistrerAction(string $numeroUtilisateur, string $idAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool;
    public function pisterAcces(string $numeroUtilisateur, string $idTraitement): bool;
    public function consulterJournaux(array $filtres = [], int $limit = 50, int $offset = 0): array;
    public function reconstituerHistoriqueEntite(string $idEntite): array;

    // --- Maintenance & Supervision Technique ---
    public function purgerAnciensJournaux(string $dateLimite): int;
    public function consulterJournauxErreurs(string $logFilePath): string;
    public function listerTachesAsynchrones(array $filtres = []): array;
    public function gererTacheAsynchrone(string $idTache, string $action): bool;

    // --- Reporting ---
    public function genererStatistiquesDashboardAdmin(): array;
}