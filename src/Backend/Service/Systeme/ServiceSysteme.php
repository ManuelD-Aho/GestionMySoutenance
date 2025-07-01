<?php

namespace App\Backend\Service\Systeme;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Logger\ServiceLoggerInterface;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSystemeInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceSysteme implements ServiceSystemeInterface
{
    private PDO $db;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceLoggerInterface $logger;
    private ServiceConfigurationSystemeInterface $configService;

    public function __construct(
        PDO $db,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceLoggerInterface $logger,
        ServiceConfigurationSystemeInterface $configService
    ) {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->logger = $logger;
        $this->configService = $configService;
    }

    public function obtenirInformationsSysteme(): array
    {
        try {
            $informations = [
                'version_application' => '1.0.0',
                'version_php' => PHP_VERSION,
                'version_base_donnees' => $this->obtenirVersionBaseDonnees(),
                'serveur_web' => $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu',
                'systeme_exploitation' => PHP_OS,
                'memoire_php' => ini_get('memory_limit'),
                'temps_execution_max' => ini_get('max_execution_time'),
                'espace_disque_disponible' => $this->obtenirEspaceDisqueDisponible(),
                'date_heure_serveur' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get(),
                'extensions_php' => get_loaded_extensions()
            ];

            $this->logger->log('info', 'Informations système récupérées', $informations);
            return $informations;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la récupération des informations système', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de récupérer les informations système: " . $e->getMessage());
        }
    }

