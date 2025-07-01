/**
 * auth-animations.js - Animations GSAP pour les pages d'authentification
 * GestionMySoutenance - Système d'authentification moderne
 */

// ===== CONFIGURATION GLOBALE =====
const ANIMATIONS_CONFIG = {
    duration: {
        fast: 0.3,
        normal: 0.6,
        slow: 1.0
    },
    easing: {
        smooth: 'power2.out',
        bounce: 'back.out(1.7)',
        elastic: 'elastic.out(1, 0.5)'
    },
    delays: {
        stagger: 0.1,
        sequence: 0.2
    }
};

// ===== ANIMATIONS D'ENTRÉE DE PAGE =====

/**
 * Animation d'entrée pour les cartes d'authentification
 * @param {string} cardSelector - Sélecteur de la carte principale
 * @param {Object} options - Options d'animation
 */
function animateAuthCardEntrance(cardSelector = '.auth-card', options = {}) {
    const defaults = {
        duration: ANIMATIONS_CONFIG.duration.slow,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        y: 50,
        opacity: 0,
        scale: 0.95
    };
    
    const config = { ...defaults, ...options };
    
    // Timeline principale
    const tl = gsap.timeline();
    
    tl.from(cardSelector, {
        duration: config.duration,
        y: config.y,
        opacity: config.opacity,
        scale: config.scale,
        ease: config.ease
    });
    
    return tl;
}

/**
 * Animation en cascade pour les éléments de formulaire
 * @param {string} formSelector - Sélecteur du formulaire
 * @param {Object} options - Options d'animation
 */
function animateFormElements(formSelector = '.auth-form', options = {}) {
    const defaults = {
        duration: ANIMATIONS_CONFIG.duration.normal,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        stagger: ANIMATIONS_CONFIG.delays.stagger,
        y: 30,
        opacity: 0
    };
    
    const config = { ...defaults, ...options };
    
    const elements = `${formSelector} .form-control, ${formSelector} .form-group, ${formSelector} .btn`;
    
    gsap.from(elements, {
        duration: config.duration,
        y: config.y,
        opacity: config.opacity,
        ease: config.ease,
        stagger: config.stagger
    });
}

/**
 * Animation d'apparition pour l'en-tête d'authentification
 * @param {string} headerSelector - Sélecteur de l'en-tête
 */
function animateAuthHeader(headerSelector = '.auth-header') {
    gsap.from(headerSelector, {
        duration: ANIMATIONS_CONFIG.duration.normal,
        y: -30,
        opacity: 0,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        delay: 0.2
    });
    
    // Animation du logo
    gsap.from(`${headerSelector} .logo-img`, {
        duration: ANIMATIONS_CONFIG.duration.slow,
        rotation: -180,
        scale: 0,
        ease: ANIMATIONS_CONFIG.easing.bounce,
        delay: 0.4
    });
    
    // Animation du texte du logo
    gsap.from(`${headerSelector} .logo-title, ${headerSelector} .logo-subtitle`, {
        duration: ANIMATIONS_CONFIG.duration.normal,
        x: -20,
        opacity: 0,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        stagger: 0.1,
        delay: 0.6
    });
}

// ===== ANIMATIONS D'INTERACTION =====

/**
 * Animation de focus pour les champs de saisie
 * @param {HTMLElement} input - Élément input
 */
function animateInputFocus(input) {
    gsap.to(input, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        scale: 1.02,
        y: -2,
        ease: ANIMATIONS_CONFIG.easing.smooth
    });
    
    // Animation de l'icône si présente
    const icon = input.parentNode.querySelector('.input-icon, .material-icons');
    if (icon) {
        gsap.to(icon, {
            duration: ANIMATIONS_CONFIG.duration.fast,
            scale: 1.1,
            color: 'var(--primary-blue)',
            ease: ANIMATIONS_CONFIG.easing.smooth
        });
    }
}

/**
 * Animation de blur pour les champs de saisie
 * @param {HTMLElement} input - Élément input
 */
