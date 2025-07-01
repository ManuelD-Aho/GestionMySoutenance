/**
 * Authentication Main Controller
 * Production-ready authentication system integration
 * 
 * Features:
 * - Form submission handling
 * - CSRF token management
 * - Loading states
 * - Error handling
 * - Integration with validation and animations
 * - Accessibility support
 * - Security features
 */

class AuthManager {
    constructor() {
        this.config = window.AuthConfig || {};
        this.currentForm = null;
        this.loadingStates = new Map();
        this.formStates = new Map();
        
        this.initializeAuth();
        this.bindEvents();
    }

    /**
     * Initialize authentication system
     */
    initializeAuth() {
        this.updateCSRFToken();
        this.setupFormHandlers();
        this.setupModalHandlers();
        this.setupTimers();
        this.handleInitialState();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.auth-form')) {
                this.handleFormSubmit(e);
            }
        });

        // CSRF token refresh
        setInterval(() => {
            this.updateCSRFToken();
        }, 30 * 60 * 1000); // Refresh every 30 minutes

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.handleEscapeKey();
            }
        });

        // Window beforeunload for form protection
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    /**
     * Handle form submission
     */
    async handleFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formId = form.id;
        
        // Validate form if validation system is available
        if (window.authValidation && !window.authValidation.validateForm(form)) {
            return false;
        }

        // Show loading state
        this.showFormLoading(form);

        try {
            // Prepare form data
            const formData = new FormData(form);
            
            // Add additional security headers
            const requestOptions = {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            };

            // Submit form
            const response = await fetch(form.action, requestOptions);
            const result = await this.parseResponse(response);

            // Handle response
            await this.handleFormResponse(form, result);

        } catch (error) {
            console.error('Form submission error:', error);
            this.handleFormError(form, error);
        } finally {
            this.hideFormLoading(form);
        }
    }

    /**
     * Parse response based on content type
     */
    async parseResponse(response) {
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            // Handle HTML response (redirect or error page)
            const text = await response.text();
            
            if (response.redirected || response.status === 302) {
                return { 
                    success: true, 
                    redirect: response.url || window.location.href 
                };
            }
            
            return { 
                success: response.ok, 
                html: text,
                status: response.status 
            };
        }
    }

    /**
     * Handle form response
     */
    async handleFormResponse(form, result) {
        if (result.success) {
            await this.handleSuccessResponse(form, result);
        } else {
            this.handleErrorResponse(form, result);
        }
    }

    /**
     * Handle successful form submission
     */
    async handleSuccessResponse(form, result) {
        const formId = form.id;

        // Show success animation if available
        if (window.authAnimations) {
            window.authAnimations.successFeedback(
                form.parentElement, 
                result.message || 'Opération réussie'
            );
        }

        // Handle specific form success actions
        switch (formId) {
            case 'loginForm':
                this.handleLoginSuccess(result);
                break;
                
            case 'forgotPasswordForm':
                this.handleForgotPasswordSuccess(result);
                break;
                
            case 'resetPasswordForm':
                this.handleResetPasswordSuccess(result);
                break;
                
            case 'verify2FAForm':
                this.handle2FASuccess(result);
                break;
                
            case 'resendVerificationForm':
                this.handleResendVerificationSuccess(result);
                break;
                
            default:
                this.handleGenericSuccess(result);
        }
    }

    /**
     * Handle login success
     */
    handleLoginSuccess(result) {
        // Clear any stored form data
        this.clearFormData('loginForm');
        
        // Show success message
        this.showMessage('Connexion réussie ! Redirection en cours...', 'success');
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = result.redirect || '/dashboard';
        }, 1500);
    }

    /**
     * Handle forgot password success
     */
    handleForgotPasswordSuccess(result) {
        // Show success modal
        const modal = document.getElementById('success-modal');
        if (modal && window.authAnimations) {
            window.authAnimations.showModal(modal);
        }
        
        // Start resend timer
        this.startResendTimer(60);
    }

    /**
     * Handle reset password success
     */
    handleResetPasswordSuccess(result) {
        // Show success modal
        const modal = document.getElementById('success-modal');
        if (modal && window.authAnimations) {
            window.authAnimations.showModal(modal);
        }
        
        // Clear form
        const form = document.getElementById('resetPasswordForm');
        if (form) {
            form.reset();
            if (window.authValidation) {
                window.authValidation.clearValidation(form);
            }
        }
    }

    /**
     * Handle 2FA success
     */
    handle2FASuccess(result) {
        if (result.setup_complete) {
            // Show recovery codes modal for initial setup
            const modal = document.getElementById('recovery-codes-modal');
            if (modal && window.authAnimations) {
                window.authAnimations.showModal(modal);
            }
        } else {
            // Regular 2FA verification success
            this.showMessage('Vérification réussie ! Redirection en cours...', 'success');
            setTimeout(() => {
                window.location.href = result.redirect || '/dashboard';
            }, 1500);
        }
    }

    /**
     * Handle resend verification success
     */
    handleResendVerificationSuccess(result) {
        this.showMessage('Email de vérification envoyé avec succès', 'success');
        this.startResendTimer(60);
    }

    /**
     * Handle generic success
     */
    handleGenericSuccess(result) {
        if (result.redirect) {
            window.location.href = result.redirect;
        } else if (result.message) {
            this.showMessage(result.message, 'success');
        }
    }

    /**
     * Handle error response
     */
    handleErrorResponse(form, result) {
        // Show error message
        let errorMessage = 'Une erreur est survenue. Veuillez réessayer.';
        
        if (result.errors) {
            if (Array.isArray(result.errors)) {
                errorMessage = result.errors.join('<br>');
            } else if (typeof result.errors === 'object') {
                errorMessage = Object.values(result.errors).join('<br>');
            } else {
                errorMessage = result.errors;
            }
        } else if (result.message) {
            errorMessage = result.message;
        }

        this.showMessage(errorMessage, 'error');

        // Handle field-specific errors
        if (result.field_errors && window.authValidation) {
            Object.entries(result.field_errors).forEach(([field, message]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    window.authValidation.updateInputState(input, {
                        valid: false,
                        message: message
                    }, true);
                }
            });
        }
    }

    /**
     * Handle form error (network, etc.)
     */
    handleFormError(form, error) {
        console.error('Form submission error:', error);
        
        let message = 'Erreur de connexion. Vérifiez votre connexion internet et réessayez.';
        
        if (error.name === 'AbortError') {
            message = 'La requête a été annulée. Veuillez réessayer.';
        } else if (error.message) {
            message = error.message;
        }
        
        this.showMessage(message, 'error');
    }

    /**
     * Show form loading state
     */
    showFormLoading(form) {
        const formId = form.id;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Disable form
        form.style.pointerEvents = 'none';
        
        // Update submit button
        if (submitButton) {
            submitButton.disabled = true;
            const btnText = submitButton.querySelector('.btn-text');
            const btnLoading = submitButton.querySelector('.btn-loading');
            
            if (btnText) btnText.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'flex';
        }

        // Show loading overlay if available
        if (window.authAnimations) {
            window.authAnimations.showLoading(
                form.closest('.auth-form-container'),
                submitButton?.getAttribute('data-loading-text') || 'Chargement...'
            );
        }

        this.loadingStates.set(formId, true);
    }

    /**
     * Hide form loading state
     */
    hideFormLoading(form) {
        const formId = form.id;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Re-enable form
        form.style.pointerEvents = '';
        
        // Update submit button
        if (submitButton) {
            submitButton.disabled = false;
            const btnText = submitButton.querySelector('.btn-text');
            const btnLoading = submitButton.querySelector('.btn-loading');
            
            if (btnText) btnText.style.display = 'flex';
            if (btnLoading) btnLoading.style.display = 'none';
        }

        // Hide loading overlay if available
        if (window.authAnimations) {
            window.authAnimations.hideLoading(
                form.closest('.auth-form-container')
            );
        }

        this.loadingStates.delete(formId);
    }

    /**
     * Show message to user
     */
    showMessage(message, type = 'info', duration = 5000) {
        // Try to use existing message container first
        let messageContainer = document.querySelector('.auth-messages');
        
        if (!messageContainer) {
            // Create new message container
            messageContainer = document.createElement('div');
            messageContainer.className = 'auth-messages';
            messageContainer.setAttribute('role', 'alert');
            messageContainer.setAttribute('aria-live', 'assertive');
            
            const container = document.querySelector('.auth-form-container');
            if (container) {
                container.insertBefore(messageContainer, container.firstChild);
            } else {
                document.body.appendChild(messageContainer);
            }
        }

        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `alert alert-${type}`;
        messageEl.innerHTML = `
            <i class="fas fa-${this.getMessageIcon(type)}" aria-hidden="true"></i>
            <span>${message}</span>
        `;

        // Add to container
        messageContainer.appendChild(messageEl);

        // Animate if available
        if (window.authAnimations) {
            window.authAnimations.showAlert(messageEl, type);
        } else {
            messageEl.style.display = 'block';
        }

        // Auto-remove after duration (except for errors)
        if (type !== 'error' && duration > 0) {
            setTimeout(() => {
                if (messageEl.parentNode) {
                    messageEl.parentNode.removeChild(messageEl);
                }
            }, duration);
        }
    }

    /**
     * Get icon for message type
     */
    getMessageIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Setup modal handlers
     */
    setupModalHandlers() {
        // Modal close handlers
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close, [data-dismiss="modal"]')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.hideModal(modal);
                }
            }
            
            // Close modal on overlay click
            if (e.target.matches('.modal-overlay')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.hideModal(modal);
                }
            }
        });

        // Recovery codes modal specific handlers
        const codesModal = document.getElementById('recovery-codes-modal');
        if (codesModal) {
            this.setupRecoveryCodesModal(codesModal);
        }
    }

    /**
     * Setup recovery codes modal
     */
    setupRecoveryCodesModal(modal) {
        const downloadBtn = modal.querySelector('.download-codes');
        const printBtn = modal.querySelector('.print-codes');
        const copyBtn = modal.querySelector('.copy-codes');
        const confirmCheckbox = modal.querySelector('#codes-saved-confirm');
        const finishBtn = modal.querySelector('#finish-setup');

        // Download codes
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                this.downloadRecoveryCodes();
            });
        }

        // Print codes
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                this.printRecoveryCodes();
            });
        }

        // Copy codes
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                this.copyRecoveryCodes();
            });
        }

        // Enable finish button when checkbox is checked
        if (confirmCheckbox && finishBtn) {
            confirmCheckbox.addEventListener('change', () => {
                finishBtn.disabled = !confirmCheckbox.checked;
            });
        }

        // Finish setup
        if (finishBtn) {
            finishBtn.addEventListener('click', () => {
                this.finishSetup();
            });
        }
    }

    /**
     * Download recovery codes
     */
    downloadRecoveryCodes() {
        const codes = this.getRecoveryCodes();
        const content = `GestionMySoutenance - Codes de récupération\n\n${codes.join('\n')}\n\nImportant:\n- Chaque code ne peut être utilisé qu'une fois\n- Gardez-les en lieu sûr\n- Ne les partagez avec personne`;
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'gestionsoutenance-recovery-codes.txt';
        a.click();
        
        URL.revokeObjectURL(url);
    }

    /**
     * Print recovery codes
     */
    printRecoveryCodes() {
        const codes = this.getRecoveryCodes();
        const printContent = `
            <html>
                <head><title>Codes de récupération - GestionMySoutenance</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h1>GestionMySoutenance - Codes de récupération</h1>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0;">
                        ${codes.map((code, index) => `<div>${index + 1}. ${code}</div>`).join('')}
                    </div>
                    <div style="margin-top: 40px; border: 1px solid #ccc; padding: 15px; background: #f9f9f9;">
                        <strong>Important:</strong>
                        <ul>
                            <li>Chaque code ne peut être utilisé qu'une seule fois</li>
                            <li>Gardez-les dans un endroit sûr et accessible</li>
                            <li>Ne les partagez avec personne</li>
                        </ul>
                    </div>
                </body>
            </html>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    /**
     * Copy recovery codes to clipboard
     */
    async copyRecoveryCodes() {
        const codes = this.getRecoveryCodes();
        const text = codes.join('\n');
        
        try {
            await navigator.clipboard.writeText(text);
            this.showMessage('Codes copiés dans le presse-papiers', 'success', 3000);
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showMessage('Codes copiés dans le presse-papiers', 'success', 3000);
        }
    }

    /**
     * Get recovery codes from modal
     */
    getRecoveryCodes() {
        const codes = [];
        const codeElements = document.querySelectorAll('.recovery-code');
        
        codeElements.forEach(el => {
            codes.push(el.textContent.trim());
        });
        
        return codes;
    }

    /**
     * Finish 2FA setup
     */
    finishSetup() {
        const modal = document.getElementById('recovery-codes-modal');
        if (modal) {
            this.hideModal(modal);
        }
        
        this.showMessage('Configuration 2FA terminée avec succès !', 'success');
        
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 2000);
    }

    /**
     * Hide modal
     */
    hideModal(modal) {
        if (window.authAnimations) {
            window.authAnimations.hideModal(modal);
        } else {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    /**
     * Setup timers (countdown, expiration, etc.)
     */
    setupTimers() {
        // 2FA timer
        const timerElement = document.getElementById('timer-value');
        if (timerElement && this.config.timerDuration) {
            this.startTimer(timerElement, this.config.timerDuration);
        }

        // Resend timer
        if (this.config.nextResendTime && this.config.nextResendTime > 0) {
            this.startResendTimer(this.config.nextResendTime);
        }
    }

    /**
     * Start countdown timer
     */
    startTimer(element, duration) {
        let remaining = duration;
        
        const updateTimer = () => {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            element.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (remaining <= 0) {
                clearInterval(interval);
                this.handleTimerExpired();
            }
            
            remaining--;
        };
        
        updateTimer();
        const interval = setInterval(updateTimer, 1000);
    }

    /**
     * Start resend timer
     */
    startResendTimer(duration) {
        const resendButton = document.querySelector('.resend-code');
        const timerElement = document.getElementById('resend-timer');
        
        if (!resendButton) return;
        
        let remaining = duration;
        resendButton.disabled = true;
        
        const updateTimer = () => {
            if (timerElement) {
                timerElement.textContent = remaining;
            }
            
            if (remaining <= 0) {
                clearInterval(interval);
                resendButton.disabled = false;
                const countdown = resendButton.querySelector('.resend-countdown');
                if (countdown) {
                    countdown.style.display = 'none';
                }
            }
            
            remaining--;
        };
        
        updateTimer();
        const interval = setInterval(updateTimer, 1000);
    }

    /**
     * Handle timer expiration
     */
    handleTimerExpired() {
        this.showMessage('Le code a expiré. Veuillez demander un nouveau code.', 'warning');
        
        // Disable form if needed
        const form = document.querySelector('.auth-form');
        if (form) {
            const inputs = form.querySelectorAll('input:not([type="hidden"])');
            inputs.forEach(input => input.disabled = true);
        }
    }

    /**
     * Handle initial state based on URL and session
     */
    handleInitialState() {
        // Handle URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        
        // Show appropriate messages based on URL params
        if (urlParams.get('verified') === '1') {
            this.showMessage('Email vérifié avec succès !', 'success');
        }
        
        if (urlParams.get('expired') === '1') {
            this.showMessage('Le lien a expiré. Veuillez demander un nouveau lien.', 'warning');
        }
        
        if (urlParams.get('invalid') === '1') {
            this.showMessage('Lien invalide. Veuillez vérifier le lien.', 'error');
        }
    }

    /**
     * Get CSRF token
     */
    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
               document.querySelector('input[name="csrf_token"]')?.value ||
               this.config.csrf_token;
    }

    /**
     * Update CSRF token
     */
    async updateCSRFToken() {
        try {
            const response = await fetch('/api/csrf-token');
            const data = await response.json();
            
            if (data.token) {
                // Update meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.token);
                }
                
                // Update form inputs
                document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                    input.value = data.token;
                });
                
                // Update config
                this.config.csrf_token = data.token;
            }
        } catch (error) {
            console.warn('Failed to update CSRF token:', error);
        }
    }

    /**
     * Handle escape key
     */
    handleEscapeKey() {
        // Close any open modals
        const openModal = document.querySelector('.modal[aria-hidden="false"]');
        if (openModal) {
            this.hideModal(openModal);
        }
    }

    /**
     * Check for unsaved changes
     */
    hasUnsavedChanges() {
        const forms = document.querySelectorAll('.auth-form');
        
        for (const form of forms) {
            const formData = new FormData(form);
            const hasData = Array.from(formData.entries()).some(([key, value]) => {
                return key !== 'csrf_token' && value.trim() !== '';
            });
            
            if (hasData) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Clear form data
     */
    clearFormData(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            if (window.authValidation) {
                window.authValidation.clearValidation(form);
            }
        }
    }

    /**
     * Setup form handlers for specific forms
     */
    setupFormHandlers() {
        // Handle form switching for unified auth page
        this.setupFormSwitching();
        
        // Handle 2FA code switching
        this.setup2FACodeSwitching();
        
        // Handle password toggles
        this.setupPasswordToggles();
    }

    /**
     * Setup form switching (for unified auth page compatibility)
     */
    setupFormSwitching() {
        // Links that switch between forms
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-switch-form]')) {
                e.preventDefault();
                const targetFormId = e.target.getAttribute('data-switch-form');
                const targetForm = document.getElementById(targetFormId);
                const currentForm = document.querySelector('.auth-form.active, .auth-form:not([style*="display: none"])');
                
                if (targetForm && window.authAnimations) {
                    window.authAnimations.formTransition(currentForm, targetForm);
                }
            }
        });
    }

    /**
     * Setup 2FA code form switching
     */
    setup2FACodeSwitching() {
        const useRecoveryBtn = document.querySelector('.use-recovery-code');
        const backTo2FABtn = document.querySelector('.back-to-2fa');
        const mainForm = document.getElementById('verify2FAForm');
        const recoveryForm = document.getElementById('recoveryCodeForm');

        if (useRecoveryBtn && recoveryForm) {
            useRecoveryBtn.addEventListener('click', () => {
                if (window.authAnimations) {
                    window.authAnimations.formTransition(mainForm, recoveryForm);
                } else {
                    if (mainForm) mainForm.style.display = 'none';
                    recoveryForm.style.display = 'block';
                }
            });
        }

        if (backTo2FABtn && mainForm) {
            backTo2FABtn.addEventListener('click', () => {
                if (window.authAnimations) {
                    window.authAnimations.formTransition(recoveryForm, mainForm);
                } else {
                    if (recoveryForm) recoveryForm.style.display = 'none';
                    mainForm.style.display = 'block';
                }
            });
        }
    }

    /**
     * Setup password toggles
     */
    setupPasswordToggles() {
        // Password visibility toggles are handled in auth-validation.js
        // This is just for additional specific behaviors if needed
    }
}

// Initialize authentication system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.authManager = new AuthManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthManager;
}

// Backward compatibility functions for legacy code
function showForm(formId) {
    console.warn('showForm() is deprecated. Use authManager.switchForm() instead.');
    if (window.authManager) {
        const form = document.getElementById(formId);
        if (form) {
            window.authManager.currentForm = form;
        }
    }
}

function showMessage(message, type = 'info') {
    if (window.authManager) {
        window.authManager.showMessage(message, type);
    }
}

function hideMessage() {
    const messages = document.querySelectorAll('.auth-messages .alert');
    messages.forEach(msg => {
        if (msg.parentNode) {
            msg.parentNode.removeChild(msg);
        }
    });
}

function resend2FA() {
    // Handle 2FA resend
    if (window.authManager) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/2fa/resend';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = window.authManager.getCSRFToken();
        form.appendChild(csrfInput);
        
        window.authManager.handleFormSubmit({ target: form, preventDefault: () => {} });
    }
}

// Export functions for global access
window.AuthPage = {
    showForm,
    showMessage,
    hideMessage,
    resend2FA
};

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

// === VALIDATION ===
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });

    // Validation spécifique pour les mots de passe
    if (form.id === 'resetForm') {
        const newPassword = form.querySelector('#new_password');
        const confirmPassword = form.querySelector('#confirm_password');

        if (newPassword.value !== confirmPassword.value) {
            showInputError(confirmPassword, 'Les mots de passe ne correspondent pas.');
            isValid = false;
        }
    }

    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();

    // Vérifier si le champ est requis et vide
    if (input.hasAttribute('required') && !value) {
        showInputError(input, 'Ce champ est obligatoire.');
        return false;
    }

    // Validation selon le type
    switch (input.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showInputError(input, 'Adresse email invalide.');
                return false;
            }
            break;

        case 'password':
            if (value && value.length < 6) {
                showInputError(input, 'Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            break;

        case 'text':
            if (input.id === 'verification_code' && value && !/^\d{6}$/.test(value)) {
                showInputError(input, 'Le code doit contenir exactement 6 chiffres.');
                return false;
            }
            break;
    }

    // Supprimer les erreurs si la validation passe
    clearInputError(input);
    return true;
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
        });
    });
}

function showInputError(input, message) {
    input.classList.add('error');

    // Supprimer l'ancien message d'erreur
    const existingError = input.parentNode.querySelector('.input-error');
    if (existingError) {
        existingError.remove();
    }

    // Ajouter le nouveau message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'input-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--accent-red)';
    errorDiv.style.fontSize = 'var(--font-size-xs)';
    errorDiv.style.marginTop = 'var(--spacing-xs)';

    input.parentNode.appendChild(errorDiv);
}

function clearInputError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentNode.querySelector('.input-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// === UTILITAIRES ===
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('message');
    if (messageDiv) {
        messageDiv.textContent = message;
        messageDiv.className = `message ${type} show`;

        // Masquer automatiquement après 5 secondes
        setTimeout(() => {
            hideMessage();
        }, 5000);
    }
}

function hideMessage() {
    const messageDiv = document.getElementById('message');
    if (messageDiv) {
        messageDiv.classList.remove('show');
    }
}

function showLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        submitButton.textContent = 'Chargement...';
    }

    form.classList.add('loading');
}

function hideLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.classList.remove('loading');

        // Restaurer le texte original
        const formId = form.id;
        switch (formId) {
            case 'loginForm':
                submitButton.textContent = 'Se connecter';
                break;
            case 'forgotForm':
                submitButton.textContent = 'Envoyer le lien';
                break;
            case 'resetForm':
                submitButton.textContent = 'Réinitialiser';
                break;
            case 'twoFactorForm':
                submitButton.textContent = 'Vérifier';
                break;
            case 'registerForm':
                submitButton.textContent = 'Créer le compte';
                break;
        }
    }

    form.classList.remove('loading');
}

function handleUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);

    // Afficher le formulaire approprié selon les paramètres URL
    if (urlParams.has('reset') && urlParams.has('token')) {
        const token = urlParams.get('token');
        document.getElementById('reset_token').value = token;
        showForm('resetForm');
    } else if (urlParams.has('2fa')) {
        showForm('twoFactorForm');
    } else if (urlParams.has('register')) {
        showForm('registerForm');
    }

    // Afficher les messages depuis les paramètres URL
    if (urlParams.has('message')) {
        const message = urlParams.get('message');
        const type = urlParams.get('type') || 'info';
        showMessage(decodeURIComponent(message), type);
    }
}

function setupEventListeners() {
    // Gérer l'appui sur Entrée pour la navigation entre formulaires
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            const activeForm = document.querySelector('.auth-form.active');
            if (activeForm) {
                const submitButton = activeForm.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.disabled) {
                    submitButton.click();
                }
            }
        }
    });

    // Gérer la fermeture des messages
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('message')) {
            hideMessage();
        }
    });
}

// === FONCTIONS UTILITAIRES POUR LES FORMULAIRES ===
function resend2FA() {
    fetch('backend/resend_2fa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Code de vérification renvoyé !', 'success');
            } else {
                showMessage(data.message || 'Erreur lors du renvoi du code.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Erreur de connexion.', 'error');
        });
}

// === GESTION DU RESPONSIVE ===
function handleResize() {
    // Ajuster la hauteur du carrousel sur mobile
    if (window.innerWidth <= 768) {
        const carouselSection = document.querySelector('.carousel-section');
        if (carouselSection) {
            const windowHeight = window.innerHeight;
            const maxHeight = windowHeight * 0.4; // 40% de la hauteur de l'écran
            carouselSection.style.height = `${Math.max(300, maxHeight)}px`;
        }
    }
}

// Écouter les changements de taille d'écran
window.addEventListener('resize', handleResize);

// === EXPORT POUR UTILISATION EXTERNE ===
window.AuthPage = {
    showForm,
    showMessage,
    hideMessage,
    resend2FA
};