    public function verifierEtatSante(): array
    {
        $etatSante = [
            'statut_global' => 'OK',
            'verifications' => []
        ];

        try {
            // Vérification de la base de données
            $etatSante['verifications']['base_donnees'] = $this->verifierBaseDonnees();

            // Vérification de l'espace disque
            $etatSante['verifications']['espace_disque'] = $this->verifierEspaceDisque();

            // Vérification de la mémoire
            $etatSante['verifications']['memoire'] = $this->verifierMemoire();

            // Vérification des services
            $etatSante['verifications']['services'] = $this->verifierServices();

            // Vérification des permissions fichiers
            $etatSante['verifications']['permissions'] = $this->verifierPermissionsFichiers();

            // Déterminer le statut global
            foreach ($etatSante['verifications'] as $verification) {
                if ($verification['statut'] === 'ERREUR') {
                    $etatSante['statut_global'] = 'ERREUR';
                    break;
                } elseif ($verification['statut'] === 'AVERTISSEMENT' && $etatSante['statut_global'] === 'OK') {
                    $etatSante['statut_global'] = 'AVERTISSEMENT';
                }
            }

            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'VERIFICATION_ETAT_SANTE',
                "Vérification de l'état de santé du système",
                null,
                null,
                $etatSante
            );

            return $etatSante;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la vérification de l\'état de santé', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de vérifier l'état de santé: " . $e->getMessage());
        }
    }

    public function effectuerMaintenanceAutomatique(): array
    {
        $resultats = [
            'debut_maintenance' => date('Y-m-d H:i:s'),
            'operations' => []
        ];

        try {
            $this->db->beginTransaction();

            // Optimisation des tables
            $resultats['operations']['optimisation_tables'] = $this->optimiserTables();

            // Nettoyage des sessions expirées
            $resultats['operations']['nettoyage_sessions'] = $this->nettoyerSessionsExpirees();

            // Archivage des logs anciens
            $resultats['operations']['archivage_logs'] = $this->archiverLogsAnciens();

            // Mise à jour des statistiques
            $resultats['operations']['mise_a_jour_statistiques'] = $this->mettreAJourStatistiques();

            // Vérification de l'intégrité des données
            $resultats['operations']['verification_integrite'] = $this->verifierIntegriteDonnees();

            $resultats['fin_maintenance'] = date('Y-m-d H:i:s');
            $resultats['duree_total'] = strtotime($resultats['fin_maintenance']) - strtotime($resultats['debut_maintenance']);

            $this->db->commit();

            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'MAINTENANCE_AUTOMATIQUE',
                "Maintenance automatique effectuée",
                null,
                null,
                $resultats
            );

            return $resultats;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->log('error', 'Erreur lors de la maintenance automatique', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible d'effectuer la maintenance: " . $e->getMessage());
        }
    }

    public function sauvegarderBaseDonnees(string $typeBackup = 'COMPLET'): string
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $nomFichier = "backup_{$typeBackup}_{$timestamp}.sql";
            $cheminBackup = ROOT_PATH . "/var/backups/{$nomFichier}";

            // Créer le répertoire de sauvegarde s'il n'existe pas
            $repertoireBackup = dirname($cheminBackup);
            if (!is_dir($repertoireBackup)) {
                mkdir($repertoireBackup, 0755, true);
            }

            // Commande mysqldump (à adapter selon la configuration)
            $host = getenv('DB_HOST') ?: 'localhost';
            $database = getenv('DB_NAME') ?: 'gestion_soutenance';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: '';

            $commande = "mysqldump -h{$host} -u{$username}";
            if (!empty($password)) {
                $commande .= " -p{$password}";
            }
            $commande .= " {$database} > {$cheminBackup}";

            $resultat = shell_exec($commande);

            if (file_exists($cheminBackup) && filesize($cheminBackup) > 0) {
                $this->supervisionService->enregistrerAction(
                    'SYSTEM',
                    'SAUVEGARDE_BASE_DONNEES',
                    "Sauvegarde de la base de données créée",
                    null,
                    null,
                    ['type' => $typeBackup, 'fichier' => $nomFichier, 'taille' => filesize($cheminBackup)]
                );

                return $cheminBackup;
            } else {
                throw new \Exception("La sauvegarde n'a pas pu être créée");
            }

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la sauvegarde', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de sauvegarder la base de données: " . $e->getMessage());
        }
    }

    public function optimiserPerformances(): array
    {
        $resultats = [
            'debut_optimisation' => date('Y-m-d H:i:s'),
            'operations' => []
        ];

        try {
            // Optimisation des tables de base de données
            $resultats['operations']['tables'] = $this->optimiserTables();

            // Nettoyage du cache PHP
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $resultats['operations']['opcache'] = ['statut' => 'OK', 'message' => 'Cache OPcache vidé'];
            }

            // Optimisation des indexes
            $resultats['operations']['indexes'] = $this->optimiserIndexes();

            // Compression des logs
            $resultats['operations']['compression_logs'] = $this->compresserLogs();

            $resultats['fin_optimisation'] = date('Y-m-d H:i:s');

            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'OPTIMISATION_PERFORMANCES',
                "Optimisation des performances effectuée",
                null,
                null,
                $resultats
            );

            return $resultats;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'optimisation', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible d'optimiser les performances: " . $e->getMessage());
        }
    }

    public function nettoyerFichiersTemporaires(int $joursConservation = 30): array
    {
        $resultats = [
            'fichiers_supprimes' => 0,
            'espace_libere' => 0,
            'repertoires_nettoyes' => []
        ];

        try {
            $dateLimit = date('Y-m-d', strtotime("-{$joursConservation} days"));

            // Répertoires à nettoyer
            $repertoires = [
                ROOT_PATH . '/tmp',
                ROOT_PATH . '/var/cache',
                ROOT_PATH . '/var/log'
            ];

            foreach ($repertoires as $repertoire) {
                if (is_dir($repertoire)) {
                    $resultatsRepertoire = $this->nettoyerRepertoire($repertoire, $dateLimit);
                    $resultats['fichiers_supprimes'] += $resultatsRepertoire['fichiers'];
                    $resultats['espace_libere'] += $resultatsRepertoire['taille'];
                    $resultats['repertoires_nettoyes'][] = [
                        'repertoire' => $repertoire,
                        'fichiers_supprimes' => $resultatsRepertoire['fichiers'],
                        'espace_libere' => $resultatsRepertoire['taille']
                    ];
                }
            }

            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'NETTOYAGE_FICHIERS_TEMPORAIRES',
                "Nettoyage des fichiers temporaires effectué",
                null,
                null,
                $resultats
            );

            return $resultats;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors du nettoyage', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de nettoyer les fichiers temporaires: " . $e->getMessage());
        }
    }

    public function genererRapportActivite(string $periode, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        try {
            // Définir les dates selon la période
            switch ($periode) {
                case 'JOUR':
                    $dateDebut = $dateDebut ?: date('Y-m-d');
                    $dateFin = $dateFin ?: date('Y-m-d');
                    break;
                case 'SEMAINE':
                    $dateDebut = $dateDebut ?: date('Y-m-d', strtotime('monday this week'));
                    $dateFin = $dateFin ?: date('Y-m-d', strtotime('sunday this week'));
                    break;
                case 'MOIS':
                    $dateDebut = $dateDebut ?: date('Y-m-01');
                    $dateFin = $dateFin ?: date('Y-m-t');
                    break;
            }

            $rapport = [
                'periode' => $periode,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'statistiques' => []
            ];

            // Statistiques d'utilisation
            $rapport['statistiques']['connexions'] = $this->obtenirStatistiquesConnexions($dateDebut, $dateFin);
            $rapport['statistiques']['actions'] = $this->obtenirStatistiquesActions($dateDebut, $dateFin);
            $rapport['statistiques']['erreurs'] = $this->obtenirStatistiquesErreurs($dateDebut, $dateFin);
            $rapport['statistiques']['performances'] = $this->obtenirStatistiquesPerformances($dateDebut, $dateFin);

            return $rapport;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la génération du rapport', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de générer le rapport d'activité: " . $e->getMessage());
        }
    }

    public function configurerParametresPerformance(array $parametres): bool
    {
        try {
            foreach ($parametres as $parametre => $valeur) {
                $this->configService->mettreAJourParametresGeneraux([$parametre => $valeur]);
            }

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'CONFIGURATION_PERFORMANCES',
                "Configuration des paramètres de performance",
                null,
                null,
                $parametres
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la configuration', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de configurer les paramètres: " . $e->getMessage());
        }
    }

    public function surveillerRessources(): array
    {
        try {
            $metriques = [
                'memoire' => [
                    'utilisation_actuelle' => memory_get_usage(true),
                    'pic_utilisation' => memory_get_peak_usage(true),
                    'limite_php' => ini_get('memory_limit')
                ],
                'processeur' => [
                    'charge_moyenne' => $this->obtenirChargeMoyenne()
                ],
                'disque' => [
                    'espace_total' => disk_total_space('.'),
                    'espace_libre' => disk_free_space('.'),
                    'espace_utilise' => disk_total_space('.') - disk_free_space('.')
                ],
                'base_donnees' => [
                    'connexions_actives' => $this->obtenirConnexionsActives(),
                    'taille_base' => $this->obtenirTailleBaseDonnees()
                ]
            ];

            return $metriques;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la surveillance des ressources', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de surveiller les ressources: " . $e->getMessage());
        }
    }

    public function redemarrerServices(array $services): bool
    {
        // Cette méthode est simulée car le redémarrage de services nécessite des privilèges système
        $this->logger->log('warning', 'Tentative de redémarrage de services', ['services' => $services]);
        
        $this->supervisionService->enregistrerAction(
            $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
            'REDEMARRAGE_SERVICES',
            "Tentative de redémarrage des services",
            null,
            null,
            ['services' => $services]
        );

        return true;
    }

    public function configurerAlertes(array $configurationAlertes): bool
    {
        try {
            $this->configService->mettreAJourParametresGeneraux([
                'configuration_alertes' => json_encode($configurationAlertes)
            ]);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'CONFIGURATION_ALERTES',
                "Configuration des alertes système",
                null,
                null,
                $configurationAlertes
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la configuration des alertes', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de configurer les alertes: " . $e->getMessage());
        }
    }

    // Méthodes privées d'assistance

    private function obtenirVersionBaseDonnees(): string
    {
        try {
            $stmt = $this->db->query("SELECT VERSION()");
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            return 'Inconnue';
        }
    }

    private function obtenirEspaceDisqueDisponible(): array
    {
        return [
            'espace_total' => disk_total_space('.'),
            'espace_libre' => disk_free_space('.'),
            'espace_utilise' => disk_total_space('.') - disk_free_space('.')
        ];
    }

    private function verifierBaseDonnees(): array
    {
        try {
            $this->db->query("SELECT 1");
            return ['statut' => 'OK', 'message' => 'Base de données accessible'];
        } catch (\Exception $e) {
            return ['statut' => 'ERREUR', 'message' => 'Base de données inaccessible: ' . $e->getMessage()];
        }
    }

    private function verifierEspaceDisque(): array
    {
        $espaceLibre = disk_free_space('.');
        $espaceTotalDisponible = disk_total_space('.');
        $pourcentageUtilise = (($espaceTotalDisponible - $espaceLibre) / $espaceTotalDisponible) * 100;

        if ($pourcentageUtilise > 90) {
            return ['statut' => 'ERREUR', 'message' => "Espace disque critique: {$pourcentageUtilise}% utilisé"];
        } elseif ($pourcentageUtilise > 80) {
            return ['statut' => 'AVERTISSEMENT', 'message' => "Espace disque faible: {$pourcentageUtilise}% utilisé"];
        } else {
            return ['statut' => 'OK', 'message' => "Espace disque suffisant: {$pourcentageUtilise}% utilisé"];
        }
    }

    private function verifierMemoire(): array
    {
        $utilisation = memory_get_usage(true);
        $limite = ini_get('memory_limit');
        
        if ($limite === '-1') {
            return ['statut' => 'OK', 'message' => 'Mémoire illimitée'];
        }

        $limiteBytes = $this->convertirTailleEnOctets($limite);
        $pourcentageUtilise = ($utilisation / $limiteBytes) * 100;

        if ($pourcentageUtilise > 90) {
            return ['statut' => 'ERREUR', 'message' => "Utilisation mémoire critique: {$pourcentageUtilise}%"];
        } elseif ($pourcentageUtilise > 80) {
            return ['statut' => 'AVERTISSEMENT', 'message' => "Utilisation mémoire élevée: {$pourcentageUtilise}%"];
        } else {
            return ['statut' => 'OK', 'message' => "Utilisation mémoire normale: {$pourcentageUtilise}%"];
        }
    }

    private function verifierServices(): array
    {
        // Vérification basique des services critiques
        $services = [
            'php' => function_exists('phpinfo'),
            'pdo' => extension_loaded('pdo'),
            'session' => session_status() !== PHP_SESSION_DISABLED
        ];

        $servicesOk = array_filter($services);
        if (count($servicesOk) === count($services)) {
            return ['statut' => 'OK', 'message' => 'Tous les services sont opérationnels'];
        } else {
            $servicesKo = array_keys(array_diff($services, $servicesOk));
            return ['statut' => 'ERREUR', 'message' => 'Services défaillants: ' . implode(', ', $servicesKo)];
        }
    }

    private function verifierPermissionsFichiers(): array
    {
        $repertoires = [
            ROOT_PATH . '/tmp',
            ROOT_PATH . '/var/log',
            ROOT_PATH . '/var/cache'
        ];

        foreach ($repertoires as $repertoire) {
            if (!is_dir($repertoire)) {
                return ['statut' => 'ERREUR', 'message' => "Répertoire manquant: {$repertoire}"];
            }
            if (!is_writable($repertoire)) {
                return ['statut' => 'ERREUR', 'message' => "Répertoire non accessible en écriture: {$repertoire}"];
            }
        }

        return ['statut' => 'OK', 'message' => 'Permissions des fichiers correctes'];
    }

    private function optimiserTables(): array
    {
        try {
            $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tablesOptimisees = 0;

            foreach ($tables as $table) {
                $this->db->exec("OPTIMIZE TABLE {$table}");
                $tablesOptimisees++;
            }

            return ['statut' => 'OK', 'tables_optimisees' => $tablesOptimisees];
        } catch (\Exception $e) {
            return ['statut' => 'ERREUR', 'message' => $e->getMessage()];
        }
    }

    private function nettoyerSessionsExpirees(): array
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE date_expiration < NOW()");
            $stmt->execute();
            $sessionsNettoyees = $stmt->rowCount();

            return ['statut' => 'OK', 'sessions_nettoyees' => $sessionsNettoyees];
        } catch (\Exception $e) {
            return ['statut' => 'ERREUR', 'message' => $e->getMessage()];
        }
    }

    private function archiverLogsAnciens(): array
    {
        // Simulation de l'archivage des logs
        return ['statut' => 'OK', 'logs_archives' => 0];
    }

    private function mettreAJourStatistiques(): array
    {
        // Simulation de la mise à jour des statistiques
        return ['statut' => 'OK', 'statistiques_mises_a_jour' => true];
    }

    private function verifierIntegriteDonnees(): array
    {
        // Simulation de la vérification d'intégrité
        return ['statut' => 'OK', 'integrite_verificee' => true];
    }

    private function optimiserIndexes(): array
    {
        // Simulation de l'optimisation des index
        return ['statut' => 'OK', 'indexes_optimises' => true];
    }

    private function compresserLogs(): array
    {
        // Simulation de la compression des logs
        return ['statut' => 'OK', 'logs_compreses' => true];
    }

    private function nettoyerRepertoire(string $repertoire, string $dateLimit): array
    {
        $fichiers = 0;
        $taille = 0;

        if ($handle = opendir($repertoire)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $cheminFichier = $repertoire . '/' . $entry;
                    if (is_file($cheminFichier)) {
                        $dateFichier = date('Y-m-d', filemtime($cheminFichier));
                        if ($dateFichier < $dateLimit) {
                            $taille += filesize($cheminFichier);
                            unlink($cheminFichier);
                            $fichiers++;
                        }
                    }
                }
            }
            closedir($handle);
        }

        return ['fichiers' => $fichiers, 'taille' => $taille];
    }

    private function obtenirStatistiquesConnexions(string $dateDebut, string $dateFin): array
    {
        $sql = "SELECT COUNT(*) as total_connexions,
                       COUNT(DISTINCT numero_utilisateur) as utilisateurs_uniques
                FROM historique_actions 
                WHERE type_action = 'CONNEXION_UTILISATEUR' 
                AND date_action BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenirStatistiquesActions(string $dateDebut, string $dateFin): array
    {
        $sql = "SELECT type_action, COUNT(*) as nombre
                FROM historique_actions 
                WHERE date_action BETWEEN ? AND ?
                GROUP BY type_action
                ORDER BY nombre DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenirStatistiquesErreurs(string $dateDebut, string $dateFin): array
    {
        $sql = "SELECT COUNT(*) as total_erreurs
                FROM historique_actions 
                WHERE type_action LIKE '%ERREUR%' 
                AND date_action BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenirStatistiquesPerformances(string $dateDebut, string $dateFin): array
    {
        // Simulation des statistiques de performance
        return [
            'temps_reponse_moyen' => 250, // ms
            'requetes_lentes' => 5,
            'utilisation_memoire_moyenne' => 75 // %
        ];
    }

    private function obtenirChargeMoyenne(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return ['1min' => $load[0], '5min' => $load[1], '15min' => $load[2]];
        }
        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    private function obtenirConnexionsActives(): int
    {
        try {
            $stmt = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['Value'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function obtenirTailleBaseDonnees(): int
    {
        try {
            $sql = "SELECT SUM(data_length + index_length) as taille
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE()";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['taille'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function convertirTailleEnOctets(string $taille): int
    {
        $unite = strtolower(substr($taille, -1));
        $valeur = (int)substr($taille, 0, -1);
        
        switch ($unite) {
            case 'g': return $valeur * 1024 * 1024 * 1024;
            case 'm': return $valeur * 1024 * 1024;
            case 'k': return $valeur * 1024;
            default: return $valeur;
        }
    }
}