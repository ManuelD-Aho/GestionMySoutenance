<?php

namespace Backend\Controller;

class authentification
{
    public function showLoginForm(): void
    {
        // Affiche la vue de connexion
        $error = $_SESSION['error_message'] ?? null;
        $this->render('src/Frontend/views/Auth/login.php', ['error' => $error]);
    }

    public function login(): void
    {
        $login = $_POST['login_utilisateur'] ?? '';
        $password = $_POST['mot_de_passe'] ?? '';
        require_once dirname(__DIR__, 2) . '/Config/Database.php';
        require_once dirname(__DIR__, 2) . '/Backend/Model/Utilisateurs.php';
        $pdo = \Database::getInstance()->getConnection();
        $model = new \authentification($pdo);
        $user = $model->authenticate($login, $password);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = $user;
            unset($_SESSION['error_message']);
            header('Location: /dashboard');
            exit;
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
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

    // Vous pouvez copier/coller la méthode render ici ou la mettre dans une classe de base Controller
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullViewPath = dirname(__DIR__, 3) . '/' . $viewPath;

        if (file_exists($fullViewPath)) {
            ob_start();
            include $fullViewPath;
            $content = ob_get_clean();
            echo $content;
        } else {
            http_response_code(500);
            echo "Erreur: La vue '$fullViewPath' est introuvable.";
        }
    }
}

