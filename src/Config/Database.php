<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Classe Singleton pour la gestion de la connexion à la base de données.
 * Assure qu'une seule instance de PDO est utilisée à travers l'application.
 */
class Database {
    private static ?self $instance = null;
    private ?PDO $pdoInstance = null;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     * Initialise la connexion PDO en utilisant les variables d'environnement.
     *
     * @throws PDOException Si la connexion à la base de données échoue.
     */
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
            // En production, ne pas exposer le message d'erreur brut de la DB
            throw new PDOException('Connexion à la base de données impossible. Veuillez vérifier la configuration ou contacter l\'administrateur.', (int)$e->getCode(), $e);
        }
    }

    /**
     * Retourne l'instance unique de la classe Database.
     * Crée l'instance si elle n'existe pas encore.
     *
     * @return self L'instance unique de Database.
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'objet PDO connecté à la base de données.
     *
     * @return PDO L'instance de PDO.
     * @throws PDOException Si la connexion PDO n'a pas été initialisée (ce qui ne devrait pas arriver avec getInstance()).
     */
    public function getConnection(): PDO {
        if ($this->pdoInstance === null) {
            throw new PDOException("La connexion PDO n'a pas été initialisée. Assurez-vous que getInstance() est appelé.");
        }
        return $this->pdoInstance;
    }

    /**
     * Empêche le clonage de l'instance Singleton.
     */
    private function __clone(){}

    /**
     * Empêche la désérialisation de l'instance Singleton.
     *
     * @throws \Exception Si la désérialisation est tentée.
     */
    public function __wakeup(){
        throw new \Exception("La désérialisation du singleton Database n'est pas autorisée.");
    }
}