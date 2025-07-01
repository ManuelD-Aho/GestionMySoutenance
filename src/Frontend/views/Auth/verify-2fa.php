<?php
/**
 * 2FA Verification page - Production ready two-factor authentication interface
 * 
 * Features:
 * - 6-digit code input with automatic focus progression
 * - QR code setup for initial configuration
 * - Recovery codes display and management
 * - Timer for code expiration
 * - Resend functionality with cooldown
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

// Check if user is in 2FA pending state
$is2FARequired = isset($_SESSION['2fa_pending']) && isset($_SESSION['2fa_user_id']);
if (!$is2FARequired) {
    header('Location: /login');
    exit;
}

// Get flash messages
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $flashMessages['error'] ?? [];
$success = $flashMessages['success'] ?? [];
$warnings = $flashMessages['warning'] ?? [];

// Clear flash messages after displaying
unset($_SESSION['flash_messages']);

// Check if this is initial 2FA setup
$isInitialSetup = $_SESSION['2fa_setup'] ?? false;
$qrCodeData = $_SESSION['2fa_qr_code'] ?? '';
$recoveryCodes = $_SESSION['2fa_recovery_codes'] ?? [];

// Rate limiting for resend attempts
$lastResend = $_SESSION['2fa_last_resend'] ?? 0;
$resendAttempts = $_SESSION['2fa_resend_attempts'] ?? 0;
$resendCooldown = 60; // 1 minute cooldown
$maxResendAttempts = 3;
$canResend = $resendAttempts < $maxResendAttempts && (time() - $lastResend) >= $resendCooldown;
$nextResendTime = $canResend ? 0 : $resendCooldown - (time() - $lastResend);
?>

<div class="auth-form-container" id="verify-2fa-container">
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

    <?php if ($isInitialSetup): ?>
        <!-- Initial 2FA Setup -->
        <div class="setup-container" id="setup-step-1">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-shield-alt fa-2x" aria-hidden="true"></i>
                </div>
                <h2>Configuration de l'authentification à deux facteurs</h2>
                <p class="form-description">
                    Configurez l'authentification à deux facteurs pour sécuriser votre compte.
                </p>
            </div>

            <div class="setup-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Scanez le QR Code</h3>
                        <p>Utilisez votre application d'authentification (Google Authenticator, Authy, etc.)</p>
                    </div>
                </div>
                
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Vérifiez le code</h3>
                        <p>Saisissez le code généré par votre application</p>
                    </div>
                </div>
                
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Sauvegardez les codes de récupération</h3>
                        <p>Gardez ces codes en lieu sûr pour récupérer votre compte</p>
                    </div>
                </div>
            </div>

            <?php if ($qrCodeData): ?>
                <div class="qr-code-container">
                    <h3>Scanez ce QR Code</h3>
                    <div class="qr-code">
                        <img src="data:image/png;base64,<?= e($qrCodeData) ?>" alt="QR Code pour l'authentification à deux facteurs" />
                    </div>
                    <div class="qr-instructions">
                        <ol>
                            <li>Ouvrez votre application d'authentification</li>
                            <li>Scannez ce QR code</li>
                            <li>Saisissez le code à 6 chiffres généré</li>
                        </ol>
                    </div>
                    <button type="button" class="btn btn-link toggle-manual-entry">
                        <i class="fas fa-keyboard" aria-hidden="true"></i>
                        Saisir le code manuellement
                    </button>
                    <div class="manual-entry" style="display: none;">
                        <p>Code à saisir manuellement :</p>
                        <code class="manual-code"><?= e($_SESSION['2fa_secret'] ?? '') ?></code>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 2FA Verification Form -->
    <form id="verify2FAForm" class="auth-form <?= $isInitialSetup ? 'setup-form' : '' ?>" method="POST" action="/2fa" novalidate>
        <!-- CSRF Protection -->
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
        
        <?php if (!$isInitialSetup): ?>
            <!-- Standard 2FA Header -->
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-mobile-alt fa-2x" aria-hidden="true"></i>
                </div>
                <h2 id="verify-2fa-title">Authentification à deux facteurs</h2>
                <p class="form-description">
                    Saisissez le code de vérification généré par votre application d'authentification.
                </p>
            </div>
        <?php else: ?>
            <!-- Setup 2FA Header -->
            <div class="form-header">
                <h3>Vérifiez votre configuration</h3>
                <p class="form-description">
                    Saisissez le code à 6 chiffres généré par votre application pour confirmer la configuration.
                </p>
            </div>
        <?php endif; ?>

        <!-- Code Expiration Timer -->
        <div class="timer-container" id="timer-container">
            <div class="timer-icon">
                <i class="fas fa-clock" aria-hidden="true"></i>
            </div>
            <div class="timer-content">
                <span class="timer-label">Code valide pendant :</span>
                <span class="timer-value" id="timer-value">05:00</span>
            </div>
        </div>

        <!-- 2FA Code Input -->
        <div class="form-group">
            <label for="code_2fa_1" class="form-label required">
                <i class="fas fa-key" aria-hidden="true"></i>
                Code de vérification
            </label>
            <div class="code-input-container">
                <div class="code-inputs" role="group" aria-labelledby="verify-2fa-title">
                    <input type="text" id="code_2fa_1" name="code_2fa_1" class="code-input" maxlength="1" autocomplete="off" aria-label="Premier chiffre du code">
                    <input type="text" id="code_2fa_2" name="code_2fa_2" class="code-input" maxlength="1" autocomplete="off" aria-label="Deuxième chiffre du code">
                    <input type="text" id="code_2fa_3" name="code_2fa_3" class="code-input" maxlength="1" autocomplete="off" aria-label="Troisième chiffre du code">
                    <input type="text" id="code_2fa_4" name="code_2fa_4" class="code-input" maxlength="1" autocomplete="off" aria-label="Quatrième chiffre du code">
                    <input type="text" id="code_2fa_5" name="code_2fa_5" class="code-input" maxlength="1" autocomplete="off" aria-label="Cinquième chiffre du code">
                    <input type="text" id="code_2fa_6" name="code_2fa_6" class="code-input" maxlength="1" autocomplete="off" aria-label="Sixième chiffre du code">
                </div>
                <input type="hidden" name="code_2fa" id="code_2fa_hidden" required>
            </div>
            <div class="form-help">
                Saisissez le code à 6 chiffres de votre application d'authentification
            </div>
            <div id="code_2fa_error" class="form-error" role="alert" aria-live="polite"></div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-verify-2fa" data-loading-text="Vérification en cours...">
            <span class="btn-text">
                <i class="fas fa-check" aria-hidden="true"></i>
                <?= $isInitialSetup ? 'Terminer la configuration' : 'Vérifier le code' ?>
            </span>
            <span class="btn-loading" aria-hidden="true">
                <i class="fas fa-spinner fa-spin"></i>
                Vérification en cours...
            </span>
        </button>

        <!-- Alternative Options -->
        <div class="alternative-options">
            <button type="button" class="btn btn-link resend-code" <?= !$canResend ? 'disabled' : '' ?>>
                <i class="fas fa-redo-alt" aria-hidden="true"></i>
                <span class="resend-text">Renvoyer le code</span>
                <?php if (!$canResend): ?>
                    <span class="resend-countdown">(disponible dans <span id="resend-timer"><?= $nextResendTime ?></span>s)</span>
                <?php endif; ?>
            </button>
            
            <button type="button" class="btn btn-link use-recovery-code">
                <i class="fas fa-life-ring" aria-hidden="true"></i>
                Utiliser un code de récupération
            </button>
        </div>
    </form>

    <!-- Recovery Code Form (Hidden by default) -->
    <form id="recoveryCodeForm" class="auth-form recovery-form" method="POST" action="/2fa" style="display: none;" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
        <input type="hidden" name="use_recovery_code" value="1">
        
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-life-ring fa-2x" aria-hidden="true"></i>
            </div>
            <h3>Code de récupération</h3>
            <p class="form-description">
                Saisissez l'un de vos codes de récupération sauvegardés.
            </p>
        </div>
        
        <div class="form-group">
            <label for="recovery_code" class="form-label required">
                <i class="fas fa-key" aria-hidden="true"></i>
                Code de récupération
            </label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="recovery_code" 
                    name="recovery_code" 
                    class="form-input recovery-input"
                    required 
                    autocomplete="off"
                    placeholder="XXXX-XXXX-XXXX-XXXX"
                    pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}"
                    aria-describedby="recovery_code_help recovery_code_error"
                >
            </div>
            <div id="recovery_code_help" class="form-help">
                Format: XXXX-XXXX-XXXX-XXXX (chaque code ne peut être utilisé qu'une fois)
            </div>
            <div id="recovery_code_error" class="form-error" role="alert" aria-live="polite"></div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-unlock" aria-hidden="true"></i>
            Utiliser le code de récupération
        </button>
        
        <button type="button" class="btn btn-link back-to-2fa">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Retour à la vérification 2FA
        </button>
    </form>

    <!-- Navigation Links -->
    <div class="form-footer">
        <div class="help-links">
            <a href="/help/2fa" class="help-link" aria-label="Obtenir de l'aide pour l'authentification à deux facteurs">
                <i class="fas fa-question-circle" aria-hidden="true"></i>
                Problème avec votre 2FA ?
            </a>
        </div>
        
        <?php if (!$isInitialSetup): ?>
            <div class="nav-links">
                <a href="/login" class="back-link" aria-label="Retourner à la page de connexion">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    Retour à la connexion
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recovery Codes Modal (for initial setup) -->
<?php if ($isInitialSetup && !empty($recoveryCodes)): ?>
<div id="recovery-codes-modal" class="modal" role="dialog" aria-labelledby="recovery-title" aria-hidden="true">
    <div class="modal-overlay"></div>
    <div class="modal-content recovery-modal">
        <div class="modal-header">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            </div>
            <h3 id="recovery-title">Codes de récupération</h3>
            <p>Sauvegardez ces codes en lieu sûr. Ils vous permettront d'accéder à votre compte si vous perdez votre appareil.</p>
        </div>
        <div class="modal-body">
            <div class="recovery-codes-container">
                <div class="codes-grid">
                    <?php foreach ($recoveryCodes as $index => $code): ?>
                        <div class="recovery-code-item">
                            <span class="code-number"><?= $index + 1 ?>.</span>
                            <code class="recovery-code"><?= e($code) ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="codes-actions">
                    <button type="button" class="btn btn-secondary download-codes">
                        <i class="fas fa-download" aria-hidden="true"></i>
                        Télécharger
                    </button>
                    <button type="button" class="btn btn-secondary print-codes">
                        <i class="fas fa-print" aria-hidden="true"></i>
                        Imprimer
                    </button>
                    <button type="button" class="btn btn-secondary copy-codes">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                        Copier tout
                    </button>
                </div>
                
                <div class="important-notice">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <div class="notice-content">
                        <strong>Important :</strong>
                        <ul>
                            <li>Chaque code ne peut être utilisé qu'une seule fois</li>
                            <li>Gardez-les dans un endroit sûr et accessible</li>
                            <li>Ne les partagez avec personne</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <label class="checkbox-container">
                <input type="checkbox" id="codes-saved-confirm" required>
                <span class="checkmark" aria-hidden="true"></span>
                <span class="checkbox-label">J'ai sauvegardé mes codes de récupération</span>
            </label>
            <button type="button" class="btn btn-primary" id="finish-setup" disabled>
                <i class="fas fa-check" aria-hidden="true"></i>
                Terminer la configuration
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Vérification en cours...</p>
    </div>
</div>

<style>
/* Page-specific styles for 2FA */
.form-icon {
    text-align: center;
    margin-bottom: var(--spacing-lg);
    color: var(--primary-blue);
}

