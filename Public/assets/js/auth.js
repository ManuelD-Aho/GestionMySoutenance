// ===== AUTH.JS - Enhanced script for authentication pages =====

// Variables globales
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const indicators = document.querySelectorAll('.indicator');
let slideInterval;

// === INITIALISATION ===
document.addEventListener('DOMContentLoaded', function() {
    initializeCarousel();
    initializeForms();
    handleUrlParameters();
    setupEventListeners();
    setupRealTimeValidation();
    initializePasswordToggles();
});

// === GESTION DU CARROUSEL ===
function initializeCarousel() {
    if (slides.length === 0) return;

    // Démarrer le carrousel automatique
    startCarousel();

    // Ajouter les écouteurs pour les indicateurs
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            goToSlide(index);
        });
    });

    // Pause au survol sur desktop
    const carouselContainer = document.querySelector('.carousel-container');
    if (carouselContainer) {
        carouselContainer.addEventListener('mouseenter', pauseCarousel);
        carouselContainer.addEventListener('mouseleave', startCarousel);
    }
}

function startCarousel() {
    slideInterval = setInterval(() => {
        nextSlide();
    }, 5000); // Change toutes les 5 secondes
}

function pauseCarousel() {
    clearInterval(slideInterval);
}

function goToSlide(index) {
    if (index === currentSlide) return;

    // Retirer la classe active de tous les éléments
    slides[currentSlide].classList.remove('active');
    indicators[currentSlide].classList.remove('active');

    // Ajouter la classe active aux nouveaux éléments
    currentSlide = index;
    slides[currentSlide].classList.add('active');
    indicators[currentSlide].classList.add('active');

    // Redémarrer le carrousel
    pauseCarousel();
    startCarousel();
}

function nextSlide() {
    const nextIndex = (currentSlide + 1) % slides.length;
    goToSlide(nextIndex);
}

// === GESTION DES FORMULAIRES ===
function initializeForms() {
    // Gérer les soumissions de formulaires
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    // Validation en temps réel
    setupRealTimeValidation();
}

function showForm(formId) {
    // Masquer tous les formulaires
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => {
        form.classList.remove('active');
    });

    // Afficher le formulaire demandé
    const targetForm = document.getElementById(formId);
    if (targetForm) {
        targetForm.classList.add('active');

        // Focus sur le premier champ
        const firstInput = targetForm.querySelector('input:not([type="hidden"])');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    // Masquer les messages
    hideMessage();
}

function handleFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const formId = form.id;

    // Validation côté client
    if (!validateForm(form)) {
        return;
    }

    // Afficher l'état de chargement
    showLoading(form);

    // Envoyer les données
    submitForm(form, formData)
        .then(response => handleFormResponse(response, formId))
        .catch(error => handleFormError(error, formId))
        .finally(() => hideLoading(form));
}

async function submitForm(form, formData) {
    const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}

function handleFormResponse(response, formId) {
    if (response.success) {
        showMessage(response.message || 'Opération réussie !', 'success');

        // Actions spécifiques selon le formulaire
        switch (formId) {
            case 'loginForm':
                if (response.requires_2fa) {
                    showForm('twoFactorForm');
                } else {
                    // Redirection après connexion
                    setTimeout(() => {
                        window.location.href = response.redirect || 'dashboard.php';
                    }, 1500);
                }
                break;

            case 'forgotForm':
                showMessage('Un email de réinitialisation a été envoyé.', 'success');
                setTimeout(() => showForm('loginForm'), 3000);
                break;

            case 'resetForm':
                showMessage('Mot de passe réinitialisé avec succès !', 'success');
                setTimeout(() => showForm('loginForm'), 2000);
                break;

            case 'twoFactorForm':
                setTimeout(() => {
                    window.location.href = response.redirect || 'dashboard.php';
                }, 1500);
                break;
        }
    } else {
        showMessage(response.message || 'Une erreur est survenue.', 'error');
    }
}

function handleFormError(error, formId) {
    console.error('Form submission error:', error);
    showMessage('Erreur de connexion. Veuillez réessayer.', 'error');
}

