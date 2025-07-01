<?php
/**
 * Email Verification page - Production ready email verification interface
 * 
 * Features:
 * - Token validation and status display
 * - Resend functionality with cooldown timer
 * - Visual status indicators
 * - Animated feedback
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

// Get verification token and status
$verificationToken = $_GET['token'] ?? $token ?? '';
$verificationStatus = $status ?? 'pending'; // pending, verified, expired, invalid
$userEmail = $_SESSION['verification_email'] ?? $email ?? '';

// Get flash messages
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $flashMessages['error'] ?? [];
$success = $flashMessages['success'] ?? [];
$warnings = $flashMessages['warning'] ?? [];

// Clear flash messages after displaying
unset($_SESSION['flash_messages']);

// Check for resend rate limiting
$lastResend = $_SESSION['email_verification_last_resend'] ?? 0;
$resendAttempts = $_SESSION['email_verification_attempts'] ?? 0;
$resendCooldown = 60; // 1 minute cooldown
$maxResendAttempts = 5;
$canResend = $resendAttempts < $maxResendAttempts && (time() - $lastResend) >= $resendCooldown;
$nextResendTime = $canResend ? 0 : $resendCooldown - (time() - $lastResend);
?>

<div class="auth-form-container" id="email-verification-container">
    <!-- Verification Status Header -->
    <div class="verification-header">
        <div class="status-icon <?= $verificationStatus ?>">
            <?php if ($verificationStatus === 'verified'): ?>
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?php elseif ($verificationStatus === 'expired'): ?>
                <i class="fas fa-clock" aria-hidden="true"></i>
            <?php elseif ($verificationStatus === 'invalid'): ?>
                <i class="fas fa-times-circle" aria-hidden="true"></i>
            <?php else: ?>
                <i class="fas fa-envelope" aria-hidden="true"></i>
            <?php endif; ?>
        </div>
        
        <h2 id="verification-title">
            <?php if ($verificationStatus === 'verified'): ?>
                Email vérifié avec succès
            <?php elseif ($verificationStatus === 'expired'): ?>
                Lien de vérification expiré
            <?php elseif ($verificationStatus === 'invalid'): ?>
                Lien de vérification invalide
            <?php else: ?>
                Vérification de votre email
            <?php endif; ?>
        </h2>
        
        <p class="status-description">
            <?php if ($verificationStatus === 'verified'): ?>
                Votre adresse email a été confirmée. Vous pouvez maintenant accéder à toutes les fonctionnalités de votre compte.
            <?php elseif ($verificationStatus === 'expired'): ?>
                Le lien de vérification a expiré. Veuillez demander un nouveau lien pour vérifier votre email.
            <?php elseif ($verificationStatus === 'invalid'): ?>
                Ce lien de vérification n'est pas valide. Veuillez vérifier le lien ou demander un nouveau email de vérification.
            <?php else: ?>
                Nous avons envoyé un email de vérification à votre adresse. Cliquez sur le lien dans l'email pour confirmer votre compte.
            <?php endif; ?>
        </p>
        
        <?php if (!empty($userEmail)): ?>
            <p class="email-display">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <strong><?= e($userEmail) ?></strong>
            </p>
        <?php endif; ?>
    </div>

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

    <!-- Action Content based on status -->
    <?php if ($verificationStatus === 'verified'): ?>
        <!-- Success Actions -->
        <div class="success-actions">
            <div class="success-info">
                <h3>Prochaines étapes</h3>
                <ul class="next-steps">
                    <li>
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        Complétez votre profil
                    </li>
                    <li>
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        Activez l'authentification à deux facteurs (recommandé)
                    </li>
                    <li>
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        Configurez vos préférences de notification
                    </li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    Se connecter maintenant
                </a>
                <a href="/dashboard" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                    Aller au tableau de bord
                </a>
            </div>
        </div>

    <?php elseif (in_array($verificationStatus, ['expired', 'invalid', 'pending'])): ?>
        <!-- Resend Verification Form -->
        <form id="resendVerificationForm" class="auth-form" method="POST" action="/resend-verification" novalidate>
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>" aria-hidden="true">
            
            <?php if ($verificationStatus === 'pending'): ?>
                <div class="pending-info">
                    <h3>Email non reçu ?</h3>
                    <div class="check-list">
                        <div class="check-item">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span>Vérifiez votre dossier spam/indésirables</span>
                        </div>
                        <div class="check-item">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span>L'email peut prendre jusqu'à 5 minutes à arriver</span>
                        </div>
                        <div class="check-item">
                            <i class="fas fa-shield-alt" aria-hidden="true"></i>
                            <span>Vérifiez que votre adresse email est correcte</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Email Field for Resend -->
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
                        value="<?= e($userEmail) ?>"
                        data-validation="email"
                    >
                    <div class="input-status" aria-hidden="true">
                        <i class="fas fa-check input-valid"></i>
                        <i class="fas fa-times input-invalid"></i>
                    </div>
                </div>
                <div id="email_help" class="form-help">
                    Saisissez l'adresse email à vérifier
                </div>
                <div id="email_error" class="form-error" role="alert" aria-live="polite"></div>
            </div>

            <!-- Rate Limiting Notice -->
            <?php if (!$canResend): ?>
                <div class="rate-limit-notice" role="alert">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <div class="rate-limit-content">
                        <strong>Veuillez patienter</strong>
                        <p>Vous pourrez renvoyer un email dans <span id="countdown-timer"><?= $nextResendTime ?></span> secondes.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Resend Button -->
            <button 
                type="submit" 
                class="btn btn-primary btn-resend" 
                data-loading-text="Envoi en cours..."
                <?= !$canResend ? 'disabled' : '' ?>
            >
                <span class="btn-text">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                    <?= $verificationStatus === 'pending' ? 'Renvoyer l\'email' : 'Envoyer un nouveau lien' ?>
                </span>
                <span class="btn-loading" aria-hidden="true">
                    <i class="fas fa-spinner fa-spin"></i>
                    Envoi en cours...
                </span>
            </button>

            <!-- Additional Information -->
            <div class="additional-info">
                <div class="info-item">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>Le lien de vérification sera valide pendant 24 heures</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <span>Votre email ne sera pas partagé avec des tiers</span>
                </div>
            </div>
        </form>
    <?php endif; ?>

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
                Problème persistant ? 
                <a href="/help/email-verification" aria-label="Obtenir de l'aide pour la vérification email">
                    Contactez le support
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Success Animation Container -->
<?php if ($verificationStatus === 'verified'): ?>
<div class="success-animation" id="success-animation">
    <div class="confetti-container">
        <!-- Confetti elements will be generated by JavaScript -->
    </div>
</div>
<?php endif; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Envoi de l'email en cours...</p>
    </div>
</div>

<style>
/* Page-specific styles for email verification */
.verification-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-xl) 0;
}

