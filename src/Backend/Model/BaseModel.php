<?php
// src/Backend/Model/BaseModel.php
namespace Backend\Model;

use PDO;
use PDOException;
use PDOStatement;

/**
 * BaseModel: Fournit les opérations CRUD génériques et des fonctionnalités de base
 * pour interagir avec n'importe quelle table de la base de données.
 *
 * @package Backend\Model
 */
abstract class BaseModel
{
    protected PDO $db; // L'instance de connexion PDO
    protected string $table; // Le nom de la table doit être défini dans la classe enfant
    protected string $primaryKey = 'id'; // La clé primaire par défaut de la table

    /**
     * Constructeur de la classe BaseModel.
     *
     * @param PDO $db L'instance de connexion PDO.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db; // Injection de dépendance pour la connexion PDO
    }

    /**
     * Récupère tous les enregistrements de la table.
     *
     * @param array $columns Les colonnes à sélectionner. Par défaut ['*'].
     * @param ?string $orderBy La colonne pour le tri (ex: "nom ASC", "date_creation DESC").
     * @param ?int $limit Le nombre maximum d'enregistrements à retourner.
     * @param ?int $offset Le décalage pour la pagination.
     * @return array Un tableau contenant tous les enregistrements.
     */
    public function findAll(array $columns = ['*'], ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        try {
            $stmt = $this->db->prepare($sql); // Préparation de la requête pour plus de sécurité et de performance
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupération de tous les résultats en tableau associatif
        } catch (PDOException $e) {
            // En production, logguer l'erreur au lieu de l'afficher directement
            // error_log("Erreur dans findAll ({$this->table}): " . $e->getMessage());
            // throw new \RuntimeException("Impossible de récupérer les données de la table {$this->table}.", 0, $e);
            // Pour le développement, on peut la laisser se propager ou la rendre plus explicite :
            throw new PDOException("Erreur lors de la récupération des données de la table {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Récupère un enregistrement par sa clé primaire.
     *
     * @param int|string $id La valeur de la clé primaire.
     * @param array $columns Les colonnes à sélectionner. Par défaut ['*'].
     * @return array|null L'enregistrement sous forme de tableau associatif, ou null si non trouvé.
     */
    public function find($id, array $columns = ['*']): ?array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table} WHERE {$this->primaryKey} = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data !== false ? $data : null; // Retourne null si aucune ligne n'est trouvée
        } catch (PDOException $e) {
            // error_log("Erreur dans find ({$this->table}, ID: {$id}): " . $e->getMessage());
            // throw new \RuntimeException("Impossible de trouver l'enregistrement ID {$id} dans la table {$this->table}.", 0, $e);
            throw new PDOException("Erreur lors de la recherche de l'enregistrement ID {$id} dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Crée un nouvel enregistrement dans la table.
     *
     * @param array $data Un tableau associatif des données à insérer (colonne => valeur).
     * @return string|false L'ID du dernier enregistrement inséré en cas de succès, ou false en cas d'échec.
     */
    public function create(array $data)
    {
        if (empty($data)) {
            // throw new \InvalidArgumentException("Les données pour la création ne peuvent pas être vides.");
            return false; // Ou gérer l'erreur comme souhaité
        }

        // Gestion automatique des champs `created_at` et `updated_at` si la table les possède (optionnel)
        // if (property_exists($this, 'timestamps') && $this->timestamps) {
        //     $timestamp = date('Y-m-d H:i:s');
        //     if (!isset($data['created_at'])) $data['created_at'] = $timestamp;
        //     if (!isset($data['updated_at'])) $data['updated_at'] = $timestamp;
        // }

        $cols = array_keys($data);
        $fields = implode(', ', $cols);
        $placeholders = ':' . implode(', :', $cols);

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        try {
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($data);
            return $success ? $this->db->lastInsertId() : false; // Retourne l'ID de l'élément créé ou false
        } catch (PDOException $e) {
            // error_log("Erreur dans create ({$this->table}): " . $e->getMessage() . " Data: " . print_r($data, true));
            // throw new \RuntimeException("Impossible de créer l'enregistrement dans la table {$this->table}.", 0, $e);
            // Ne pas exposer les $data dans les messages d'erreur en production.
            throw new PDOException("Erreur lors de la création de l'enregistrement dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Met à jour un enregistrement existant par sa clé primaire.
     *
     * @param int|string $id La valeur de la clé primaire de l'enregistrement à mettre à jour.
     * @param array $data Un tableau associatif des données à mettre à jour (colonne => valeur).
     * @return bool True en cas de succès, false en cas d'échec.
     */
    public function update($id, array $data): bool
    {
        if (empty($data)) {
            // throw new \InvalidArgumentException("Les données pour la mise à jour ne peuvent pas être vides.");
            return false;
        }

        // Gestion automatique du champ `updated_at` (optionnel)
        // if (property_exists($this, 'timestamps') && $this->timestamps && !isset($data['updated_at'])) {
        //    $data['updated_at'] = date('Y-m-d H:i:s');
        // }

        $updateFields = [];
        foreach (array_keys($data) as $col) {
            $updateFields[] = "{$col} = :{$col}";
        }
        $setString = implode(', ', $updateFields);

        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :primary_key_id";

        try {
            $stmt = $this->db->prepare($sql);
            $data['primary_key_id'] = $id; // Ajouter l'ID pour le binding
            return $stmt->execute($data);
        } catch (PDOException $e) {
            // error_log("Erreur dans update ({$this->table}, ID: {$id}): " . $e->getMessage() . " Data: " . print_r($data, true));
            // throw new \RuntimeException("Impossible de mettre à jour l'enregistrement ID {$id} dans la table {$this->table}.", 0, $e);
            throw new PDOException("Erreur lors de la mise à jour de l'enregistrement ID {$id} dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Supprime un enregistrement par sa clé primaire.
     *
     * @param int|string $id La valeur de la clé primaire de l'enregistrement à supprimer.
     * @return bool True en cas de succès, false en cas d'échec.
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // error_log("Erreur dans delete ({$this->table}, ID: {$id}): " . $e->getMessage());
            // throw new \RuntimeException("Impossible de supprimer l'enregistrement ID {$id} dans la table {$this->table}.", 0, $e);
            throw new PDOException("Erreur lors de la suppression de l'enregistrement ID {$id} dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Récupère des enregistrements basés sur des conditions spécifiques.
     *
     * @param array $conditions Tableau de conditions (ex: ['colonne' => 'valeur', 'autre_colonne >' => 10]).
     * @param array $columns Les colonnes à sélectionner.
     * @param ?string $orderBy La colonne pour le tri.
     * @param ?int $limit Le nombre maximum d'enregistrements.
     * @param ?int $offset Le décalage.
     * @return array Les enregistrements trouvés.
     */
    public function findBy(array $conditions, array $columns = ['*'], ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                // Gérer les opérateurs simples comme '>', '<', 'LIKE', '!=' etc.
                // Exemple: 'views >' => 100 devient "views > :views"
                $parts = explode(' ', $key);
                $column = $parts[0];
                $operator = count($parts) > 1 ? $parts[1] : '=';

                // Créer un placeholder unique pour éviter les collisions si la même colonne est utilisée avec différents opérateurs
                $placeholder = preg_replace('/[^a-zA-Z0-9_]/', '', $column) . '_' . count($params);
                $whereClauses[] = "{$column} {$operator} :{$placeholder}";
                $params[$placeholder] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT :limit_val"; // Utiliser un placeholder différent pour éviter conflit avec un champ 'limit'
            $params['limit_val'] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET :offset_val"; // Utiliser un placeholder différent
                $params['offset_val'] = $offset;
            }
        }

        try {
            $stmt = $this->db->prepare($sql);
            // Binder les valeurs des conditions et de la pagination
            foreach($params as $key => &$val) { // Passer par référence pour bindValue
                if(is_int($val)) {
                    $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $val);
                }
            }
            unset($val); // Rompre la référence

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Erreur dans findBy ({$this->table}): " . $e->getMessage() . " Conditions: " . print_r($conditions, true));
            throw new PDOException("Erreur lors de la recherche par conditions dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Récupère le premier enregistrement correspondant aux conditions.
     *
     * @param array $conditions Tableau de conditions.
     * @param array $columns Les colonnes à sélectionner.
     * @param ?string $orderBy La colonne pour le tri.
     * @return array|null L'enregistrement ou null.
     */
    public function findOneBy(array $conditions, array $columns = ['*'], ?string $orderBy = null): ?array
    {
        $results = $this->findBy($conditions, $columns, $orderBy, 1);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Compte le nombre total d'enregistrements dans la table ou selon des conditions.
     *
     * @param array $conditions Optionnel. Tableau de conditions pour filtrer le comptage.
     * @return int Le nombre d'enregistrements.
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $parts = explode(' ', $key);
                $column = $parts[0];
                $operator = count($parts) > 1 ? $parts[1] : '=';
                $placeholder = preg_replace('/[^a-zA-Z0-9_]/', '', $column) . '_' . count($params);
                $whereClauses[] = "{$column} {$operator} :{$placeholder}";
                $params[$placeholder] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        try {
            $stmt = $this->db->prepare($sql);
            foreach($params as $key => &$val) {
                if(is_int($val)) {
                    $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $val);
                }
            }
            unset($val);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            // error_log("Erreur dans count ({$this->table}): " . $e->getMessage() . " Conditions: " . print_r($conditions, true));
            throw new PDOException("Erreur lors du comptage des enregistrements dans {$this->table}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Exécute une requête SQL brute.
     * À utiliser avec précaution, principalement pour des requêtes complexes non couvertes par les autres méthodes.
     * Assurez-vous que les paramètres sont correctement échappés ou utilisez des requêtes préparées.
     *
     * @param string $sql La requête SQL brute.
     * @param array $params Les paramètres à lier à la requête.
     * @return PDOStatement L'objet PDOStatement résultant.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // error_log("Erreur dans query: " . $e->getMessage() . " SQL: " . $sql . " Params: " . print_r($params, true));
            throw new PDOException("Erreur lors de l'exécution de la requête brute: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Commence une transaction.
     * @return bool True en cas de succès, false sinon.
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Valide une transaction.
     * @return bool True en cas de succès, false sinon.
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Annule une transaction.
     * @return bool True en cas de succès, false sinon.
     */
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * Vérifie si une transaction est active.
     * @return bool True si une transaction est active, false sinon.
     */
    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Méthode magique pour permettre d'appeler des méthodes findBy<NomDeColonne>().
     * Par exemple, $model->findByEmail('test@example.com')
     * ou $model->findOneByLogin('utilisateur123')
     *
     * @param string $method Le nom de la méthode appelée.
     * @param array $arguments Les arguments passés à la méthode.
     * @return array|null|mixed Dépend de la méthode appelée.
     * @throws \BadMethodCallException Si la méthode n'est pas reconnue.
     */
    public function __call(string $method, array $arguments)
    {
        // findBy<ColumnName>
        if (strpos($method, 'findBy') === 0) {
            $by = substr($method, 6); // Retire "findBy"
            $column = $this->convertToSnakeCase($by); // Convertit CamelCase en snake_case pour le nom de colonne
            if (!empty($arguments)) {
                return $this->findBy([$column => $arguments[0]]);
            }
        }

        // findOneBy<ColumnName>
        if (strpos($method, 'findOneBy') === 0) {
            $by = substr($method, 9); // Retire "findOneBy"
            $column = $this->convertToSnakeCase($by);
            if (!empty($arguments)) {
                return $this->findOneBy([$column => $arguments[0]]);
            }
        }

        throw new \BadMethodCallException("La méthode {$method} n'existe pas dans " . get_called_class());
    }

    /**
     * Convertit une chaîne de CamelCase à snake_case.
     * Utilisé par la méthode magique __call.
     *
     * @param string $input La chaîne en CamelCase (ex: UserName).
     * @return string La chaîne en snake_case (ex: user_name).
     */
    private function convertToSnakeCase(string $input): string
    {
        // Remplace les majuscules (sauf la première si elle est en début de chaîne) par _minuscule
        $output = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
        return $output;
    }
}