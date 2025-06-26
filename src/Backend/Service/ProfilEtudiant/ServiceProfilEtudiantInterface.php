<?php

namespace App\Backend\Service\ProfilEtudiant;

interface ServiceProfilEtudiantInterface
{
    /**
     * Met à jour les coordonnées personnelles d'un étudiant.
     * @param string $numeroEtudiant L'ID de l'étudiant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     */
    public function mettreAJourCoordonneesPersonnelles(string $numeroEtudiant, array $donnees): bool;

    /**
     * Gère l'upload et l'enregistrement de la photo de profil de l'étudiant.
     * @param string $numeroEtudiant L'ID de l'étudiant.
     * @param array $fileData Les données du fichier uploadé (ex: $_FILES['photo']).
     * @return string Le chemin relatif de la nouvelle photo de profil.
     */
    public function telechargerPhotoProfil(string $numeroEtudiant, array $fileData): string;

    /**
     * Récupère les détails complets du profil étudiant.
     * @param string $numeroEtudiant L'ID de l'étudiant.
     * @return array|null Les détails du profil ou null si non trouvé.
     */
    public function getProfilEtudiant(string $numeroEtudiant): ?array;
}