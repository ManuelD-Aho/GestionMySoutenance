<?php
/**
 * Page de vérification d'email - GestionMySoutenance
 * Interface moderne pour la confirmation d'adresse email
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Vérification d\'email';
$pageSubtitle = 'Confirmez votre adresse email pour activer votre compte';
$showLogo = true;

// Données de la page (depuis le contrôleur)
$verification_status = $verification_status ?? 'pending'; // pending, success, expired, invalid
$user_email = $user_email ?? '';
$user_name = $user_name ?? '';
$token = $token ?? '';
$resend_available = $resend_available ?? true;
$resend_cooldown = $resend_cooldown ?? 0;
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';

// Messages selon le statut
$status_messages = [
    'pending' => [
        'title' => 'Vérification d\'email requise',
        'message' => 'Un email de vérification a été envoyé à votre adresse. Cliquez sur le lien pour activer votre compte.',
        'icon' => 'mark_email_unread',
        'type' => 'info'
    ],
    'success' => [
        'title' => 'Email vérifié avec succès !',
        'message' => 'Votre adresse email a été confirmée. Votre compte est maintenant activé.',
        'icon' => 'verified',
        'type' => 'success'
    ],
    'expired' => [
        'title' => 'Lien de vérification expiré',
        'message' => 'Le lien de vérification a expiré. Un nouveau lien peut être envoyé.',
        'icon' => 'schedule',
        'type' => 'warning'
    ],
    'invalid' => [
        'title' => 'Lien de vérification invalide',
        'message' => 'Le lien de vérification est invalide ou a déjà été utilisé.',
        'icon' => 'error_outline',
        'type' => 'error'
    ]
];

$current_status = $status_messages[$verification_status] ?? $status_messages['pending'];

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
    <meta name="description" content="Vérification d'email pour GestionMySoutenance - Confirmez votre adresse email">
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
        /* Styles spécifiques à la page de vérification d'email */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .verification-card {
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
            text-align: center;
        }
        
        .status-indicator {
            margin-bottom: var(--spacing-xl);
        }
        
        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
            font-size: 48px;
            color: var(--text-white);
            position: relative;
            overflow: hidden;
        }
        
        .status-icon.success {
            background: linear-gradient(135deg, var(--primary-green) 0%, #059669 100%);
            animation: successPulse 2s ease-in-out infinite;
        }
        
        .status-icon.error {
            background: linear-gradient(135deg, var(--accent-red) 0%, #dc2626 100%);
            animation: errorShake 0.5s ease-in-out;
        }
        
        .status-icon.warning {
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #d97706 100%);
            animation: warningBlink 1.5s ease-in-out infinite;
        }
        
        .status-icon.info {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1d4ed8 100%);
            animation: infoPulse 2s ease-in-out infinite;
        }
        
        .status-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: var(--border-radius-full);
            background: inherit;
            opacity: 0.3;
            transform: scale(1.2);
            animation: ripple 2s ease-out infinite;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes warningBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes infoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        @keyframes ripple {
            0% { transform: scale(1.2); opacity: 0.3; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        
        .status-title {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            line-height: 1.2;
        }
        
        .status-title.success {
            color: var(--primary-green-dark);
        }
        
        .status-title.error {
            color: var(--accent-red-dark);
        }
        
        .status-title.warning {
            color: var(--accent-yellow-dark);
        }
        
        .status-title.info {
            color: var(--primary-blue-dark);
        }
        
        .status-message {
            font-size: var(--font-size-base);
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: var(--spacing-xl);
        }
        
        .user-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .user-info-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-base);
        }
        
        .user-email {
            font-family: monospace;
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-bold);
            color: var(--primary-blue);
            background: rgba(59, 130, 246, 0.1);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-sm);
        }
        
        .user-name {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            text-decoration: none;
            transition: all var(--transition-normal);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .action-btn.primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-green) 100%);
            color: var(--text-white);
        }
        
        .action-btn.primary:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }
        
        .action-btn.secondary {
            background: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
        }
        
        .action-btn.secondary:hover:not(:disabled) {
            background: var(--primary-blue);
            color: var(--text-white);
            transform: translateY(-2px);
        }
        
        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .resend-info {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border: 1px solid var(--accent-yellow);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            font-size: var(--font-size-sm);
            color: var(--accent-yellow-dark);
        }
        
        .resend-timer {
            font-family: monospace;
            font-weight: var(--font-weight-bold);
            color: var(--accent-red);
        }
        
        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .progress-step {
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
        
        .progress-step.completed {
            background: var(--primary-green);
            color: var(--text-white);
            transform: scale(1.1);
        }
        
        .progress-step.active {
            background: var(--primary-blue);
            color: var(--text-white);
            transform: scale(1.1);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .progress-connector {
            width: 30px;
            height: 2px;
            background: var(--border-light);
            transition: background var(--transition-fast);
        }
        
        .progress-connector.active {
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
        
        .email-tips {
            background: rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }
        
        .email-tips-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-md);
        }
        
        .email-tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .email-tip {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-sm);
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-sm);
            line-height: 1.5;
        }
        
        .email-tip:last-child {
            margin-bottom: 0;
        }
        
        .tip-icon {
            color: var(--primary-blue);
            font-size: 16px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .contact-support {
            text-align: center;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-light);
        }
        
        .contact-support-text {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-sm);
        }
        
        .contact-support a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-fast);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }
        
        .contact-support a:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .verification-container {
                padding: var(--spacing-md);
            }
            
            .verification-card {
                padding: var(--spacing-lg);
                max-width: 100%;
            }
            
            .status-icon {
                width: 80px;
                height: 80px;
                font-size: 36px;
            }
            
            .status-title {
                font-size: var(--font-size-xl);
            }
        }
        
        @media (max-width: 480px) {
            .verification-card {
                padding: var(--spacing-md);
            }
            
            .action-buttons {
                gap: var(--spacing-sm);
            }
            
            .progress-steps {
                gap: var(--spacing-sm);
            }
            
            .progress-step {
                width: 35px;
                height: 35px;
                font-size: var(--font-size-xs);
            }
            
            .progress-connector {
                width: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <!-- Carte de vérification -->
        <div class="verification-card" id="verificationCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <!-- Étapes de progression -->
            <div class="progress-steps">
                <div class="progress-step completed">
                    <span class="material-icons">check</span>
                    <div class="step-label">Inscription</div>
                </div>
                <div class="progress-connector active"></div>
                <div class="progress-step <?= $verification_status === 'success' ? 'completed' : 'active' ?>">
                    <?php if ($verification_status === 'success'): ?>
                        <span class="material-icons">check</span>
                    <?php else: ?>
                        2
                    <?php endif; ?>
                    <div class="step-label">Vérification</div>
                </div>
                <div class="progress-connector <?= $verification_status === 'success' ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $verification_status === 'success' ? 'active' : '' ?>">
                    3
                    <div class="step-label">Activation</div>
                </div>
            </div>
            
            <!-- Indicateur de statut -->
            <div class="status-indicator">
                <div class="status-icon <?= e($current_status['type']) ?>">
                    <span class="material-icons"><?= e($current_status['icon']) ?></span>
                </div>
                <h1 class="status-title <?= e($current_status['type']) ?>">
                    <?= e($current_status['title']) ?>
                </h1>
                <p class="status-message">
                    <?= e($current_status['message']) ?>
                </p>
            </div>
            
            <!-- Informations utilisateur -->
            <?php if ($user_email): ?>
            <div class="user-info">
                <div class="user-info-title">
                    <span class="material-icons" aria-hidden="true">account_circle</span>
                    Compte utilisateur
                </div>
                <div class="user-email"><?= e($user_email) ?></div>
                <?php if ($user_name): ?>
                <div class="user-name"><?= e($user_name) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Messages d'alerte supplémentaires -->
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
            
            <!-- Informations de renvoi d'email -->
            <?php if (($verification_status === 'pending' || $verification_status === 'expired') && $resend_cooldown > 0): ?>
            <div class="resend-info">
                <div style="display: flex; align-items: center; justify-content: center; gap: var(--spacing-xs); margin-bottom: var(--spacing-xs);">
                    <span class="material-icons" aria-hidden="true">timer</span>
                    <span>Nouveau renvoi possible dans :</span>
                </div>
                <div class="resend-timer" id="resendTimer"><?= e($resend_cooldown) ?>s</div>
            </div>
            <?php endif; ?>
            
            <!-- Boutons d'action -->
            <div class="action-buttons">
                <?php if ($verification_status === 'success'): ?>
                    <!-- Compte vérifié avec succès -->
                    <a href="/login" class="action-btn primary">
                        <span class="material-icons" aria-hidden="true">login</span>
                        Se connecter maintenant
                    </a>
                    <a href="/dashboard" class="action-btn secondary">
                        <span class="material-icons" aria-hidden="true">dashboard</span>
                        Accéder au tableau de bord
                    </a>
                
                <?php elseif ($verification_status === 'pending'): ?>
                    <!-- En attente de vérification -->
                    <button type="button" 
                            class="action-btn primary" 
                            onclick="checkEmailClient()"
                            id="checkEmailBtn">
                        <span class="material-icons" aria-hidden="true">email</span>
                        Ouvrir ma messagerie
                    </button>
                    
                    <button type="button" 
                            class="action-btn secondary" 
                            onclick="resendVerificationEmail()"
                            id="resendBtn"
                            <?= !$resend_available || $resend_cooldown > 0 ? 'disabled' : '' ?>>
                        <span class="material-icons" aria-hidden="true">refresh</span>
                        Renvoyer l'email
                    </button>
                
                <?php elseif ($verification_status === 'expired' || $verification_status === 'invalid'): ?>
                    <!-- Lien expiré ou invalide -->
                    <button type="button" 
                            class="action-btn primary" 
                            onclick="resendVerificationEmail()"
                            id="resendBtn"
                            <?= !$resend_available || $resend_cooldown > 0 ? 'disabled' : '' ?>>
                        <span class="material-icons" aria-hidden="true">send</span>
                        Envoyer un nouveau lien
                    </button>
                    
                    <a href="/login" class="action-btn secondary">
                        <span class="material-icons" aria-hidden="true">login</span>
                        Retour à la connexion
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Conseils pour retrouver l'email -->
            <?php if ($verification_status !== 'success'): ?>
            <div class="email-tips">
                <div class="email-tips-title">
                    <span class="material-icons" aria-hidden="true">lightbulb</span>
                    Vous ne trouvez pas l'email ?
                </div>
                <ul class="email-tips-list">
                    <li class="email-tip">
                        <span class="tip-icon material-icons">search</span>
                        <span>Vérifiez votre dossier de courriers indésirables (spam)</span>
                    </li>
                    <li class="email-tip">
                        <span class="tip-icon material-icons">schedule</span>
                        <span>L'email peut prendre quelques minutes à arriver</span>
                    </li>
                    <li class="email-tip">
                        <span class="tip-icon material-icons">filter_alt</span>
                        <span>Recherchez "GestionMySoutenance" dans votre boîte mail</span>
                    </li>
                    <li class="email-tip">
                        <span class="tip-icon material-icons">contact_mail</span>
                        <span>Ajoutez noreply@gestionsoutenance.ufhb.edu.ci à vos contacts</span>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Contact support -->
            <div class="contact-support">
                <div class="contact-support-text">
                    Problème persistant ?
                </div>
                <a href="/support/contact">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">support_agent</span>
                    Contacter le support technique
                </a>
            </div>
            
            <!-- Navigation -->
            <div class="form-footer-links" style="margin-top: var(--spacing-xl);">
                <a href="/" class="link link-secondary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">home</span>
                    Retour à l'accueil
                </a>
                <?php if ($verification_status !== 'success'): ?>
                <a href="/register" class="link link-primary">
                    <span class="material-icons" style="font-size: 16px;" aria-hidden="true">person_add</span>
                    Nouvelle inscription
                </a>
                <?php endif; ?>
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
        const VERIFICATION_STATUS = '<?= e($verification_status) ?>';
        const RESEND_COOLDOWN_INITIAL = <?= (int)$resend_cooldown ?>;
        
        // Variables globales
        let resendCooldown = RESEND_COOLDOWN_INITIAL;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            initAnimations();
            
            // Initialisation des fonctionnalités
            initResendTimer();
            initStatusSpecificFeatures();
            
            // Vérification automatique du statut (si en attente)
            if (VERIFICATION_STATUS === 'pending') {
                startStatusPolling();
            }
        });
        
        /**
         * Initialise les animations d'entrée
         */
        function initAnimations() {
            gsap.timeline()
                .to('.verification-card', {
                    duration: 0.8,
                    y: 0,
                    opacity: 1,
                    ease: 'power3.out'
                })
                .from('.progress-step', {
                    duration: 0.5,
                    scale: 0,
                    ease: 'back.out(1.7)',
                    stagger: 0.1
                }, '-=0.5')
                .from('.status-icon', {
                    duration: 0.8,
                    scale: 0,
                    rotation: -180,
                    ease: 'back.out(1.7)'
                }, '-=0.3')
                .from('.status-title, .status-message', {
                    duration: 0.6,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.2')
                .from('.action-btn', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.1');
        }
        
        /**
         * Initialise le timer de renvoi d'email
         */
        function initResendTimer() {
            if (resendCooldown <= 0) return;
            
            const timerElement = document.getElementById('resendTimer');
            const resendBtn = document.getElementById('resendBtn');
            
            if (!timerElement || !resendBtn) return;
            
            const updateTimer = () => {
                if (resendCooldown > 0) {
                    timerElement.textContent = `${resendCooldown}s`;
                    resendCooldown--;
                    setTimeout(updateTimer, 1000);
                } else {
                    // Activer le bouton de renvoi
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = `
                        <span class="material-icons">refresh</span>
                        Renvoyer l'email
                    `;
                    
                    // Masquer le timer
                    const resendInfo = timerElement.closest('.resend-info');
                    if (resendInfo) {
                        gsap.to(resendInfo, {
                            duration: 0.5,
                            opacity: 0,
                            height: 0,
                            marginBottom: 0,
                            ease: 'power2.out',
                            onComplete: () => {
                                resendInfo.style.display = 'none';
                            }
                        });
                    }
                }
            };
            
            updateTimer();
        }
        
        /**
         * Initialise les fonctionnalités spécifiques au statut
         */
        function initStatusSpecificFeatures() {
            switch (VERIFICATION_STATUS) {
                case 'success':
                    // Animation de confettis pour le succès
                    createSuccessConfetti();
                    
                    // Focus automatique sur le bouton de connexion
                    const loginBtn = document.querySelector('.action-btn.primary');
                    if (loginBtn) {
                        setTimeout(() => loginBtn.focus(), 500);
                    }
                    break;
                    
                case 'pending':
                    // Animation de pulse pour l'icône d'info
                    const statusIcon = document.querySelector('.status-icon.info');
                    if (statusIcon) {
                        gsap.to(statusIcon, {
                            duration: 2,
                            scale: 1.05,
                            ease: 'power2.inOut',
                            yoyo: true,
                            repeat: -1
                        });
                    }
                    break;
                    
                case 'expired':
                case 'invalid':
                    // Animation d'attention pour les erreurs
                    const errorIcon = document.querySelector('.status-icon.warning, .status-icon.error');
                    if (errorIcon) {
                        gsap.to(errorIcon, {
                            duration: 0.5,
                            rotation: 5,
                            ease: 'power2.inOut',
                            yoyo: true,
                            repeat: 3
                        });
                    }
                    break;
            }
        }
        
        /**
         * Démarre la vérification périodique du statut (pour auto-rafraîchir)
         */
        function startStatusPolling() {
            // Vérifier le statut toutes les 30 secondes
            setInterval(async () => {
                try {
                    const response = await fetch('/api/verification-status', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.status === 'verified') {
                        // L'email a été vérifié, recharger la page
                        window.location.reload();
                    }
                } catch (error) {
                    // Ignorer les erreurs de vérification automatique
                    console.log('Status polling error:', error);
                }
            }, 30000);
        }
        
        /**
         * Ouvre le client email de l'utilisateur
         */
        function checkEmailClient() {
            // Essayer d'ouvrir le client email par défaut
            window.open('mailto:', '_self');
            
            // Animation de feedback
            const btn = document.getElementById('checkEmailBtn');
            gsap.to(btn, {
                duration: 0.2,
                scale: 0.95,
                ease: 'power2.out',
                yoyo: true,
                repeat: 1
            });
            
            // Message d'information
            if (typeof showAlert === 'function') {
                showAlert('info', 'Ouverture de votre application de messagerie...', {
                    duration: 3000
                });
            }
        }
        
        /**
         * Renvoie l'email de vérification
         */
        async function resendVerificationEmail() {
            const resendBtn = document.getElementById('resendBtn');
            
            if (resendBtn.disabled) return;
            
            // État de chargement
            resendBtn.disabled = true;
            resendBtn.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                Envoi en cours...
            `;
            
            try {
                const response = await fetch('/resend-verification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        email: '<?= e($user_email) ?>',
                        csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Animation de succès
                    gsap.to('.verification-card', {
                        duration: 0.3,
                        scale: 1.02,
                        ease: 'power2.out',
                        yoyo: true,
                        repeat: 1
                    });
                    
                    if (typeof showAlert === 'function') {
                        showAlert('success', result.message || 'Email de vérification renvoyé !', {
                            duration: 5000
                        });
                    }
                    
                    // Redémarrer le cooldown
                    resendCooldown = 60; // 60 secondes
                    initResendTimer();
                    
                    // Afficher le timer
                    const resendInfo = document.querySelector('.resend-info');
                    if (resendInfo) {
                        resendInfo.style.display = 'block';
                        resendInfo.innerHTML = `
                            <div style="display: flex; align-items: center; justify-content: center; gap: var(--spacing-xs); margin-bottom: var(--spacing-xs);">
                                <span class="material-icons">timer</span>
                                <span>Nouveau renvoi possible dans :</span>
                            </div>
                            <div class="resend-timer" id="resendTimer">${resendCooldown}s</div>
                        `;
                        
                        gsap.from(resendInfo, {
                            duration: 0.5,
                            opacity: 0,
                            height: 0,
                            ease: 'power2.out'
                        });
                    }
                    
                } else {
                    // Animation d'erreur
                    gsap.to(resendBtn, {
                        duration: 0.1,
                        x: -5,
                        ease: 'power2.inOut',
                        repeat: 5,
                        yoyo: true,
                        onComplete: () => {
                            gsap.set(resendBtn, { x: 0 });
                        }
                    });
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur lors du renvoi');
                    }
                    
                    // Restaurer le bouton
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = `
                        <span class="material-icons">refresh</span>
                        Renvoyer l'email
                    `;
                }
            } catch (error) {
                console.error('Erreur renvoi:', error);
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau. Veuillez réessayer.');
                }
                
                // Restaurer le bouton
                resendBtn.disabled = false;
                resendBtn.innerHTML = `
                    <span class="material-icons">refresh</span>
                    Renvoyer l'email
                `;
            }
        }
        
        /**
         * Crée des confettis pour célébrer le succès
         */
        function createSuccessConfetti() {
            const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: fixed;
                    width: 10px;
                    height: 10px;
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 9999;
                    left: 50%;
                    top: 20%;
                `;
                
                document.body.appendChild(confetti);
                
                // Animation du confetti
                gsap.to(confetti, {
                    duration: 3 + Math.random() * 2,
                    x: (Math.random() - 0.5) * window.innerWidth,
                    y: window.innerHeight,
                    rotation: Math.random() * 360,
                    opacity: 0,
                    ease: 'power2.out',
                    onComplete: () => {
                        document.body.removeChild(confetti);
                    }
                });
            }
        }
        
        // Gestion du raccourci clavier pour l'email
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                checkEmailClient();
            }
        });
    </script>
</body>
</html>