document.addEventListener('DOMContentLoaded', () => {
    const htmlElement = document.documentElement;
    const loginPage = document.getElementById('loginPage');
    const appLayout = document.getElementById('appLayout');
    const loginForm = document.getElementById('loginForm');
    const logoutBtn = document.getElementById('logoutBtn');
    const darkModeToggle = document.getElementById('darkModeToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const toastContainer = document.getElementById('toastContainer');

    // --- Theme Management ---
    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlElement.classList.add('dark');
            darkModeToggle.innerHTML = '<span class="material-icons">light_mode</span><span>Mode Clair</span>';
        } else {
            htmlElement.classList.remove('dark');
            darkModeToggle.innerHTML = '<span class="material-icons">dark_mode</span><span>Mode Sombre</span>';
        }
        localStorage.setItem('theme', theme);
    }

    // Initialize theme from localStorage or system preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme('dark'); // Apply system preference if no saved theme
    } else {
        applyTheme('light'); // Default to light if no preference
    }

    darkModeToggle.addEventListener('click', () => {
        const currentTheme = htmlElement.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(currentTheme === 'light' ? 'dark' : 'light');
    });

    // --- Page Navigation (Login/App) ---
    function showPage(pageId) {
        if (pageId === 'login') {
            loginPage.classList.remove('hidden');
            appLayout.classList.add('hidden');
            document.body.style.overflow = ''; // Ensure body scrollable on login page
        } else {
            loginPage.classList.add('hidden');
            appLayout.classList.remove('hidden');
            // Adjust body overflow for app layout if sidebar is fixed on mobile
            if (window.innerWidth <= 767 && sidebar.classList.contains('is-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    }

    // Check login status (simple simulation)
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    if (isLoggedIn) {
        showPage('app');
    } else {
        showPage('login');
    }

    // Login Form Submission
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Simulate authentication
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            if (email === 'test@example.com' && password === 'password') {
                localStorage.setItem('isLoggedIn', 'true');
                showToast('success', 'Connexion réussie ! Bienvenue.');
                showPage('app');
            } else {
                showToast('danger', 'Email ou mot de passe incorrect.');
            }
        });
    }

    // Logout Button
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.setItem('isLoggedIn', 'false');
            showToast('info', 'Vous avez été déconnecté.');
            showPage('login');
            // Close sidebar if open after logout
            if (sidebar.classList.contains('is-open')) {
                sidebar.classList.remove('is-open');
            }
        });
    }

    // --- Sidebar Toggle (for mobile) ---
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
            // Toggle body overflow to prevent scrolling when sidebar is open
            if (sidebar.classList.contains('is-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (event) => {
            if (window.innerWidth <= 767 && sidebar.classList.contains('is-open')) {
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }
        });

        // Close sidebar on navigation item click (for mobile UX)
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 767) {
                    sidebar.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // --- Loading Button State ---
    const loadingBtn = document.getElementById('loadingBtn');
    if (loadingBtn) {
        loadingBtn.addEventListener('click', () => {
            loadingBtn.classList.add('btn-loading');
            loadingBtn.disabled = true;
            setTimeout(() => {
                loadingBtn.classList.remove('btn-loading');
                loadingBtn.disabled = false;
            }, 3000); // Simulate loading for 3 seconds
        });
    }

    // --- Modals ---
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalDismisses = document.querySelectorAll('[data-modal-dismiss]');
    const modals = document.querySelectorAll('.modal');

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.dataset.modalTarget;
            const modal = document.querySelector(targetId);
            if (modal) {
                modal.classList.add('visible');
                document.body.style.overflow = 'hidden'; // Prevent scrolling body
                // Create and show backdrop
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.classList.add('modal-backdrop');
                    document.body.appendChild(backdrop);
                }
                backdrop.classList.add('visible');
            }
        });
    });

    modalDismisses.forEach(dismissBtn => {
        dismissBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = dismissBtn.dataset.modalDismiss;
            const modal = document.querySelector(targetId);
            if (modal) {
                modal.classList.remove('visible');
                document.body.style.overflow = ''; // Restore body scrolling
                // Hide backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('visible');
                }
            }
        });
    });

    // Close modal when clicking outside the modal-dialog (on the backdrop)
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) { // Check if click is directly on the modal (backdrop area)
                modal.classList.remove('visible');
                document.body.style.overflow = '';
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('visible');
                }
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.visible');
            if (openModal) {
                openModal.classList.remove('visible');
                document.body.style.overflow = '';
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('visible');
                }
            }
        }
    });

    // --- Toasts ---
    function showToast(type, message, duration = 5000) {
        const toast = document.createElement('div');
        toast.classList.add('toast', type);
        toast.innerHTML = `
            <span class="material-icons toast-icon">${getToastIcon(type)}</span>
            <div class="toast-body">
                <h4 class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)} Notification</h4>
                <p>${message}</p>
            </div>
            <button type="button" class="btn-close toast-close" aria-label="Fermer la notification"></button>
        `;

        toastContainer.appendChild(toast);

        // Dismiss on close button click
        toast.querySelector('.toast-close').addEventListener('click', () => {
            dismissToast(toast);
        });

        // Auto-dismiss after duration
        setTimeout(() => {
            dismissToast(toast);
        }, duration);
    }

    function dismissToast(toast) {
        toast.classList.add('hide'); // Trigger fade-out animation
        toast.addEventListener('animationend', () => {
            toast.remove();
        }, { once: true }); // Remove element after animation
    }

    function getToastIcon(type) {
        switch (type) {
            case 'success': return 'check_circle';
            case 'warning': return 'warning';
            case 'danger': return 'error';
            case 'info': return 'info';
            default: return 'notifications';
        }
    }

    document.getElementById('showSuccessToast').addEventListener('click', () => {
        showToast('success', 'Votre opération a été effectuée avec succès !');
    });
    document.getElementById('showWarningToast').addEventListener('click', () => {
        showToast('warning', 'Attention : Certaines données pourraient être incomplètes.');
    });
    document.getElementById('showDangerToast').addEventListener('click', () => {
        showToast('danger', 'Erreur critique : Impossible de traiter votre demande.');
    });
    document.getElementById('showInfoToast').addEventListener('click', () => {
        showToast('info', 'Une nouvelle mise à jour est disponible.');
    });

    // --- Alert Dismissible ---
    document.querySelectorAll('.alert-dismissible .btn-close').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('.alert').remove();
        });
    });

    // --- Tabs functionality ---
    document.querySelectorAll('.tabs-list .tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            // Remove active from all tabs
            tab.closest('.tabs-list').querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            // Add active to clicked tab
            e.target.classList.add('active');
            // In a real app, you'd also show/hide corresponding tab content here
        });
    });

    // --- Progress Circle Animation (for demo purposes) ---
    const progressCircles = document.querySelectorAll('.progress-circle');
    progressCircles.forEach(circle => {
        const valueElement = circle.querySelector('.progress-circle-value');
        const fillCircle = circle.querySelector('.progress-fill');
        if (valueElement && fillCircle) {
            const percentage = parseInt(valueElement.textContent);
            const radius = fillCircle.r.baseVal.value;
            const circumference = 2 * Math.PI * radius;
            fillCircle.style.strokeDasharray = circumference;
            fillCircle.style.strokeDashoffset = circumference - (percentage / 100) * circumference;
        }
    });

});