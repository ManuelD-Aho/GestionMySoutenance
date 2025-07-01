document.addEventListener('DOMContentLoaded', () => {
    gsap.from("#auth-container > .card", { duration: 0.5, opacity: 0, y: 50, ease: "power2.out" });

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
        gsap.from(feedbackDiv.firstElementChild, { duration: 0.3, opacity: 0, y: -10 });
    };

    const handleFormSubmit = async (form) => {
        const submitButton = form.querySelector('button[type="submit"]');
        const feedbackDiv = document.getElementById('form-feedback');

        feedbackDiv.classList.add('hidden');
        document.getElementById('global-alerts')?.classList.add('hidden');
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const data = await response.json();

            if (response.ok && data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showFeedback(data.message, 'success');
                    form.reset();
                }
            } else {
                showFeedback(data.message || 'Une erreur est survenue.', 'error');
            }
        } catch (error) {
            showFeedback('Erreur de connexion au serveur. Veuillez réessayer.', 'error');
        } finally {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
        }
    };

    const forms = ['login-form', '2fa-form', 'forgot-password-form', 'reset-password-form'];
    forms.forEach(formId => {
        const formElement = document.getElementById(formId);
        if (formElement) {
            formElement.addEventListener('submit', (e) => {
                e.preventDefault();
                handleFormSubmit(formElement);
            });
        }
    });
});