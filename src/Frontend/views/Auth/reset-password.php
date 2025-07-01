<?php
// Ensure CSRF token and reset token are available
$csrf_token = $csrf_token ?? '';
$token = $token ?? $_GET['token'] ?? '';
?>

<div class="auth-form">
    <div class="auth-layout-header">
        <div class="auth-layout-logo">
            <i class="fas fa-shield-alt mr-2"></i>
            Nouveau mot de passe
        </div>
        <p class="auth-layout-subtitle">Choisissez un mot de passe sécurisé</p>
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
            <i class="fas fa-key mr-2"></i>
            Réinitialiser le mot de passe
        </h2>

        <form id="resetPasswordForm" class="space-y-6" method="POST" action="/reset-password" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            
            <!-- New Password Field -->
            <div class="auth-form-group">
                <label for="new_password" class="auth-form-label">
                    <i class="fas fa-lock mr-2"></i>
                    Nouveau mot de passe
                </label>
                <div class="auth-password-container">
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="auth-form-input pr-12"
                        placeholder="Entrez votre nouveau mot de passe"
                        required
                        autocomplete="new-password"
                        data-validation="required|min:8|complexity"
                    >
                    <button type="button" class="auth-password-toggle" id="newPasswordToggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="auth-form-error" id="new_password_error"></div>
                
                <!-- Password Strength Indicator -->
                <div class="auth-password-strength" id="passwordStrength">
                    <div class="auth-strength-bar">
                        <div class="auth-strength-fill" id="strengthFill"></div>
                    </div>
                    <p class="auth-strength-text" id="strengthText">Entrez un mot de passe</p>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="auth-form-group">
                <label for="confirm_password" class="auth-form-label">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmer le mot de passe
                </label>
                <div class="auth-password-container">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="auth-form-input pr-12"
                        placeholder="Confirmez votre nouveau mot de passe"
                        required
                        autocomplete="new-password"
                        data-validation="required|match:new_password"
                    >
                    <button type="button" class="auth-password-toggle" id="confirmPasswordToggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="auth-form-error" id="confirm_password_error"></div>
            </div>

            <!-- Password Requirements -->
            <div class="bg-gray-50 p-4 rounded-lg border">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Le mot de passe doit contenir :
                </h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li id="req-length" class="password-requirement">
                        <i class="fas fa-times text-red-500 mr-2"></i>
                        Au moins 8 caractères
                    </li>
                    <li id="req-lowercase" class="password-requirement">
                        <i class="fas fa-times text-red-500 mr-2"></i>
                        Une lettre minuscule
                    </li>
                    <li id="req-uppercase" class="password-requirement">
                        <i class="fas fa-times text-red-500 mr-2"></i>
                        Une lettre majuscule
                    </li>
                    <li id="req-number" class="password-requirement">
                        <i class="fas fa-times text-red-500 mr-2"></i>
                        Un chiffre
                    </li>
                    <li id="req-special" class="password-requirement">
                        <i class="fas fa-times text-red-500 mr-2"></i>
                        Un caractère spécial (@$!%*?&)
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" id="resetButton" disabled>
                <span class="auth-btn-text">
                    <i class="fas fa-save mr-2"></i>
                    Réinitialiser le mot de passe
                </span>
                <span class="auth-btn-loading hidden">
                    <div class="auth-spinner"></div>
                    Réinitialisation en cours...
                </span>
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="/login" class="auth-link auth-link-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la connexion
            </a>
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

.auth-password-container input {
    padding-right: 3rem;
}

.password-requirement.valid i {
    color: #10b981;
}

.password-requirement.valid i:before {
    content: "\f00c";
}

.hidden {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const newPasswordToggle = document.getElementById('newPasswordToggle');
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
    const resetButton = document.getElementById('resetButton');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const form = document.getElementById('resetPasswordForm');
    const flashMessage = document.getElementById('flashMessage');
    
    // Auto-hide flash messages
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Password toggles
    function setupPasswordToggle(toggle, input) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    setupPasswordToggle(newPasswordToggle, newPasswordInput);
    setupPasswordToggle(confirmPasswordToggle, confirmPasswordInput);
    
    // Password strength checking
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        checkPasswordStrength(password);
        updatePasswordRequirements(password);
        validateForm();
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        validatePasswordMatch();
        validateForm();
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        
        // Length check
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        
        // Character variety checks
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[@$!%*?&]/.test(password)) score++;
        
        const strength = ['weak', 'weak', 'fair', 'good', 'good', 'strong'][Math.min(score, 5)];
        const strengthLabels = {
            weak: 'Faible',
            fair: 'Moyen',
            good: 'Bon',
            strong: 'Fort'
        };
        
        strengthFill.className = `auth-strength-fill ${strength}`;
        strengthText.className = `auth-strength-text ${strength}`;
        strengthText.textContent = strengthLabels[strength] || 'Très faible';
    }
    
    function updatePasswordRequirements(password) {
        const requirements = [
            { id: 'req-length', test: password.length >= 8 },
            { id: 'req-lowercase', test: /[a-z]/.test(password) },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-number', test: /[0-9]/.test(password) },
            { id: 'req-special', test: /[@$!%*?&]/.test(password) }
        ];
        
        requirements.forEach(req => {
            const element = document.getElementById(req.id);
            if (req.test) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        });
    }
    
    function validatePasswordMatch() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const errorElement = document.getElementById('confirm_password_error');
        
        if (confirmPassword && password !== confirmPassword) {
            errorElement.textContent = 'Les mots de passe ne correspondent pas';
            errorElement.classList.add('show');
            confirmPasswordInput.classList.add('error');
            return false;
        } else {
            errorElement.classList.remove('show');
            confirmPasswordInput.classList.remove('error');
            return true;
        }
    }
    
    function validateForm() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        // Check all requirements
        const hasLength = password.length >= 8;
        const hasLowercase = /[a-z]/.test(password);
        const hasUppercase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[@$!%*?&]/.test(password);
        const passwordsMatch = password === confirmPassword && confirmPassword.length > 0;
        
        const isValid = hasLength && hasLowercase && hasUppercase && hasNumber && hasSpecial && passwordsMatch;
        
        resetButton.disabled = !isValid;
        
        if (isValid) {
            resetButton.classList.add('auth-animate-pulse');
        } else {
            resetButton.classList.remove('auth-animate-pulse');
        }
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (resetButton.disabled) {
            return;
        }
        
        // Show loading state
        resetButton.classList.add('loading');
        resetButton.disabled = true;
        
        // Submit form data (replace with actual submission)
        const formData = new FormData(form);
        
        // Simulate form submission
        setTimeout(() => {
            // This would be replaced with actual AJAX submission
            form.submit();
        }, 1000);
    });
});
</script>