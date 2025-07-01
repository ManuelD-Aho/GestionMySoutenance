<?php
/**
 * Page de réinitialisation de mot de passe - GestionMySoutenance
 * Interface moderne avec validation de sécurité et barre de force
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Nouveau mot de passe';
$pageSubtitle = 'Sécurisez votre compte avec un mot de passe fort';
$showLogo = true;

// Données du formulaire (depuis le contrôleur)
$csrf_token = $csrf_token ?? '';
$reset_token = $reset_token ?? '';
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';
$password_requirements = $password_requirements ?? [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special' => true,
    'max_history' => 5
];

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
    <meta name="description" content="Réinitialisation sécurisée de mot de passe pour GestionMySoutenance">
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
        /* Styles spécifiques à la page reset password */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 550px;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 2;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .reset-steps {
            display: flex;
            justify-content: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .step-indicator {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .step {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-full);
            background: var(--primary-green);
            color: var(--text-white);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-sm);
            position: relative;
        }
        
        .step.completed {
            background: var(--primary-green);
        }
        
        .step.active {
            background: var(--primary-blue);
            transform: scale(1.1);
            box-shadow: var(--shadow-md);
        }
        
        .step-connector {
            width: 30px;
            height: 2px;
            background: var(--primary-green);
        }
        
        .step-label {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
            white-space: nowrap;
            margin-top: var(--spacing-xs);
        }
        
        .password-form .form-control {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }
        
        .password-input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) 50px;
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.9);
            font-family: monospace;
        }
        
        .password-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
            transform: translateY(-2px);
        }
        
        .password-input.valid {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        .password-input.invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }
        
        .password-input:focus + .input-icon {
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
        
        .password-strength {
            margin-top: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .strength-meter {
            height: 8px;
            background: var(--border-light);
            border-radius: var(--border-radius-full);
            overflow: hidden;
            margin-bottom: var(--spacing-sm);
            position: relative;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all var(--transition-normal);
            border-radius: var(--border-radius-full);
            background: linear-gradient(90deg, var(--accent-red), var(--accent-yellow), var(--primary-green));
            position: relative;
        }
        
        .strength-indicator {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 20px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius-full);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .strength-text {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }
        
        .strength-level {
            color: var(--text-secondary);
        }
        
        .strength-score {
            font-family: monospace;
            font-weight: var(--font-weight-bold);
        }
        
        .requirements-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .requirements-title {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-base);
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-sm);
        }
        
        .requirement-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            transition: all var(--transition-fast);
            padding: var(--spacing-xs);
            border-radius: var(--border-radius-sm);
        }
        
        .requirement-item.valid {
            color: var(--primary-green-dark);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .requirement-item.invalid {
            color: var(--accent-red-dark);
        }
        
        .requirement-icon {
            font-size: 16px;
            transition: all var(--transition-fast);
        }
        
        .requirement-item.valid .requirement-icon {
            color: var(--primary-green);
            transform: scale(1.1);
        }
        
        .requirement-item.invalid .requirement-icon {
            color: var(--accent-red);
        }
        
        .submit-btn {
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
            margin-bottom: var(--spacing-lg);
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .security-features {
            background: rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }
        
        .security-features-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-md);
        }
        
        .security-features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-sm);
        }
        
        .security-feature {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
        }
        
        .timer-display {
            text-align: center;
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border: 1px solid var(--accent-yellow);
            border-radius: var(--border-radius-lg);
        }
        
        .timer-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            color: var(--accent-yellow-dark);
            font-weight: var(--font-weight-medium);
            font-size: var(--font-size-sm);
        }
        
        .timer-countdown {
            font-family: monospace;
            font-weight: var(--font-weight-bold);
            color: var(--accent-red);
            font-size: var(--font-size-lg);
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes strengthIncrease {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        
        .strength-fill.animating {
            animation: strengthIncrease 0.5s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .reset-container {
                padding: var(--spacing-md);
            }
            
            .reset-card {
                padding: var(--spacing-lg);
                max-width: 100%;
            }
            
            .requirements-list {
                grid-template-columns: 1fr;
            }
            
            .security-features-list {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .reset-card {
                padding: var(--spacing-md);
            }
            
            .step-indicator {
                gap: var(--spacing-sm);
            }
            
            .step {
                width: 35px;
                height: 35px;
                font-size: var(--font-size-xs);
            }
            
            .step-connector {
                width: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Carte de réinitialisation -->
        <div class="reset-card" id="resetCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <!-- Indicateur d'étapes -->
            <div class="reset-steps">
                <div class="step-indicator">
                    <div class="step completed">
                        <span class="material-icons">check</span>
                        <div class="step-label">Email</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step completed">
                        <span class="material-icons">check</span>
                        <div class="step-label">Vérification</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step active">
                        3
                        <div class="step-label">Nouveau MDP</div>
                    </div>
                </div>
            </div>
            
            <!-- Timer d'expiration -->
            <div class="timer-display" id="timerDisplay">
                <div class="timer-text">
                    <span class="material-icons" aria-hidden="true">timer</span>
                    <span>Ce lien expire dans :</span>
                </div>
                <div class="timer-countdown" id="tokenTimer">59:30</div>
            </div>
            
            <!-- Messages d'alerte -->
            <?php if ($error_message): ?>
            <div class="alert alert-error" role="alert">
                <span class="material-icons" aria-hidden="true">error</span>
                <?= e($error_message) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <span class="material-icons" aria-hidden="true">check_circle</span>
                <?= e($success_message) ?>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire de réinitialisation -->
            <form class="password-form" id="resetForm" method="POST" action="/reset-password" novalidate>
                <!-- Tokens -->
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="reset_token" value="<?= e($reset_token) ?>">
                
                <!-- Nouveau mot de passe -->
                <div class="form-control">
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           placeholder="Nouveau mot de passe"
                           required
                           autocomplete="new-password"
                           aria-describedby="password-requirements"
                           class="password-input input input-bordered">
                    <span class="input-icon material-icons" aria-hidden="true">lock</span>
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword('new_password')"
                            aria-label="Afficher/masquer le mot de passe"
                            title="Afficher/masquer le mot de passe">
                        <span class="material-icons">visibility</span>
                    </button>
                </div>
                
                <!-- Barre de force du mot de passe -->
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-meter">
                        <div class="strength-fill" id="strengthFill">
                            <div class="strength-indicator"></div>
                        </div>
                    </div>
                    <div class="strength-text">
                        <span class="strength-level" id="strengthLevel">Faible</span>
                        <span class="strength-score" id="strengthScore">0/100</span>
                    </div>
                </div>
                
                <!-- Confirmation du mot de passe -->
                <div class="form-control">
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirmer le nouveau mot de passe"
                           required
                           autocomplete="new-password"
                           class="password-input input input-bordered">
                    <span class="input-icon material-icons" aria-hidden="true">lock_outline</span>
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword('confirm_password')"
                            aria-label="Afficher/masquer la confirmation"
                            title="Afficher/masquer la confirmation">
                        <span class="material-icons">visibility</span>
                    </button>
                </div>
                
                <!-- Exigences de sécurité -->
                <div class="requirements-section" id="password-requirements">
                    <div class="requirements-title">
                        <span class="material-icons" aria-hidden="true">security</span>
                        Exigences de sécurité
                    </div>
                    <ul class="requirements-list">
                        <li class="requirement-item" data-requirement="length">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Au moins <?= e($password_requirements['min_length']) ?> caractères
                        </li>
                        <li class="requirement-item" data-requirement="uppercase">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Une majuscule (A-Z)
                        </li>
                        <li class="requirement-item" data-requirement="lowercase">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Une minuscule (a-z)
                        </li>
                        <li class="requirement-item" data-requirement="numbers">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Un chiffre (0-9)
                        </li>
                        <li class="requirement-item" data-requirement="special">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Un caractère spécial
                        </li>
                        <li class="requirement-item" data-requirement="match">
                            <span class="requirement-icon material-icons">radio_button_unchecked</span>
                            Mots de passe identiques
                        </li>
                    </ul>
                </div>
                
                <!-- Bouton de soumission -->
                <button type="submit" class="submit-btn btn btn-primary" id="submitBtn" disabled>
                    <span class="material-icons" style="font-size: 20px;" aria-hidden="true">vpn_key</span>
                    Mettre à jour le mot de passe
                </button>
            </form>
            
            <!-- Fonctionnalités de sécurité -->
            <div class="security-features">
                <div class="security-features-title">
                    <span class="material-icons" aria-hidden="true">verified_user</span>
                    Sécurité avancée
                </div>
                <div class="security-features-list">
                    <div class="security-feature">
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">history</span>
                        Protection historique (<?= e($password_requirements['max_history']) ?> derniers)
                    </div>
                    <div class="security-feature">
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">fingerprint</span>
                        Chiffrement SHA-256
                    </div>
                    <div class="security-feature">
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">shield</span>
                        Protection CSRF
                    </div>
                    <div class="security-feature">
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">timer</span>
                        Expiration automatique
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="form-footer-links" style="margin-top: var(--spacing-lg);">
                <a href="/login" class="link link-primary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">arrow_back</span>
                    Retour à la connexion
                </a>
                <a href="/forgot-password" class="link link-secondary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">refresh</span>
                    Nouveau lien
                </a>
            </div>
            
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
        // Configuration
        const PASSWORD_REQUIREMENTS = <?= json_encode($password_requirements) ?>;
        const TOKEN_EXPIRE_TIME = 60; // minutes (sera récupéré du serveur en réalité)
        
        // Variables globales
        let passwordStrength = 0;
        let allRequirementsMet = false;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            initAnimations();
            
            // Initialisation des fonctionnalités
            initPasswordValidation();
            initFormSubmission();
            initTokenTimer();
            
            // Focus automatique
            const passwordInput = document.getElementById('new_password');
            setTimeout(() => passwordInput.focus(), 100);
        });
        
        /**
         * Initialise les animations d'entrée
         */
        function initAnimations() {
            gsap.timeline()
                .to('.reset-card', {
                    duration: 0.8,
                    y: 0,
                    opacity: 1,
                    ease: 'power3.out'
                })
                .from('.step', {
                    duration: 0.5,
                    scale: 0,
                    ease: 'back.out(1.7)',
                    stagger: 0.1
                }, '-=0.5')
                .from('.timer-display', {
                    duration: 0.6,
                    y: -30,
                    opacity: 0,
                    ease: 'power2.out'
                }, '-=0.3')
                .from('.form-control, .requirements-section', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.2');
        }
        
        /**
         * Initialise la validation des mots de passe
         */
        function initPasswordValidation() {
            const passwordInput = document.getElementById('new_password');
            const confirmInput = document.getElementById('confirm_password');
            
            passwordInput.addEventListener('input', function() {
                validatePassword(this.value);
                checkPasswordMatch();
                updateSubmitButton();
            });
            
            confirmInput.addEventListener('input', function() {
                checkPasswordMatch();
                updateSubmitButton();
            });
            
            // Validation en temps réel avec debounce
            let validationTimeout;
            passwordInput.addEventListener('input', function() {
                clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => {
                    validatePasswordStrength(this.value);
                }, 150);
            });
        }
        
        /**
         * Valide le mot de passe selon les exigences
         */
        function validatePassword(password) {
            const requirements = {
                length: password.length >= PASSWORD_REQUIREMENTS.min_length,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                numbers: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
            
            // Mettre à jour l'affichage des exigences
            Object.keys(requirements).forEach(req => {
                const element = document.querySelector(`[data-requirement="${req}"]`);
                const icon = element.querySelector('.requirement-icon');
                
                if (requirements[req]) {
                    element.classList.add('valid');
                    element.classList.remove('invalid');
                    icon.textContent = 'check_circle';
                    
                    // Animation de validation
                    gsap.to(element, {
                        duration: 0.3,
                        scale: 1.05,
                        ease: 'back.out(1.7)',
                        yoyo: true,
                        repeat: 1
                    });
                } else {
                    element.classList.remove('valid');
                    element.classList.add('invalid');
                    icon.textContent = 'radio_button_unchecked';
                }
            });
            
            allRequirementsMet = Object.values(requirements).every(req => req);
            return allRequirementsMet;
        }
        
        /**
         * Calcule et affiche la force du mot de passe
         */
        function validatePasswordStrength(password) {
            let score = 0;
            let level = 'Très faible';
            
            // Calcul du score
            if (password.length >= 8) score += 20;
            if (password.length >= 12) score += 10;
            if (/[a-z]/.test(password)) score += 10;
            if (/[A-Z]/.test(password)) score += 10;
            if (/[0-9]/.test(password)) score += 10;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score += 15;
            if (password.length >= 16) score += 10;
            if (/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) score += 15;
            
            // Déterminer le niveau
            if (score >= 90) level = 'Excellent';
            else if (score >= 70) level = 'Très fort';
            else if (score >= 50) level = 'Fort';
            else if (score >= 30) level = 'Moyen';
            else if (score >= 10) level = 'Faible';
            
            passwordStrength = score;
            updateStrengthMeter(score, level);
        }
        
        /**
         * Met à jour la barre de force du mot de passe
         */
        function updateStrengthMeter(score, level) {
            const fill = document.getElementById('strengthFill');
            const levelElement = document.getElementById('strengthLevel');
            const scoreElement = document.getElementById('strengthScore');
            
            // Animation de la barre
            fill.classList.add('animating');
            gsap.to(fill, {
                duration: 0.5,
                width: `${score}%`,
                ease: 'power2.out',
                onComplete: () => {
                    fill.classList.remove('animating');
                }
            });
            
            // Couleur selon le score
            if (score >= 70) {
                fill.style.background = 'var(--primary-green)';
            } else if (score >= 40) {
                fill.style.background = 'var(--accent-yellow)';
            } else {
                fill.style.background = 'var(--accent-red)';
            }
            
            levelElement.textContent = level;
            scoreElement.textContent = `${score}/100`;
            
            // Animation du texte
            gsap.from([levelElement, scoreElement], {
                duration: 0.3,
                scale: 1.1,
                ease: 'power2.out'
            });
        }
        
        /**
         * Vérifie la correspondance des mots de passe
         */
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const matchReq = document.querySelector('[data-requirement="match"]');
            const icon = matchReq.querySelector('.requirement-icon');
            
            if (confirm && password === confirm) {
                matchReq.classList.add('valid');
                matchReq.classList.remove('invalid');
                icon.textContent = 'check_circle';
                
                document.getElementById('confirm_password').classList.add('valid');
                document.getElementById('confirm_password').classList.remove('invalid');
            } else if (confirm) {
                matchReq.classList.remove('valid');
                matchReq.classList.add('invalid');
                icon.textContent = 'radio_button_unchecked';
                
                document.getElementById('confirm_password').classList.remove('valid');
                document.getElementById('confirm_password').classList.add('invalid');
            } else {
                matchReq.classList.remove('valid', 'invalid');
                icon.textContent = 'radio_button_unchecked';
                
                document.getElementById('confirm_password').classList.remove('valid', 'invalid');
            }
        }
        
        /**
         * Met à jour l'état du bouton de soumission
         */
        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            const canSubmit = allRequirementsMet && 
                            password && 
                            confirm && 
                            password === confirm &&
                            passwordStrength >= 30; // Score minimum requis
            
            submitBtn.disabled = !canSubmit;
            
            if (canSubmit) {
                submitBtn.classList.add('btn-success');
                submitBtn.classList.remove('btn-disabled');
                
                // Animation de disponibilité
                gsap.to(submitBtn, {
                    duration: 0.3,
                    scale: 1.02,
                    ease: 'power2.out',
                    yoyo: true,
                    repeat: 1
                });
            } else {
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-disabled');
            }
        }
        
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
            
            // Animation du toggle
            gsap.to(icon, {
                duration: 0.2,
                rotation: 180,
                ease: 'power2.out',
                onComplete: () => {
                    gsap.set(icon, { rotation: 0 });
                }
            });
        }
        
        /**
         * Initialise la soumission du formulaire
         */
        function initFormSubmission() {
            const form = document.getElementById('resetForm');
            form.addEventListener('submit', handleResetSubmit);
        }
        
        /**
         * Gère la soumission du formulaire
         */
        async function handleResetSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(form);
            
            // Validation finale
            if (!validateResetForm(form)) {
                return;
            }
            
            // État de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                Mise à jour...
            `;
            
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
                    animateSuccess();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Mot de passe mis à jour avec succès !', {
                            duration: 6000,
                            actions: [{
                                text: 'Se connecter',
                                type: 'primary',
                                onclick: 'window.location.href="/login"'
                            }]
                        });
                    }
                    
                    // Redirection après 3 secondes
                    setTimeout(() => {
                        window.location.href = '/login?message=' + encodeURIComponent('Mot de passe mis à jour. Vous pouvez vous connecter.');
                    }, 3000);
                    
                } else {
                    // Animation d'erreur
                    animateError();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur lors de la mise à jour');
                    }
                    
                    restoreSubmitButton();
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
                
                animateError();
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau. Veuillez réessayer.');
                }
                
                restoreSubmitButton();
            }
        }
        
        /**
         * Valide le formulaire de réinitialisation
         */
        function validateResetForm(form) {
            const password = form.querySelector('#new_password').value;
            const confirm = form.querySelector('#confirm_password').value;
            
            if (!allRequirementsMet) {
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Le mot de passe ne respecte pas toutes les exigences de sécurité');
                }
                return false;
            }
            
            if (password !== confirm) {
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Les mots de passe ne correspondent pas');
                }
                return false;
            }
            
            if (passwordStrength < 30) {
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Le mot de passe n\'est pas assez fort (minimum 30/100)');
                }
                return false;
            }
            
            return true;
        }
        
        /**
         * Animation de succès
         */
        function animateSuccess() {
            gsap.timeline()
                .to('.reset-card', {
                    duration: 0.4,
                    scale: 1.03,
                    ease: 'power2.out'
                })
                .to('.reset-card', {
                    duration: 0.4,
                    scale: 1,
                    ease: 'power2.out'
                })
                .to('.step.active', {
                    duration: 0.5,
                    backgroundColor: 'var(--primary-green)',
                    ease: 'power2.out',
                    onComplete: () => {
                        document.querySelector('.step.active').innerHTML = '<span class="material-icons">check</span>';
                    }
                }, '-=0.4');
        }
        
        /**
         * Animation d'erreur
         */
        function animateError() {
            gsap.to('.reset-card', {
                duration: 0.1,
                x: -10,
                ease: 'power2.inOut',
                repeat: 5,
                yoyo: true,
                onComplete: () => {
                    gsap.set('.reset-card', { x: 0 });
                }
            });
        }
        
        /**
         * Restaure le bouton de soumission
         */
        function restoreSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.remove('loading');
            updateSubmitButton(); // Réévalue l'état
            submitBtn.innerHTML = `
                <span class="material-icons" style="font-size: 20px;">vpn_key</span>
                Mettre à jour le mot de passe
            `;
        }
        
        /**
         * Initialise le timer d'expiration du token
         */
        function initTokenTimer() {
            const timerElement = document.getElementById('tokenTimer');
            if (!timerElement) return;
            
            let timeLeft = TOKEN_EXPIRE_TIME * 60; // En secondes
            
            const updateTimer = () => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Changer la couleur selon le temps restant
                if (timeLeft < 300) { // 5 minutes
                    timerElement.style.color = 'var(--accent-red)';
                    timerElement.style.animation = 'pulse 1s ease-in-out infinite';
                } else if (timeLeft < 900) { // 15 minutes
                    timerElement.style.color = 'var(--accent-yellow-dark)';
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    if (typeof showAlert === 'function') {
                        showAlert('error', 'Le lien de réinitialisation a expiré. Veuillez en demander un nouveau.', {
                            duration: 10000,
                            actions: [{
                                text: 'Nouveau lien',
                                type: 'primary',
                                onclick: 'window.location.href="/forgot-password"'
                            }]
                        });
                    }
                    document.getElementById('resetForm').style.opacity = '0.5';
                    document.getElementById('submitBtn').disabled = true;
                    return;
                }
                
                timeLeft--;
            };
            
            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);
        }
    </script>
</body>
</html>