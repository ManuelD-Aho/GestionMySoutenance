/**
 * auth-validation.js - Validation côté client pour les pages d'authentification
 * GestionMySoutenance - Système de validation moderne avec feedback visuel
 */

// ===== CONFIGURATION GLOBALE =====
const VALIDATION_CONFIG = {
    // Règles de mot de passe
    password: {
        minLength: 8,
        maxLength: 128,
        requireUppercase: true,
        requireLowercase: true,
        requireNumbers: true,
        requireSpecialChars: true,
        specialChars: '!@#$%^&*()_+-=[]{}|;:,.<>?',
        forbiddenPatterns: [
            /(.)\1{3,}/, // 4 caractères identiques consécutifs
            /1234|abcd|qwer/i, // Séquences communes
            /password|motdepasse|admin|user/i // Mots interdits
        ]
    },
    
    // Règles d'email
    email: {
        pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        domains: {
            academic: [
                'ufhb.edu.ci', 'univ-abidjan.ci', 'edu.ci',
                'ensea.edu.ci', 'inphb.edu.ci'
            ],
            common: [
                'gmail.com', 'outlook.com', 'hotmail.com',
                'yahoo.fr', 'yahoo.com', 'live.com'
            ]
        },
        maxLength: 254,
        minLength: 5
    },
    
    // Codes 2FA
    twofa: {
        codeLength: 6,
        pattern: /^\d{6}$/,
        backupCodePattern: /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/
    },
    
    // Délais de validation
    delays: {
        realtime: 300, // ms pour validation en temps réel
        debounce: 500, // ms pour debounce des requêtes
        feedback: 2000 // ms pour maintenir le feedback visuel
    }
};

// ===== UTILITAIRES DE VALIDATION =====

/**
 * Classe principale de validation
 */
class AuthValidator {
    constructor(options = {}) {
        this.config = { ...VALIDATION_CONFIG, ...options };
        this.validators = new Map();
        this.debounceTimers = new Map();
        this.init();
    }
    
    /**
     * Initialise le validateur
     */
    init() {
        this.setupDefaultValidators();
        this.bindEvents();
    }
    
    /**
     * Configure les validateurs par défaut
     */
    setupDefaultValidators() {
        // Validateur d'email
        this.addValidator('email', {
            validate: (value) => this.validateEmail(value),
            message: 'Adresse email invalide',
            realtime: true
        });
        
        // Validateur de mot de passe
        this.addValidator('password', {
            validate: (value) => this.validatePassword(value),
            message: 'Mot de passe non conforme aux exigences',
            realtime: true
        });
        
        // Validateur de confirmation de mot de passe
        this.addValidator('password-confirm', {
            validate: (value, context) => this.validatePasswordConfirm(value, context),
            message: 'Les mots de passe ne correspondent pas',
            realtime: true
        });
        
        // Validateur de code 2FA
        this.addValidator('2fa-code', {
            validate: (value) => this.validate2FACode(value),
            message: 'Code à 6 chiffres requis',
            realtime: true
        });
        
        // Validateur de code de récupération
        this.addValidator('backup-code', {
            validate: (value) => this.validateBackupCode(value),
            message: 'Format de code de récupération invalide',
            realtime: false
        });
        
        // Validateur de champ requis
        this.addValidator('required', {
            validate: (value) => this.validateRequired(value),
            message: 'Ce champ est obligatoire',
            realtime: false
        });
    }
    
    /**
     * Ajoute un validateur personnalisé
     * @param {string} name - Nom du validateur
     * @param {Object} config - Configuration du validateur
     */
    addValidator(name, config) {
        this.validators.set(name, {
            validate: config.validate,
            message: config.message || 'Valeur invalide',
            realtime: config.realtime || false,
            dependencies: config.dependencies || []
        });
    }
    
