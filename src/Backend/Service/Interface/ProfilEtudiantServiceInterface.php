<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

interface ProfilEtudiantServiceInterface
{
    /**
     * Crée une entité métier "Étudiant".
     *
     * @param array $donnees Données du profil étudiant (nom, prénom, date_naissance...).
     * @return string Le numéro de carte de l'étudiant créé.
     * @throws DoublonException Si un étudiant avec le même numéro de carte existe déjà.
     */
    public function creerEtudiant(array $donnees): string;

    /**
     * Met à jour les informations du profil d'un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'étudiant n'existe pas.
     */
    public function mettreAJourProfil(string $numeroEtudiant, array $donnees): bool;

    /**
     * Récupère les données d'un profil étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return array|null Les données du profil ou null.
     */
    public function recupererProfil(string $numeroEtudiant): ?array;

    /**
     * Ajoute ou modifie la photo de profil d'un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $fichier Le tableau `$_FILES` de la photo.
     * @return string Le chemin de la photo enregistrée.
     */
    public function mettreAJourPhotoProfil(string $numeroEtudiant, array $fichier): string;

    /**
     * Supprime la photo de profil d'un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return bool True en cas de succès.
     */
    public function supprimerPhotoProfil(string $numeroEtudiant): bool;

    /**
     * Récupère toutes les données associées à un étudiant (profil, inscriptions, notes, stages...).
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return array Le dossier complet de l'étudiant.
     */
    public function getDossierComplet(string $numeroEtudiant): array;
}