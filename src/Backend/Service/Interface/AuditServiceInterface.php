<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface AuditServiceInterface
{
    /**
     * Enregistre une action métier ou système dans la piste d'audit.
     *
     * @param string $numeroUtilisateur L'identifiant de l'utilisateur effectuant l'action.
     * @param string $codeAction Le code unique de l'action (correspond à action.id_action).
     * @param string|null $idEntiteConcernee L'ID de l'entité affectée (ex: ID d'un rapport, d'un utilisateur).
     * @param string|null $typeEntiteConcernee Le type de l'entité (ex: 'RapportEtudiant', 'Utilisateur').
     * @param array $detailsAction Un tableau associatif contenant des détails contextuels, qui sera stocké en JSON.
     */
    public function enregistrerAction(
        string $numeroUtilisateur,
        string $codeAction,
        ?string $idEntiteConcernee = null,
        ?string $typeEntiteConcernee = null,
        array $detailsAction = []
    ): void;

    /**
     * Récupère une liste paginée de logs d'audit, avec des options de filtrage.
     *
     * @param int $limite Nombre maximum de logs à retourner.
     * @param int $offset Point de départ pour la pagination.
     * @param array $filtres Critères de filtrage (ex: ['numero_utilisateur' => '...', 'id_action' => '...']).
     * @return array La liste des logs.
     */
    public function listerLogs(int $limite = 50, int $offset = 0, array $filtres = []): array;

    /**
     * Récupère l'historique complet des actions pour une entité spécifique.
     *
     * @param string $idEntite L'identifiant de l'entité.
     * @param string $typeEntite Le type de l'entité.
     * @return array L'historique des actions sur cette entité.
     */
    public function getHistoriquePourEntite(string $idEntite, string $typeEntite): array;
}