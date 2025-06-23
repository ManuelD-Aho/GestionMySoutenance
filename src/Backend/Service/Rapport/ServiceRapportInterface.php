<?php
namespace App\Backend\Service\Rapport;

interface ServiceRapportInterface
{
    public function creerOuMettreAJourBrouillonRapport(string $numeroCarteEtudiant, array $metadonnees, array $sectionsContenu): string;
    public function soumettreRapportPourVerification(string $idRapportEtudiant): bool;
    public function enregistrerCorrectionsSoumises(string $idRapportEtudiant, array $sectionsContenuCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative = null): bool;
    public function recupererInformationsRapportComplet(string $idRapportEtudiant): ?array;
    public function mettreAJourStatutRapport(string $idRapportEtudiant, string $newStatutId): bool;
    public function reactiverEditionRapport(string $idRapportEtudiant, string $motifActivation = 'Reprise demandée'): bool;
    public function listerRapportsParCriteres(array $criteres = [], array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}