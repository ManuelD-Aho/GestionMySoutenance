<?php
/**
 * Login page - Production ready authentication interface
 * 
 * Features:
 * - CSRF protection
 * - Real-time validation
 * - Accessibility compliance (WCAG 2.1)
 * - Responsive design
 * - GSAP animations
 * - Security features (rate limiting, captcha support)
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

// Function to safely output HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Get flash messages
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $flashMessages['error'] ?? [];
$success = $flashMessages['success'] ?? [];
$warnings = $flashMessages['warning'] ?? [];

// Clear flash messages after displaying
unset($_SESSION['flash_messages']);
?>

<div class="auth-form-container" id="login-container">
    <!-- Flash Messages -->
    <?php if (!empty($errors) || !empty($success) || !empty($warnings)): ?>
        <div class="auth-messages" role="alert" aria-live="assertive">
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endforeach; ?>
            
            <?php foreach ($success as $message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span><?= e($message) ?></span>
                </div>
            <?php endforeach; ?>
            
            <?php foreach ($warnings as $warning): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    <span><?= e($warning) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form id="loginForm" class="auth-form" method="POST" action="/login" novalidate>
        <!-- CSRF Protection -->
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
        
        <!-- Form Header -->
        <div class="form-header">
            <h2 id="login-title">Connexion</h2>
            <p class="form-description">Connectez-vous à votre compte</p>
        </div>

        <!-- Email/Username Field -->
        <div class="form-group">
            <label for="login_email" class="form-label required">
                <i class="fas fa-user" aria-hidden="true"></i>
                Email ou nom d'utilisateur
            </label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="login_email" 
                    name="login_email" 
                    class="form-input"
                    required 
                    autocomplete="username"
                    aria-describedby="login_email_help login_email_error"
                    aria-invalid="false"
                    placeholder="Votre email ou nom d'utilisateur"
                    data-validation="email-or-username"
                >
                <div class="input-status" aria-hidden="true">
                    <i class="fas fa-check input-valid"></i>
                    <i class="fas fa-times input-invalid"></i>
                </div>
            </div>
            <div id="login_email_help" class="form-help">
                Saisissez votre adresse email ou votre nom d'utilisateur
            </div>
            <div id="login_email_error" class="form-error" role="alert" aria-live="polite"></div>
        </div>

        <!-- Password Field -->
        <div class="form-group">
            <label for="password" class="form-label required">
                <i class="fas fa-lock" aria-hidden="true"></i>
                Mot de passe
            </label>
            <div class="input-wrapper password-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input"
                    required 
                    autocomplete="current-password"
                    aria-describedby="password_help password_error"
                    aria-invalid="false"
                    placeholder="Votre mot de passe"
                    data-validation="password"
                    minlength="6"
                >
                <button 
                    type="button" 
                    class="password-toggle" 
                    aria-label="Afficher/masquer le mot de passe"
                    tabindex="0"
                >
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
                <div class="input-status" aria-hidden="true">
                    <i class="fas fa-check input-valid"></i>
                    <i class="fas fa-times input-invalid"></i>
                </div>
            </div>
            <div id="password_help" class="form-help">
                Saisissez votre mot de passe (minimum 6 caractères)
            </div>
            <div id="password_error" class="form-error" role="alert" aria-live="polite"></div>
        </div>

        <!-- Form Options -->
        <div class="form-options">
            <div class="checkbox-group">
                <label class="checkbox-container">
                    <input 
                        type="checkbox" 
                        name="remember_me" 
                        id="remember_me"
                        value="1"
                        aria-describedby="remember_help"
                    >
                    <span class="checkmark" aria-hidden="true"></span>
                    <span class="checkbox-label">Se souvenir de moi</span>
                </label>
                <div id="remember_help" class="checkbox-help">
                    Garder ma session active pendant 30 jours
                </div>
            </div>
            
            <a href="/forgot-password" class="forgot-link" aria-label="Réinitialiser le mot de passe">
                <i class="fas fa-key" aria-hidden="true"></i>
                Mot de passe oublié ?
            </a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-login" data-loading-text="Connexion en cours...">
            <span class="btn-text">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                Se connecter
            </span>
            <span class="btn-loading" aria-hidden="true">
                <i class="fas fa-spinner fa-spin"></i>
                Connexion en cours...
            </span>
        </button>

        <!-- Security Notice -->
        <div class="security-notice">
            <i class="fas fa-shield-alt" aria-hidden="true"></i>
            <span>Connexion sécurisée SSL/TLS</span>
        </div>
    </form>

    <!-- Additional Links -->
    <div class="form-footer">
        <p class="help-text">
            Problème de connexion ? 
            <a href="/help" aria-label="Obtenir de l'aide pour la connexion">
                Contactez le support
            </a>
        </p>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Vérification en cours...</p>
    </div>
</div>

<!-- Captcha Modal (if needed) -->
<div id="captcha-modal" class="modal" role="dialog" aria-labelledby="captcha-title" aria-hidden="true">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="captcha-title">Vérification de sécurité</h3>
            <button type="button" class="modal-close" aria-label="Fermer la vérification">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Pour votre sécurité, veuillez confirmer que vous n'êtes pas un robot.</p>
            <!-- Captcha content would be inserted here -->
            <div id="captcha-container"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" id="verify-captcha">Vérifier</button>
        </div>
    </div>
</div>

<script>
// Define page-specific configuration
window.AuthConfig = {
    page: 'login',
    enableRealTimeValidation: true,
    enableAnimations: true,
    maxAttempts: 3,
    requireCaptcha: false,
    csrf_token: '<?= e($csrf_token ?? '') ?>'
};

// Auto-focus on first input when page loads
document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.getElementById('login_email');
    if (firstInput && !window.matchMedia('(max-width: 768px)').matches) {
        // Only auto-focus on desktop to avoid mobile keyboard issues
        setTimeout(() => firstInput.focus(), 100);
    }
});
</script>