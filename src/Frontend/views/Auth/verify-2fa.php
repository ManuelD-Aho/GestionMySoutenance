<?php
// Ensure CSRF token is available
$csrf_token = $csrf_token ?? '';
$qr_code_url = $qr_code_url ?? '';
$setup_mode = $setup_mode ?? false;
$recovery_codes = $recovery_codes ?? [];
?>

<div class="auth-form">
    <div class="auth-layout-header">
        <div class="auth-layout-logo">
            <i class="fas fa-shield-alt mr-2"></i>
            <?= $setup_mode ? 'Configuration 2FA' : 'Authentification à deux facteurs' ?>
        </div>
        <p class="auth-layout-subtitle">
            <?= $setup_mode ? 'Sécurisez votre compte' : 'Vérification supplémentaire requise' ?>
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

        <?php if ($setup_mode): ?>
            <!-- 2FA Setup Mode -->
            <div id="setupStep1" class="setup-step">
                <h2 class="auth-form-title">
                    <i class="fas fa-mobile-alt mr-2"></i>
                    Étape 1: Scanner le QR Code
                </h2>

                <div class="auth-qr-container">
                    <?php if ($qr_code_url): ?>
                        <div class="auth-qr-code">
                            <img src="<?= htmlspecialchars($qr_code_url, ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="QR Code pour l'authentification à deux facteurs"
                                 class="mx-auto border rounded-lg shadow-md">
                        </div>
                    <?php endif; ?>
                    
                    <div class="auth-qr-instructions">
                        <p class="mb-3">
                            <strong>1.</strong> Téléchargez une application d'authentification :
                        </p>
                        <div class="flex justify-center space-x-4 mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-mobile-alt mr-1"></i>
                                Google Authenticator
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-key mr-1"></i>
                                Authy
                            </span>
                        </div>
                        <p class="mb-3">
                            <strong>2.</strong> Scannez le QR code ci-dessus avec votre application
                        </p>
                        <p>
                            <strong>3.</strong> Entrez le code généré ci-dessous
                        </p>
                    </div>
                </div>

                <button type="button" class="auth-btn auth-btn-primary mt-6" onclick="nextStep()">
                    <i class="fas fa-arrow-right mr-2"></i>
                    Continuer
                </button>
            </div>

            <div id="setupStep2" class="setup-step hidden">
                <h2 class="auth-form-title">
                    <i class="fas fa-key mr-2"></i>
                    Étape 2: Vérifier le code
                </h2>
        <?php endif; ?>

        <!-- 2FA Code Input Form -->
        <div id="codeInputSection" class="<?= $setup_mode ? 'hidden' : '' ?>">
            <?php if (!$setup_mode): ?>
                <h2 class="auth-form-title">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Code d'authentification
                </h2>
                
                <p class="text-center text-gray-600 mb-6">
                    Entrez le code à 6 chiffres généré par votre application d'authentification
                </p>
            <?php endif; ?>

            <form id="twoFactorForm" class="space-y-6" method="POST" action="/2fa" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                
                <!-- 2FA Code Input -->
                <div class="auth-form-group">
                    <label class="auth-form-label text-center block mb-4">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Code d'authentification
                    </label>
                    
                    <div class="auth-2fa-container" id="codeInputs">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="0" autocomplete="off">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="1" autocomplete="off">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="2" autocomplete="off">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="3" autocomplete="off">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="4" autocomplete="off">
                        <input type="text" maxlength="1" class="auth-2fa-input" data-index="5" autocomplete="off">
                    </div>
                    
                    <input type="hidden" name="code_2fa" id="code2fa">
                    <div class="auth-form-error text-center" id="code_error"></div>
                </div>

                <!-- Timer Display -->
                <div class="auth-timer" id="codeTimer">
                    <i class="fas fa-clock mr-2"></i>
                    Code valide pendant <span id="timerCountdown">30</span> secondes
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary" id="verifyButton" disabled>
                    <span class="auth-btn-text">
                        <i class="fas fa-check mr-2"></i>
                        <?= $setup_mode ? 'Activer la 2FA' : 'Vérifier' ?>
                    </span>
                    <span class="auth-btn-loading hidden">
                        <div class="auth-spinner"></div>
                        Vérification en cours...
                    </span>
                </button>

                <?php if (!$setup_mode): ?>
                    <!-- Use Recovery Code -->
                    <div class="text-center">
                        <button type="button" class="auth-link" id="useRecoveryCode">
                            <i class="fas fa-life-ring mr-2"></i>
                            Utiliser un code de récupération
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($setup_mode): ?>
            </div>

            <!-- Recovery Codes Display -->
            <div id="setupStep3" class="setup-step hidden">
                <h2 class="auth-form-title">
                    <i class="fas fa-life-ring mr-2"></i>
                    Codes de récupération
                </h2>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important :</strong> Sauvegardez ces codes de récupération dans un endroit sûr. 
                                Ils vous permettront d'accéder à votre compte si vous perdez votre appareil d'authentification.
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($recovery_codes)): ?>
                    <div class="auth-recovery-codes">
                        <?php foreach ($recovery_codes as $code): ?>
                            <div class="auth-recovery-code">
                                <?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="flex space-x-4 mt-6">
                    <button type="button" class="auth-btn auth-btn-secondary flex-1" onclick="downloadCodes()">
                        <i class="fas fa-download mr-2"></i>
                        Télécharger
                    </button>
                    <button type="button" class="auth-btn auth-btn-primary flex-1" onclick="completeSteup()">
                        <i class="fas fa-check mr-2"></i>
                        Terminer
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recovery Code Input (Hidden by default) -->
        <div id="recoveryCodeSection" class="hidden">
            <h3 class="auth-form-title">
                <i class="fas fa-life-ring mr-2"></i>
                Code de récupération
            </h3>
            
            <form id="recoveryForm" class="space-y-6" method="POST" action="/2fa/recovery">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="auth-form-group">
                    <label for="recovery_code" class="auth-form-label">
                        <i class="fas fa-key mr-2"></i>
                        Code de récupération
                    </label>
                    <input 
                        type="text" 
                        id="recovery_code" 
                        name="recovery_code" 
                        class="auth-form-input"
                        placeholder="Entrez votre code de récupération"
                        autocomplete="off"
                    >
                </div>
                
                <button type="submit" class="auth-btn auth-btn-primary">
                    <i class="fas fa-unlock mr-2"></i>
                    Utiliser le code de récupération
                </button>
                
                <button type="button" class="auth-btn auth-btn-secondary" onclick="showCodeInput()">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour au code d'authentification
                </button>
            </form>
        </div>

        <!-- Back to Login (only in non-setup mode) -->
        <?php if (!$setup_mode): ?>
            <div class="mt-6 text-center">
                <a href="/login" class="auth-link auth-link-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour à la connexion
                </a>
            </div>
        <?php endif; ?>
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

