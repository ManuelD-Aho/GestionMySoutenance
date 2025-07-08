<?php

namespace App\Backend\Service\Delegation;

use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use PDO;

class ServiceDelegation implements ServiceDelegationInterface
{
    private PDO $db;
    private ServiceSupervisionInterface $supervisionService;
    private ServiceSecuriteInterface $securiteService;

    public function __construct(
        PDO $db,
        ServiceSupervisionInterface $supervisionService,
        ServiceSecuriteInterface $securiteService
    ) {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->securiteService = $securiteService;
    }

    public function countActiveDelegations(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM delegation 
            WHERE statut = 'ACTIVE' 
            AND date_debut <= NOW() 
            AND date_fin >= NOW()
        ");
        return (int)$stmt->fetchColumn();
    }

    public function countExpiredDelegations(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM delegation 
            WHERE date_fin < NOW() 
            AND statut != 'EXPIRED'
        ");
        return (int)$stmt->fetchColumn();
    }

    public function countOrphanTasks(): int
    {
        // Exemple : compter les rapports assignés à des utilisateurs inactifs
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM rapport_etudiant r
            JOIN utilisateur u ON r.assigne_a = u.numero_utilisateur
            WHERE u.statut_compte = 'INACTIF'
        ");
        return (int)$stmt->fetchColumn();
    }

