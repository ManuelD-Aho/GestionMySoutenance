<?php
namespace App\Backend\Model;

use PDO;
use PDOStatement; // Assurez-vous d'importer PDOStatement
use App\Backend\Exception\ElementNonTrouveException; // Assurez-vous d'importer cette exception
use App\Backend\Exception\DoublonException; // Assurez-vous d'importer cette exception

abstract class BaseModel
{
    // Propriétés à définir dans les modèles enfants
    protected string $table;
    // Peut être une string (pour clé simple) ou un tableau de strings (pour clé composite)
    protected string|array $primaryKey;

    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getClePrimaire(): string|array
    {
        return $this->primaryKey;
    }

    /**
     * Retourne l'instance de la connexion PDO associée à ce modèle.
     * @return PDO
     */
    public function getDb(): PDO
    {
        return $this->db;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    protected function preparerListeColonnes(array $colonnes): string
    {
        if (empty($colonnes) || in_array('*', $colonnes)) {
            return '*';
        }
        return implode(', ', array_map(fn($col) => "`{$col}`", $colonnes));
    }

    /**
     * Construit la clause WHERE pour les opérations de recherche/mise à jour/suppression par clés.
     * Supporte les clés simples et composites.
     * @param string|int|array $keys La valeur de la clé primaire (simple) ou un tableau associatif (pour composite).
     * @return array Tableau contenant la clause WHERE et les paramètres bindés.
     * @throws \InvalidArgumentException Si la clé primaire n'est pas définie ou le format de $keys est incorrect.
     */
    protected function preparerClauseWhereParCles(string|int|array $keys): array
    {
        $whereClause = '';
        $params = [];

        if (is_array($this->primaryKey)) { // Clé composite
            if (!is_array($keys) || count(array_diff_key(array_flip($this->primaryKey), $keys)) > 0) {
                throw new \InvalidArgumentException("Les clés composites doivent être fournies sous forme de tableau associatif avec toutes les colonnes de la clé primaire.");
            }
            $conditions = [];
            foreach ($this->primaryKey as $keyName) {
                $conditions[] = "`{$keyName}` = :{$keyName}";
                $params[":{$keyName}"] = $keys[$keyName];
            }
            $whereClause = implode(' AND ', $conditions);
        } else { // Clé simple
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
            // Pour les clés composites, cette méthode générique n'est pas appropriée.
            // Il faut utiliser trouverUnParCritere ou une méthode spécifique (e.g. trouverParCles)
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
                // Si la clé primaire est auto-incrémentée et n'est pas dans $donnees
                if (is_string($this->primaryKey) && !isset($donnees[$this->primaryKey])) {
                    $lastInsertId = $this->db->lastInsertId();
                    return $lastInsertId ?: true; // Retourne l'ID ou true si pas d'ID auto-généré
                }
                // Si la clé primaire est fournie dans $donnees (cas de VARCHAR généré par le système)
                if (is_string($this->primaryKey) && isset($donnees[$this->primaryKey])) {
                    return $donnees[$this->primaryKey];
                }
                // Si clé composite, on ne peut pas retourner un simple ID
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            // Gérer les cas de doublons via une exception spécifique
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'unicité
                throw new DoublonException("Une ressource avec des attributs uniques similaires existe déjà dans la table '{$this->table}'.", 0, $e);
            }
            throw $e; // Re-lancer l'exception si ce n'est pas un doublon
        }
    }

