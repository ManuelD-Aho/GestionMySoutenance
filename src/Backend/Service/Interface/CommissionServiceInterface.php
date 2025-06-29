<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface CommissionServiceInterface
{
    /**
     * Crée une nouvelle session de validation.
     *
     * @param array $donnees Données de la session (nom, date, président, membres).
     * @return string L'ID de la nouvelle session.
     * @throws DoublonException Si une session avec le même nom existe pour la même période.
     */
    public function creerSessionValidation(array $donnees): string;

    /**
     * Met à jour les détails d'une session de validation.
     *
     * @param string $idSession L'ID de la session.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la session n'existe pas.
     */
    public function mettreAJourSession(string $idSession, array $donnees): bool;

    /**
     * Clôture une session de validation.
     *
     * @param string $idSession L'ID de la session.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la session n'existe pas.
     * @throws OperationImpossibleException Si des rapports dans la session sont encore en attente de vote.
     */
    public function cloturerSession(string $idSession): bool;

    /**
     * Ajoute un rapport éligible à une session planifiée.
     *
     * @param string $idSession L'ID de la session.
     * @param string $idRapport L'ID du rapport.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la session ou le rapport n'existe pas.
     * @throws OperationImpossibleException Si la session n'est pas à l'état 'planifiée'.
     */
    public function ajouterRapportASession(string $idSession, string $idRapport): bool;

    /**
     * Retire un rapport d'une session planifiée.
     *
     * @param string $idSession L'ID de la session.
     * @param string $idRapport L'ID du rapport.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'association n'existe pas.
     */
    public function retirerRapportDeSession(string $idSession, string $idRapport): bool;

    /**
     * Liste les sessions de validation avec des filtres.
     *
     * @param array $filtres Critères de filtrage (ex: statut, année académique).
     * @return array La liste des sessions.
     */
    public function listerSessions(array $filtres = []): array;

    /**
     * Récupère les détails complets d'une session, incluant les rapports et les membres.
     *
     * @param string $idSession L'ID de la session.
     * @return array|null Les données de la session ou null si non trouvée.
     */
    public function recupererSessionParId(string $idSession): ?array;

    /**
     * Déclenche le processus de vote pour un rapport au sein d'une session.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $idSession L'ID de la session.
     * @return bool True en cas de succès.
     */
    public function lancerVotePourRapport(string $idRapport, string $idSession): bool;

    /**
     * Incrémente le tour de vote pour un rapport en délibération si le consensus n'est pas atteint.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $idSession L'ID de la session.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si le tour de vote précédent n'est pas terminé.
     */
    public function lancerNouveauTourDeVote(string $idRapport, string $idSession): bool;
}