<?php
// src/Backend/Util/DatabaseSessionHandler.php

namespace App\Backend\Util;

use PDO;
use SessionHandlerInterface;
use App\Config\Database;

/**
 * Gère le stockage des sessions PHP directement dans la base de données.
 * Cela permet de centraliser les sessions, de les lier à un utilisateur
 * et de les manipuler programmatiquement (ex: mise à jour des permissions).
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    /**
     * Obtient une instance de la connexion PDO.
     * La connexion est établie "paresseusement" (lazy loading) au premier besoin.
     */
    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            // Utilise le singleton Database pour garantir une seule connexion
            $this->pdo = Database::getInstance()->getConnection();
        }
        return $this->pdo;
    }

    /**
     * Ouvre la session. Ne fait rien car la connexion est gérée paresseusement.
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Ferme la session. Libère la connexion PDO.
     */
    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }

    /**
     * Lit les données d'une session à partir de la base de données.
     * @param string $id L'ID de la session.
     * @return string|false Les données de session sérialisées ou false en cas d'échec.
     */
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

    /**
     * Écrit les données d'une session dans la base de données.
     * C'est ici que nous lions la session à un user_id.
     * @param string $id L'ID de la session.
     * @param string $data Les données de session sérialisées.
     * @return bool True en cas de succès, false sinon.
     */
    public function write(string $id, string $data): bool
    {
        try {
            $time = time();
            $lifetime = (int) ini_get('session.gc_maxlifetime');
            // Récupère le user_id depuis la superglobale $_SESSION si elle existe
            $userId = $_SESSION['user_id'] ?? null;

            $stmt = $this->getDb()->prepare(
                'REPLACE INTO sessions (session_id, session_data, session_last_activity, session_lifetime, user_id)
                 VALUES (:id, :data, :last_activity, :lifetime, :user_id)'
            );

            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':data', $data, PDO::PARAM_LOB); // LOB pour les données potentiellement volumineuses
            $stmt->bindParam(':last_activity', $time, PDO::PARAM_INT);
            $stmt->bindParam(':lifetime', $lifetime, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Session Write Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Détruit une session de la base de données (utilisé lors du logout).
     * @param string $id L'ID de la session.
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
     * Le "Garbage Collector" : supprime les sessions expirées de la base de données.
     * @param int $max_lifetime La durée de vie maximale d'une session en secondes.
     * @return int|false Le nombre de sessions supprimées ou false en cas d'échec.
     */
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