<?php
// src/Backend/Service/Supervision/ServiceSupervision.php

namespace App\Backend\Service\Supervision;

use App\Backend\Exception\ElementNonTrouveException;
use PDO;
use App\Backend\Model\GenericModel;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Exception\OperationImpossibleException;
use Random\RandomException; // Importez cette classe

class ServiceSupervision implements ServiceSupervisionInterface
{
    private PDO $db;
    private GenericModel $enregistrerModel;
    private GenericModel $pisterModel;
    private GenericModel $actionModel;
    private GenericModel $queueJobsModel;
    private Utilisateur $utilisateurModel;
    private RapportEtudiant $rapportEtudiantModel;

    public function __construct(
        PDO $db,
        GenericModel $enregistrerModel,
        GenericModel $pisterModel,
        GenericModel $actionModel,
        GenericModel $queueJobsModel,
        Utilisateur $utilisateurModel,
        RapportEtudiant $rapportEtudiantModel
    ) {
        $this->db = $db;
        $this->enregistrerModel = $enregistrerModel;
        $this->pisterModel = $pisterModel;
        $this->actionModel = $actionModel;
        $this->queueJobsModel = $queueJobsModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
    }

    /**
     * Enregistre une action utilisateur dans le journal d'audit.
     * L'approche est "auto-réparatrice" : si l'action n'existe pas, elle est créée.
     * La méthode est conçue pour ne jamais bloquer l'exécution principale.
     */
    public function enregistrerAction(
        string $numeroUtilisateur,
        string $idAction,
        ?string $idEntiteConcernee = null,
        ?string $typeEntiteConcernee = null,
        array $detailsJson = []
    ): bool {
        if (empty($numeroUtilisateur) || empty($idAction)) {
            error_log("ServiceSupervision::enregistrerAction - Paramètres obligatoires manquants.");
            return false;
        }
        try {
            $this->db->beginTransaction();
            $action = $this->actionModel->trouverParIdentifiant($idAction);
            if (!$action) {
                $this->actionModel->creer([
                    'id_action' => $idAction,
                    'libelle_action' => ucwords(strtolower(str_replace('_', ' ', $idAction))),
                    'categorie_action' => 'Dynamique'
                ]);
            }

            // Correction: Utiliser random_bytes pour une meilleure unicité de l'ID
            try {
                $idEnregistrement = bin2hex(random_bytes(16)); // Génère un ID de 32 caractères hexadécimaux
            } catch (RandomException $e) {
                // Fallback si random_bytes échoue (très rare)
                $idEnregistrement = uniqid('LOG_', true);
                error_log("ServiceSupervision::enregistrerAction - random_bytes failed, falling back to uniqid: " . $e->getMessage());
            }

            $data = [
                'id_enregistrement' => $idEnregistrement, // Utilisation du nouvel ID
                'numero_utilisateur' => $numeroUtilisateur,
                'id_action' => $idAction,
                'date_action' => date('Y-m-d H:i:s'),
                'adresse_ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'id_entite_concernee' => $idEntiteConcernee,
                'type_entite_concernee' => $typeEntiteConcernee,
                'details_action' => !empty($detailsJson) ? json_encode($detailsJson, JSON_UNESCAPED_UNICODE) : null,
                'session_id_utilisateur' => session_id() ?: null
            ];

            // Log l'ID généré et les données pour l'insertion
            error_log("DEBUG ServiceSupervision: Tentative d'insertion dans 'enregistrer' avec id_enregistrement: " . $idEnregistrement . " et numero_utilisateur: " . $numeroUtilisateur);
            error_log("DEBUG ServiceSupervision: Données complètes pour 'enregistrer': " . json_encode($data));


            $result = $this->enregistrerModel->creer($data);
            $this->db->commit();
            return (bool) $result;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            // Log l'erreur pour le débogage
            error_log("ServiceSupervision::enregistrerAction a échoué : " . $e->getMessage());
            // Ne pas relancer l'exception pour ne pas bloquer l'exécution principale
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ServiceSupervision::enregistrerAction a échoué : " . $e->getMessage());
            return false;
        }
    }

