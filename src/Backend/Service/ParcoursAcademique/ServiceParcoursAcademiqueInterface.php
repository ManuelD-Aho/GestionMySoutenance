<?php
// src/Backend/Service/ParcoursAcademique/ServiceParcoursAcademiqueInterface.php

namespace App\Backend\Service\ParcoursAcademique;

interface ServiceParcoursAcademiqueInterface
{
    // --- CRUD Inscriptions ---
    public function creerInscription(array $donnees): bool;
    public function lireInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee): ?array;
    public function mettreAJourInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee, array $donnees): bool;
    public function supprimerInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee): bool;
    public function listerInscriptions(array $filtres = []): array;

    // --- CRUD Notes ---
    public function creerOuMettreAJourNote(array $donnees): bool;
    public function lireNote(string $numeroEtudiant, string $idEcue, string $idAnnee): ?array;
    public function supprimerNote(string $numeroEtudiant, string $idEcue, string $idAnnee): bool;
    public function listerNotes(array $filtres = []): array;

    // --- CRUD Stages ---
    public function creerStage(array $donnees): bool;
    public function lireStage(string $numeroEtudiant, string $idEntreprise): ?array;
    public function mettreAJourStage(string $numeroEtudiant, string $idEntreprise, array $donnees): bool;
    public function supprimerStage(string $numeroEtudiant, string $idEntreprise): bool;
    public function validerStage(string $numeroEtudiant, string $idEntreprise): bool;
    public function listerStages(array $filtres = []): array; // Ajout de cette méthode

    // --- CRUD Pénalités ---
    public function creerPenalite(array $donnees): string;
    public function lirePenalite(string $idPenalite): ?array;
    public function mettreAJourPenalite(string $idPenalite, array $donnees): bool;
    public function regulariserPenalite(string $idPenalite, string $numeroPersonnel): bool;
    public function listerPenalites(array $filtres = []): array;

    // --- Logique Métier ---
    public function estEtudiantEligibleSoumission(string $numeroEtudiant): bool;
    public function enregistrerDecisionPassage(string $numeroEtudiant, string $idAnnee, string $idDecision): bool;
    public function calculerMoyennes(string $numeroEtudiant, string $idAnnee): array;
}