.timer-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-md);
    background: rgba(var(--accent-yellow-rgb, 245, 158, 11), 0.1);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--accent-yellow);
}

.timer-icon {
    color: var(--accent-yellow-dark);
}

.timer-content {
    text-align: center;
}

.timer-label {
    display: block;
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: 2px;
}

.timer-value {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--accent-yellow-dark);
    font-family: monospace;
}

.code-input-container {
    margin-bottom: var(--spacing-md);
}

.code-inputs {
    display: flex;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.code-input {
    width: 60px;
    height: 60px;
    text-align: center;
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    border: 2px solid var(--border-light);
    border-radius: var(--border-radius-lg);
    background: var(--bg-primary);
    transition: all var(--transition-fast);
    font-family: monospace;
}

.code-input:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: scale(1.05);
}

.code-input.filled {
    border-color: var(--primary-green);
    background: rgba(var(--primary-green-rgb, 16, 185, 129), 0.1);
}

.code-input.error {
    border-color: var(--accent-red);
    background: rgba(var(--accent-red-rgb, 239, 68, 68), 0.1);
    animation: shake 0.5s ease-in-out;
}

.alternative-options {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    align-items: center;
    margin-top: var(--spacing-lg);
}

.resend-countdown {
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
}

.recovery-form {
    animation: slideInUp 0.3s ease-out;
}

