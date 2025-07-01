<?php

namespace App\Backend\Service\Supervision;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Logger\ServiceLoggerInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceSupervision implements ServiceSupervisionInterface
{
    private PDO $db;
    private ServiceSupervisionAdminInterface $supervisionAdminService;
    private ServiceLoggerInterface $logger;
    private ServiceNotificationInterface $notificationService;

    private array $seuils = [
        'cpu_usage' => ['warning' => 80, 'critical' => 95],
        'memory_usage' => ['warning' => 85, 'critical' => 95],
        'disk_usage' => ['warning' => 85, 'critical' => 95],
        'response_time' => ['warning' => 1000, 'critical' => 3000],
        'error_rate' => ['warning' => 5, 'critical' => 10],
        'active_sessions' => ['warning' => 1000, 'critical' => 1500]
    ];

    public function __construct(
        PDO $db,
        ServiceSupervisionAdminInterface $supervisionAdminService,
        ServiceLoggerInterface $logger,
        ServiceNotificationInterface $notificationService
    ) {
        $this->db = $db;
        $this->supervisionAdminService = $supervisionAdminService;
        $this->logger = $logger;
        $this->notificationService = $notificationService;
    }

    public function surveillerPerformancesTempsReel(): array
    {
        try {
            $metriques = [
                'timestamp' => date('Y-m-d H:i:s'),
                'systeme' => $this->obtenirMetriquesSysteme(),
                'application' => $this->obtenirMetriquesApplication(),
                'base_donnees' => $this->obtenirMetriquesBaseDonnees(),
                'utilisateurs' => $this->obtenirMetriquesUtilisateurs()
            ];

            // Enregistrer les métriques
            $this->enregistrerMetriquesEnBase($metriques);

            return $metriques;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la surveillance des performances', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de surveiller les performances: " . $e->getMessage());
        }
    }

