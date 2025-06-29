<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface NotationServiceInterface
{
    /**
     * Enregistre ou met à jour la note d'un étudiant pour un ECUE.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param float $note La note obtenue.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'étudiant ou l'ECUE n'existe pas.
     */
    public function saisirNote(string $numeroEtudiant, string $idEcue, float $note): bool;

    /**
     * Modifie une note déjà existante.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param float $note La nouvelle note.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la note à modifier n'existe pas.
     */
    public function modifierNote(string $numeroEtudiant, string $idEcue, float $note): bool;

    /**
     * Importe des notes en masse depuis un fichier (CSV, Excel).
     *
     * @param string $cheminFichier Le chemin du fichier à importer.
     * @return array Un rapport d'importation (succès, erreurs).
     */
    public function importerNotesDepuisFichier(string $cheminFichier): array;

    /**
     * Calcule la moyenne d'un étudiant pour un semestre donné.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idSemestre L'ID du semestre.
     * @return float La moyenne calculée.
     * @throws OperationImpossibleException Si des notes sont manquantes pour le calcul.
     */
    public function calculerMoyenneSemestre(string $numeroEtudiant, string $idSemestre): float;

    /**
     * Calcule la moyenne générale d'un étudiant pour une année académique.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnnee L'ID de l'année.
     * @return float La moyenne calculée.
     */
    public function calculerMoyenneAnnee(string $numeroEtudiant, string $idAnnee): float;

    /**
     * Liste toutes les notes d'un étudiant pour une année donnée ou toutes les années.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string|null $idAnnee L'ID de l'année (optionnel).
     * @return array La liste des notes.
     */
    public function listerNotesParEtudiant(string $numeroEtudiant, ?string $idAnnee): array;
}