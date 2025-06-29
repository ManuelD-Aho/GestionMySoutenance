<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

interface StageServiceInterface
{
    /**
     * Enregistre un nouveau stage pour un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param array $donneesStage Les données du stage (entreprise, dates, sujet).
     * @return string L'ID du nouveau stage.
     * @throws DoublonException Si un stage actif existe déjà pour cet étudiant.
     */
    public function enregistrerStage(string $numeroEtudiant, array $donneesStage): string;

    /**
     * Met à jour les informations d'un stage.
     *
     * @param string $idStage L'ID du stage.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si le stage n'existe pas.
     */
    public function mettreAJourStage(string $idStage, array $donnees): bool;

    /**
     * Valide administrativement un stage.
     *
     * @param string $idStage L'ID du stage.
     * @param string $idAgent L'ID de l'agent qui valide.
     * @return bool True en cas de succès.
     */
    public function validerStage(string $idStage, string $idAgent): bool;

    /**
     * Liste les stages avec des filtres.
     *
     * @param array $filtres Critères (ex: statut, entreprise, étudiant).
     * @return array La liste des stages.
     */
    public function listerStages(array $filtres = []): array;

    /**
     * Récupère le stage actif d'un étudiant pour une année donnée.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnnee L'ID de l'année académique.
     * @return array|null Les données du stage ou null.
     */
    public function getStageParEtudiant(string $numeroEtudiant, string $idAnnee): ?array;
}