    public function genererRapportSupervision(string $periode, array $metriques = []): array
    {
        try {
            $datesFin = $this->calculerDatesRapport($periode);
            
            $rapport = [
                'periode' => $periode,
                'date_debut' => $datesFin['debut'],
                'date_fin' => $datesFin['fin'],
                'date_generation' => date('Y-m-d H:i:s'),
                'resume_executif' => $this->genererResumeExecutif($datesFin['debut'], $datesFin['fin']),
                'metriques_detaillees' => []
            ];

            $metriquesAInclure = !empty($metriques) ? $metriques : [
                'performances', 'utilisation_ressources', 'activite_utilisateurs', 
                'erreurs', 'disponibilite', 'securite'
            ];

            foreach ($metriquesAInclure as $metrique) {
                $rapport['metriques_detaillees'][$metrique] = $this->obtenirMetriqueDetaillee(
                    $metrique, 
                    $datesFin['debut'], 
                    $datesFin['fin']
                );
            }

            $rapport['recommandations'] = $this->genererRecommandations($rapport);
            $rapport['alertes'] = $this->obtenirAlertesRapport($datesFin['debut'], $datesFin['fin']);

            $this->supervisionAdminService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'GENERATION_RAPPORT_SUPERVISION',
                "Génération d'un rapport de supervision",
                null,
                null,
                ['periode' => $periode, 'metriques' => $metriquesAInclure]
            );

            return $rapport;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la génération du rapport', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de générer le rapport: " . $e->getMessage());
        }
    }

    public function configurerSeuilsAlerte(array $seuils): bool
    {
        try {
            foreach ($seuils as $metrique => $valeurs) {
                if (isset($this->seuils[$metrique])) {
                    $this->seuils[$metrique] = array_merge($this->seuils[$metrique], $valeurs);
                } else {
                    $this->seuils[$metrique] = $valeurs;
                }
            }

            // Sauvegarder les seuils en base de données
            $sql = "INSERT INTO supervision_seuils (metrique, seuils, date_modification) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE seuils = VALUES(seuils), date_modification = NOW()";
            
            $stmt = $this->db->prepare($sql);
            foreach ($this->seuils as $metrique => $valeurs) {
                $stmt->execute([$metrique, json_encode($valeurs)]);
            }

            $this->supervisionAdminService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'CONFIGURATION_SEUILS_ALERTE',
                "Configuration des seuils d'alerte",
                null,
                null,
                $seuils
            );

            return true;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la configuration des seuils', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de configurer les seuils: " . $e->getMessage());
        }
    }

    public function verifierEtDeclencherAlertes(): array
    {
        try {
            $alertesDeClenchees = [];
            $metriquesActuelles = $this->surveillerPerformancesTempsReel();

            foreach ($this->seuils as $nomMetrique => $seuils) {
                $valeurActuelle = $this->extraireValeurMetrique($metriquesActuelles, $nomMetrique);
                
                if ($valeurActuelle === null) {
                    continue;
                }

                $niveauAlerte = $this->determinerNiveauAlerte($valeurActuelle, $seuils);
                
                if ($niveauAlerte !== null) {
                    $alerte = [
                        'metrique' => $nomMetrique,
                        'valeur_actuelle' => $valeurActuelle,
                        'niveau' => $niveauAlerte,
                        'seuil_depasse' => $seuils[$niveauAlerte],
                        'timestamp' => date('Y-m-d H:i:s'),
                        'message' => $this->genererMessageAlerte($nomMetrique, $valeurActuelle, $niveauAlerte)
                    ];

                    $alertesDeClenchees[] = $alerte;
                    $this->traiterAlerte($alerte);
                }
            }

            return $alertesDeClenchees;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la vérification des alertes', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de vérifier les alertes: " . $e->getMessage());
        }
    }

    public function enregistrerMetrique(string $nomMetrique, mixed $valeur, array $contexte = []): bool
    {
        try {
            $sql = "INSERT INTO supervision_metriques (nom_metrique, valeur, contexte, timestamp) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$nomMetrique, json_encode($valeur), json_encode($contexte)]);

            if ($result) {
                $this->logger->log('debug', "Métrique enregistrée: {$nomMetrique}", ['valeur' => $valeur, 'contexte' => $contexte]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'enregistrement de métrique', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function obtenirHistoriqueMetrique(string $nomMetrique, string $dateDebut, string $dateFin, string $granularite = 'HEURE'): array
    {
        try {
            $formatDate = match ($granularite) {
                'MINUTE' => '%Y-%m-%d %H:%i:00',
                'HEURE' => '%Y-%m-%d %H:00:00',
                'JOUR' => '%Y-%m-%d 00:00:00',
                default => '%Y-%m-%d %H:00:00'
            };

            $sql = "SELECT 
                        DATE_FORMAT(timestamp, ?) as periode,
                        AVG(JSON_EXTRACT(valeur, '$')) as valeur_moyenne,
                        MIN(JSON_EXTRACT(valeur, '$')) as valeur_min,
                        MAX(JSON_EXTRACT(valeur, '$')) as valeur_max,
                        COUNT(*) as nombre_mesures
                    FROM supervision_metriques 
                    WHERE nom_metrique = ? 
                    AND timestamp BETWEEN ? AND ?
                    GROUP BY periode
                    ORDER BY periode";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$formatDate, $nomMetrique, $dateDebut, $dateFin]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la récupération de l\'historique', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de récupérer l'historique: " . $e->getMessage());
        }
    }

    public function surveillerRessourcesSysteme(): array
    {
        try {
            $ressources = [
                'cpu' => $this->obtenirUtilisationCPU(),
                'memoire' => $this->obtenirUtilisationMemoire(),
                'disque' => $this->obtenirUtilisationDisque(),
                'reseau' => $this->obtenirUtilisationReseau(),
                'processus' => $this->obtenirInfoProcessus()
            ];

            // Enregistrer les métriques
            foreach ($ressources as $type => $donnees) {
                $this->enregistrerMetrique("systeme_{$type}", $donnees);
            }

            return $ressources;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la surveillance des ressources', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de surveiller les ressources: " . $e->getMessage());
        }
    }

    public function surveillerConnexionsUtilisateurs(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as sessions_actives,
                        COUNT(DISTINCT numero_utilisateur) as utilisateurs_uniques,
                        AVG(TIMESTAMPDIFF(MINUTE, date_creation, NOW())) as duree_moyenne_session,
                        MAX(date_creation) as derniere_connexion
                    FROM sessions 
                    WHERE date_expiration > NOW()";

            $stmt = $this->db->query($sql);
            $donnees = $stmt->fetch(PDO::FETCH_ASSOC);

            // Ajouter des statistiques supplémentaires
            $donnees['connexions_derniere_heure'] = $this->compterConnexionsDerniereHeure();
            $donnees['repartition_par_type'] = $this->obtenirRepartitionUtilisateursParType();

            $this->enregistrerMetrique('connexions_utilisateurs', $donnees);

            return $donnees;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la surveillance des connexions', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de surveiller les connexions: " . $e->getMessage());
        }
    }

    public function surveillerErreursApplication(): array
    {
        try {
            $erreurs = [
                'erreurs_derniere_heure' => $this->compterErreursDerniereHeure(),
                'erreurs_par_type' => $this->obtenirErreursParType(),
                'erreurs_critiques' => $this->compterErreursCritiques(),
                'taux_erreur' => $this->calculerTauxErreur(),
                'erreurs_frequentes' => $this->obtenirErreursFrequentes()
            ];

            $this->enregistrerMetrique('erreurs_application', $erreurs);

            return $erreurs;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la surveillance des erreurs', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de surveiller les erreurs: " . $e->getMessage());
        }
    }

    public function genererRecommandationsOptimisation(): array
    {
        try {
            $metriques = $this->surveillerPerformancesTempsReel();
            $recommandations = [];

            // Analyser l'utilisation CPU
            if ($metriques['systeme']['cpu']['utilisation'] > 80) {
                $recommandations[] = [
                    'type' => 'PERFORMANCE',
                    'priorite' => 'HAUTE',
                    'titre' => 'Utilisation CPU élevée',
                    'description' => 'L\'utilisation CPU dépasse 80%. Considérez l\'optimisation des requêtes ou l\'ajout de ressources.',
                    'actions_suggeres' => [
                        'Analyser les requêtes lentes',
                        'Optimiser les algorithmes',
                        'Ajouter des ressources CPU'
                    ]
                ];
            }

            // Analyser l'utilisation mémoire
            if ($metriques['systeme']['memoire']['pourcentage_utilise'] > 85) {
                $recommandations[] = [
                    'type' => 'MEMOIRE',
                    'priorite' => 'MOYENNE',
                    'titre' => 'Utilisation mémoire élevée',
                    'description' => 'L\'utilisation mémoire dépasse 85%. Vérifiez les fuites mémoire.',
                    'actions_suggeres' => [
                        'Analyser les fuites mémoire',
                        'Optimiser le cache',
                        'Augmenter la mémoire disponible'
                    ]
                ];
            }

            // Analyser les erreurs
            $erreurs = $this->surveillerErreursApplication();
            if ($erreurs['taux_erreur'] > 5) {
                $recommandations[] = [
                    'type' => 'QUALITE',
                    'priorite' => 'HAUTE',
                    'titre' => 'Taux d\'erreur élevé',
                    'description' => 'Le taux d\'erreur dépasse 5%. Analysez les erreurs récurrentes.',
                    'actions_suggeres' => [
                        'Analyser les logs d\'erreur',
                        'Corriger les bugs identifiés',
                        'Améliorer la gestion d\'erreurs'
                    ]
                ];
            }

            return $recommandations;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de la génération des recommandations', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible de générer les recommandations: " . $e->getMessage());
        }
    }

    public function archiverDonneesSupervision(int $joursConservation = 90): array
    {
        try {
            $dateArchivage = date('Y-m-d', strtotime("-{$joursConservation} days"));
            
            $resultats = [
                'date_archivage' => $dateArchivage,
                'donnees_archivees' => []
            ];

            // Archiver les métriques anciennes
            $sql = "SELECT COUNT(*) as count FROM supervision_metriques WHERE timestamp < ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateArchivage]);
            $metriquesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($metriquesCount > 0) {
                // Créer une archive
                $this->creerArchiveMetriques($dateArchivage);
                
                // Supprimer les anciennes données
                $sql = "DELETE FROM supervision_metriques WHERE timestamp < ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$dateArchivage]);
                
                $resultats['donnees_archivees']['metriques'] = $metriquesCount;
            }

            // Archiver les alertes anciennes
            $sql = "SELECT COUNT(*) as count FROM supervision_alertes WHERE date_creation < ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateArchivage]);
            $alertesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($alertesCount > 0) {
                $sql = "DELETE FROM supervision_alertes WHERE date_creation < ? AND statut = 'RESOLUE'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$dateArchivage]);
                
                $resultats['donnees_archivees']['alertes'] = $alertesCount;
            }

            $this->supervisionAdminService->enregistrerAction(
                'SYSTEM',
                'ARCHIVAGE_DONNEES_SUPERVISION',
                "Archivage des données de supervision",
                null,
                null,
                $resultats
            );

            return $resultats;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'archivage', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible d'archiver les données: " . $e->getMessage());
        }
    }

    public function exporterDonneesSupervision(array $criteres, string $format = 'JSON'): string
    {
        try {
            $donnees = $this->recupererDonneesExport($criteres);
            
            $timestamp = date('Y-m-d_H-i-s');
            $nomFichier = "supervision_export_{$timestamp}";

            switch (strtoupper($format)) {
                case 'JSON':
                    $cheminFichier = $this->exporterJSON($donnees, $nomFichier);
                    break;
                case 'CSV':
                    $cheminFichier = $this->exporterCSV($donnees, $nomFichier);
                    break;
                case 'PDF':
                    $cheminFichier = $this->exporterPDF($donnees, $nomFichier);
                    break;
                default:
                    throw new \InvalidArgumentException("Format d'export non supporté: {$format}");
            }

            $this->supervisionAdminService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'EXPORT_DONNEES_SUPERVISION',
                "Export des données de supervision",
                null,
                null,
                ['format' => $format, 'fichier' => basename($cheminFichier)]
            );

            return $cheminFichier;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'export', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Impossible d'exporter les données: " . $e->getMessage());
        }
    }

    // Méthodes privées d'assistance

    private function obtenirMetriquesSysteme(): array
    {
        return [
            'cpu' => $this->obtenirUtilisationCPU(),
            'memoire' => $this->obtenirUtilisationMemoire(),
            'disque' => $this->obtenirUtilisationDisque()
        ];
    }

    private function obtenirMetriquesApplication(): array
    {
        return [
            'temps_reponse_moyen' => $this->calculerTempsReponseMoyen(),
            'requetes_par_seconde' => $this->calculerRequetesParSeconde(),
            'cache_hit_ratio' => $this->calculerCacheHitRatio()
        ];
    }

    private function obtenirMetriquesBaseDonnees(): array
    {
        try {
            $sql = "SHOW STATUS LIKE 'Threads_connected'";
            $stmt = $this->db->query($sql);
            $connexions = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;

            return [
                'connexions_actives' => (int)$connexions,
                'requetes_lentes' => $this->compterRequetesLentes(),
                'taille_base' => $this->obtenirTailleBaseDonnees()
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function obtenirMetriquesUtilisateurs(): array
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM sessions WHERE date_expiration > NOW()";
            $stmt = $this->db->query($sql);
            $sessionsActives = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return [
                'sessions_actives' => (int)$sessionsActives,
                'connexions_derniere_heure' => $this->compterConnexionsDerniereHeure()
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function obtenirUtilisationCPU(): array
    {
        $load = sys_getloadavg();
        return [
            'load_1min' => $load[0] ?? 0,
            'load_5min' => $load[1] ?? 0,
            'load_15min' => $load[2] ?? 0,
            'utilisation' => min(($load[0] ?? 0) * 100 / 4, 100) // Estimation pour 4 cœurs
        ];
    }

    private function obtenirUtilisationMemoire(): array
    {
        $memoire = [
            'utilisation_php' => memory_get_usage(true),
            'pic_utilisation_php' => memory_get_peak_usage(true),
            'limite_php' => $this->convertirTailleEnOctets(ini_get('memory_limit'))
        ];

        if ($memoire['limite_php'] > 0) {
            $memoire['pourcentage_utilise'] = ($memoire['utilisation_php'] / $memoire['limite_php']) * 100;
        } else {
            $memoire['pourcentage_utilise'] = 0;
        }

        return $memoire;
    }

    private function obtenirUtilisationDisque(): array
    {
        $total = disk_total_space('.');
        $libre = disk_free_space('.');
        $utilise = $total - $libre;

        return [
            'espace_total' => $total,
            'espace_libre' => $libre,
            'espace_utilise' => $utilise,
            'pourcentage_utilise' => $total > 0 ? ($utilise / $total) * 100 : 0
        ];
    }

    private function obtenirUtilisationReseau(): array
    {
        // Simulation - dans un vrai système, utiliser des outils système
        return [
            'bytes_envoyes' => 0,
            'bytes_recus' => 0,
            'paquets_envoyes' => 0,
            'paquets_recus' => 0
        ];
    }

    private function obtenirInfoProcessus(): array
    {
        return [
            'pid' => getmypid(),
            'utilisateur' => get_current_user(),
            'version_php' => PHP_VERSION
        ];
    }

    private function calculerTempsReponseMoyen(): float
    {
        // Simulation - calculer basé sur les logs de performance
        return 250.0; // ms
    }

    private function calculerRequetesParSeconde(): float
    {
        // Simulation - calculer basé sur les logs d'accès
        return 15.0;
    }

    private function calculerCacheHitRatio(): float
    {
        // Simulation - calculer basé sur les statistiques de cache
        return 85.0; // %
    }

    private function compterRequetesLentes(): int
    {
        try {
            $sql = "SHOW STATUS LIKE 'Slow_queries'";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['Value'] ?? 0);
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
            return (int)($result['taille'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function compterConnexionsDerniereHeure(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action = 'CONNEXION_UTILISATEUR' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->db->query($sql);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function obtenirRepartitionUtilisateursParType(): array
    {
        try {
            $sql = "SELECT tu.libelle_type_utilisateur, COUNT(*) as count
                    FROM sessions s
                    JOIN utilisateur u ON s.numero_utilisateur = u.numero_utilisateur
                    JOIN type_utilisateur tu ON u.id_type_utilisateur = tu.id_type_utilisateur
                    WHERE s.date_expiration > NOW()
                    GROUP BY tu.libelle_type_utilisateur";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function compterErreursDerniereHeure(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR%' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->db->query($sql);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function obtenirErreursParType(): array
    {
        try {
            $sql = "SELECT type_action, COUNT(*) as count
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR%' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY type_action
                    ORDER BY count DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function compterErreursCritiques(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR_CRITIQUE%' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->query($sql);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculerTauxErreur(): float
    {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN type_action LIKE '%ERREUR%' THEN 1 ELSE 0 END) as erreurs,
                        COUNT(*) as total
                    FROM historique_actions 
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ($result['erreurs'] / $result['total']) * 100;
            }
            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function obtenirErreursFrequentes(): array
    {
        try {
            $sql = "SELECT description_action, COUNT(*) as count
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR%' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY description_action
                    ORDER BY count DESC
                    LIMIT 10";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function enregistrerMetriquesEnBase(array $metriques): void
    {
        try {
            $sql = "INSERT INTO supervision_snapshots (donnees_snapshot, timestamp) VALUES (?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([json_encode($metriques)]);
        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'enregistrement des métriques', ['error' => $e->getMessage()]);
        }
    }

    private function calculerDatesRapport(string $periode): array
    {
        $dateFin = date('Y-m-d H:i:s');
        
        switch ($periode) {
            case 'HEURE':
                $dateDebut = date('Y-m-d H:i:s', strtotime('-1 hour'));
                break;
            case 'JOUR':
                $dateDebut = date('Y-m-d 00:00:00');
                break;
            case 'SEMAINE':
                $dateDebut = date('Y-m-d 00:00:00', strtotime('monday this week'));
                break;
            case 'MOIS':
                $dateDebut = date('Y-m-01 00:00:00');
                break;
            default:
                $dateDebut = date('Y-m-d 00:00:00');
        }

        return ['debut' => $dateDebut, 'fin' => $dateFin];
    }

    private function genererResumeExecutif(string $dateDebut, string $dateFin): array
    {
        return [
            'disponibilite' => $this->calculerDisponibilite($dateDebut, $dateFin),
            'performance_moyenne' => $this->calculerPerformanceMoyenne($dateDebut, $dateFin),
            'incidents_majeurs' => $this->compterIncidentsMajeurs($dateDebut, $dateFin),
            'utilisation_pic' => $this->obtenirUtilisationPic($dateDebut, $dateFin)
        ];
    }

    private function obtenirMetriqueDetaillee(string $metrique, string $dateDebut, string $dateFin): array
    {
        // Récupérer les données détaillées pour chaque type de métrique
        switch ($metrique) {
            case 'performances':
                return $this->obtenirDonneesPerformances($dateDebut, $dateFin);
            case 'utilisation_ressources':
                return $this->obtenirDonneesRessources($dateDebut, $dateFin);
            case 'activite_utilisateurs':
                return $this->obtenirDonneesUtilisateurs($dateDebut, $dateFin);
            case 'erreurs':
                return $this->obtenirDonneesErreurs($dateDebut, $dateFin);
            case 'disponibilite':
                return $this->obtenirDonneesDisponibilite($dateDebut, $dateFin);
            case 'securite':
                return $this->obtenirDonneesSecurite($dateDebut, $dateFin);
            default:
                return [];
        }
    }

    private function genererRecommandations(array $rapport): array
    {
        // Analyser le rapport et générer des recommandations
        return $this->genererRecommandationsOptimisation();
    }

    private function obtenirAlertesRapport(string $dateDebut, string $dateFin): array
    {
        try {
            $sql = "SELECT * FROM supervision_alertes 
                    WHERE date_creation BETWEEN ? AND ?
                    ORDER BY niveau_alerte DESC, date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function extraireValeurMetrique(array $metriques, string $nomMetrique): ?float
    {
        // Extraire la valeur d'une métrique spécifique depuis les données de surveillance
        switch ($nomMetrique) {
            case 'cpu_usage':
                return $metriques['systeme']['cpu']['utilisation'] ?? null;
            case 'memory_usage':
                return $metriques['systeme']['memoire']['pourcentage_utilise'] ?? null;
            case 'disk_usage':
                return $metriques['systeme']['disque']['pourcentage_utilise'] ?? null;
            case 'response_time':
                return $metriques['application']['temps_reponse_moyen'] ?? null;
            case 'error_rate':
                return $this->calculerTauxErreur();
            case 'active_sessions':
                return $metriques['utilisateurs']['sessions_actives'] ?? null;
            default:
                return null;
        }
    }

    private function determinerNiveauAlerte(float $valeur, array $seuils): ?string
    {
        if (isset($seuils['critical']) && $valeur >= $seuils['critical']) {
            return 'critical';
        }
        if (isset($seuils['warning']) && $valeur >= $seuils['warning']) {
            return 'warning';
        }
        return null;
    }

    private function genererMessageAlerte(string $metrique, float $valeur, string $niveau): string
    {
        $messages = [
            'cpu_usage' => "Utilisation CPU à {$valeur}% (niveau {$niveau})",
            'memory_usage' => "Utilisation mémoire à {$valeur}% (niveau {$niveau})",
            'disk_usage' => "Utilisation disque à {$valeur}% (niveau {$niveau})",
            'response_time' => "Temps de réponse de {$valeur}ms (niveau {$niveau})",
            'error_rate' => "Taux d'erreur de {$valeur}% (niveau {$niveau})",
            'active_sessions' => "{$valeur} sessions actives (niveau {$niveau})"
        ];

        return $messages[$metrique] ?? "Alerte {$niveau} pour {$metrique}: {$valeur}";
    }

    private function traiterAlerte(array $alerte): void
    {
        try {
            // Enregistrer l'alerte en base
            $sql = "INSERT INTO supervision_alertes (metrique, valeur, niveau_alerte, message, date_creation, statut) 
                    VALUES (?, ?, ?, ?, NOW(), 'ACTIVE')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $alerte['metrique'],
                $alerte['valeur_actuelle'],
                $alerte['niveau'],
                $alerte['message']
            ]);

            // Envoyer une notification selon le niveau
            if ($alerte['niveau'] === 'critical') {
                $this->notificationService->envoyerNotificationGroupe(
                    'ADMINISTRATEURS',
                    'ALERTE_CRITIQUE',
                    $alerte['message'],
                    $alerte
                );
            } else {
                $this->logger->log('warning', $alerte['message'], $alerte);
            }

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors du traitement de l\'alerte', [
                'alerte' => $alerte,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function creerArchiveMetriques(string $dateArchivage): void
    {
        // Créer un fichier d'archive des métriques anciennes
        $nomArchive = "metriques_archive_" . date('Y_m_d', strtotime($dateArchivage)) . ".json";
        $cheminArchive = ROOT_PATH . "/var/archives/{$nomArchive}";
        
        // Créer le répertoire d'archives s'il n'existe pas
        $repertoireArchive = dirname($cheminArchive);
        if (!is_dir($repertoireArchive)) {
            mkdir($repertoireArchive, 0755, true);
        }

        // Récupérer les données à archiver
        $sql = "SELECT * FROM supervision_metriques WHERE timestamp < ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateArchivage]);
        $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sauvegarder dans le fichier d'archive
        file_put_contents($cheminArchive, json_encode($donnees, JSON_PRETTY_PRINT));
    }

    private function recupererDonneesExport(array $criteres): array
    {
        // Récupérer les données selon les critères spécifiés
        $sql = "SELECT * FROM supervision_snapshots WHERE 1=1";
        $params = [];

        if (!empty($criteres['date_debut'])) {
            $sql .= " AND timestamp >= ?";
            $params[] = $criteres['date_debut'];
        }

        if (!empty($criteres['date_fin'])) {
            $sql .= " AND timestamp <= ?";
            $params[] = $criteres['date_fin'];
        }

        $sql .= " ORDER BY timestamp DESC";

        if (!empty($criteres['limite'])) {
            $sql .= " LIMIT ?";
            $params[] = $criteres['limite'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function exporterJSON(array $donnees, string $nomFichier): string
    {
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.json";
        file_put_contents($cheminFichier, json_encode($donnees, JSON_PRETTY_PRINT));
        return $cheminFichier;
    }

    private function exporterCSV(array $donnees, string $nomFichier): string
    {
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.csv";
        $handle = fopen($cheminFichier, 'w');
        
        if (!empty($donnees)) {
            // Écrire l'en-tête
            fputcsv($handle, array_keys($donnees[0]));
            
            // Écrire les données
            foreach ($donnees as $ligne) {
                fputcsv($handle, $ligne);
            }
        }
        
        fclose($handle);
        return $cheminFichier;
    }

    private function exporterPDF(array $donnees, string $nomFichier): string
    {
        // Simulation d'export PDF
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.pdf";
        file_put_contents($cheminFichier, "Export PDF des données de supervision");
        return $cheminFichier;
    }

    private function convertirTailleEnOctets(string $taille): int
    {
        if ($taille === '-1') {
            return -1;
        }
        
        $unite = strtolower(substr($taille, -1));
        $valeur = (int)substr($taille, 0, -1);
        
        switch ($unite) {
            case 'g': return $valeur * 1024 * 1024 * 1024;
            case 'm': return $valeur * 1024 * 1024;
            case 'k': return $valeur * 1024;
            default: return $valeur;
        }
    }

    // Méthodes de calcul pour le rapport détaillé
    private function calculerDisponibilite(string $dateDebut, string $dateFin): float
    {
        // Simulation - calculer basé sur les temps d'arrêt
        return 99.9;
    }

    private function calculerPerformanceMoyenne(string $dateDebut, string $dateFin): float
    {
        // Simulation - calculer basé sur les métriques de performance
        return 250.0;
    }

    private function compterIncidentsMajeurs(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM supervision_alertes 
                    WHERE niveau_alerte = 'critical' 
                    AND date_creation BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function obtenirUtilisationPic(string $dateDebut, string $dateFin): array
    {
        // Simulation - récupérer les pics d'utilisation
        return [
            'cpu_max' => 85.0,
            'memoire_max' => 78.0,
            'disque_max' => 45.0
        ];
    }

    private function obtenirDonneesPerformances(string $dateDebut, string $dateFin): array
    {
        return [
            'temps_reponse_moyen' => 250.0,
            'temps_reponse_p95' => 500.0,
            'temps_reponse_max' => 2000.0,
            'requetes_par_seconde' => 15.0
        ];
    }

    private function obtenirDonneesRessources(string $dateDebut, string $dateFin): array
    {
        return [
            'cpu_moyen' => 45.0,
            'cpu_max' => 85.0,
            'memoire_moyenne' => 60.0,
            'memoire_max' => 78.0,
            'disque_moyen' => 35.0,
            'disque_max' => 45.0
        ];
    }

    private function obtenirDonneesUtilisateurs(string $dateDebut, string $dateFin): array
    {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT numero_utilisateur) as utilisateurs_uniques,
                        COUNT(*) as connexions_totales,
                        AVG(TIMESTAMPDIFF(MINUTE, date_creation, date_expiration)) as duree_session_moyenne
                    FROM sessions 
                    WHERE date_creation BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function obtenirDonneesErreurs(string $dateDebut, string $dateFin): array
    {
        return [
            'erreurs_totales' => $this->compterErreursPeriode($dateDebut, $dateFin),
            'erreurs_critiques' => $this->compterErreursCritiquesPeriode($dateDebut, $dateFin),
            'taux_erreur_moyen' => $this->calculerTauxErreurMoyenPeriode($dateDebut, $dateFin)
        ];
    }

    private function obtenirDonneesDisponibilite(string $dateDebut, string $dateFin): array
    {
        return [
            'disponibilite' => 99.9,
            'temps_arret_total' => 0,
            'incidents' => []
        ];
    }

    private function obtenirDonneesSecurite(string $dateDebut, string $dateFin): array
    {
        return [
            'tentatives_connexion_echouees' => $this->compterTentativesConnexionEchouees($dateDebut, $dateFin),
            'alertes_securite' => $this->compterAlertesSecurite($dateDebut, $dateFin),
            'violations_acces' => $this->compterViolationsAcces($dateDebut, $dateFin)
        ];
    }

    private function compterErreursPeriode(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR%' 
                    AND date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function compterErreursCritiquesPeriode(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action LIKE '%ERREUR_CRITIQUE%' 
                    AND date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculerTauxErreurMoyenPeriode(string $dateDebut, string $dateFin): float
    {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN type_action LIKE '%ERREUR%' THEN 1 ELSE 0 END) as erreurs,
                        COUNT(*) as total
                    FROM historique_actions 
                    WHERE date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ($result['erreurs'] / $result['total']) * 100;
            }
            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function compterTentativesConnexionEchouees(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action = 'TENTATIVE_CONNEXION_ECHOUEE' 
                    AND date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function compterAlertesSecurite(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action LIKE '%SECURITE%' 
                    AND date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function compterViolationsAcces(string $dateDebut, string $dateFin): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM historique_actions 
                    WHERE type_action = 'ACCES_NON_AUTORISE' 
                    AND date_action BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }
}