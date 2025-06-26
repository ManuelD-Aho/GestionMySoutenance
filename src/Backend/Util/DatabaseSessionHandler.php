<?php

namespace App\Backend\Util;

use PDO;
use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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
        $stmt = $this->pdo->prepare('SELECT session_data FROM sessions WHERE session_id = :id');
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

        $stmt = $this->pdo->prepare(
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
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE session_id = :id');
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $old = time() - $max_lifetime;
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE session_last_activity < :old');
        $stmt->bindParam(':old', $old);
        $stmt->execute();

        return $stmt->rowCount();
    }
}