<?php
// src/Backend/Controller/AuthentificationController.php

namespace Backend\Controller;

use Config\Database; // Assurez-vous que cette ligne est présente et correcte
use App\Backend\Model\Utilisateur;
// Si votre classe TypeUtilisateur n'est pas (encore) namespacée et gérée par Composer,
// vous pourriez avoir besoin de l'inclure manuellement.
// Cependant, la meilleure approche est de la namespacer (ex: Backend\Model\TypeUtilisateur)
// et de la faire gérer par l'autoloader de Composer.
// Exemple si elle était globale (à éviter si possible) :
// require_once ROOT_PATH . '/src/Backend/Model/TypeUtilisateur.php';

class AuthentificationController extends BaseController // Assurez-vous que BaseController est accessible
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Récupérer le message d'erreur depuis la session, s'il existe
        // (comme dans votre vue login.php originale)
        $error_message = null;
        if (isset($_SESSION['error_message'])) {
            $error_message = $_SESSION['error_message'];
            // Il est préférable de ne pas unset le message ici, mais plutôt après l'avoir affiché dans la vue
            // ou de le passer directement à la vue et le supprimer ensuite dans Public/index.php.
            // Pour l'instant, on le passe à la vue.
        }

        // La méthode render est héritée de BaseController
        // Elle devrait savoir comment trouver 'src/Frontend/views/Auth/login.php'
        // en se basant sur ROOT_PATH.
        $this->render('src/Frontend/views/Auth/login.php', ['error_message' => $error_message]);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $login = $_POST['login_utilisateur'] ?? '';
        $password = $_POST['mot_de_passe'] ?? '';

        try {
            $pdo = Database::getInstance()->getConnection();
        } catch (\Throwable $e) { // Catch Throwable pour intercepter les erreurs fatales aussi
            error_log("Erreur critique lors de l'accès à la base de données (AuthentificationController): " . $e->getMessage());
            $_SESSION['error_message'] = "Une erreur serveur critique est survenue. Veuillez réessayer plus tard. (Code: DBINIT)";
            header('Location: /login');
            exit;
        }

        $userModel = new Utilisateur($pdo);
        $user = $userModel->authenticate($login, $password);

        if ($user) {
            $_SESSION['user'] = $user; // Informations de base de l'utilisateur

            if (isset($user['id_type_utilisateur'])) {
                $userTypeId = (int) $user['id_type_utilisateur'];
                $_SESSION['user_role_id'] = $userTypeId;

                try {
                    // Assurez-vous que TypeUtilisateurModel est correctement instancié.
                    // Le namespace Backend\Model\TypeUtilisateur est une supposition, adaptez si besoin.
                    $typeUserModel = new \App\Backend\Model\TypeUtilisateur($pdo);
                    $typeInfo = $typeUserModel->find($userTypeId); // find() vient de BaseModel

                    if ($typeInfo && isset($typeInfo['lib_type_utilisateur'])) {
                        $_SESSION['user_role_label'] = $typeInfo['lib_type_utilisateur'];
                    } else {
                        $_SESSION['user_role_label'] = 'Rôle Inconnu';
                        error_log("Type d'utilisateur (ID: " . $userTypeId . ") non trouvé ou libellé manquant pour l'utilisateur " . htmlspecialchars($user['login_utilisateur']));
                    }
                } catch (\Throwable $e) { // Catch Throwable
                    $_SESSION['user_role_label'] = 'Erreur Rôle';
                    error_log("Erreur lors de la récupération du rôle pour l'utilisateur " . htmlspecialchars($user['login_utilisateur']) . " (ID Type: " . $userTypeId . "): " . $e->getMessage());
                }
            } else {
                $_SESSION['user_role_id'] = null;
                $_SESSION['user_role_label'] = 'Rôle Non Défini';
                error_log("L'utilisateur " . htmlspecialchars($user['login_utilisateur']) . " n'a pas de 'id_type_utilisateur' défini.");
            }

            // Nettoyer le message d'erreur s'il y en avait un d'une tentative précédente
            if (isset($_SESSION['error_message'])) {
                unset($_SESSION['error_message']);
            }

            header('Location: /dashboard'); // Rediriger vers le tableau de bord
            exit;
        } else {
            $_SESSION['error_message'] = "Identifiant ou mot de passe incorrect.";
            header('Location: /login');
            exit;
        }
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();    // Supprime toutes les variables de session
        session_destroy();  // Détruit la session elle-même

        header('Location: /login'); // Rediriger vers la page de connexion
        exit;
    }
}