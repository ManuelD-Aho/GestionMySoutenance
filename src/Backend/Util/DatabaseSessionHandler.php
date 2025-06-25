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

        // REQUÊTE SQL CORRIGÉE : Utilise `session_last_activity` et `session_lifetime`
        $stmt = $this->getDb()->prepare(
            'REPLACE INTO sessions (session_id, session_data, session_last_activity, session_lifetime, user_id)
             VALUES (:id, :data, :last_activity_time, :lifetime, :user_id)'
        );


        // BINDINGS DES PARAMÈTRES CORRIGÉS :
        // Le paramètre pour le temps doit CORRESPONDRE au placeholder dans la requête SQL
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, PDO::PARAM_LOB); // Utiliser PARAM_LOB pour les BLOB/LONGBLOB
        $stmt->bindParam(':last_activity_time', $time, PDO::PARAM_INT); // CORRECTION ICI : le nom du paramètre
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
        // REQUÊTE SQL CORRIGÉE : Utilise `session_last_activity`
        $stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_last_activity < :old');$stmt = $this->getDb()->prepare('DELETE FROM sessions WHERE session_time < :old');
        $stmt->bindParam(':old', $old);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
