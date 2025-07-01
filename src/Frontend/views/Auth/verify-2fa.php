<?php
/**
 * Page de vérification 2FA - GestionMySoutenance
 * Interface moderne pour l'authentification à deux facteurs
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Authentification à deux facteurs';
$pageSubtitle = 'Sécurisez votre connexion avec un code de vérification';
$showLogo = true;

// Données du formulaire (depuis le contrôleur)
$csrf_token = $csrf_token ?? '';
$user_email = $user_email ?? '';
$backup_codes_available = $backup_codes_available ?? false;
$resend_available = $resend_available ?? true;
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';
$attempts_remaining = $attempts_remaining ?? 3;
$lockout_time = $lockout_time ?? null;

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
    <meta name="description" content="Vérification 2FA pour GestionMySoutenance - Sécurité renforcée">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> - GestionMySoutenance</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
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
        /* Styles spécifiques à la page 2FA */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .twofa-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .twofa-card {
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
        
        .security-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .security-shield {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-green) 100%);
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-white);
            font-size: 36px;
            position: relative;
            animation: securityPulse 2s ease-in-out infinite;
        }
        
        .security-shield::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: var(--border-radius-full);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-green) 100%);
            opacity: 0.3;
            transform: scale(1.2);
            animation: securityRipple 2s ease-out infinite;
        }
        
        @keyframes securityPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes securityRipple {
            0% { transform: scale(1.2); opacity: 0.3; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        
        .user-info {
            text-align: center;
            margin-bottom: var(--spacing-xl);
            padding: var(--spacing-lg);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--border-radius-lg);
        }
        
        .user-email {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            font-weight: var(--font-weight-medium);
            margin-bottom: var(--spacing-sm);
        }
        
        .user-email strong {
            color: var(--primary-blue);
            font-family: 'JetBrains Mono', monospace;
        }
        
        .verification-instructions {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .code-input-container {
            margin-bottom: var(--spacing-xl);
        }
        
        .code-inputs {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .code-digit {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            font-family: 'JetBrains Mono', monospace;
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            background: rgba(255, 255, 255, 0.9);
            transition: all var(--transition-normal);
            outline: none;
        }
        
        .code-digit:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
            transform: translateY(-2px) scale(1.05);
        }
        
        .code-digit.filled {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        }
        
        .code-digit.error {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            animation: shake 0.5s ease-in-out;
        }
        
        .code-input-label {
            text-align: center;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-md);
        }
        
        .verification-timer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-lg);
        }
        
        .timer-countdown {
            font-family: 'JetBrains Mono', monospace;
            font-weight: var(--font-weight-bold);
            color: var(--primary-blue);
        }
        
        .timer-countdown.warning {
            color: var(--accent-yellow-dark);
            animation: pulse 1s ease-in-out infinite;
        }
        
        .timer-countdown.critical {
            color: var(--accent-red);
            animation: pulse 0.5s ease-in-out infinite;
        }
        
        .verify-btn {
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
        
        .verify-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }
        
        .verify-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alternative-methods {
            border-top: 1px solid var(--border-light);
            padding-top: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }
        
        .alternative-title {
            text-align: center;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-md);
        }
        
        .alternative-options {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        .alternative-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-medium);
            border-radius: var(--border-radius-md);
            background: transparent;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            transition: all var(--transition-fast);
            cursor: pointer;
        }
        
        .alternative-btn:hover {
            background: var(--primary-blue);
            color: var(--text-white);
            border-color: var(--primary-blue);
            transform: translateY(-1px);
        }
        
        .alternative-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .attempts-warning {
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
        
        .lockout-info {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            border: 1px solid var(--accent-red);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            text-align: center;
        }
        
        .lockout-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            color: var(--accent-red);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-sm);
        }
        
        .lockout-message {
            color: var(--accent-red-dark);
            font-size: var(--font-size-sm);
            line-height: 1.6;
        }
        
        .lockout-timer {
            font-family: 'JetBrains Mono', monospace;
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-lg);
            color: var(--accent-red);
            margin-top: var(--spacing-sm);
        }
        
        .backup-code-toggle {
            display: none;
        }
        
        .backup-code-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'JetBrains Mono', monospace;
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            transition: all var(--transition-normal);
            margin-bottom: var(--spacing-md);
        }
        
        .backup-code-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
        }
        
        /* Animations */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes typewriter {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .twofa-container {
                padding: var(--spacing-md);
            }
            
            .twofa-card {
                padding: var(--spacing-lg);
                max-width: 100%;
            }
            
            .code-digit {
                width: 45px;
                height: 55px;
                font-size: var(--font-size-lg);
            }
            
            .security-shield {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }
        }
        
        @media (max-width: 480px) {
            .twofa-card {
                padding: var(--spacing-md);
            }
            
            .code-inputs {
                gap: var(--spacing-xs);
            }
            
            .code-digit {
                width: 40px;
                height: 50px;
                font-size: var(--font-size-base);
            }
            
            .alternative-options {
                gap: var(--spacing-xs);
            }
        }
    </style>
