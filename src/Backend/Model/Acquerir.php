<?php

namespace Backend\Model;

use PDO;
use Backend\Model\BaseModel;

class Acquerir extends BaseModel {

    protected string $table = 'acquerir';
    // Pour les clés composites, $primaryKey de BaseModel n'est pas directement utilisable
    // pour find, update, delete. On peut la laisser ou la commenter.
    // protected string $primaryKey = 'id_grade'; // Ou une autre partie, mais avec prudence

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    // findAll() est hérité et fonctionne.
    // create($data) est hérité. $data devrait contenir id_grade, id_enseignant, date_acquisition.

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
     * $data devrait contenir les champs à mettre à jour, par exemple ['date_acquisition' => 'nouvelle_date']
     */
    public function updateByCompositeKey(int $id_grade, int $id_enseignant, array $data): bool
    {
        $cols = array_keys($data);
        $setString = implode(', ', array_map(fn($col) => "{$col} = :{$col}", $cols));

        $sql = "UPDATE {$this->table} SET {$setString} WHERE id_grade = :pk_id_grade AND id_enseignant = :pk_id_enseignant";
        $stmt = $this->db->prepare($sql);

        $data['pk_id_grad'] = $id_grade; // Ajouter les clés primaires pour le binding
        $data['pk_id_enseignant'] = $id_enseignant;

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