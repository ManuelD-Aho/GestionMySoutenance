document.addEventListener('DOMContentLoaded', () => {
    // Animation d'entrée de la carte
    gsap.from("#auth-container > .card", { duration: 0.5, opacity: 0, y: 50, ease: "power2.out" });

    // Cible tous les formulaires dans le conteneur d'authentification
    const forms = document.querySelectorAll('#auth-container form');

    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            // On utilise Fetch uniquement pour les navigateurs modernes, sinon on laisse le formulaire se soumettre normalement.
            if (typeof fetch === 'undefined') {
                return;
            }

            e.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const feedbackDiv = document.getElementById('form-feedback');

            // Cacher les alertes précédentes
            feedbackDiv.innerHTML = '';
            feedbackDiv.classList.add('hidden');
            document.getElementById('global-alerts')?.remove();

            // Activer l'état de chargement
            submitButton.classList.add('loading');
            submitButton.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Pour identifier les requêtes AJAX côté serveur
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showFeedback(data.message, 'success', feedbackDiv);
                        form.reset();
                    }
                } else {
                    showFeedback(data.message || 'Une erreur est survenue.', 'error', feedbackDiv);
                }
            } catch (error) {
                showFeedback('Erreur de connexion au serveur. Veuillez réessayer.', 'error', feedbackDiv);
                console.error('Fetch error:', error);
            } finally {
                // Désactiver l'état de chargement
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
            }
        });
    });

    /**
     * Affiche un message de retour dans la zone dédiée.
     * @param {string} message - Le message à afficher.
     * @param {'success'|'error'} type - Le type de message.
     * @param {HTMLElement} container - L'élément où injecter le message.
     */
    const showFeedback = (message, type, container) => {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        container.innerHTML = `
            <div role="alert" class="alert ${alertClass} shadow-lg">
                <span>${message}</span>
            </div>`;
        container.classList.remove('hidden');
        gsap.from(container.firstElementChild, { duration: 0.3, opacity: 0, y: -10 });
    };
});