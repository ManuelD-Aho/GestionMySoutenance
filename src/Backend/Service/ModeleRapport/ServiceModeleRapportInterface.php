<?php
namespace App\Backend\Service\ModeleRapport;

interface ServiceModeleRapportInterface
{
    public function creerModele(array $data): string;
    public function modifierModele(string $idModele, array $data): bool;
    public function supprimerModele(string $idModele): bool;
    public function ajouterSectionAuModele(string $idModele, array $dataSection): bool;
    public function assignerModeleANiveau(string $idModele, string $idNiveauEtude): bool;
    public function listerModelesPourNiveau(string $idNiveauEtude): array;
    public function creerRapportDepuisModele(string $idModele, string $numeroEtudiant): string;
}