function animateInputBlur(input) {
    gsap.to(input, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        scale: 1,
        y: 0,
        ease: ANIMATIONS_CONFIG.easing.smooth
    });
    
    // Restaurer l'icône
    const icon = input.parentNode.querySelector('.input-icon, .material-icons');
    if (icon) {
        gsap.to(icon, {
            duration: ANIMATIONS_CONFIG.duration.fast,
            scale: 1,
            color: 'var(--text-secondary)',
            ease: ANIMATIONS_CONFIG.easing.smooth
        });
    }
}

/**
 * Animation de validation réussie pour un champ
 * @param {HTMLElement} input - Élément input
 */
function animateInputSuccess(input) {
    // Effet de validation réussie
    gsap.to(input, {
        duration: 0.2,
        scale: 1.05,
        ease: ANIMATIONS_CONFIG.easing.bounce,
        yoyo: true,
        repeat: 1
    });
    
    // Effet de particules (optionnel)
    createSuccessParticles(input);
}

/**
 * Animation d'erreur de validation pour un champ
 * @param {HTMLElement} input - Élément input
 */
function animateInputError(input) {
    // Effet de shake
    gsap.to(input, {
        duration: 0.1,
        x: -5,
        ease: 'power2.inOut',
        repeat: 5,
        yoyo: true,
        onComplete: () => {
            gsap.set(input, { x: 0 });
        }
    });
    
    // Effet de pulse rouge
    gsap.fromTo(input, {
        boxShadow: '0 0 0 0px rgba(239, 68, 68, 0.4)'
    }, {
        duration: 0.6,
        boxShadow: '0 0 0 10px rgba(239, 68, 68, 0)',
        ease: 'power2.out'
    });
}

// ===== ANIMATIONS DE BOUTONS =====

/**
 * Animation de hover pour les boutons
 * @param {HTMLElement} button - Élément bouton
 */
function animateButtonHover(button) {
    gsap.to(button, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        y: -3,
        scale: 1.02,
        boxShadow: '0 10px 25px rgba(0, 0, 0, 0.15)',
        ease: ANIMATIONS_CONFIG.easing.smooth
    });
}

/**
 * Animation de sortie de hover pour les boutons
 * @param {HTMLElement} button - Élément bouton
 */
function animateButtonLeave(button) {
    gsap.to(button, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        y: 0,
        scale: 1,
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
        ease: ANIMATIONS_CONFIG.easing.smooth
    });
}

/**
 * Animation de clic pour les boutons
 * @param {HTMLElement} button - Élément bouton
 */
function animateButtonClick(button) {
    gsap.to(button, {
        duration: 0.1,
        scale: 0.98,
        ease: 'power2.out',
        onComplete: () => {
            gsap.to(button, {
                duration: 0.2,
                scale: 1,
                ease: ANIMATIONS_CONFIG.easing.bounce
            });
        }
    });
}

/**
 * Animation de chargement pour les boutons
 * @param {HTMLElement} button - Élément bouton
 */
