<?php

namespace App\Backend\Util;

use PDO;
use SessionHandlerInterface;
use App\Config\Database;

/**
 * Gère le stockage des sessions PHP dans la base de données.
 * Remplace le mécanisme par défaut basé sur les fichiers.
 * Crucial pour la sécurité et la gestion des permissions en temps réel.
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    /**
     * Récupère l'instance de connexion PDO de manière paresseuse (lazy loading).
     */
    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = Database::getInstance()->getConnection();
        }
        return $this->pdo;
    }

    public function open(string $path, string $name): bool
    {
        // La connexion est gérée par getDb(), donc rien à faire ici.
        return true;
    }

    public function close(): bool
    {
        // La connexion PDO est persistante et gérée par le Singleton, pas besoin de la fermer ici.
        $this->pdo = null;
        return true;
    }

    /**
     * Lit les données d'une session à partir de la base de données.
     *
     * @param string $id L'ID de la session.
     * @return string|false Les données de session sérialisées, ou false si non trouvées.
     */
    public function read(string $id): string|false
    {
        try {
            $stmt = $this->getDb()->prepare('SELECT session_data FROM sessions WHERE session_id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['session_data'] : ''; // PHP gère la conversion de '' en false si nécessaire, mais '' est plus sûr.
        } catch (\PDOException $e) {
            error_log("Session Read Error: " . $e->getMessage());
            return false; // En cas d'erreur DB, retourner false.
        }
    }

    /**
     * Écrit les données d'une session dans la base de données.
     * Utilise REPLACE INTO pour une opération atomique (INSERT ou UPDATE).
     *
     * @param string $id L'ID de la session.
     * @param string $data Les données de session sérialisées.
     * @return bool True en cas de succès, false sinon.
     */
    public function write(string $id, string $data): bool
    {
        try {
            // CORRECTION : Utilise 'user_id' qui est la clé correcte définie dans le service d'authentification.
            $userId = $_SESSION['user_id'] ?? null;

            $stmt = $this->getDb()->prepare(
                'REPLACE INTO sessions (session_id, user_id, session_data, session_last_access) 
                 VALUES (:id, :user_id, :data, NOW())'
            );

            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':data', $data, PDO::PARAM_LOB); // Utiliser LOB pour les données binaires/blob

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Session Write Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Détruit une session spécifique de la base de données.
     *
     * @param string $id L'ID de la session à détruire.
     * @return bool True en cas de succès, false sinon.
     */
    public function destroy(string $id): bool
    {
        try {
            $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Session Destroy Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Nettoie les anciennes sessions expirées (Garbage Collector).
     *
     * @param int $max_lifetime La durée de vie maximale d'une session en secondes.
     * @return int|false Le nombre de sessions supprimées ou false en cas d'échec.
     */
    public function gc(int $max_lifetime): int|false
    {
        try {
            // Calcule le timestamp limite
            $limit = time() - $max_lifetime;

            $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE UNIX_TIMESTAMP(session_last_access) < :limit');
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Session GC Error: " . $e->getMessage());
            return false;
        }
    }
}