<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?self $instance = null;
    private ?PDO $pdo;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_DATABASE') ?: 'mysoutenance';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: ''; // Utilise DB_PASSWORD
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Pour une application en production, loguer l'erreur $e->getMessage() de manière sécurisée
            // et ne pas exposer de détails sensibles à l'utilisateur.
            throw new PDOException('Connexion à la base de données impossible. Veuillez contacter l\'administrateur.', (int)$e->getCode(), $e);
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->pdo === null) {
            // Ce cas ne devrait pas arriver si getInstance est toujours utilisé correctement
            // Mais c'est une sécurité si l'objet Database a été créé d'une manière ou d'une autre sans que le constructeur initialise pdo
            throw new PDOException("La connexion PDO n'a pas été initialisée.");
        }
        return $this->pdo;
    }

    private function __clone(){}

    public function __wakeup(){
        throw new \Exception("La désérialisation du singleton Database n'est pas autorisée.");
    }
}