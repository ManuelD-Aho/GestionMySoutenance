<?php

namespace Backend\Model;

use PDO;

class Utilisateur extends BaseModel {

    protected string $table = 'utilisateur'; // Nom de la table correspondante
    protected string $primaryKey = 'id_utilisateur'; // Clé primaire de la table utilisateur

    // Le constructeur est hérité de BaseModel, mais si on veut être explicite :
    public function __construct(PDO $pdo) {
        parent::__construct($pdo); // Appel du constructeur parent
        // $this->table et $this->primaryKey sont déjà définis ci-dessus
    }

    public function authenticate($login_utilisateur, $mot_de_passe) {
        // La connexion $this->db est disponible grâce à l'héritage de BaseModel
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE login_utilisateur = :login_utilisateur");
        $stmt->bindParam(':login_utilisateur', $login_utilisateur);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    // Les méthodes CRUD génériques (findAll, find, create, update, delete)
    // sont maintenant héritées de BaseModel.
    // Si tu as besoin de versions spécifiques pour les utilisateurs, tu peux les surcharger ici.
    // Par exemple, pour créer un utilisateur, tu voudras probablement hacher le mot de passe :
    /*
    public function create(array $data): bool
    {
        if (isset($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }
        return parent::create($data); // Appelle la méthode create de BaseModel
    }
    */
}