    /**
     * Lie les événements aux éléments du DOM
     */
    bindEvents() {
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-validate]')) {
                this.handleRealtimeValidation(e.target);
            }
        });
        
        document.addEventListener('blur', (e) => {
            if (e.target.matches('[data-validate]')) {
                this.validateField(e.target);
            }
        });
        
        document.addEventListener('focus', (e) => {
            if (e.target.matches('[data-validate]')) {
                this.clearFieldValidation(e.target);
            }
        });
    }
    
    /**
     * Gère la validation en temps réel avec debounce
     * @param {HTMLElement} field - Champ à valider
     */
    handleRealtimeValidation(field) {
        const validatorNames = field.dataset.validate.split(' ');
        const hasRealtimeValidator = validatorNames.some(name => {
            const validator = this.validators.get(name);
            return validator && validator.realtime;
        });
        
        if (!hasRealtimeValidator) return;
        
        // Debounce pour éviter trop de validations
        const fieldId = field.id || field.name || Math.random().toString();
        
        if (this.debounceTimers.has(fieldId)) {
            clearTimeout(this.debounceTimers.get(fieldId));
        }
        
        this.debounceTimers.set(fieldId, setTimeout(() => {
            this.validateField(field);
        }, this.config.delays.realtime));
    }
    
    /**
     * Valide un champ spécifique
     * @param {HTMLElement} field - Champ à valider
     * @returns {boolean} - Résultat de la validation
     */
    validateField(field) {
        const validatorNames = field.dataset.validate.split(' ');
        const value = field.value.trim();
        const context = this.getFieldContext(field);
        
        let isValid = true;
        let messages = [];
        
        for (const validatorName of validatorNames) {
            const validator = this.validators.get(validatorName);
            
            if (!validator) {
                console.warn(`Validateur inconnu: ${validatorName}`);
                continue;
            }
            
            try {
                const result = validator.validate(value, context);
                
                if (result === false) {
                    isValid = false;
                    messages.push(validator.message);
                } else if (typeof result === 'object' && !result.valid) {
                    isValid = false;
                    messages.push(result.message || validator.message);
                }
            } catch (error) {
                console.error(`Erreur dans le validateur ${validatorName}:`, error);
                isValid = false;
                messages.push('Erreur de validation');
            }
        }
        
        this.displayFieldValidation(field, isValid, messages);
        return isValid;
    }
    
    /**
     * Obtient le contexte d'un champ (autres champs du formulaire)
     * @param {HTMLElement} field - Champ de référence
     * @returns {Object} - Contexte du champ
     */
    getFieldContext(field) {
        const form = field.closest('form');
        if (!form) return {};
        
        const context = {};
        const fields = form.querySelectorAll('[name]');
        
        fields.forEach(f => {
            context[f.name] = f.value;
        });
        
        return context;
    }
    
    /**
     * Affiche le résultat de la validation d'un champ
     * @param {HTMLElement} field - Champ validé
     * @param {boolean} isValid - Résultat de la validation
     * @param {Array} messages - Messages d'erreur
     */
    displayFieldValidation(field, isValid, messages = []) {
        // Nettoyer l'affichage précédent
        this.clearFieldValidation(field);
        
        if (isValid) {
            field.classList.add('valid');
            field.classList.remove('invalid');
            
            // Animation de succès si disponible
            if (typeof AuthAnimations !== 'undefined') {
                AuthAnimations.animateInputSuccess(field);
            }
            
            this.showFieldSuccess(field);
        } else {
            field.classList.add('invalid');
            field.classList.remove('valid');
            
            // Animation d'erreur si disponible
            if (typeof AuthAnimations !== 'undefined') {
                AuthAnimations.animateInputError(field);
            }
            
            this.showFieldError(field, messages[0] || 'Valeur invalide');
        }
    }
    
    /**
     * Efface la validation d'un champ
     * @param {HTMLElement} field - Champ à nettoyer
     */
    clearFieldValidation(field) {
        field.classList.remove('valid', 'invalid');
        
        // Supprimer les messages d'erreur
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
        
        // Supprimer les icônes de validation
        const validationIcon = field.parentNode.querySelector('.validation-icon');
        if (validationIcon) {
            validationIcon.remove();
        }
    }
    
    /**
     * Affiche un message d'erreur pour un champ
     * @param {HTMLElement} field - Champ en erreur
     * @param {string} message - Message d'erreur
     */
    showFieldError(field, message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.setAttribute('role', 'alert');
        errorElement.setAttribute('aria-live', 'polite');
        
        field.parentNode.appendChild(errorElement);
        
        // Animation d'apparition
        if (typeof gsap !== 'undefined') {
            gsap.from(errorElement, {
                duration: 0.3,
                opacity: 0,
                y: -10,
                ease: 'power2.out'
            });
        }
    }
    
    /**
     * Affiche une indication de succès pour un champ
     * @param {HTMLElement} field - Champ validé
     */
    showFieldSuccess(field) {
        const iconElement = document.createElement('span');
        iconElement.className = 'validation-icon material-icons';
        iconElement.textContent = 'check_circle';
        iconElement.style.cssText = `
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-green);
            font-size: 20px;
            pointer-events: none;
        `;
        
        // S'assurer que le parent est en position relative
        if (getComputedStyle(field.parentNode).position === 'static') {
            field.parentNode.style.position = 'relative';
        }
        
        field.parentNode.appendChild(iconElement);
        
        // Animation d'apparition
        if (typeof gsap !== 'undefined') {
            gsap.from(iconElement, {
                duration: 0.4,
                scale: 0,
                rotation: -180,
                ease: 'back.out(1.7)'
            });
        }
    }
    
    /**
     * Valide un formulaire complet
     * @param {HTMLFormElement} form - Formulaire à valider
     * @returns {boolean} - Résultat de la validation
     */
    validateForm(form) {
        const fields = form.querySelectorAll('[data-validate]');
        let isValid = true;
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // ===== VALIDATEURS SPÉCIFIQUES =====
    
    /**
     * Valide une adresse email
     * @param {string} email - Email à valider
     * @returns {boolean|Object} - Résultat de la validation
     */
    validateEmail(email) {
        if (!email) return { valid: false, message: 'Adresse email requise' };
        
        if (email.length < this.config.email.minLength) {
            return { valid: false, message: 'Adresse email trop courte' };
        }
        
        if (email.length > this.config.email.maxLength) {
            return { valid: false, message: 'Adresse email trop longue' };
        }
        
        if (!this.config.email.pattern.test(email)) {
            return { valid: false, message: 'Format d\'adresse email invalide' };
        }
        
        // Vérification du domaine
        const domain = email.split('@')[1].toLowerCase();
        const isAcademic = this.config.email.domains.academic.includes(domain);
        const isCommon = this.config.email.domains.common.includes(domain);
        
        if (!isAcademic && !isCommon) {
            return { 
                valid: true, 
                warning: 'Domaine email non reconnu',
                suggestion: this.suggestEmailDomain(email)
            };
        }
        
        return { valid: true };
    }
    
    /**
     * Suggère un domaine email similaire
     * @param {string} email - Email à corriger
     * @returns {string|null} - Suggestion ou null
     */
    suggestEmailDomain(email) {
        const [username, domain] = email.split('@');
        if (!domain) return null;
        
        const allDomains = [
            ...this.config.email.domains.academic,
            ...this.config.email.domains.common
        ];
        
        // Recherche de domaine similaire (distance de Levenshtein simplifiée)
        let bestMatch = null;
        let bestScore = Infinity;
        
        for (const suggestedDomain of allDomains) {
            const score = this.calculateDistance(domain.toLowerCase(), suggestedDomain);
            if (score < bestScore && score <= 2) {
                bestScore = score;
                bestMatch = suggestedDomain;
            }
        }
        
        return bestMatch ? `${username}@${bestMatch}` : null;
    }
    
    /**
     * Calcule la distance entre deux chaînes (Levenshtein simplifié)
     * @param {string} a - Première chaîne
     * @param {string} b - Deuxième chaîne
     * @returns {number} - Distance
     */
    calculateDistance(a, b) {
        if (a.length === 0) return b.length;
        if (b.length === 0) return a.length;
        
        const matrix = [];
        
        for (let i = 0; i <= b.length; i++) {
            matrix[i] = [i];
        }
        
        for (let j = 0; j <= a.length; j++) {
            matrix[0][j] = j;
        }
        
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }
        
        return matrix[b.length][a.length];
    }
    
    /**
     * Valide un mot de passe
     * @param {string} password - Mot de passe à valider
     * @returns {Object} - Résultat détaillé de la validation
     */
    validatePassword(password) {
        const result = {
            valid: true,
            score: 0,
            requirements: {
                length: false,
                uppercase: false,
                lowercase: false,
                numbers: false,
                special: false
            },
            issues: []
        };
        
        if (!password) {
            result.valid = false;
            result.issues.push('Mot de passe requis');
            return result;
        }
        
        // Longueur minimum
        if (password.length >= this.config.password.minLength) {
            result.requirements.length = true;
            result.score += 20;
        } else {
            result.valid = false;
            result.issues.push(`Minimum ${this.config.password.minLength} caractères requis`);
        }
        
        // Longueur maximum
        if (password.length > this.config.password.maxLength) {
            result.valid = false;
            result.issues.push(`Maximum ${this.config.password.maxLength} caractères autorisés`);
        }
        
        // Majuscules
        if (/[A-Z]/.test(password)) {
            result.requirements.uppercase = true;
            result.score += 15;
        } else if (this.config.password.requireUppercase) {
            result.valid = false;
            result.issues.push('Au moins une majuscule requise');
        }
        
        // Minuscules
        if (/[a-z]/.test(password)) {
            result.requirements.lowercase = true;
            result.score += 15;
        } else if (this.config.password.requireLowercase) {
            result.valid = false;
            result.issues.push('Au moins une minuscule requise');
        }
        
        // Chiffres
        if (/[0-9]/.test(password)) {
            result.requirements.numbers = true;
            result.score += 15;
        } else if (this.config.password.requireNumbers) {
            result.valid = false;
            result.issues.push('Au moins un chiffre requis');
        }
        
        // Caractères spéciaux
        const specialRegex = new RegExp(`[${this.config.password.specialChars.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}]`);
        if (specialRegex.test(password)) {
            result.requirements.special = true;
            result.score += 15;
        } else if (this.config.password.requireSpecialChars) {
            result.valid = false;
            result.issues.push('Au moins un caractère spécial requis');
        }
        
        // Bonus pour la complexité
        if (password.length >= 12) result.score += 10;
        if (password.length >= 16) result.score += 10;
        if (/[A-Z].*[A-Z]/.test(password)) result.score += 5; // Plusieurs majuscules
        if (/[0-9].*[0-9]/.test(password)) result.score += 5; // Plusieurs chiffres
        
        // Vérification des patterns interdits
        for (const pattern of this.config.password.forbiddenPatterns) {
            if (pattern.test(password)) {
                result.valid = false;
                result.score -= 20;
                result.issues.push('Mot de passe trop prévisible');
                break;
            }
        }
        
        // Limiter le score à 100
        result.score = Math.min(100, Math.max(0, result.score));
        
        return result;
    }
    
    /**
     * Valide la confirmation d'un mot de passe
     * @param {string} confirm - Confirmation du mot de passe
     * @param {Object} context - Contexte du formulaire
     * @returns {boolean} - Résultat de la validation
     */
    validatePasswordConfirm(confirm, context) {
        const password = context.new_password || context.password;
        
        if (!confirm) {
            return { valid: false, message: 'Confirmation du mot de passe requise' };
        }
        
        if (confirm !== password) {
            return { valid: false, message: 'Les mots de passe ne correspondent pas' };
        }
        
        return { valid: true };
    }
    
    /**
     * Valide un code 2FA
     * @param {string} code - Code 2FA à valider
     * @returns {boolean} - Résultat de la validation
     */
    validate2FACode(code) {
        if (!code) {
            return { valid: false, message: 'Code de vérification requis' };
        }
        
        if (!this.config.twofa.pattern.test(code)) {
            return { valid: false, message: 'Code à 6 chiffres requis' };
        }
        
        return { valid: true };
    }
    
    /**
     * Valide un code de récupération
     * @param {string} code - Code de récupération à valider
     * @returns {boolean} - Résultat de la validation
     */
    validateBackupCode(code) {
        if (!code) {
            return { valid: false, message: 'Code de récupération requis' };
        }
        
        const normalizedCode = code.toUpperCase().replace(/\s/g, '');
        
        if (!this.config.twofa.backupCodePattern.test(normalizedCode)) {
            return { 
                valid: false, 
                message: 'Format: XXXX-XXXX-XXXX-XXXX (4 groupes de 4 caractères)' 
            };
        }
        
        return { valid: true };
    }
    
    /**
     * Valide qu'un champ n'est pas vide
     * @param {string} value - Valeur à valider
     * @returns {boolean} - Résultat de la validation
     */
    validateRequired(value) {
        return value && value.trim().length > 0;
    }
}

