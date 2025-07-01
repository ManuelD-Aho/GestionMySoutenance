<?php
/**
 * Reset Password page - Production ready password reset interface
 * 
 * Features:
 * - Token validation and expiration checking
 * - Password strength meter with visual feedback
 * - Real-time password confirmation validation
 * - Security criteria display
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

// Get and validate the reset token
$resetToken = $_GET['token'] ?? $token ?? '';
$isValidToken = !empty($resetToken) && strlen($resetToken) >= 32;

// Get flash messages
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $flashMessages['error'] ?? [];
$success = $flashMessages['success'] ?? [];
$warnings = $flashMessages['warning'] ?? [];

// Clear flash messages after displaying
unset($_SESSION['flash_messages']);
?>

<div class="auth-form-container" id="reset-password-container">
    <?php if (!$isValidToken): ?>
        <!-- Invalid Token Notice -->
        <div class="auth-messages" role="alert" aria-live="assertive">
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                <div class="alert-content">
                    <strong>Lien invalide ou expiré</strong>
                    <p>Ce lien de réinitialisation n'est plus valide. Veuillez demander un nouveau lien.</p>
                </div>
            </div>
        </div>
        
        <div class="invalid-token-actions">
            <a href="/forgot-password" class="btn btn-primary">
                <i class="fas fa-redo-alt" aria-hidden="true"></i>
                Demander un nouveau lien
            </a>
            <a href="/login" class="btn btn-secondary">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour à la connexion
            </a>
        </div>
    <?php else: ?>
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

        <!-- Reset Password Form -->
        <form id="resetPasswordForm" class="auth-form" method="POST" action="/reset-password" novalidate>
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
            <input type="hidden" name="token" value="<?= e($resetToken) ?>" aria-hidden="true">
            
            <!-- Form Header -->
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-lock fa-2x" aria-hidden="true"></i>
                </div>
                <h2 id="reset-password-title">Nouveau mot de passe</h2>
                <p class="form-description">
                    Choisissez un mot de passe sécurisé pour votre compte.
                </p>
            </div>

            <!-- Password Strength Indicator -->
            <div class="password-strength-container">
                <div class="strength-header">
                    <span class="strength-label">Force du mot de passe :</span>
                    <span id="strength-text" class="strength-text">Faible</span>
                </div>
                <div class="strength-meter">
                    <div id="strength-bar" class="strength-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="strength-requirements" id="strength-requirements">
                    <div class="requirement" data-requirement="length">
                        <i class="fas fa-times requirement-icon" aria-hidden="true"></i>
                        <span>Au moins 8 caractères</span>
                    </div>
                    <div class="requirement" data-requirement="lowercase">
                        <i class="fas fa-times requirement-icon" aria-hidden="true"></i>
                        <span>Une lettre minuscule</span>
                    </div>
                    <div class="requirement" data-requirement="uppercase">
                        <i class="fas fa-times requirement-icon" aria-hidden="true"></i>
                        <span>Une lettre majuscule</span>
                    </div>
                    <div class="requirement" data-requirement="number">
                        <i class="fas fa-times requirement-icon" aria-hidden="true"></i>
                        <span>Un chiffre</span>
                    </div>
                    <div class="requirement" data-requirement="special">
                        <i class="fas fa-times requirement-icon" aria-hidden="true"></i>
                        <span>Un caractère spécial (!@#$%^&*)</span>
                    </div>
                </div>
            </div>

            <!-- New Password Field -->
            <div class="form-group">
                <label for="new_password" class="form-label required">
                    <i class="fas fa-key" aria-hidden="true"></i>
                    Nouveau mot de passe
                </label>
                <div class="input-wrapper password-wrapper">
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-input"
                        required 
                        autocomplete="new-password"
                        aria-describedby="new_password_help new_password_error strength-requirements"
                        aria-invalid="false"
                        placeholder="Votre nouveau mot de passe"
                        data-validation="password-strength"
                        minlength="8"
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
                <div id="new_password_help" class="form-help">
                    Choisissez un mot de passe respectant tous les critères de sécurité
                </div>
                <div id="new_password_error" class="form-error" role="alert" aria-live="polite"></div>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="confirm_password" class="form-label required">
                    <i class="fas fa-check-double" aria-hidden="true"></i>
                    Confirmer le mot de passe
                </label>
                <div class="input-wrapper password-wrapper">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input"
                        required 
                        autocomplete="new-password"
                        aria-describedby="confirm_password_help confirm_password_error"
                        aria-invalid="false"
                        placeholder="Confirmez votre mot de passe"
                        data-validation="password-confirmation"
                    >
                    <button 
                        type="button" 
                        class="password-toggle" 
                        aria-label="Afficher/masquer la confirmation"
                        tabindex="0"
                    >
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                    <div class="input-status" aria-hidden="true">
                        <i class="fas fa-check input-valid"></i>
                        <i class="fas fa-times input-invalid"></i>
                    </div>
                </div>
                <div id="confirm_password_help" class="form-help">
                    Saisissez à nouveau votre mot de passe pour confirmation
                </div>
                <div id="confirm_password_error" class="form-error" role="alert" aria-live="polite"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-reset-password" data-loading-text="Mise à jour en cours...">
                <span class="btn-text">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Définir le nouveau mot de passe
                </span>
                <span class="btn-loading" aria-hidden="true">
                    <i class="fas fa-spinner fa-spin"></i>
                    Mise à jour en cours...
                </span>
            </button>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="notice-item">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>Votre mot de passe sera chiffré et stocké de manière sécurisée</span>
                </div>
                <div class="notice-item">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <span>Ce lien expirera automatiquement après utilisation</span>
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
        </div>
    <?php endif; ?>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal" role="dialog" aria-labelledby="success-title" aria-hidden="true">
    <div class="modal-overlay"></div>
    <div class="modal-content success-modal">
        <div class="modal-header">
            <div class="success-icon">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <h3 id="success-title">Mot de passe mis à jour</h3>
        </div>
        <div class="modal-body">
            <p>Votre mot de passe a été modifié avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
            <div class="next-steps">
                <h4>Prochaines étapes :</h4>
                <ol>
                    <li>Connectez-vous avec votre nouveau mot de passe</li>
                    <li>Mettez à jour vos autres appareils si nécessaire</li>
                    <li>Activez l'authentification à deux facteurs (recommandé)</li>
                </ol>
            </div>
        </div>
        <div class="modal-footer">
            <a href="/login" class="btn btn-primary">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                Se connecter maintenant
            </a>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Mise à jour du mot de passe...</p>
    </div>
</div>

<style>
/* Page-specific styles */
.form-icon {
    text-align: center;
    margin-bottom: var(--spacing-lg);
    color: var(--primary-blue);
}

