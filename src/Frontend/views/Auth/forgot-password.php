<?php
// Ensure CSRF token is available
$csrf_token = $csrf_token ?? '';
?>

<div class="auth-form">
    <div class="auth-layout-header">
        <div class="auth-layout-logo">
            <i class="fas fa-key mr-2"></i>
            Récupération de compte
        </div>
        <p class="auth-layout-subtitle">Réinitialisation de mot de passe</p>
    </div>

    <div class="auth-form-content p-8">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="auth-message <?= htmlspecialchars($_SESSION['flash_message']['type']) ?>" id="flashMessage">
                <i class="fas fa-<?= $_SESSION['flash_message']['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> mr-2"></i>
                <?= htmlspecialchars($_SESSION['flash_message']['message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <h2 class="auth-form-title">
            <i class="fas fa-envelope mr-2"></i>
            Mot de passe oublié
        </h2>

        <p class="text-sm text-gray-600 text-center mb-6">
            Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        <form id="forgotPasswordForm" class="space-y-6" method="POST" action="/forgot-password" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            
            <!-- Email Field -->
            <div class="auth-form-group">
                <label for="email" class="auth-form-label">
                    <i class="fas fa-envelope mr-2"></i>
                    Adresse email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="auth-form-input"
                    placeholder="votre.email@exemple.com"
                    required
                    autocomplete="email"
                    data-validation="required|email"
                >
                <div class="auth-form-error" id="email_error"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" id="forgotPasswordButton">
                <span class="auth-btn-text">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Envoyer le lien de réinitialisation
                </span>
                <span class="auth-btn-loading hidden">
                    <div class="auth-spinner"></div>
                    Envoi en cours...
                </span>
            </button>

            <!-- Resend Timer -->
            <div id="resendTimer" class="auth-timer hidden">
                <i class="fas fa-clock mr-2"></i>
                Vous pourrez renvoyer un email dans <span id="timerCountdown">60</span> secondes
            </div>

            <!-- Resend Button -->
            <button type="button" class="auth-btn auth-btn-secondary hidden" id="resendButton">
                <i class="fas fa-redo mr-2"></i>
                Renvoyer l'email
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <div class="relative mb-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Ou</span>
                </div>
            </div>
            
            <a href="/login" class="auth-link auth-link-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la connexion
            </a>
        </div>

        <!-- Help Section -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
            <h4 class="text-sm font-medium text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Besoin d'aide ?
            </h4>
            <p class="text-sm text-blue-700">
                Si vous ne recevez pas l'email, vérifiez votre dossier spam ou contactez l'administrateur système.
            </p>
        </div>
    </div>
</div>

<style>
.auth-btn-loading {
    display: none;
}

.auth-btn.loading .auth-btn-text {
    display: none;
}

.auth-btn.loading .auth-btn-loading {
    display: inline-flex;
    align-items: center;
}

.hidden {
    display: none;
}

.auth-timer.expired {
    color: #ef4444;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let resendTimerActive = false;
    let timeLeft = 60;
    
    const form = document.getElementById('forgotPasswordForm');
    const button = document.getElementById('forgotPasswordButton');
    const resendTimer = document.getElementById('resendTimer');
    const resendButton = document.getElementById('resendButton');
    const timerCountdown = document.getElementById('timerCountdown');
    const flashMessage = document.getElementById('flashMessage');
    
    // Auto-hide flash messages
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (resendTimerActive) {
            return;
        }
        
        // Show loading state
        button.classList.add('loading');
        button.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            // Reset button state
            button.classList.remove('loading');
            button.disabled = false;
            
            // Start resend timer
            startResendTimer();
            
            // Show success message (replace with actual response handling)
            showMessage('Un email de réinitialisation a été envoyé !', 'success');
        }, 2000);
    });
    
    function startResendTimer() {
        resendTimerActive = true;
        timeLeft = 60;
        
        // Hide submit button and show timer
        button.classList.add('hidden');
        resendTimer.classList.remove('hidden');
        
        const interval = setInterval(() => {
            timeLeft--;
            timerCountdown.textContent = timeLeft;
            
            if (timeLeft <= 10) {
                resendTimer.classList.add('expired');
            }
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                
                // Hide timer and show resend button
                resendTimer.classList.add('hidden');
                resendButton.classList.remove('hidden');
                
                resendTimerActive = false;
            }
        }, 1000);
    }
    
    // Handle resend button
    resendButton.addEventListener('click', function() {
        // Hide resend button and show submit button
        resendButton.classList.add('hidden');
        button.classList.remove('hidden');
        resendTimer.classList.remove('expired');
    });
    
    function showMessage(message, type) {
        // Create and show message element
        const messageEl = document.createElement('div');
        messageEl.className = `auth-message ${type}`;
        messageEl.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
            ${message}
        `;
        
        // Insert at top of form content
        const formContent = document.querySelector('.auth-form-content');
        formContent.insertBefore(messageEl, formContent.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageEl.style.opacity = '0';
            setTimeout(() => messageEl.remove(), 300);
        }, 5000);
    }
});
</script>