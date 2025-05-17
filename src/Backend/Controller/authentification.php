<?php

namespace App\Backend\Controller;  // ou Backend\Controller
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Model/Utilisateurs.php';

class authentification {
    private $model;

    public function showForm(): void
    {
        // c’est ici qu’on va inclure ton login.php
        include __DIR__ . '/../../Frontend/views/Auth/login.php';
    }

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->model = new Utilisateurs($pdo);
    }

    public function login() {
        session_start();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login_utilisateur'];
            $password = $_POST['mot_de_passe'];
            $user = $this->model->authenticate($login, $password);

            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: /dashboard');
                exit;
            } else {
                $error = "Login ou mot de passe incorrect.";
            }
        }
        include __DIR__ . '/../../Frontend/views/Auth/login.php';
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}