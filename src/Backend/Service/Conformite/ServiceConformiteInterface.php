<?php
namespace App\Backend\Service\Conformite;

interface ServiceConformiteInterface
{
    public function traiterVerificationConformite(string $idRapportEtudiant, string $numeroPersonnelAdministratif, string $idStatutConformite, ?string $commentaireConformite): bool;
    public function recupererRapportsEnAttenteDeVerification(): array;
    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array;
}