<?php

namespace App\Backend\Service\Commission;

interface ServiceCommissionInterface
{
    public function creerSessionValidation(string $libelleSession, string $dateDebutSession, string $dateFinPrevue, ?string $numeroPresidentCommission = null, array $idsRapports = []): string;
    public function demarrerSession(string $idSession): bool;
    public function cloturerSession(string $idSession): bool;
    public function listerSessionsValidation(array $criteres = []): array;
    public function prolongerSession(string $idSession, string $nouvelleDateFin): bool;
    public function retirerRapportDeSession(string $idSession, string $idRapportEtudiant): bool;
    public function enregistrerVotePourRapport(string $idRapportEtudiant, string $numeroEnseignant, string $idDecisionVote, ?string $commentaireVote, int $tourVote, ?string $idSession = null): bool;
    public function finaliserDecisionCommissionPourRapport(string $idRapportEtudiant): bool;
    public function lancerNouveauTourVote(string $idRapportEtudiant): bool;
    public function recupererRapportsAssignedToJury(string $numeroEnseignant, ?string $idSession = null): array;
    public function recupererRapportsAssignedToJuryForCorrection(string $numeroEnseignant): array;
    public function getVoteByEnseignantRapportTour(string $numeroEnseignant, string $idRapportEtudiant, int $tourVote): ?array;
    public function redigerOuMettreAJourPv(string $idRedacteur, string $libellePv, string $typePv, ?string $idRapportEtudiant = null, array $idsRapportsSession = [], ?string $idCompteRenduExistant = null): string;
    public function soumettrePvPourValidation(string $idCompteRendu): bool;
    public function validerOuRejeterPv(string $idCompteRendu, string $numeroEnseignantValidateur, string $idDecisionValidationPv, ?string $commentaireValidation): bool;
    public function listerPvEnAttenteValidationParMembre(string $numeroEnseignant): array;
    public function deleguerRedactionPv(string $idCompteRendu, string $ancienRedacteur, string $nouveauRedacteur): bool;
    public function gererApprobationsPvBloquees(string $idCompteRendu, string $action, ?string $numeroPersonnelAction = null, ?string $commentaire = null): bool;
}