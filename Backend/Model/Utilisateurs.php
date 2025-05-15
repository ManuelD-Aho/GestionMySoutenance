<?php
class Utilisateurs {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function authenticate($login_utilisateur, $mot_de_passe) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE login_utilisateur = ?");
        $stmt->execute([$login_utilisateur]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }
}