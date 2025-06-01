<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?self $instance = null;
    private ?PDO $pdoInstance = null;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_DATABASE') ?: 'mysoutenance';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdoInstance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            throw new PDOException('Connexion à la base de données impossible. Veuillez vérifier la configuration ou contacter l\'administrateur.', (int)$e->getCode(), $e);
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->pdoInstance === null) {
            throw new PDOException("La connexion PDO n'a pas été initialisée. Assurez-vous que getInstance() est appelé.");
        }
        return $this->pdoInstance;
    }

    private function __clone(){}

    public function __wakeup(){
        throw new \Exception("La désérialisation du singleton Database n'est pas autorisée.");
    }
}