<?php
namespace App\Backend\Service\Importation;

interface ServiceImportationInterface
{
    public function analyserFichier(string $cheminFichier): array;
    public function previsualiserDonnees(string $cheminFichier, array $mappage): array;
    public function lancerImportationAsynchrone(string $cheminFichier, array $mappage, string $typeEntite): string;
    public function getStatutImportation(string $idTache): array;
}