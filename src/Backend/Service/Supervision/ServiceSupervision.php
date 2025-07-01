<?php
// src/Backend/Service/Supervision/ServiceSupervision.php

namespace App\Backend\Service\Supervision;

use PDO;
use App\Backend\Model\GenericModel;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\RapportEtudiant;

class ServiceSupervision implements ServiceSupervisionInterface
{
    private PDO $db;
    private GenericModel $enregistrerModel;
    private GenericModel $pisterModel;
    private GenericModel $actionModel;
    private Utilisateur $utilisateurModel;
    private RapportEtudiant $rapportEtudiantModel;

    public function __construct(
        PDO $db,
        GenericModel $enregistrerModel,
        GenericModel $pisterModel,
        GenericModel $actionModel,
        Utilisateur $utilisateurModel,
        RapportEtudiant $rapportEtudiantModel
    ) {
        $this->db = $db;
        $this->enregistrerModel = $enregistrerModel;
        $this->pisterModel = $pisterModel;
        $this->actionModel = $actionModel;
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
            if (!$action)
                $this->actionModel->creer([
                    'id_action' => $idAction,
                    'libelle_action' => ucwords(strtolower(str_replace('_', ' ', $idAction))),
                    'categorie_action' => 'Dynamique'
                ]);
            $data = [
                'id_enregistrement' => uniqid('LOG_'),
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

            $result = $this->enregistrerModel->creer($data);
            $this->db->commit();
            return (bool) $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("ServiceSupervision::enregistrerAction a échoué : " . $e->getMessage());
            return false;
        }
    }

    public function pisterAcces(string $numeroUtilisateur, string $idTraitement): bool
    {
        try {
            $data = [
                'id_piste' => uniqid('PISTE_'),
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
                LEFT JOIN `etudiant` u_conc ON enr.id_entite_concernee = u_conc.numero_utilisateur AND enr.type_entite_concernee = 'Utilisateur'
                ";

        $params = [];
        if (!empty($filtres)) {
            $whereParts = [];
            foreach ($filtres as $key => $value) {
                $whereParts[] = "enr.`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
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

    /**
     * Génère un ensemble complet de statistiques pour le tableau de bord de l'administrateur.
     */
    public function genererStatistiquesDashboardAdmin(): array
    {
        $stats = [];

        // 1. Statistiques sur les utilisateurs
        $stmtUsers = $this->db->query("SELECT statut_compte, COUNT(*) as count FROM utilisateur GROUP BY statut_compte");
        $stats['utilisateurs'] = $stmtUsers->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['utilisateurs']['total'] = array_sum($stats['utilisateurs']);

        // 2. Statistiques sur les rapports
        $stmtRapports = $this->db->query("
            SELECT s.libelle_statut_rapport, COUNT(r.id_rapport_etudiant) as count 
            FROM statut_rapport_ref s
            LEFT JOIN rapport_etudiant r ON s.id_statut_rapport = r.id_statut_rapport
            GROUP BY s.id_statut_rapport
        ");
        $stats['rapports'] = $stmtRapports->fetchAll(PDO::FETCH_KEY_PAIR);

        // 3. Statistiques sur les tâches en attente
        $stmtQueue = $this->db->query("SELECT status, COUNT(*) as count FROM queue_jobs GROUP BY status");
        $stats['queue'] = $stmtQueue->fetchAll(PDO::FETCH_KEY_PAIR);

        // 4. Statistiques sur l'activité récente (7 derniers jours)
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

        // 5. Statistiques sur les réclamations
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