<?php
// src/Backend/Service/Document/ServiceDocumentInterface.php

namespace App\Backend\Service\Document;

interface ServiceDocumentInterface
{
    // --- Section 1: Génération de Documents PDF ---
    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string;
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string;
    public function genererPvValidation(string $idCompteRendu): string;
    public function genererRecuPaiement(string $idInscription): string;
    public function genererRapportEtudiantPdf(string $idRapport): string;
    public function genererListePdf(string $nomListe, array $donnees, array $colonnes): string;

    // --- Section 2: Gestion des Modèles de Documents (CRUD) ---
    public function creerModeleDocument(string $nom, string $contenuHtml, string $type = 'pdf'): string;
    public function lireModeleDocument(string $idModele): ?array;
    public function mettreAJourModeleDocument(string $idModele, string $nom, string $contenuHtml): bool;
    public function supprimerModeleDocument(string $idModele): bool;
    public function listerModelesDocument(string $type = 'pdf'): array;

    // --- Section 3: Gestion des Fichiers ---
    public function uploadFichierSecurise(array $fileData, string $destinationType, array $allowedMimeTypes, int $maxSizeInBytes): string;
    public function supprimerFichier(string $relativePath): bool;
// --- Section 4: Vérification des Droits (NOUVEAU) ---
    /**
     * Vérifie si un utilisateur est le propriétaire d'un document généré.
     *
     * @param string $filename Le nom du fichier (pas le chemin complet).
     * @param string $numeroUtilisateur L'ID de l'utilisateur à vérifier.
     * @return bool True si l'utilisateur est le propriétaire.
     */
    public function verifierProprieteDocument(string $filename, string $numeroUtilisateur): bool;


}
