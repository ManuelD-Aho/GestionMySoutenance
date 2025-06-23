<?php
namespace App\Backend\Service\Reclamation;

interface ServiceReclamationInterface
{
    public function soumettreReclamation(string $numeroCarteEtudiant, string $sujetReclamation, string $descriptionReclamation): string;
    public function getDetailsReclamation(string $idReclamation): ?array;
    public function recupererReclamationsEtudiant(string $numeroCarteEtudiant): array;
    public function recupererToutesReclamations(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;
    public function traiterReclamation(string $idReclamation, string $numeroPersonnelTraitant, string $newStatut, ?string $reponseReclamation): bool;
}