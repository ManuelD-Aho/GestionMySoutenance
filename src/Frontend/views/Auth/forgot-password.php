<?php
/**
 * Forgot Password page - Production ready password reset interface
 * 
 * Features:
 * - Email validation with rate limiting
 * - Timer cooldown for resend attempts
 * - Modal confirmation feedback
 * - CSRF protection
 * - Accessibility compliance (WCAG 2.1)
 * - GSAP animations
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

// Check for rate limiting
$lastAttempt = $_SESSION['forgot_password_last_attempt'] ?? 0;
$attemptCount = $_SESSION['forgot_password_attempts'] ?? 0;
$cooldownTime = 300; // 5 minutes cooldown
$maxAttempts = 3;
$isRateLimited = $attemptCount >= $maxAttempts && (time() - $lastAttempt) < $cooldownTime;
$remainingTime = $isRateLimited ? $cooldownTime - (time() - $lastAttempt) : 0;
?>

<div class="auth-form-container" id="forgot-password-container">
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

    <!-- Rate Limiting Notice -->
    <?php if ($isRateLimited): ?>
        <div class="alert alert-warning rate-limit-notice" role="alert">
            <i class="fas fa-clock" aria-hidden="true"></i>
            <div class="rate-limit-content">
                <strong>Trop de tentatives</strong>
                <p>Veuillez attendre <span id="countdown-timer"><?= $remainingTime ?></span> secondes avant de réessayer.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Forgot Password Form -->
    <form id="forgotPasswordForm" class="auth-form" method="POST" action="/forgot-password" novalidate <?= $isRateLimited ? 'data-disabled="true"' : '' ?>>
        <!-- CSRF Protection -->
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
        
        <!-- Form Header -->
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-key fa-2x" aria-hidden="true"></i>
            </div>
            <h2 id="forgot-password-title">Mot de passe oublié</h2>
            <p class="form-description">
                Saisissez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
            </p>
        </div>

        <!-- Email Field -->
        <div class="form-group">
            <label for="email" class="form-label required">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                Adresse email
            </label>
            <div class="input-wrapper">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input"
                    required 
                    autocomplete="email"
                    aria-describedby="email_help email_error"
                    aria-invalid="false"
                    placeholder="votre.email@exemple.com"
                    data-validation="email"
                    <?= $isRateLimited ? 'disabled' : '' ?>
                >
                <div class="input-status" aria-hidden="true">
                    <i class="fas fa-check input-valid"></i>
                    <i class="fas fa-times input-invalid"></i>
                </div>
            </div>
            <div id="email_help" class="form-help">
                L'email associé à votre compte GestionMySoutenance
            </div>
            <div id="email_error" class="form-error" role="alert" aria-live="polite"></div>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="btn btn-primary btn-forgot-password" 
            data-loading-text="Envoi en cours..."
            <?= $isRateLimited ? 'disabled' : '' ?>
        >
            <span class="btn-text">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                Envoyer le lien de réinitialisation
            </span>
            <span class="btn-loading" aria-hidden="true">
                <i class="fas fa-spinner fa-spin"></i>
                Envoi en cours...
            </span>
        </button>

        <!-- Security Information -->
        <div class="security-info">
            <div class="info-item">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span>Le lien sera valide pendant 1 heure</span>
            </div>
            <div class="info-item">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                <span>Vérifiez votre dossier spam si vous ne recevez pas l'email</span>
            </div>
        </div>
    </form>

    <!-- Navigation Links -->
    <div class="form-footer">
        <div class="nav-links">
            <a href="/login" class="back-link" aria-label="Retourner à la page de connexion">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour à la connexion
            </a>
        </div>
        
        <div class="help-links">
            <p class="help-text">
                Vous n'avez pas de compte ? 
                <a href="/help" aria-label="Contacter le support pour créer un compte">
                    Contactez le support
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal" role="dialog" aria-labelledby="success-title" aria-hidden="true">
    <div class="modal-overlay"></div>
    <div class="modal-content success-modal">
        <div class="modal-header">
            <div class="success-icon">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <h3 id="success-title">Email envoyé avec succès</h3>
        </div>
        <div class="modal-body">
            <p>Un lien de réinitialisation de mot de passe a été envoyé à votre adresse email.</p>
            <div class="next-steps">
                <h4>Prochaines étapes :</h4>
                <ol>
                    <li>Vérifiez votre boîte de réception</li>
                    <li>Cliquez sur le lien dans l'email</li>
                    <li>Créez un nouveau mot de passe</li>
                </ol>
            </div>
            <div class="resend-info">
                <p>Vous n'avez pas reçu l'email ?</p>
                <button type="button" id="resend-email" class="btn btn-link" disabled>
                    <span class="resend-text">Renvoyer l'email</span>
                    <span class="resend-countdown">(disponible dans <span id="resend-timer">60</span>s)</span>
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Compris</button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Envoi de l'email en cours...</p>
    </div>
</div>

<style>
/* Page-specific styles */
.rate-limit-notice {
    margin-bottom: var(--spacing-lg);
}