// === ENHANCED FORM VALIDATION AND HANDLING ===
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });

    // Validation spécifique pour les mots de passe
    if (form.id === 'resetPasswordForm') {
        const newPassword = form.querySelector('#new_password');
        const confirmPassword = form.querySelector('#confirm_password');

        if (newPassword && confirmPassword && newPassword.value !== confirmPassword.value) {
            showInputError(confirmPassword, 'Les mots de passe ne correspondent pas.');
            isValid = false;
        }
    }

    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();
    const validationRules = input.dataset.validation || '';

    // Vérifier si le champ est requis et vide
    if (input.hasAttribute('required') && !value) {
        showInputError(input, 'Ce champ est obligatoire.');
        return false;
    }

    // Validation basée sur les règles data-validation
    if (validationRules) {
        const rules = validationRules.split('|');
        
        for (const rule of rules) {
            if (rule === 'required' && !value) {
                showInputError(input, 'Ce champ est obligatoire.');
                return false;
            }
            
            if (rule === 'email' && value && !isValidEmail(value)) {
                showInputError(input, 'Adresse email invalide.');
                return false;
            }
            
            if (rule === 'email_or_username' && value && !isValidEmailOrUsername(value)) {
                showInputError(input, 'Login ou email invalide.');
                return false;
            }
            
            if (rule.startsWith('min:')) {
                const minLength = parseInt(rule.split(':')[1]);
                if (value && value.length < minLength) {
                    showInputError(input, `Minimum ${minLength} caractères requis.`);
                    return false;
                }
            }
            
            if (rule === 'complexity') {
                const complexityResult = checkPasswordComplexity(value);
                if (value && !complexityResult.isValid) {
                    showInputError(input, complexityResult.message);
                    return false;
                }
            }
            
            if (rule.startsWith('match:')) {
                const targetFieldId = rule.split(':')[1];
                const targetField = document.getElementById(targetFieldId);
                if (targetField && value !== targetField.value) {
                    showInputError(input, 'Les mots de passe ne correspondent pas.');
                    return false;
                }
            }
        }
    }

    // Supprimer les erreurs si la validation passe
    clearInputError(input);
    return true;
}

function checkPasswordComplexity(password) {
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
            : 'Mot de passe valide'
    };
}

function setupRealTimeValidation() {
    const inputs = document.querySelectorAll('input, select');

    inputs.forEach(input => {
        input.addEventListener('blur', () => validateInput(input));
        input.addEventListener('input', () => {
            // Supprimer l'erreur pendant la saisie
            if (input.classList.contains('error')) {
                clearInputError(input);
            }
            
            // Validation en temps réel pour certains champs
            if (input.type === 'password' && input.id === 'new_password') {
                updatePasswordStrength(input.value);
            }
        });
    });
}

function updatePasswordStrength(password) {
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    if (!strengthFill || !strengthText) return;
    
    let score = 0;
    
    // Calcul du score
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[@$!%*?&]/.test(password)) score++;
    
    const strength = ['weak', 'weak', 'fair', 'good', 'good', 'strong'][Math.min(score, 5)];
    const strengthLabels = {
        weak: 'Faible',
        fair: 'Moyen', 
        good: 'Bon',
        strong: 'Fort'
    };
    
    strengthFill.className = `auth-strength-fill ${strength}`;
    strengthText.className = `auth-strength-text ${strength}`;
    strengthText.textContent = strengthLabels[strength] || 'Très faible';
}

function showInputError(input, message) {
    input.classList.add('error');

    // Supprimer l'ancien message d'erreur
    const existingError = input.parentNode.querySelector('.auth-form-error');
    if (existingError) {
        existingError.textContent = message;
        existingError.classList.add('show');
        return;
    }

    // Ajouter le nouveau message s'il n'existe pas
    const errorDiv = document.createElement('div');
    errorDiv.className = 'auth-form-error show';
    errorDiv.textContent = message;

    input.parentNode.appendChild(errorDiv);
}

function clearInputError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentNode.querySelector('.auth-form-error');
    if (errorDiv) {
        errorDiv.classList.remove('show');
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 300);
    }
}

// === PASSWORD TOGGLE FUNCTIONALITY ===
function initializePasswordToggles() {
    const toggles = document.querySelectorAll('.auth-password-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (!input || input.type !== 'password' && input.type !== 'text') return;
            
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    });
}

// === ENHANCED UTILITAIRES ===
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidEmailOrUsername(value) {
    // Username: alphanumeric + underscore/dot, 3-20 chars
    const usernameRegex = /^[a-zA-Z0-9._]{3,20}$/;
    return isValidEmail(value) || usernameRegex.test(value);
}

