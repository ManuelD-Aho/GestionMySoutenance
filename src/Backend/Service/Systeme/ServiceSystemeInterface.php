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
// --- Gestion des Menus ---
    /**
     * Met à jour la structure hiérarchique et l'ordre d'affichage des éléments de menu.
     *
     * @param array $menuStructure Un tableau représentant la nouvelle hiérarchie et l'ordre.
     *                             Ex: [['id' => 'MENU_DASHBOARDS', 'order' => 1, 'parent' => null], ['id' => 'TRAIT_ADMIN_DASHBOARD_ACCEDER', 'order' => 1, 'parent' => 'MENU_DASHBOARDS']]
     * @return bool True si la mise à jour est réussie, false sinon.
     */
    public function updateMenuStructure(array $menuStructure): bool;

}