    public function countAbsentUsers(): int
    {
        // Utilisateurs qui n'ont pas de connexion depuis 30 jours
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT u.numero_utilisateur) 
            FROM utilisateur u
            LEFT JOIN pister p ON u.numero_utilisateur = p.numero_utilisateur 
                AND p.date_pister > DATE_SUB(NOW(), INTERVAL 30 DAY)
            WHERE u.statut_compte = 'ACTIF' 
            AND p.numero_utilisateur IS NULL
        ");
        return (int)$stmt->fetchColumn();
    }

    public function getRecentDelegations(int $limit): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                u1.nom as delegateur_nom,
                u1.prenom as delegateur_prenom,
                u2.nom as delegataire_nom,
                u2.prenom as delegataire_prenom
            FROM delegation d
            JOIN utilisateur u1 ON d.delegateur_id = u1.numero_utilisateur
            JOIN utilisateur u2 ON d.delegataire_id = u2.numero_utilisateur
            ORDER BY d.date_creation DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDelegations(array $filters = []): array
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($filters['statut'])) {
            $whereClause .= " AND d.statut = :statut";
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['delegateur'])) {
            $whereClause .= " AND d.delegateur_id = :delegateur";
            $params['delegateur'] = $filters['delegateur'];
        }

        if (!empty($filters['delegataire'])) {
            $whereClause .= " AND d.delegataire_id = :delegataire";
            $params['delegataire'] = $filters['delegataire'];
        }

        if (!empty($filters['date_debut'])) {
            $whereClause .= " AND d.date_debut >= :date_debut";
            $params['date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $whereClause .= " AND d.date_fin <= :date_fin";
            $params['date_fin'] = $filters['date_fin'];
        }

        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                u1.nom as delegateur_nom,
                u1.prenom as delegateur_prenom,
                u2.nom as delegataire_nom,
                u2.prenom as delegataire_prenom
            FROM delegation d
            JOIN utilisateur u1 ON d.delegateur_id = u1.numero_utilisateur
            JOIN utilisateur u2 ON d.delegataire_id = u2.numero_utilisateur
            $whereClause
            ORDER BY d.date_creation DESC
        ");

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDelegation(string $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                u1.nom as delegateur_nom,
                u1.prenom as delegateur_prenom,
                u2.nom as delegataire_nom,
                u2.prenom as delegataire_prenom
            FROM delegation d
            JOIN utilisateur u1 ON d.delegateur_id = u1.numero_utilisateur
            JOIN utilisateur u2 ON d.delegataire_id = u2.numero_utilisateur
            WHERE d.id_delegation = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createDelegation(array $data): string
    {
        $delegationId = 'DEL-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO delegation (
                id_delegation, delegateur_id, delegataire_id, 
                date_debut, date_fin, motif, permissions_deleguees,
                restrictions, statut, date_creation
            ) VALUES (
                :id_delegation, :delegateur_id, :delegataire_id,
                :date_debut, :date_fin, :motif, :permissions_deleguees,
                :restrictions, :statut, NOW()
            )
        ");

        $stmt->execute([
            'id_delegation' => $delegationId,
            'delegateur_id' => $data['delegateur_id'],
            'delegataire_id' => $data['delegataire_id'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'motif' => $data['motif'],
            'permissions_deleguees' => json_encode($data['permissions_deleguees']),
            'restrictions' => $data['restrictions'] ?? '',
            'statut' => $data['auto_activate'] ? 'ACTIVE' : 'INACTIVE'
        ]);

        return $delegationId;
    }

    public function updateDelegation(string $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE delegation SET
                date_debut = :date_debut,
                date_fin = :date_fin,
                motif = :motif,
                permissions_deleguees = :permissions_deleguees,
                restrictions = :restrictions,
                statut = :statut
            WHERE id_delegation = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'motif' => $data['motif'],
            'permissions_deleguees' => json_encode($data['permissions_deleguees']),
            'restrictions' => $data['restrictions'] ?? '',
            'statut' => $data['statut']
        ]);
    }

    public function deleteDelegation(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM delegation WHERE id_delegation = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function activateDelegation(string $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE delegation 
            SET statut = 'ACTIVE', date_activation = NOW() 
            WHERE id_delegation = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    public function deactivateDelegation(string $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE delegation 
            SET statut = 'INACTIVE', date_desactivation = NOW() 
            WHERE id_delegation = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    public function detectOrphanTasks(string $userId): array
    {
        // Détecter les tâches/rapports orphelins pour un utilisateur
        $stmt = $this->db->prepare("
            SELECT 
                'rapport' as type_tache,
                r.id_rapport_etudiant as id_tache,
                r.libelle_rapport_etudiant as titre_tache,
                r.date_creation,
                r.id_statut_rapport
            FROM rapport_etudiant r
            WHERE r.assigne_a = :user_id
            AND r.id_statut_rapport IN ('RAP_SOUMIS', 'RAP_EN_COMMISSION')
            
            UNION ALL
            
            SELECT 
                'pv' as type_tache,
                pv.id_compte_rendu as id_tache,
                CONCAT('PV Session ', pv.date_creation) as titre_tache,
                pv.date_creation,
                pv.id_statut_pv
            FROM compte_rendu pv
            WHERE pv.redacteur_id = :user_id
            AND pv.id_statut_pv = 'PV_BROUILLON'
        ");

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reassignTask(string $taskId, string $newAssigneeId, string $reason): bool
    {
        // Réassigner une tâche à un nouvel utilisateur
        // Cette implémentation dépend du type de tâche

        // Pour les rapports
        $stmt = $this->db->prepare("
            UPDATE rapport_etudiant 
            SET assigne_a = :new_assignee, 
                reassign_reason = :reason,
                reassign_date = NOW()
            WHERE id_rapport_etudiant = :task_id
        ");

        return $stmt->execute([
            'task_id' => $taskId,
            'new_assignee' => $newAssigneeId,
            'reason' => $reason
        ]);
    }
    public function revoquerDelegation(string $idDelegation): bool
    {
        $delegation = $this->getDelegation($idDelegation);
        if (!$delegation) {
            throw new ElementNonTrouveException("Délégation non trouvée.");
        }

        $stmt = $this->db->prepare("UPDATE delegation SET statut = 'Révoquée' WHERE id_delegation = :id");
        $success = $stmt->execute(['id' => $idDelegation]);

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'REVOCATION_DELEGATION',
                $idDelegation,
                'Delegation'
            );
        }

        return $success;
    }
}