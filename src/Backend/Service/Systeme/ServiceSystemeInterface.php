<?php

namespace App\Backend\Service\Systeme;

interface ServiceSystemeInterface
{
    /**
     * Récupère les informations système globales.
     * @return array Les informations système.
     */
    public function obtenirInformationsSysteme(): array;

    /**
     * Vérifie l'état de santé du système.
     * @return array L'état de santé détaillé.
     */
    public function verifierEtatSante(): array;

    /**
     * Effectue la maintenance automatique du système.
     * @return array Le résultat de la maintenance.
     */
    public function effectuerMaintenanceAutomatique(): array;

    /**
     * Sauvegarde la base de données.
     * @param string $typeBackup Le type de sauvegarde (COMPLET, INCREMENTAL).
     * @return string Le chemin de la sauvegarde créée.
     */
    public function sauvegarderBaseDonnees(string $typeBackup = 'COMPLET'): string;

    /**
     * Optimise les performances du système.
     * @return array Le résultat de l'optimisation.
     */
    public function optimiserPerformances(): array;

    /**
     * Nettoie les fichiers temporaires et logs anciens.
     * @param int $joursConservation Le nombre de jours de conservation.
     * @return array Le résultat du nettoyage.
     */
    public function nettoyerFichiersTemporaires(int $joursConservation = 30): array;

    /**
     * Génère un rapport d'activité système.
     * @param string $periode La période (JOUR, SEMAINE, MOIS).
     * @param string|null $dateDebut La date de début (optionnel).
     * @param string|null $dateFin La date de fin (optionnel).
     * @return array Le rapport d'activité.
     */
    public function genererRapportActivite(string $periode, ?string $dateDebut = null, ?string $dateFin = null): array;

    /**
     * Configure les paramètres de performance.
     * @param array $parametres Les paramètres à configurer.
     * @return bool Vrai si la configuration a réussi.
     */
    public function configurerParametresPerformance(array $parametres): bool;

    /**
     * Surveille l'utilisation des ressources.
     * @return array Les métriques d'utilisation.
     */
    public function surveillerRessources(): array;

    /**
     * Redémarre les services système.
     * @param array $services Les services à redémarrer.
     * @return bool Vrai si le redémarrage a réussi.
     */
    public function redemarrerServices(array $services): bool;

    /**
     * Configure les alertes système.
     * @param array $configurationAlertes La configuration des alertes.
     * @return bool Vrai si la configuration a réussi.
     */
    public function configurerAlertes(array $configurationAlertes): bool;
}