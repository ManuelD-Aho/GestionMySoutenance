/**
 * Authentication Validation System
 * Production-ready real-time validation for auth forms
 * 
 * Features:
 * - Real-time input validation
 * - Password strength checking
 * - Email format validation
 * - 2FA code validation
 * - Accessibility support
 * - Debounced validation
 * - Custom validation rules
 */

class AuthValidation {
    constructor() {
        this.validators = new Map();
        this.debounceTimers = new Map();
        this.validationRules = {
            email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Veuillez saisir une adresse email valide'
            },
            password: {
                minLength: 6,
                message: 'Le mot de passe doit contenir au moins 6 caractères'
            },
            passwordStrength: {
                minLength: 8,
                patterns: {
                    lowercase: /[a-z]/,
                    uppercase: /[A-Z]/,
                    number: /\d/,
                    special: /[!@#$%^&*(),.?":{}|<>]/
                }
            },
            twoFactorCode: {
                pattern: /^\d{6}$/,
                message: 'Le code doit contenir exactement 6 chiffres'
            },
            recoveryCode: {
                pattern: /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/,
                message: 'Format de code de récupération invalide (XXXX-XXXX-XXXX-XXXX)'
            }
        };

        this.initializeValidation();
        this.bindEvents();
    }

    /**
     * Initialize validation system
     */
    initializeValidation() {
        this.setupCustomValidators();
        this.setupPasswordStrengthMeter();
        this.setup2FACodeInputs();
    }

