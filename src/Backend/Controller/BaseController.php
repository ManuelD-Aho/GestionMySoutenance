<?php
// src/Backend/Controller/BaseController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
// use App\Backend\Exception\PermissionException; // Retiré car géré par renderError
use App\Backend\Exception\ElementNonTrouveException;
// use App\Backend\Util\FormValidator; // Retiré car non utilisé directement ici

use JetBrains\PhpStorm\NoReturn;
use Random\RandomException; // Import pour la gestion de l'exception RandomException

abstract class BaseController
{
    protected Container $container;
    protected ServiceSecuriteInterface $securiteService;
    protected ServiceSupervisionInterface $supervisionService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->securiteService = $container->get(ServiceSecuriteInterface::class);
        $this->supervisionService = $container->get(ServiceSupervisionInterface::class);

        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Mettre à jour l'activité de la session
        // La durée d'inactivité est gérée par le ServiceSecurite lors de la connexion
        // Ici, on s'assure juste que la session est active et on met à jour le timestamp
        if (isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Rend une vue en incluant le layout principal.
     *
     * @param string $viewPath Chemin de la vue (ex: 'Auth/login').
     * @param array $data Données à passer à la vue.
     * @param string $layout Chemin du layout (ex: 'layout/app').
     * @return void
     *
     * Note: Le paramètre $viewPath est utilisé implicitement par le layout qui inclut la vue.
     * Le linter peut signaler qu'il est inutilisé, mais c'est la conception voulue.
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'layout/app'): void
    {
        // Récupérer les messages flash
        $data['flash_messages'] = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);

        // Récupérer l'utilisateur connecté et ses permissions
        $data['user'] = $this->securiteService->getUtilisateurConnecte();
        $data['is_impersonating'] = $this->securiteService->estEnModeImpersonation();
        $data['impersonator_data'] = $this->securiteService->getImpersonatorData();

        // Construire le menu de navigation dynamique
        $data['menu_items'] = $this->securiteService->construireMenuPourUtilisateurConnecte();

        // Extraire les données pour les rendre accessibles directement dans la vue
        extract($data);

        // CORRECTION ICI : Utiliser ROOT_PATH pour construire le chemin absolu
        $layoutPath = ROOT_PATH . '/src/Frontend/views/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new ElementNonTrouveException("Layout non trouvé: " . $layoutPath);
        }
        require_once $layoutPath;
    }

    /**
     * Rend une vue d'erreur spécifique.
     *
     * @param int $statusCode Code HTTP de l'erreur (ex: 404, 403, 500).
     * @param string $message Message d'erreur à afficher.
     * @return void
     * @throws ElementNonTrouveException Si le layout d'erreur n'est pas trouvé.
     */
    #[NoReturn]
    public function renderError(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        $viewPath = 'errors/' . $statusCode;
        $data = ['message' => $message];
        // Utiliser un layout minimal pour les erreurs
        $this->render($viewPath, $data, 'layout/layout_auth');
        exit();
    }

    /**
     * Redirige l'utilisateur vers une URL donnée.
     *
     * @param string $url L'URL de destination.
     * @return void
     */
    #[NoReturn]
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Ajoute un message flash à la session.
     *
     * @param string $type Type de message (success, error, warning, info).
     * @param string $message Contenu du message.
     * @return void
     */
    protected function addFlashMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Récupère les données POST de la requête.
     *
     * @return array Les données POST nettoyées.
     */
    protected function getPostData(): array
    {
        // FILTER_SANITIZE_FULL_SPECIAL_CHARS est suffisant pour la plupart des cas
        // Pour des données plus complexes (HTML riche), une validation/nettoyage spécifique est nécessaire.
        return filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS, true) ?? [];
    }

    /**
     * Récupère les données GET de la requête.
     *
     * @return array Les données GET nettoyées.
     */
    protected function getGetData(): array
    {
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS, true) ?? [];
    }

    /**
     * Récupère les données d'un fichier uploadé.
     *
     * @param string $fieldName Le nom du champ de fichier dans le formulaire.
     * @return array|null Les données du fichier ou null si non présent.
     */
    protected function getFileData(string $fieldName): ?array
    {
        return $_FILES[$fieldName] ?? null;
    }

    /**
     * Vérifie si la requête est de type POST.
     *
     * @return bool
     */
    protected function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Vérifie si la requête est de type GET.
     *
     * @return bool
     */
    protected function isGetRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Vérifie la permission de l'utilisateur connecté.
     * Redirige vers une page 403 si la permission est refusée.
     *
     * @param string $permissionCode Le code de la permission requise.
     * @param string|null $contexteId L'ID de l'entité concernée par la permission.
     * @param string|null $contexteType Le type de l'entité concernée par la permission.
     * @return void
     * @throws \App\Backend\Exception\PermissionException Si l'utilisateur n'a pas la permission.
     */
    protected function requirePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): void
    {
        if (!$this->securiteService->utilisateurPossedePermission($permissionCode, $contexteId, $contexteType)) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'ANONYMOUS',
                'ACCES_REFUSE',
                $contexteId,
                $contexteType,
                ['permission_requise' => $permissionCode, 'url' => $_SERVER['REQUEST_URI']]
            );
            // Lève une exception pour que le routeur ou un gestionnaire d'erreurs global puisse la capturer
            // ou, comme implémenté ici, appelle directement renderError.
            $this->renderError(403, "Vous n'avez pas la permission d'accéder à cette ressource ou d'effectuer cette action.");
        }
    }

    /**
     * Génère et vérifie un jeton CSRF pour protéger les formulaires.
     *
     * @param string $formName Nom unique du formulaire.
     * @return string Le jeton CSRF à inclure dans le formulaire.
     * @throws RandomException Si une source de hasard cryptographiquement sûre n'est pas disponible.
     */
    protected function generateCsrfToken(string $formName): string
    {
        try {
            if (empty($_SESSION['csrf_tokens'][$formName])) {
                $_SESSION['csrf_tokens'][$formName] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_tokens'][$formName];
        } catch (RandomException $e) {
            // Log l'erreur et/ou affiche un message générique à l'utilisateur
            error_log("Erreur de génération CSRF: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur de sécurité est survenue. Veuillez réessayer.');
            // En production, on pourrait rediriger ou afficher une page d'erreur critique.
            throw $e; // Re-lancer l'exception pour une gestion plus globale si nécessaire
        }
    }

    /**
     * Valide un jeton CSRF soumis.
     *
     * @param string $formName Nom unique du formulaire.
     * @param string $token Le jeton soumis par l'utilisateur.
     * @return bool Vrai si le jeton est valide, faux sinon.
     */
    protected function validateCsrfToken(string $formName, string $token): bool
    {
        if (!isset($_SESSION['csrf_tokens'][$formName]) || $_SESSION['csrf_tokens'][$formName] !== $token) {
            unset($_SESSION['csrf_tokens'][$formName]); // Invalide le token après usage ou échec
            $this->addFlashMessage('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return false;
        }
        unset($_SESSION['csrf_tokens'][$formName]); // Le token est à usage unique
        return true;
    }
}