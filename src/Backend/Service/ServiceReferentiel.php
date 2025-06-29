<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Service\Interface\ReferentielServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceReferentiel implements ReferentielServiceInterface
{
    private PDO $pdo;
    private AuditServiceInterface $auditService;

    public function __construct(PDO $pdo, AuditServiceInterface $auditService)
    {
        $this->pdo = $pdo;
        $this->auditService = $auditService;
    }

    public function creerItem(string $nomReferentiel, array $donnees): string
    {
        $this->validerNomTable($nomReferentiel);

        $colonnes = implode(', ', array_keys($donnees));
        $placeholders = ':' . implode(', :', array_keys($donnees));

        $sql = "INSERT INTO {$nomReferentiel} ({$colonnes}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($donnees);

        $id = $this->pdo->lastInsertId();
        $this->auditService->enregistrerAction('ManuelD-Aho', 'REFERENCE_ITEM_CREATED', $id, $nomReferentiel, $donnees);
        return $id;
    }

    public function mettreAJourItem(string $nomReferentiel, string $id, array $donnees): bool
    {
        $this->validerNomTable($nomReferentiel);
        $primaryKey = $this->getPrimaryKey($nomReferentiel);

        $updates = [];
        foreach ($donnees as $key => $value) {
            $updates[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $updates);

        $sql = "UPDATE {$nomReferentiel} SET {$setClause} WHERE {$primaryKey} = :pk_id";
        $stmt = $this->pdo->prepare($sql);

        $donnees['pk_id'] = $id;
        return $stmt->execute($donnees);
    }

    public function supprimerItem(string $nomReferentiel, string $id): bool
    {
        $this->validerNomTable($nomReferentiel);
        $primaryKey = $this->getPrimaryKey($nomReferentiel);

        try {
            $sql = "DELETE FROM {$nomReferentiel} WHERE {$primaryKey} = :pk_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':pk_id' => $id]);
        } catch (\PDOException $e) {
            if ($e->getCode() == '23000') { // Contrainte d'intégrité
                throw new OperationImpossibleException("Impossible de supprimer cet item car il est utilisé par d'autres entités.");
            }
            throw $e;
        }
    }

    public function listerItems(string $nomReferentiel): array
    {
        $this->validerNomTable($nomReferentiel);
        $stmt = $this->pdo->query("SELECT * FROM {$nomReferentiel}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItemParId(string $nomReferentiel, string $id): ?array
    {
        $this->validerNomTable($nomReferentiel);
        $primaryKey = $this->getPrimaryKey($nomReferentiel);

        $sql = "SELECT * FROM {$nomReferentiel} WHERE {$primaryKey} = :pk_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pk_id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    private function validerNomTable(string $nomTable): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nomTable)) {
            throw new \InvalidArgumentException("Nom de table de référentiel invalide.");
        }
    }

    private function getPrimaryKey(string $nomTable): string
    {
        // Stratégie simplifiée. Une solution robuste utiliserait `SHOW KEYS FROM ...` ou `INFORMATION_SCHEMA`.
        $stmt = $this->pdo->query("DESCRIBE {$nomTable}");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ($column['Key'] === 'PRI') {
                return $column['Field'];
            }
        }
        throw new \RuntimeException("Impossible de déterminer la clé primaire pour la table {$nomTable}.");
    }
}