    /**
     * Bind validation events
     */
    bindEvents() {
        // Real-time validation on input
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-validation]')) {
                this.handleInputValidation(e.target);
            }
        });

        // Validation on blur
        document.addEventListener('blur', (e) => {
            if (e.target.matches('[data-validation]')) {
                this.validateInput(e.target, true);
            }
        }, true);

        // Form submission validation
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.auth-form')) {
                this.handleFormSubmission(e);
            }
        });

        // Password toggle functionality
        document.addEventListener('click', (e) => {
            if (e.target.matches('.password-toggle') || e.target.closest('.password-toggle')) {
                this.togglePasswordVisibility(e.target.closest('.password-toggle'));
            }
        });
    }

    /**
     * Setup custom validators
     */
    setupCustomValidators() {
        // Email or username validator
        this.validators.set('email-or-username', (value) => {
            if (!value.trim()) {
                return { valid: false, message: 'Ce champ est obligatoire' };
            }
            
            // Check if it's an email or username
            const isEmail = value.includes('@');
            if (isEmail) {
                return this.validateEmail(value);
            } else {
                // Username validation (alphanumeric, underscore, dash, min 3 chars)
                const usernamePattern = /^[a-zA-Z0-9_-]{3,}$/;
                if (!usernamePattern.test(value)) {
                    return { 
                        valid: false, 
                        message: 'Nom d\'utilisateur invalide (min 3 caractères, lettres, chiffres, _ et - autorisés)' 
                    };
                }
            }
            
            return { valid: true };
        });

        // Password confirmation validator
        this.validators.set('password-confirmation', (value, element) => {
            const passwordField = document.getElementById('new_password') || 
                                 document.getElementById('password');
            
            if (!passwordField) {
                return { valid: false, message: 'Champ mot de passe non trouvé' };
            }

            if (value !== passwordField.value) {
                return { valid: false, message: 'Les mots de passe ne correspondent pas' };
            }

            return { valid: true };
        });
    }

    /**
     * Handle input validation with debouncing
     */
    handleInputValidation(input) {
        const inputId = input.id;
        
        // Clear existing timer
        if (this.debounceTimers.has(inputId)) {
            clearTimeout(this.debounceTimers.get(inputId));
        }

        // Set new timer
        const timer = setTimeout(() => {
            this.validateInput(input, false);
        }, 300); // 300ms debounce

        this.debounceTimers.set(inputId, timer);
    }

    /**
     * Validate individual input
     */
    validateInput(input, showErrors = true) {
        const validationType = input.getAttribute('data-validation');
        if (!validationType) return true;

        const value = input.value.trim();
        let result = { valid: true };

        // Check required fields
        if (input.hasAttribute('required') && !value) {
            result = { valid: false, message: 'Ce champ est obligatoire' };
        } else if (value) {
            // Apply specific validation
            switch (validationType) {
                case 'email':
                    result = this.validateEmail(value);
                    break;
                case 'email-or-username':
                    result = this.validators.get('email-or-username')(value, input);
                    break;
                case 'password':
                    result = this.validatePassword(value);
                    break;
                case 'password-strength':
                    result = this.validatePasswordStrength(value);
                    break;
                case 'password-confirmation':
                    result = this.validators.get('password-confirmation')(value, input);
                    break;
                case '2fa-code':
                    result = this.validate2FACode(value);
                    break;
                case 'recovery-code':
                    result = this.validateRecoveryCode(value);
                    break;
                default:
                    // Custom validator
                    if (this.validators.has(validationType)) {
                        result = this.validators.get(validationType)(value, input);
                    }
            }
        }

        // Update UI
        this.updateInputState(input, result, showErrors);
        return result.valid;
    }

    /**
     * Validate email format
     */
    validateEmail(email) {
        if (!email) {
            return { valid: false, message: 'Adresse email requise' };
        }

        if (!this.validationRules.email.pattern.test(email)) {
            return { valid: false, message: this.validationRules.email.message };
        }

        // Additional checks
        if (email.length > 254) {
            return { valid: false, message: 'Adresse email trop longue' };
        }

        const parts = email.split('@');
        if (parts[0].length > 64) {
            return { valid: false, message: 'Partie locale de l\'email trop longue' };
        }

        return { valid: true };
    }

    /**
     * Validate password
     */
    validatePassword(password) {
        if (!password) {
            return { valid: false, message: 'Mot de passe requis' };
        }

        if (password.length < this.validationRules.password.minLength) {
            return { valid: false, message: this.validationRules.password.message };
        }

        return { valid: true };
    }

    /**
     * Validate password strength
     */
    validatePasswordStrength(password) {
        if (!password) {
            return { valid: false, message: 'Mot de passe requis' };
        }

        const rules = this.validationRules.passwordStrength;
        const checks = {
            length: password.length >= rules.minLength,
            lowercase: rules.patterns.lowercase.test(password),
            uppercase: rules.patterns.uppercase.test(password),
            number: rules.patterns.number.test(password),
            special: rules.patterns.special.test(password)
        };

        // Update strength requirements UI
        this.updatePasswordRequirements(checks);

        // Calculate strength
        const strength = this.calculatePasswordStrength(password, checks);
        this.updatePasswordStrengthMeter(strength);

        // Password is valid if it meets minimum requirements
        const isValid = checks.length && checks.lowercase && checks.uppercase && checks.number;
        
        if (!isValid) {
            return { 
                valid: false, 
                message: 'Le mot de passe ne respecte pas tous les critères de sécurité'
            };
        }

        return { valid: true, strength: strength };
    }

    /**
     * Calculate password strength score
     */
    calculatePasswordStrength(password, checks) {
        let score = 0;
        
        // Length scoring
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 25;
        
        // Character type scoring
        if (checks.lowercase) score += 15;
        if (checks.uppercase) score += 15;
        if (checks.number) score += 15;
        if (checks.special) score += 15;

        // Additional complexity
        const uniqueChars = new Set(password.toLowerCase()).size;
        if (uniqueChars >= 6) score += 10;

        // Common patterns penalty
        if (/(.)\1{2,}/.test(password)) score -= 10; // Repeated characters
        if (/123|abc|qwe/i.test(password)) score -= 15; // Sequential patterns

        score = Math.max(0, Math.min(100, score));

        // Determine strength level
        if (score < 40) return { level: 'weak', score, text: 'Faible' };
        if (score < 60) return { level: 'fair', score, text: 'Moyen' };
        if (score < 80) return { level: 'good', score, text: 'Bon' };
        return { level: 'strong', score, text: 'Fort' };
    }

    /**
     * Update password requirements UI
     */
    updatePasswordRequirements(checks) {
        const requirements = document.querySelectorAll('.requirement');
        
        requirements.forEach(req => {
            const requirement = req.getAttribute('data-requirement');
            const icon = req.querySelector('.requirement-icon');
            
            if (checks[requirement]) {
                req.classList.add('met');
                if (icon) {
                    icon.className = 'fas fa-check requirement-icon';
                }
            } else {
                req.classList.remove('met');
                if (icon) {
                    icon.className = 'fas fa-times requirement-icon';
                }
            }
        });
    }

    /**
     * Update password strength meter
     */
    updatePasswordStrengthMeter(strength) {
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        if (strengthBar) {
            strengthBar.setAttribute('data-strength', strength.level);
            strengthBar.setAttribute('aria-valuenow', strength.score);
        }

        if (strengthText) {
            strengthText.textContent = strength.text;
            strengthText.setAttribute('data-strength', strength.level);
        }

        // Trigger animation if available
        if (window.authAnimations) {
            window.authAnimations.passwordStrength(strength.level, strength.text);
        }
    }

    /**
     * Validate 2FA code
     */
    validate2FACode(code) {
        if (!code) {
            return { valid: false, message: 'Code de vérification requis' };
        }

        if (!this.validationRules.twoFactorCode.pattern.test(code)) {
            return { valid: false, message: this.validationRules.twoFactorCode.message };
        }

        return { valid: true };
    }

    /**
     * Validate recovery code
     */
    validateRecoveryCode(code) {
        if (!code) {
            return { valid: false, message: 'Code de récupération requis' };
        }

        const normalizedCode = code.toUpperCase().replace(/\s/g, '');
        
        if (!this.validationRules.recoveryCode.pattern.test(normalizedCode)) {
            return { valid: false, message: this.validationRules.recoveryCode.message };
        }

        return { valid: true };
    }

    /**
     * Setup password strength meter
     */
    setupPasswordStrengthMeter() {
        const passwordField = document.querySelector('[data-validation="password-strength"]');
        if (!passwordField) return;

        // Initialize meter
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        if (strengthBar) {
            strengthBar.setAttribute('aria-label', 'Force du mot de passe');
            strengthBar.setAttribute('role', 'progressbar');
            strengthBar.setAttribute('aria-valuenow', '0');
            strengthBar.setAttribute('aria-valuemin', '0');
            strengthBar.setAttribute('aria-valuemax', '100');
        }
    }

    /**
     * Setup 2FA code inputs with auto-progression
     */
    setup2FACodeInputs() {
        const codeInputs = document.querySelectorAll('.code-input');
        if (codeInputs.length === 0) return;

        const hiddenInput = document.getElementById('code_2fa_hidden');

        codeInputs.forEach((input, index) => {
            // Only allow numeric input
            input.addEventListener('input', (e) => {
                const value = e.target.value.replace(/\D/g, '');
                e.target.value = value;

                if (value.length === 1 && index < codeInputs.length - 1) {
                    // Move to next input
                    codeInputs[index + 1].focus();
                }

                // Update hidden input with combined value
                this.update2FAHiddenInput(codeInputs, hiddenInput);

                // Trigger animations
                if (window.authAnimations) {
                    window.authAnimations.twoFactorCodeProgression(codeInputs, this.getFilledInputsCount(codeInputs));
                }
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/\D/g, '');
                
                for (let i = 0; i < Math.min(pastedData.length, codeInputs.length - index); i++) {
                    if (codeInputs[index + i]) {
                        codeInputs[index + i].value = pastedData[i];
                    }
                }

                // Focus on next empty input or last input
                const nextIndex = Math.min(index + pastedData.length, codeInputs.length - 1);
                codeInputs[nextIndex].focus();

                // Update hidden input
                this.update2FAHiddenInput(codeInputs, hiddenInput);
            });
        });
    }

    /**
     * Update 2FA hidden input with combined value
     */
    update2FAHiddenInput(inputs, hiddenInput) {
        if (!hiddenInput) return;

        const combinedValue = Array.from(inputs)
            .map(input => input.value)
            .join('');
        
        hiddenInput.value = combinedValue;

        // Validate combined code
        if (combinedValue.length === 6) {
            const result = this.validate2FACode(combinedValue);
            if (!result.valid) {
                inputs.forEach(input => input.classList.add('error'));
                setTimeout(() => {
                    inputs.forEach(input => input.classList.remove('error'));
                }, 500);
            }
        }
    }

    /**
     * Get count of filled inputs
     */
    getFilledInputsCount(inputs) {
        return Array.from(inputs).filter(input => input.value.trim()).length;
    }

    /**
     * Toggle password visibility
     */
    togglePasswordVisibility(toggleButton) {
        const passwordInput = toggleButton.parentElement.querySelector('input[type="password"], input[type="text"]');
        if (!passwordInput) return;

        const icon = toggleButton.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            if (icon) {
                icon.className = 'fas fa-eye-slash';
            }
            toggleButton.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            passwordInput.type = 'password';
            if (icon) {
                icon.className = 'fas fa-eye';
            }
            toggleButton.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    }

    /**
     * Update input visual state
     */
    updateInputState(input, result, showErrors = true) {
        const errorElement = document.getElementById(input.id + '_error');
        const statusIcon = input.parentElement.querySelector('.input-status');

        // Remove previous states
        input.classList.remove('valid', 'invalid', 'error');

        if (result.valid) {
            input.classList.add('valid');
            input.setAttribute('aria-invalid', 'false');

            // Hide error message
            if (errorElement) {
                if (window.authAnimations) {
                    window.authAnimations.validationSuccess(input);
                } else {
                    errorElement.style.display = 'none';
                }
            }

            // Show success icon
            if (statusIcon) {
                const validIcon = statusIcon.querySelector('.input-valid');
                const invalidIcon = statusIcon.querySelector('.input-invalid');
                if (validIcon) validIcon.style.display = 'block';
                if (invalidIcon) invalidIcon.style.display = 'none';
            }
        } else if (showErrors) {
            input.classList.add('invalid', 'error');
            input.setAttribute('aria-invalid', 'true');

            // Show error message
            if (errorElement && result.message) {
                if (window.authAnimations) {
                    window.authAnimations.validationError(input, result.message);
                } else {
                    errorElement.textContent = result.message;
                    errorElement.style.display = 'block';
                }
            }

            // Show error icon
            if (statusIcon) {
                const validIcon = statusIcon.querySelector('.input-valid');
                const invalidIcon = statusIcon.querySelector('.input-invalid');
                if (validIcon) validIcon.style.display = 'none';
                if (invalidIcon) invalidIcon.style.display = 'block';
            }
        }
    }

    /**
     * Handle form submission validation
     */
    handleFormSubmission(form) {
        const inputs = form.querySelectorAll('[data-validation]');
        let isValid = true;

        // Validate all inputs
        inputs.forEach(input => {
            if (!this.validateInput(input, true)) {
                isValid = false;
            }
        });

        // Additional form-specific validation
        if (form.id === 'resetPasswordForm') {
            const newPassword = form.querySelector('#new_password');
            const confirmPassword = form.querySelector('#confirm_password');
            
            if (newPassword && confirmPassword && newPassword.value !== confirmPassword.value) {
                this.updateInputState(confirmPassword, {
                    valid: false,
                    message: 'Les mots de passe ne correspondent pas'
                }, true);
                isValid = false;
            }
        }

        if (!isValid) {
            form.preventDefault();
            
            // Focus on first invalid input
            const firstInvalid = form.querySelector('.invalid, .error');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    }

    /**
     * Add custom validator
     */
    addValidator(name, validator) {
        this.validators.set(name, validator);
    }

    /**
     * Remove validator
     */
    removeValidator(name) {
        this.validators.delete(name);
    }

    /**
     * Validate entire form programmatically
     */
    validateForm(form) {
        const inputs = form.querySelectorAll('[data-validation]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateInput(input, false)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Clear all validation states
     */
    clearValidation(form) {
        const inputs = form.querySelectorAll('[data-validation]');
        
        inputs.forEach(input => {
            input.classList.remove('valid', 'invalid', 'error');
            input.setAttribute('aria-invalid', 'false');
            
            const errorElement = document.getElementById(input.id + '_error');
            if (errorElement) {
                errorElement.style.display = 'none';
                errorElement.textContent = '';
            }
            
            const statusIcon = input.parentElement.querySelector('.input-status');
            if (statusIcon) {
                const icons = statusIcon.querySelectorAll('i');
                icons.forEach(icon => icon.style.display = 'none');
            }
        });
    }
}

// Initialize validation when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.authValidation = new AuthValidation();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthValidation;
}