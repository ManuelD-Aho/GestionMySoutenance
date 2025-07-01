<?php
// Ensure CSRF token and verification status are available
$csrf_token = $csrf_token ?? '';
$verification_status = $verification_status ?? 'pending'; // pending, success, error, expired
$user_email = $user_email ?? '';
$resend_available = $resend_available ?? true;
?>

<div class="auth-form">
    <div class="auth-layout-header">
        <div class="auth-layout-logo">
            <?php if ($verification_status === 'success'): ?>
                <i class="fas fa-check-circle mr-2 text-green-400"></i>
                Email vérifié !
            <?php elseif ($verification_status === 'error' || $verification_status === 'expired'): ?>
                <i class="fas fa-exclamation-triangle mr-2 text-red-400"></i>
                Vérification échouée
            <?php else: ?>
                <i class="fas fa-envelope-open mr-2"></i>
                Vérification email
            <?php endif; ?>
        </div>
        <p class="auth-layout-subtitle">
            <?php if ($verification_status === 'success'): ?>
                Votre compte est maintenant actif
            <?php elseif ($verification_status === 'error'): ?>
                Lien de vérification invalide
            <?php elseif ($verification_status === 'expired'): ?>
                Lien de vérification expiré
            <?php else: ?>
                Confirmez votre adresse email
            <?php endif; ?>
        </p>
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

        <?php if ($verification_status === 'success'): ?>
            <!-- Success State -->
            <div class="text-center space-y-6">
                <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                
                <h2 class="auth-form-title text-green-700">
                    Email vérifié avec succès !
                </h2>
                
                <p class="text-gray-600">
                    Votre adresse email <strong><?= htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') ?></strong> 
                    a été vérifiée avec succès. Votre compte est maintenant actif.
                </p>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-green-600 mr-3"></i>
                        <div class="text-sm text-green-700">
                            <p class="font-medium">Compte activé</p>
                            <p>Vous pouvez maintenant vous connecter et utiliser toutes les fonctionnalités de la plateforme.</p>
                        </div>
                    </div>
                </div>
                
                <a href="/login" class="auth-btn auth-btn-primary inline-flex">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Se connecter maintenant
                </a>
            </div>

        <?php elseif ($verification_status === 'error'): ?>
            <!-- Error State -->
            <div class="text-center space-y-6">
                <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600 text-3xl"></i>
                </div>
                
                <h2 class="auth-form-title text-red-700">
                    Lien de vérification invalide
                </h2>
                
                <p class="text-gray-600">
                    Le lien de vérification que vous avez utilisé n'est pas valide ou a déjà été utilisé.
                </p>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                        <div class="text-sm text-red-700">
                            <p class="font-medium">Possible causes :</p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Le lien a déjà été utilisé</li>
                                <li>Le lien a été corrompu</li>
                                <li>Le lien provient d'un ancien email</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <?php if ($resend_available): ?>
                    <form id="resendForm" method="POST" action="/resend-verification" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="auth-btn auth-btn-secondary" id="resendButton">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Renvoyer l'email de vérification
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        <?php elseif ($verification_status === 'expired'): ?>
            <!-- Expired State -->
            <div class="text-center space-y-6">
                <div class="mx-auto w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-3xl"></i>
                </div>
                
                <h2 class="auth-form-title text-orange-700">
                    Lien de vérification expiré
                </h2>
                
                <p class="text-gray-600">
                    Le lien de vérification a expiré. Pour des raisons de sécurité, 
                    les liens de vérification ne sont valides que pendant 24 heures.
                </p>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-orange-600 mr-3"></i>
                        <div class="text-sm text-orange-700">
                            <p class="font-medium">Que faire maintenant ?</p>
                            <p>Demandez un nouveau lien de vérification en cliquant sur le bouton ci-dessous.</p>
                        </div>
                    </div>
                </div>
                
                <?php if ($resend_available): ?>
                    <form id="resendForm" method="POST" action="/resend-verification" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="auth-btn auth-btn-primary" id="resendButton">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer un nouveau lien
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Pending State -->
            <div class="text-center space-y-6">
                <div class="mx-auto w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-600 text-3xl auth-animate-pulse"></i>
                </div>
                
                <h2 class="auth-form-title">
                    Vérifiez votre email
                </h2>
                
                <p class="text-gray-600">
                    Nous avons envoyé un email de vérification à <strong><?= htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') ?></strong>
                </p>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-2">
                            <i class="fas fa-info-circle mr-2"></i>
                            Instructions :
                        </p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Consultez votre boîte de réception</li>
                            <li>Cliquez sur le lien de vérification dans l'email</li>
                            <li>Revenez ici pour vous connecter</li>
                        </ol>
                    </div>
                </div>
                
                <!-- Resend Timer -->
                <div id="resendSection">
                    <div id="resendTimer" class="auth-timer">
                        <i class="fas fa-clock mr-2"></i>
                        Vous pourrez renvoyer l'email dans <span id="timerCountdown">60</span> secondes
                    </div>
                    
                    <form id="resendForm" method="POST" action="/resend-verification" class="hidden">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="auth-btn auth-btn-secondary" id="resendButton">
                            <span class="auth-btn-text">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Renvoyer l'email
                            </span>
                            <span class="auth-btn-loading hidden">
                                <div class="auth-spinner"></div>
                                Envoi en cours...
                            </span>
                        </button>
                    </form>
                </div>
                
                <!-- Email not received help -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-800 mb-2">
                        <i class="fas fa-question-circle mr-2"></i>
                        Vous ne recevez pas l'email ?
                    </h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>• Vérifiez votre dossier spam/courrier indésirable</p>
                        <p>• Assurez-vous que l'adresse email est correcte</p>
                        <p>• Attendez quelques minutes, la livraison peut être retardée</p>
                        <p>• Contactez le support si le problème persiste</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Common Actions -->
        <div class="mt-8 text-center space-y-3">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Ou</span>
                </div>
            </div>
            
            <div class="flex flex-col space-y-2">
                <a href="/login" class="auth-link auth-link-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Retour à la connexion
                </a>
                
                <?php if ($verification_status !== 'success'): ?>
                    <a href="/support" class="auth-link auth-link-center">
                        <i class="fas fa-life-ring mr-2"></i>
                        Contacter le support
                    </a>
                <?php endif; ?>
            </div>
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

