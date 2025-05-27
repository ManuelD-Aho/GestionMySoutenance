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

    public function trouverTout(array $colonnes = ['*']): array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table}";
        $declaration = $this->db->query($sql);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function trouverParIdentifiant(int|string $id, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE {$this->clePrimaire} = :id";
        $declaration = $this->db->prepare($sql);
        $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $declaration->bindParam(':id', $id, $typeParametre);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function creer(array $donnees): string|bool
    {
        $colonnes = implode(', ', array_keys($donnees));
        $placeholders = ':' . implode(', :', array_keys($donnees));
        $sql = "INSERT INTO {$this->table} ({$colonnes}) VALUES ({$placeholders})";
        $declaration = $this->db->prepare($sql);

        try {
            $succes = $declaration->execute($donnees);
            if ($succes) {
                $dernierId = $this->db->lastInsertId();
                if ($dernierId && $dernierId !== "0") {
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
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->clePrimaire} = :id_cle_primaire";
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
        $sql = "DELETE FROM {$this->table} WHERE {$this->clePrimaire} = :id";
        $declaration = $this->db->prepare($sql);
        $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $declaration->bindParam(':id', $id, $typeParametre);
        try {
            return $declaration->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND'): array
    {
        if (empty($criteres)) {
            return $this->trouverTout($colonnes);
        }
        $listeColonnes = implode(', ', $colonnes);
        $conditions = [];
        foreach (array_keys($criteres) as $champ) {
            $conditions[] = "{$champ} = :{$champ}";
        }
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE " . implode(" {$operateurLogique} ", $conditions);
        $declaration = $this->db->prepare($sql);
        $declaration->execute($criteres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function trouverUnParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND'): ?array
    {
        if (empty($criteres)) {
            $sql = "SELECT " . implode(', ', $colonnes) . " FROM {$this->table} LIMIT 1";
            $declaration = $this->db->query($sql);
            $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
            return $resultat ?: null;
        }
        $listeColonnes = implode(', ', $colonnes);
        $conditions = [];
        foreach (array_keys($criteres) as $champ) {
            $conditions[] = "{$champ} = :{$champ}";
        }
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE " . implode(" {$operateurLogique} ", $conditions) . " LIMIT 1";
        $declaration = $this->db->prepare($sql);
        $declaration->execute($criteres);
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function compterParCritere(array $criteres, string $operateurLogique = 'AND'): int
    {
        if (empty($criteres)) {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $declaration = $this->db->query($sql);
            return (int) $declaration->fetchColumn();
        }
        $conditions = [];
        foreach (array_keys($criteres) as $champ) {
            $conditions[] = "{$champ} = :{$champ}";
        }
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(" {$operateurLogique} ", $conditions);
        $declaration = $this->db->prepare($sql);
        $declaration->execute($criteres);
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