function animateButtonLoading(button) {
    const originalContent = button.innerHTML;
    
    // Créer le spinner
    button.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
        <span class="loading-text">${button.dataset.loadingText || 'Chargement...'}</span>
    `;
    
    // Animation du spinner
    const spinner = button.querySelector('.spinner');
    if (spinner) {
        gsap.to(spinner, {
            duration: 1,
            rotation: 360,
            ease: 'none',
            repeat: -1
        });
    }
    
    // Animation du texte
    gsap.from(button.querySelector('.loading-text'), {
        duration: ANIMATIONS_CONFIG.duration.fast,
        opacity: 0,
        x: 10,
        ease: ANIMATIONS_CONFIG.easing.smooth
    });
    
    return originalContent;
}

/**
 * Arrête l'animation de chargement d'un bouton
 * @param {HTMLElement} button - Élément bouton
 * @param {string} originalContent - Contenu original du bouton
 */
function stopButtonLoading(button, originalContent) {
    gsap.to(button, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        opacity: 0,
        scale: 0.95,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        onComplete: () => {
            button.innerHTML = originalContent;
            gsap.to(button, {
                duration: ANIMATIONS_CONFIG.duration.fast,
                opacity: 1,
                scale: 1,
                ease: ANIMATIONS_CONFIG.easing.bounce
            });
        }
    });
}

// ===== ANIMATIONS DE NOTIFICATION =====

/**
 * Animation d'apparition pour les alertes
 * @param {HTMLElement} alert - Élément d'alerte
 * @param {string} type - Type d'alerte (success, error, warning, info)
 */
function animateAlertEntrance(alert, type = 'info') {
    // Animation d'entrée depuis la droite
    gsap.fromTo(alert, {
        x: 100,
        opacity: 0,
        scale: 0.9
    }, {
        duration: ANIMATIONS_CONFIG.duration.normal,
        x: 0,
        opacity: 1,
        scale: 1,
        ease: ANIMATIONS_CONFIG.easing.bounce
    });
    
    // Animation spécifique selon le type
    const icon = alert.querySelector('.material-icons');
    if (icon) {
        switch (type) {
            case 'success':
                animateSuccessIcon(icon);
                break;
            case 'error':
                animateErrorIcon(icon);
                break;
            case 'warning':
                animateWarningIcon(icon);
                break;
            default:
                animateInfoIcon(icon);
        }
    }
}

/**
 * Animation de sortie pour les alertes
 * @param {HTMLElement} alert - Élément d'alerte
 * @param {Function} callback - Fonction à exécuter après l'animation
 */
function animateAlertExit(alert, callback = null) {
    gsap.to(alert, {
        duration: ANIMATIONS_CONFIG.duration.fast,
        x: 100,
        opacity: 0,
        scale: 0.9,
        ease: 'power2.in',
        onComplete: () => {
            if (callback) callback();
        }
    });
}

// ===== ANIMATIONS D'ICÔNES =====

/**
 * Animation d'icône de succès
 * @param {HTMLElement} icon - Élément icône
 */
function animateSuccessIcon(icon) {
    gsap.from(icon, {
        duration: 0.6,
        scale: 0,
        rotation: -180,
        ease: ANIMATIONS_CONFIG.easing.bounce
    });
    
    // Effet de pulse
    gsap.to(icon, {
        duration: 1.5,
        scale: 1.1,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: 2
    });
}

/**
 * Animation d'icône d'erreur
 * @param {HTMLElement} icon - Élément icône
 */
function animateErrorIcon(icon) {
    // Effet de shake
    gsap.to(icon, {
        duration: 0.1,
        x: -3,
        ease: 'power2.inOut',
        repeat: 5,
        yoyo: true,
        onComplete: () => {
            gsap.set(icon, { x: 0 });
        }
    });
    
    // Effet de pulse rouge
    gsap.to(icon, {
        duration: 0.5,
        color: 'var(--accent-red)',
        scale: 1.2,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        yoyo: true,
        repeat: 1
    });
}

/**
 * Animation d'icône d'avertissement
 * @param {HTMLElement} icon - Élément icône
 */
function animateWarningIcon(icon) {
    // Effet de balancement
    gsap.to(icon, {
        duration: 0.3,
        rotation: -15,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: 3,
        onComplete: () => {
            gsap.set(icon, { rotation: 0 });
        }
    });
}

/**
 * Animation d'icône d'information
 * @param {HTMLElement} icon - Élément icône
 */
function animateInfoIcon(icon) {
    // Effet de pulse doux
    gsap.to(icon, {
        duration: 1,
        scale: 1.1,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
    });
}

// ===== ANIMATIONS DE PROGRESSION =====

/**
 * Animation de barre de progression
 * @param {HTMLElement} progressBar - Élément de barre de progression
 * @param {number} percentage - Pourcentage de progression (0-100)
 * @param {Object} options - Options d'animation
 */
function animateProgressBar(progressBar, percentage, options = {}) {
    const defaults = {
        duration: ANIMATIONS_CONFIG.duration.slow,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        color: 'var(--primary-blue)'
    };
    
    const config = { ...defaults, ...options };
    
    gsap.to(progressBar, {
        duration: config.duration,
        width: `${percentage}%`,
        backgroundColor: config.color,
        ease: config.ease
    });
    
    // Effet de brillance
    const shine = document.createElement('div');
    shine.style.cssText = `
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shine 2s ease-in-out infinite;
    `;
    
    progressBar.style.position = 'relative';
    progressBar.appendChild(shine);
    
    // Supprimer l'effet après l'animation
    setTimeout(() => {
        if (shine.parentNode) {
            shine.parentNode.removeChild(shine);
        }
    }, config.duration * 1000 + 2000);
}

/**
 * Animation d'indicateur d'étape
 * @param {NodeList} steps - Liste des étapes
 * @param {number} currentStep - Étape actuelle (0-based)
 */
function animateStepIndicator(steps, currentStep) {
    steps.forEach((step, index) => {
        if (index < currentStep) {
            // Étape complétée
            gsap.to(step, {
                duration: ANIMATIONS_CONFIG.duration.fast,
                backgroundColor: 'var(--primary-green)',
                scale: 1,
                ease: ANIMATIONS_CONFIG.easing.smooth
            });
            
            // Ajouter une icône de check
            step.innerHTML = '<span class="material-icons">check</span>';
            gsap.from(step.querySelector('.material-icons'), {
                duration: ANIMATIONS_CONFIG.duration.fast,
                scale: 0,
                rotation: 180,
                ease: ANIMATIONS_CONFIG.easing.bounce,
                delay: 0.1
            });
        } else if (index === currentStep) {
            // Étape active
            gsap.to(step, {
                duration: ANIMATIONS_CONFIG.duration.fast,
                backgroundColor: 'var(--primary-blue)',
                scale: 1.1,
                ease: ANIMATIONS_CONFIG.easing.bounce
            });
        } else {
            // Étape future
            gsap.to(step, {
                duration: ANIMATIONS_CONFIG.duration.fast,
                backgroundColor: 'var(--border-light)',
                scale: 1,
                ease: ANIMATIONS_CONFIG.easing.smooth
            });
        }
    });
}

// ===== EFFETS SPÉCIAUX =====

/**
 * Crée des particules de succès autour d'un élément
 * @param {HTMLElement} element - Élément de référence
 */
function createSuccessParticles(element) {
    const rect = element.getBoundingClientRect();
    const particles = [];
    
    for (let i = 0; i < 8; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: 6px;
            height: 6px;
            background: var(--primary-green);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            left: ${rect.left + rect.width / 2}px;
            top: ${rect.top + rect.height / 2}px;
        `;
        
        document.body.appendChild(particle);
        particles.push(particle);
        
        // Animation de la particule
        const angle = (i / 8) * Math.PI * 2;
        const distance = 50 + Math.random() * 30;
        
        gsap.to(particle, {
            duration: 0.8,
            x: Math.cos(angle) * distance,
            y: Math.sin(angle) * distance,
            opacity: 0,
            scale: 0,
            ease: 'power2.out',
            onComplete: () => {
                document.body.removeChild(particle);
            }
        });
    }
}

