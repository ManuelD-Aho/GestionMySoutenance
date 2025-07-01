<?php
// src/Backend/Service/WorkflowSoutenance/ServiceWorkflowSoutenanceInterface.php

namespace App\Backend\Service\WorkflowSoutenance;

interface ServiceWorkflowSoutenanceInterface
{
    // --- PHASE 1: GESTION DU RAPPORT PAR L'ÉTUDIANT ---
    public function creerOuMettreAJourBrouillon(string $numeroEtudiant, array $metadonnees, array $sections): string;
    public function soumettreRapport(string $idRapport, string $numeroEtudiant): bool;
    public function soumettreCorrections(string $idRapport, string $numeroEtudiant, array $sections, string $noteExplicative): bool;
    public function lireRapportComplet(string $idRapport): ?array;
    public function listerRapports(array $filtres = []): array;

    // --- PHASE 2: VÉRIFICATION DE CONFORMITÉ PAR L'ADMINISTRATION ---
    public function traiterVerificationConformite(string $idRapport, string $numeroPersonnel, bool $estConforme, array $detailsChecklist, ?string $commentaireGeneral): bool;

    // --- PHASE 3: GESTION DE LA SESSION DE VALIDATION (PRÉSIDENT) ---
    public function creerSession(string $idPresident, array $donneesSession): string;
    public function modifierSession(string $idSession, string $idPresident, array $donnees): bool;
    public function composerSession(string $idSession, string $idPresident, array $idsRapports): bool;
    public function demarrerSession(string $idSession, string $idPresident): bool;
    public function cloturerSession(string $idSession, string $idPresident): bool;
    public function listerSessionsPourCommission(): array;

    // --- PHASE 4: ÉVALUATION ET VOTE PAR LA COMMISSION ---
    public function enregistrerVote(string $idRapport, string $numeroEnseignant, string $decision, ?string $commentaire): bool;
    public function lancerNouveauTourDeVote(string $idRapport, string $idPresident): bool;
    public function consulterEtatVotes(string $idSession): array;

    // --- PHASE 5: GESTION DES PROCÈS-VERBAUX (PV) ---
    public function initierRedactionPv(string $idSession, string $idRedacteur): string;
    public function mettreAJourContenuPv(string $idCompteRendu, string $idRedacteur, string $contenu): bool;
    public function soumettrePvPourValidation(string $idCompteRendu, string $idRedacteur): bool;
    public function approuverOuRejeterPv(string $idCompteRendu, string $numeroMembre, bool $approbation, ?string $commentaire): bool;
    public function forcerValidationPv(string $idCompteRendu, string $idPresident, string $methode, string $justification): bool; // 'substitution' ou 'quorum'

    // --- PHASE 6: FINALISATION POST-VALIDATION (PRÉSIDENT) ---
    public function designerDirecteurMemoire(string $idRapport, string $idPresident, string $numeroEnseignantDirecteur): bool;
}