<?php
/**
 * Page de connexion moderne - GestionMySoutenance
 * Interface responsive avec validation temps réel et animations GSAP
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Connexion';
$pageSubtitle = 'Accédez à votre espace sécurisé';
$showLogo = true;

// Données du formulaire (depuis le contrôleur)
$csrf_token = $csrf_token ?? '';
$login_email = $login_email ?? '';
$remember_me = $remember_me ?? false;
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';

// Messages flash depuis la session
if (isset($_SESSION['flash_message'])) {
    if ($_SESSION['flash_type'] === 'error') {
        $error_message = $_SESSION['flash_message'];
    } else {
        $success_message = $_SESSION['flash_message'];
    }
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion sécurisée à GestionMySoutenance - Plateforme de gestion des soutenances académiques UFHB">
    <meta name="keywords" content="connexion, authentification, UFHB, soutenance, étudiant, enseignant">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> - GestionMySoutenance</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DaisyUI & Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.0/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    
    <!-- CSS personnalisés -->
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    
    <!-- GSAP pour les animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <style>
        /* Styles spécifiques à la page de connexion */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 2;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .background-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-full);
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 60px;
            height: 60px;
            top: 70%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .shape-3 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 15%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .login-form .form-control {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }
        
        .login-form .form-control input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.9);
            padding-left: 50px;
        }
        
        .login-form .form-control input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
            transform: translateY(-2px);
        }
        
        .form-control .input-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            transition: color var(--transition-fast);
            z-index: 2;
        }
        
        .form-control input:focus + .input-icon {
            color: var(--primary-blue);
        }
        
        .password-toggle {
            position: absolute;
            right: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: var(--spacing-xs);
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-fast);
        }
        
        .password-toggle:hover {
            color: var(--primary-blue);
            background: rgba(59, 130, 246, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: var(--spacing-md);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-green) 100%);
            color: var(--text-white);
            border: none;
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }
        
        .login-btn:active {
            transform: translateY(-1px);
        }
        
        .login-btn.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }
        
        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
        }
        
        .login-btn.loading .loading-spinner {
            display: block;
        }
        
        .login-btn.loading .btn-text {
            opacity: 0;
        }
        
        .spinner {
            width: 24px;
            height: 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid var(--text-white);
            border-radius: var(--border-radius-full);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .form-footer-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: var(--spacing-lg);
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }
        
        .form-footer-links a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            font-size: var(--font-size-sm);
            transition: all var(--transition-fast);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }
        
        .form-footer-links a:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        
        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            border: 1px solid var(--accent-red);
            color: var(--accent-red-dark);
            padding: var(--spacing-md);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            animation: shake 0.5s ease-in-out;
        }
        
        .success-message {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 1px solid var(--primary-green);
            color: var(--primary-green-dark);
            padding: var(--spacing-md);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            animation: bounce 0.6s ease-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: var(--spacing-md);
            }
            
            .login-card {
                padding: var(--spacing-lg);
                max-width: 100%;
            }
            
            .form-footer-links {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: var(--spacing-md);
            }
            
            .floating-shape {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Éléments de fond animés -->
        <div class="background-elements">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
        </div>
        
        <!-- Carte de connexion -->
        <div class="login-card" id="loginCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <!-- Messages d'alerte -->
            <?php if ($error_message): ?>
            <div class="error-message" role="alert">
                <span class="material-icons" aria-hidden="true">error</span>
                <?= e($error_message) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="success-message" role="alert">
                <span class="material-icons" aria-hidden="true">check_circle</span>
                <?= e($success_message) ?>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire de connexion -->
            <form class="login-form" id="loginForm" method="POST" action="/login" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                
                <!-- Champ Email/Login -->
                <div class="form-control">
                    <input type="text" 
                           id="login_email" 
                           name="login_email" 
                           value="<?= e($login_email) ?>"
                           placeholder="Email ou nom d'utilisateur"
                           required
                           autocomplete="username"
                           aria-describedby="login-help"
                           class="input input-bordered">
                    <span class="input-icon material-icons" aria-hidden="true">person</span>
                    <div id="login-help" class="sr-only">Saisissez votre email ou nom d'utilisateur</div>
                </div>
                
                <!-- Champ Mot de passe -->
                <div class="form-control">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Mot de passe"
                           required
                           autocomplete="current-password"
                           aria-describedby="password-help"
                           class="input input-bordered">
                    <span class="input-icon material-icons" aria-hidden="true">lock</span>
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword('password')"
                            aria-label="Afficher/masquer le mot de passe"
                            title="Afficher/masquer le mot de passe">
                        <span class="material-icons">visibility</span>
                    </button>
                    <div id="password-help" class="sr-only">Saisissez votre mot de passe</div>
                </div>
                
                <!-- Options du formulaire -->
                <div class="form-options">
                    <label class="checkbox-container cursor-pointer label">
                        <input type="checkbox" 
                               name="remember_me" 
                               id="remember_me"
                               <?= $remember_me ? 'checked' : '' ?>
                               class="checkbox checkbox-primary">
                        <span class="label-text ml-2">Se souvenir de moi</span>
                    </label>
                </div>
                
                <!-- Bouton de connexion -->
                <button type="submit" class="login-btn btn btn-primary" id="loginBtn">
                    <span class="btn-text">Se connecter</span>
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                </button>
                
                <!-- Liens du pied de page -->
                <div class="form-footer-links">
                    <a href="/forgot-password" class="link link-primary">
                        <span class="material-icons" style="font-size: 16px;" aria-hidden="true">help_outline</span>
                        Mot de passe oublié ?
                    </a>
                    <a href="/" class="link link-secondary">
                        <span class="material-icons" style="font-size: 16px;" aria-hidden="true">home</span>
                        Retour à l'accueil
                    </a>
                </div>
            </form>
            
            <!-- Pied de page -->
            <?php $showSupport = false; include_once __DIR__ . '/components/auth-footer.php'; ?>
        </div>
    </div>
    
    <!-- Inclusion du composant alertes -->
    <?php include_once __DIR__ . '/components/auth-alerts.php'; ?>
    
    <!-- Scripts JavaScript -->
    <script src="/assets/js/auth-validation.js"></script>
    <script src="/assets/js/auth-animations.js"></script>
    <script src="/assets/js/auth.js"></script>
    
    <script>
        // Initialisation spécifique à la page de connexion
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            gsap.timeline()
                .to('.login-card', {
                    duration: 0.8,
                    y: 0,
                    opacity: 1,
                    ease: 'power3.out'
                })
                .from('.auth-header', {
                    duration: 0.6,
                    y: -30,
                    opacity: 0,
                    ease: 'power2.out'
                }, '-=0.4')
                .from('.form-control', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.3')
                .from('.login-btn', {
                    duration: 0.4,
                    scale: 0.95,
                    opacity: 0,
                    ease: 'back.out(1.7)'
                }, '-=0.2');
            
            // Animation des formes flottantes
            gsap.to('.floating-shape', {
                duration: 6,
                y: '-20px',
                rotation: 180,
                ease: 'power1.inOut',
                repeat: -1,
                yoyo: true,
                stagger: {
                    each: 2,
                    repeat: -1
                }
            });
            
            // Initialisation de la validation
            if (typeof initLoginValidation === 'function') {
                initLoginValidation();
            }
            
            // Focus automatique sur le premier champ
            const firstInput = document.getElementById('login_email');
            if (firstInput && !firstInput.value) {
                setTimeout(() => firstInput.focus(), 100);
            }
            
            // Gestion du formulaire
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', handleLoginSubmit);
        });
        
        /**
         * Toggle visibilité du mot de passe
         */
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.password-toggle .material-icons');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
        
        /**
         * Gestion de la soumission du formulaire
         */
        async function handleLoginSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('loginBtn');
            const formData = new FormData(form);
            
            // Validation côté client
            if (!validateLoginForm(form)) {
                return;
            }
            
            // État de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Animation de succès
                    gsap.to('.login-card', {
                        duration: 0.3,
                        scale: 1.05,
                        ease: 'power2.out',
                        yoyo: true,
                        repeat: 1
                    });
                    
                    if (typeof showAlert === 'function') {
                        showAlert('success', result.message || 'Connexion réussie !');
                    }
                    
                    // Redirection
                    setTimeout(() => {
                        if (result.requires_2fa) {
                            window.location.href = '/2fa';
                        } else {
                            window.location.href = result.redirect || '/dashboard';
                        }
                    }, 1500);
                } else {
                    // Animation d'erreur
                    gsap.to('.login-card', {
                        duration: 0.1,
                        x: -10,
                        ease: 'power2.inOut',
                        repeat: 5,
                        yoyo: true,
                        onComplete: () => {
                            gsap.set('.login-card', { x: 0 });
                        }
                    });
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur de connexion');
                    }
                }
            } catch (error) {
                console.error('Erreur de connexion:', error);
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau. Veuillez réessayer.');
                }
                
                // Animation d'erreur
                gsap.to('.login-card', {
                    duration: 0.5,
                    rotationX: 5,
                    ease: 'power2.out',
                    yoyo: true,
                    repeat: 1
                });
            } finally {
                // Retirer l'état de chargement
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        }
        
        /**
         * Validation basique du formulaire de connexion
         */
        function validateLoginForm(form) {
            const loginEmail = form.querySelector('#login_email');
            const password = form.querySelector('#password');
            
            if (!loginEmail.value.trim()) {
                loginEmail.focus();
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Veuillez saisir votre email ou nom d\'utilisateur');
                }
                return false;
            }
            
            if (!password.value) {
                password.focus();
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Veuillez saisir votre mot de passe');
                }
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>