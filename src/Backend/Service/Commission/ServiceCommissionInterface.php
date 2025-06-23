<?php
namespace App\Backend\Service\Commission;

interface ServiceCommissionInterface
{
    public function creerSessionValidation(string $nomSession, string $idPresident, array $idsRapports, ?string $dateFinPrevue = null): string;
    public function demarrerSession(string $idSession): bool;
    public function cloturerSession(string $idSession): bool;
    public function listerSessionsValidation(array $criteres = []): array;
    public function recupererRapportsPourSession(string $idSession): array;
    public function enregistrerVote(string $idSession, string $idRapport, string $idEnseignant, string $idDecision, ?string $commentaire, int $tour): bool;
    public function lancerNouveauTourDeVote(string $idSession, string $idRapport): bool;
    public function redigerPv(string $idSession, string $idRedacteur, string $contenu): string;
    public function approuverPv(string $idCompteRendu, string $idApprobateur): bool;
}