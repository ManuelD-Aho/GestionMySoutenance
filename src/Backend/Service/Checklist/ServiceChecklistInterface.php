<?php
namespace App\Backend\Service\Checklist;

interface ServiceChecklistInterface
{
    public function creerCritere(array $data): bool;
    public function modifierCritere(string $idCritere, array $data): bool;
    public function supprimerCritere(string $idCritere): bool;
    public function listerCriteresActifs(): array;
    public function enregistrerResultatsChecklist(string $idRapport, string $idVerificateur, array $resultats): bool;
    public function getResultatsChecklistPourRapport(string $idRapport): array;
}