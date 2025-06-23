<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Gère la connexion à la base de données en utilisant le design pattern Singleton.
 * Assure qu'une seule instance de PDO est créée pour toute la durée de la requête.
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $connection;

    /**
     * Le constructeur est privé pour empêcher l'instanciation directe.
     * Il lit les variables d'environnement et établit la connexion PDO.
     */
    private function __construct()
    {
        // Lecture des variables d'environnement avec des valeurs par défaut sécurisées
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $db   = $_ENV['DB_NAME'] ?? 'mysoutenance';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Enregistrer l'erreur détaillée pour les développeurs
            error_log("PDO Connection Error: " . $e->getMessage());
            // Lancer une exception générique pour l'utilisateur final
            // Cela empêche de fuiter des informations sensibles sur la base de données.
            throw new PDOException("Erreur de connexion au service de base de données.", (int)$e->getCode());
        }
    }

    /**
     * Méthode statique pour obtenir l'instance unique de la classe.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'objet de connexion PDO actif.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Empêche le clonage de l'instance (partie du pattern Singleton).
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation de l'instance (partie du pattern Singleton).
     */
    public function __wakeup()
    {
        throw new \Exception("La désérialisation du singleton Database n'est pas autorisée.");
    }
}