.rate-limit-content {
    text-align: center;
}

.form-icon {
    text-align: center;
    margin-bottom: var(--spacing-lg);
    color: var(--primary-blue);
}

.security-info {
    margin-top: var(--spacing-lg);
    padding: var(--spacing-md);
    background: rgba(var(--primary-blue-rgb, 59, 130, 246), 0.05);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--primary-blue);
}

.info-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.info-item:last-child {
    margin-bottom: 0;
}

.nav-links {
    text-align: center;
    margin-bottom: var(--spacing-lg);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: var(--font-weight-medium);
    transition: all var(--transition-fast);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--border-radius-md);
}

.back-link:hover {
    color: var(--primary-blue-dark);
    background: rgba(var(--primary-blue-rgb, 59, 130, 246), 0.1);
    transform: translateX(-2px);
}

.success-modal .modal-content {
    max-width: 500px;
}

.success-icon {
    text-align: center;
    margin-bottom: var(--spacing-md);
}

.success-icon i {
    font-size: 4rem;
    color: var(--primary-green);
}

.next-steps {
    margin: var(--spacing-lg) 0;
    padding: var(--spacing-md);
    background: rgba(var(--primary-green-rgb, 16, 185, 129), 0.05);
    border-radius: var(--border-radius-md);
}

.next-steps h4 {
    margin-bottom: var(--spacing-sm);
    color: var(--primary-green-dark);
}

.next-steps ol {
    padding-left: var(--spacing-lg);
    color: var(--text-secondary);
}

.next-steps li {
    margin-bottom: var(--spacing-xs);
}

.resend-info {
    text-align: center;
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
}

.resend-countdown {
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
}

@media (max-width: 768px) {
    .form-icon i {
        font-size: 1.5rem;
    }
    
    .next-steps ol {
        padding-left: var(--spacing-md);
    }
}
</style>

<script>
// Define page-specific configuration
window.AuthConfig = {
    page: 'forgot-password',
    enableRealTimeValidation: true,
    enableAnimations: true,
    rateLimited: <?= $isRateLimited ? 'true' : 'false' ?>,
    remainingTime: <?= $remainingTime ?>,
    csrf_token: '<?= e($csrf_token ?? '') ?>'
};

// Rate limiting countdown
<?php if ($isRateLimited): ?>
document.addEventListener('DOMContentLoaded', function() {
    let remainingTime = <?= $remainingTime ?>;
    const countdownElement = document.getElementById('countdown-timer');
    const form = document.getElementById('forgotPasswordForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const emailInput = form.querySelector('#email');
    
    const countdown = setInterval(() => {
        remainingTime--;
        countdownElement.textContent = remainingTime;
        
        if (remainingTime <= 0) {
            clearInterval(countdown);
            // Re-enable form
            form.removeAttribute('data-disabled');
            submitButton.disabled = false;
            emailInput.disabled = false;
            document.querySelector('.rate-limit-notice').style.display = 'none';
        }
    }, 1000);
});
<?php endif; ?>

// Auto-focus on email input when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isRateLimited): ?>
    const emailInput = document.getElementById('email');
    if (emailInput && !window.matchMedia('(max-width: 768px)').matches) {
        setTimeout(() => emailInput.focus(), 100);
    }
    <?php endif; ?>
});
</script>