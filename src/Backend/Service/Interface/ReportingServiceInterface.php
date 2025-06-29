<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface ReportingServiceInterface
{
    /**
     * Calcule les taux de validation des rapports sur une période donnée.
     *
     * @param array $filtres Critères (ex: année, spécialité).
     * @return array Le rapport sur les taux de validation.
     */
    public function genererRapportTauxValidation(array $filtres): array;

    /**
     * Calcule les délais moyens de traitement pour chaque étape du workflow des rapports.
     *
     * @param array $filtres Critères de filtrage.
     * @return array Le rapport sur les délais de traitement.
     */
    public function genererRapportDelaisTraitement(array $filtres): array;

    /**
     * Calcule les statistiques d'utilisation de la plateforme.
     *
     * @param string $periode La période à analyser (ex: 'dernier_mois').
     * @return array Les statistiques d'utilisation (connexions, rapports soumis, etc.).
     */
    public function genererStatistiquesUtilisation(string $periode): array;

    /**
     * Exporte un jeu de données en format CSV ou PDF.
     *
     * @param string $typeDonnees Le type de données à exporter (ex: 'etudiants', 'rapports').
     * @param string $format Le format d'export ('csv', 'pdf').
     * @param array $filtres Critères de filtrage des données.
     * @return string Le chemin vers le fichier exporté.
     */
    public function exporterDonnees(string $typeDonnees, string $format, array $filtres): string;

    /**
     * Permet à un administrateur de créer un tableau de bord personnalisé.
     *
     * @param string $nom Le nom du dashboard.
     * @param array $widgets La configuration des widgets à afficher.
     * @return string L'ID du nouveau dashboard.
     */
    public function creerDashboardPersonnalise(string $nom, array $widgets): string;
}