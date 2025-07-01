<?php

namespace App\Backend\Service\Supervision;

interface ServiceSupervisionInterface
{
    /**
     * Surveille les performances du système en temps réel.
     * @return array Les métriques de performance actuelles.
     */
    public function surveillerPerformancesTempsReel(): array;

    /**
     * Génère un rapport de supervision détaillé.
     * @param string $periode La période de supervision (HEURE, JOUR, SEMAINE, MOIS).
     * @param array $metriques Les métriques à inclure.
     * @return array Le rapport de supervision.
     */
    public function genererRapportSupervision(string $periode, array $metriques = []): array;

    /**
     * Configure les seuils d'alerte pour différentes métriques.
     * @param array $seuils Les seuils d'alerte à configurer.
     * @return bool Vrai si la configuration a réussi.
     */
    public function configurerSeuilsAlerte(array $seuils): bool;

    /**
     * Vérifie et déclenche les alertes nécessaires.
     * @return array Les alertes déclenchées.
     */
    public function verifierEtDeclencherAlertes(): array;

    /**
     * Enregistre une métrique personnalisée.
     * @param string $nomMetrique Le nom de la métrique.
     * @param mixed $valeur La valeur de la métrique.
     * @param array $contexte Le contexte supplémentaire.
     * @return bool Vrai si l'enregistrement a réussi.
     */
    public function enregistrerMetrique(string $nomMetrique, mixed $valeur, array $contexte = []): bool;

    /**
     * Récupère l'historique d'une métrique.
     * @param string $nomMetrique Le nom de la métrique.
     * @param string $dateDebut La date de début.
     * @param string $dateFin La date de fin.
     * @param string $granularite La granularité (MINUTE, HEURE, JOUR).
     * @return array L'historique de la métrique.
     */
    public function obtenirHistoriqueMetrique(string $nomMetrique, string $dateDebut, string $dateFin, string $granularite = 'HEURE'): array;

    /**
     * Surveille l'utilisation des ressources système.
     * @return array Les données d'utilisation des ressources.
     */
    public function surveillerRessourcesSysteme(): array;

    /**
     * Surveille les connexions et sessions utilisateurs.
     * @return array Les données de connexions.
     */
    public function surveillerConnexionsUtilisateurs(): array;

    /**
     * Surveille les erreurs et exceptions de l'application.
     * @return array Les données d'erreurs.
     */
    public function surveillerErreursApplication(): array;

    /**
     * Génère des recommandations d'optimisation.
     * @return array Les recommandations.
     */
    public function genererRecommandationsOptimisation(): array;

    /**
     * Archive les anciennes données de supervision.
     * @param int $joursConservation Le nombre de jours de conservation.
     * @return array Le résultat de l'archivage.
     */
    public function archiverDonneesSupervision(int $joursConservation = 90): array;

    /**
     * Exporte les données de supervision.
     * @param array $criteres Les critères d'export.
     * @param string $format Le format d'export (CSV, JSON, PDF).
     * @return string Le chemin du fichier exporté.
     */
    public function exporterDonneesSupervision(array $criteres, string $format = 'JSON'): string;
}