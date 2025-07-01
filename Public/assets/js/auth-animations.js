/**
 * Authentication Animations with GSAP
 * Production-ready animation system for auth interface
 * 
 * Features:
 * - Page entrance animations
 * - Form validation feedback
 * - Loading states and transitions
 * - Success/error animations
 * - Micro-interactions
 * - Accessibility-aware (respects prefers-reduced-motion)
 */

class AuthAnimations {
    constructor() {
        this.isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.defaultDuration = this.isReducedMotion ? 0.01 : 0.3;
        this.slowDuration = this.isReducedMotion ? 0.01 : 0.6;
        this.fastDuration = this.isReducedMotion ? 0.01 : 0.15;
        
        this.initializeGSAP();
        this.bindEvents();
    }

    /**
     * Initialize GSAP settings and register plugins if available
     */
    initializeGSAP() {
        if (typeof gsap === 'undefined') {
            console.warn('GSAP not loaded, animations will be disabled');
            return;
        }

        // Set GSAP defaults
        gsap.defaults({
            duration: this.defaultDuration,
            ease: "power2.out"
        });

        // Register ScrollTrigger if available
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }
    }

    /**
     * Bind animation events to common elements
     */
    bindEvents() {
        // Form input focus animations
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('.form-input')) {
                this.animateInputFocus(e.target);
            }
        });

        // Form input blur animations
        document.addEventListener('focusout', (e) => {
            if (e.target.matches('.form-input')) {
                this.animateInputBlur(e.target);
            }
        });

        // Button hover animations
        document.addEventListener('mouseenter', (e) => {
            if (e.target.matches('.btn')) {
                this.animateButtonHover(e.target, true);
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (e.target.matches('.btn')) {
                this.animateButtonHover(e.target, false);
            }
        }, true);

        // Button click animations
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn') || e.target.closest('.btn')) {
                this.animateButtonClick(e.target.closest('.btn') || e.target);
            }
        });
    }

    /**
     * Animate page entrance
     */
    pageEntrance() {
        if (typeof gsap === 'undefined') return;

        const container = document.querySelector('.auth-layout-container');
        const header = document.querySelector('.auth-layout-header');
        const form = document.querySelector('.auth-form');
        const footer = document.querySelector('.form-footer');

        if (!container) return;

        // Set initial states
        gsap.set([header, form, footer], { 
            opacity: 0, 
            y: 30 
        });

        gsap.set(container, { 
            opacity: 0, 
            scale: 0.95 
        });

        // Animate entrance
        const tl = gsap.timeline();

        tl.to(container, {
            opacity: 1,
            scale: 1,
            duration: this.slowDuration,
            ease: "back.out(1.7)"
        })
        .to(header, {
            opacity: 1,
            y: 0,
            duration: this.defaultDuration,
            ease: "power2.out"
        }, "-=0.3")
        .to(form, {
            opacity: 1,
            y: 0,
            duration: this.defaultDuration,
            ease: "power2.out"
        }, "-=0.2")
        .to(footer, {
            opacity: 1,
            y: 0,
            duration: this.defaultDuration,
            ease: "power2.out"
        }, "-=0.1");

        return tl;
    }

    /**
     * Animate form transitions
     */
    formTransition(fromForm, toForm) {
        if (typeof gsap === 'undefined') {
            if (fromForm) fromForm.style.display = 'none';
            if (toForm) toForm.style.display = 'block';
            return;
        }

        const tl = gsap.timeline();

        if (fromForm) {
            tl.to(fromForm, {
                opacity: 0,
                x: -30,
                duration: this.fastDuration,
                ease: "power2.in",
                onComplete: () => {
                    fromForm.style.display = 'none';
                }
            });
        }

        if (toForm) {
            gsap.set(toForm, { 
                display: 'block', 
                opacity: 0, 
                x: 30 
            });

            tl.to(toForm, {
                opacity: 1,
                x: 0,
                duration: this.defaultDuration,
                ease: "power2.out"
            }, fromForm ? "-=0.1" : "0");
        }

        return tl;
    }

    /**
     * Animate input focus state
     */
    animateInputFocus(input) {
        if (typeof gsap === 'undefined') return;

        const wrapper = input.closest('.input-wrapper');
        const label = input.previousElementSibling;

        gsap.to(input, {
            scale: 1.02,
            duration: this.fastDuration,
            ease: "power2.out"
        });

        if (wrapper) {
            gsap.to(wrapper, {
                boxShadow: "0 0 0 3px rgba(59, 130, 246, 0.1)",
                duration: this.fastDuration
            });
        }

        if (label && label.matches('.form-label')) {
            gsap.to(label, {
                color: "#3b82f6",
                duration: this.fastDuration
            });
        }
    }

    /**
     * Animate input blur state
     */
    animateInputBlur(input) {
        if (typeof gsap === 'undefined') return;

        const wrapper = input.closest('.input-wrapper');
        const label = input.previousElementSibling;

        gsap.to(input, {
            scale: 1,
            duration: this.fastDuration,
            ease: "power2.out"
        });

        if (wrapper) {
            gsap.to(wrapper, {
                boxShadow: "none",
                duration: this.fastDuration
            });
        }

        if (label && label.matches('.form-label')) {
            gsap.to(label, {
                color: "",
                duration: this.fastDuration
            });
        }
    }

    /**
     * Animate button hover state
     */
    animateButtonHover(button, isHover) {
        if (typeof gsap === 'undefined') return;
        if (button.disabled) return;

        gsap.to(button, {
            scale: isHover ? 1.02 : 1,
            y: isHover ? -2 : 0,
            duration: this.fastDuration,
            ease: "power2.out"
        });
    }

    /**
     * Animate button click
     */
    animateButtonClick(button) {
        if (typeof gsap === 'undefined') return;
        if (button.disabled) return;

        gsap.to(button, {
            scale: 0.98,
            duration: 0.1,
            ease: "power2.out",
            yoyo: true,
            repeat: 1
        });
    }

    /**
     * Animate form validation error
     */
    validationError(input, message) {
        if (typeof gsap === 'undefined') {
            if (message) {
                const errorEl = document.getElementById(input.id + '_error');
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.style.display = 'block';
                }
            }
            return;
        }

        const wrapper = input.closest('.input-wrapper') || input.parentElement;
        const errorElement = document.getElementById(input.id + '_error');

        // Shake animation
        gsap.to(wrapper, {
            x: [-5, 5, -5, 5, 0],
            duration: 0.5,
            ease: "power2.out"
        });

        // Add error class with animation
        input.classList.add('error');
        gsap.to(input, {
            borderColor: "#ef4444",
            backgroundColor: "rgba(239, 68, 68, 0.05)",
            duration: this.fastDuration
        });

        // Show error message
        if (errorElement && message) {
            errorElement.textContent = message;
            gsap.set(errorElement, { 
                display: 'block', 
                opacity: 0, 
                y: -10 
            });
            gsap.to(errorElement, {
                opacity: 1,
                y: 0,
                duration: this.defaultDuration,
                ease: "power2.out"
            });
        }
    }

    /**
     * Animate form validation success
     */
    validationSuccess(input) {
        if (typeof gsap === 'undefined') {
            input.classList.remove('error');
            input.classList.add('valid');
            return;
        }

        const errorElement = document.getElementById(input.id + '_error');
        const statusIcon = input.parentElement.querySelector('.input-valid');

        // Remove error state
        input.classList.remove('error');
        input.classList.add('valid');

        gsap.to(input, {
            borderColor: "#10b981",
            backgroundColor: "rgba(16, 185, 129, 0.05)",
            duration: this.fastDuration
        });

        // Hide error message
        if (errorElement) {
            gsap.to(errorElement, {
                opacity: 0,
                y: -10,
                duration: this.fastDuration,
                onComplete: () => {
                    errorElement.style.display = 'none';
                }
            });
        }

        // Show success icon with bounce
        if (statusIcon) {
            gsap.set(statusIcon, { scale: 0 });
            gsap.to(statusIcon, {
                scale: 1,
                duration: this.defaultDuration,
                ease: "back.out(2)"
            });
        }
    }

    /**
     * Animate loading state
     */
    showLoading(container, message = 'Chargement...') {
        if (typeof gsap === 'undefined') {
            container.classList.add('loading');
            return;
        }

        container.classList.add('loading');

        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            const spinnerText = loadingOverlay.querySelector('p');
            if (spinnerText) spinnerText.textContent = message;

            gsap.set(loadingOverlay, { 
                display: 'flex', 
                opacity: 0 
            });
            gsap.to(loadingOverlay, {
                opacity: 1,
                duration: this.defaultDuration
            });

            // Animate spinner
            const spinner = loadingOverlay.querySelector('.fa-spin');
            if (spinner) {
                gsap.to(spinner, {
                    rotation: 360,
                    duration: 1,
                    repeat: -1,
                    ease: "none"
                });
            }
        }
    }

    /**
     * Hide loading state
     */
    hideLoading(container) {
        if (typeof gsap === 'undefined') {
            container.classList.remove('loading');
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) loadingOverlay.style.display = 'none';
            return;
        }

        container.classList.remove('loading');

        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            gsap.to(loadingOverlay, {
                opacity: 0,
                duration: this.defaultDuration,
                onComplete: () => {
                    loadingOverlay.style.display = 'none';
                }
            });
        }
    }

    /**
     * Animate success feedback
     */
    successFeedback(element, message) {
        if (typeof gsap === 'undefined') {
            if (message) alert(message);
            return;
        }

        // Create success message element
        const successEl = document.createElement('div');
        successEl.className = 'success-message';
        successEl.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;

        element.appendChild(successEl);

        // Animate entrance
        gsap.set(successEl, { 
            opacity: 0, 
            y: 20, 
            scale: 0.9 
        });

        gsap.to(successEl, {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: this.defaultDuration,
            ease: "back.out(1.7)"
        });

        // Auto-remove after 3 seconds
        gsap.to(successEl, {
            opacity: 0,
            y: -20,
            duration: this.defaultDuration,
            delay: 3,
            onComplete: () => {
                if (successEl.parentNode) {
                    successEl.parentNode.removeChild(successEl);
                }
            }
        });
    }

    /**
     * Animate password strength meter
     */
    passwordStrength(strengthValue, strengthText) {
        if (typeof gsap === 'undefined') return;

        const strengthBar = document.getElementById('strength-bar');
        const strengthTextEl = document.getElementById('strength-text');

        if (!strengthBar) return;

        const colors = {
            weak: '#ef4444',
            fair: '#f59e0b',
            good: '#3b82f6',
            strong: '#10b981'
        };

        const widths = {
            weak: '25%',
            fair: '50%',
            good: '75%',
            strong: '100%'
        };

        // Animate bar width and color
        gsap.to(strengthBar, {
            width: widths[strengthValue] || '0%',
            backgroundColor: colors[strengthValue] || '#e5e7eb',
            duration: this.defaultDuration,
            ease: "power2.out"
        });

        // Update text with color
        if (strengthTextEl) {
            gsap.to(strengthTextEl, {
                color: colors[strengthValue] || '#6b7280',
                duration: this.fastDuration,
                onStart: () => {
                    strengthTextEl.textContent = strengthText;
                    strengthTextEl.setAttribute('data-strength', strengthValue);
                }
            });
        }
    }

    /**
     * Animate 2FA code input progression
     */
    twoFactorCodeProgression(inputs, currentIndex) {
        if (typeof gsap === 'undefined') return;

        inputs.forEach((input, index) => {
            if (index < currentIndex) {
                // Filled inputs
                input.classList.add('filled');
                gsap.to(input, {
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    scale: 1,
                    duration: this.fastDuration
                });
            } else if (index === currentIndex) {
                // Current input
                gsap.to(input, {
                    scale: 1.1,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    duration: this.fastDuration,
                    ease: "back.out(1.7)"
                });
            } else {
                // Future inputs
                input.classList.remove('filled');
                gsap.to(input, {
                    scale: 1,
                    borderColor: '#e5e7eb',
                    backgroundColor: 'transparent',
                    duration: this.fastDuration
                });
            }
        });
    }

    /**
     * Animate modal entrance
     */
    showModal(modal) {
        if (typeof gsap === 'undefined') {
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            return;
        }

        const overlay = modal.querySelector('.modal-overlay');
        const content = modal.querySelector('.modal-content');

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        gsap.set([overlay, content], { opacity: 0 });
        gsap.set(content, { scale: 0.9, y: 20 });

        const tl = gsap.timeline();

        tl.to(overlay, {
            opacity: 1,
            duration: this.defaultDuration
        })
        .to(content, {
            opacity: 1,
            scale: 1,
            y: 0,
            duration: this.defaultDuration,
            ease: "back.out(1.7)"
        }, "-=0.2");

        return tl;
    }

    /**
     * Animate modal exit
     */
    hideModal(modal) {
        if (typeof gsap === 'undefined') {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            return;
        }

        const overlay = modal.querySelector('.modal-overlay');
        const content = modal.querySelector('.modal-content');

        const tl = gsap.timeline({
            onComplete: () => {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        });

        tl.to(content, {
            opacity: 0,
            scale: 0.9,
            y: 20,
            duration: this.fastDuration,
            ease: "power2.in"
        })
        .to(overlay, {
            opacity: 0,
            duration: this.fastDuration
        }, "-=0.1");

        return tl;
    }

    /**
     * Animate alert messages
     */
    showAlert(alertElement, type = 'info') {
        if (typeof gsap === 'undefined') {
            alertElement.style.display = 'block';
            return;
        }

        gsap.set(alertElement, {
            display: 'block',
            opacity: 0,
            x: -20,
            scale: 0.95
        });

        gsap.to(alertElement, {
            opacity: 1,
            x: 0,
            scale: 1,
            duration: this.defaultDuration,
            ease: "back.out(1.7)"
        });

        // Auto-hide after 5 seconds for non-error alerts
        if (type !== 'error') {
            gsap.to(alertElement, {
                opacity: 0,
                x: 20,
                duration: this.defaultDuration,
                delay: 5,
                onComplete: () => {
                    alertElement.style.display = 'none';
                }
            });
        }
    }

    /**
     * Create and animate confetti for success states
     */
    confetti(container) {
        if (typeof gsap === 'undefined') return;

        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
        const confettiCount = 50;

        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: absolute;
                width: 8px;
                height: 8px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                border-radius: 2px;
                top: -10px;
                left: ${Math.random() * 100}%;
                pointer-events: none;
                z-index: 1000;
            `;

            container.appendChild(confetti);

            gsap.to(confetti, {
                y: window.innerHeight + 20,
                rotation: 360 * (Math.random() > 0.5 ? 1 : -1),
                x: `+=${(Math.random() - 0.5) * 200}`,
                opacity: 0,
                duration: 3 + Math.random() * 2,
                ease: "power2.out",
                onComplete: () => {
                    if (confetti.parentNode) {
                        confetti.parentNode.removeChild(confetti);
                    }
                }
            });
        }
    }
}

// Initialize animations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.authAnimations = new AuthAnimations();
    
    // Trigger page entrance animation
    if (window.authAnimations) {
        window.authAnimations.pageEntrance();
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthAnimations;
}