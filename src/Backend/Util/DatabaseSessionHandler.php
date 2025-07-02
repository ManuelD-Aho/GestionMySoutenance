<?php
namespace App\Backend\Util;

use PDO;
use SessionHandlerInterface;
use App\Config\Database;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    public function __construct()
    {
        // Initialisation différée de PDO
    }

    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = Database::getInstance()->getConnection();
            } catch (\PDOException $e) {
                error_log("DatabaseSessionHandler PDO Connection Error: " . $e->getMessage());
                throw new \RuntimeException("Impossible d'établir la connexion à la base de données pour les sessions.", 0, $e);
            }
        }
        return $this->pdo;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $stmt = $this->getDb()->prepare('SELECT session_data FROM sessions WHERE session_id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['session_data'] : '';
        } catch (\PDOException $e) {
            error_log("Session Read Error: " . $e->getMessage());
            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $time = time();
            $lifetime = (int) ini_get('session.gc_maxlifetime');
            $userId = $_SESSION['user_id'] ?? null;

            // CORRIGÉ : Noms de colonnes cohérents
            $stmt = $this->getDb()->prepare(
                'REPLACE INTO sessions (session_id, session_data, session_last_activity, session_lifetime, user_id)
                 VALUES (:id, :data, :last_activity, :lifetime, :user_id)'
            );

            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':data', $data, PDO::PARAM_LOB);
            $stmt->bindParam(':last_activity', $time, PDO::PARAM_INT);
            $stmt->bindParam(':lifetime', $lifetime, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Session Write Error: " . $e->getMessage());
            return false;
        }
    }

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

    public function gc(int $max_lifetime): int|false
    {
        try {
            $old = time() - $max_lifetime;
            $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_last_activity < :old');
            $stmt->bindParam(':old', $old, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Session GC Error: " . $e->getMessage());
            return false;
        }
    }
}