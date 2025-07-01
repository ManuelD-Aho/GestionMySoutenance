document.addEventListener('DOMContentLoaded', function () {
    const passwordToggles = document.querySelectorAll('[data-toggle-password]');

    const eyeIcon = `
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.022 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>`;

    const eyeOffIcon = `
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.022-7 9.542-7 .847 0 1.67.127 2.458.365M18.232 18.232c.443-.443.83-1.014 1.13-1.688M5.232 5.232c-.443.443-.83 1.014-1.13 1.688m13.86 0A9.949 9.949 0 0012 7c-2.761 0-5.26 1.12-7.071 2.929M1 1l22 22"></path>
        </svg>`;

    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const passwordInput = document.getElementById(this.dataset.togglePassword);
            if (passwordInput) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Change icon by replacing the innerHTML of the button
                if (type === 'password') {
                    this.innerHTML = eyeIcon;
                } else {
                    this.innerHTML = eyeOffIcon;
                }
            }
        });
    });
});