/**
 * Animation de typewriter pour le texte
 * @param {HTMLElement} element - Élément contenant le texte
 * @param {string} text - Texte à animer
 * @param {Object} options - Options d'animation
 */
function animateTypewriter(element, text, options = {}) {
    const defaults = {
        duration: 2,
        ease: 'none',
        cursor: true
    };
    
    const config = { ...defaults, ...options };
    
    // Vider l'élément
    element.textContent = '';
    
    // Ajouter le curseur si demandé
    if (config.cursor) {
        element.style.borderRight = '2px solid var(--primary-blue)';
        element.style.animation = 'blink 1s infinite';
    }
    
    // Animation du texte
    gsap.to(element, {
        duration: config.duration,
        textContent: text,
        ease: config.ease,
        snap: { textContent: 1 }, // Assure des caractères entiers
        onComplete: () => {
            if (config.cursor) {
                setTimeout(() => {
                    element.style.borderRight = 'none';
                    element.style.animation = 'none';
                }, 1000);
            }
        }
    });
}

/**
 * Animation de révélation de texte
 * @param {HTMLElement} element - Élément à révéler
 * @param {Object} options - Options d'animation
 */
function animateTextReveal(element, options = {}) {
    const defaults = {
        duration: ANIMATIONS_CONFIG.duration.slow,
        ease: ANIMATIONS_CONFIG.easing.smooth,
        direction: 'up' // up, down, left, right
    };
    
    const config = { ...defaults, ...options };
    
    // Masquer initialement
    element.style.overflow = 'hidden';
    
    // Wrapper pour l'animation
    const wrapper = document.createElement('div');
    wrapper.style.transform = getTransformFromDirection(config.direction);
    wrapper.innerHTML = element.innerHTML;
    
    element.innerHTML = '';
    element.appendChild(wrapper);
    
    // Animation
    gsap.to(wrapper, {
        duration: config.duration,
        transform: 'translate(0, 0)',
        ease: config.ease,
        onComplete: () => {
            element.innerHTML = wrapper.innerHTML;
            element.style.overflow = 'visible';
        }
    });
}

