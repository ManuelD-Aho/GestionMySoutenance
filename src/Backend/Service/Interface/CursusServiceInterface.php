<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

interface CursusServiceInterface
{
    /**
     * Crée une nouvelle Unité d'Enseignement (UE).
     *
     * @param array $donnees Données de l'UE.
     * @return string L'ID de la nouvelle UE.
     * @throws DoublonException Si une UE avec le même code existe déjà.
     */
    public function creerUE(array $donnees): string;

    /**
     * Met à jour une Unité d'Enseignement.
     *
     * @param string $idUe L'ID de l'UE.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'UE n'existe pas.
     */
    public function mettreAJourUE(string $idUe, array $donnees): bool;

    /**
     * Crée un nouvel Élément Constitutif d'UE (ECUE).
     *
     * @param array $donnees Données de l'ECUE.
     * @return string L'ID du nouvel ECUE.
     * @throws DoublonException Si un ECUE avec le même code existe déjà.
     */
    public function creerECUE(array $donnees): string;

    /**
     * Met à jour un Élément Constitutif d'UE.
     *
     * @param string $idEcue L'ID de l'ECUE.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'ECUE n'existe pas.
     */
    public function mettreAJourECUE(string $idEcue, array $donnees): bool;

    /**
     * Associe un ECUE à son UE parente.
     *
     * @param string $idEcue L'ID de l'ECUE.
     * @param string $idUe L'ID de l'UE.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'ECUE ou l'UE n'existe pas.
     */
    public function lierEcueAUe(string $idEcue, string $idUe): bool;

    /**
     * Affiche la maquette pédagogique complète pour un niveau d'étude donné.
     *
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @return array La structure complète du cursus.
     */
    public function listerCursusComplet(string $idNiveauEtude): array;
}