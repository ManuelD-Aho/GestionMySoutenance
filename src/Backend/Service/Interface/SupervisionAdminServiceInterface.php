<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface SupervisionAdminServiceInterface
{
    /**
     * Récupère des statistiques de haut niveau sur le système.
     *
     * @return array Statistiques (nombre d'utilisateurs, de rapports, etc.).
     */
    public function getStatistiquesSysteme(): array;

    /**
     * Vérifie l'état des services externes (base de données, serveur de mail, etc.).
     *
     * @return array Le statut de chaque service.
     */
    public function getStatutServices(): array;

    /**
     * Déclenche manuellement une tâche de maintenance (ex: archivage, nettoyage).
     *
     * @param string $nomTache Le nom de la tâche à lancer.
     * @return bool True si la tâche a été lancée.
     */
    public function lancerTacheMaintenance(string $nomTache): bool;

    /**
     * Récupère les derniers logs d'erreur critiques pour un diagnostic rapide.
     *
     * @param int $limite Le nombre de logs à récupérer.
     * @return array La liste des logs critiques.
     */
    public function consulterLogsCritiques(int $limite): array;

    /**
     * Génère un rapport de santé complet du système.
     *
     * @return string Le rapport généré (format texte ou HTML).
     */
    public function genererRapportSante(): string;

    /**
     * Configure les règles d'alerting pour la supervision.
     *
     * @param array $regles Les règles d'alerte (seuils, destinataires).
     * @return bool True en cas de succès.
     */
    public function configurerAlertes(array $regles): bool;
}