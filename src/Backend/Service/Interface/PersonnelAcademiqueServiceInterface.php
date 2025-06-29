<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

/**
 * Contrat pour le service de gestion du personnel académique et administratif.
 * Responsable de la gestion des profils, des spécialités, des grades et des fonctions.
 */
interface PersonnelAcademiqueServiceInterface
{
    /**
     * Récupère le profil complet d'un enseignant.
     *
     * @param string $numeroEnseignant
     * @return array
     * @throws ElementNonTrouveException si l'enseignant n'est pas trouvé.
     */
    public function getProfilEnseignant(string $numeroEnseignant): array;

    /**
     * Lie une spécialité à un enseignant via la table `attribuer`.
     *
     * @param string $numeroEnseignant
     * @param string $idSpecialite
     * @return bool
     * @throws ElementNonTrouveException si l'enseignant ou la spécialité n'existe pas.
     * @throws DoublonException si l'assignation existe déjà.
     */
    public function assignerSpecialiteAEnseignant(string $numeroEnseignant, string $idSpecialite): bool;

    /**
     * Ajoute un grade à l'historique d'un enseignant (table `acquerir`).
     *
     * @param string $numeroEnseignant
     * @param string $idGrade
     * @param \DateTimeInterface $dateAcquisition
     * @return bool
     */
    public function ajouterGradeHistorique(string $numeroEnseignant, string $idGrade, \DateTimeInterface $dateAcquisition): bool;

    /**
     * Ajoute une fonction à l'historique d'un enseignant (table `occuper`).
     *
     * @param string $numeroEnseignant
     * @param string $idFonction
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface|null $dateFin
     * @return bool
     */
    public function ajouterFonctionHistorique(string $numeroEnseignant, string $idFonction, \DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin = null): bool;

    /**
     * Liste le personnel (enseignant ou administratif) selon des critères.
     *
     * @param string $typePersonnel 'enseignant' ou 'administratif'.
     * @param array $criteres
     * @return array
     */
    public function listerPersonnel(string $typePersonnel, array $criteres): array;
}