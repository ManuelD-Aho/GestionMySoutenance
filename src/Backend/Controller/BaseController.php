<?php
// src/Backend/Controller/BaseController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;
use Exception;

abstract class BaseController
{
    protected ServiceSecuriteInterface $securiteService;
    protected ServiceSupervisionInterface $supervisionService;
    protected FormValidator $validator;

    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        $this->securiteService = $securiteService;
        $this->supervisionService = $supervisionService;
        $this->validator = $validator;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Mettre à jour l'activité de la session
        if (isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Rends une vue PHP avec les données fournies et un layout optionnel.
     *
     * @param string $viewPath Le chemin de la vue (ex: 'Auth/login').
     * @param array $data Les données à passer à la vue.
     * @param string $layout Le chemin du layout (ex: 'layout/app'). Si false, aucun layout n'est utilisé.
     * @throws ElementNonTrouveException Si le fichier de vue ou de layout n'est pas trouvé.
     * @throws Exception En cas d'erreur inattendue lors du rendu.
     */
    protected function render(string $viewPath, array $data = [], string|false $layout = 'layout/app'): void
    {
        // 1. Récupérer les flash messages DE LA SESSION et les passer aux données de la vue.
        // C'est la *seule* fois où ils sont pris de la session.
        $data['flash_messages'] = $_SESSION['flash_messages'] ?? [];
        // 2. VIDER les flash messages de la session IMMÉDIATEMENT après les avoir récupérés.
        // Cela garantit qu'ils ne sont affichés qu'une seule fois.
        unset($_SESSION['flash_messages']);

        // Données utilisateur et menu (toujours présentes)
        $data['user'] = $this->securiteService->getUtilisateurConnecte();
        $data['is_impersonating'] = $this->securiteService->estEnModeImpersonation();
        $data['impersonator_data'] = $this->securiteService->getImpersonatorData();
        $data['menu_items'] = $this->securiteService->construireMenuPourUtilisateurConnecte();

        // Le chemin complet de la vue spécifique (ex: dashboard_admin.php)
        $viewFullPath = ROOT_PATH . '/src/Frontend/views/' . $viewPath . '.php';
        if (!file_exists($viewFullPath)) {
            // S'il y a une erreur de fichier de vue, on log et on lance une exception claire.
            error_log("Fichier de vue non trouvé : " . $viewFullPath);
            throw new ElementNonTrouveException("Fichier de vue non trouvé : " . $viewFullPath);
        }

        // Extrait les données dans le scope local pour la vue et le layout.
        // IMPORTANT: La vue/layout doit lire `$flash_messages` et non `$_SESSION['flash_messages']`.
        extract($data);

        // Capture le contenu HTML de la vue dans $content
        ob_start(); // Démarre la mise en mémoire tampon de sortie
        require $viewFullPath; // Exécute le fichier de vue. Son output est capturé.
        $content = ob_get_clean(); // Récupère le contenu généré et le stocke dans $content, puis vide le tampon.

        if ($layout) {
        $layoutPath = ROOT_PATH . '/src/Frontend/views/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            error_log("Fichier de layout non trouvé : " . $layoutPath);
            throw new ElementNonTrouveException("Fichier de layout non trouvé : " . $layoutPath);
        }
        /* Le layout doit contenir une ligne comme `<?php echo $content;?>' là où le contenu de la vue doit être inséré.*/
        require_once $layoutPath; // Inclut le fichier de layout (ex: app.php)
        } else {
        // Si aucun layout, affiche directement le contenu de la vue capturé.
        echo $content;
        }
    }

    /**
     * Rend une page d'erreur HTTP et termine l'exécution.
     *
     * @param int $statusCode Le code de statut HTTP (ex: 403, 404, 500).
     * @param string $message Le message d'erreur à afficher.
     */
    #[NoReturn]
    public function renderError(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        $viewPath = 'errors/' . $statusCode;
        $data = ['title' => "Erreur {$statusCode}", 'error_message' => $message];
        try {
            $this->render($viewPath, $data, 'Auth/layout_auth');
        } catch (Exception $e) {
            error_log("Erreur critique lors du rendu de la page d'erreur {$statusCode}: " . $e->getMessage());
            echo "<h1>Erreur {$statusCode}</h1><p>{$message}</p><p>Une erreur est survenue lors de l'affichage de la page d'erreur.</p>";
        }
        exit();
    }