// ===== FONCTIONS UTILITAIRES =====

/**
 * Initialise la validation pour un formulaire
 * @param {string|HTMLFormElement} formSelector - Sélecteur ou élément de formulaire
 * @param {Object} options - Options de validation
 * @returns {AuthValidator} - Instance du validateur
 */
function initFormValidation(formSelector, options = {}) {
    const form = typeof formSelector === 'string' 
        ? document.querySelector(formSelector) 
        : formSelector;
    
    if (!form) {
        console.error('Formulaire non trouvé:', formSelector);
        return null;
    }
    
    const validator = new AuthValidator(options);
    
    // Empêcher la soumission si la validation échoue
    form.addEventListener('submit', (e) => {
        if (!validator.validateForm(form)) {
            e.preventDefault();
            
            // Focus sur le premier champ en erreur
            const firstError = form.querySelector('.invalid');
            if (firstError) {
                firstError.focus();
            }
            
            // Afficher une alerte globale si disponible
            if (typeof showAlert === 'function') {
                showAlert('error', 'Veuillez corriger les erreurs dans le formulaire');
            }
        }
    });
    
    return validator;
}

/**
 * Initialise la validation spécifique pour la page de connexion
 */
function initLoginValidation() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;
    
    // Ajouter les attributs de validation
    const emailInput = loginForm.querySelector('#login_email');
    const passwordInput = loginForm.querySelector('#password');
    
    if (emailInput) {
        emailInput.setAttribute('data-validate', 'required email');
    }
    
    if (passwordInput) {
        passwordInput.setAttribute('data-validate', 'required');
    }
    
    return initFormValidation(loginForm);
}