@keyframes emailPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.auth-animate-pulse {
    animation: emailPulse 2s ease-in-out infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flashMessage');
    const resendTimer = document.getElementById('resendTimer');
    const resendForm = document.getElementById('resendForm');
    const resendButton = document.getElementById('resendButton');
    const timerCountdown = document.getElementById('timerCountdown');
    
    let timeLeft = 60;
    let timerActive = false;
    
    // Auto-hide flash messages
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Start resend timer if we're in pending state
    if (resendTimer && !resendTimer.classList.contains('hidden')) {
        startResendTimer();
    }
    
    function startResendTimer() {
        timerActive = true;
        timeLeft = 60;
        
        const interval = setInterval(() => {
            timeLeft--;
            if (timerCountdown) {
                timerCountdown.textContent = timeLeft;
            }
            
            if (timeLeft <= 10 && resendTimer) {
                resendTimer.classList.add('expired');
            }
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                
                // Hide timer and show resend form
                if (resendTimer) {
                    resendTimer.classList.add('hidden');
                }
                if (resendForm) {
                    resendForm.classList.remove('hidden');
                }
                
                timerActive = false;
            }
        }, 1000);
    }
    
    // Handle resend form submission
    if (resendForm) {
        resendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (timerActive) {
                return;
            }
            
            // Show loading state
            if (resendButton) {
                resendButton.classList.add('loading');
                resendButton.disabled = true;
            }
            
            // Submit form data
            const formData = new FormData(resendForm);
            
            // Simulate AJAX submission (replace with actual implementation)
            fetch(resendForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                if (resendButton) {
                    resendButton.classList.remove('loading');
                    resendButton.disabled = false;
                }
                
                if (data.success) {
                    // Show success message
                    showMessage('Email de vérification renvoyé avec succès !', 'success');
                    
                    // Hide form and restart timer
                    resendForm.classList.add('hidden');
                    if (resendTimer) {
                        resendTimer.classList.remove('hidden', 'expired');
                        startResendTimer();
                    }
                } else {
                    // Show error message
                    showMessage(data.message || 'Erreur lors de l\'envoi de l\'email', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button state
                if (resendButton) {
                    resendButton.classList.remove('loading');
                    resendButton.disabled = false;
                }
                
                showMessage('Erreur de connexion. Veuillez réessayer.', 'error');
            });
        });
    }
    
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
    
    // Auto-refresh page for email verification (only in pending state)
    <?php if ($verification_status === 'pending'): ?>
    // Check verification status every 30 seconds
    setInterval(() => {
        fetch('/check-verification-status', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified) {
                // Reload page to show success state
                window.location.reload();
            }
        })
        .catch(error => {
            console.log('Verification check failed:', error);
        });
    }, 30000);
    <?php endif; ?>
});
</script>