.recovery-input {
    font-family: monospace;
    font-size: var(--font-size-lg);
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Setup styles */
.setup-container {
    margin-bottom: var(--spacing-xl);
}

.setup-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-xl);
    position: relative;
}

.setup-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    height: 2px;
    background: var(--border-light);
    z-index: 1;
}

.step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius-full);
    background: var(--border-light);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-bold);
    margin: 0 auto var(--spacing-sm);
    transition: all var(--transition-fast);
}

.step.active .step-number {
    background: var(--primary-blue);
    color: var(--text-white);
}

.step.completed .step-number {
    background: var(--primary-green);
    color: var(--text-white);
}

.step-content h3 {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
}

.step-content p {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    line-height: var(--line-height-tight);
}

.qr-code-container {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: var(--bg-secondary);
    border-radius: var(--border-radius-xl);
}

.qr-code {
    margin: var(--spacing-lg) 0;
    padding: var(--spacing-lg);
    background: var(--bg-primary);
    border-radius: var(--border-radius-lg);
    display: inline-block;
}

.qr-code img {
    max-width: 200px;
    height: auto;
}

.qr-instructions {
    margin: var(--spacing-lg) 0;
}

.qr-instructions ol {
    text-align: left;
    display: inline-block;
    padding-left: var(--spacing-lg);
}

