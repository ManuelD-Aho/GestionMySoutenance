<?php

namespace App\Backend\Model;

use PDO;
use PDOStatement;
use PDOException;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $clePrimaire = 'id'; // Valeur par défaut, surchargée dans les classes enfants

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

    /**
     * Prépare la chaîne de caractères des colonnes pour une requête SELECT.
     * Gère le cas de '*' et protège les noms de colonnes individuels avec des backticks.
     */
    protected function preparerListeColonnes(array $colonnes): string
    {
        if (count($colonnes) === 1 && $colonnes[0] === '*') {
            return '*';
        }
        // Nettoie chaque nom de colonne (enlève les backticks existants pour éviter le doublage)
        // et ajoute des backticks autour de chaque nom de colonne.
        return implode(', ', array_map(fn($col) => "`" . trim(str_replace('`', '', $col)) . "`", $colonnes));
    }

    public function trouverTout(array $colonnes = ['*']): array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes); // CORRIGÉ
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}`";
        try {
            $declaration = $this->db->query($sql);
            return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverTout pour la table {$this->table}: " . $e->getMessage());
            throw $e; // Ou retourner un tableau vide selon votre stratégie d'erreur
        }
    }

    public function trouverParIdentifiant(int|string $id, array $colonnes = ['*']): ?array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes); // CORRIGÉ
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `{$this->clePrimaire}` = :id_cle_primaire_bind"; // Placeholder unique
        try {
            $declaration = $this->db->prepare($sql);
            $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $declaration->bindParam(':id_cle_primaire_bind', $id, $typeParametre);
            $declaration->execute();
            $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
            return $resultat ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParIdentifiant pour la table {$this->table} avec ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function creer(array $donnees): string|bool
    {
        if (empty($donnees)) {
            return false;
        }
        $colonnesArray = array_keys($donnees);
        $colonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnesArray));
        $placeholders = ':' . implode(', :', $colonnesArray);
        $sql = "INSERT INTO `{$this->table}` ({$colonnes}) VALUES ({$placeholders})";

        try {
            $declaration = $this->db->prepare($sql);
            $succes = $declaration->execute($donnees);
            if ($succes) {
                // Tenter de récupérer lastInsertId() uniquement si la clé primaire est susceptible d'être auto-incrémentée (typiquement INT)
                // Pour les clés primaires VARCHAR (comme numero_utilisateur), lastInsertId() ne fonctionnera pas comme attendu.
                // La valeur de la clé primaire est déjà dans $donnees si elle n'est pas auto-générée.
                $dernierId = $this->db->lastInsertId();
                // lastInsertId() retourne "0" (chaîne) si la dernière ligne insérée n'a pas de valeur auto-incrémentée.
                if ($dernierId && $dernierId !== "0") {
                    return $dernierId; // Pour les PK auto-incrémentées numériques
                }
                // Si la clé primaire est fournie dans $donnees (ex: VARCHAR) et n'est pas auto-incrémentée
                if (isset($donnees[$this->clePrimaire])) {
                    return $donnees[$this->clePrimaire];
                }
                return true; // Succès générique si aucun ID pertinent n'est retourné par lastInsertId
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans creer pour la table {$this->table}: " . $e->getMessage());
            // Vous pourriez vouloir vérifier les codes d'erreur spécifiques, ex: 23000 pour violation de contrainte d'unicité
            throw $e; // Relancer pour que le service puisse la gérer
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
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `{$this->clePrimaire}` = :id_cle_primaire_bind"; // Placeholder unique pour l'ID

        try {
            $declaration = $this->db->prepare($sql);
            $parametres = $donnees;
            $parametres['id_cle_primaire_bind'] = $id; // Ajouter l'ID aux paramètres pour le bind
            return $declaration->execute($parametres);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans mettreAJourParIdentifiant pour la table {$this->table} avec ID {$id}: " . $e->getMessage());
            return false; // Ou throw $e;
        }
    }

    public function supprimerParIdentifiant(int|string $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->clePrimaire}` = :id_cle_primaire_bind"; // Placeholder unique
        try {
            $declaration = $this->db->prepare($sql);
            $typeParametre = is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $declaration->bindParam(':id_cle_primaire_bind', $id, $typeParametre);
            return $declaration->execute();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans supprimerParIdentifiant pour la table {$this->table} avec ID {$id}: " . $e->getMessage());
            return false; // Ou throw $e;
        }
    }

    public function trouverParCritere(array $criteres, array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes); // CORRIGÉ
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}`";
        $conditions = [];
        $parametres = [];

        if (!empty($criteres)) {
            $index = 0;
            foreach ($criteres as $champ => $valeur) {
                $placeholder = ":critere_" . preg_replace('/[^a-zA-Z0-9_]/', '', $champ) . "_" . $index++; // Placeholder unique
                if (is_array($valeur) && isset($valeur['operateur']) && isset($valeur['valeur'])) {
                    // Gérer des opérateurs plus complexes si nécessaire, ex: ['champ' => ['operateur' => 'LIKE', 'valeur' => '%terme%']]
                    $conditions[] = "`{$champ}` {$valeur['operateur']} {$placeholder}";
                    $parametres[$placeholder] = $valeur['valeur'];
                } else {
                    $conditions[] = "`{$champ}` = {$placeholder}";
                    $parametres[$placeholder] = $valeur;
                }
            }
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $conditions);
        }
        if ($orderBy !== null) {
            // Attention à l'injection SQL ici si $orderBy vient de l'extérieur sans validation stricte
            $sql .= " ORDER BY " . $orderBy; // Valider/assainir $orderBy en amont
        }
        if ($limit !== null) {
            $sql .= " LIMIT :limit_bind"; // Placeholder unique
            if ($offset !== null) {
                $sql .= " OFFSET :offset_bind"; // Placeholder unique
            }
        }

        try {
            $declaration = $this->db->prepare($sql);
            foreach ($parametres as $key => $value) {
                $typeParam = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
                $declaration->bindValue($key, $value, $typeParam);
            }
            if ($limit !== null) {
                $declaration->bindValue(':limit_bind', $limit, PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $declaration->bindValue(':offset_bind', $offset, PDO::PARAM_INT);
            }
            $declaration->execute();
            return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParCritere pour la table {$this->table}: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
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
        $parametres = [];
        if (!empty($criteres)) {
            $index = 0;
            foreach ($criteres as $champ => $valeur) {
                $placeholder = ":critere_" . preg_replace('/[^a-zA-Z0-9_]/', '', $champ) . "_" . $index++;
                $conditions[] = "`{$champ}` = {$placeholder}";
                $parametres[$placeholder] = $valeur;
            }
            $sql .= " WHERE " . implode(" {$operateurLogique} ", $conditions);
        }
        try {
            $declaration = $this->db->prepare($sql);
            foreach ($parametres as $key => $value) {
                $typeParam = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
                $declaration->bindValue($key, $value, $typeParam);
            }
            $declaration->execute();
            return (int) $declaration->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans compterParCritere pour la table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function executerRequete(string $sql, array $parametres = []): PDOStatement
    {
        try {
            $declaration = $this->db->prepare($sql);
            $declaration->execute($parametres);
            return $declaration;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans executerRequete: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
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
