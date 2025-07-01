<?php
// Ensure CSRF token is available
$csrf_token = $csrf_token ?? '';
?>

<div class="auth-form">
    <div class="auth-layout-header">
        <div class="auth-layout-logo">
            <i class="fas fa-graduation-cap mr-2"></i>
            GestionMySoutenance
        </div>
        <p class="auth-layout-subtitle">Plateforme de gestion des soutenances</p>
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
            <i class="fas fa-sign-in-alt mr-2"></i>
            Connexion
        </h2>

        <form id="loginForm" class="space-y-6" method="POST" action="/login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            
            <!-- Email/Username Field -->
            <div class="auth-form-group">
                <label for="login_email" class="auth-form-label">
                    <i class="fas fa-user mr-2"></i>
                    Login ou Email
                </label>
                <input 
                    type="text" 
                    id="login_email" 
                    name="login_email" 
                    class="auth-form-input"
                    placeholder="Entrez votre login ou email"
                    required
                    autocomplete="username"
                    data-validation="required|email_or_username"
                >
                <div class="auth-form-error" id="login_email_error"></div>
            </div>

            <!-- Password Field -->
            <div class="auth-form-group">
                <label for="password" class="auth-form-label">
                    <i class="fas fa-lock mr-2"></i>
                    Mot de passe
                </label>
                <div class="auth-password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="auth-form-input pr-12"
                        placeholder="Entrez votre mot de passe"
                        required
                        autocomplete="current-password"
                        data-validation="required|min:6"
                    >
                    <button type="button" class="auth-password-toggle" id="passwordToggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="auth-form-error" id="password_error"></div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="auth-checkbox-container">
                    <input 
                        type="checkbox" 
                        id="remember_me" 
                        name="remember_me" 
                        class="auth-checkbox"
                    >
                    <label for="remember_me" class="auth-checkbox-label">
                        Se souvenir de moi
                    </label>
                </div>
                <a href="/forgot-password" class="auth-link">
                    <i class="fas fa-question-circle mr-1"></i>
                    Mot de passe oublié ?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" id="loginButton">
                <span class="auth-btn-text">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Se connecter
                </span>
                <span class="auth-btn-loading hidden">
                    <div class="auth-spinner"></div>
                    Connexion en cours...
                </span>
            </button>
        </form>

        <!-- Additional Links -->
        <div class="mt-6 text-center space-y-3">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Ou</span>
                </div>
            </div>
            
            <p class="text-sm text-gray-600">
                Pas encore de compte ? 
                <a href="/register" class="auth-link">
                    <i class="fas fa-user-plus mr-1"></i>
                    Créer un compte
                </a>
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

.auth-password-container input {
    padding-right: 3rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize password toggle
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Auto-hide flash messages
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
});
</script>