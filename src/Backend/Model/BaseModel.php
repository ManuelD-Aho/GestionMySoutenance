<?php
// src/Models/BaseModel.php
namespace App\Models;

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
        $cols   = array_keys($data);
        $sets   = implode(' = ?, ', $cols) . ' = ?';

        $sql    = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?";
        $stmt   = $this->db->prepare($sql);
        $values = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        $sql  = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}


// src/Models/EtudiantModel.php
namespace App\Models;

class EtudiantModel extends BaseModel
{
    protected string $table      = 'etudiants';
    protected string $primaryKey = 'num_etd';
}


// src/Models/RapportEtudiantModel.php
namespace App\Models;

class RapportEtudiantModel extends BaseModel
{
    protected string $table      = 'rapport_etudiant';
    protected string $primaryKey = 'id_rapport_etd';

    /**
     * Récupère les rapports n'ayant pas encore reçu de validation.
     */
    public function findPending(): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} NOT IN (SELECT {$this->primaryKey} FROM valider)";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


// src/Models/EnseignantModel.php
namespace App\Models;

class EnseignantModel extends BaseModel
{
    protected string $table      = 'enseignants';
    protected string $primaryKey = 'id_ens';
}