</head>
<body>
    <div class="twofa-container">
        <!-- Carte 2FA -->
        <div class="twofa-card" id="twofaCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <!-- Indicateur de sécurité -->
            <div class="security-indicator">
                <div class="security-shield">
                    <span class="material-icons">security</span>
                </div>
            </div>
            
            <!-- Informations utilisateur -->
            <div class="user-info">
                <div class="user-email">
                    Code envoyé à : <strong><?= e($user_email) ?></strong>
                </div>
                <div class="verification-instructions">
                    Ouvrez votre application d'authentification et saisissez le code à 6 chiffres généré pour votre compte GestionMySoutenance.
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
            
            <!-- Avertissement tentatives -->
            <?php if ($attempts_remaining < 3 && !$lockout_time): ?>
            <div class="attempts-warning">
                <span class="material-icons" aria-hidden="true">warning</span>
                <span>Attention : <?= e($attempts_remaining) ?> tentative(s) restante(s)</span>
            </div>
            <?php endif; ?>
            
            <!-- Information de verrouillage -->
            <?php if ($lockout_time): ?>
            <div class="lockout-info">
                <div class="lockout-title">
                    <span class="material-icons" aria-hidden="true">lock_clock</span>
                    Compte temporairement verrouillé
                </div>
                <div class="lockout-message">
                    Trop de tentatives échouées. Veuillez patienter avant de réessayer.
                </div>
                <div class="lockout-timer" id="lockoutTimer">--:--</div>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire de vérification -->
            <form class="twofa-form" id="twofaForm" method="POST" action="/2fa" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                
                <!-- Timer de vérification -->
                <div class="verification-timer">
                    <span class="material-icons" aria-hidden="true">timer</span>
                    <span>Code valide pendant :</span>
                    <span class="timer-countdown" id="codeTimer">30s</span>
                </div>
                
                <!-- Saisie du code 2FA -->
                <div class="code-input-container" id="normalCodeInput">
                    <div class="code-input-label">
                        Saisissez votre code à 6 chiffres
                    </div>
                    <div class="code-inputs">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" data-index="0">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="1">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="2">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="3">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="4">
                        <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="5">
                    </div>
                    <input type="hidden" name="verification_code" id="verificationCode">
                </div>
                
                <!-- Saisie code de récupération (caché par défaut) -->
                <div class="backup-code-toggle" id="backupCodeInput">
                    <div class="code-input-label">
                        Saisissez un code de récupération
                    </div>
                    <input type="text" 
                           class="backup-code-input" 
                           name="backup_code" 
                           placeholder="ABCD-EFGH-IJKL-MNOP"
                           autocomplete="off">
                </div>
                
                <!-- Bouton de vérification -->
                <button type="submit" 
                        class="verify-btn btn btn-primary" 
                        id="verifyBtn" 
                        disabled
                        <?= $lockout_time ? 'disabled' : '' ?>>
                    <span class="material-icons" style="font-size: 20px;" aria-hidden="true">verified_user</span>
                    Vérifier le code
                </button>
            </form>
            
            <!-- Méthodes alternatives -->
            <div class="alternative-methods">
                <div class="alternative-title">Autres options</div>
                <div class="alternative-options">
                    <?php if ($backup_codes_available): ?>
                    <button type="button" class="alternative-btn" onclick="toggleBackupCode()">
                        <span class="material-icons" aria-hidden="true">key</span>
                        Utiliser un code de récupération
                    </button>
                    <?php endif; ?>
                    
                    <button type="button" 
                            class="alternative-btn <?= !$resend_available ? 'disabled' : '' ?>" 
                            onclick="resendCode()"
                            id="resendBtn">
                        <span class="material-icons" aria-hidden="true">refresh</span>
                        Renvoyer le code
                    </button>
                    
                    <a href="/login" class="alternative-btn">
                        <span class="material-icons" aria-hidden="true">logout</span>
                        Se déconnecter
                    </a>
                </div>
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
        const LOCKOUT_TIME = <?= $lockout_time ? json_encode($lockout_time) : 'null' ?>;
        const CODE_VALIDITY_DURATION = 30; // secondes
        
        // Variables globales
        let codeTimer = CODE_VALIDITY_DURATION;
        let isBackupMode = false;
        let resendCooldown = 0;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            initAnimations();
            
            // Initialisation des fonctionnalités
            initCodeInput();
            initFormSubmission();
            initTimers();
            
            // Focus automatique sur le premier champ
            if (!LOCKOUT_TIME) {
                const firstDigit = document.querySelector('.code-digit');
                setTimeout(() => firstDigit.focus(), 100);
            }
        });
        
        /**
         * Initialise les animations d'entrée
         */
        function initAnimations() {
            gsap.timeline()
                .to('.twofa-card', {
                    duration: 0.8,
                    y: 0,
                    opacity: 1,
                    ease: 'power3.out'
                })
                .from('.security-shield', {
                    duration: 0.8,
                    scale: 0,
                    rotation: -180,
                    ease: 'back.out(1.7)'
                }, '-=0.6')
                .from('.user-info', {
                    duration: 0.6,
                    y: 30,
                    opacity: 0,
                    ease: 'power2.out'
                }, '-=0.4')
                .from('.code-digit', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.3')
                .from('.verify-btn', {
                    duration: 0.4,
                    scale: 0.95,
                    opacity: 0,
                    ease: 'back.out(1.7)'
                }, '-=0.2');
        }
        
        /**
         * Initialise la saisie du code
         */
        function initCodeInput() {
            const digits = document.querySelectorAll('.code-digit');
            
            digits.forEach((digit, index) => {
                // Saisie de chiffres
                digit.addEventListener('input', function() {
                    const value = this.value.replace(/[^0-9]/g, '');
                    this.value = value;
                    
                    if (value) {
                        this.classList.add('filled');
                        
                        // Animation de saisie
                        gsap.to(this, {
                            duration: 0.2,
                            scale: 1.1,
                            ease: 'power2.out',
                            yoyo: true,
                            repeat: 1
                        });
                        
                        // Focus sur le champ suivant
                        if (index < digits.length - 1) {
                            digits[index + 1].focus();
                        }
                    } else {
                        this.classList.remove('filled');
                    }
                    
                    updateVerificationCode();
                    updateSubmitButton();
                });
                
                // Gestion du retour arrière
                digit.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        digits[index - 1].focus();
                        digits[index - 1].value = '';
                        digits[index - 1].classList.remove('filled');
                        updateVerificationCode();
                        updateSubmitButton();
                    }
                    
                    if (e.key === 'ArrowLeft' && index > 0) {
                        digits[index - 1].focus();
                    }
                    
                    if (e.key === 'ArrowRight' && index < digits.length - 1) {
                        digits[index + 1].focus();
                    }
                });
                
                // Coller un code complet
                digit.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const code = paste.replace(/[^0-9]/g, '').slice(0, 6);
                    
                    if (code.length === 6) {
                        fillCodeDigits(code);
                    }
                });
                
                // Sélection automatique du contenu au focus
                digit.addEventListener('focus', function() {
                    this.select();
                });
            });
        }
        
        /**
         * Remplit automatiquement les champs de code
         */
        function fillCodeDigits(code) {
            const digits = document.querySelectorAll('.code-digit');
            
            digits.forEach((digit, index) => {
                if (index < code.length) {
                    digit.value = code[index];
                    digit.classList.add('filled');
                    
                    // Animation de remplissage
                    gsap.from(digit, {
                        duration: 0.3,
                        scale: 1.2,
                        ease: 'back.out(1.7)',
                        delay: index * 0.05
                    });
                }
            });
            
            updateVerificationCode();
            updateSubmitButton();
        }
        
        /**
         * Met à jour le champ caché avec le code complet
         */
        function updateVerificationCode() {
            const digits = document.querySelectorAll('.code-digit');
            const code = Array.from(digits).map(digit => digit.value).join('');
            document.getElementById('verificationCode').value = code;
        }
        
        /**
         * Met à jour l'état du bouton de soumission
         */
        function updateSubmitButton() {
            const submitBtn = document.getElementById('verifyBtn');
            const code = document.getElementById('verificationCode').value;
            const backupInput = document.querySelector('.backup-code-input');
            
            let canSubmit = false;
            
            if (isBackupMode) {
                canSubmit = backupInput.value.trim().length >= 8;
            } else {
                canSubmit = code.length === 6;
            }
            
            submitBtn.disabled = !canSubmit || LOCKOUT_TIME;
            
            if (canSubmit && !LOCKOUT_TIME) {
                submitBtn.classList.add('btn-success');
                submitBtn.classList.remove('btn-disabled');
                
                // Animation de disponibilité
                if (!submitBtn.classList.contains('ready')) {
                    submitBtn.classList.add('ready');
                    gsap.to(submitBtn, {
                        duration: 0.3,
                        scale: 1.02,
                        ease: 'power2.out',
                        yoyo: true,
                        repeat: 1
                    });
                }
            } else {
                submitBtn.classList.remove('btn-success', 'ready');
                submitBtn.classList.add('btn-disabled');
            }
        }
        
        /**
         * Bascule vers le mode code de récupération
         */
        function toggleBackupCode() {
            isBackupMode = !isBackupMode;
            
            const normalInput = document.getElementById('normalCodeInput');
            const backupInput = document.getElementById('backupCodeInput');
            const submitBtn = document.getElementById('verifyBtn');
            
            if (isBackupMode) {
                // Passer en mode code de récupération
                gsap.to(normalInput, {
                    duration: 0.3,
                    opacity: 0,
                    y: -20,
                    ease: 'power2.out',
                    onComplete: () => {
                        normalInput.style.display = 'none';
                        backupInput.style.display = 'block';
                        
                        gsap.from(backupInput, {
                            duration: 0.3,
                            opacity: 0,
                            y: 20,
                            ease: 'power2.out'
                        });
                        
                        const backupInputField = backupInput.querySelector('.backup-code-input');
                        backupInputField.focus();
                        
                        // Écouter les changements
                        backupInputField.addEventListener('input', updateSubmitButton);
                    }
                });
                
                submitBtn.innerHTML = `
                    <span class="material-icons" style="font-size: 20px;">key</span>
                    Utiliser le code de récupération
                `;
                
            } else {
                // Retour au mode normal
                gsap.to(backupInput, {
                    duration: 0.3,
                    opacity: 0,
                    y: -20,
                    ease: 'power2.out',
                    onComplete: () => {
                        backupInput.style.display = 'none';
                        normalInput.style.display = 'block';
                        
                        gsap.from(normalInput, {
                            duration: 0.3,
                            opacity: 0,
                            y: 20,
                            ease: 'power2.out'
                        });
                        
                        const firstDigit = normalInput.querySelector('.code-digit');
                        firstDigit.focus();
                    }
                });
                
                submitBtn.innerHTML = `
                    <span class="material-icons" style="font-size: 20px;">verified_user</span>
                    Vérifier le code
                `;
            }
            
            updateSubmitButton();
        }
        
        /**
         * Initialise la soumission du formulaire
         */
        function initFormSubmission() {
            const form = document.getElementById('twofaForm');
            form.addEventListener('submit', handle2FASubmit);
        }
        
        /**
         * Gère la soumission du formulaire 2FA
         */
        async function handle2FASubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('verifyBtn');
            const formData = new FormData(form);
            
            // Validation
            if (!validate2FAForm(form)) {
                return;
            }
            
            // État de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                Vérification...
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
                    animate2FASuccess();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Authentification réussie !', {
                            duration: 3000
                        });
                    }
                    
                    // Redirection
                    setTimeout(() => {
                        window.location.href = result.redirect || '/dashboard';
                    }, 2000);
                    
                } else {
                    // Animation d'erreur
                    animate2FAError();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Code incorrect');
                    }
                    
                    // Vider les champs et remettre le focus
                    clearCodeInputs();
                    restoreSubmitButton();
                    
                    // Mettre à jour le compteur de tentatives si fourni
                    if (result.attempts_remaining !== undefined) {
                        updateAttemptsWarning(result.attempts_remaining);
                    }
                }
            } catch (error) {
                console.error('Erreur 2FA:', error);
                
                animate2FAError();
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau. Veuillez réessayer.');
                }
                
                restoreSubmitButton();
            }
        }
        
        /**
         * Valide le formulaire 2FA
         */
        function validate2FAForm(form) {
            if (isBackupMode) {
                const backupCode = form.querySelector('.backup-code-input').value.trim();
                if (!backupCode || backupCode.length < 8) {
                    if (typeof showAlert === 'function') {
                        showAlert('warning', 'Veuillez saisir un code de récupération valide');
                    }
                    return false;
                }
            } else {
                const code = document.getElementById('verificationCode').value;
                if (code.length !== 6) {
                    if (typeof showAlert === 'function') {
                        showAlert('warning', 'Veuillez saisir un code à 6 chiffres');
                    }
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Animation de succès 2FA
         */
        function animate2FASuccess() {
            const shield = document.querySelector('.security-shield');
            const digits = document.querySelectorAll('.code-digit');
            
            // Animation du bouclier
            gsap.to(shield, {
                duration: 0.5,
                scale: 1.2,
                ease: 'power2.out',
                yoyo: true,
                repeat: 1
            });
            
            // Animation des chiffres
            gsap.to(digits, {
                duration: 0.3,
                backgroundColor: 'var(--primary-green)',
                color: 'var(--text-white)',
                scale: 1.1,
                ease: 'power2.out',
                stagger: 0.05
            });
            
            // Animation de la carte
            gsap.to('.twofa-card', {
                duration: 0.4,
                scale: 1.02,
                ease: 'power2.out',
                yoyo: true,
                repeat: 1
            });
        }
        
        /**
         * Animation d'erreur 2FA
         */
        function animate2FAError() {
            const digits = document.querySelectorAll('.code-digit');
            
            // Animation shake des chiffres
            digits.forEach(digit => {
                digit.classList.add('error');
                setTimeout(() => {
                    digit.classList.remove('error');
                }, 500);
            });
            
            // Animation shake de la carte
            gsap.to('.twofa-card', {
                duration: 0.1,
                x: -10,
                ease: 'power2.inOut',
                repeat: 5,
                yoyo: true,
                onComplete: () => {
                    gsap.set('.twofa-card', { x: 0 });
                }
            });
        }
        
        /**
         * Vide les champs de saisie
         */
        function clearCodeInputs() {
            const digits = document.querySelectorAll('.code-digit');
            digits.forEach(digit => {
                digit.value = '';
                digit.classList.remove('filled');
            });
            
            const backupInput = document.querySelector('.backup-code-input');
            if (backupInput) {
                backupInput.value = '';
            }
            
            updateVerificationCode();
            updateSubmitButton();
            
            // Focus sur le premier champ
            if (!isBackupMode) {
                digits[0].focus();
            } else {
                backupInput.focus();
            }
        }
        
        /**
         * Restaure le bouton de soumission
         */
        function restoreSubmitButton() {
            const submitBtn = document.getElementById('verifyBtn');
            submitBtn.classList.remove('loading');
            
            if (isBackupMode) {
                submitBtn.innerHTML = `
                    <span class="material-icons" style="font-size: 20px;">key</span>
                    Utiliser le code de récupération
                `;
            } else {
                submitBtn.innerHTML = `
                    <span class="material-icons" style="font-size: 20px;">verified_user</span>
                    Vérifier le code
                `;
            }
            
            updateSubmitButton();
        }
        
        /**
         * Met à jour l'avertissement de tentatives
         */
        function updateAttemptsWarning(remaining) {
            const warningElement = document.querySelector('.attempts-warning');
            
            if (remaining <= 0) {
                location.reload(); // Recharger pour afficher le verrouillage
            } else if (remaining < 3) {
                if (warningElement) {
                    const span = warningElement.querySelector('span:last-child');
                    span.textContent = `Attention : ${remaining} tentative(s) restante(s)`;
                    
                    // Animation d'alerte
                    gsap.to(warningElement, {
                        duration: 0.2,
                        scale: 1.05,
                        ease: 'power2.out',
                        yoyo: true,
                        repeat: 3
                    });
                }
            }
        }
        
        /**
         * Renvoie un nouveau code
         */
        async function resendCode() {
            const resendBtn = document.getElementById('resendBtn');
            
            if (resendCooldown > 0) {
                return;
            }
            
            resendBtn.classList.add('disabled');
            resendBtn.innerHTML = `
                <span class="material-icons">hourglass_empty</span>
                Envoi en cours...
            `;
            
            try {
                const response = await fetch('/2fa/resend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        csrf_token: document.querySelector('input[name="csrf_token"]').value
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Nouveau code envoyé !');
                    }
                    
                    // Redémarrer le timer
                    codeTimer = CODE_VALIDITY_DURATION;
                    
                    // Cooldown de 60 secondes
                    startResendCooldown();
                    
                } else {
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur lors du renvoi');
                    }
                    
                    resendBtn.classList.remove('disabled');
                    resendBtn.innerHTML = `
                        <span class="material-icons">refresh</span>
                        Renvoyer le code
                    `;
                }
            } catch (error) {
                console.error('Erreur renvoi:', error);
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau');
                }
                
                resendBtn.classList.remove('disabled');
                resendBtn.innerHTML = `
                    <span class="material-icons">refresh</span>
                    Renvoyer le code
                `;
            }
        }
        
        /**
         * Démarre le cooldown de renvoi
         */
        function startResendCooldown() {
            const resendBtn = document.getElementById('resendBtn');
            resendCooldown = 60;
            
            const updateCooldown = () => {
                if (resendCooldown > 0) {
                    resendBtn.innerHTML = `
                        <span class="material-icons">timer</span>
                        Renvoyer (${resendCooldown}s)
                    `;
                    resendCooldown--;
                    setTimeout(updateCooldown, 1000);
                } else {
                    resendBtn.classList.remove('disabled');
                    resendBtn.innerHTML = `
                        <span class="material-icons">refresh</span>
                        Renvoyer le code
                    `;
                }
            };
            
            updateCooldown();
        }
        
        /**
         * Initialise les timers
         */
        function initTimers() {
            initCodeTimer();
            if (LOCKOUT_TIME) {
                initLockoutTimer();
            }
        }
        
        /**
         * Initialise le timer du code
         */
        function initCodeTimer() {
            const timerElement = document.getElementById('codeTimer');
            
            const updateCodeTimer = () => {
                timerElement.textContent = `${codeTimer}s`;
                
                if (codeTimer <= 10) {
                    timerElement.classList.add('critical');
                } else if (codeTimer <= 15) {
                    timerElement.classList.add('warning');
                }
                
                if (codeTimer <= 0) {
                    // Code expiré
                    if (typeof showAlert === 'function') {
                        showAlert('warning', 'Code expiré. Un nouveau code a été généré.', {
                            duration: 5000
                        });
                    }
                    codeTimer = CODE_VALIDITY_DURATION;
                    timerElement.classList.remove('warning', 'critical');
                    clearCodeInputs();
                } else {
                    codeTimer--;
                    setTimeout(updateCodeTimer, 1000);
                }
            };
            
            updateCodeTimer();
        }
        
        /**
         * Initialise le timer de verrouillage
         */
        function initLockoutTimer() {
            const timerElement = document.getElementById('lockoutTimer');
            if (!timerElement) return;
            
            const lockoutEnd = new Date(LOCKOUT_TIME).getTime();
            
            const updateLockoutTimer = () => {
                const now = new Date().getTime();
                const remaining = lockoutEnd - now;
                
                if (remaining <= 0) {
                    location.reload(); // Permettre une nouvelle tentative
                    return;
                }
                
                const minutes = Math.floor(remaining / (1000 * 60));
                const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
                
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                setTimeout(updateLockoutTimer, 1000);
            };
            
            updateLockoutTimer();
        }
    </script>
</body>
</html>