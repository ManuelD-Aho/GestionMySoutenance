<?php
// src/Backend/Model/BaseModel.php
namespace Backend\Model; // Changement de namespace

use PDO;
use PDOException;

/**
 * BaseModel: fournit les opérations CRUD génériques pour n'importe quelle table.
 */
abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data !== false ? $data : null;
    }

    public function create(array $data): bool
    {
        $cols         = array_keys($data);
        $fields       = implode(', ', $cols);
        $placeholders = ':' . implode(', :', $cols);

        $sql  = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $updateFields = [];
        foreach (array_keys($data) as $col) {
            $updateFields[] = "{$col} = :{$col}";
        }
        $setString = implode(', ', $updateFields);

        $sql    = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :primary_key_id";
        $stmt   = $this->db->prepare($sql);

        $data['primary_key_id'] = $id; // Ajouter l'ID pour le binding
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $sql  = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}