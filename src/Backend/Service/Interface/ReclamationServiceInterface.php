<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface ReclamationServiceInterface
{
    /**
     * Permet à un étudiant de créer une nouvelle réclamation.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $sujet Le sujet de la réclamation.
     * @param string $description La description détaillée.
     * @return string L'ID de la nouvelle réclamation.
     */
    public function soumettreReclamation(string $numeroEtudiant, string $sujet, string $description): string;

    /**
     * Permet au personnel administratif de répondre à une réclamation.
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $idAgent L'ID de l'agent qui répond.
     * @param string $reponse La réponse apportée.
     * @return bool True en cas de succès.
     */
    public function repondreAReclamation(string $idReclamation, string $idAgent, string $reponse): bool;

    /**
     * Met à jour le statut d'une réclamation (ex: 'en cours', 'résolue').
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $nouveauStatut Le nouveau statut.
     * @return bool True en cas de succès.
     */
    public function changerStatutReclamation(string $idReclamation, string $nouveauStatut): bool;

    /**
     * Assigne une réclamation à un agent spécifique pour traitement.
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $idAgent L'ID de l'agent.
     * @return bool True en cas de succès.
     */
    public function assignerReclamation(string $idReclamation, string $idAgent): bool;

    /**
     * Liste les réclamations avec des filtres.
     *
     * @param array $filtres Critères (ex: statut, agent assigné, étudiant).
     * @return array La liste des réclamations.
     */
    public function listerReclamations(array $filtres = []): array;

    /**
     * Récupère les détails complets d'une réclamation.
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @return array|null Les données de la réclamation ou null.
     */
    public function getReclamationParId(string $idReclamation): ?array;

    /**
     * Fait remonter une réclamation à un niveau hiérarchique supérieur si elle n'est pas traitée à temps.
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $niveau Le niveau d'escalade.
     * @return bool True en cas de succès.
     */
    public function escaladerReclamation(string $idReclamation, string $niveau): bool;

    /**
     * Clôture une réclamation une fois qu'elle est résolue.
     *
     * @param string $idReclamation L'ID de la réclamation.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si la réclamation n'est pas à l'état 'résolue'.
     */
    public function cloturerReclamation(string $idReclamation): bool;
}