    public function pisterAcces(string $numeroUtilisateur, string $idTraitement): bool
    {
        try {
            // Correction: Utiliser random_bytes pour une meilleure unicité de l'ID
            try {
                $idPiste = bin2hex(random_bytes(16));
            } catch (RandomException $e) {
                $idPiste = uniqid('PISTE_', true);
                error_log("ServiceSupervision::pisterAcces - random_bytes failed, falling back to uniqid: " . $e->getMessage());
            }

            $data = [
                'id_piste' => $idPiste, // Utilisation du nouvel ID
                'numero_utilisateur' => $numeroUtilisateur,
                'id_traitement' => $idTraitement,
                'date_pister' => date('Y-m-d H:i:s'),
                'acceder' => 1
            ];
            return (bool) $this->pisterModel->creer($data);
        } catch (\Exception $e) {
            error_log("ServiceSupervision::pisterAcces a échoué : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Consulte les journaux d'audit, enrichis avec les informations des entités liées.
     * Cette version inclut le nom de l'étudiant ou du personnel si l'entité concernée est un rapport.
     */
    public function consulterJournaux(array $filtres = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT
                    enr.*,
                    act.libelle_action,
                    usr.login_utilisateur,
                    -- Enrichissement contextuel de l'entité concernée
                    CASE
                        WHEN enr.type_entite_concernee = 'RapportEtudiant' THEN CONCAT(etu.prenom, ' ', etu.nom)
                        WHEN enr.type_entite_concernee = 'Utilisateur' THEN CONCAT(u_conc.prenom, ' ', u_conc.nom)
                        ELSE NULL
                    END as nom_entite_concernee
                FROM `enregistrer` enr
                LEFT JOIN `action` act ON enr.id_action = act.id_action
                LEFT JOIN `utilisateur` usr ON enr.numero_utilisateur = usr.numero_utilisateur
                -- Jointure pour récupérer le nom de l'étudiant si l'entité est un rapport
                LEFT JOIN `rapport_etudiant` rap ON enr.id_entite_concernee = rap.id_rapport_etudiant AND enr.type_entite_concernee = 'RapportEtudiant'
                LEFT JOIN `etudiant` etu ON rap.numero_carte_etudiant = etu.numero_carte_etudiant
                -- Jointure pour récupérer le nom si l'entité est un utilisateur (ex: création de compte)
                LEFT JOIN `utilisateur` u_conc ON enr.id_entite_concernee = u_conc.numero_utilisateur AND enr.type_entite_concernee = 'Utilisateur'
                ";

        $params = [];
        if (!empty($filtres)) {
            $whereParts = [];
            foreach ($filtres as $key => $value) {
                if ($key === 'search') {
                    $search = '%' . $value . '%';
                    $whereParts[] = "(enr.id_enregistrement LIKE :search OR enr.numero_utilisateur LIKE :search OR act.libelle_action LIKE :search OR enr.id_entite_concernee LIKE :search OR enr.type_entite_concernee LIKE :search OR usr.login_utilisateur LIKE :search)";
                    $params[':search'] = $search;
                } else {
                    $whereParts[] = "enr.`{$key}` = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            $sql .= " WHERE " . implode(" AND ", $whereParts);
        }

        $sql .= " ORDER BY enr.date_action DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function reconstituerHistoriqueEntite(string $idEntite): array
    {
        return $this->enregistrerModel->trouverParCritere(
            ['id_entite_concernee' => $idEntite],
            ['*'],
            'AND',
            'date_action ASC'
        );
    }

    // ====================================================================
    // SECTION 2 : Maintenance & Supervision Technique
    // ====================================================================

    public function purgerAnciensJournaux(string $dateLimite): int
    {
        $stmt = $this->db->prepare("DELETE FROM enregistrer WHERE date_action < :date_limite");
        $stmt->execute([':date_limite' => $dateLimite]);
        $rowCount = $stmt->rowCount();
        $this->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'PURGE_LOGS', null, null, ['date_limite' => $dateLimite, 'lignes_supprimees' => $rowCount]);
        return $rowCount;
    }

    public function consulterJournauxErreurs(string $logFilePath): string
    {
        if (!file_exists($logFilePath) || !is_readable($logFilePath)) {
            throw new OperationImpossibleException("Le fichier de log est introuvable ou illisible.");
        }
        $content = file($logFilePath);
        return implode("", array_slice($content, -500));
    }

    public function listerTachesAsynchrones(array $filtres = []): array
    {
        return $this->queueJobsModel->trouverParCritere($filtres, ['*'], 'AND', 'created_at DESC');
    }

    public function gererTacheAsynchrone(string $idTache, string $action): bool
    {
        $tache = $this->queueJobsModel->trouverParIdentifiant($idTache);
        if (!$tache) throw new ElementNonTrouveException("Tâche non trouvée.");

        switch ($action) {
            case 'relancer':
                $nouvelleTache = [
                    'job_name' => $tache['job_name'],
                    'payload' => $tache['payload'],
                    'status' => 'pending',
                    'attempts' => 0
                ];
                $this->queueJobsModel->creer($nouvelleTache);
                return $this->queueJobsModel->mettreAJourParIdentifiant($idTache, ['status' => 'failed_retried']);

            case 'supprimer':
                return $this->queueJobsModel->supprimerParIdentifiant($idTache);

            default:
                throw new OperationImpossibleException("Action '{$action}' non reconnue.");
        }
    }

    /**
     * Génère un ensemble complet de statistiques pour le tableau de bord de l'administrateur.
     */
    public function genererStatistiquesDashboardAdmin(): array
    {
        $stats = [];

        $stmtUsers = $this->db->query("SELECT statut_compte, COUNT(*) as count FROM utilisateur GROUP BY statut_compte");
        $stats['utilisateurs'] = $stmtUsers->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['utilisateurs']['total'] = array_sum($stats['utilisateurs']);

        $stmtRapports = $this->db->query("
            SELECT s.libelle_statut_rapport, COUNT(r.id_rapport_etudiant) as count
            FROM statut_rapport_ref s
            LEFT JOIN rapport_etudiant r ON s.id_statut_rapport = r.id_statut_rapport
            GROUP BY s.id_statut_rapport
        ");
        $stats['rapports'] = $stmtRapports->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtQueue = $this->db->query("SELECT status, COUNT(*) as count FROM queue_jobs GROUP BY status");
        $stats['queue'] = $stmtQueue->fetchAll(PDO::FETCH_KEY_PAIR);

        $dateLimite = (new \DateTime())->modify('-7 days')->format('Y-m-d H:i:s');
        $stmtActivity = $this->db->prepare("
            SELECT id_action, COUNT(*) as count
            FROM enregistrer
            WHERE date_action >= :date_limite
            GROUP BY id_action
        ");
        $stmtActivity->bindParam(':date_limite', $dateLimite);
        $stmtActivity->execute();
        $stats['activite_recente'] = $stmtActivity->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtReclamations = $this->db->query("
            SELECT s.libelle_statut_reclamation, COUNT(r.id_reclamation) as count
            FROM statut_reclamation_ref s
            LEFT JOIN reclamation r ON s.id_statut_reclamation = r.id_statut_reclamation
            GROUP BY s.id_statut_reclamation
        ");
        $stats['reclamations'] = $stmtReclamations->fetchAll(PDO::FETCH_KEY_PAIR);

        return $stats;
    }
}