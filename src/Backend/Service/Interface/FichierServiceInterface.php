<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

interface FichierServiceInterface
{
    /**
     * Gère l'upload sécurisé d'un fichier sur le serveur.
     *
     * @param array $fichier Le tableau `$_FILES` du fichier.
     * @param string $destination Le répertoire de destination.
     * @param array $contraintes Contraintes de validation (ex: taille max, types MIME autorisés).
     * @return string Le chemin relatif du fichier uploadé.
     * @throws ValidationException Si le fichier ne respecte pas les contraintes.
     */
    public function uploader(array $fichier, string $destination, array $contraintes = []): string;

    /**
     * Supprime un fichier physique du serveur.
     *
     * @param string $cheminFichier Le chemin relatif du fichier à supprimer.
     * @return bool True en cas de succès.
     */
    public function supprimer(string $cheminFichier): bool;

    /**
     * Crée une URL de téléchargement temporaire et sécurisée pour un fichier.
     *
     * @param string $cheminFichier Le chemin du fichier.
     * @param int $dureeValidite La durée de validité de l'URL en secondes.
     * @return string L'URL sécurisée.
     */
    public function genererUrlSecurisee(string $cheminFichier, int $dureeValidite): string;

    /**
     * Extrait les métadonnées d'un fichier.
     *
     * @param string $cheminFichier Le chemin du fichier.
     * @return array Les métadonnées (taille, type MIME, etc.).
     */
    public function getMetadonnees(string $cheminFichier): array;

    /**
     * Crée une version redimensionnée d'une image.
     *
     * @param string $cheminImage Le chemin de l'image source.
     * @param int $largeur La largeur désirée.
     * @param int $hauteur La hauteur désirée.
     * @return string Le chemin de la nouvelle image redimensionnée.
     * @throws OperationImpossibleException Si le fichier n'est pas une image supportée.
     */
    public function redimensionnerImage(string $cheminImage, int $largeur, int $hauteur): string;

    /**
     * Compresse un fichier (ex: en format ZIP).
     *
     * @param string $cheminFichier Le chemin du fichier à compresser.
     * @return string Le chemin du fichier compressé.
     */
    public function compresserFichier(string $cheminFichier): string;

    /**
     * Scanne un fichier uploadé à l'aide d'un antivirus.
     *
     * @param string $cheminFichier Le chemin du fichier.
     * @return bool True si le fichier est sain.
     * @throws OperationImpossibleException Si un virus est détecté.
     */
    public function scannerAntivirus(string $cheminFichier): bool;
}