<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions; // Requis par BaseController
use App\Backend\Util\FormValidator;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\ValidationException;
use Exception;

class AuthentificationController extends BaseController
{
    protected ServiceAuthentication $authService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService, // Requis par BaseController
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->authService = $authService; // Réassignation pour un accès direct
    }

    /**
     * Redirige l'utilisateur vers le tableau de bord s'il est déjà connecté.
     */
    protected function requireNoLogin(): void
    {
        // Vérifie si l'utilisateur est déjà connecté ET n'est pas en attente de 2FA
        if ($this->authService->estUtilisateurConnecteEtSessionValide() && !isset($_SESSION['2fa_pending'])) {
            $this->redirect('/dashboard'); // Redirige vers le tableau de bord
        }
    }
    /**
     * Affiche la page d'authentification unifiée avec le formulaire approprié.
     * Détermine le formulaire initial basé sur l'URL ou les messages flash.
     * @param string|null $token Le token de réinitialisation si présent dans l'URL.
     */
    public function showUnifiedAuthPage(?string $token = null): void
    {
        $this->requireNoLogin();

        $data = [
            'page_title' => 'Authentification',
            'current_form' => 'login', // Formulaire par défaut
            'reset_token' => '',
        ];

        // Déterminer le formulaire à afficher en fonction de la route ou des erreurs précédentes
        $requestUri = $_SERVER['REQUEST_URI'];

        if (strpos($requestUri, '/forgot-password') !== false) {
            $data['current_form'] = 'forgot-password';
            $data['page_title'] = 'Mot de passe oublié';
        } elseif (strpos($requestUri, '/reset-password') !== false && $token) {
            try {
                // Vérifier la validité du token avant d'afficher le formulaire de réinitialisation
                $user = $this->authService->utilisateurModel->trouverParTokenResetMdp(hash('sha256', $token));
                if (!$user) {
                    throw new TokenInvalideException("Le lien de réinitialisation est invalide ou a déjà été utilisé.");
                }
                if (new \DateTime() > new \DateTime($user['date_expiration_token_reset'])) {
                    throw new TokenExpireException("Le lien de réinitialisation a expiré.");
                }
                $data['current_form'] = 'reset-password';
                $data['page_title'] = 'Réinitialiser votre mot de passe';
                $data['reset_token'] = $token;
            } catch (TokenExpireException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                // Rediriger vers la page unifiée avec le formulaire de base (login) ou forgot-password
                $this->redirect('/login');
                return; // Stop execution
            } catch (TokenInvalideException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/forgot-password'); // Rediriger pour redemander un lien
                return; // Stop execution
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Une erreur est survenue lors de la validation du lien.');
                error_log("Show reset password form error: " . $e->getMessage());
                $this->redirect('/login');
                return; // Stop execution
            }
        } elseif (strpos($requestUri, '/2fa') !== false) {
            // L'utilisateur doit être en attente de 2FA pour voir ce formulaire
            if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
                $this->setFlashMessage('error', 'Accès non autorisé au formulaire 2FA.');
                $this->redirect('/login');
                return; // Stop execution
            }
            $data['current_form'] = '2fa';
            $data['page_title'] = 'Vérification 2FA';
        }

        // Charger et afficher la vue unifiée
        $this->render('Auth/auth', $data, 'none'); // Pas de layout pour la page d'authentification
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->requireNoLogin(); // Assurer que l'utilisateur n'est pas déjà connecté

        $data = ['page_title' => 'Connexion'];
        $this->render('Auth/login', $data, 'Auth/layout_auth'); // Pas de layout pour la page de login pour être simple
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function handleLogin(): void
    {
        $this->requireNoLogin(); // S'assurer que l'utilisateur n'est pas déjà connecté

        if (!$this->isPostRequest()) {
            $this->redirect('/login');
            return;
        }

        // VÉRIFICATION CSRF : Ajout de cette ligne
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            $this->setFlashMessage('error', "Jeton de sécurité invalide ou expiré. Veuillez réessayer.");
            $this->redirect('/login');
            return;
        }

        // MODIFICATION : Remplacement de getRequestData par post()
        $identifiant = $this->post('login_email');
        $motDePasse = $this->post('password');


        // Validation simple côté contrôleur (règles plus complexes dans le service)
        // La validation doit être faite sur les données après nettoyage par post().
        // Assurez-vous que votre FormValidator est bien injecté.
        $rules = [
            'login_email' => 'required|string|min:3', // Ajout d'une min_length pour un exemple
            'password' => 'required|string|min:8', // Min 8 caractères pour le mot de passe
        ];
        $this->validator->validate(['login_email' => $identifiant, 'password' => $motDePasse], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/login');
            return;
        }

        try {
            $result = $this->authService->tenterConnexion($identifiant, $motDePasse);

            if ($result['status'] === '2fa_required') {
                $this->redirect('/2fa'); // Rediriger vers le formulaire 2FA
            } else if ($result['status'] === 'success') {
                $this->setFlashMessage('success', 'Connexion réussie !');
                $this->redirect('/dashboard'); // Rediriger vers le tableau de bord
            }
        } catch (IdentifiantsInvalidesException $e) {
            $this->setFlashMessage('error', 'Identifiants invalides. Veuillez réessayer.');
            $this->redirect('/login');
        } catch (CompteBloqueException|CompteNonValideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (ValidationException $e) { // Capture ValidationException si le service en lève une
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (Exception $e) { // Capturer d'autres erreurs inattendues
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la connexion.');
            error_log("Login error: " . $e->getMessage()); // Log l'erreur pour le débogage
            $this->redirect('/login');
        }
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('/login');
    }

    /**
     * Affiche le formulaire de mot de passe oublié.
     */
    public function showForgotPasswordForm(): void
    {
        $this->requireNoLogin();
        $data = ['page_title' => 'Mot de passe oublié'];
        $this->render('Auth/forgot_password_form', $data, 'none');
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     */
    public function handleForgotPasswordRequest(): void
    {
        $this->requireNoLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/forgot-password');
            return;
        }

        // VÉRIFICATION CSRF : Ajout de cette ligne
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            $this->setFlashMessage('error', "Jeton de sécurité invalide ou expiré. Veuillez réessayer.");
            $this->redirect('/forgot-password');
            return;
        }

        // MODIFICATION : Remplacement de getRequestData par post()
        $email = $this->post('email');
        $rules = ['email' => 'required|email'];
        $this->validator->validate(['email' => $email], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $this->authService->demanderReinitialisationMotDePasse($email);
            // Toujours afficher un message générique pour des raisons de sécurité (éviter l'énumération d'emails)
            $this->setFlashMessage('success', 'Si votre adresse e-mail est dans notre système, un lien de réinitialisation de mot de passe vous a été envoyé.');
            $this->redirect('/login'); // Rediriger vers la page de connexion
        } catch (EmailException $e) {
            $this->setFlashMessage('error', 'Impossible d\'envoyer l\'e-mail de réinitialisation. Veuillez contacter l\'administrateur.');
            $this->redirect('/forgot-password');
        } catch (ValidationException $e) { // Ajout pour capturer les exceptions de validation du service
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue.');
            error_log("Forgot password error: " . $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe.
     * @param string $token Le token de réinitialisation reçu par email.
     */
    public function showResetPasswordForm(string $token): void
    {
        $this->requireNoLogin();

        try {
            // Vérifier la validité du token avant d'afficher le formulaire
            // Le service gère la logique de hachage et d'expiration
            $user = $this->authService->utilisateurModel->trouverParTokenResetMdp(hash('sha256', $token));
            if (!$user) {
                throw new TokenInvalideException("Le lien de réinitialisation est invalide ou a déjà été utilisé.");
            }
            if (new \DateTime() > new \DateTime($user['date_expiration_token_reset'])) {
                throw new TokenExpireException("Le lien de réinitialisation a expiré.");
            }

            $data = [
                'page_title' => 'Réinitialiser votre mot de passe',
                'token' => $token
            ];
            $this->render('Auth/reset_password_form', $data, 'none');
        } catch (TokenExpireException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (TokenInvalideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password'); // Rediriger vers le formulaire "mot de passe oublié" pour redemander
        } catch (ValidationException $e) { // Ajout pour capturer les exceptions de validation
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Une erreur est survenue lors de la validation du lien.');
            error_log("Reset password form error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe.
     */
    public function handleResetPasswordSubmission(): void
    {
        $this->requireNoLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/login');
            return;
        }

        // VÉRIFICATION CSRF : Ajout de cette ligne
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            $this->setFlashMessage('error', "Jeton de sécurité invalide ou expiré. Veuillez réessayer.");
            $this->redirect('/forgot-password'); // Ou rediriger vers showResetPasswordForm avec le token si possible
            return;
        }

        // MODIFICATION : Remplacement de getRequestData par post()
        $token = $this->post('token');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        $rules = [
            'token' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password', // Assurez-vous que 'same' est géré par votre FormValidator
        ];
        $this->validator->validate(['token' => $token, 'new_password' => $newPassword, 'confirm_password' => $confirmPassword], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/reset-password' . urlencode($token));
            return;
        }

        try {
            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $newPassword);
            $this->setFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException $e) {
            $this->setFlashMessage('error', 'Le lien de réinitialisation a expiré. Veuillez refaire une demande.');
            $this->redirect('/login');
        } catch (TokenInvalideException $e) {
            $this->setFlashMessage('error', 'Le lien de réinitialisation est invalide ou a déjà été utilisé.');
            $this->redirect('/forgot-password');
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', 'Mot de passe invalide: ' . $e->getMessage());
            $this->redirect('/reset-password' . urlencode($token));
        } catch (ValidationException $e) { // Ajout pour capturer les exceptions de validation du service
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/reset-password/' . urlencode($token));

        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la réinitialisation.');
            error_log("Reset password submission error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le formulaire de saisie du code 2FA.
     */
    public function show2FAForm(): void
    {
        // L'utilisateur doit être en attente de 2FA
        if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
            $this->setFlashMessage('error', 'Accès non autorisé au formulaire 2FA.');
            $this->redirect('/login');
        }
        $data = ['page_title' => 'Vérification 2FA'];
        $this->render('Auth/form_2fa', $data, 'Auth/layout_auth');
    }

    /**
     * Traite la soumission du code 2FA.
     */
    public function handle2FASubmission(): void
    {
        // Vérifier que la vérification 2FA est en attente
        if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
            $this->setFlashMessage('error', 'Session 2FA expirée ou accès non autorisé.');
            $this->redirect('/login');
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/2fa');
            return;
        }

        // VÉRIFICATION CSRF : Ajout de cette ligne
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            $this->setFlashMessage('error', "Jeton de sécurité invalide ou expiré. Veuillez réessayer.");
            $this->redirect('/2fa');
            return;
        }

        $userId = $_SESSION['2fa_user_id'];
        $codeTOTP = $this->post('code_2fa');

        $rules = ['code_2fa' => 'required|numeric|length:6']; // Le code TOTP est généralement à 6 chiffres
        $this->validator->validate(['code_2fa' => $codeTOTP], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/2fa');
            return;
        }

        try {
            if ($this->authService->verifierCodeAuthentificationDeuxFacteurs($userId, $codeTOTP)) {
                $this->authService->demarrerSessionUtilisateur($userId); // Démarre la session complète
                $this->setFlashMessage('success', 'Vérification 2FA réussie. Bienvenue !');
                $this->redirect('/dashboard');
            } else {
                throw new IdentifiantsInvalidesException("Code 2FA incorrect. Veuillez réessayer.");
            }
        } catch (IdentifiantsInvalidesException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/2fa');
        } catch (ValidationException $e) { // Ajout pour capturer les exceptions de validation du service
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/2fa');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la vérification 2FA.');
            error_log("2FA submission error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le formulaire de changement de mot de passe (pour utilisateur connecté).
     */
    public function showChangePasswordForm(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        $data = ['page_title' => 'Changer votre mot de passe'];
        $this->render('Auth/change_password_form', $data, 'Auth/layout_auth'); // Utilisez un layout adaptée vue
    }

    /**
     * Traite le changement de mot de passe de l'utilisateur connecté.
     */
    public function handleChangePassword(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/profile/change-password');
            return;
        }

        // VÉRIFICATION CSRF : Ajout de cette ligne
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            $this->setFlashMessage('error', "Jeton de sécurité invalide ou expiré. Veuillez réessayer.");
            $this->redirect('/dashboard/profile/change-password');
            return;
        }

        // MODIFICATION : Remplacement de getRequestData par post()
        $ancienMotDePasse = $this->post('old_password');
        $nouveauMotDePasse = $this->post('new_password');
        $confirmNouveauMotDePasse = $this->post('confirm_new_password');

        $rules = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_new_password' => 'required|string|same:new_password',
        ];
        $this->validator->validate([
            'old_password' => $ancienMotDePasse,
            'new_password' => $nouveauMotDePasse,
            'confirm_new_password' => $confirmNouveauMotDePasse
        ], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/profile/change-password');
            return;
        }

        try {
            $currentUser = $this->getCurrentUser(); // Utilise la méthode du BaseController
            $userId = $currentUser['numero_utilisateur']; // Accès sécurisé

            $this->setFlashMessage('success', 'Votre mot de passe a été modifié avec succès.');
            $this->redirect('/dashboard/profile'); // Rediriger vers la page de profil
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        } catch (ValidationException $e) { // Ajout pour capturer les exceptions de validation du service
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors du changement de mot de passe.');
            error_log("Change password error: " . $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        }
    }

    /**
     * Charge et retourne le contenu HTML d'un partiel de formulaire d'authentification.
     * Cette méthode est appelée par AJAX depuis auth.js.
     */
    public function loadFormPartial(): void
    {
        // On récupère le nom du formulaire demandé depuis les paramètres GET
        $formName = $_GET['name'] ?? 'login_form'; // 'login_form' par défaut
        $token = $_GET['token'] ?? null; // Pour le formulaire de réinitialisation de mot de passe

        // Assurez une validation stricte du nom du formulaire pour éviter les parcours de répertoire (path traversal)
        $allowedForms = [
            'login_form',
            'forgot_password_form',
            'reset_password_form',
            '2fa_form'
        ];

        if (!in_array($formName, $allowedForms)) {
            // Si le formulaire n'est pas autorisé, retourner une erreur 404 ou 400
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Formulaire demandé invalide.']);
            exit;
        }

        // Construire le chemin complet vers le fichier partiel
        // Assurez-vous que ce chemin est correct par rapport à l'emplacement de vos partials
        $partialPath = __DIR__ . '/../../Frontend/views/Auth/partials/' . $formName . '.php';

        if (!file_exists($partialPath)) {
            http_response_code(404); // Not Found
            echo json_encode(['message' => 'Le partiel du formulaire n\'existe pas.']);
            exit;
        }

        // Générer un nouveau token CSRF pour le formulaire qui sera chargé
        // C'est crucial pour la sécurité. Assurez-vous que votre BaseController
        // ou votre système gère la génération de CSRF et qu'il est accessible ici.
        // Exemple simple :
        $csrf_token = $this->generateCsrfToken(); // Appelez la méthode de votre BaseController pour générer un token
        // ou $_SESSION['csrf_token'] si vous le stockez globalement.

        // Mettre en mémoire tampon la sortie pour inclure le fichier partiel
        ob_start();
        // Le partiel peut maintenant accéder à $csrf_token et $token (pour reset_password)
        // en tant que variables locales.
        include $partialPath;
        $htmlContent = ob_get_clean(); // Récupérer le contenu HTML

        // Retourner le contenu HTML et le nouveau token CSRF en JSON
        header('Content-Type: application/json');
        echo json_encode([
            'html' => $htmlContent,
            'csrf_token' => $csrf_token // Envoyer le token pour qu'il soit mis à jour dans le formulaire
        ]);
        exit;
    }
}