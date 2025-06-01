<?php

namespace App\Backend\Service\SupervisionAdmin;

use DateTime;
use PDO; // Si des méthodes retournent directement des résultats PDOStatement

interface ServiceSupervisionAdminInterface
{
    // Ajoutez ici les signatures de toutes les méthodes publiques de ServiceSupervisionAdmin
    // que d'autres parties de votre application pourraient utiliser.
    // Par exemple, si ServicePermissions appelle une méthode spécifique :
    public function enregistrerAction(
        string $loginUtilisateur,
        string $codeAction,
        DateTime $dateAction,
        string $adresseIp,
        string $userAgent,
        string $contexteEntite,
        ?string $idEntite,
        ?array $details
    ): bool; // Ou void, ou int, selon ce que la méthode fait/retourne

    public function obtenirStatistiquesGlobalesRapports(): array;
    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limite = 50, int $page = 1): array;
    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limite = 50, int $page = 1): array;
    public function listerPvEligiblesArchivage(array $criteres = []): array;

    // Ajoutez TOUTES les autres méthodes publiques de ServiceSupervisionAdmin
}