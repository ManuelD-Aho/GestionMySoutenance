/**
 * ==============================================
 * ADMIN.JS - Fonctionnalités Administratives
 * GestionMySoutenance - Module Administration
 * ==============================================
 */

(function() {
    'use strict';

    // ==============================================
    // VARIABLES GLOBALES ADMIN
    // ==============================================

    let currentSection = 'dashboard';
    let currentTab = null;
    let currentFilters = {};
    let currentSort = {};
    let adminData = {};

    // ==============================================
    // INITIALISATION ADMIN
    // ==============================================

    document.addEventListener('DOMContentLoaded', function() {
        initAdminInterface();
        initAdminNavigation();
        initAdminTabs();
        initAdminModals();
        initAdminForms();
        initAdminTables();
        initAdminFilters();
        initAdminNotifications();
        initAdminCharts();
        initAdminDashboard();

        console.log('🔧 Module Administration initialisé');
    });

    // ==============================================
    // INTERFACE ADMIN
    // ==============================================

    function initAdminInterface() {
        // Gestion du thème admin
        initAdminTheme();

        // Gestion de la disposition
        initAdminLayout();

        // Raccourcis clavier admin
        initAdminKeyboardShortcuts();

        // Auto-sauvegarde
        initAutoSave();
    }

    function initAdminTheme() {
        const savedTheme = localStorage.getItem('admin-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Toggle theme button
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('admin-theme', newTheme);

                this.querySelector('.material-icons').textContent =
                    newTheme === 'dark' ? 'light_mode' : 'dark_mode';
            });
        }
    }

    function initAdminLayout() {
        // Sauvegarde de l'état de la sidebar
        const sidebarState = localStorage.getItem('admin-sidebar-collapsed') === 'true';
        if (sidebarState) {
            document.body.classList.add('sidebar-collapsed');
        }

        // Gestion responsive
        handleResponsiveLayout();
        window.addEventListener('resize', debounce(handleResponsiveLayout, 250));
    }

    function handleResponsiveLayout() {
        const isMobile = window.innerWidth <= 768;
        const isTablet = window.innerWidth <= 1024;

        document.body.classList.toggle('is-mobile', isMobile);
        document.body.classList.toggle('is-tablet', isTablet);

        // Adapter les éléments selon la taille d'écran
        if (isMobile) {
            closeAllDropdowns();
            adaptMobileInterface();
        }
    }

    function adaptMobileInterface() {
        // Adapter les tableaux pour mobile
        document.querySelectorAll('.admin-table').forEach(table => {
            const wrapper = table.closest('.admin-table-container');
            if (wrapper && !wrapper.querySelector('.table-scroll-hint')) {
                const hint = document.createElement('div');
                hint.className = 'table-scroll-hint';
                hint.textContent = '← Faites défiler horizontalement →';
                wrapper.appendChild(hint);
            }
        });
    }

    // ==============================================
    // NAVIGATION ADMIN
    // ==============================================

    function initAdminNavigation() {
        // Navigation principale
        document.querySelectorAll('[data-section]').forEach(navItem => {
            navItem.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');
                switchAdminSection(section);
            });
        });

        // Breadcrumb navigation
        initBreadcrumb();

        // Menu contextuel
        initContextMenu();
    }

    function switchAdminSection(sectionId) {
        // Sauvegarder l'état actuel
        saveCurrentState();

        // Masquer toutes les sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Désactiver tous les liens de navigation
        document.querySelectorAll('[data-section]').forEach(navItem => {
            navItem.classList.remove('active');
        });

        // Activer la nouvelle section
        const targetSection = document.getElementById(`${sectionId}-content`);
        const navItem = document.querySelector(`[data-section="${sectionId}"]`);

        if (targetSection && navItem) {
            targetSection.classList.add('active');
            navItem.classList.add('active');
            currentSection = sectionId;

            // Charger le contenu de la section
            loadSectionContent(sectionId);

            // Mettre à jour l'URL
            updateURL(sectionId);

            // Mettre à jour le breadcrumb
            updateBreadcrumb(sectionId);

            // Analytics
            trackSectionView(sectionId);
        }
    }

    function loadSectionContent(sectionId) {
        const section = document.getElementById(`${sectionId}-content`);
        if (!section) return;

        // Afficher un loader
        showSectionLoader(section);

        // Simuler le chargement (remplacer par un vrai appel AJAX)
        setTimeout(() => {
            hideSectionLoader(section);

            // Charger le contenu spécifique
            switch (sectionId) {
                case 'users':
                    loadUsersContent();
                    break;
                case 'permissions':
                    loadPermissionsContent();
                    break;
                case 'system_config':
                    loadSystemConfigContent();
                    break;
                case 'academic_year':
                    loadAcademicYearContent();
                    break;
                default:
                    loadDashboardContent();
            }
        }, 500);
    }

    function showSectionLoader(section) {
        const loader = document.createElement('div');
        loader.className = 'admin-loading section-loader';
        loader.innerHTML = `
            <div class="admin-spinner"></div>
            <span>Chargement...</span>
        `;
        section.appendChild(loader);
    }

    function hideSectionLoader(section) {
        const loader = section.querySelector('.section-loader');
        if (loader) {
            loader.remove();
        }
    }

    // ==============================================
    // SYSTÈME D'ONGLETS ADMIN
    // ==============================================

    function initAdminTabs() {
        // Onglets classiques
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                const container = this.closest('.admin-tabs').parentElement;
                switchAdminTab(tabId, container);
            });
        });

        // Support pour les anciens onglets
        document.querySelectorAll('.users-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                switchLegacyTab(tabId, this);
            });
        });

        // Onglets avec contenu lazy
        initLazyTabs();
    }

    function switchAdminTab(tabId, container) {
        // Désactiver tous les onglets du container
        container.querySelectorAll('.admin-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Masquer tous les contenus
        container.querySelectorAll('.admin-tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Activer l'onglet et son contenu
        const targetTab = container.querySelector(`[data-tab="${tabId}"]`);
        const targetContent = container.querySelector(`#${tabId}-content`) ||
            container.querySelector(`.admin-tab-content[data-tab="${tabId}"]`);

        if (targetTab) {
            targetTab.classList.add('active');
            currentTab = tabId;
        }

        if (targetContent) {
            targetContent.classList.add('active');

            // Lazy loading du contenu
            if (targetContent.hasAttribute('data-lazy')) {
                loadTabContent(tabId, targetContent);
            }

            // Re-initialiser les composants dans l'onglet
            initTabComponents(targetContent);
        }

        // Sauvegarder l'état
        localStorage.setItem(`admin-active-tab-${currentSection}`, tabId);
    }

    function switchLegacyTab(tabId, clickedTab) {
        const container = clickedTab.closest('.section-header-sticky').parentElement;

        // Désactiver tous les onglets
        container.querySelectorAll('.users-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Masquer tous les contenus
        container.querySelectorAll('[class*="-tab-content"]').forEach(content => {
            content.classList.remove('active');
        });

        // Activer l'onglet cliqué
        clickedTab.classList.add('active');

        // Trouver et activer le contenu correspondant
        const possibleSelectors = [
            `#${currentSection}-${tabId}-content`,
            `#${tabId}-content`,
            `.${currentSection}-tab-content[data-tab="${tabId}"]`
        ];

        for (const selector of possibleSelectors) {
            const content = container.querySelector(selector);
            if (content) {
                content.classList.add('active');
                break;
            }
        }
    }

    function loadTabContent(tabId, contentElement) {
        if (contentElement.hasAttribute('data-loaded')) return;

        // Marquer comme chargé
        contentElement.setAttribute('data-loaded', 'true');

        // Afficher un loader
        const loader = document.createElement('div');
        loader.className = 'admin-loading';
        loader.innerHTML = '<div class="admin-spinner"></div><span>Chargement...</span>';
        contentElement.appendChild(loader);

        // Simuler le chargement AJAX
        setTimeout(() => {
            loader.remove();

            // Charger le contenu spécifique à l'onglet
            switch (tabId) {
                case 'utilisateurs_actifs':
                    loadActiveUsersTab(contentElement);
                    break;
                case 'utilisateurs_inactifs':
                    loadInactiveUsersTab(contentElement);
                    break;
                case 'roles_permissions':
                    loadRolesPermissionsTab(contentElement);
                    break;
                default:
                    contentElement.innerHTML = '<p>Contenu à implémenter</p>';
            }
        }, 800);
    }

    function initTabComponents(tabContent) {
        // Re-initialiser les composants dans l'onglet
        initTablesInContainer(tabContent);
        initFormsInContainer(tabContent);
        initModalsInContainer(tabContent);
    }

    // ==============================================
    // MODALES ADMINISTRATIVES
    // ==============================================

    function initAdminModals() {
        // Modales de confirmation
        initConfirmationModals();

        // Modales de formulaire
        initFormModals();

        // Modales d'information
        initInfoModals();

        // Gestion globale des modales
        initModalManager();
    }

    function initConfirmationModals() {
        document.querySelectorAll('[data-confirm-modal]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();

                const message = this.getAttribute('data-confirm-message') || 'Êtes-vous sûr ?';
                const title = this.getAttribute('data-confirm-title') || 'Confirmation';
                const action = this.getAttribute('data-confirm-action');
                const type = this.getAttribute('data-confirm-type') || 'warning';

                showConfirmationModal(title, message, action, type, this);
            });
        });
    }

    function showConfirmationModal(title, message, action, type = 'warning', triggerElement = null) {
        const modal = createModal('confirmation-modal', {
            title: title,
            type: type,
            content: `
                <div class="admin-alert ${type}">
                    <span class="material-icons">${getModalIcon(type)}</span>
                    <div class="admin-alert-content">
                        <p class="admin-alert-text">${message}</p>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Annuler',
                    class: 'admin-btn-ghost',
                    action: 'close'
                },
                {
                    text: 'Confirmer',
                    class: `admin-btn-${type === 'danger' ? 'danger' : 'primary'}`,
                    action: () => {
                        if (action && triggerElement) {
                            executeConfirmedAction(action, triggerElement);
                        }
                        closeModal('confirmation-modal');
                    }
                }
            ]
        });

        openModal('confirmation-modal');
    }

    function executeConfirmedAction(action, triggerElement) {
        switch (action) {
            case 'delete':
                handleDelete(triggerElement);
                break;
            case 'bulk-delete':
                handleBulkDelete();
                break;
            case 'reset':
                handleReset(triggerElement);
                break;
            case 'submit':
                if (triggerElement.form) {
                    triggerElement.form.submit();
                }
                break;
            default:
                // Action personnalisée
                if (triggerElement.hasAttribute('data-action-url')) {
                    performAjaxAction(triggerElement);
                }
        }
    }

    function getModalIcon(type) {
        const icons = {
            success: 'check_circle',
            warning: 'warning',
            danger: 'error',
            info: 'info'
        };
        return icons[type] || 'info';
    }

    // ==============================================
    // FORMULAIRES ADMIN
    // ==============================================

    function initAdminForms() {
        // Validation avancée
        initAdvancedValidation();

        // Auto-complétion
        initAutoComplete();

        // Upload de fichiers
        initFileUpload();

        // Formulaires multi-étapes
        initMultiStepForms();

        // Sauvegarde automatique
        initFormAutoSave();
    }

    function initAdvancedValidation() {
        document.querySelectorAll('.admin-form').forEach(form => {
            const fields = form.querySelectorAll('input, select, textarea');

            fields.forEach(field => {
                // Validation en temps réel
                field.addEventListener('input', function() {
                    validateFieldAdvanced(this);
                });

                field.addEventListener('blur', function() {
                    validateFieldAdvanced(this, true);
                });
            });

            // Validation avant soumission
            form.addEventListener('submit', function(e) {
                if (!validateFormAdvanced(this)) {
                    e.preventDefault();
                    showValidationErrors(this);
                }
            });
        });
    }

    function validateFieldAdvanced(field, showErrors = false) {
        const value = field.value.trim();
        const rules = getValidationRules(field);
        const errors = [];

        // Validation des règles
        rules.forEach(rule => {
            if (!rule.test(value, field)) {
                errors.push(rule.message);
            }
        });

        // Affichage des erreurs
        if (showErrors && errors.length > 0) {
            showFieldError(field, errors[0]);
            return false;
        } else if (errors.length === 0) {
            clearFieldError(field);
            return true;
        }

        return errors.length === 0;
    }

    function getValidationRules(field) {
        const rules = [];

        // Règles de base
        if (field.hasAttribute('required')) {
            rules.push({
                test: (value) => value !== '',
                message: 'Ce champ est requis'
            });
        }

        if (field.type === 'email') {
            rules.push({
                test: (value) => !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
                message: 'Format d\'email invalide'
            });
        }

        if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            rules.push({
                test: (value) => !value || value.length >= minLength,
                message: `Minimum ${minLength} caractères`
            });
        }

        if (field.hasAttribute('maxlength')) {
            const maxLength = parseInt(field.getAttribute('maxlength'));
            rules.push({
                test: (value) => !value || value.length <= maxLength,
                message: `Maximum ${maxLength} caractères`
            });
        }

        // Règles personnalisées
        const customRule = field.getAttribute('data-validation');
        if (customRule) {
            rules.push(getCustomValidationRule(customRule));
        }

        return rules;
    }

    function getCustomValidationRule(ruleName) {
        const customRules = {
            'strong-password': {
                test: (value) => !value || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(value),
                message: 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial'
            },
            'phone-number': {
                test: (value) => !value || /^(\+33|0)[1-9](\d{8})$/.test(value.replace(/\s/g, '')),
                message: 'Format de téléphone invalide'
            },
            'unique-username': {
                test: (value, field) => {
                    // Ici, vous feriez un appel AJAX pour vérifier l'unicité
                    return true; // Placeholder
                },
                message: 'Ce nom d\'utilisateur est déjà utilisé'
            }
        };

        return customRules[ruleName] || {
            test: () => true,
            message: ''
        };
    }

    function initFileUpload() {
        document.querySelectorAll('.file-upload-area').forEach(area => {
            const input = area.querySelector('input[type="file"]');
            const dropZone = area.querySelector('.drop-zone');
            const preview = area.querySelector('.file-preview');

            if (!input || !dropZone) return;

            // Drag & Drop
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });

            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');

                const files = e.dataTransfer.files;
                handleFileSelection(files, input, preview);
            });

            // Click to select
            dropZone.addEventListener('click', function() {
                input.click();
            });

            input.addEventListener('change', function() {
                handleFileSelection(this.files, input, preview);
            });
        });
    }

    function handleFileSelection(files, input, preview) {
        Array.from(files).forEach(file => {
            if (validateFile(file, input)) {
                displayFilePreview(file, preview);
                if (input.hasAttribute('data-auto-upload')) {
                    uploadFile(file, input);
                }
            }
        });
    }

    function validateFile(file, input) {
        const maxSize = parseInt(input.getAttribute('data-max-size')) || 5 * 1024 * 1024; // 5MB
        const allowedTypes = input.getAttribute('data-allowed-types')?.split(',') || [];

        if (file.size > maxSize) {
            showFlashMessage('error', `Le fichier ${file.name} est trop volumineux (max: ${formatBytes(maxSize)})`);
            return false;
        }

        if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            showFlashMessage('error', `Type de fichier non autorisé: ${file.type}`);
            return false;
        }

        return true;
    }

    function displayFilePreview(file, preview) {
        if (!preview) return;

        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';

        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            previewItem.appendChild(img);
        } else {
            const icon = document.createElement('span');
            icon.className = 'material-icons';
            icon.textContent = getFileIcon(file.type);
            previewItem.appendChild(icon);
        }

        const info = document.createElement('div');
        info.className = 'file-info';
        info.innerHTML = `
            <div class="file-name">${file.name}</div>
            <div class="file-size">${formatBytes(file.size)}</div>
        `;
        previewItem.appendChild(info);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'file-remove';
        removeBtn.innerHTML = '<span class="material-icons">close</span>';
        removeBtn.addEventListener('click', function() {
            previewItem.remove();
        });
        previewItem.appendChild(removeBtn);

        preview.appendChild(previewItem);
    }

    // ==============================================
    // TABLEAUX ADMIN AVANCÉS
    // ==============================================

    function initAdminTables() {
        // Tri avancé
        initAdvancedSorting();

        // Filtrage en ligne
        initInlineFiltering();

        // Édition en ligne
        initInlineEditing();

        // Export de données
        initDataExport();

        // Pagination avancée
        initAdvancedPagination();
    }

    function initAdvancedSorting() {
        document.querySelectorAll('.admin-table th[data-sortable]').forEach(header => {
            header.style.cursor = 'pointer';
            header.classList.add('sortable');

            header.addEventListener('click', function() {
                const table = this.closest('.admin-table');
                const column = this.getAttribute('data-sort-key') || this.textContent.trim();
                const currentSort = table.getAttribute('data-current-sort');

                let direction = 'asc';
                if (currentSort === `${column}-asc`) {
                    direction = 'desc';
                } else if (currentSort === `${column}-desc`) {
                    direction = 'asc';
                }

                sortTableAdvanced(table, column, direction);
            });
        });
    }

    function sortTableAdvanced(table, column, direction) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const headerIndex = Array.from(table.querySelectorAll('th')).findIndex(th =>
            (th.getAttribute('data-sort-key') || th.textContent.trim()) === column
        );

        if (headerIndex === -1) return;

        rows.sort((a, b) => {
            const aValue = getCellValue(a.children[headerIndex]);
            const bValue = getCellValue(b.children[headerIndex]);

            let comparison = 0;

            // Détection automatique du type
            if (!isNaN(aValue) && !isNaN(bValue)) {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            } else if (Date.parse(aValue) && Date.parse(bValue)) {
                comparison = new Date(aValue) - new Date(bValue);
            } else {
                comparison = aValue.localeCompare(bValue, 'fr', { numeric: true });
            }

            return direction === 'asc' ? comparison : -comparison;
        });

        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));

        // Mettre à jour les indicateurs visuels
        updateSortIndicators(table, column, direction);

        // Sauvegarder l'état
        table.setAttribute('data-current-sort', `${column}-${direction}`);
    }

    function getCellValue(cell) {
        // Chercher d'abord un attribut data-value
        if (cell.hasAttribute('data-value')) {
            return cell.getAttribute('data-value');
        }

        // Sinon, prendre le contenu textuel en nettoyant
        return cell.textContent.trim().replace(/[^\w\s\-\.]/g, '');
    }

    function updateSortIndicators(table, column, direction) {
        // Nettoyer tous les indicateurs
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });

        // Ajouter l'indicateur à la colonne active
        const activeHeader = Array.from(table.querySelectorAll('th')).find(th =>
            (th.getAttribute('data-sort-key') || th.textContent.trim()) === column
        );

        if (activeHeader) {
            activeHeader.classList.add(`sort-${direction}`);
        }
    }

    function initInlineEditing() {
        document.querySelectorAll('.admin-table [data-editable]').forEach(cell => {
            cell.addEventListener('dblclick', function() {
                if (this.classList.contains('editing')) return;

                const originalValue = this.textContent.trim();
                const fieldType = this.getAttribute('data-field-type') || 'text';

                this.classList.add('editing');
                this.innerHTML = createInlineEditor(originalValue, fieldType);

                const input = this.querySelector('input, select, textarea');
                input.focus();
                input.select();

                input.addEventListener('blur', () => finishInlineEdit(this, originalValue));
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        finishInlineEdit(cell, originalValue);
                    } else if (e.key === 'Escape') {
                        cancelInlineEdit(cell, originalValue);
                    }
                });
            });
        });
    }

    function createInlineEditor(value, type) {
        switch (type) {
            case 'select':
                // Vous devrez adapter ceci selon vos options
                return `<select class="inline-editor">${getSelectOptions(value)}</select>`;
            case 'textarea':
                return `<textarea class="inline-editor" rows="3">${value}</textarea>`;
            case 'number':
                return `<input type="number" class="inline-editor" value="${value}">`;
            case 'date':
                return `<input type="date" class="inline-editor" value="${value}">`;
            default:
                return `<input type="text" class="inline-editor" value="${value}">`;
        }
    }

    function finishInlineEdit(cell, originalValue) {
        const input = cell.querySelector('.inline-editor');
        const newValue = input.value;

        if (newValue !== originalValue) {
            // Sauvegarder la nouvelle valeur
            saveInlineEdit(cell, newValue, originalValue);
        } else {
            cancelInlineEdit(cell, originalValue);
        }
    }

    function saveInlineEdit(cell, newValue, originalValue) {
        const row = cell.closest('tr');
        const column = cell.getAttribute('data-field');
        const recordId = row.getAttribute('data-id');

        // Afficher un loader
        cell.innerHTML = '<div class="inline-saving">💾</div>';

        // Simuler la sauvegarde AJAX
        setTimeout(() => {
            cell.textContent = newValue;
            cell.classList.remove('editing');
            cell.classList.add('just-saved');

            showFlashMessage('success', 'Modification sauvegardée');

            setTimeout(() => {
                cell.classList.remove('just-saved');
            }, 2000);

            // Ici, vous feriez un vrai appel AJAX
            // saveFieldValue(recordId, column, newValue);
        }, 500);
    }

    function cancelInlineEdit(cell, originalValue) {
        cell.textContent = originalValue;
        cell.classList.remove('editing');
    }

    // ==============================================
    // SYSTÈME DE FILTRES AVANCÉS
    // ==============================================

    function initAdminFilters() {
        // Filtres rapides
        initQuickFilters();

        // Filtres avancés
        initAdvancedFilters();

        // Recherche globale
        initGlobalSearch();

        // Sauvegarde des filtres
        initFilterPersistence();
    }

    function initQuickFilters() {
        document.querySelectorAll('.quick-filter').forEach(filter => {
            filter.addEventListener('change', function() {
                const filterType = this.getAttribute('data-filter');
                const filterValue = this.value;

                applyQuickFilter(filterType, filterValue);
            });
        });
    }

    function applyQuickFilter(type, value) {
        currentFilters[type] = value;

        // Appliquer immédiatement le filtre
        filterTableData();

        // Sauvegarder l'état
        saveFilterState();
    }

    function filterTableData() {
        const tables = document.querySelectorAll('.admin-table');

        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let shouldShow = true;

                // Appliquer chaque filtre
                for (const [filterType, filterValue] of Object.entries(currentFilters)) {
                    if (!filterValue) continue;

                    const cellValue = getCellValueForFilter(row, filterType);
                    if (!matchesFilter(cellValue, filterValue, filterType)) {
                        shouldShow = false;
                        break;
                    }
                }

                row.style.display = shouldShow ? '' : 'none';
            });

            updateTableStats(table);
        });
    }

    function getCellValueForFilter(row, filterType) {
        const cell = row.querySelector(`[data-filter-field="${filterType}"]`);
        return cell ? cell.textContent.trim() : '';
    }

    function matchesFilter(cellValue, filterValue, filterType) {
        switch (filterType) {
            case 'status':
                return cellValue.toLowerCase() === filterValue.toLowerCase();
            case 'date':
                return isDateInRange(cellValue, filterValue);
            case 'text':
                return cellValue.toLowerCase().includes(filterValue.toLowerCase());
            default:
                return cellValue.includes(filterValue);
        }
    }

    // ==============================================
    // NOTIFICATIONS ADMIN
    // ==============================================

    function initAdminNotifications() {
        // Notifications en temps réel
        initRealTimeNotifications();

        // Notifications push
        initPushNotifications();

        // Centre de notifications
        initNotificationCenter();
    }

    function initRealTimeNotifications() {
        // Simuler des notifications en temps réel
        setInterval(() => {
            if (Math.random() < 0.1) { // 10% de chance
                const notifications = [
                    {
                        type: 'info',
                        title: 'Nouvelle inscription',
                        message: 'Un nouvel étudiant s\'est inscrit',
                        icon: 'person_add'
                    },
                    {
                        type: 'warning',
                        title: 'Système',
                        message: 'Maintenance programmée dans 30 minutes',
                        icon: 'warning'
                    },
                    {
                        type: 'success',
                        title: 'Sauvegarde',
                        message: 'Sauvegarde automatique terminée',
                        icon: 'backup'
                    }
                ];

                const notification = notifications[Math.floor(Math.random() * notifications.length)];
                showAdminNotification(notification);
            }
        }, 30000); // Chaque 30 secondes
    }

    function showAdminNotification(notification) {
        // Ajouter à la liste des notifications
        addToNotificationCenter(notification);

        // Afficher une notification toast
        showNotificationToast(notification);

        // Mettre à jour le compteur
        updateNotificationBadge();
    }

    function addToNotificationCenter(notification) {
        const notificationsList = document.querySelector('.notifications-list');
        if (!notificationsList) return;

        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item unread';
        notificationItem.innerHTML = `
            <div class="notification-icon ${notification.type}">
                <span class="material-icons">${notification.icon}</span>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-text">${notification.message}</div>
                <div class="notification-time">${formatTime(new Date())}</div>
            </div>
        `;

        notificationsList.insertBefore(notificationItem, notificationsList.firstChild);

        // Limiter le nombre de notifications affichées
        const items = notificationsList.querySelectorAll('.notification-item');
        if (items.length > 20) {
            items[items.length - 1].remove();
        }
    }

    function showNotificationToast(notification) {
        const toast = document.createElement('div');
        toast.className = `notification-toast ${notification.type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <span class="material-icons">${notification.icon}</span>
            </div>
            <div class="toast-content">
                <div class="toast-title">${notification.title}</div>
                <div class="toast-message">${notification.message}</div>
            </div>
            <button class="toast-close">
                <span class="material-icons">close</span>
            </button>
        `;

        const container = getOrCreateToastContainer();
        container.appendChild(toast);

        // Auto-fermeture
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Fermeture manuelle
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        });
    }

    function getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // ==============================================
    // DASHBOARD ADMIN
    // ==============================================

    function initAdminDashboard() {
        // Widgets du dashboard
        initDashboardWidgets();

        // Graphiques
        initDashboardCharts();

        // Métriques en temps réel
        initRealTimeMetrics();

        // Actions rapides
        initQuickActions();
    }

    function initDashboardWidgets() {
        // Widgets redimensionnables
        initResizableWidgets();

        // Widgets repositionnables
        initDraggableWidgets();

        // Personnalisation des widgets
        initWidgetCustomization();
    }

    function loadDashboardContent() {
        const dashboardContent = document.getElementById('dashboard-content');
        if (!dashboardContent) return;

        // Charger les statistiques
        loadDashboardStats();

        // Charger les graphiques
        loadDashboardCharts();

        // Charger les activités récentes
        loadRecentActivities();
    }

    function loadDashboardStats() {
        // Simuler le chargement des statistiques
        const stats = {
            totalUsers: 1247,
            activeUsers: 892,
            newUsers: 45,
            systemLoad: 68
        };

        // Mettre à jour les cartes de statistiques
        updateStatCard('total-users', stats.totalUsers);
        updateStatCard('active-users', stats.activeUsers);
        updateStatCard('new-users', stats.newUsers);
        updateStatCard('system-load', stats.systemLoad + '%');
    }

    function updateStatCard(cardId, value) {
        const card = document.querySelector(`[data-stat="${cardId}"]`);
        if (card) {
            const valueElement = card.querySelector('.stat-value');
            if (valueElement) {
                animateCounterUpdate(valueElement, value);
            }
        }
    }

    function animateCounterUpdate(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        const increment = (newValue - currentValue) / 20;
        let current = currentValue;

        const timer = setInterval(() => {
            current += increment;
            if (
                (increment > 0 && current >= newValue) ||
                (increment < 0 && current <= newValue)
            ) {
                element.textContent = newValue;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 50);
    }

    // ==============================================
    // GRAPHIQUES ET VISUALISATIONS
    // ==============================================

    function initAdminCharts() {
        // Initialiser les graphiques si Chart.js est disponible
        if (typeof Chart !== 'undefined') {
            initChartInstances();
        } else {
            console.warn('Chart.js non trouvé - les graphiques ne seront pas affichés');
        }
    }

    function initChartInstances() {
        // Graphique d'évolution des utilisateurs
        const userChart = document.getElementById('userChart');
        if (userChart) {
            createUserEvolutionChart(userChart);
        }

        // Graphique de répartition des rôles
        const rolesChart = document.getElementById('rolesChart');
        if (rolesChart) {
            createRolesDistributionChart(rolesChart);
        }
    }

    // ==============================================
    // UTILITAIRES ADMIN
    // ==============================================

    function saveCurrentState() {
        const state = {
            section: currentSection,
            tab: currentTab,
            filters: currentFilters,
            sort: currentSort,
            timestamp: Date.now()
        };

        localStorage.setItem('admin-state', JSON.stringify(state));
    }

    function restoreState() {
        const savedState = localStorage.getItem('admin-state');
        if (savedState) {
            try {
                const state = JSON.parse(savedState);

                // Restaurer la section
                if (state.section) {
                    switchAdminSection(state.section);
                }

                // Restaurer l'onglet
                if (state.tab) {
                    const container = document.querySelector('.admin-tabs')?.parentElement;
                    if (container) {
                        switchAdminTab(state.tab, container);
                    }
                }

                // Restaurer les filtres
                if (state.filters) {
                    currentFilters = state.filters;
                    restoreFilterInputs();
                }
            } catch (e) {
                console.error('Erreur lors de la restauration de l\'état:', e);
            }
        }
    }

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

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatTime(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    function updateURL(section, tab = null) {
        const url = new URL(window.location);
        url.searchParams.set('section', section);

        if (tab) {
            url.searchParams.set('tab', tab);
        } else {
            url.searchParams.delete('tab');
        }

        window.history.pushState({ section, tab }, '', url);
    }

    // ==============================================
    // GESTIONNAIRE D'ERREURS ADMIN
    // ==============================================

    function initAdminErrorHandling() {
        window.addEventListener('error', function(e) {
            console.error('Erreur Admin:', e.error);

            // Log l'erreur côté serveur si nécessaire
            logErrorToServer(e.error);

            // Afficher une notification d'erreur
            showAdminNotification({
                type: 'danger',
                title: 'Erreur système',
                message: 'Une erreur inattendue s\'est produite',
                icon: 'error'
            });
        });
    }

    function logErrorToServer(error) {
        // Implémenter l'envoi d'erreurs au serveur
        fetch('/admin/log-error', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: error.message,
                stack: error.stack,
                url: window.location.href,
                timestamp: new Date().toISOString()
            })
        }).catch(console.error);
    }

    // ==============================================
    // API PUBLIQUE ADMIN
    // ==============================================

    // Exposer les fonctions utiles globalement
    window.AdminInterface = {
        switchSection: switchAdminSection,
        switchTab: switchAdminTab,
        showConfirmation: showConfirmationModal,
        showNotification: showAdminNotification,
        applyFilter: applyQuickFilter,
        saveState: saveCurrentState,
        restoreState: restoreState,
        version: '1.0.0'
    };

    // ==============================================
    // INITIALISATION FINALE
    // ==============================================

    // Restaurer l'état au chargement
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(restoreState, 100);
        initAdminErrorHandling();
    });

})();

// ==============================================
// STYLES CSS POUR LES TOASTS
// ==============================================

const adminToastStyles = document.createElement('style');
adminToastStyles.textContent = `
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        pointer-events: none;
    }
    
    .notification-toast {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        margin-bottom: 12px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-left: 4px solid var(--primary-accent);
        max-width: 400px;
        pointer-events: auto;
        animation: slideInRight 0.3s ease-out;
    }
    
    .notification-toast.success { border-left-color: var(--primary-accent); }
    .notification-toast.warning { border-left-color: var(--warning-accent); }
    .notification-toast.danger { border-left-color: var(--danger-accent); }
    .notification-toast.info { border-left-color: var(--info-accent); }
    
    .toast-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .notification-toast.success .toast-icon { background: var(--primary-accent); }
    .notification-toast.warning .toast-icon { background: var(--warning-accent); }
    .notification-toast.danger .toast-icon { background: var(--danger-accent); }
    .notification-toast.info .toast-icon { background: var(--info-accent); }
    
    .toast-content {
        flex: 1;
        min-width: 0;
    }
    
    .toast-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .toast-message {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.4;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .toast-close:hover {
        background: var(--hover-bg);
        color: var(--text-primary);
    }
    
    .toast-close .material-icons {
        font-size: 16px;
    }
    
    .notification-toast.fade-out {
        animation: slideOutRight 0.3s ease-in forwards;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
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
    
    .inline-editor {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid var(--primary-accent);
        border-radius: 4px;
        font-size: inherit;
        font-family: inherit;
    }
    
    .inline-saving {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        color: var(--primary-accent);
    }
    
    .just-saved {
        background: var(--primary-accent-light) !important;
        transition: background-color 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .toast-container {
            left: 16px;
            right: 16px;
            top: 80px;
        }
        
        .notification-toast {
            max-width: none;
        }
    }
`;

document.head.appendChild(adminToastStyles);