.status-icon {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
    transition: all var(--transition-normal);
}

.status-icon i {
    font-size: 2.5rem;
}

.status-icon.verified {
    background: rgba(var(--primary-green-rgb, 16, 185, 129), 0.1);
    color: var(--primary-green);
    animation: pulse 2s infinite;
}

.status-icon.expired {
    background: rgba(var(--accent-yellow-rgb, 245, 158, 11), 0.1);
    color: var(--accent-yellow);
}

.status-icon.invalid {
    background: rgba(var(--accent-red-rgb, 239, 68, 68), 0.1);
    color: var(--accent-red);
}

.status-icon.pending {
    background: rgba(var(--primary-blue-rgb, 59, 130, 246), 0.1);
    color: var(--primary-blue);
    animation: bounce 2s infinite;
}

#verification-title {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.status-description {
    font-size: var(--font-size-base);
    color: var(--text-secondary);
    line-height: var(--line-height-relaxed);
    margin-bottom: var(--spacing-lg);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.email-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    background: var(--bg-secondary);
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--border-radius-full);
    margin: var(--spacing-lg) auto 0;
    max-width: fit-content;
}

.success-actions {
    margin-top: var(--spacing-xl);
}

.success-info h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
    text-align: center;
}

.next-steps {
    list-style: none;
    padding: 0;
    margin-bottom: var(--spacing-xl);
}

.next-steps li {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--border-radius-md);
    transition: transform var(--transition-fast);
}

.next-steps li:hover {
    transform: translateX(4px);
}

