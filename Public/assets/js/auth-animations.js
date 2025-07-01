// ===== AUTH-ANIMATIONS.JS - GSAP animations for authentication pages =====

class AuthAnimations {
    constructor() {
        this.timeline = null;
        this.isGSAPLoaded = typeof gsap !== 'undefined';
        
        if (!this.isGSAPLoaded) {
            console.warn('GSAP not loaded. Animations will be disabled.');
            return;
        }
        
        this.init();
    }
    
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupPageAnimations();
            this.setupFormAnimations();
            this.setupInteractiveAnimations();
            this.setupErrorAnimations();
            this.setupSuccessAnimations();
            this.setupLoadingAnimations();
        });
    }
    
    // === PAGE ENTRANCE ANIMATIONS ===
    setupPageAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // Main container entrance
        const container = document.querySelector('.auth-layout-container');
        if (container) {
            gsap.set(container, { opacity: 0, y: 50, scale: 0.95 });
            gsap.to(container, {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.8,
                ease: "power3.out",
                delay: 0.1
            });
        }
        
        // Header animation
        const header = document.querySelector('.auth-layout-header');
        if (header) {
            gsap.set(header, { opacity: 0, y: -20 });
            gsap.to(header, {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power2.out",
                delay: 0.3
            });
        }
        
        // Form content animation
        const formContent = document.querySelector('.auth-form-content');
        if (formContent) {
            const elements = formContent.querySelectorAll('.auth-form-group, .auth-btn, .auth-message');
            gsap.set(elements, { opacity: 0, y: 20 });
            gsap.to(elements, {
                opacity: 1,
                y: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: "power2.out",
                delay: 0.5
            });
        }
        
        // Logo animation with typewriter effect
        this.animateLogo();
    }
    
    animateLogo() {
        if (!this.isGSAPLoaded) return;
        
        const logo = document.querySelector('.auth-layout-logo');
        if (logo) {
            const text = logo.textContent;
            logo.innerHTML = '';
            
            // Create spans for each character
            text.split('').forEach(char => {
                const span = document.createElement('span');
                span.textContent = char === ' ' ? '\u00A0' : char;
                span.style.opacity = '0';
                logo.appendChild(span);
            });
            
            const chars = logo.querySelectorAll('span');
            gsap.to(chars, {
                opacity: 1,
                duration: 0.05,
                stagger: 0.03,
                ease: "none",
                delay: 0.2
            });
        }
    }
    
    // === FORM ANIMATIONS ===
    setupFormAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // Form switching animations
        const forms = document.querySelectorAll('.auth-form, .setup-step');
        forms.forEach(form => {
            // Set initial state for hidden forms
            if (form.classList.contains('hidden')) {
                gsap.set(form, { opacity: 0, x: 30 });
            }
        });
        
        // Input focus animations
        const inputs = document.querySelectorAll('.auth-form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                this.animateInputFocus(input);
            });
            
            input.addEventListener('blur', () => {
                this.animateInputBlur(input);
            });
        });
        
        // Button hover animations
        const buttons = document.querySelectorAll('.auth-btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', () => {
                this.animateButtonHover(button);
            });
            
            button.addEventListener('mouseleave', () => {
                this.animateButtonLeave(button);
            });
        });
    }
    
    animateFormSwitch(fromForm, toForm) {
        if (!this.isGSAPLoaded) return;
        
        const tl = gsap.timeline();
        
        if (fromForm) {
            tl.to(fromForm, {
                opacity: 0,
                x: -30,
                duration: 0.3,
                ease: "power2.in",
                onComplete: () => {
                    fromForm.classList.add('hidden');
                }
            });
        }
        
        if (toForm) {
            tl.set(toForm, { opacity: 0, x: 30 })
              .call(() => {
                  toForm.classList.remove('hidden');
              })
              .to(toForm, {
                  opacity: 1,
                  x: 0,
                  duration: 0.4,
                  ease: "power2.out"
              });
        }
        
        return tl;
    }
    
    animateInputFocus(input) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(input, {
            scale: 1.02,
            boxShadow: "0 0 0 3px rgba(59, 130, 246, 0.1)",
            duration: 0.2,
            ease: "power2.out"
        });
        
        // Animate label if present
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) {
            gsap.to(label, {
                color: "#3b82f6",
                duration: 0.2,
                ease: "power2.out"
            });
        }
    }
    
    animateInputBlur(input) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(input, {
            scale: 1,
            boxShadow: "0 0 0 0px rgba(59, 130, 246, 0)",
            duration: 0.2,
            ease: "power2.out"
        });
        
        // Reset label color
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) {
            gsap.to(label, {
                color: "#374151",
                duration: 0.2,
                ease: "power2.out"
            });
        }
    }
    
    animateButtonHover(button) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(button, {
            y: -2,
            scale: 1.02,
            boxShadow: "0 10px 25px -5px rgba(0, 0, 0, 0.2)",
            duration: 0.2,
            ease: "power2.out"
        });
    }
    
    animateButtonLeave(button) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(button, {
            y: 0,
            scale: 1,
            boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
            duration: 0.2,
            ease: "power2.out"
        });
    }
    
    // === INTERACTIVE ANIMATIONS ===
    setupInteractiveAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // 2FA input animations
        const twoFAInputs = document.querySelectorAll('.auth-2fa-input');
        twoFAInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value) {
                    this.animate2FAInputFill(input);
                }
            });
        });
        
        // Password toggle animations
        const passwordToggles = document.querySelectorAll('.auth-password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                this.animatePasswordToggle(toggle);
            });
        });
        
        // Checkbox animations
        const checkboxes = document.querySelectorAll('.auth-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.animateCheckbox(checkbox);
            });
        });
        
        // QR Code animation
        const qrCode = document.querySelector('.auth-qr-code img');
        if (qrCode) {
            this.animateQRCode(qrCode);
        }
    }
    
    animate2FAInputFill(input) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(input, {
            scale: 1.1,
            backgroundColor: "#eff6ff",
            duration: 0.2,
            ease: "power2.out",
            yoyo: true,
            repeat: 1
        });
    }
    
    animatePasswordToggle(toggle) {
        if (!this.isGSAPLoaded) return;
        
        const icon = toggle.querySelector('i');
        if (icon) {
            gsap.to(icon, {
                rotationY: 180,
                duration: 0.3,
                ease: "power2.inOut"
            });
        }
    }
    
    animateCheckbox(checkbox) {
        if (!this.isGSAPLoaded) return;
        
        if (checkbox.checked) {
            gsap.to(checkbox, {
                scale: 1.2,
                duration: 0.1,
                ease: "power2.out",
                yoyo: true,
                repeat: 1
            });
        }
    }
    
    animateQRCode(qrCode) {
        if (!this.isGSAPLoaded) return;
        
        gsap.set(qrCode, { opacity: 0, scale: 0.8, rotation: -5 });
        gsap.to(qrCode, {
            opacity: 1,
            scale: 1,
            rotation: 0,
            duration: 0.8,
            ease: "back.out(1.7)",
            delay: 0.5
        });
    }
    
    // === ERROR ANIMATIONS ===
    setupErrorAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // Watch for error messages being added
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('auth-form-error')) {
                            this.animateErrorMessage(node);
                        }
                        
                        if (node.classList && node.classList.contains('auth-message') && 
                            node.classList.contains('error')) {
                            this.animateErrorMessage(node);
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    animateErrorMessage(errorElement) {
        if (!this.isGSAPLoaded) return;
        
        gsap.set(errorElement, { opacity: 0, y: -10 });
        gsap.to(errorElement, {
            opacity: 1,
            y: 0,
            duration: 0.3,
            ease: "power2.out"
        });
        
        // Shake animation
        gsap.to(errorElement, {
            x: -5,
            duration: 0.1,
            repeat: 5,
            yoyo: true,
            ease: "power2.inOut",
            delay: 0.2
        });
    }
    
    animateFormError(form) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(form, {
            x: -10,
            duration: 0.1,
            repeat: 5,
            yoyo: true,
            ease: "power2.inOut"
        });
    }
    
    // === SUCCESS ANIMATIONS ===
    setupSuccessAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // Watch for success messages
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.classList) {
                        if (node.classList.contains('auth-message') && 
                            node.classList.contains('success')) {
                            this.animateSuccessMessage(node);
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    animateSuccessMessage(successElement) {
        if (!this.isGSAPLoaded) return;
        
        gsap.set(successElement, { opacity: 0, scale: 0.8 });
        gsap.to(successElement, {
            opacity: 1,
            scale: 1,
            duration: 0.5,
            ease: "back.out(1.7)"
        });
        
        // Add sparkle effect
        this.createSparkleEffect(successElement);
    }
    
    createSparkleEffect(element) {
        if (!this.isGSAPLoaded) return;
        
        const sparkles = [];
        for (let i = 0; i < 6; i++) {
            const sparkle = document.createElement('div');
            sparkle.style.cssText = `
                position: absolute;
                width: 6px;
                height: 6px;
                background: linear-gradient(45deg, #ffd700, #ffed4a);
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
            `;
            element.appendChild(sparkle);
            sparkles.push(sparkle);
            
            const angle = (i / 6) * Math.PI * 2;
            const distance = 40 + Math.random() * 20;
            
            gsap.set(sparkle, {
                x: 0,
                y: 0,
                opacity: 0
            });
            
            gsap.to(sparkle, {
                x: Math.cos(angle) * distance,
                y: Math.sin(angle) * distance,
                opacity: 1,
                duration: 0.3,
                ease: "power2.out",
                delay: i * 0.05
            });
            
            gsap.to(sparkle, {
                opacity: 0,
                scale: 0,
                duration: 0.3,
                ease: "power2.in",
                delay: 0.5 + i * 0.05,
                onComplete: () => {
                    sparkle.remove();
                }
            });
        }
    }
    
    animateFormSuccess(form) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(form, {
            scale: 1.02,
            duration: 0.2,
            ease: "power2.out",
            yoyo: true,
            repeat: 1
        });
    }
    
    // === LOADING ANIMATIONS ===
    setupLoadingAnimations() {
        if (!this.isGSAPLoaded) return;
        
        // Animate loading spinners
        const spinners = document.querySelectorAll('.auth-spinner');
        spinners.forEach(spinner => {
            gsap.to(spinner, {
                rotation: 360,
                duration: 1,
                ease: "none",
                repeat: -1
            });
        });
        
        // Button loading state animations
        const buttons = document.querySelectorAll('.auth-btn');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                if (button.classList.contains('loading')) {
                    this.animateButtonLoading(button);
                }
            });
        });
    }
    
    animateButtonLoading(button) {
        if (!this.isGSAPLoaded) return;
        
        const spinner = button.querySelector('.auth-spinner');
        if (spinner) {
            gsap.to(spinner, {
                rotation: 360,
                duration: 1,
                ease: "none",
                repeat: -1
            });
        }
        
        // Pulse effect
        gsap.to(button, {
            scale: 0.98,
            duration: 0.8,
            ease: "power2.inOut",
            yoyo: true,
            repeat: -1
        });
    }
    
    // === UTILITY METHODS ===
    fadeIn(element, duration = 0.5, delay = 0) {
        if (!this.isGSAPLoaded) return;
        
        gsap.set(element, { opacity: 0 });
        gsap.to(element, {
            opacity: 1,
            duration: duration,
            delay: delay,
            ease: "power2.out"
        });
    }
    
    fadeOut(element, duration = 0.3) {
        if (!this.isGSAPLoaded) return;
        
        return gsap.to(element, {
            opacity: 0,
            duration: duration,
            ease: "power2.in"
        });
    }
    
    slideIn(element, direction = 'left', duration = 0.5) {
        if (!this.isGSAPLoaded) return;
        
        const startPos = direction === 'left' ? { x: -50 } : 
                        direction === 'right' ? { x: 50 } : 
                        direction === 'up' ? { y: -50 } : { y: 50 };
        
        gsap.set(element, { ...startPos, opacity: 0 });
        gsap.to(element, {
            x: 0,
            y: 0,
            opacity: 1,
            duration: duration,
            ease: "power2.out"
        });
    }
    
    bounce(element, intensity = 1) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(element, {
            y: -10 * intensity,
            duration: 0.3,
            ease: "power2.out",
            yoyo: true,
            repeat: 1
        });
    }
    
    shake(element, intensity = 1) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(element, {
            x: -5 * intensity,
            duration: 0.1,
            repeat: 5,
            yoyo: true,
            ease: "power2.inOut"
        });
    }
    
    pulse(element, scale = 1.05, duration = 0.5) {
        if (!this.isGSAPLoaded) return;
        
        gsap.to(element, {
            scale: scale,
            duration: duration,
            ease: "power2.inOut",
            yoyo: true,
            repeat: -1
        });
    }
    
    // === PUBLIC API ===
    getTimeline() {
        return this.timeline;
    }
    
    pauseAnimations() {
        if (this.isGSAPLoaded) {
            gsap.globalTimeline.pause();
        }
    }
    
    resumeAnimations() {
        if (this.isGSAPLoaded) {
            gsap.globalTimeline.resume();
        }
    }
    
    killAllAnimations() {
        if (this.isGSAPLoaded) {
            gsap.killTweensOf("*");
        }
    }
}

// Initialize animations
const authAnimations = new AuthAnimations();

// Export for global use
window.AuthAnimations = AuthAnimations;
window.authAnimations = authAnimations;