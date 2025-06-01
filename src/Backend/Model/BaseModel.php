<?php

namespace App\Backend\Model;

use PDO;
use PDOStatement;
use PDOException;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $clePrimaire = 'id';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getClePrimaire(): string
    {
        return $this->clePrimaire;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function trouverTout(array $colonnes = ['*']): array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}`";
        $declaration = $this->db->query($sql);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function trouverParIdentifiant(int|string $id, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `{$this->clePrimaire}` = :id";
        $declaration = $this->db->prepare($sql);
        $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $declaration->bindParam(':id', $id, $typeParametre);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function creer(array $donnees): string|bool
    {
        $colonnes = implode(', ', array_map(fn($col) => "`$col`", array_keys($donnees)));
        $placeholders = ':' . implode(', :', array_keys($donnees));
        $sql = "INSERT INTO `{$this->table}` ({$colonnes}) VALUES ({$placeholders})";
        $declaration = $this->db->prepare($sql);
        try {
            $succes = $declaration->execute($donnees);
            if ($succes) {
                $dernierId = $this->db->lastInsertId();
                if ($dernierId && $dernierId !== "0" && $dernierId !== 0) { // Gère les chaînes et les entiers
                    return $dernierId;
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function mettreAJourParIdentifiant(int|string $id, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "`{$colonne}` = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `{$this->clePrimaire}` = :id_cle_primaire";
        $declaration = $this->db->prepare($sql);
        $parametres = $donnees;
        $parametres['id_cle_primaire'] = $id;
        try {
            return $declaration->execute($parametres);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function supprimerParIdentifiant(int|string $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->clePrimaire}` = :id";
        $declaration = $this->db->prepare($sql);
        $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $declaration->bindParam(':id', $id, $typeParametre);
        try {
            return $declaration->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}`";
        $conditions = [];
        if (!empty($criteres)) {
            foreach (array_keys($criteres) as $champ) {
                $conditions[] = "`{$champ}` = :{$champ}";
            }
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $conditions);
        }
        if ($orderBy !== null) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }
        $declaration = $this->db->prepare($sql);
        foreach ($criteres as $key => $value) {
            $typeParam = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $declaration->bindValue(":$key", $value, $typeParam);
        }
        if ($limit !== null) {
            $declaration->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $declaration->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function trouverUnParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null): ?array
    {
        $resultats = $this->trouverParCritere($criteres, $colonnes, $operateurLogique, $orderBy, 1);
        return $resultats[0] ?? null;
    }

    public function compterParCritere(array $criteres, string $operateurLogique = 'AND'): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        $conditions = [];
        if (!empty($criteres)) {
            foreach (array_keys($criteres) as $champ) {
                $conditions[] = "`{$champ}` = :{$champ}";
            }
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $conditions);
        }
        $declaration = $this->db->prepare($sql);
        foreach ($criteres as $key => $value) {
            $typeParam = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $declaration->bindValue(":$key", $value, $typeParam);
        }
        $declaration->execute();
        return (int) $declaration->fetchColumn();
    }

    public function executerRequete(string $sql, array $parametres = []): PDOStatement
    {
        $declaration = $this->db->prepare($sql);
        $declaration->execute($parametres);
        return $declaration;
    }

    public function commencerTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function validerTransaction(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function annulerTransaction(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}