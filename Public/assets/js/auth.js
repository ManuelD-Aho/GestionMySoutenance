// ===== AUTH.JS - Script pour la page d'authentification =====

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