    public function mettreAJourParIdentifiant(int|string $id, array $donnees): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("La méthode mettreAJourParIdentifiant n'est pas supportée pour les clés primaires composites. Utilisez mettreAJourParCles.");
        }

        $setParts = [];
        $params = [];
        foreach ($donnees as $key => $value) {
            $setParts[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        $params[':id'] = $id;
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts) . " WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'unicité
                throw new DoublonException("Une ressource avec des attributs uniques similaires existe déjà lors de la mise à jour de la table '{$this->table}'.", 0, $e);
            }
            throw $e;
        }
    }

    /**
     * Met à jour un enregistrement en utilisant un tableau de clés (pour clés composites ou simples).
     * @param array $keys Tableau associatif des clés (ex: ['col1' => 'val1', 'col2' => 'val2']).
     * @param array $donnees Données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     * @throws \InvalidArgumentException Si $keys est mal formé.
     * @throws DoublonException Si la mise à jour provoque une violation de contrainte d'unicité.
     */
    public function mettreAJourParCles(array $keys, array $donnees): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $setParts = [];
        $params = [];

        foreach ($donnees as $key => $value) {
            $setParts[] = "`{$key}` = :set_{$key}"; // Utiliser un préfixe pour éviter les conflits de nom avec les clés WHERE
            $params[":set_{$key}"] = $value;
        }

        $params = array_merge($params, $whereInfo['params']);
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts) . " WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'unicité
                throw new DoublonException("Une ressource avec des attributs uniques similaires existe déjà lors de la mise à jour de la table '{$this->table}'.", 0, $e);
            }
            throw $e;
        }
    }


    /**
     * Met à jour des enregistrements dans la table basés sur des critères spécifiés.
     * Cette méthode est puissante et doit être utilisée avec précaution.
     *
     * @param array $criteres Tableau associatif des critères de sélection.
     * @param array $donnees Tableau associatif des colonnes et de leurs nouvelles valeurs.
     * @param string $operateurLogique L'opérateur logique entre les critères ('AND' ou 'OR').
     * @return int Le nombre d'enregistrements mis à jour.
     * @throws \PDOException Si une erreur de base de données survient.
     * @throws DoublonException Si la mise à jour provoque une violation de contrainte d'unicité.
     */
    public function mettreAJourParCritere(array $criteres, array $donnees, string $operateurLogique = 'AND'): int
    {
        $whereParts = [];
        $whereParams = [];

        // Réutilise la logique de préparation de critères de trouverParCritere
        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
                    $inPlaceholders = [];
                    foreach ($value['values'] as $i => $inValue) {
                        $inPlaceholders[] = ":where_{$key}_in_{$i}"; // Préfixe pour éviter conflits
                        $whereParams[":where_{$key}_in_{$i}"] = $inValue;
                    }
                    $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'between') {
                    if (count($value['values']) === 2) {
                        $whereParts[] = "`{$key}` BETWEEN :where_{$key}_start AND :where_{$key}_end";
                        $whereParams[":where_{$key}_start"] = $value['values'][0];
                        $whereParams[":where_{$key}_end"] = $value['values'][1];
                    }
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'like') {
                    $whereParts[] = "`{$key}` LIKE :where_{$key}_like";
                    $whereParams[":where_{$key}_like"] = $value['value'];
                } elseif (isset($value['operator'])) { // Pour d'autres opérateurs comme '!=', '<', '>'
                    $whereParts[] = "`{$key}` {$value['operator']} :where_{$key}_op";
                    $whereParams[":where_{$key}_op"] = $value['value'];
                }
            } else { // Critère simple d'égalité
                $whereParts[] = "`{$key}` = :where_{$key}"; // Préfixe pour éviter conflits
                $whereParams[":where_{$key}"] = $value;
            }
        }

        $setParts = [];
        $setParams = [];
        foreach ($donnees as $key => $value) {
            $setParts[] = "`{$key}` = :set_{$key}";
            $setParams[":set_{$key}"] = $value;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts);
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $whereParts);
        }

        $params = array_merge($setParams, $whereParams);
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute($params);
            return $stmt->rowCount(); // Retourne le nombre de lignes affectées
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'unicité
                throw new DoublonException("Une violation de contrainte d'unicité est survenue lors de la mise à jour par critère dans la table '{$this->table}'.", 0, $e);
            }
            throw $e; // Re-lancer l'exception si ce n'est pas un doublon
        }
    }


    public function supprimerParIdentifiant(int|string $id): bool
    {
        if (is_array($this->primaryKey)) {
            throw new \BadMethodCallException("La méthode supprimerParIdentifiant n'est pas supportée pour les clés primaires composites. Utilisez supprimerParCles.");
        }

        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Supprime un enregistrement en utilisant un tableau de clés (pour clés composites ou simples).
     * @param array $keys Tableau associatif des clés (ex: ['col1' => 'val1', 'col2' => 'val2']).
     * @return bool Vrai si la suppression a réussi, faux sinon.
     * @throws \InvalidArgumentException Si $keys est mal formé.
     */
    public function supprimerParCles(array $keys): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $sql = "DELETE FROM `{$this->table}` WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($whereInfo['params']);
    }




    /**
     * Supprime des enregistrements de la table basés sur des critères spécifiés.
     * Cette méthode est puissante et doit être utilisée avec précaution.
     *
     * @param array $criteres Tableau associatif des critères de suppression (clé => valeur ou tableau pour opérateurs).
     * @param string $operateurLogique L'opérateur logique entre les critères ('AND' ou 'OR').
     * @return int Le nombre d'enregistrements supprimés.
     * @throws \PDOException Si une erreur de base de données survient.
     */
    public function supprimerParCritere(array $criteres, string $operateurLogique = 'AND'): int
    {
        $whereParts = [];
        $params = [];

        // Réutilise la logique de préparation de critères de trouverParCritere
        foreach ($criteres as $key => $value) {
            if (is_array($value)) {
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
                    $inPlaceholders = [];
                    foreach ($value['values'] as $i => $inValue) {
                        $inPlaceholders[] = ":{$key}_del_in_{$i}"; // Préfixe pour éviter conflits
                        $params[":{$key}_del_in_{$i}"] = $inValue;
                    }
                    $whereParts[] = "`{$key}` IN (" . implode(', ', $inPlaceholders) . ")";
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'between') {
                    if (count($value['values']) === 2) {
                        $whereParts[] = "`{$key}` BETWEEN :{$key}_del_start AND :{$key}_del_end";
                        $params[":{$key}_del_start"] = $value['values'][0];
                        $params[":{$key}_del_end"] = $value['values'][1];
                    }
                } elseif (isset($value['operator']) && strtolower($value['operator']) === 'like') {
                    $whereParts[] = "`{$key}` LIKE :{$key}_del_like";
                    $params[":{$key}_del_like"] = $value['value'];
                } elseif (isset($value['operator'])) { // Pour d'autres opérateurs comme '!=', '<', '>'
                    $whereParts[] = "`{$key}` {$value['operator']} :{$key}_del_op";
                    $params[":{$key}_del_op"] = $value['value'];
                }
            } else { // Critère simple d'égalité
                $whereParts[] = "`{$key}` = :{$key}_del"; // Préfixe pour éviter conflits
                $params[":{$key}_del"] = $value;
            }
        }

        $sql = "DELETE FROM `{$this->table}`";
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount(); // Retourne le nombre de lignes affectées
    }



    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = $this->preparerListeColonnes($colonnes);
        $whereParts = [];
        $params = [];

        foreach ($criteres as $key => $value) {
            if (is_array($value)) { // Gérer les critères de type IN ou BETWEEN
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
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
                }
                // Ajoutez d'autres opérateurs si nécessaire
            } else { // Critère simple d'égalité
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
            if (is_array($value)) { // Gérer les critères de type IN, BETWEEN, LIKE
                if (isset($value['operator']) && strtolower($value['operator']) === 'in') {
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
        $this->db->beginTransaction();
    }

    public function validerTransaction(): void
    {
        $this->db->commit();
    }

    public function annulerTransaction(): void
    {
        $this->db->rollBack();
    }

    /**
     * Méthode générique pour mettre à jour un enregistrement par ses clés primaires (simple ou composite).
     * Similaire à mettreAJourParCles, mais sert de point d'entrée pour les modèles enfants.
     * @param string|int|array $keys La valeur de la clé primaire (simple) ou un tableau associatif (pour composite).
     * @param array $donnees Données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     * @throws \InvalidArgumentException Si $keys est mal formé.
     * @throws DoublonException Si la mise à jour provoque une violation de contrainte d'unicité.
     */
    public function mettreAJourParClesInternes(string|int|array $keys, array $donnees): bool
    {
        // Réutilise la logique de preparerClauseWhereParCles et le UPDATE SQL
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

    /**
     * Méthode générique pour supprimer un enregistrement par ses clés primaires (simple ou composite).
     * Similaire à supprimerParCles, mais sert de point d'entrée pour les modèles enfants.
     * @param string|int|array $keys La valeur de la clé primaire (simple) ou un tableau associatif (pour composite).
     * @return bool Vrai si la suppression a réussi, faux sinon.
     * @throws \InvalidArgumentException Si $keys est mal formé.
     */
    public function supprimerParClesInternes(string|int|array $keys): bool
    {
        $whereInfo = $this->preparerClauseWhereParCles($keys);
        $sql = "DELETE FROM `{$this->table}` WHERE {$whereInfo['clause']}";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($whereInfo['params']);
    }
}