/**
 * Obtient la transformation CSS selon la direction
 * @param {string} direction - Direction de l'animation
 * @returns {string} - Transformation CSS
 */
function getTransformFromDirection(direction) {
    switch (direction) {
        case 'up': return 'translateY(100%)';
        case 'down': return 'translateY(-100%)';
        case 'left': return 'translateX(100%)';
        case 'right': return 'translateX(-100%)';
        default: return 'translateY(100%)';
    }
}

// ===== INITIALISATION AUTOMATIQUE =====

/**
 * Initialise les animations automatiques au chargement de la page
 */
function initAuthAnimations() {
    // Écouter les événements sur les champs de saisie
    document.addEventListener('focus', (e) => {
        if (e.target.matches('input, textarea, select')) {
            animateInputFocus(e.target);
        }
    }, true);
    
    document.addEventListener('blur', (e) => {
        if (e.target.matches('input, textarea, select')) {
            animateInputBlur(e.target);
        }
    }, true);
    
    // Écouter les événements sur les boutons
    document.addEventListener('mouseenter', (e) => {
        if (e.target.matches('button:not(:disabled), .btn:not(:disabled)')) {
            animateButtonHover(e.target);
        }
    });
    
    document.addEventListener('mouseleave', (e) => {
        if (e.target.matches('button, .btn')) {
            animateButtonLeave(e.target);
        }
    });
    
    document.addEventListener('click', (e) => {
        if (e.target.matches('button:not(:disabled), .btn:not(:disabled)')) {
            animateButtonClick(e.target);
        }
    });
}

// ===== EXPORT GLOBAL =====
window.AuthAnimations = {
    // Animations d'entrée
    animateAuthCardEntrance,
    animateFormElements,
    animateAuthHeader,
    
    // Animations d'interaction
    animateInputFocus,
    animateInputBlur,
    animateInputSuccess,
    animateInputError,
    
    // Animations de boutons
    animateButtonHover,
    animateButtonLeave,
    animateButtonClick,
    animateButtonLoading,
    stopButtonLoading,
    
    // Animations de notification
    animateAlertEntrance,
    animateAlertExit,
    
    // Animations d'icônes
    animateSuccessIcon,
    animateErrorIcon,
    animateWarningIcon,
    animateInfoIcon,
    
    // Animations de progression
    animateProgressBar,
    animateStepIndicator,
    
    // Effets spéciaux
    createSuccessParticles,
    animateTypewriter,
    animateTextReveal,
    
    // Initialisation
    init: initAuthAnimations,
    
    // Configuration
    config: ANIMATIONS_CONFIG
};

// Initialisation automatique si GSAP est disponible
if (typeof gsap !== 'undefined') {
    document.addEventListener('DOMContentLoaded', initAuthAnimations);
    
    // Ajouter les styles CSS pour les animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes blink {
            0%, 50% { border-color: var(--primary-blue); }
            51%, 100% { border-color: transparent; }
        }
        
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .loading-spinner {
            display: inline-block;
            margin-right: 8px;
        }
        
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
        }
    `;
    document.head.appendChild(style);
}