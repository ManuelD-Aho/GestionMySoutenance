<?php

namespace App\Backend\Model;

use PDO;
use PDOStatement;
use PDOException;
use BadMethodCallException;
use App\Backend\Exception\DoublonException;

/**
 * Classe de base abstraite pour tous les modèles de l'application.
 * Fournit une implémentation générique et robuste des opérations CRUD,
 * la gestion des transactions, et une recherche avancée par critères.
 */
abstract class BaseModel
{
    /**
     * L'instance de connexion à la base de données.
     * @var PDO
     */
    protected PDO $db;

    /**
     * Le nom de la table de base de données associée à ce modèle.
     * Doit être défini dans la classe enfant.
     * @var string
     */
    public string $table;

    /**
     * Le nom de la clé primaire de la table.
     * Peut être une chaîne de caractères pour une clé simple,
     * ou un tableau de chaînes de caractères pour une clé composite.
     * Doit être défini dans la classe enfant.
     * @var string|array
     */
    public string|array $primaryKey;

    /**
     * Constructeur de BaseModel.
     * @param PDO $db L'instance de connexion PDO.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Prépare dynamiquement la clause WHERE pour une clé primaire simple ou composite.
     *
     * @param array $keys Un tableau associatif des clés primaires et de leurs valeurs.
     * @return array Un tableau contenant la clause WHERE (`clause`) et les paramètres (`params`).
     * @throws \InvalidArgumentException Si les clés fournies ne correspondent pas à la clé primaire du modèle.
     */
    protected function preparerClauseWhereParCles(array $keys): array
    {
        $primaryKeyDefinition = is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey];

        if (count($keys) !== count($primaryKeyDefinition) || count(array_diff(array_keys($keys), $primaryKeyDefinition)) > 0) {
            throw new \InvalidArgumentException("Les clés fournies ne correspondent pas à la clé primaire composite du modèle '{$this->table}'.");
        }

        $conditions = [];
        $params = [];
        foreach ($primaryKeyDefinition as $keyName) {
            $conditions[] = "`{$keyName}` = :where_{$keyName}";
            $params[":where_{$keyName}"] = $keys[$keyName];
        }