/**
 * Initialise la validation pour la page de mot de passe oublié
 */
function initForgotPasswordValidation() {
    const forgotForm = document.getElementById('forgotForm');
    if (!forgotForm) return;
    
    const emailInput = forgotForm.querySelector('#email_principal');
    if (emailInput) {
        emailInput.setAttribute('data-validate', 'required email');
    }
    
    return initFormValidation(forgotForm);
}

/**
 * Initialise la validation pour la page de réinitialisation de mot de passe
 */
function initResetPasswordValidation() {
    const resetForm = document.getElementById('resetForm');
    if (!resetForm) return;
    
    const passwordInput = resetForm.querySelector('#new_password');
    const confirmInput = resetForm.querySelector('#confirm_password');
    
    if (passwordInput) {
        passwordInput.setAttribute('data-validate', 'required password');
    }
    
    if (confirmInput) {
        confirmInput.setAttribute('data-validate', 'required password-confirm');
    }
    
    return initFormValidation(resetForm);
}

/**
 * Initialise la validation pour la page 2FA
 */
function init2FAValidation() {
    const twofaForm = document.getElementById('twofaForm');
    if (!twofaForm) return;
    
    const codeInputs = twofaForm.querySelectorAll('.code-digit');
    const backupInput = twofaForm.querySelector('.backup-code-input');
    
    // Validation en temps réel pour les chiffres 2FA
    codeInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    if (backupInput) {
        backupInput.setAttribute('data-validate', 'backup-code');
    }
    
    return initFormValidation(twofaForm);
}

