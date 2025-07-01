<?php
// src/Backend/Service/Document/ServiceDocumentInterface.php

namespace App\Backend\Service\Document;

interface ServiceDocumentInterface
{
    // --- Section 1: Génération de Documents PDF ---
    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string;
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string;
    public function genererPvValidation(string $idCompteRendu): string;
    public function genererRecuPaiement(string $idInscription): string; // NOUVEAU
    public function genererRapportEtudiantPdf(string $idRapport): string; // NOUVEAU
    public function genererListePdf(string $nomListe, array $donnees, array $colonnes): string; // NOUVEAU

    // --- Section 2: Gestion des Fichiers ---
    public function uploadFichierSecurise(array $fileData, string $destinationType, array $allowedMimeTypes, int $maxSizeInBytes): string;
    public function supprimerFichier(string $relativePath): bool;
}