.next-steps li i {
    color: var(--primary-green);
    font-size: var(--font-size-lg);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    align-items: center;
}

.pending-info {
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: rgba(var(--primary-blue-rgb, 59, 130, 246), 0.05);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--primary-blue);
}

.pending-info h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-md);
    color: var(--primary-blue-dark);
}

.check-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.check-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
}

.check-item i {
    color: var(--primary-blue);
    width: 16px;
    text-align: center;
}

.rate-limit-notice {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
    background: rgba(var(--accent-yellow-rgb, 245, 158, 11), 0.1);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--accent-yellow);
}

.rate-limit-notice i {
    color: var(--accent-yellow-dark);
    font-size: var(--font-size-lg);
}

.rate-limit-content {
    flex: 1;
}

.rate-limit-content strong {
    display: block;
    color: var(--accent-yellow-dark);
    margin-bottom: var(--spacing-xs);
}

.rate-limit-content p {
    color: var(--text-secondary);
    margin: 0;
}

.additional-info {
    margin-top: var(--spacing-lg);
    padding: var(--spacing-md);
    background: rgba(var(--primary-green-rgb, 16, 185, 129), 0.05);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--primary-green);
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

.info-item i {
    color: var(--primary-green);
    width: 16px;
    text-align: center;
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

/* Success Animation */
.success-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1000;
}

.confetti-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 8px;
    height: 8px;
    border-radius: 2px;
    animation: fall 3s linear infinite;
}

/* Animations */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 20px rgba(16, 185, 129, 0);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-8px);
    }
    60% {
        transform: translateY(-4px);
    }
}

@keyframes fall {
    0% {
        opacity: 1;
        transform: translateY(-100vh) rotate(0deg);
    }
    100% {
        opacity: 0;
        transform: translateY(100vh) rotate(360deg);
    }
}

@media (max-width: 768px) {
    .status-icon {
        width: 60px;
        height: 60px;
    }
    
    .status-icon i {
        font-size: 2rem;
    }
    
    #verification-title {
        font-size: var(--font-size-xl);
    }
    
    .next-steps li {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-sm);
    }
    
    .action-buttons {
        width: 100%;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    .check-list {
        font-size: var(--font-size-sm);
    }
}

@media (max-width: 480px) {
    .verification-header {
        padding: var(--spacing-lg) 0;
    }
    
    .status-description {
        font-size: var(--font-size-sm);
    }
    
    .email-display {
        flex-direction: column;
        gap: var(--spacing-xs);
    }
}
</style>

<script>
// Define page-specific configuration
window.AuthConfig = {
    page: 'email-verification',
    enableRealTimeValidation: true,
    enableAnimations: true,
    status: '<?= e($verificationStatus) ?>',
    canResend: <?= $canResend ? 'true' : 'false' ?>,
    nextResendTime: <?= $nextResendTime ?>,
    csrf_token: '<?= e($csrf_token ?? '') ?>'
};

// Success confetti animation
<?php if ($verificationStatus === 'verified'): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Create confetti animation
    function createConfetti() {
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
        const container = document.querySelector('.confetti-container');
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 3 + 's';
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            container.appendChild(confetti);
        }
        
        // Remove animation after 5 seconds
        setTimeout(() => {
            const animation = document.getElementById('success-animation');
            if (animation) {
                animation.style.display = 'none';
            }
        }, 5000);
    }
    
    // Start confetti after a short delay
    setTimeout(createConfetti, 500);
});
<?php endif; ?>

// Rate limiting countdown
<?php if (!$canResend): ?>
document.addEventListener('DOMContentLoaded', function() {
    let remainingTime = <?= $nextResendTime ?>;
    const countdownElement = document.getElementById('countdown-timer');
    const submitButton = document.querySelector('button[type="submit"]');
    
    const countdown = setInterval(() => {
        remainingTime--;
        countdownElement.textContent = remainingTime;
        
        if (remainingTime <= 0) {
            clearInterval(countdown);
            submitButton.disabled = false;
            document.querySelector('.rate-limit-notice').style.display = 'none';
        }
    }, 1000);
});
<?php endif; ?>

// Auto-focus on email input if form is present
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput && !window.matchMedia('(max-width: 768px)').matches) {
        setTimeout(() => emailInput.focus(), 100);
    }
});
</script>