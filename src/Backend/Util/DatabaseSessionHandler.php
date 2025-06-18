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
        // On ne se connecte pas ici pour éviter les erreurs si la DB n'est pas encore prête
    }

    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            // CORRECTION: On utilise la méthode statique getInstance()
            $this->pdo = Database::getInstance()->getConnection();
        }
        return $this->pdo;
    }

    public function open(string $path, string $name): bool
    {
        // Pas besoin d'action spécifique car la connexion est gérée à la demande
        return true;
    }

    public function close(): bool
    {
        // Pas besoin de fermer la connexion manuellement, PDO s'en charge
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->getDb()->prepare('SELECT session_data FROM sessions WHERE session_id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['session_data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $time = time();
        $lifetime = (int) ini_get('session.gc_maxlifetime');
        $userId = $_SESSION['user']['numero_utilisateur'] ?? null;

        $stmt = $this->getDb()->prepare(
            'REPLACE INTO sessions (session_id, session_data, session_lifetime, session_time, user_id) 
             VALUES (:id, :data, :lifetime, :time, :user_id)'
        );

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':lifetime', $lifetime);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':user_id', $userId);

        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_id = :id');
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $old = time() - $max_lifetime;
        $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_time < :old');
        $stmt->bindParam(':old', $old);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