// ===== EXPORT GLOBAL =====
window.AuthValidation = {
    AuthValidator,
    initFormValidation,
    initLoginValidation,
    initForgotPasswordValidation,
    initResetPasswordValidation,
    init2FAValidation,
    config: VALIDATION_CONFIG
};

// ===== STYLES CSS POUR LA VALIDATION =====
// Ajouter les styles CSS pour les états de validation
const validationStyles = document.createElement('style');
validationStyles.textContent = `
    /* États de validation des champs */
    .form-control input.valid,
    .form-control select.valid,
    .form-control textarea.valid {
        border-color: var(--primary-green) !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }
    
    .form-control input.invalid,
    .form-control select.invalid,
    .form-control textarea.invalid {
        border-color: var(--accent-red) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    /* Messages d'erreur */
    .field-error {
        color: var(--accent-red);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-medium);
        margin-top: var(--spacing-xs);
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
    }
    
    .field-error::before {
        content: '⚠';
        font-size: var(--font-size-sm);
    }
    
    /* Icônes de validation */
    .validation-icon {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        pointer-events: none !important;
        z-index: 2 !important;
    }
    
    /* Animation pour les messages d'erreur */
    @keyframes errorSlideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .field-error {
        animation: errorSlideIn 0.3s ease-out;
    }
    
    /* Styles pour les suggestions d'email */
    .email-suggestion {
        margin-top: var(--spacing-xs);
        padding: var(--spacing-xs) var(--spacing-sm);
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-xs);
        color: var(--primary-blue);
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .email-suggestion:hover {
        background: rgba(59, 130, 246, 0.15);
        transform: translateY(-1px);
    }
    
    .email-suggestion::before {
        content: '💡';
        margin-right: var(--spacing-xs);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .field-error {
            font-size: 11px;
        }
        
        .validation-icon {
            right: 8px !important;
            font-size: 18px !important;
        }
    }
`;

document.head.appendChild(validationStyles);