document.addEventListener('DOMContentLoaded', () => {
    // Animation d'entrée
    gsap.from("#auth-container > .card", {
        duration: 0.5,
        opacity: 0,
        y: 50,
        ease: "power2.out"
    });

    const loginForm = document.getElementById('login-form');
    const twoFaForm = document.getElementById('2fa-form');
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    const resetPasswordForm = document.getElementById('reset-password-form');

    /**
     * Affiche un message de retour dans la zone dédiée.
     * @param {string} message - Le message à afficher.
     * @param {'success'|'error'} type - Le type de message.
     */
    const showFeedback = (message, type = 'error') => {
        const feedbackDiv = document.getElementById('form-feedback');
        if (!feedbackDiv) return;

        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        feedbackDiv.innerHTML = `
            <div role="alert" class="alert ${alertClass} shadow-lg">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2 2m2-2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>${message}</span>
                </div>
            </div>`;
        feedbackDiv.classList.remove('hidden');
    };

    /**
     * Gère la soumission d'un formulaire via Fetch API.
     * @param {HTMLFormElement} form - Le formulaire à soumettre.
     */
    const handleFormSubmit = async (form) => {
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.loading');
        const feedbackDiv = document.getElementById('form-feedback');

        // Cacher les alertes précédentes
        feedbackDiv.classList.add('hidden');
        document.getElementById('global-alerts')?.classList.add('hidden');

        // Activer le spinner et désactiver le bouton
        spinner.classList.remove('hidden');
        submitButton.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showFeedback(data.message, 'success');
                    form.reset();
                }
            } else {
                // Gérer les erreurs de validation ou autres erreurs serveur
                const errorMessage = data.message || 'Une erreur est survenue.';
                showFeedback(errorMessage, 'error');
            }
        } catch (error) {
            showFeedback('Erreur de connexion au serveur. Veuillez réessayer.', 'error');
            console.error('Fetch error:', error);
        } finally {
            // Réactiver le bouton et cacher le spinner
            spinner.classList.add('hidden');
            submitButton.disabled = false;
        }
    };

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleFormSubmit(loginForm);
        });
    }

    if (twoFaForm) {
        twoFaForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleFormSubmit(twoFaForm);
        });
    }

    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleFormSubmit(forgotPasswordForm);
        });
    }

    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleFormSubmit(resetPasswordForm);
        });
    }
});