<?php
namespace App\Backend\Model;

use PDO;
use PDOStatement;
use App\Backend\Exception\DoublonException;

abstract class BaseModel
{
    protected string $table;
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

    public function getTable(): string
    {
        return $this->table;
    }

    public function getClePrimaire(): string|array
    {
        return $this->primaryKey;
    }

    protected function preparerListeColonnes(array $colonnes): string
    {
        if (empty($colonnes) || in_array('*', $colonnes)) {
            return '*';
        }
        return implode(', ', array_map(fn($col) => "`{$col}`", $colonnes));
    }

    protected function preparerClauseWhereParCles(string|int|array $keys): array
    {
        $whereClause = '';
        $params = [];

        if (is_array($this->primaryKey)) {
            if (!is_array($keys) || count(array_diff_key(array_flip($this->primaryKey), $keys)) > 0) {
                throw new \InvalidArgumentException("Les clés composites doivent être fournies sous forme de tableau associatif avec toutes les colonnes de la clé primaire.");
            }
            $conditions = [];
            foreach ($this->primaryKey as $keyName) {
                $conditions[] = "`{$keyName}` = :{$keyName}";
                $params[":{$keyName}"] = $keys[$keyName];
            }
            $whereClause = implode(' AND ', $conditions);
        } else {
            if (is_array($keys)) {
                throw new \InvalidArgumentException("La clé primaire simple doit être fournie directement, pas un tableau.");
            }
            $whereClause = "`{$this->primaryKey}` = :id";
            $params[':id'] = $keys;
        }
        return ['clause' => $whereClause, 'params' => $params];
    }

    public function trouverTout(array $colonnes = ['*']): array
    {
        $cols = $this->preparerListeColonnes($colonnes);
        $stmt = $this->db->query("SELECT {$cols} FROM `{$this->table}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouverParIdentifiant(int|string $id, array $colonnes = ['*']): ?array
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("La méthode trouverParIdentifiant n'est pas supportée pour les clés primaires composites. Utilisez trouverUnParCritere ou une méthode de recherche par clés.");
        }

        $cols = $this->preparerListeColonnes($colonnes);
        $stmt = $this->db->prepare("SELECT {$cols} FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function creer(array $donnees): string|bool
    {
        $cols = [];
        $placeholders = [];
        $params = [];

        foreach ($donnees as $key => $value) {
            $cols[] = "`{$key}`";
            $placeholders[] = ":{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "INSERT INTO `{$this->table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);

        try {
            if ($stmt->execute($params)) {
                if (is_string($this->primaryKey) && !isset($donnees[$this->primaryKey])) {
                    $lastInsertId = $this->db->lastInsertId();
                    return $lastInsertId ?: true;
                }
                if (is_string($this->primaryKey) && isset($donnees[$this->primaryKey])) {
                    return $donnees[$this->primaryKey];
                }
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new DoublonException("Une ressource avec des attributs uniques similaires existe déjà dans la table '{$this->table}'.", 0, $e);
            }
            throw $e;
        }
    }

    public function mettreAJourParIdentifiant(int|string $id, array $donnees): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("La méthode mettreAJourParIdentifiant n'est pas supportée pour les clés primaires composites. Utilisez mettreAJourParClesInternes.");
        }
        return $this->mettreAJourParClesInternes($id, $donnees);
    }

    public function supprimerParIdentifiant(int|string $id): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("La méthode supprimerParIdentifiant n'est pas supportée pour les clés primaires composites. Utilisez supprimerParClesInternes.");
        }
        return $this->supprimerParClesInternes($id);
    }

    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = $this->preparerListeColonnes($colonnes);
        $whereParts = [];
        $params = [];

        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
                    if (empty($value['values'])) return [];
                    $inPlaceholders = [];
                    foreach ($value['values'] as $i => $inValue) {
                        $inPlaceholders[] = ":{$key}_in_{$i}";
                        $params[":{$key}_in_{$i}"] = $inValue;
                    }
                    $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'between') {
                    if (count($value['values']) === 2) {
                        $whereParts[] = "`{$key}` BETWEEN :{$key}_start AND :{$key}_end";
                        $params[":{$key}_start"] = $value['values'][0];
                        $params[":{$key}_end"] = $value['values'][1];
                    }
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'like') {
                    $whereParts[] = "`{$key}` LIKE :{$key}_like";
                    $params[":{$key}_like"] = $value['value'];
                } elseif (isset($value['operator'])) {
                    $whereParts[] = "`{$key}` {$value['operator']} :where_{$key}_op";
                    $params[":where_{$key}_op"] = $value['value'];
                }
            } else {
                $whereParts[] = "`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        $sql = "SELECT {$cols} FROM `{$this->table}`";
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $whereParts);
        }
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouverUnParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null): ?array
    {
        $results = $this->trouverParCritere($criteres, $colonnes, $operateurLogique, $orderBy, 1);
        return $results[0] ?? null;
    }

    public function compterParCritere(array $criteres, string $operateurLogique = 'AND'): int
    {
        $whereParts = [];
        $params = [];

        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
                    if (empty($value['values'])) return 0;
                    $inPlaceholders = [];
                    foreach ($value['values'] as $i => $inValue) {
                        $inPlaceholders[] = ":{$key}_in_{$i}";
                        $params[":{$key}_in_{$i}"] = $inValue;
                    }
                    $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'between') {
                    if (count($value['values']) === 2) {
                        $whereParts[] = "`{$key}` BETWEEN :{$key}_start AND :{$key}_end";
                        $params[":{$key}_start"] = $value['values'][0];
                        $params[":{$key}_end"] = $value['values'][1];
                    }
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'like') {
                    $whereParts[] = "`{$key}` LIKE :{$key}_like";
                    $params[":{$key}_like"] = $value['value'];
                } elseif (isset($value['operator'])) {
                    $whereParts[] = "`{$key}` {$value['operator']} :where_{$key}_op";
                    $params[":where_{$key}_op"] = $value['value'];
                }
            } else {
                $whereParts[] = "`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function executerRequete(string $sql, array $parametres = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametres);
        return $stmt;
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

    public function mettreAJourParClesInternes(string|int|array $keys, array $donnees): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $setParts = [];
        $params = [];

        foreach ($donnees as $key => $value) {
            $setParts[] = "`{$key}` = :set_{$key}";
            $params[":set_{$key}"] = $value;
        }

        $params = array_merge($params, $whereInfo['params']);
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts) . " WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new DoublonException("Une ressource avec des attributs uniques similaires existe déjà lors de la mise à jour de la table '{$this->table}'.", 0, $e);
            }
            throw $e;
        }
    }

    public function supprimerParClesInternes(string|int|array $keys): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $sql = "DELETE FROM `{$this->table}` WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($whereInfo['params']);
    }
}