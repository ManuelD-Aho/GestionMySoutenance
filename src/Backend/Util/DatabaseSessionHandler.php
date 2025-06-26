<?php

namespace App\Backend\Util;

use PDO;
use SessionHandlerInterface;
use App\Config\Database; // Assurez-vous que cette classe est correctement importée

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    public function __construct()
    {
        // Le constructeur n'initialise plus PDO directement ici.
        // La connexion sera obtenue via getDb() au moment du besoin.
    }

    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = Database::getInstance()->getConnection();
            } catch (\PDOException $e) {
                // Log l'erreur de connexion à la DB pour le débogage
                error_log("DatabaseSessionHandler PDO Connection Error: " . $e->getMessage());
                // Il est crucial de ne pas laisser l'application continuer sans DB pour les sessions
                // Vous pouvez choisir de relancer l'exception ou de gérer une défaillance gracieuse
                throw new \RuntimeException("Impossible d'établir la connexion à la base de données pour les sessions.", 0, $e);
            }
        }
        return $this->pdo;
    }

    public function open(string $path, string $name): bool
    {
        // La connexion est établie au premier appel à getDb() (par read/write/destroy/gc)
        return true;
    }

    public function close(): bool
    {
        // Optionnel: libérer la connexion si elle n'est plus nécessaire
        $this->pdo = null;
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
        $userId = $_SESSION['user_id'] ?? null;

        $stmt = $this->getDb()->prepare(
            'REPLACE INTO sessions (session_id, session_data, session_last_activity, session_lifetime, user_id)
             VALUES (:id, :data, :last_activity_time, :lifetime, :user_id)'
        );

        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, PDO::PARAM_LOB);
        $stmt->bindParam(':last_activity_time', $time, PDO::PARAM_INT);
        $stmt->bindParam(':lifetime', $lifetime, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);

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
        $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_last_activity < :old');
        $stmt->bindParam(':old', $old);
        $stmt->execute();

        return $stmt->rowCount();
    }
}