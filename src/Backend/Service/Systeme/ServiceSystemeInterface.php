<?php
// src/Backend/Service/Systeme/ServiceSystemeInterface.php

namespace App\Backend\Service\Systeme;

interface ServiceSystemeInterface
{
    // --- Gestion des Identifiants ---
    public function genererIdentifiantUnique(string $prefixe): string;

    // --- Gestion des Paramètres et du Mode Maintenance ---
    public function getParametre(string $cle, mixed $defaut = null);
    public function getAllParametres(): array;
    public function setParametres(array $parametres): bool;
    public function activerMaintenanceMode(bool $actif, string $message = "Le site est en cours de maintenance."): bool;
    public function estEnMaintenance(): bool;

    // --- Gestion des Années Académiques ---
    public function creerAnneeAcademique(string $libelle, string $dateDebut, string $dateFin, bool $estActive = false): string;
    public function lireAnneeAcademique(string $idAnneeAcademique): ?array;
    public function mettreAJourAnneeAcademique(string $idAnneeAcademique, array $donnees): bool;
    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool;
    public function listerAnneesAcademiques(): array;
    public function getAnneeAcademiqueActive(): ?array;
    public function setAnneeAcademiqueActive(string $idAnneeAcademique): bool;

    // --- Gestion des Référentiels ---
    public function gererReferentiel(string $operation, string $nomReferentiel, ?string $id = null, ?array $donnees = null);
}