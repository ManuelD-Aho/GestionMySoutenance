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
    public function forcerChangementStatutRapport(string $idRapport, string $nouveauStatut, string $adminId, string $justification): bool;

    // --- PHASE 2: VÉRIFICATION DE CONFORMITÉ PAR L'ADMINISTRATION ---
    public function traiterVerificationConformite(string $idRapport, string $numeroPersonnel, bool $estConforme, array $detailsChecklist, ?string $commentaireGeneral): bool;

    // --- PHASE 3: GESTION DE LA SESSION DE VALIDATION (PRÉSIDENT) ---
    public function creerSession(string $idPresident, array $donneesSession): string;
    public function modifierSession(string $idSession, array $donnees): bool;
    public function composerSession(string $idSession, array $idsRapports): bool;
    public function demarrerSession(string $idSession): bool;
    public function cloturerSession(string $idSession): bool;
    public function suspendreSession(string $idSession): bool;
    public function reprendreSession(string $idSession): bool;
    public function listerSessionsPourCommission(array $filtres = []): array;
    public function lireSessionComplete(string $idSession): ?array;
    public function designerRapporteur(string $idRapport, string $numeroEnseignantRapporteur): bool;
    public function recuserMembre(string $idSession, string $numeroEnseignant, string $justification): bool;

    // --- PHASE 4: ÉVALUATION ET VOTE PAR LA COMMISSION ---
    public function enregistrerVote(string $idRapport, string $idSession, string $numeroEnseignant, string $decision, ?string $commentaire): bool;
    public function lancerNouveauTourDeVote(string $idRapport, string $idSession): bool;
    public function consulterEtatVotes(string $idSession): array;

    // --- PHASE 5: GESTION DES PROCÈS-VERBAUX (PV) ---
    public function initierRedactionPv(string $idSession, string $idRedacteur): string;
    public function reassignerRedactionPv(string $idCompteRendu, string $idNouveauRedacteur): bool;
    public function mettreAJourContenuPv(string $idCompteRendu, string $contenu): bool;
    public function soumettrePvPourApprobation(string $idCompteRendu): bool;
    public function approuverPv(string $idCompteRendu, string $idPresident): bool;
    public function forcerValidationPv(string $idCompteRendu, string $idPresident, string $justification): bool;

    // --- PHASE 6: FINALISATION POST-VALIDATION (PRÉSIDENT) ---
    public function designerDirecteurMemoire(string $idRapport, string $numeroEnseignantDirecteur): bool;

    // --- PHASE 7: GESTION DES RÉCLAMATIONS ---
    public function creerReclamation(string $numeroEtudiant, string $categorie, string $sujet, string $description): string;
    public function listerReclamations(array $filtres = []): array;
    public function lireReclamation(string $idReclamation): ?array;
    public function traiterReclamation(string $idReclamation, string $reponse, string $numeroPersonnel): bool;
}