        return ['clause' => implode(' AND ', $conditions), 'params' => $params];
    }

    /**
     * Récupère tous les enregistrements de la table.
     *
     * @param array $colonnes Les colonnes à retourner.
     * @return array La liste des enregistrements.
     */
    public function trouverTout(array $colonnes = ['*']): array
    {
        $cols = implode(', ', $colonnes);
        $stmt = $this->db->query("SELECT {$cols} FROM `{$this->table}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve un enregistrement par sa clé primaire (pour clés simples uniquement).
     *
     * @param string|int $id La valeur de la clé primaire.
     * @param array $colonnes Les colonnes à retourner.
     * @return array|null L'enregistrement trouvé ou null.
     * @throws BadMethodCallException Si appelée sur un modèle à clé composite.
     */
    public function trouverParIdentifiant(string|int $id, array $colonnes = ['*']): ?array
    {
        if (is_array($this->primaryKey)) {
            throw new BadMethodCallException("La méthode trouverParIdentifiant() n'est pas supportée pour les clés primaires composites. Utilisez trouverUnParCritere() avec toutes les clés.");
        }
        $cols = implode(', ', $colonnes);
        $stmt = $this->db->prepare("SELECT {$cols} FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée un nouvel enregistrement dans la table.
     *
     * @param array $donnees Tableau associatif des données à insérer.
     * @return string|bool L'ID du dernier enregistrement inséré ou true, sinon false.
     * @throws DoublonException Si une contrainte d'unicité est violée.
     * @throws PDOException pour les autres erreurs SQL.
     */
    public function creer(array $donnees): string|bool
    {
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($donnees)));
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($donnees)));

        $sql = "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        try {
            if ($stmt->execute($donnees)) {
                if (is_string($this->primaryKey) && !isset($donnees[$this->primaryKey])) {
                    $lastInsertId = $this->db->lastInsertId();
                    return $lastInsertId ?: true;
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new DoublonException("Violation de contrainte d'unicité dans la table '{$this->table}'.", 23000, $e);
            }
            throw $e;
        }
    }

    /**
     * Met à jour un enregistrement par sa clé primaire (pour clés simples uniquement).
     *
     * @param string|int $id La valeur de la clé primaire.
     * @param array $donnees Les données à mettre à jour.
     * @return bool True en cas de succès, false sinon.
     * @throws BadMethodCallException Si appelée sur un modèle à clé composite.
     * @throws DoublonException Si une contrainte d'unicité est violée.
     */
    public function mettreAJourParIdentifiant(string|int $id, array $donnees): bool
    {
        if (is_array($this->primaryKey)) {
            throw new BadMethodCallException("La méthode mettreAJourParIdentifiant() n'est pas supportée pour les clés composites. Utilisez mettreAJourParCles().");
        }
        $setParts = [];
        foreach (array_keys($donnees) as $key) {
            $setParts[] = "`{$key}` = :{$key}";
        }
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts) . " WHERE `{$this->primaryKey}` = :id_pk";
        $stmt = $this->db->prepare($sql);
        $donnees['id_pk'] = $id;

        try {
            return $stmt->execute($donnees);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new DoublonException("Violation de contrainte d'unicité lors de la mise à jour dans '{$this->table}'.", 23000, $e);
            }
            throw $e;
        }
    }

    /**
     * Supprime un enregistrement par sa clé primaire (pour clés simples uniquement).
     *
     * @param string|int $id La valeur de la clé primaire.
     * @return bool True en cas de succès, false sinon.
     * @throws BadMethodCallException Si appelée sur un modèle à clé composite.
     */
    public function supprimerParIdentifiant(string|int $id): bool
    {
        if (is_array($this->primaryKey)) {
            throw new BadMethodCallException("La méthode supprimerParIdentifiant() n'est pas supportée pour les clés composites. Utilisez supprimerParCles().");
        }
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Met à jour un enregistrement par ses clés composites.
     *
     * @param array $keys Tableau associatif clé/valeur pour la clause WHERE.
     * @param array $donnees Les données à mettre à jour.
     * @return bool True en cas de succès, false sinon.
     * @throws DoublonException Si une contrainte d'unicité est violée.
     */
    public function mettreAJourParCles(array $keys, array $donnees): bool
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
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new DoublonException("Violation de contrainte d'unicité lors de la mise à jour dans '{$this->table}'.", 23000, $e);
            }
            throw $e;
        }
    }

    /**
     * Supprime un enregistrement par ses clés composites.
     *
     * @param array $keys Tableau associatif clé/valeur pour la clause WHERE.
     * @return bool True en cas de succès, false sinon.
     */
    public function supprimerParCles(array $keys): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $sql = "DELETE FROM `{$this->table}` WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($whereInfo['params']);
    }

    /**
     * Trouve des enregistrements basés sur des critères multiples.
     *
     * @param array $criteres Critères de recherche. e.g., ['statut' => 'actif', 'id' => ['operator' => 'IN', 'values' => [1, 2, 3]]]
     * @param array $colonnes Colonnes à retourner.
     * @param string $operateurLogique Opérateur logique 'AND' ou 'OR'.
     * @param string|null $orderBy Clause de tri.
     * @param int|null $limit Limite de résultats.
     * @param int|null $offset Décalage pour la pagination.
     * @return array La liste des enregistrements trouvés.
     */
    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = implode(', ', $colonnes);
        $whereParts = [];
        $params = [];

        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                $operator = strtoupper($value['operator'] ?? '=');
                switch ($operator) {
                    case 'IN':
                        $inPlaceholders = [];
                        foreach ($value['values'] as $i => $inValue) {
                            $paramName = ":{$key}_in_{$i}";
                            $inPlaceholders[] = $paramName;
                            $params[$paramName] = $inValue;
                        }
                        $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                        break;
                    case 'BETWEEN':
                        if (count($value['values']) === 2) {
                            $whereParts[] = "`{$key}` BETWEEN :{$key}_start AND :{$key}_end";
                            $params[":{$key}_start"] = $value['values'][0];
                            $params[":{$key}_end"] = $value['values'][1];
                        }
                        break;
                    default: // Gère LIKE, !=, >, <, etc.
                        $whereParts[] = "`{$key}` {$operator} :{$key}";
                        $params[":{$key}"] = $value['value'];
                        break;
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

    /**
     * Trouve un seul enregistrement basé sur des critères.
     *
     * @param array $criteres Critères de recherche.
     * @param array $colonnes Colonnes à retourner.
     * @param string $operateurLogique Opérateur logique 'AND' ou 'OR'.
     * @return array|null Le premier enregistrement trouvé ou null.
     */
    public function trouverUnParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND'): ?array
    {
        $result = $this->trouverParCritere($criteres, $colonnes, $operateurLogique, null, 1);
        return $result[0] ?? null;
    }

    /**
     * Compte les enregistrements basés sur des critères.
     *
     * @param array $criteres Critères de recherche.
     * @param string $operateurLogique Opérateur logique 'AND' ou 'OR'.
     * @return int Le nombre d'enregistrements.
     */
    public function compterParCritere(array $criteres, string $operateurLogique = 'AND'): int
    {
        $whereParts = [];
        $params = [];

        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                $operator = strtoupper($value['operator'] ?? '=');
                switch ($operator) {
                    case 'IN':
                        $inPlaceholders = [];
                        foreach ($value['values'] as $i => $inValue) {
                            $paramName = ":{$key}_in_{$i}";
                            $inPlaceholders[] = $paramName;
                            $params[$paramName] = $inValue;
                        }
                        $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                        break;
                    // Ajoutez d'autres opérateurs si nécessaire
                    default:
                        $whereParts[] = "`{$key}` {$operator} :{$key}";
                        $params[":{$key}"] = $value['value'];
                        break;
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

    /**
     * Démarre une transaction.
     */
    public function commencerTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Valide la transaction en cours.
     */
    public function validerTransaction(): void
    {
        $this->db->commit();
    }

    /**
     * Annule la transaction en cours.
     */
    public function annulerTransaction(): void
    {
        $this->db->rollBack();
    }
}