.qr-instructions li {
    margin-bottom: var(--spacing-xs);
    color: var(--text-secondary);
}

.manual-entry {
    margin-top: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--bg-primary);
    border-radius: var(--border-radius-md);
}

.manual-code {
    display: block;
    font-family: monospace;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    padding: var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--border-radius-sm);
    margin-top: var(--spacing-sm);
    letter-spacing: 2px;
}

/* Recovery codes modal */
.recovery-modal .modal-content {
    max-width: 600px;
}

.warning-icon {
    text-align: center;
    margin-bottom: var(--spacing-md);
}

.warning-icon i {
    font-size: 3rem;
    color: var(--accent-yellow);
}

.recovery-codes-container {
    padding: var(--spacing-lg);
}

.codes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: var(--bg-secondary);
    border-radius: var(--border-radius-lg);
}

.recovery-code-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--bg-primary);
    border-radius: var(--border-radius-md);
}

.code-number {
    font-weight: var(--font-weight-bold);
    color: var(--text-secondary);
    min-width: 20px;
}

.recovery-code {
    font-family: monospace;
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-sm);
    background: transparent;
    color: var(--text-primary);
}

.codes-actions {
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.important-notice {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: rgba(var(--accent-red-rgb, 239, 68, 68), 0.1);
    border-radius: var(--border-radius-md);
    border-left: 4px solid var(--accent-red);
}

.important-notice i {
    color: var(--accent-red);
    margin-top: 2px;
}

.notice-content {
    flex: 1;
}

.notice-content strong {
    color: var(--accent-red-dark);
    display: block;
    margin-bottom: var(--spacing-xs);
}

.notice-content ul {
    margin: 0;
    padding-left: var(--spacing-lg);
}

.notice-content li {
    margin-bottom: var(--spacing-xs);
    color: var(--text-secondary);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .code-inputs {
        gap: var(--spacing-xs);
    }
    
    .code-input {
        width: 45px;
        height: 45px;
        font-size: var(--font-size-xl);
    }
    
    .setup-steps {
        flex-direction: column;
        gap: var(--spacing-lg);
    }
    
    .setup-steps::before {
        display: none;
    }
    
    .codes-grid {
        grid-template-columns: 1fr;
    }
    
    .codes-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Define page-specific configuration
window.AuthConfig = {
    page: 'verify-2fa',
    enableRealTimeValidation: true,
    enableAnimations: true,
    isInitialSetup: <?= $isInitialSetup ? 'true' : 'false' ?>,
    canResend: <?= $canResend ? 'true' : 'false' ?>,
    nextResendTime: <?= $nextResendTime ?>,
    timerDuration: 300, // 5 minutes
    csrf_token: '<?= e($csrf_token ?? '') ?>'
};

// Auto-focus on first code input when page loads
document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.getElementById('code_2fa_1');
    if (firstInput && !window.matchMedia('(max-width: 768px)').matches) {
        setTimeout(() => firstInput.focus(), 100);
    }
});
</script>