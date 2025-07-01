<?php
// src/Backend/Model/GenericModel.php

namespace App\Backend\Model;

use PDO;

class GenericModel extends BaseModel
{
    public function __construct(PDO $db, string $table, string|array $primaryKey)
    {
        parent::__construct($db);
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    public function getClePrimaire(): array|string
    {
        return $this->primaryKey;
    }

    /**
     * Met à jour les lignes correspondant aux clés fournies.
     */
    public function mettreAJourParCles(array $cles, array $donnees): bool
    {
        $setParts = [];
        $params = [];
        foreach ($donnees as $col => $val) {
            $setParts[] = "$col = :set_$col";
            $params[":set_$col"] = $val;
        }
        $whereParts = [];
        foreach ($cles as $col => $val) {
            $whereParts[] = "$col = :where_$col";
            $params[":where_$col"] = $val;
        }
        $sql = "UPDATE {$this->table} SET ".implode(', ', $setParts)." WHERE ".implode(' AND ', $whereParts);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime les lignes correspondant aux clés fournies.
     */
    public function supprimerParCles(array $cles): bool
    {
        $whereParts = [];
        $params = [];
        foreach ($cles as $col => $val) {
            $whereParts[] = "$col = :$col";
            $params[":$col"] = $val;
        }
        $sql = "DELETE FROM {$this->table} WHERE ".implode(' AND ', $whereParts);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}