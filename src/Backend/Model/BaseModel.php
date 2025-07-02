<?php
// src/Backend/Model/BaseModel.php

namespace App\Backend\Model;

use PDO;
use PDOStatement;
use App\Backend\Exception\DoublonException;

abstract class BaseModel
{
    public string $table; // Changé de protected à public
    protected string|array $primaryKey;
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    public function getTable(): string // Changé de protected à public
    {
        return $this->table;
    }

    public function getClePrimaire(): array|string
    {
        return $this->primaryKey;
    }

    public function trouverTout(array $colonnes = ['*']): array
    {
        $cols = implode(', ', $colonnes);
        $stmt = $this->db->query("SELECT {$cols} FROM `{$this->table}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouverParIdentifiant(int|string $id, array $colonnes = ['*']): ?array
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("Cette méthode ne supporte pas les clés primaires composites. Utilisez trouverUnParCritere().");
        }
        $cols = implode(', ', $colonnes);
        $stmt = $this->db->prepare("SELECT {$cols} FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $op = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = implode(', ', $colonnes);
        $sql = "SELECT {$cols} FROM `{$this->table}`";
        $params = [];

        if (!empty($criteres)) {
            $whereParts = [];
            foreach ($criteres as $key => $value) {
                if (is_array($value) && isset($value['operator'])) {
                    $whereParts[] = "`{$key}` {$value['operator']} :{$key}";
                    $params[":{$key}"] = $value['value'];
                } else {
                    $whereParts[] = "`{$key}` = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            $sql .= " WHERE " . implode(" {$op} ", $whereParts);
        }

        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit !== null) $sql .= " LIMIT {$limit}";
        if ($offset !== null) $sql .= " OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouverUnParCritere(array $criteres, array $colonnes = ['*'], string $op = 'AND'): ?array
    {
        $result = $this->trouverParCritere($criteres, $colonnes, $op, null, 1);
        return $result[0] ?? null;
    }

    public function creer(array $donnees): string|bool
    {
        $cols = implode('`, `', array_keys($donnees));
        $placeholders = ':' . implode(', :', array_keys($donnees));
        $sql = "INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        try {
            if ($stmt->execute($donnees)) {
                if (is_string($this->primaryKey) && !isset($donnees[$this->primaryKey])) {
                    return $this->db->lastInsertId();
                }
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10); // Récupère la pile d'appels
                $caller = $backtrace[1] ?? ['function' => 'unknown', 'class' => 'unknown', 'file' => 'unknown', 'line' => 'unknown'];
                error_log("Doublon détecté lors de la création dans {$this->table} (appelé par {$caller['class']}::{$caller['function']} à {$caller['file']}:{$caller['line']}): " . $e->getMessage());
                throw new DoublonException("Une entrée avec des attributs uniques similaires existe déjà.");
            }
            error_log("Erreur PDO lors de la création dans {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function mettreAJourParIdentifiant(int|string $id, array $donnees): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("Cette méthode ne supporte pas les clés primaires composites.");
        }
        $setParts = [];
        foreach ($donnees as $key => $value) {
            $setParts[] = "`{$key}` = :{$key}";
        }
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts) . " WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        $donnees['id'] = $id;
        return $stmt->execute($donnees);
    }

    public function supprimerParIdentifiant(int|string $id): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("Cette méthode ne supporte pas les clés primaires composites.");
        }
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function commencerTransaction(): void { $this->db->beginTransaction(); }
    public function validerTransaction(): void { $this->db->commit(); }
    public function annulerTransaction(): void { $this->db->rollBack(); }
}