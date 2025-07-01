// ===== AUTH-VALIDATION.JS - Real-time validation for authentication forms =====

class AuthValidator {
    constructor() {
        this.rules = {
            required: (value) => value && value.trim().length > 0,
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            emailOrUsername: (value) => {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const usernameRegex = /^[a-zA-Z0-9._]{3,20}$/;
                return emailRegex.test(value) || usernameRegex.test(value);
            },
            minLength: (value, length) => value && value.length >= parseInt(length),
            maxLength: (value, length) => !value || value.length <= parseInt(length),
            match: (value, targetId) => {
                const target = document.getElementById(targetId);
                return target && value === target.value;
            },
            numeric: (value) => /^\d+$/.test(value),
            alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
            password: (value) => this.validatePassword(value),
            complexPassword: (value) => this.validateComplexPassword(value)
        };
        
        this.messages = {
            required: 'Ce champ est obligatoire',
            email: 'Adresse email invalide',
            emailOrUsername: 'Login ou email invalide',
            minLength: 'Minimum {length} caractères requis',
            maxLength: 'Maximum {length} caractères autorisés',
            match: 'Les champs ne correspondent pas',
            numeric: 'Seuls les chiffres sont autorisés',
            alphanumeric: 'Seuls les lettres et chiffres sont autorisés',
            password: 'Mot de passe trop faible',
            complexPassword: 'Le mot de passe ne respecte pas les critères de sécurité'
        };
        
        this.init();
    }
    
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.attachEventListeners();
            this.setupPasswordStrengthIndicators();
            this.setupFormValidation();
        });
    }
    
    attachEventListeners() {
        // Validation en temps réel pour tous les champs avec data-validation
        const inputs = document.querySelectorAll('input[data-validation], select[data-validation], textarea[data-validation]');
        
        inputs.forEach(input => {
            // Validation on blur (when user leaves field)
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            // Clear errors on input (while typing)
            input.addEventListener('input', () => {
                this.clearFieldError(input);
                
                // Special handling for password fields
                if (input.type === 'password') {
                    this.updatePasswordStrength(input);
                }
                
                // Special handling for confirm password
                if (input.dataset.validation && input.dataset.validation.includes('match:')) {
                    this.validateField(input);
                }
            });
            
            // Special handling for password confirmation
            if (input.dataset.validation && input.dataset.validation.includes('match:')) {
                const targetId = input.dataset.validation.match(/match:([^|]+)/)?.[1];
                const targetField = document.getElementById(targetId);
                
                if (targetField) {
                    targetField.addEventListener('input', () => {
                        if (input.value) {
                            this.validateField(input);
                        }
                    });
                }
            }
        });
    }
    
    validateField(field) {
        const value = field.value;
        const validationRules = field.dataset.validation;
        
        if (!validationRules) return true;
        
        const rules = validationRules.split('|');
        
        for (const rule of rules) {
            const result = this.applyRule(value, rule, field);
            
            if (!result.isValid) {
                this.showFieldError(field, result.message);
                return false;
            }
        }
        
        this.clearFieldError(field);
        this.showFieldSuccess(field);
        return true;
    }
    
    applyRule(value, rule, field) {
        const [ruleName, ...params] = rule.split(':');
        
        switch (ruleName) {
            case 'required':
                return {
                    isValid: this.rules.required(value),
                    message: this.messages.required
                };
                
            case 'email':
                return {
                    isValid: !value || this.rules.email(value),
                    message: this.messages.email
                };
                
            case 'emailOrUsername':
                return {
                    isValid: !value || this.rules.emailOrUsername(value),
                    message: this.messages.emailOrUsername
                };
                
            case 'min':
                const minLength = params[0];
                return {
                    isValid: this.rules.minLength(value, minLength),
                    message: this.messages.minLength.replace('{length}', minLength)
                };
                
            case 'max':
                const maxLength = params[0];
                return {
                    isValid: this.rules.maxLength(value, maxLength),
                    message: this.messages.maxLength.replace('{length}', maxLength)
                };
                
            case 'match':
                const targetId = params[0];
                return {
                    isValid: this.rules.match(value, targetId),
                    message: this.messages.match
                };
                
            case 'numeric':
                return {
                    isValid: !value || this.rules.numeric(value),
                    message: this.messages.numeric
                };
                
            case 'complexity':
                const complexityResult = this.validateComplexPassword(value);
                return {
                    isValid: !value || complexityResult.isValid,
                    message: complexityResult.message
                };
                
            default:
                return { isValid: true, message: '' };
        }
    }
    
    validatePassword(password) {
        return {
            isValid: password.length >= 6,
            message: 'Le mot de passe doit contenir au moins 6 caractères'
        };
    }
    
    validateComplexPassword(password) {
        const requirements = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[@$!%*?&]/.test(password)
        };
        
        const failedRequirements = [];
        if (!requirements.length) failedRequirements.push('8 caractères');
        if (!requirements.lowercase) failedRequirements.push('une minuscule');
        if (!requirements.uppercase) failedRequirements.push('une majuscule');
        if (!requirements.number) failedRequirements.push('un chiffre');
        if (!requirements.special) failedRequirements.push('un caractère spécial');
        
        return {
            isValid: Object.values(requirements).every(req => req),
            message: failedRequirements.length > 0 
                ? `Manque: ${failedRequirements.join(', ')}`
                : 'Mot de passe valide',
            requirements
        };
    }
    
    updatePasswordStrength(passwordField) {
        const password = passwordField.value;
        const strengthContainer = document.querySelector('.auth-password-strength');
        
        if (!strengthContainer) return;
        
        const strengthFill = strengthContainer.querySelector('.auth-strength-fill');
        const strengthText = strengthContainer.querySelector('.auth-strength-text');
        const requirements = strengthContainer.querySelectorAll('.password-requirement');
        
        if (!password) {
            if (strengthFill) {
                strengthFill.className = 'auth-strength-fill';
            }
            if (strengthText) {
                strengthText.textContent = 'Entrez un mot de passe';
                strengthText.className = 'auth-strength-text';
            }
            return;
        }
        
        const complexity = this.validateComplexPassword(password);
        let score = 0;
        
        // Calculate strength score
        if (complexity.requirements.length) score++;
        if (complexity.requirements.lowercase) score++;
        if (complexity.requirements.uppercase) score++;
        if (complexity.requirements.number) score++;
        if (complexity.requirements.special) score++;
        if (password.length >= 12) score++; // Bonus for longer passwords
        
        const strength = ['weak', 'weak', 'fair', 'good', 'good', 'strong'][Math.min(score, 5)];
        const strengthLabels = {
            weak: 'Faible',
            fair: 'Moyen',
            good: 'Bon',
            strong: 'Fort'
        };
        
        if (strengthFill) {
            strengthFill.className = `auth-strength-fill ${strength}`;
        }
        
        if (strengthText) {
            strengthText.className = `auth-strength-text ${strength}`;
            strengthText.textContent = strengthLabels[strength] || 'Très faible';
        }
        
        // Update individual requirements
        if (requirements.length > 0) {
            this.updatePasswordRequirements(complexity.requirements, requirements);
        }
    }
    
    updatePasswordRequirements(requirements, requirementElements) {
        const reqMap = {
            'req-length': requirements.length,
            'req-lowercase': requirements.lowercase,
            'req-uppercase': requirements.uppercase,
            'req-number': requirements.number,
            'req-special': requirements.special
        };
        
        requirementElements.forEach(req => {
            const isValid = reqMap[req.id];
            const icon = req.querySelector('i');
            
            if (isValid) {
                req.classList.add('valid');
                if (icon) {
                    icon.className = 'fas fa-check text-green-500 mr-2';
                }
            } else {
                req.classList.remove('valid');
                if (icon) {
                    icon.className = 'fas fa-times text-red-500 mr-2';
                }
            }
        });
    }
    
    showFieldError(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentNode.querySelector('.auth-form-error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'auth-form-error';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.classList.add('show');
        
        // Add shake animation
        field.classList.add('auth-animate-shake');
        setTimeout(() => field.classList.remove('auth-animate-shake'), 500);
    }
    
    clearFieldError(field) {
        field.classList.remove('error');
        
        const errorElement = field.parentNode.querySelector('.auth-form-error');
        if (errorElement) {
            errorElement.classList.remove('show');
            setTimeout(() => {
                if (errorElement.parentNode) {
                    errorElement.remove();
                }
            }, 300);
        }
    }
    
    showFieldSuccess(field) {
        // Only show success for certain field types
        if (field.type === 'password' || field.type === 'email') {
            field.classList.add('valid');
            setTimeout(() => field.classList.remove('valid'), 2000);
        }
    }
    
    validateForm(form) {
        const fields = form.querySelectorAll('input[data-validation], select[data-validation], textarea[data-validation]');
        let isValid = true;
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    setupPasswordStrengthIndicators() {
        const passwordFields = document.querySelectorAll('input[type="password"][data-validation*="complexity"]');
        
        passwordFields.forEach(field => {
            // Create strength indicator if it doesn't exist
            if (!field.parentNode.querySelector('.auth-password-strength')) {
                this.createPasswordStrengthIndicator(field);
            }
        });
    }
    
    createPasswordStrengthIndicator(passwordField) {
        const strengthContainer = document.createElement('div');
        strengthContainer.className = 'auth-password-strength mt-2';
        
        strengthContainer.innerHTML = `
            <div class="auth-strength-bar">
                <div class="auth-strength-fill"></div>
            </div>
            <p class="auth-strength-text text-xs mt-1">Entrez un mot de passe</p>
        `;
        
        passwordField.parentNode.appendChild(strengthContainer);
    }
    
    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (form.querySelector('[data-validation]')) {
                    if (!this.validateForm(form)) {
                        e.preventDefault();
                        
                        // Focus on first invalid field
                        const firstInvalid = form.querySelector('.error');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        
                        // Show form-level error message
                        this.showFormError(form, 'Veuillez corriger les erreurs dans le formulaire');
                    }
                }
            });
        });
    }
    
    showFormError(form, message) {
        // Remove existing form errors
        const existingErrors = form.querySelectorAll('.form-error-message');
        existingErrors.forEach(error => error.remove());
        
        // Create new form error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error-message auth-message error';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle mr-2"></i>
            ${message}
        `;
        
        // Insert at the top of the form
        form.insertBefore(errorDiv, form.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.style.opacity = '0';
            setTimeout(() => errorDiv.remove(), 300);
        }, 5000);
    }
    
    // Public API methods
    validate(field) {
        return this.validateField(field);
    }
    
    validateFormData(form) {
        return this.validateForm(form);
    }
    
    addCustomRule(name, validator, message) {
        this.rules[name] = validator;
        this.messages[name] = message;
    }
    
    setMessage(ruleName, message) {
        this.messages[ruleName] = message;
    }
}

// Initialize the validator
const authValidator = new AuthValidator();

// Export for global use
window.AuthValidator = AuthValidator;
window.authValidator = authValidator;