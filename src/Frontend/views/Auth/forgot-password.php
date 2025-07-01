<?php
/**
 * Page de demande de réinitialisation de mot de passe - GestionMySoutenance
 * Interface moderne avec validation et feedback visuel
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Mot de passe oublié';
$pageSubtitle = 'Récupérez l\'accès à votre compte';
$showLogo = true;

// Données du formulaire (depuis le contrôleur)
$csrf_token = $csrf_token ?? '';
$email_principal = $email_principal ?? '';
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';
$rate_limit_remaining = $rate_limit_remaining ?? 3;
$rate_limit_reset = $rate_limit_reset ?? null;

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
    <meta name="description" content="Réinitialisation de mot de passe pour GestionMySoutenance - Récupérez l'accès à votre compte">
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
        /* Styles spécifiques à la page forgot password */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .forgot-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 500px;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 2;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .forgot-steps {
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
            background: var(--border-light);
            color: var(--text-secondary);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-sm);
            transition: all var(--transition-fast);
            position: relative;
        }
        
        .step.active {
            background: var(--primary-blue);
            color: var(--text-white);
            transform: scale(1.1);
        }
        
        .step.completed {
            background: var(--primary-green);
            color: var(--text-white);
        }
        
        .step-connector {
            width: 30px;
            height: 2px;
            background: var(--border-light);
            transition: background var(--transition-fast);
        }
        
        .step-connector.active {
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
        
        .info-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }
        
        .info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: var(--primary-blue);
            color: var(--text-white);
            border-radius: var(--border-radius-full);
            margin-bottom: var(--spacing-md);
            font-size: 28px;
        }
        
        .info-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
        }
        
        .info-text {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .email-input-group {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }
        
        .email-input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) 50px;
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.9);
        }
        
        .email-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
            transform: translateY(-2px);
        }
        
        .email-input.valid {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        .email-input.invalid {
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
        
        .email-input:focus + .input-icon {
            color: var(--primary-blue);
        }
        
        .email-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-top: none;
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            z-index: 10;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        
        .suggestion-item {
            padding: var(--spacing-sm) var(--spacing-md);
            cursor: pointer;
            transition: background var(--transition-fast);
            border-bottom: 1px solid var(--border-light);
        }
        
        .suggestion-item:hover {
            background: var(--hover-bg);
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
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
        
        .submit-btn.loading {
            opacity: 0.8;
        }
        
        .rate-limit-info {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border: 1px solid var(--accent-yellow);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--accent-yellow-dark);
            font-size: var(--font-size-sm);
        }
        
        .countdown-timer {
            font-weight: var(--font-weight-bold);
            color: var(--accent-red);
        }
        
        .security-notice {
            background: rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-top: var(--spacing-lg);
            text-align: center;
        }
        
        .security-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
        }
        
        .security-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .security-items li {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xs);
        }
        
        .security-items li:last-child {
            margin-bottom: 0;
        }
        
        /* Animation CSS */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .loading-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .forgot-container {
                padding: var(--spacing-md);
            }
            
            .forgot-card {
                padding: var(--spacing-lg);
                max-width: 100%;
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
        
        @media (max-width: 480px) {
            .forgot-card {
                padding: var(--spacing-md);
            }
            
            .info-section {
                padding: var(--spacing-md);
            }
            
            .info-icon {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <!-- Carte de réinitialisation -->
        <div class="forgot-card" id="forgotCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <!-- Indicateur d'étapes -->
            <div class="forgot-steps">
                <div class="step-indicator">
                    <div class="step active">
                        1
                        <div class="step-label">Email</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step">
                        2
                        <div class="step-label">Vérification</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step">
                        3
                        <div class="step-label">Nouveau MDP</div>
                    </div>
                </div>
            </div>
            
            <!-- Section d'information -->
            <div class="info-section">
                <div class="info-icon">
                    <span class="material-icons">mail_lock</span>
                </div>
                <div class="info-title">Récupération de mot de passe</div>
                <div class="info-text">
                    Saisissez votre adresse email pour recevoir un lien de réinitialisation sécurisé. 
                    Ce lien sera valide pendant 1 heure.
                </div>
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
            
            <!-- Information sur la limitation de taux -->
            <?php if ($rate_limit_remaining < 3): ?>
            <div class="rate-limit-info">
                <span class="material-icons" aria-hidden="true">warning</span>
                <span>
                    Tentatives restantes : <strong><?= e($rate_limit_remaining) ?></strong>
                    <?php if ($rate_limit_reset): ?>
                    - Nouveau essai dans <span class="countdown-timer" id="rateLimitTimer"></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire de demande de réinitialisation -->
            <form class="forgot-form" id="forgotForm" method="POST" action="/forgot-password" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                
                <!-- Champ Email -->
                <div class="email-input-group">
                    <input type="email" 
                           id="email_principal" 
                           name="email_principal" 
                           value="<?= e($email_principal) ?>"
                           placeholder="Votre adresse email"
                           required
                           autocomplete="email"
                           aria-describedby="email-help"
                           class="email-input input input-bordered">
                    <span class="input-icon material-icons" aria-hidden="true">email</span>
                    <div class="email-suggestions" id="emailSuggestions"></div>
                    <div id="email-help" class="sr-only">Saisissez l'adresse email associée à votre compte</div>
                </div>
                
                <!-- Bouton de soumission -->
                <button type="submit" 
                        class="submit-btn btn btn-primary" 
                        id="submitBtn"
                        <?= $rate_limit_remaining <= 0 ? 'disabled' : '' ?>>
                    <span class="btn-text">
                        <span class="material-icons" style="font-size: 20px;" aria-hidden="true">send</span>
                        Envoyer le lien
                    </span>
                    <div class="loading-spinner hidden">
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>
            
            <!-- Notice de sécurité -->
            <div class="security-notice">
                <div class="security-title">
                    <span class="material-icons" aria-hidden="true">security</span>
                    Sécurité et confidentialité
                </div>
                <ul class="security-items">
                    <li>
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">timer</span>
                        Le lien expire automatiquement après 1 heure
                    </li>
                    <li>
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">lock</span>
                        Connexion sécurisée SSL/TLS
                    </li>
                    <li>
                        <span class="material-icons" style="font-size: 14px;" aria-hidden="true">privacy_tip</span>
                        Vos données ne sont jamais partagées
                    </li>
                </ul>
            </div>
            
            <!-- Navigation -->
            <div class="form-footer-links" style="margin-top: var(--spacing-lg);">
                <a href="/login" class="link link-primary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">arrow_back</span>
                    Retour à la connexion
                </a>
                <a href="/" class="link link-secondary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">home</span>
                    Accueil
                </a>
            </div>
            
            <!-- Pied de page -->
            <?php $showSupport = true; include_once __DIR__ . '/components/auth-footer.php'; ?>
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
        const RATE_LIMIT_RESET = <?= $rate_limit_reset ? json_encode($rate_limit_reset) : 'null' ?>;
        const EMAIL_DOMAINS = ['gmail.com', 'yahoo.fr', 'outlook.com', 'hotmail.com', 'ufhb.edu.ci'];
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            gsap.timeline()
                .to('.forgot-card', {
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
                .from('.info-section', {
                    duration: 0.6,
                    y: 30,
                    opacity: 0,
                    ease: 'power2.out'
                }, '-=0.3')
                .from('.email-input-group, .submit-btn', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.2');
            
            // Initialisation des fonctionnalités
            initEmailValidation();
            initFormSubmission();
            initCountdownTimer();
            
            // Focus automatique
            const emailInput = document.getElementById('email_principal');
            if (emailInput && !emailInput.value) {
                setTimeout(() => emailInput.focus(), 100);
            }
        });
        
        /**
         * Initialise la validation email avec suggestions
         */
        function initEmailValidation() {
            const emailInput = document.getElementById('email_principal');
            const suggestions = document.getElementById('emailSuggestions');
            
            emailInput.addEventListener('input', function() {
                const value = this.value.trim();
                
                // Validation en temps réel
                if (value) {
                    if (isValidEmail(value)) {
                        this.classList.remove('invalid');
                        this.classList.add('valid');
                    } else {
                        this.classList.remove('valid');
                        this.classList.add('invalid');
                    }
                    
                    // Suggestions d'email
                    showEmailSuggestions(value);
                } else {
                    this.classList.remove('valid', 'invalid');
                    hideSuggestions();
                }
            });
            
            emailInput.addEventListener('blur', function() {
                setTimeout(() => hideSuggestions(), 200); // Délai pour permettre le clic
            });
            
            emailInput.addEventListener('focus', function() {
                if (this.value) {
                    showEmailSuggestions(this.value);
                }
            });
        }
        
        /**
         * Affiche les suggestions d'email
         */
        function showEmailSuggestions(value) {
            const suggestions = document.getElementById('emailSuggestions');
            const atIndex = value.indexOf('@');
            
            if (atIndex === -1) {
                // Suggérer des domaines si pas de @
                const suggestedEmails = EMAIL_DOMAINS.map(domain => `${value}@${domain}`);
                renderSuggestions(suggestedEmails);
            } else {
                // Suggérer des domaines similaires
                const username = value.substring(0, atIndex);
                const domain = value.substring(atIndex + 1);
                
                if (username && domain) {
                    const matchingDomains = EMAIL_DOMAINS.filter(d => 
                        d.toLowerCase().includes(domain.toLowerCase())
                    );
                    
                    if (matchingDomains.length > 0) {
                        const suggestedEmails = matchingDomains.map(d => `${username}@${d}`);
                        renderSuggestions(suggestedEmails);
                    } else {
                        hideSuggestions();
                    }
                }
            }
        }
        
        /**
         * Affiche les suggestions dans le DOM
         */
        function renderSuggestions(emails) {
            const suggestions = document.getElementById('emailSuggestions');
            
            if (emails.length === 0) {
                hideSuggestions();
                return;
            }
            
            suggestions.innerHTML = emails.map(email => 
                `<div class="suggestion-item" onclick="selectSuggestion('${email}')">${email}</div>`
            ).join('');
            
            suggestions.style.display = 'block';
        }
        
        /**
         * Sélectionne une suggestion d'email
         */
        function selectSuggestion(email) {
            const emailInput = document.getElementById('email_principal');
            emailInput.value = email;
            emailInput.classList.remove('invalid');
            emailInput.classList.add('valid');
            hideSuggestions();
            emailInput.focus();
        }
        
        /**
         * Cache les suggestions
         */
        function hideSuggestions() {
            const suggestions = document.getElementById('emailSuggestions');
            suggestions.style.display = 'none';
        }
        
        /**
         * Validation email
         */
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
        
        /**
         * Initialise la soumission du formulaire
         */
        function initFormSubmission() {
            const form = document.getElementById('forgotForm');
            form.addEventListener('submit', handleForgotSubmit);
        }
        
        /**
         * Gère la soumission du formulaire
         */
        async function handleForgotSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const emailInput = document.getElementById('email_principal');
            const formData = new FormData(form);
            
            // Validation
            if (!validateForm(form)) {
                return;
            }
            
            // État de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="loading-pulse">
                    <span class="material-icons" style="font-size: 20px;">hourglass_empty</span>
                    Envoi en cours...
                </span>
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
                        showAlert('success', result.message || 'Email de réinitialisation envoyé !', {
                            duration: 8000,
                            actions: [{
                                text: 'Vérifier mes emails',
                                type: 'primary',
                                onclick: 'window.open("mailto:", "_self")'
                            }]
                        });
                    }
                    
                    // Mettre à jour l'interface
                    updateStepIndicator(2);
                    
                    // Désactiver le formulaire
                    emailInput.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="material-icons" style="font-size: 20px;">check_circle</span>
                        Email envoyé
                    `;
                    
                } else {
                    // Animation d'erreur
                    animateError();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur lors de l\'envoi');
                    }
                    
                    // Restaurer le bouton
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
         * Valide le formulaire
         */
        function validateForm(form) {
            const emailInput = form.querySelector('#email_principal');
            const email = emailInput.value.trim();
            
            if (!email) {
                emailInput.focus();
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Veuillez saisir votre adresse email');
                }
                return false;
            }
            
            if (!isValidEmail(email)) {
                emailInput.focus();
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Veuillez saisir une adresse email valide');
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
                .to('.forgot-card', {
                    duration: 0.3,
                    scale: 1.02,
                    ease: 'power2.out'
                })
                .to('.forgot-card', {
                    duration: 0.3,
                    scale: 1,
                    ease: 'power2.out'
                })
                .to('.info-icon', {
                    duration: 0.5,
                    rotation: 360,
                    ease: 'power2.out'
                }, '-=0.3');
        }
        
        /**
         * Animation d'erreur
         */
        function animateError() {
            gsap.to('.forgot-card', {
                duration: 0.1,
                x: -10,
                ease: 'power2.inOut',
                repeat: 5,
                yoyo: true,
                onComplete: () => {
                    gsap.set('.forgot-card', { x: 0 });
                }
            });
        }
        
        /**
         * Met à jour l'indicateur d'étapes
         */
        function updateStepIndicator(activeStep) {
            const steps = document.querySelectorAll('.step');
            const connectors = document.querySelectorAll('.step-connector');
            
            steps.forEach((step, index) => {
                const stepNumber = index + 1;
                step.classList.remove('active');
                
                if (stepNumber < activeStep) {
                    step.classList.add('completed');
                    step.innerHTML = '<span class="material-icons">check</span>';
                } else if (stepNumber === activeStep) {
                    step.classList.add('active');
                }
            });
            
            connectors.forEach((connector, index) => {
                if (index < activeStep - 1) {
                    connector.classList.add('active');
                }
            });
        }
        
        /**
         * Restaure le bouton de soumission
         */
        function restoreSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <span class="material-icons" style="font-size: 20px;">send</span>
                Envoyer le lien
            `;
        }
        
        /**
         * Initialise le timer de compte à rebours
         */
        function initCountdownTimer() {
            if (!RATE_LIMIT_RESET) return;
            
            const timer = document.getElementById('rateLimitTimer');
            if (!timer) return;
            
            const resetTime = new Date(RATE_LIMIT_RESET).getTime();
            
            const updateTimer = () => {
                const now = new Date().getTime();
                const distance = resetTime - now;
                
                if (distance < 0) {
                    timer.textContent = '0s';
                    location.reload(); // Recharger pour permettre un nouvel essai
                    return;
                }
                
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                timer.textContent = `${minutes}m ${seconds}s`;
            };
            
            updateTimer();
            setInterval(updateTimer, 1000);
        }
    </script>
</body>
</html>