<?php
// src/Backend/Service/Supervision/ServiceSupervisionInterface.php

namespace App\Backend\Service\Supervision;

interface ServiceSupervisionInterface
{
    /**
     * Enregistre une action système dans le journal d'audit. C'est la méthode la plus critique du service.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur qui a effectué l'action (ou 'SYSTEM').
     * @param string $idAction Le code unique de l'action (ex: 'SUCCES_LOGIN').
     * @param string|null $idEntiteConcernee L'ID de l'entité principale concernée (ex: un ID de rapport).
     * @param string|null $typeEntiteConcernee Le type de l'entité (ex: 'RapportEtudiant').
     * @param array $detailsJson Données contextuelles riches à stocker en JSON (ex: ancienne et nouvelle valeur).
     * @return bool True si l'action a été enregistrée avec succès.
     */
    public function enregistrerAction(string $numeroUtilisateur, string $idAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool;

    /**
     * Enregistre une trace d'accès à une fonctionnalité protégée (un "traitement").
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $idTraitement L'ID du traitement accédé.
     * @return bool True si la trace a été enregistrée.
     */
    public function pisterAcces(string $numeroUtilisateur, string $idTraitement): bool;

    /**
     * Consulte les journaux d'audit avec des options de filtrage et de pagination.
     *
     * @param array $filtres Critères de filtrage (ex: ['numero_utilisateur' => 'ETU-2024-0001']).
     * @param int $limit Limite de résultats.
     * @param int $offset Offset pour la pagination.
     * @return array Liste des actions journalisées, enrichies avec les libellés.
     */
    public function consulterJournaux(array $filtres = [], int $limit = 50, int $offset = 0): array;

    /**
     * Génère des statistiques agrégées pour le tableau de bord de l'administrateur.
     *
     * @return array Un tableau contenant diverses statistiques (ex: nombre de rapports par statut).
     */
    public function genererStatistiquesDashboardAdmin(): array;
}