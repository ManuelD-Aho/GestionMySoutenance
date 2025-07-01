<?php
/**
 * Page d'inscription - GestionMySoutenance
 * Interface moderne pour la création de nouveaux comptes utilisateur
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Configuration de la page
$pageTitle = 'Inscription';
$pageSubtitle = 'Créez votre compte GestionMySoutenance';
$showLogo = true;

// Données du formulaire (depuis le contrôleur)
$csrf_token = $csrf_token ?? '';
$user_types = $user_types ?? ['etudiant' => 'Étudiant', 'enseignant' => 'Enseignant'];
$form_data = $form_data ?? [];
$error_message = $error_message ?? '';
$success_message = $success_message ?? '';
$registration_enabled = $registration_enabled ?? true;

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
    <meta name="description" content="Inscription à GestionMySoutenance - Créez votre compte pour accéder à la plateforme">
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
        /* Styles spécifiques à la page d'inscription */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 600px;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 2;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .registration-steps {
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
        
        .user-type-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .user-type-option {
            position: relative;
            cursor: pointer;
        }
        
        .user-type-card {
            padding: var(--spacing-lg);
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            background: rgba(255, 255, 255, 0.9);
            transition: all var(--transition-normal);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .user-type-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .user-type-card:hover::before {
            left: 100%;
        }
        
        .user-type-card:hover {
            border-color: var(--primary-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .user-type-card.selected {
            border-color: var(--primary-blue);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .user-type-icon {
            font-size: 48px;
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
            display: block;
        }
        
        .user-type-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .user-type-description {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.5;
        }
        
        .user-type-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
        }
        
        .form-group {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            display: block;
            font-weight: var(--font-weight-medium);
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
            font-size: var(--font-size-sm);
        }
        
        .form-label.required::after {
            content: ' *';
            color: var(--accent-red);
            font-weight: var(--font-weight-bold);
        }
        
        .form-input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) 50px;
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: var(--bg-primary);
            transform: translateY(-2px);
        }
        
        .form-input.valid {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        .form-input.invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 38px;
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }
        
        .form-input:focus + .input-icon {
            color: var(--primary-blue);
        }
        
        .password-strength {
            margin-top: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
        }
        
        .strength-meter {
            height: 6px;
            background: var(--border-light);
            border-radius: var(--border-radius-full);
            overflow: hidden;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all var(--transition-normal);
            border-radius: var(--border-radius-full);
        }
        
        .strength-text {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
        }
        
        .terms-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .terms-title {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-base);
        }
        
        .terms-content {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: var(--spacing-md);
        }
        
        .terms-agreement {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }
        
        .terms-checkbox {
            margin-top: 2px;
        }
        
        .terms-text {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.5;
        }
        
        .terms-text a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }
        
        .terms-text a:hover {
            text-decoration: underline;
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
        
        .login-link {
            text-align: center;
            padding: var(--spacing-lg);
            border-top: 1px solid var(--border-light);
            margin-top: var(--spacing-lg);
        }
        
        .login-link-text {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-sm);
        }
        
        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-fast);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }
        
        .login-link a:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        
        .registration-disabled {
            text-align: center;
            padding: var(--spacing-xl);
        }
        
        .disabled-icon {
            font-size: 64px;
            color: var(--text-light);
            margin-bottom: var(--spacing-lg);
        }
        
        .disabled-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-md);
        }
        
        .disabled-message {
            font-size: var(--font-size-base);
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: var(--spacing-lg);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                padding: var(--spacing-md);
            }
            
            .register-card {
                padding: var(--spacing-lg);
                max-width: 100%;
            }
            
            .user-type-selection {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .user-type-icon {
                font-size: 36px;
            }
        }
        
        @media (max-width: 480px) {
            .register-card {
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
            
            .user-type-card {
                padding: var(--spacing-md);
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Carte d'inscription -->
        <div class="register-card" id="registerCard">
            <!-- En-tête -->
            <?php include_once __DIR__ . '/components/auth-header.php'; ?>
            
            <?php if (!$registration_enabled): ?>
                <!-- Inscription désactivée -->
                <div class="registration-disabled">
                    <div class="disabled-icon">
                        <span class="material-icons" style="font-size: inherit;">app_registration</span>
                    </div>
                    <div class="disabled-title">Inscription temporairement fermée</div>
                    <div class="disabled-message">
                        Les inscriptions sont actuellement fermées. Veuillez contacter l'administration 
                        pour plus d'informations ou réessayer ultérieurement.
                    </div>
                    <div class="form-footer-links">
                        <a href="/login" class="link link-primary">
                            <span class="material-icons" style="font-size: 16px;" aria-hidden="true">login</span>
                            Se connecter
                        </a>
                        <a href="/" class="link link-secondary">
                            <span class="material-icons" style="font-size: 16px;" aria-hidden="true">home</span>
                            Accueil
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Indicateur d'étapes -->
                <div class="registration-steps">
                    <div class="step-indicator">
                        <div class="step active">
                            1
                            <div class="step-label">Informations</div>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            2
                            <div class="step-label">Vérification</div>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            3
                            <div class="step-label">Activation</div>
                        </div>
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
                
                <!-- Formulaire d'inscription -->
                <form class="register-form" id="registerForm" method="POST" action="/register" novalidate>
                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <!-- Sélection du type d'utilisateur -->
                    <div class="user-type-selection" id="userTypeSelection">
                        <?php foreach ($user_types as $type => $label): ?>
                        <div class="user-type-option">
                            <input type="radio" 
                                   id="user_type_<?= e($type) ?>" 
                                   name="user_type" 
                                   value="<?= e($type) ?>" 
                                   class="user-type-radio"
                                   <?= ($form_data['user_type'] ?? '') === $type ? 'checked' : '' ?>
                                   required>
                            <label for="user_type_<?= e($type) ?>" class="user-type-card">
                                <span class="user-type-icon material-icons">
                                    <?= $type === 'etudiant' ? 'school' : 'person' ?>
                                </span>
                                <div class="user-type-title"><?= e($label) ?></div>
                                <div class="user-type-description">
                                    <?php if ($type === 'etudiant'): ?>
                                        Accès aux soutenances, dépôt de rapports et suivi des évaluations
                                    <?php else: ?>
                                        Encadrement d'étudiants, évaluation de rapports et participation aux jurys
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Informations personnelles -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="prenom" class="form-label required">Prénom</label>
                            <input type="text" 
                                   id="prenom" 
                                   name="prenom" 
                                   value="<?= e($form_data['prenom'] ?? '') ?>"
                                   class="form-input"
                                   data-validate="required"
                                   autocomplete="given-name"
                                   required>
                            <span class="input-icon material-icons">person</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom" class="form-label required">Nom</label>
                            <input type="text" 
                                   id="nom" 
                                   name="nom" 
                                   value="<?= e($form_data['nom'] ?? '') ?>"
                                   class="form-input"
                                   data-validate="required"
                                   autocomplete="family-name"
                                   required>
                            <span class="input-icon material-icons">badge</span>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="email_principal" class="form-label required">Adresse email</label>
                            <input type="email" 
                                   id="email_principal" 
                                   name="email_principal" 
                                   value="<?= e($form_data['email_principal'] ?? '') ?>"
                                   class="form-input"
                                   data-validate="required email"
                                   autocomplete="email"
                                   required>
                            <span class="input-icon material-icons">email</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_telephone" class="form-label">Téléphone</label>
                            <input type="tel" 
                                   id="numero_telephone" 
                                   name="numero_telephone" 
                                   value="<?= e($form_data['numero_telephone'] ?? '') ?>"
                                   class="form-input"
                                   autocomplete="tel">
                            <span class="input-icon material-icons">phone</span>
                        </div>
                        
                        <div class="form-group" id="studentFields" style="display: none;">
                            <label for="numero_carte_etudiant" class="form-label">Numéro étudiant</label>
                            <input type="text" 
                                   id="numero_carte_etudiant" 
                                   name="numero_carte_etudiant" 
                                   value="<?= e($form_data['numero_carte_etudiant'] ?? '') ?>"
                                   class="form-input"
                                   placeholder="Ex: 20190001">
                            <span class="input-icon material-icons">credit_card</span>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="mot_de_passe" class="form-label required">Mot de passe</label>
                            <input type="password" 
                                   id="mot_de_passe" 
                                   name="mot_de_passe" 
                                   class="form-input"
                                   data-validate="required password"
                                   autocomplete="new-password"
                                   required>
                            <span class="input-icon material-icons">lock</span>
                            
                            <!-- Barre de force du mot de passe -->
                            <div class="password-strength" id="passwordStrength" style="display: none;">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text">
                                    <span id="strengthLevel">Faible</span>
                                    <span id="strengthScore">0/100</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="confirmer_mot_de_passe" class="form-label required">Confirmer le mot de passe</label>
                            <input type="password" 
                                   id="confirmer_mot_de_passe" 
                                   name="confirmer_mot_de_passe" 
                                   class="form-input"
                                   data-validate="required password-confirm"
                                   autocomplete="new-password"
                                   required>
                            <span class="input-icon material-icons">lock_outline</span>
                        </div>
                    </div>
                    
                    <!-- Conditions d'utilisation -->
                    <div class="terms-section">
                        <div class="terms-title">
                            <span class="material-icons" aria-hidden="true">gavel</span>
                            Conditions d'utilisation
                        </div>
                        <div class="terms-content">
                            En créant un compte, vous acceptez nos conditions d'utilisation et notre politique de confidentialité. 
                            Votre compte sera soumis à validation par l'administration avant activation.
                        </div>
                        <div class="terms-agreement">
                            <input type="checkbox" 
                                   id="accept_terms" 
                                   name="accept_terms" 
                                   class="terms-checkbox checkbox checkbox-primary"
                                   required>
                            <label for="accept_terms" class="terms-text">
                                J'accepte les 
                                <a href="/legal/terms" target="_blank">conditions d'utilisation</a> et la 
                                <a href="/legal/privacy" target="_blank">politique de confidentialité</a>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Bouton de soumission -->
                    <button type="submit" class="submit-btn btn btn-primary" id="submitBtn" disabled>
                        <span class="material-icons" style="font-size: 20px;" aria-hidden="true">person_add</span>
                        Créer le compte
                    </button>
                </form>
                
                <!-- Lien de connexion -->
                <div class="login-link">
                    <div class="login-link-text">Vous avez déjà un compte ?</div>
                    <a href="/login">
                        <span class="material-icons" style="font-size: 16px;" aria-hidden="true">login</span>
                        Se connecter
                    </a>
                </div>
            <?php endif; ?>
            
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
        // Variables globales
        let selectedUserType = '<?= e($form_data['user_type'] ?? '') ?>';
        let passwordStrength = 0;
        let formValid = false;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation GSAP d'entrée
            initAnimations();
            
            // Initialisation des fonctionnalités
            initUserTypeSelection();
            initPasswordValidation();
            initFormValidation();
            initTermsAgreement();
            
            // Focus automatique sur le premier champ
            const firstInput = document.getElementById('prenom');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        });
        
        /**
         * Initialise les animations d'entrée
         */
        function initAnimations() {
            gsap.timeline()
                .to('.register-card', {
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
                .from('.user-type-card', {
                    duration: 0.6,
                    y: 30,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.1
                }, '-=0.3')
                .from('.form-group', {
                    duration: 0.5,
                    y: 20,
                    opacity: 0,
                    ease: 'power2.out',
                    stagger: 0.05
                }, '-=0.2');
        }
        
        /**
         * Initialise la sélection du type d'utilisateur
         */
        function initUserTypeSelection() {
            const userTypeRadios = document.querySelectorAll('.user-type-radio');
            const userTypeCards = document.querySelectorAll('.user-type-card');
            
            userTypeRadios.forEach((radio, index) => {
                radio.addEventListener('change', function() {
                    selectedUserType = this.value;
                    updateUserTypeSelection();
                    toggleStudentFields();
                    updateSubmitButton();
                });
            });
            
            // Clic sur les cartes
            userTypeCards.forEach((card, index) => {
                card.addEventListener('click', function() {
                    const radio = userTypeRadios[index];
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change'));
                    }
                });
            });
            
            // Initialiser l'état si un type est déjà sélectionné
            if (selectedUserType) {
                updateUserTypeSelection();
                toggleStudentFields();
            }
        }
        
        /**
         * Met à jour l'affichage de la sélection du type d'utilisateur
         */
        function updateUserTypeSelection() {
            const userTypeCards = document.querySelectorAll('.user-type-card');
            
            userTypeCards.forEach((card, index) => {
                const radio = document.querySelectorAll('.user-type-radio')[index];
                
                if (radio && radio.checked) {
                    card.classList.add('selected');
                    
                    // Animation de sélection
                    gsap.to(card, {
                        duration: 0.3,
                        scale: 1.02,
                        ease: 'power2.out',
                        yoyo: true,
                        repeat: 1
                    });
                } else {
                    card.classList.remove('selected');
                }
            });
        }
        
        /**
         * Affiche/cache les champs spécifiques aux étudiants
         */
        function toggleStudentFields() {
            const studentFields = document.getElementById('studentFields');
            const studentIdInput = document.getElementById('numero_carte_etudiant');
            
            if (selectedUserType === 'etudiant') {
                studentFields.style.display = 'block';
                studentIdInput.setAttribute('data-validate', 'required');
                studentIdInput.required = true;
                
                // Animation d'apparition
                gsap.from(studentFields, {
                    duration: 0.4,
                    height: 0,
                    opacity: 0,
                    ease: 'power2.out'
                });
            } else {
                studentFields.style.display = 'none';
                studentIdInput.removeAttribute('data-validate');
                studentIdInput.required = false;
            }
        }
        
        /**
         * Initialise la validation du mot de passe
         */
        function initPasswordValidation() {
            const passwordInput = document.getElementById('mot_de_passe');
            const strengthContainer = document.getElementById('passwordStrength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                if (password) {
                    strengthContainer.style.display = 'block';
                    updatePasswordStrength(password);
                } else {
                    strengthContainer.style.display = 'none';
                }
                
                updateSubmitButton();
            });
        }
        
        /**
         * Met à jour l'indicateur de force du mot de passe
         */
        function updatePasswordStrength(password) {
            let score = 0;
            let level = 'Très faible';
            
            // Calcul du score
            if (password.length >= 8) score += 25;
            if (/[a-z]/.test(password)) score += 15;
            if (/[A-Z]/.test(password)) score += 15;
            if (/[0-9]/.test(password)) score += 15;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score += 20;
            if (password.length >= 12) score += 10;
            
            // Déterminer le niveau
            if (score >= 90) level = 'Excellent';
            else if (score >= 70) level = 'Très fort';
            else if (score >= 50) level = 'Fort';
            else if (score >= 30) level = 'Moyen';
            else if (score >= 15) level = 'Faible';
            
            passwordStrength = score;
            
            // Mettre à jour l'affichage
            const fill = document.getElementById('strengthFill');
            const levelElement = document.getElementById('strengthLevel');
            const scoreElement = document.getElementById('strengthScore');
            
            // Animation de la barre
            gsap.to(fill, {
                duration: 0.5,
                width: `${score}%`,
                ease: 'power2.out'
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
        }
        
        /**
         * Initialise la validation du formulaire
         */
        function initFormValidation() {
            if (typeof AuthValidation !== 'undefined') {
                AuthValidation.initFormValidation('#registerForm');
            }
            
            // Écouter les changements dans les champs
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', updateSubmitButton);
                input.addEventListener('change', updateSubmitButton);
            });
        }
        
        /**
         * Initialise l'accord des conditions d'utilisation
         */
        function initTermsAgreement() {
            const termsCheckbox = document.getElementById('accept_terms');
            
            termsCheckbox.addEventListener('change', function() {
                updateSubmitButton();
                
                if (this.checked) {
                    // Animation de validation
                    gsap.to(this.parentNode.parentNode, {
                        duration: 0.3,
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        ease: 'power2.out'
                    });
                } else {
                    gsap.to(this.parentNode.parentNode, {
                        duration: 0.3,
                        backgroundColor: 'transparent',
                        ease: 'power2.out'
                    });
                }
            });
        }
        
        /**
         * Met à jour l'état du bouton de soumission
         */
        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('registerForm');
            
            // Vérifications de base
            const userTypeSelected = selectedUserType !== '';
            const requiredFieldsFilled = validateRequiredFields();
            const termsAccepted = document.getElementById('accept_terms').checked;
            const passwordsMatch = checkPasswordsMatch();
            const minPasswordStrength = passwordStrength >= 30;
            
            formValid = userTypeSelected && 
                       requiredFieldsFilled && 
                       termsAccepted && 
                       passwordsMatch && 
                       minPasswordStrength;
            
            submitBtn.disabled = !formValid;
            
            if (formValid) {
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
         * Valide que tous les champs requis sont remplis
         */
        function validateRequiredFields() {
            const requiredInputs = document.querySelectorAll('input[required]');
            
            for (const input of requiredInputs) {
                if (!input.value.trim()) {
                    return false;
                }
                
                // Validation spéciale pour l'email
                if (input.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Vérifie que les mots de passe correspondent
         */
        function checkPasswordsMatch() {
            const password = document.getElementById('mot_de_passe').value;
            const confirm = document.getElementById('confirmer_mot_de_passe').value;
            
            return password && confirm && password === confirm;
        }
        
        /**
         * Gère la soumission du formulaire
         */
        document.getElementById('registerForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            if (!formValid) {
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'Veuillez remplir correctement tous les champs requis');
                }
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(this);
            
            // État de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                Création du compte...
            `;
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Animation de succès
                    animateRegistrationSuccess();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('success', result.message || 'Compte créé avec succès !', {
                            duration: 8000,
                            actions: [{
                                text: 'Aller à la connexion',
                                type: 'primary',
                                onclick: 'window.location.href="/login"'
                            }]
                        });
                    }
                    
                    // Mettre à jour l'indicateur d'étapes
                    updateStepIndicator(2);
                    
                    // Redirection après 5 secondes
                    setTimeout(() => {
                        window.location.href = '/login?message=' + encodeURIComponent('Compte créé. Vérifiez votre email pour l\'activation.');
                    }, 5000);
                    
                } else {
                    // Animation d'erreur
                    animateRegistrationError();
                    
                    if (typeof showAlert === 'function') {
                        showAlert('error', result.message || 'Erreur lors de la création du compte');
                    }
                    
                    restoreSubmitButton();
                }
            } catch (error) {
                console.error('Erreur inscription:', error);
                
                animateRegistrationError();
                
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Erreur de réseau. Veuillez réessayer.');
                }
                
                restoreSubmitButton();
            }
        });
        
        /**
         * Animation de succès de l'inscription
         */
        function animateRegistrationSuccess() {
            gsap.timeline()
                .to('.register-card', {
                    duration: 0.4,
                    scale: 1.02,
                    ease: 'power2.out'
                })
                .to('.register-card', {
                    duration: 0.4,
                    scale: 1,
                    ease: 'power2.out'
                })
                .to('.user-type-card.selected', {
                    duration: 0.5,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    ease: 'power2.out'
                }, '-=0.4');
        }
        
        /**
         * Animation d'erreur de l'inscription
         */
        function animateRegistrationError() {
            gsap.to('.register-card', {
                duration: 0.1,
                x: -10,
                ease: 'power2.inOut',
                repeat: 5,
                yoyo: true,
                onComplete: () => {
                    gsap.set('.register-card', { x: 0 });
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
            updateSubmitButton();
            submitBtn.innerHTML = `
                <span class="material-icons" style="font-size: 20px;">person_add</span>
                Créer le compte
            `;
        }
    </script>
</body>
</html>