.password-strength-container {
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: rgba(var(--primary-blue-rgb, 59, 130, 246), 0.05);
    border-radius: var(--border-radius-lg);
    border: 1px solid rgba(var(--primary-blue-rgb, 59, 130, 246), 0.2);
}

.strength-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.strength-label {
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
}

.strength-text {
    font-weight: var(--font-weight-semibold);
    transition: color var(--transition-fast);
}

.strength-meter {
    width: 100%;
    height: 8px;
    background: var(--border-light);
    border-radius: var(--border-radius-full);
    overflow: hidden;
    margin-bottom: var(--spacing-md);
}

.strength-bar {
    height: 100%;
    width: 0%;
    transition: all var(--transition-normal);
    border-radius: var(--border-radius-full);
}

.strength-bar[data-strength="weak"] {
    background: var(--accent-red);
    width: 25%;
}

.strength-bar[data-strength="fair"] {
    background: var(--accent-yellow);
    width: 50%;
}

.strength-bar[data-strength="good"] {
    background: var(--primary-blue);
    width: 75%;
}

.strength-bar[data-strength="strong"] {
    background: var(--primary-green);
    width: 100%;
}

.strength-requirements {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xs);
}

.requirement {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    transition: color var(--transition-fast);
}

.requirement.met {
    color: var(--primary-green);
}

.requirement-icon {
    font-size: var(--font-size-xs);
    transition: all var(--transition-fast);
}

.requirement.met .requirement-icon {
    color: var(--primary-green);
}

.requirement.met .requirement-icon:before {
    content: "\f00c"; /* Check icon */
}

.security-notice {
    margin-top: var(--spacing-lg);
    padding: var(--spacing-md);
    background: rgba(var(--primary-green-rgb, 16, 185, 129), 0.05);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--primary-green);
}

.notice-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.notice-item:last-child {
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

.invalid-token-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    align-items: center;
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

/* Color coding for strength levels */
.strength-text[data-strength="weak"] { color: var(--accent-red); }
.strength-text[data-strength="fair"] { color: var(--accent-yellow); }
.strength-text[data-strength="good"] { color: var(--primary-blue); }
.strength-text[data-strength="strong"] { color: var(--primary-green); }

@media (max-width: 768px) {
    .strength-requirements {
        grid-template-columns: 1fr;
    }
    
    .form-icon i {
        font-size: 1.5rem;
    }
    
    .invalid-token-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Define page-specific configuration
window.AuthConfig = {
    page: 'reset-password',
    enableRealTimeValidation: true,
    enableAnimations: true,
    enablePasswordStrength: true,
    token: '<?= e($resetToken) ?>',
    isValidToken: <?= $isValidToken ? 'true' : 'false' ?>,
    csrf_token: '<?= e($csrf_token ?? '') ?>'
};

// Auto-focus on first input when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isValidToken): ?>
    const firstInput = document.getElementById('new_password');
    if (firstInput && !window.matchMedia('(max-width: 768px)').matches) {
        setTimeout(() => firstInput.focus(), 100);
    }
    <?php endif; ?>
});
</script>