<?php

namespace App\Backend\Service\Fichier;

interface ServiceFichierInterface
{
    /**
     * Gère l'upload d'un fichier, incluant la validation, le renommage unique et le déplacement sécurisé.
     * @param array $fileData Les données du fichier uploadé (ex: $_FILES['photo']).
     * @param string $destinationType Le type de destination (ex: 'profile_pictures', 'reclamation_attachments').
     * @param array $allowedMimeTypes Les types MIME autorisés.
     * @param int $maxSize La taille maximale autorisée en octets.
     * @return string Le chemin relatif du fichier sauvegardé.
     * @throws \Exception En cas d'erreur d'upload ou de validation.
     */
    public function uploadFichier(array $fileData, string $destinationType, array $allowedMimeTypes = [], int $maxSize = 0): string;

    /**
     * Supprime un fichier du système de stockage.
     * @param string $filePath Le chemin relatif du fichier à supprimer.
     * @return bool Vrai si la suppression a réussi.
     */
    public function supprimerFichier(string $filePath): bool;

    /**
     * Récupère le chemin de stockage configurable pour un type de fichier donné.
     * @param string $typeFichier Le type de fichier (ex: 'profile_pictures').
     * @return string Le chemin absolu du répertoire de stockage.
     */
    public function getCheminStockage(string $typeFichier): string;
}