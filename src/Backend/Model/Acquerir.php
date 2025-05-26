<?php

namespace Backend\Model;

use PDO; // Keep PDO for type hinting in custom methods if needed
use Backend\Model\BaseModel;

class Acquerir extends BaseModel {

    protected string $table = 'acquerir';
    protected string $primaryKey = 'id_grade'; // First part of composite key

    // Constructor is removed, parent::__construct is called by BaseModel's constructor implicitly

    // findAll() is inherited.
    // create($data) is inherited. $data should contain id_grade, id_enseignant, date_acquisition.
    // find($id), update($id, $data), delete($id) from BaseModel might not work as expected due to composite key.

    /**
     * Récupère un enregistrement par sa clé composite.
     */
    public function findByCompositeKey(int $id_grade, int $id_enseignant): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_grade = :id_grade AND id_enseignant = :id_enseignant";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_grade' => $id_grade, 'id_enseignant' => $id_enseignant]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    /**
     * Met à jour un enregistrement basé sur sa clé composite.
     * $data dovrebbe contenere i campi da aggiornare, ad esempio ['date_acquisition' => 'nouvelle_date']
     */
    public function updateByCompositeKey(int $id_grade, int $id_enseignant, array $data): bool
    {
        $cols = array_keys($data);
        $setString = implode(', ', array_map(fn($col) => "{$col} = :{$col}", $cols));

        $sql = "UPDATE {$this->table} SET {$setString} WHERE id_grade = :pk_id_grade AND id_enseignant = :pk_id_enseignant";
        
        // Add composite keys to data array for binding
        $data['pk_id_grade'] = $id_grade; 
        $data['pk_id_enseignant'] = $id_enseignant;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Supprime un enregistrement par sa clé composite.
     */
    public function deleteByCompositeKey(int $id_grade, int $id_enseignant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_grade = :id_grade AND id_enseignant = :id_enseignant";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_grade' => $id_grade, 'id_enseignant' => $id_enseignant]);
    }
}