function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.auth-message:not(#flashMessage)');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `auth-message ${type}`;
    messageDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
        ${message}
    `;
    
    // Insert at top of first form content
    const formContent = document.querySelector('.auth-form-content');
    if (formContent) {
        formContent.insertBefore(messageDiv, formContent.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => messageDiv.remove(), 300);
        }, 5000);
    }
}

function hideMessage() {
    const messages = document.querySelectorAll('.auth-message:not(#flashMessage)');
    messages.forEach(msg => {
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 300);
    });
}

function showLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        
        // Hide text, show loading
        const btnText = submitButton.querySelector('.auth-btn-text');
        const btnLoading = submitButton.querySelector('.auth-btn-loading');
        
        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline-flex';
    }

    form.classList.add('loading');
}

function hideLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.classList.remove('loading');
        
        // Show text, hide loading
        const btnText = submitButton.querySelector('.auth-btn-text');
        const btnLoading = submitButton.querySelector('.auth-btn-loading');
        
        if (btnText) btnText.style.display = 'inline-flex';
        if (btnLoading) btnLoading.style.display = 'none';
    }

    form.classList.remove('loading');
}

function handleUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');
    const message = urlParams.get('message');
    
    if (error) {
        showMessage(decodeURIComponent(message || 'Une erreur est survenue'), 'error');
    } else if (success) {
        showMessage(decodeURIComponent(message || 'Opération réussie'), 'success');
    }
}

function setupEventListeners() {
    // Global form submission
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('auth-form') || form.closest('.auth-form')) {
            e.preventDefault();
            handleFormSubmit(e);
        }
    });
    
    // 2FA code auto-advance
    const twoFAInputs = document.querySelectorAll('.auth-2fa-input');
    if (twoFAInputs.length > 0) {
        setup2FAInputs(twoFAInputs);
    }
    
    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const form = e.target.closest('form');
            if (form && !e.target.matches('textarea')) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    e.preventDefault();
                    submitBtn.click();
                }
            }
        }
    });
}

function setup2FAInputs(inputs) {
    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Only allow numbers
            if (!/^\d$/.test(value) && value !== '') {
                e.target.value = '';
                return;
            }
            
            if (value) {
                input.classList.add('filled');
                // Move to next input
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Check if all inputs are filled
                const allFilled = Array.from(inputs).every(inp => inp.value);
                if (allFilled) {
                    const form = input.closest('form');
                    const hiddenInput = form?.querySelector('input[name="code_2fa"]');
                    if (hiddenInput) {
                        hiddenInput.value = Array.from(inputs).map(inp => inp.value).join('');
                    }
                    
                    const submitBtn = form?.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.add('auth-animate-bounce');
                        setTimeout(() => submitBtn.classList.remove('auth-animate-bounce'), 600);
                    }
                }
            } else {
                input.classList.remove('filled');
            }
        });
        
        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
            }
        });
        
        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            
            digits.split('').forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                    inputs[i].classList.add('filled');
                }
            });
            
            if (digits.length > 0) {
                const lastFilledIndex = Math.min(digits.length - 1, 5);
                inputs[lastFilledIndex].focus();
            }
        });
    });
}

// === FONCTIONS UTILITAIRES POUR LES FORMULAIRES ===
function resend2FA() {
    const resendBtn = document.getElementById('resend2FABtn');
    if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.textContent = 'Envoi en cours...';
        
        // Simulate resend (replace with actual AJAX call)
        setTimeout(() => {
            showMessage('Code renvoyé avec succès !', 'success');
            resendBtn.textContent = 'Code renvoyé';
            
            // Start 60 second timer before allowing resend
            let timer = 60;
            const interval = setInterval(() => {
                timer--;
                resendBtn.textContent = `Renvoyer (${timer}s)`;
                
                if (timer <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Renvoyer le code';
                }
            }, 1000);
        }, 2000);
    }
}

// === GESTION DU RESPONSIVE ===
function handleResize() {
    const authContainer = document.querySelector('.auth-container');
    if (!authContainer) return;
    
    if (window.innerWidth <= 768) {
        // Mobile adjustments
        authContainer.classList.add('mobile');
    } else {
        authContainer.classList.remove('mobile');
    }
}

// === ACCESSIBILITY ENHANCEMENTS ===
function enhanceAccessibility() {
    // Add ARIA labels to interactive elements
    const passwordToggles = document.querySelectorAll('.auth-password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.setAttribute('aria-label', 'Afficher/masquer le mot de passe');
        toggle.setAttribute('role', 'button');
        toggle.setAttribute('tabindex', '0');
        
        // Keyboard support
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Enhance form inputs with better ARIA support
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label && !input.getAttribute('aria-labelledby')) {
            input.setAttribute('aria-labelledby', label.id || `${input.id}-label`);
        }
    });
}

// Initialize accessibility on page load
document.addEventListener('DOMContentLoaded', function() {
    enhanceAccessibility();
    handleResize();
});

// Listen for resize events
window.addEventListener('resize', handleResize);

// === EXPORT FOR EXTERNAL USE ===
window.AuthJS = {
    validateForm,
    validateInput,
    showMessage,
    hideMessage,
    showLoading,
    hideLoading,
    isValidEmail,
    isValidEmailOrUsername,
    checkPasswordComplexity
};