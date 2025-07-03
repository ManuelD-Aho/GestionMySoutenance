/**
 * ==============================================
 * MAIN.JS - JavaScript Principal
 * GestionMySoutenance - Système de gestion
 * ==============================================
 */

(function() {
    'use strict';

    // ==============================================
    // VARIABLES GLOBALES
    // ==============================================

    let sidebarOpen = true;
    let currentModal = null;
    let flashMessageTimer = null;

    // ==============================================
    // INITIALISATION
    // ==============================================

    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initModals();
        initTabs();
        initForms();
        initTables();
        initFlashMessages();
        initTooltips();
        initConfirmActions();
        initSearchAndFilters();
        initDashboard();

        // Animation d'entrée pour les éléments
        animateOnLoad();

        console.log('🚀 GestionMySoutenance - Interface initialisée');
    });

    // ==============================================
    // GESTION DE LA SIDEBAR
    // ==============================================

    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.querySelector('.mobile-sidebar-toggle');
        const mainContent = document.getElementById('mainContentArea');

        // Toggle sidebar mobile
        if (toggleButton) {
            toggleButton.addEventListener('click', toggleMobileSidebar);
        }

        // Navigation active
        updateActiveNavItem();

        // Fermer sidebar en cliquant à l'extérieur (mobile)
        if (window.innerWidth <= 768) {
            document.addEventListener('click', function(e) {
                if (sidebar && !sidebar.contains(e.target) && !toggleButton?.contains(e.target)) {
                    closeMobileSidebar();
                }
            });
        }
    }

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-mobile-open');
        }
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-mobile-open');
        }
    }

    function updateActiveNavItem() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.nav-item');

        navItems.forEach(item => {
            item.classList.remove('active');
            const href = item.getAttribute('href');
            if (href && currentPath.startsWith(href) && href !== '/') {
                item.classList.add('active');
            } else if (href === '/' && currentPath === '/') {
                item.classList.add('active');
            }
        });
    }

    // ==============================================
    // GESTION DES MODALES
    // ==============================================

    function initModals() {
        // Boutons d'ouverture de modale
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal-target');
                openModal(modalId);
            });
        });

        // Boutons de fermeture de modale
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        });

        // Fermer modale en cliquant sur l'overlay
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        });

        // Fermer modale avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && currentModal) {
                closeModal();
            }
        });
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            currentModal = modal;
            document.body.style.overflow = 'hidden';

            // Focus sur le premier élément focusable
            const firstFocusable = modal.querySelector('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
        }
    }

    function closeModal() {
        if (currentModal) {
            currentModal.classList.remove('active');
            currentModal = null;
            document.body.style.overflow = '';
        }
    }

    // ==============================================
    // GESTION DES ONGLETS
    // ==============================================

    function initTabs() {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                switchTab(targetTab);
            });
        });

        // Support pour les anciens systèmes d'onglets
        document.querySelectorAll('.users-tab').forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                switchLegacyTab(targetTab);
            });
        });
    }

    function switchTab(targetTab) {
        // Désactiver tous les boutons d'onglets
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Cacher tous les panneaux d'onglets
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Activer le bouton cliqué
        document.querySelector(`[data-tab="${targetTab}"]`).classList.add('active');

        // Afficher le panneau correspondant
        const targetPanel = document.getElementById(`${targetTab}-panel`);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }
    }

    function switchLegacyTab(targetTab) {
        // Pour compatibilité avec l'ancien système
        const container = document.querySelector('.users-tabs').closest('.section-header-sticky').parentElement;

        // Désactiver tous les boutons
        container.querySelectorAll('.users-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Cacher tous les contenus
        container.querySelectorAll('.users-tab-content, .templates_docs-tab-content, .system_config-tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Activer le bouton et contenu ciblés
        const targetButton = container.querySelector(`[data-tab="${targetTab}"]`);
        const targetContent = container.querySelector(`#*-${targetTab}-content`);

        if (targetButton) targetButton.classList.add('active');
        if (targetContent) targetContent.classList.add('active');
    }

    // ==============================================
    // GESTION DES FORMULAIRES
    // ==============================================

    function initForms() {
        // Validation en temps réel
        document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });

        // Soumission des formulaires
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });
        });

        // Auto-resize des textareas
        document.querySelectorAll('.form-textarea').forEach(textarea => {
            autoResizeTextarea(textarea);
            textarea.addEventListener('input', function() {
                autoResizeTextarea(this);
            });
        });
    }

    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const type = field.type;

        clearFieldError(field);

        if (isRequired && !value) {
            showFieldError(field, 'Ce champ est requis');
            return false;
        }

        if (value && type === 'email' && !isValidEmail(value)) {
            showFieldError(field, 'Adresse email invalide');
            return false;
        }

        if (value && field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
            showFieldError(field, `Minimum ${field.getAttribute('minlength')} caractères`);
            return false;
        }

        return true;
    }

    function validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('.form-input, .form-select, .form-textarea');

        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function showFieldError(field, message) {
        clearFieldError(field);

        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    }

    function clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.form-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    function autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // ==============================================
    // GESTION DES TABLEAUX
    // ==============================================

    function initTables() {
        // Tri des colonnes
        document.querySelectorAll('.table th[data-sort]').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(this);
            });
        });

        // Sélection multiple
        initTableSelection();

        // Actions en lot
        initBulkActions();
    }

    function sortTable(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const sortKey = header.getAttribute('data-sort');
        const isAscending = !header.classList.contains('sort-desc');

        // Nettoyer les classes de tri précédentes
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });

        // Ajouter la classe de tri actuelle
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

        // Trier les lignes
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();

            let comparison = 0;
            if (sortKey === 'number') {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            } else if (sortKey === 'date') {
                comparison = new Date(aValue) - new Date(bValue);
            } else {
                comparison = aValue.localeCompare(bValue);
            }

            return isAscending ? comparison : -comparison;
        });

        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));
    }

    function initTableSelection() {
        // Checkbox "tout sélectionner"
        document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const table = this.closest('table');
                const checkboxes = table.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkActionsVisibility();
            });
        });

        // Checkboxes individuelles
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsVisibility);
        });
    }

    function initBulkActions() {
        document.querySelectorAll('.bulk-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const selectedRows = getSelectedRows();

                if (selectedRows.length === 0) {
                    showFlashMessage('warning', 'Aucun élément sélectionné');
                    return;
                }

                if (confirm(`Êtes-vous sûr de vouloir ${action} ${selectedRows.length} élément(s) ?`)) {
                    performBulkAction(action, selectedRows);
                }
            });
        });
    }

    function getSelectedRows() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.closest('tr'));
    }

    function updateBulkActionsVisibility() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActions = document.querySelector('.bulk-actions');

        if (bulkActions) {
            bulkActions.style.display = selectedCount > 0 ? 'block' : 'none';
        }
    }

    function performBulkAction(action, rows) {
        // Ici vous pouvez implémenter les actions en lot
        console.log(`Action ${action} sur`, rows);
        showFlashMessage('success', `Action ${action} effectuée sur ${rows.length} élément(s)`);
    }

    // ==============================================
    // GESTION DES MESSAGES FLASH
    // ==============================================

    function initFlashMessages() {
        // Auto-fermeture des messages
        document.querySelectorAll('.flash-message').forEach(message => {
            const closeBtn = message.querySelector('.flash-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    closeFlashMessage(message);
                });
            }

            // Auto-fermeture après 5 secondes
            setTimeout(() => {
                closeFlashMessage(message);
            }, 5000);
        });
    }

    function showFlashMessage(type, message, title = null) {
        const container = getOrCreateFlashContainer();

        const flashDiv = document.createElement('div');
        flashDiv.className = `flash-message ${type} fade-in`;

        const icon = getFlashIcon(type);

        flashDiv.innerHTML = `
            <span class="material-icons">${icon}</span>
            <div class="flash-content">
                ${title ? `<div class="flash-title">${title}</div>` : ''}
                <p class="flash-text">${message}</p>
            </div>
            <button class="flash-close">
                <span class="material-icons">close</span>
            </button>
        `;

        container.appendChild(flashDiv);

        // Event listener pour fermer
        flashDiv.querySelector('.flash-close').addEventListener('click', function() {
            closeFlashMessage(flashDiv);
        });

        // Auto-fermeture
        setTimeout(() => {
            closeFlashMessage(flashDiv);
        }, 5000);
    }

    function closeFlashMessage(messageElement) {
        if (messageElement && messageElement.parentNode) {
            messageElement.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                messageElement.remove();
            }, 300);
        }
    }

    function getOrCreateFlashContainer() {
        let container = document.querySelector('.flash-messages');
        if (!container) {
            container = document.createElement('div');
            container.className = 'flash-messages';
            document.body.appendChild(container);
        }
        return container;
    }

    function getFlashIcon(type) {
        const icons = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };
        return icons[type] || 'info';
    }

    // ==============================================
    // GESTION DES TOOLTIPS
    // ==============================================

    function initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
        });
    }

    function showTooltip(e) {
        const text = e.target.getAttribute('data-tooltip');
        if (!text) return;

        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
            white-space: nowrap;
        `;

        document.body.appendChild(tooltip);

        const rect = e.target.getBoundingClientRect();
        tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';

        e.target._tooltip = tooltip;
    }

    function hideTooltip(e) {
        if (e.target._tooltip) {
            e.target._tooltip.remove();
            delete e.target._tooltip;
        }
    }

    // ==============================================
    // ACTIONS DE CONFIRMATION
    // ==============================================

    function initConfirmActions() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // ==============================================
    // RECHERCHE ET FILTRES
    // ==============================================

    function initSearchAndFilters() {
        // Recherche en temps réel
        document.querySelectorAll('.search-input').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    performSearch(this.value, this.getAttribute('data-search-target'));
                }, 300);
            });
        });

        // Filtres
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                applyFilter(this.value, this.getAttribute('data-filter-type'));
            });
        });
    }

    function performSearch(query, target) {
        const targetElement = document.querySelector(target);
        if (!targetElement) return;

        const rows = targetElement.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    }

    function applyFilter(value, filterType) {
        console.log(`Filtre ${filterType} avec valeur: ${value}`);
        // Implémenter la logique de filtrage selon vos besoins
    }

    // ==============================================
    // DASHBOARD
    // ==============================================

    function initDashboard() {
        // Animation des statistiques au chargement
        animateCounters();

        // Rafraîchissement automatique des données
        if (document.querySelector('.stats-grid')) {
            setInterval(refreshDashboardData, 300000); // 5 minutes
        }
    }

    function animateCounters() {
        document.querySelectorAll('.stat-value').forEach(counter => {
            const target = parseInt(counter.textContent.replace(/\D/g, ''));
            if (isNaN(target)) return;

            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 20);
        });
    }

    function refreshDashboardData() {
        // Implémenter le rafraîchissement des données via AJAX
        console.log('Rafraîchissement des données du dashboard...');
    }

    // ==============================================
    // ANIMATIONS
    // ==============================================

    function animateOnLoad() {
        // Observer pour les animations à l'entrée
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        });

        document.querySelectorAll('.card, .stat-card').forEach(el => {
            observer.observe(el);
        });
    }

    // ==============================================
    // UTILITAIRES
    // ==============================================

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    function formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    // ==============================================
    // API PUBLIQUE
    // ==============================================

    // Exposer les fonctions utiles globalement
    window.GestionMySoutenance = {
        showFlashMessage,
        openModal,
        closeModal,
        toggleMobileSidebar,
        formatDate,
        formatNumber,
        version: '1.0.0'
    };

    // ==============================================
    // GESTION DES ERREURS
    // ==============================================

    window.addEventListener('error', function(e) {
        console.error('Erreur JavaScript:', e.error);
        showFlashMessage('error', 'Une erreur inattendue s\'est produite');
    });

    // Gestion des promesses rejetées
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Promesse rejetée:', e.reason);
        showFlashMessage('error', 'Erreur de communication avec le serveur');
    });

})();

// ==============================================
// STYLES CSS AJOUTÉS DYNAMIQUEMENT
// ==============================================

// Ajout de styles pour les animations et interactions
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .tooltip {
        animation: fadeIn 0.2s ease-out;
    }
    
    .sort-asc::after {
        content: ' ↑';
        color: var(--primary-accent);
    }
    
    .sort-desc::after {
        content: ' ↓';
        color: var(--primary-accent);
    }
    
    .form-input.error,
    .form-select.error,
    .form-textarea.error {
        border-color: var(--danger-accent);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
    
    .bulk-actions {
        background: var(--primary-accent-light);
        border: 1px solid var(--primary-accent);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        display: none;
    }
    
    .sidebar-mobile-open {
        overflow: hidden;
    }
    
    @media (max-width: 768px) {
        .sidebar-mobile-open .gestionsoutenance-sidebar {
            transform: translateX(0);
        }
        
        .sidebar-mobile-open::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    }
`;

document.head.appendChild(style);