.setup-step {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.auth-2fa-input.filled {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

.auth-timer.expired {
    color: #ef4444;
    font-weight: 600;
}

.auth-timer.expired .fa-clock {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInputs = document.querySelectorAll('.auth-2fa-input');
    const code2faHidden = document.getElementById('code2fa');
    const verifyButton = document.getElementById('verifyButton');
    const timerCountdown = document.getElementById('timerCountdown');
    const codeTimer = document.getElementById('codeTimer');
    const form = document.getElementById('twoFactorForm');
    const flashMessage = document.getElementById('flashMessage');
    
    let timeLeft = 30;
    let timerInterval;
    
    // Auto-hide flash messages
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Setup 2FA input handling
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Only allow numbers
            if (!/^\d$/.test(value) && value !== '') {
                e.target.value = '';
                return;
            }
            
            if (value) {
                input.classList.add('filled');
                // Move to next input
                if (index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
            } else {
                input.classList.remove('filled');
            }
            
            updateHiddenInput();
            checkFormValidity();
        });
        
        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && !input.value && index > 0) {
                codeInputs[index - 1].focus();
                codeInputs[index - 1].value = '';
                codeInputs[index - 1].classList.remove('filled');
                updateHiddenInput();
                checkFormValidity();
            }
            
            // Handle paste
            if (e.key === 'Enter') {
                e.preventDefault();
                if (verifyButton && !verifyButton.disabled) {
                    form.submit();
                }
            }
        });
        
        // Handle paste operation
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            
            digits.split('').forEach((digit, i) => {
                if (codeInputs[i]) {
                    codeInputs[i].value = digit;
                    codeInputs[i].classList.add('filled');
                }
            });
            
            if (digits.length > 0) {
                const lastFilledIndex = Math.min(digits.length - 1, 5);
                codeInputs[lastFilledIndex].focus();
            }
            
            updateHiddenInput();
            checkFormValidity();
        });
    });
    
    function updateHiddenInput() {
        const code = Array.from(codeInputs).map(input => input.value).join('');
        code2faHidden.value = code;
    }
    
    function checkFormValidity() {
        const code = code2faHidden.value;
        const isValid = code.length === 6 && /^\d{6}$/.test(code);
        
        if (verifyButton) {
            verifyButton.disabled = !isValid;
            
            if (isValid) {
                verifyButton.classList.add('auth-animate-bounce');
                setTimeout(() => {
                    verifyButton.classList.remove('auth-animate-bounce');
                }, 600);
            }
        }
    }
    
    // Start countdown timer
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            timerCountdown.textContent = timeLeft;
            
            if (timeLeft <= 10) {
                codeTimer.classList.add('expired');
            }
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                codeTimer.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Code expiré - Veuillez vous reconnecter';
                // Optionally disable form or redirect
            }
        }, 1000);
    }
    
    // Auto-focus first input
    if (codeInputs.length > 0) {
        codeInputs[0].focus();
        startTimer();
    }
    
    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (verifyButton.disabled) {
                return;
            }
            
            // Show loading state
            verifyButton.classList.add('loading');
            verifyButton.disabled = true;
            
            // Submit form
            setTimeout(() => {
                form.submit();
            }, 500);
        });
    }
    
    // Recovery code toggle
    const useRecoveryCodeBtn = document.getElementById('useRecoveryCode');
    if (useRecoveryCodeBtn) {
        useRecoveryCodeBtn.addEventListener('click', function() {
            document.getElementById('codeInputSection').classList.add('hidden');
            document.getElementById('recoveryCodeSection').classList.remove('hidden');
        });
    }
});

// Setup mode functions
function nextStep() {
    document.getElementById('setupStep1').classList.add('hidden');
    document.getElementById('setupStep2').classList.remove('hidden');
    document.getElementById('codeInputSection').classList.remove('hidden');
}

function showRecoveryCodes() {
    document.getElementById('setupStep2').classList.add('hidden');
    document.getElementById('setupStep3').classList.remove('hidden');
}

function downloadCodes() {
    // Implement download functionality for recovery codes
    const codes = Array.from(document.querySelectorAll('.auth-recovery-code')).map(el => el.textContent);
    const content = 'Codes de récupération GestionMySoutenance\n\n' + codes.join('\n');
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'recovery-codes-gestionmysoutenance.txt';
    a.click();
    window.URL.revokeObjectURL(url);
}

function completeSteup() {
    window.location.href = '/dashboard';
}

function showCodeInput() {
    document.getElementById('recoveryCodeSection').classList.add('hidden');
    document.getElementById('codeInputSection').classList.remove('hidden');
}
</script>