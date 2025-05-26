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
            // --- MODIFICATION IMPORTANTE ICI ---
            // Comparaison directe du mot de passe fourni avec celui en base de données
            // Assurez-vous que la colonne dans votre base de données s'appelle bien 'mot_de_passe'
            // et qu'elle contient les mots de passe en clair.
            if ($mot_de_passe_fourni === $user['mot_de_passe']) {
                // Le mot de passe en clair correspond
                return $user;
            }
            // --- FIN DE LA MODIFICATION ---
        }
        return false; // L'utilisateur n'a pas été trouvé ou le mot de passe ne correspond pas
    }

    // Si vous aviez une méthode create qui hachait le mot de passe,
    // vous devrez également la modifier pour enregistrer le mot de passe en clair.
    // Par exemple, si vous activez la méthode create commentée :
    /*
    public function create(array $data): bool|string // Modifié pour correspondre à la signature de BaseModel
    {
        // NE PAS HACHER LE MOT DE PASSE SI VOUS LE VOULEZ EN CLAIR
        // if (isset($data['mot_de_passe'])) {
        //     $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        // }
        return parent::create($data); // Appelle la méthode create de BaseModel
    }
    */
}