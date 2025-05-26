<?php
// src/Backend/Model/Utilisateur.php

namespace Backend\Model;

use PDO;

class Utilisateur extends BaseModel {

    protected string $table = 'utilisateur'; // Nom de la table correspondante
    protected string $primaryKey = 'id_utilisateur'; // Clé primaire de la table utilisateur

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    public function authenticate($login_utilisateur, $mot_de_passe_fourni) {
        // La connexion $this->db est disponible grâce à l'héritage de BaseModel
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE login_utilisateur = :login_utilisateur");
        $stmt->bindParam(':login_utilisateur', $login_utilisateur);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérifier le mot de passe fourni avec le mot de passe haché en base de données
            if (password_verify($mot_de_passe_fourni, $user['mot_de_passe'])) {
                // Le mot de passe est correct
                return $user;
            }
        }
        return false; // L'utilisateur n'a pas été trouvé ou le mot de passe ne correspond pas
    }

    public function create(array $data): string|false
    {
        // Hacher le mot de passe avant de l'enregistrer
        if (isset($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }
        return parent::create($data); // Appelle la méthode create de BaseModel
    }
}