    /**
     * Redirige l'utilisateur vers une URL donnée et termine l'exécution.
     *
     * @param string $url L'URL de destination.
     */
    #[NoReturn]
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Ajoute un message flash à la session pour affichage ultérieur.
     *
     * @param string $type Le type de message (ex: 'success', 'error', 'warning', 'info').
     * @param string $message Le contenu du message.
     */
    protected function addFlashMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Récupère et nettoie les données POST.
     *
     * @return array Les données POST nettoyées.
     */
    protected function getPostData(): array
    {
        return filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS, true) ?? [];
    }

    /**
     * Récupère et nettoie les données GET.
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
     * @return array|null Les données du fichier uploadé ou null si non présent.
     */
    protected function getFileData(string $fieldName): ?array
    {
        return $_FILES[$fieldName] ?? null;
    }

    /**
     * Vérifie si la requête HTTP est de type POST.
     *
     * @return bool True si la requête est POST, false sinon.
     */
    protected function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Vérifie si la requête HTTP est de type GET.
     *
     * @return bool True si la requête est GET, false sinon.
     */
    protected function isGetRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Vérifie si l'utilisateur connecté possède une permission spécifique.
     * Si la permission n'est pas accordée, une page d'erreur 403 est affichée.
     *
     * @param string $permissionCode Le code de la permission requise (ex: 'TRAIT_ADMIN_DASHBOARD_ACCEDER').
     * @param string|null $contexteId L'ID de l'entité concernée par la permission (optionnel).
     * @param string|null $contexteType Le type de l'entité concernée (optionnel).
     */
    protected function requirePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): void
    {
        if (!$this->securiteService->estUtilisateurConnecte()) {
            $this->supervisionService->enregistrerAction(
                'ANONYMOUS',
                'ACCES_REFUSE',
                $contexteId,
                $contexteType,
                ['permission_requise' => $permissionCode, 'url' => $_SERVER['REQUEST_URI'], 'reason' => 'Non connecté']
            );
            $this->redirect('/login'); // Rediriger vers la page de connexion si non connecté
        }

        if (!$this->securiteService->utilisateurPossedePermission($permissionCode, $contexteId, $contexteType)) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'],
                'ACCES_REFUSE',
                $contexteId,
                $contexteType,
                ['permission_requise' => $permissionCode, 'url' => $_SERVER['REQUEST_URI']]
            );
            $this->renderError(403, "Vous n'avez pas la permission d'accéder à cette ressource ou d'effectuer cette action.");
        }
    }

    /**
     * Génère un jeton CSRF unique pour un formulaire donné et le stocke en session.
     *
     * @param string $formName Le nom unique du formulaire.
     * @return string Le jeton CSRF généré.
     * @throws RandomException Si la génération de bytes aléatoires échoue.
     */
    protected function generateCsrfToken(string $formName): string
    {
        try {
            if (empty($_SESSION['csrf_tokens'][$formName])) {
                $_SESSION['csrf_tokens'][$formName] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_tokens'][$formName];
        } catch (RandomException $e) {
            error_log("Erreur de génération CSRF pour le formulaire '{$formName}': " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur de sécurité est survenue. Veuillez réessayer.');
            throw $e;
        }
    }

    /**
     * Valide un jeton CSRF soumis avec celui stocké en session.
     * Le jeton est détruit après validation (même en cas d'échec) pour empêcher les attaques par rejeu.
     *
     * @param string $formName Le nom unique du formulaire.
     * @param string $token Le jeton soumis par le formulaire.
     * @return bool True si le jeton est valide, false sinon.
     */
    protected function validateCsrfToken(string $formName, string $token): bool
    {
        if (!isset($_SESSION['csrf_tokens'][$formName]) || !hash_equals($_SESSION['csrf_tokens'][$formName], $token)) {
            unset($_SESSION['csrf_tokens'][$formName]); // Détruire le token même en cas d'échec
            $this->addFlashMessage('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return false;
        }
        unset($_SESSION['csrf_tokens'][$formName]); // Détruire le token après succès
        return true;
    }

    /**
     * Envoie une réponse au format JSON et termine l'exécution du script.
     * Idéal pour les réponses aux requêtes AJAX.
     *
     * @param array $data Le tableau de données à encoder en JSON.
     * @param int $statusCode Le code de statut HTTP à renvoyer (par défaut 200 OK).
     */
    #[NoReturn]
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        header_remove();
        header('Content-Type: application/json');
        http_response_code($statusCode);

        echo json_encode($data);

        exit();
    }
}