<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Delegation;
use App\Backend\Service\Interface\TransitionRoleServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceTransitionRole implements TransitionRoleServiceInterface
{
    private PDO $pdo;
    private Delegation $delegationModel;

    public function __construct(PDO $pdo, Delegation $delegationModel)
    {
        $this->pdo = $pdo;
        $this->delegationModel = $delegationModel;
    }

    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): string
    {
        $idDelegation = 'DEL-' . uniqid(); // Utiliser IdentifiantGenerator en production
        $donnees = [
            'id_delegation' => $idDelegation,
            'id_delegant' => $idDelegant,
            'id_delegue' => $idDelegue,
            'id_traitement' => $idTraitement,
            'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
            'date_fin' => $dateFin->format('Y-m-d H:i:s'),
            'statut' => 'Active',
        ];
        $this->delegationModel->creer($donnees);
        return $idDelegation;
    }

    public function revoquerDelegation(string $idDelegation): bool
    {
        $delegation = $this->delegationModel->trouverParIdentifiant($idDelegation) ?: throw new ElementNonTrouveException("Délégation non trouvée.");
        if ($delegation['statut'] === 'Révoquée') {
            return true;
        }
        return $this->delegationModel->mettreAJourParIdentifiant($idDelegation, ['statut' => 'Révoquée']);
    }

    public function listerDelegationsActives(?string $idDelegue = null, ?string $idDelegant = null): array
    {
        $sql = "SELECT * FROM delegation 
                WHERE statut = 'Active' 
                AND NOW() BETWEEN date_debut AND date_fin";

        $params = [];
        if ($idDelegue) {
            $sql .= " AND id_delegue = :id_delegue";
            $params[':id_delegue'] = $idDelegue;
        }
        if ($idDelegant) {
            $sql .= " AND id_delegant = :id_delegant";
            $params[':id_delegant'] = $idDelegant;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPermissionsDeleguees(string $numeroUtilisateur): array
    {
        $delegations = $this->listerDelegationsActives($numeroUtilisateur);
        return array_column($delegations, 'id_traitement');
    }

    public function detecterTachesOrphelines(string $numeroUtilisateurPartant): array
    {
        // Logique complexe, exemple pour les réclamations
        $sql = "SELECT id_reclamation as id_tache, 'Reclamation' as type_tache 
                FROM reclamation 
                WHERE numero_personnel_traitant = :user_id 
                AND id_statut_reclamation NOT IN ('REC_CLOTUREE', 'REC_REJETEE')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $numeroUtilisateurPartant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reassignerTache(string $idTache, string $typeTache, string $idNouvelUtilisateur): bool
    {
        $tableMap = ['Reclamation' => 'reclamation'];
        $columnMap = ['Reclamation' => 'numero_personnel_traitant'];
        $idColumnMap = ['Reclamation' => 'id_reclamation'];

        if (!isset($tableMap[$typeTache])) {
            throw new \InvalidArgumentException("Type de tâche non supporté pour la réassignation.");
        }

        $table = $tableMap[$typeTache];
        $colonne = $columnMap[$typeTache];
        $idColonne = $idColumnMap[$typeTache];

        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE {$table} SET {$colonne} = :new_user WHERE {$idColonne} = :id_tache";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':new_user' => $idNouvelUtilisateur, ':id_tache' => $idTache]);
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Échec de la réassignation de la tâche : " . $e->getMessage());
        }
    }

    public function historiserChangementDePoste(string $numeroEnseignant, string $idAncienneFonction, string $idNouvelleFonction, \DateTimeInterface $dateChangement): void
    {
        $this->pdo->beginTransaction();
        try {
            // Clôturer l'ancienne fonction
            $sql_update = "UPDATE occuper SET date_fin_occupation = :date_changement 
                           WHERE numero_enseignant = :num_ens 
                           AND id_fonction = :id_ancienne_fonction 
                           AND date_fin_occupation IS NULL";
            $stmt_update = $this->pdo->prepare($sql_update);
            $stmt_update->execute([
                ':date_changement' => $dateChangement->format('Y-m-d'),
                ':num_ens' => $numeroEnseignant,
                ':id_ancienne_fonction' => $idAncienneFonction
            ]);

            // Créer la nouvelle
            $sql_insert = "INSERT INTO occuper (id_fonction, numero_enseignant, date_debut_occupation) 
                           VALUES (:id_nouvelle_fonction, :num_ens, :date_changement)";
            $stmt_insert = $this->pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':id_nouvelle_fonction' => $idNouvelleFonction,
                ':num_ens' => $numeroEnseignant,
                ':date_changement' => $dateChangement->format('Y-m-d')
            ]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Échec de l'historisation du changement de poste : " . $e->getMessage());
        }
    }
}