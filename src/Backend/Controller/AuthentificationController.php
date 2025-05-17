<?php
namespace Backend\Controller;
use Database;
use Backend\Model\Utilisateur;

class AuthentificationController extends BaseController
{
    public function showLoginForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Affiche la vue de connexion
        $error = $_SESSION['error_message'] ?? null;
        // La méthode render est maintenant héritée de BaseController
        $this->render('src/Frontend/views/Auth/login.php', ['error' => $error]);
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) { // Assurer que la session est démarrée
            session_start();
        }

        $login = $_POST['login_utilisateur'] ?? '';
        $password = $_POST['mot_de_passe'] ?? '';
        $pdo = Database::getInstance()->getConnection();
        $userModel = new Utilisateur($pdo); // Utilisation du modèle refactorisé
        $user = $userModel->authenticate($login, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            unset($_SESSION['error_message']); // Nettoyer le message d'erreur en cas de succès
            header('Location: /dashboard'); // Assure-toi que cette route existe
            exit;
        } else {
            $_SESSION['error_message'] = "Login ou mot de passe incorrect.";
            header('Location: /login');
            exit;
        }
    }
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}