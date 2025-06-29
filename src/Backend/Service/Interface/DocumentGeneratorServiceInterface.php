<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ModeleNonTrouveException;

interface DocumentGeneratorServiceInterface
{
    /**
     * Convertit une chaîne de caractères HTML en un fichier PDF.
     *
     * @param string $htmlContent Le contenu HTML à convertir.
     * @param array $options Options de configuration pour la génération (ex: orientation, format).
     * @return string Le chemin vers le fichier PDF généré.
     * @throws OperationImpossibleException En cas d'erreur de génération.
     */
    public function genererPdfDepuisHtml(string $htmlContent, array $options = []): string;

    /**
     * Génère un fichier PDF à partir d'un modèle prédéfini et de variables.
     *
     * @param string $templateCode Le code identifiant le modèle à utiliser.
     * @param array $variables Les variables à injecter dans le modèle.
     * @return string Le chemin vers le fichier PDF généré.
     * @throws ModeleNonTrouveException Si le modèle n'existe pas.
     */
    public function genererPdfDepuisTemplate(string $templateCode, array $variables): string;

    /**
     * Ajoute un filigrane textuel sur un fichier PDF existant.
     *
     * @param string $cheminPdf Le chemin vers le PDF à modifier.
     * @param string $texte Le texte du filigrane.
     * @return bool True en cas de succès.
     */
    public function ajouterFiligrane(string $cheminPdf, string $texte): bool;

    /**
     * Fusionne plusieurs fichiers PDF en un seul document.
     *
     * @param array $cheminsPdfs Un tableau de chemins vers les fichiers PDF à fusionner.
     * @return string Le chemin vers le fichier PDF fusionné.
     */
    public function fusionnerPdfs(array $cheminsPdfs): string;

    /**
     * Appose une signature électronique à un fichier PDF.
     *
     * @param string $cheminPdf Le chemin vers le PDF.
     * @param array $infosSignature Les informations de signature (certificat, etc.).
     * @return bool True en cas de succès.
     */
    public function signerPdf(string $cheminPdf, array $infosSignature): bool;
}