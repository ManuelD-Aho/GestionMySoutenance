document.addEventListener('DOMContentLoaded', function () {
    // Mock Data
    let mockUsers = [];
    let mockUserTypes = [];
    let mockUserGroups = [];
    let mockAccessLevels = [];
    let mockTreatments = [];
    let mockPermissionsAssignments = []; // { groupId: 'GRP_ID', treatmentId: 'TREAT_ID' }
    let mockAcademicYears = [];
    let mockStudyLevels = [];
    let mockSpecialities = [];
    let mockGenericStatuses = []; // For various status types
    let mockPdfTemplates = [];
    let mockEmailTemplates = []; // Table Message
    let mockAppSettings = {};
    let mockWorkflowItems = [];
    let mockPVs = []; // Procès-verbaux
    let mockSystemNotifications = []; // Table Recevoir
    let mockAuditLogsEnregistrer = [];
    let mockAuditLogsPister = [];
    let mockReports = [];
    let mockDashboardActivities = [];
    let mockNotifications = [];

    const currentAdminUser = {
        login: 'ManuelD-Aho',
        nom: 'Aho',
        prenom: 'Manuel D.',
        role: 'Administrateur Système',
        initials: 'MA'
    };

    // State variables
    let currentUserFormStep = 1;
    let selectedUserTypeForCreation = '';
    let currentUsersTablePage = { 'all': 1, 'students': 1, 'teachers': 1, 'staff': 1 };
    let usersPerPage = { 'all': 25, 'students': 25, 'teachers': 25, 'staff': 25 };
    let currentSort = { 'all': { column: 'login_utilisateur', order: 'asc' } }; // Add for other tabs

    // DOM Elements (Cache frequently used ones)
    const pageTitle = document.getElementById('page-title');
    const breadcrumbCurrentPage = document.getElementById('breadcrumb-current-page');
    const mainContentSections = document.querySelectorAll('.content-section');
    const navItems = document.querySelectorAll('.nav-item');
    const userFormModal = document.getElementById('user-form-modal');
    const userModalTitle = document.getElementById('user-modal-title');
    const userForm = document.getElementById('user-form');
    const userFormStepIndicator = document.getElementById('user-form-step-indicator');
    const userFormStepContents = userForm.querySelectorAll('.step-content');
    const userFormPrevBtn = document.getElementById('user-form-prev-step-btn');
    const userFormNextBtn = document.getElementById('user-form-next-step-btn');
    const userFormSubmitBtn = document.getElementById('user-form-submit-btn');
    const userFormModeInput = document.getElementById('user-form-mode');
    const userFormEditIdInput = document.getElementById('user-form-edit-id');
    const userFormTypeSelect = document.getElementById('user-form-type');

    const genericCrudModal = document.getElementById('generic-crud-modal');
    const genericCrudModalTitle = document.getElementById('generic-crud-modal-title');
    const genericCrudForm = document.getElementById('generic-crud-form');
    const genericCrudFormFields = document.getElementById('generic-crud-form-fields');
    const genericCrudEntityTypeInput = document.getElementById('generic-crud-entity-type');
    const genericCrudModeInput = document.getElementById('generic-crud-mode');
    const genericCrudEditIdInput = document.getElementById('generic-crud-edit-id');

    const alertConfirmationModal = document.getElementById('alert-confirmation-modal');
    const alertConfirmationTitle = document.getElementById('alert-confirmation-title');
    const alertConfirmationMessage = document.getElementById('alert-confirmation-message');
    const alertConfirmationConfirmBtn = document.getElementById('alert-confirmation-confirm-btn');
    let alertConfirmationCallback = null;

    // Initialization
    function initializeApp() {
        console.log("Initializing Admin Dashboard...");
        setupAdminInfo();
        generateMockData();
        setupEventListeners();
        renderDashboard();
        populateUserFormDropdowns();
        renderUsersTable('all', 1); // Initial render for "Vue Générale"
        renderPermissionsSection();
        renderSystemConfigSection();
        renderAcademicYearSection();
        renderTemplatesDocsSection();
        renderAppSettingsSection();
        renderWorkflowMonitoringSection();
        renderPVManagementSection();
        renderSystemNotificationsMgmtSection();
        renderAuditLogsSection();
        renderDataToolsSection();
        renderTechnicalMaintenanceSection();
        renderReportingAnalyticsSection();
        updateNotificationCount();
        console.log("Admin Dashboard Initialized.");
    }

    function setupAdminInfo() {
        document.getElementById('admin-avatar-initials').textContent = currentAdminUser.initials;
        document.getElementById('admin-name-display').textContent = `${currentAdminUser.prenom} ${currentAdminUser.nom}`;
        document.getElementById('admin-role-display').textContent = currentAdminUser.role;
    }

    // Mock Data Generation
    function generateMockData() {
        // User Types (C.1)
        mockUserTypes = [
            { id_type_utilisateur: 'TYPE_ETUD', libelle_type_utilisateur: 'Étudiant' },
            { id_type_utilisateur: 'TYPE_ENS', libelle_type_utilisateur: 'Enseignant' },
            { id_type_utilisateur: 'TYPE_PERS_ADMIN', libelle_type_utilisateur: 'Personnel Administratif' },
            { id_type_utilisateur: 'TYPE_ADMIN_SYS', libelle_type_utilisateur: 'Administrateur Système' }
        ];

        // User Groups (C.2)
        mockUserGroups = [
            { id_groupe_utilisateur: 'GRP_ETUDIANT_L1', libelle_groupe_utilisateur: 'Étudiants L1' },
            { id_groupe_utilisateur: 'GRP_ETUDIANT_M2_INFO', libelle_groupe_utilisateur: 'Étudiants M2 Informatique' },
            { id_groupe_utilisateur: 'GRP_ENSEIGNANT_CHERCHEUR', libelle_groupe_utilisateur: 'Enseignants Chercheurs' },
            { id_groupe_utilisateur: 'GRP_COMMISSION_VALID', libelle_groupe_utilisateur: 'Commission de Validation' },
            { id_groupe_utilisateur: 'GRP_AGENT_CONFORMITE', libelle_groupe_utilisateur: 'Agents de Contrôle Conformité' },
            { id_groupe_utilisateur: 'GRP_GEST_SCOL', libelle_groupe_utilisateur: 'Gestionnaires Scolarité' },
            { id_groupe_utilisateur: 'GRP_ADMIN_TECH', libelle_groupe_utilisateur: 'Administrateurs Techniques' }
        ];

        // Access Levels (C.3)
        mockAccessLevels = [
            { id_niveau_acces_donne: 'LVL_PERSONNEL', libelle_niveau_acces_donne: 'Données Personnelles Uniquement' },
            { id_niveau_acces_donne: 'LVL_DEPARTEMENT', libelle_niveau_acces_donne: 'Données du Département' },
            { id_niveau_acces_donne: 'LVL_SERVICE_SCOL', libelle_niveau_acces_donne: 'Données Service Scolarité' },
            { id_niveau_acces_donne: 'LVL_GLOBAL_READ', libelle_niveau_acces_donne: 'Lecture Globale' },
            { id_niveau_acces_donne: 'LVL_GLOBAL_WRITE', libelle_niveau_acces_donne: 'Accès Global (Lecture/Écriture)' }
        ];

        // Treatments (Fonctionnalités) (C.4.1)
        mockTreatments = [
            { id_traitement: 'USER_CREATE_ETUD', libelle_traitement: 'Créer Utilisateur Étudiant' },
            { id_traitement: 'USER_VIEW_LIST', libelle_traitement: 'Voir Liste des Utilisateurs' },
            { id_traitement: 'USER_EDIT_ANY', libelle_traitement: 'Modifier Tout Utilisateur' },
            { id_traitement: 'RAPPORT_VALID_CONF', libelle_traitement: 'Valider Conformité Rapport' },
            { id_traitement: 'CONFIG_ACADEMIC_YEAR', libelle_traitement: 'Configurer Année Académique' },
            { id_traitement: 'VIEW_AUDIT_LOGS', libelle_traitement: 'Consulter Journaux d\'Audit' },
            { id_traitement: 'ASSIGN_PERMISSIONS', libelle_traitement: 'Assigner Permissions aux Groupes' },
        ];

        // Permissions Assignments (rattacher) (C.4.2)
        mockPermissionsAssignments = [
            { id_groupe_utilisateur: 'GRP_GEST_SCOL', id_traitement: 'USER_CREATE_ETUD' },
            { id_groupe_utilisateur: 'GRP_GEST_SCOL', id_traitement: 'USER_VIEW_LIST' },
            { id_groupe_utilisateur: 'GRP_AGENT_CONFORMITE', id_traitement: 'RAPPORT_VALID_CONF' },
            { id_groupe_utilisateur: 'GRP_ADMIN_TECH', id_traitement: 'USER_EDIT_ANY' },
            { id_groupe_utilisateur: 'GRP_ADMIN_TECH', id_traitement: 'CONFIG_ACADEMIC_YEAR' },
            { id_groupe_utilisateur: 'GRP_ADMIN_TECH', id_traitement: 'VIEW_AUDIT_LOGS' },
            { id_groupe_utilisateur: 'GRP_ADMIN_TECH', id_traitement: 'ASSIGN_PERMISSIONS' },
        ];


        // Users (B.0)
        const firstNames = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Fiona', 'George', 'Hannah', 'Ian', 'Julia'];
        const lastNames = ['Smith', 'Jones', 'Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson'];
        const statutsCompte = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];

        for (let i = 0; i < 150; i++) {
            const type = mockUserTypes[Math.floor(Math.random() * mockUserTypes.length)];
            const group = mockUserGroups[Math.floor(Math.random() * mockUserGroups.length)];
            const accessLevel = mockAccessLevels[Math.floor(Math.random() * mockAccessLevels.length)];
            const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
            const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
            const login = `${firstName.toLowerCase().substring(0,3)}${lastName.toLowerCase().substring(0,3)}${i}`;
            const email = `${login}@example.com`;

            let userSpecificIdField = {};
            let userSpecificProfileFields = {};

            switch (type.id_type_utilisateur) {
                case 'TYPE_ETUD':
                    userSpecificIdField = { numero_carte_etudiant: `ETUD${String(1000 + i).padStart(6, '0')}` };
                    userSpecificProfileFields = {
                        date_naissance: `199${Math.floor(Math.random() * 10)}-0${Math.ceil(Math.random()*9)}-${String(Math.ceil(Math.random()*28)).padStart(2,'0')}`,
                        lieu_naissance: "Quelquepart",
                        telephone: `06${String(Math.floor(Math.random() * 90000000) + 10000000)}`
                    };
                    break;
                case 'TYPE_ENS':
                    userSpecificIdField = { numero_enseignant: `ENS${String(100 + i).padStart(5, '0')}` };
                    userSpecificProfileFields = {
                        telephone_professionnel: `01${String(Math.floor(Math.random() * 90000000) + 10000000)}`,
                        email_professionnel: `${login}@univ-example.fr`,
                    };
                    break;
                case 'TYPE_PERS_ADMIN':
                    userSpecificIdField = { numero_personnel_administratif: `PERS${String(10 + i).padStart(4, '0')}` };
                    userSpecificProfileFields = {
                        telephone_professionnel: `01${String(Math.floor(Math.random() * 90000000) + 10000000)}`,
                        email_professionnel: `${login}@admin-example.fr`,
                        responsabilites_cles: "Gestion diverse"
                    };
                    break;
                case 'TYPE_ADMIN_SYS':
                    userSpecificIdField = { numero_personnel_administratif: `ADM${String(i).padStart(3, '0')}` };
                    userSpecificProfileFields = {
                        telephone_professionnel: `01000000${i.toString().padStart(2,'0')}`,
                        email_professionnel: `${login}@system.com`,
                    };
                    break;
            }

            mockUsers.push({
                numero_utilisateur: `USR_${Date.now()}_${i}`, // Unique ID
                login_utilisateur: login,
                email_principal: email,
                nom: lastName,
                prenom: firstName,
                id_type_utilisateur: type.id_type_utilisateur,
                id_groupe_utilisateur: group.id_groupe_utilisateur,
                id_niveau_acces_donne: accessLevel.id_niveau_acces_donne,
                statut_compte: statutsCompte[Math.floor(Math.random() * statutsCompte.length)],
                date_creation: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000).toISOString(),
                derniere_connexion: Math.random() > 0.3 ? new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString() : null,
                photo_profil: `https://i.pravatar.cc/40?u=${login}`,
                email_valide: Math.random() > 0.2,
                preferences_2fa_active: Math.random() > 0.7,
                tentatives_connexion_echouees: Math.floor(Math.random() * 3),
                ...userSpecificIdField, // numero_carte_etudiant, numero_enseignant, etc.
                ...userSpecificProfileFields // Other profile fields
            });
        }
        // Add current admin
        mockUsers.push({
            numero_utilisateur: `ADM_SYS_001`,
            login_utilisateur: currentAdminUser.login,
            email_principal: `${currentAdminUser.login.toLowerCase()}@system.com`,
            nom: currentAdminUser.nom,
            prenom: currentAdminUser.prenom,
            id_type_utilisateur: 'TYPE_ADMIN_SYS',
            id_groupe_utilisateur: 'GRP_ADMIN_TECH',
            id_niveau_acces_donne: 'LVL_GLOBAL_WRITE',
            statut_compte: 'actif',
            date_creation: new Date().toISOString(),
            derniere_connexion: new Date().toISOString(),
            photo_profil: `https://i.pravatar.cc/40?u=${currentAdminUser.login}`,
            email_valide: true,
            preferences_2fa_active: true,
            tentatives_connexion_echouees: 0,
            numero_personnel_administratif: `ADM001`
        });


        // Academic Years (D.1)
        mockAcademicYears = [
            { id_annee_academique: 'AA_2023_2024', libelle_annee_academique: '2023-2024', date_debut: '2023-09-01', date_fin: '2024-08-31', est_active: false },
            { id_annee_academique: 'AA_2024_2025', libelle_annee_academique: '2024-2025', date_debut: '2024-09-01', date_fin: '2025-08-31', est_active: true },
            { id_annee_academique: 'AA_2025_2026', libelle_annee_academique: '2025-2026', date_debut: '2025-09-01', date_fin: '2026-08-31', est_active: false },
        ];

        mockStudyLevels = [
            { id_niveau_etude: 'L1', libelle_niveau_etude: 'Licence 1ère année' },
            { id_niveau_etude: 'L2', libelle_niveau_etude: 'Licence 2ème année' },
            { id_niveau_etude: 'L3', libelle_niveau_etude: 'Licence 3ème année' },
            { id_niveau_etude: 'M1', libelle_niveau_etude: 'Master 1ère année' },
            { id_niveau_etude: 'M2', libelle_niveau_etude: 'Master 2ème année' },
        ];
        mockSpecialities = [
            { id_specialite: 'INFO_DEV', libelle_specialite: 'Informatique - Développement Logiciel' },
            { id_specialite: 'INFO_RESEAU', libelle_specialite: 'Informatique - Réseaux et Sécurité' },
            { id_specialite: 'MATH_APP', libelle_specialite: 'Mathématiques Appliquées' },
        ];
        mockGenericStatuses = [ // Example for statut_rapport_ref
            { id_statut: 'RAP_SOUMIS', libelle_statut: 'Rapport Soumis', type: 'rapport' },
            { id_statut: 'RAP_CONFORME', libelle_statut: 'Conforme', type: 'rapport' },
            { id_statut: 'RAP_VALIDE', libelle_statut: 'Validé par Commission', type: 'rapport' },
            { id_statut: 'PV_BROUILLON', libelle_statut: 'PV Brouillon', type: 'pv' },
            { id_statut: 'PV_VALID', libelle_statut: 'PV Validé', type: 'pv' },
        ];

        // Templates & Docs (D.3)
        mockPdfTemplates = [
            { id_template: 'PDF_ATTEST_DEPOT', nom_template: 'Attestation de Dépôt de Rapport', date_creation: '2024-01-15', version: '1.2' },
            { id_template: 'PDF_PV_SOUTENANCE', nom_template: 'Procès-Verbal de Soutenance', date_creation: '2024-02-01', version: '2.0' },
        ];
        mockEmailTemplates = [ // Table Message
            { id_message: 'EMAIL_COMPTE_CREE', sujet_message: 'Votre compte GestionMySoutenance a été créé', libelle_message: 'Cher {{prenom}} {{nom}}, ...', type_message: 'EMAIL_CREATION_COMPTE' },
            { id_message: 'EMAIL_RAPPORT_VALIDE', sujet_message: 'Votre rapport a été validé', libelle_message: 'Félicitations {{prenom}}, votre rapport "{{titre_rapport}}" a été validé...', type_message: 'EMAIL_RAPPORT_STATUT' },
        ];

        // App Settings (D.2)
        mockAppSettings = {
            delai_validation_conformite_jours: 5,
            max_taille_fichier_rapport_mo: 10,
            formats_rapport_autorises: '.pdf,.doc,.docx',
            seuil_alerte_rapport_attente_jours: 7,
            smtp_server: 'smtp.example.com',
            smtp_port: 587,
            admin_email_contact: 'admin@gestionsoutenance.com'
        };

        // Audit Logs (F.4)
        const actions = ['Création utilisateur', 'Modification profil', 'Connexion réussie', 'Changement de statut', 'Permission assignée'];
        const entites = ['Utilisateur', 'Rapport', 'Permission', 'Système'];
        for (let i = 0; i < 50; i++) {
            const user = mockUsers[Math.floor(Math.random() * mockUsers.length)];
            mockAuditLogsEnregistrer.push({
                id_enregistrement: `ENR_${Date.now()}_${i}`,
                numero_utilisateur: user.numero_utilisateur, // Qui
                id_action: actions[Math.floor(Math.random() * actions.length)], // Quoi (libelle_action)
                date_action: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString(), // Quand
                type_entite_concernee: entites[Math.floor(Math.random() * entites.length)],
                id_entite_concernee: `ID_ENTITE_${Math.floor(Math.random() * 1000)}`,
                details_action: JSON.stringify({ oldValue: 'X', newValue: 'Y', ip_address: `192.168.1.${Math.floor(Math.random()*255)}` }),
                adresse_ip: `192.168.1.${Math.floor(Math.random()*255)}`,
                user_agent: 'Mozilla/5.0 ...'
            });
            mockAuditLogsPister.push({
                id_piste: `PISTE_${Date.now()}_${i}`,
                numero_utilisateur: user.numero_utilisateur,
                id_traitement: mockTreatments[Math.floor(Math.random() * mockTreatments.length)].id_traitement,
                date_pister: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString(),
                acceder: Math.random() > 0.1 // true for granted, false for denied
            });
        }

        // Dashboard Activities (A)
        mockDashboardActivities = mockAuditLogsEnregistrer.slice(0, 10).map(log => ({
            icon: getIconForAction(log.id_action),
            bgColorClass: getBgColorForAction(log.id_action),
            text: `${log.id_action} par <strong>${getUserDisplay(log.numero_utilisateur)}</strong> sur ${log.type_entite_concernee} ${log.id_entite_concernee.substring(0,15)}...`,
            time: timeAgo(log.date_action),
            type: log.type_entite_concernee === 'Utilisateur' ? 'user_management' : (log.id_action.includes('Permission') ? 'security' : 'system_config')
        }));

        // Notifications (Header)
        mockNotifications = [
            { id: 1, text: "Maintenance système prévue ce soir à 23h.", time: "il y a 10 minutes", read: false, type: 'system' },
            { id: 2, text: "Rapport de sécurité mensuel disponible.", time: "il y a 2 heures", read: false, type: 'report' },
            { id: 3, text: "3 nouveaux utilisateurs en attente de validation.", time: "il y a 5 heures", read: true, type: 'user' },
        ];
    }

    function getUserDisplay(numeroUtilisateur) {
        const user = mockUsers.find(u => u.numero_utilisateur === numeroUtilisateur);
        return user ? `${user.prenom} ${user.nom} (${user.login_utilisateur})` : numeroUtilisateur;
    }

    function getIconForAction(action) {
        if (action.toLowerCase().includes('création') || action.toLowerCase().includes('ajout')) return 'add_circle_outline';
        if (action.toLowerCase().includes('modification') || action.toLowerCase().includes('mise à jour')) return 'edit';
        if (action.toLowerCase().includes('suppression') || action.toLowerCase().includes('archivage')) return 'delete_outline';
        if (action.toLowerCase().includes('connexion')) return 'login';
        if (action.toLowerCase().includes('permission')) return 'security';
        return 'settings';
    }
    function getBgColorForAction(action) {
        if (action.toLowerCase().includes('création') || action.toLowerCase().includes('connexion')) return 'icon-bg-green';
        if (action.toLowerCase().includes('modification')) return 'icon-bg-blue';
        if (action.toLowerCase().includes('permission')) return 'icon-bg-orange';
        return 'icon-bg-grey'; // Define icon-bg-grey or use another
    }


    // Navigation Logic
    function navigateToSection(sectionId, subTab = null) {
        mainContentSections.forEach(sec => sec.classList.remove('active'));
        const targetSection = document.getElementById(sectionId + '-content');
        if (targetSection) {
            targetSection.classList.add('active');
            const navItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
            pageTitle.textContent = navItem ? navItem.querySelector('span:last-child').textContent : sectionId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            breadcrumbCurrentPage.textContent = pageTitle.textContent;

            if (subTab) {
                const tabContainer = targetSection.querySelector('.users-tabs, .permissions-tabs-container, .system_config-tabs-container, .audit_logs-tabs-container, .templates_docs-tabs-container'); // Add other tab containers
                if (tabContainer) {
                    setActiveTab(tabContainer, subTab);
                }
            }
        } else {
            console.warn(`Section ${sectionId}-content not found!`);
            document.getElementById('dashboard-content').classList.add('active'); // Fallback to dashboard
            pageTitle.textContent = 'Tableau de Bord';
            breadcrumbCurrentPage.textContent = 'Dashboard';
        }

        navItems.forEach(item => item.classList.remove('active'));
        const activeNavItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
        if (activeNavItem) activeNavItem.classList.add('active');
    }

    function setActiveTab(tabContainer, tabId) {
        const tabs = tabContainer.querySelectorAll('.users-tab'); // Generic enough for now
        const tabContents = tabContainer.parentElement.parentElement.querySelectorAll('.users-tab-content, .permissions-tab-content, .system_config-tab-content, .audit_logs-tab-content, .templates_docs-tab-content'); // Adjust selectors

        tabs.forEach(tab => tab.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        const activeTab = tabContainer.querySelector(`.users-tab[data-tab="${tabId}"]`);
        const activeContent = document.getElementById(`${tabContainer.id.replace('-tabs-container', '')}-${tabId}-content`);

        if (activeTab) activeTab.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
        else console.warn(`Tab content for ${tabId} not found in container ${tabContainer.id}`);
    }


    // Event Listeners
    function setupEventListeners() {
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionId = item.dataset.section;
                navigateToSection(sectionId);
            });
        });

        // User Management Tabs
        const usersTabsContainer = document.getElementById('users-tabs-container');
        if (usersTabsContainer) {
            usersTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) {
                    const tabId = e.target.dataset.tab;
                    setActiveTab(usersTabsContainer, tabId);
                    if (tabId !== 'overview') { // 'overview' is 'all'
                        renderUsersTable(tabId, 1); // Render specific user type table
                    } else {
                        renderUsersTable('all', 1);
                    }
                }
            });
        }

        // Permissions Tabs
        const permissionsTabsContainer = document.getElementById('permissions-tabs-container');
        if (permissionsTabsContainer) {
            permissionsTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) { // Re-using .users-tab class
                    const tabId = e.target.dataset.tab;
                    setActiveTab(permissionsTabsContainer, tabId);
                    // Call render function for the specific permissions tab
                    renderPermissionsSection(tabId);
                }
            });
        }

        // System Config Tabs
        const systemConfigTabsContainer = document.getElementById('system_config-tabs-container');
        if (systemConfigTabsContainer) {
            systemConfigTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) {
                    const tabId = e.target.dataset.tab.replace('referentiels_', ''); // e.g. annee_academique
                    setActiveTab(systemConfigTabsContainer, e.target.dataset.tab);
                    renderSystemConfigSection(tabId);
                }
            });
        }

        // Templates & Docs Tabs
        const templatesDocsTabsContainer = document.getElementById('templates_docs-tabs-container');
        if (templatesDocsTabsContainer) {
            templatesDocsTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) {
                    const tabId = e.target.dataset.tab; // pdf_templates or email_templates
                    setActiveTab(templatesDocsTabsContainer, tabId);
                    renderTemplatesDocsSection(tabId);
                }
            });
        }

        // Audit Logs Tabs
        const auditLogsTabsContainer = document.getElementById('audit_logs-tabs-container');
        if (auditLogsTabsContainer) {
            auditLogsTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) {
                    const tabId = e.target.dataset.tab; // actions_log or access_log
                    setActiveTab(auditLogsTabsContainer, tabId);
                    renderAuditLogsSection(tabId);
                }
            });
        }


        // User Form Modal
        userForm.addEventListener('submit', handleUserFormSubmit);
        userFormTypeSelect.addEventListener('change', updateUserFormForType); // For dynamic fields in step 1/2

        // Generic CRUD Modal Form
        genericCrudForm.addEventListener('submit', handleGenericCrudFormSubmit);

        // Alert Confirmation Modal
        alertConfirmationConfirmBtn.addEventListener('click', () => {
            if (alertConfirmationCallback) alertConfirmationCallback();
            closeAlertConfirmationModal();
        });

        // Dashboard activity filters
        const dashboardActivityFilters = document.getElementById('dashboard-activity-filters');
        if (dashboardActivityFilters) {
            dashboardActivityFilters.addEventListener('click', (e) => {
                if (e.target.classList.contains('filter-btn')) {
                    dashboardActivityFilters.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    renderDashboardActivities(e.target.dataset.filter);
                }
            });
        }
    }

    // Dashboard Rendering (A)
    function renderDashboard() {
        updateDashboardStats(); // A.1
        renderDashboardActivities('all'); // Part of A (Recent Activities)
        renderDashboardSystemHealth(); // A.2 (Simulated)
        renderDashboardPermissionsOverview(); // Part of A.3 (Quick links to C)
        renderDashboardUserTypeSummary(); // Part of A.1
    }

    function updateDashboardStats() {
        const statsGrid = document.getElementById('dashboard-stats-grid');
        if (!statsGrid) return;

        const activeUsers = mockUsers.filter(u => u.statut_compte === 'actif').length;
        // Simulate other stats for now
        const pendingReports = Math.floor(Math.random() * 100);
        const systemActions = mockAuditLogsEnregistrer.length + mockAuditLogsPister.length;
        const systemAlerts = mockNotifications.filter(n => !n.read && n.type !== 'report').length; // Example

        statsGrid.innerHTML = `
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Utilisateurs Actifs</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">people</span></div>
                </div>
                <p class="stat-value">${activeUsers.toLocaleString()}</p>
                <p class="stat-change positive"><span class="material-icons">arrow_upward</span> +${Math.floor(Math.random()*5+1)}%</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports en Cours (Sim.)</h3>
                    <div class="stat-icon icon-bg-orange"><span class="material-icons">assignment</span></div>
                </div>
                <p class="stat-value">${pendingReports}</p>
                <p class="stat-change neutral"><span class="material-icons">horizontal_rule</span> Stable</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Actions Système (Logs)</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">history_toggle_off</span></div>
                </div>
                <p class="stat-value">${systemActions.toLocaleString()}</p>
                <p class="stat-change positive"><span class="material-icons">arrow_upward</span> +${Math.floor(Math.random()*10+1)}%</p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Alertes Non Lues</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">warning</span></div>
                </div>
                <p class="stat-value">${systemAlerts}</p>
                <p class="stat-change negative"><span class="material-icons">priority_high</span> Attention requise</p>
            </div>
        `;
    }

    function renderDashboardUserTypeSummary() {
        const summaryContainer = document.getElementById('dashboard-user-type-summary');
        if (!summaryContainer) return;
        summaryContainer.innerHTML = mockUserTypes.map(type => {
            const count = mockUsers.filter(u => u.id_type_utilisateur === type.id_type_utilisateur && u.statut_compte === 'actif').length;
            return `
                <div class="summary-item">
                    <span class="summary-label">${type.libelle_type_utilisateur}s</span>
                    <span class="summary-count">${count}</span>
                </div>
            `;
        }).join('');
    }

    function renderDashboardActivities(filterType = 'all') {
        const listElement = document.getElementById('dashboard-activities-list');
        if (!listElement) return;

        let filteredActivities = mockDashboardActivities;
        if (filterType !== 'all') {
            filteredActivities = mockDashboardActivities.filter(activity => activity.type === filterType);
        }

        if (filteredActivities.length === 0) {
            listElement.innerHTML = '<p class="text-muted text-center">Aucune activité récente à afficher.</p>';
            return;
        }
        listElement.innerHTML = filteredActivities.slice(0, 5).map(activity => `
            <div class="activity-item">
                <div class="activity-icon ${activity.bgColorClass || 'icon-bg-grey'}">
                    <span class="material-icons">${activity.icon || 'info'}</span>
                </div>
                <div class="activity-details">
                    <p class="activity-text">${activity.text}</p>
                    <p class="activity-time">${activity.time}</p>
                </div>
            </div>
        `).join('');
    }

    function renderDashboardSystemHealth() {
        const healthMetricsContainer = document.getElementById('dashboard-health-metrics');
        if (!healthMetricsContainer) return;
        // Simulated data
        const dbUsage = Math.floor(Math.random() * 30 + 60); // 60-90%
        const serverLoad = Math.floor(Math.random() * 50 + 20); // 20-70%
        const storageUsage = Math.floor(Math.random() * 40 + 55); // 55-95%

        healthMetricsContainer.innerHTML = `
            <div class="metric-item">
                <div class="metric-header">
                    <span class="metric-label">Base de Données</span>
                    <span class="metric-status ${dbUsage > 85 ? 'warning' : 'healthy'}">${dbUsage > 85 ? 'Élevé' : 'OK'}</span>
                </div>
                <div class="metric-bar"><div class="metric-fill ${dbUsage > 85 ? 'warning' : ''}" style="width: ${dbUsage}%"></div></div>
                <span class="metric-value">${dbUsage}% utilisé</span>
            </div>
            <div class="metric-item">
                <div class="metric-header">
                    <span class="metric-label">Serveur Web</span>
                    <span class="metric-status ${serverLoad > 70 ? 'warning' : 'healthy'}">${serverLoad > 70 ? 'Charge' : 'OK'}</span>
                </div>
                <div class="metric-bar"><div class="metric-fill ${serverLoad > 70 ? 'warning' : ''}" style="width: ${serverLoad}%"></div></div>
                <span class="metric-value">${serverLoad}% charge CPU</span>
            </div>
            <div class="metric-item">
                <div class="metric-header">
                    <span class="metric-label">Stockage Principal</span>
                    <span class="metric-status ${storageUsage > 90 ? 'error' : (storageUsage > 80 ? 'warning' : 'healthy')}">
                        ${storageUsage > 90 ? 'Critique' : (storageUsage > 80 ? 'Attention' : 'OK')}
                    </span>
                </div>
                <div class="metric-bar"><div class="metric-fill ${storageUsage > 90 ? 'error' : (storageUsage > 80 ? 'warning' : '')}" style="width: ${storageUsage}%"></div></div>
                <span class="metric-value">${storageUsage}% utilisé</span>
            </div>
        `;
        const overallHealthIndicator = document.querySelector('#dashboard-overall-health .health-indicator');
        const overallHealthText = document.querySelector('#dashboard-overall-health span:last-child');
        if (dbUsage > 90 || serverLoad > 85 || storageUsage > 95) {
            overallHealthIndicator.className = 'health-indicator error';
            overallHealthText.textContent = 'Problèmes Critiques';
        } else if (dbUsage > 85 || serverLoad > 70 || storageUsage > 80) {
            overallHealthIndicator.className = 'health-indicator warning';
            overallHealthText.textContent = 'Avertissements Actifs';
        } else {
            overallHealthIndicator.className = 'health-indicator healthy';
            overallHealthText.textContent = 'Opérationnel';
        }
    }

    function renderDashboardPermissionsOverview() {
        const summaryContainer = document.getElementById('dashboard-permissions-summary');
        if (!summaryContainer) return;

        const activeGroups = mockUserGroups.filter(g => mockUsers.some(u => u.id_groupe_utilisateur === g.id_groupe_utilisateur && u.statut_compte === 'actif'));

        summaryContainer.innerHTML = `
            <div class="permission-group">
                <div class="group-header">
                    <span class="group-name">Types d'Utilisateur</span>
                    <span class="group-count">${mockUserTypes.length}</span>
                </div>
                <div class="group-items">
                    ${mockUserTypes.slice(0,4).map(ut => `<span class="group-item">${ut.libelle_type_utilisateur} (${mockUsers.filter(u => u.id_type_utilisateur === ut.id_type_utilisateur).length})</span>`).join('')}
                </div>
            </div>
            <div class="permission-group">
                <div class="group-header">
                    <span class="group-name">Groupes Actifs</span>
                    <span class="group-count">${activeGroups.length}</span>
                </div>
                <div class="group-items">
                     ${activeGroups.slice(0,3).map(g => `<span class="group-item">${g.libelle_groupe_utilisateur} (${mockUsers.filter(u => u.id_groupe_utilisateur === g.id_groupe_utilisateur && u.statut_compte === 'actif').length})</span>`).join('')}
                     ${activeGroups.length > 3 ? '<span class="group-item">...et plus</span>' : ''}
                </div>
            </div>
             <div class="permission-group">
                <div class="group-header">
                    <span class="group-name">Fonctionnalités Définies</span>
                    <span class="group-count">${mockTreatments.length}</span>
                </div>
            </div>
        `;
    }

    // User Management (B)
    function renderUsersTable(userTypeKey, page = 1) { // userTypeKey: 'all', 'students', 'teachers', 'staff'
        const tableIdSuffix = userTypeKey;
        const tableBody = document.getElementById(`users-table-body-${tableIdSuffix}`);
        const paginationControls = document.getElementById(`users-pagination-${tableIdSuffix}`);
        const tableTitle = document.getElementById(`users-table-title-${tableIdSuffix}`);

        if (!tableBody) {
            console.warn(`Table body for ${userTypeKey} not found.`);
            // Create the tab content if it doesn't exist (e.g., for students, teachers, staff)
            if (userTypeKey !== 'all') {
                const overviewContent = document.getElementById('users-overview-content').cloneNode(true);
                overviewContent.id = `users-${userTypeKey}-content`;
                overviewContent.classList.remove('active');
                overviewContent.querySelectorAll('[id]').forEach(el => el.id = el.id.replace('-all', `-${userTypeKey}`));
                overviewContent.querySelector('h3').textContent = `Recherche et Filtres (${userTypeKey.charAt(0).toUpperCase() + userTypeKey.slice(1)})`;
                overviewContent.querySelector(`#users-table-title-${userTypeKey}`).textContent = `Liste des ${userTypeKey.charAt(0).toUpperCase() + userTypeKey.slice(1)}`;

                const usersContentDiv = document.getElementById('users-content');
                usersContentDiv.appendChild(overviewContent);

                // Re-fetch newly created elements
                return renderUsersTable(userTypeKey, page); // Retry rendering
            }
            return;
        }

        let filteredUsers = [...mockUsers];

        // Apply type filter if not 'all'
        if (userTypeKey === 'students') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_ETUD');
        else if (userTypeKey === 'teachers') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_ENS');
        else if (userTypeKey === 'staff') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_PERS_ADMIN' || u.id_type_utilisateur === 'TYPE_ADMIN_SYS');

        // Apply search and filters from THIS tab's controls
        const searchTerm = document.getElementById(`user-search-${tableIdSuffix}`)?.value.toLowerCase() || '';
        const typeFilter = document.getElementById(`user-type-filter-${tableIdSuffix}`)?.value || '';
        const statusFilter = document.getElementById(`user-status-filter-${tableIdSuffix}`)?.value || '';
        const groupFilter = document.getElementById(`user-group-filter-${tableIdSuffix}`)?.value || '';

        if (searchTerm) {
            filteredUsers = filteredUsers.filter(u =>
                u.login_utilisateur.toLowerCase().includes(searchTerm) ||
                u.email_principal.toLowerCase().includes(searchTerm) ||
                u.nom.toLowerCase().includes(searchTerm) ||
                u.prenom.toLowerCase().includes(searchTerm) ||
                u.numero_utilisateur.toLowerCase().includes(searchTerm)
            );
        }
        if (typeFilter && userTypeKey === 'all') { // Type filter only applies to 'all' tab for now, others are pre-filtered
            filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === typeFilter);
        }
        if (statusFilter) {
            filteredUsers = filteredUsers.filter(u => u.statut_compte === statusFilter);
        }
        if (groupFilter) {
            filteredUsers = filteredUsers.filter(u => u.id_groupe_utilisateur === groupFilter);
        }

        // Sorting (example for 'all' tab, extend for others)
        if (currentSort[tableIdSuffix]) {
            const { column, order } = currentSort[tableIdSuffix];
            filteredUsers.sort((a, b) => {
                let valA = a[column] || '';
                let valB = b[column] || '';
                if (column === 'type') { // Special sort for type libelle
                    valA = mockUserTypes.find(t => t.id_type_utilisateur === a.id_type_utilisateur)?.libelle_type_utilisateur || '';
                    valB = mockUserTypes.find(t => t.id_type_utilisateur === b.id_type_utilisateur)?.libelle_type_utilisateur || '';
                }
                if (column === 'name') {
                    valA = `${a.nom} ${a.prenom}`;
                    valB = `${b.nom} ${b.prenom}`;
                }

                if (typeof valA === 'string') valA = valA.toLowerCase();
                if (typeof valB === 'string') valB = valB.toLowerCase();

                if (valA < valB) return order === 'asc' ? -1 : 1;
                if (valA > valB) return order === 'asc' ? 1 : -1;
                return 0;
            });
        }


        const itemsPerPage = parseInt(document.getElementById(`users-per-page-${tableIdSuffix}`)?.value || usersPerPage[userTypeKey]);
        const totalItems = filteredUsers.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        currentUsersTablePage[userTypeKey] = Math.min(page, totalPages) || 1;

        const startIndex = (currentUsersTablePage[userTypeKey] - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedUsers = filteredUsers.slice(startIndex, endIndex);

        // Headers
        const tableHead = tableBody.parentElement.querySelector('thead tr');
        if (tableHead.innerHTML === '') { // Populate headers once
            tableHead.innerHTML = `
                <th><input type="checkbox" id="select-all-users-${tableIdSuffix}" onchange="toggleSelectAllUsers('${tableIdSuffix}')"></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'login_utilisateur')">Login <span class="material-icons">sort</span></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'name')">Nom Complet <span class="material-icons">sort</span></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'email_principal')">Email <span class="material-icons">sort</span></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'type')">Type <span class="material-icons">sort</span></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'statut_compte')">Statut <span class="material-icons">sort</span></th>
                <th onclick="sortUsersTable('${tableIdSuffix}', 'derniere_connexion')">Dern. Conn. <span class="material-icons">sort</span></th>
                <th>Actions</th>
            `;
        }

        // Populate filter dropdowns if they are empty (for dynamically created tabs)
        populateUserFilterDropdowns(tableIdSuffix);


        tableBody.innerHTML = paginatedUsers.map(user => {
            const userType = mockUserTypes.find(t => t.id_type_utilisateur === user.id_type_utilisateur);
            return `
                <tr>
                    <td><input type="checkbox" class="user-checkbox-${tableIdSuffix}" data-user-id="${user.numero_utilisateur}" onchange="updateBulkActionsPanel('${tableIdSuffix}')"></td>
                    <td>${user.login_utilisateur}</td>
                    <td>${user.prenom} ${user.nom}</td>
                    <td>${user.email_principal}</td>
                    <td>${userType ? userType.libelle_type_utilisateur : user.id_type_utilisateur}</td>
                    <td><span class="status-badge ${user.statut_compte}">${user.statut_compte.replace('_', ' ')}</span></td>
                    <td>${user.derniere_connexion ? timeAgo(user.derniere_connexion) : 'Jamais'}</td>
                    <td class="actions-cell">
                        <button onclick="viewUserDetails('${user.numero_utilisateur}')" title="Voir Détails"><span class="material-icons">visibility</span></button>
                        <button onclick="openEditUserModal('${user.numero_utilisateur}')" title="Modifier"><span class="material-icons">edit</span></button>
                        <button class="delete-btn" onclick="confirmDeleteUser('${user.numero_utilisateur}')" title="Archiver/Supprimer"><span class="material-icons">archive</span></button>
                    </td>
                </tr>
            `;
        }).join('');

        renderPagination(paginationControls, totalPages, currentUsersTablePage[userTypeKey], (p) => renderUsersTable(userTypeKey, p));

        const countInfo = document.querySelector(`#users-${tableIdSuffix}-content .table-info`) || document.createElement('span'); // Fallback
        if (countInfo) {
            countInfo.textContent = `Affichage de ${Math.min(startIndex + 1, totalItems)}-${Math.min(endIndex, totalItems)} sur ${totalItems} utilisateurs`;
        }
        if (tableTitle) { // Update title with count
            let titleBase = "Liste des Utilisateurs";
            if (userTypeKey === 'students') titleBase = "Liste des Étudiants";
            else if (userTypeKey === 'teachers') titleBase = "Liste des Enseignants";
            else if (userTypeKey === 'staff') titleBase = "Liste du Personnel Administratif";
            tableTitle.textContent = `${titleBase} (${totalItems})`;
        }
    }

    window.applyUsersTableFilters = function(userTypeKey) { // Make it global for onkeyup/onchange
        renderUsersTable(userTypeKey, 1);
    }
    window.sortUsersTable = function(userTypeKey, column) {
        if (!currentSort[userTypeKey]) currentSort[userTypeKey] = { column: '', order: 'asc'};

        if (currentSort[userTypeKey].column === column) {
            currentSort[userTypeKey].order = currentSort[userTypeKey].order === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort[userTypeKey].column = column;
            currentSort[userTypeKey].order = 'asc';
        }
        // Update sort icons
        const headers = document.querySelectorAll(`#users-table-${userTypeKey} th`);
        headers.forEach(th => {
            const icon = th.querySelector('.material-icons');
            if (icon) icon.textContent = 'sort';
            if (th.textContent.toLowerCase().includes(column.replace('_', ' ').split(' ')[0])) { // Basic match
                if (icon) icon.textContent = currentSort[userTypeKey].order === 'asc' ? 'arrow_upward' : 'arrow_downward';
            }
        });
        renderUsersTable(userTypeKey, currentUsersTablePage[userTypeKey]);
    }


    function populateUserFilterDropdowns(userTypeKey) {
        const typeFilterSelect = document.getElementById(`user-type-filter-${userTypeKey}`);
        const statusFilterSelect = document.getElementById(`user-status-filter-${userTypeKey}`);
        const groupFilterSelect = document.getElementById(`user-group-filter-${userTypeKey}`);

        if (typeFilterSelect && typeFilterSelect.options.length <= 1) {
            mockUserTypes.forEach(type => {
                typeFilterSelect.add(new Option(type.libelle_type_utilisateur, type.id_type_utilisateur));
            });
        }
        if (statusFilterSelect && statusFilterSelect.options.length <= 1) {
            ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'].forEach(status => {
                statusFilterSelect.add(new Option(status.replace('_', ' '), status));
            });
        }
        if (groupFilterSelect && groupFilterSelect.options.length <= 1) {
            mockUserGroups.forEach(group => {
                groupFilterSelect.add(new Option(group.libelle_groupe_utilisateur, group.id_groupe_utilisateur));
            });
        }
    }

    window.resetUsersFilters = function(userTypeKey) {
        document.getElementById(`user-search-${userTypeKey}`).value = '';
        document.getElementById(`user-type-filter-${userTypeKey}`).value = '';
        document.getElementById(`user-status-filter-${userTypeKey}`).value = '';
        document.getElementById(`user-group-filter-${userTypeKey}`).value = '';
        renderUsersTable(userTypeKey, 1);
    }

    window.toggleSelectAllUsers = function(userTypeKey) {
        const selectAllCheckbox = document.getElementById(`select-all-users-${userTypeKey}`);
        const userCheckboxes = document.querySelectorAll(`.user-checkbox-${userTypeKey}`);
        userCheckboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        updateBulkActionsPanel(userTypeKey);
    }

    window.updateBulkActionsPanel = function(userTypeKey) {
        const panel = document.getElementById('users-bulk-actions-panel'); // Assuming one panel for all user tabs for now
        const countSpan = document.getElementById('users-bulk-selection-count');
        const selectedCheckboxes = document.querySelectorAll(`.user-checkbox-${userTypeKey}:checked`);

        if (selectedCheckboxes.length > 0) {
            panel.style.display = 'block'; // Or add 'active' class
            countSpan.textContent = `${selectedCheckboxes.length} utilisateur(s) sélectionné(s)`;
        } else {
            panel.style.display = 'none'; // Or remove 'active' class
        }
    }
    window.clearUsersBulkSelection = function() {
        // This needs to know which tab is active to clear correctly, or clear all
        const activeUserTabKey = document.querySelector('#users-tabs-container .users-tab.active')?.dataset.tab || 'all';
        document.querySelectorAll(`.user-checkbox-${activeUserTabKey}:checked`).forEach(cb => cb.checked = false);
        const selectAll = document.getElementById(`select-all-users-${activeUserTabKey}`);
        if(selectAll) selectAll.checked = false;
        updateBulkActionsPanel(activeUserTabKey);
        document.getElementById('users-bulk-actions-panel').style.display = 'none';
    }
    // Placeholder for bulk actions
    window.bulkChangeUsersStatus = function() { showGenericModal("Changer Statut en Masse", "Fonctionnalité non implémentée."); }
    window.bulkSendUsersNotification = function() { showGenericModal("Envoyer Notification en Masse", "Fonctionnalité non implémentée."); }
    window.bulkArchiveUsers = function() { showGenericModal("Archiver en Masse", "Fonctionnalité non implémentée."); }


    // User Form Modal Logic (B.1.3, B.2.3, B.3.3)
    window.openCreateUserModal = function() {
        userForm.reset();
        userModalTitle.textContent = 'Créer un Nouvel Utilisateur';
        userFormModeInput.value = 'create';
        userFormEditIdInput.value = '';
        selectedUserTypeForCreation = ''; // Reset selected type
        currentUserFormStep = 1;
        updateUserFormStepUI();
        // Reset/hide type-specific fields
        document.querySelectorAll('#user-form-profile-fields .form-group').forEach(fg => fg.style.display = 'none');
        document.getElementById('user-form-type-specific-fields-container').innerHTML = '';
        document.getElementById('user-form-password-group').style.display = 'block';
        userFormModal.classList.add('active');
    }
    window.openCreateUserModalWithType = function(typeId) {
        openCreateUserModal();
        userFormTypeSelect.value = typeId;
        selectedUserTypeForCreation = typeId; // Set it
        updateUserFormForType(); // Trigger field updates
        // Optionally auto-advance to step 2 if type is pre-selected
        // currentUserFormStep = 2;
        // updateUserFormStepUI();
    }

    window.openEditUserModal = function(userId) {
        const user = mockUsers.find(u => u.numero_utilisateur === userId);
        if (!user) {
            showGenericModal("Erreur", "Utilisateur non trouvé.");
            return;
        }
        userForm.reset();
        userModalTitle.textContent = `Modifier Utilisateur: ${user.prenom} ${user.nom}`;
        userFormModeInput.value = 'edit';
        userFormEditIdInput.value = userId;

        // Populate form
        userFormTypeSelect.value = user.id_type_utilisateur;
        selectedUserTypeForCreation = user.id_type_utilisateur; // Use same var for consistency
        updateUserFormForType(); // This will show/hide correct fields

        // Populate common fields
        document.getElementById('user-form-nom').value = user.nom || '';
        document.getElementById('user-form-prenom').value = user.prenom || '';
        document.getElementById('user-form-login').value = user.login_utilisateur || '';
        document.getElementById('user-form-email_principal').value = user.email_principal || '';
        document.getElementById('user-form-statut_compte').value = user.statut_compte || 'actif';
        document.getElementById('user-form-id_groupe_utilisateur').value = user.id_groupe_utilisateur || '';
        document.getElementById('user-form-id_niveau_acces_donne').value = user.id_niveau_acces_donne || '';
        document.getElementById('user-form-photo_profil').value = user.photo_profil || '';

        // Populate type-specific profile fields
        if (user.numero_carte_etudiant && document.getElementById('user-form-numero_carte_etudiant')) {
            document.getElementById('user-form-numero_carte_etudiant').value = user.numero_carte_etudiant;
        }
        if (user.numero_enseignant && document.getElementById('user-form-numero_enseignant')) {
            document.getElementById('user-form-numero_enseignant').value = user.numero_enseignant;
        }
        if (user.numero_personnel_administratif && document.getElementById('user-form-numero_personnel_administratif')) {
            document.getElementById('user-form-numero_personnel_administratif').value = user.numero_personnel_administratif;
        }
        if (user.date_naissance && document.getElementById('user-form-date_naissance')) {
            document.getElementById('user-form-date_naissance').value = user.date_naissance;
        }
        if (user.telephone_personnel && document.getElementById('user-form-telephone_personnel')) {
            document.getElementById('user-form-telephone_personnel').value = user.telephone_personnel;
        }
        if (user.email_professionnel && document.getElementById('user-form-email_professionnel')) {
            document.getElementById('user-form-email_professionnel').value = user.email_professionnel;
        }


        document.getElementById('user-form-password-group').style.display = 'none'; // Hide password by default in edit

        currentUserFormStep = 2; // Start at profile info for edit
        userFormTypeSelect.disabled = true; // Cannot change type in edit mode
        updateUserFormStepUI();
        userFormModal.classList.add('active');
    }

    window.closeUserFormModal = function() {
        userFormModal.classList.remove('active');
        userFormTypeSelect.disabled = false;
    }

    function populateUserFormDropdowns() {
        // User Type (Step 1)
        userFormTypeSelect.innerHTML = '<option value="">-- Choisir un type --</option>';
        mockUserTypes.forEach(type => {
            userFormTypeSelect.add(new Option(type.libelle_type_utilisateur, type.id_type_utilisateur));
        });

        // Status, Group, Access Level (Step 3)
        const statusSelect = document.getElementById('user-form-statut_compte');
        statusSelect.innerHTML = ''; // Clear existing
        ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'].forEach(s => {
            statusSelect.add(new Option(s.replace('_', ' '), s));
        });

        const groupSelect = document.getElementById('user-form-id_groupe_utilisateur');
        groupSelect.innerHTML = '<option value="">-- Choisir un groupe --</option>';
        mockUserGroups.forEach(g => groupSelect.add(new Option(g.libelle_groupe_utilisateur, g.id_groupe_utilisateur)));

        const accessLevelSelect = document.getElementById('user-form-id_niveau_acces_donne');
        accessLevelSelect.innerHTML = '<option value="">-- Choisir un niveau --</option>';
        mockAccessLevels.forEach(lvl => accessLevelSelect.add(new Option(lvl.libelle_niveau_acces_donne, lvl.id_niveau_acces_donne)));
    }

    window.updateUserFormForType = function() {
        selectedUserTypeForCreation = userFormTypeSelect.value;
        const profileFieldsContainer = document.getElementById('user-form-profile-fields');
        // Hide all specific fields first
        profileFieldsContainer.querySelectorAll('.form-group').forEach(fg => fg.style.display = 'none');

        // Show common profile fields (Nom, Prénom are outside this specific container)
        // These are always visible in step 2.
        // document.getElementById('user-form-date_naissance').closest('.form-group').style.display = 'block';
        // document.getElementById('user-form-telephone_personnel').closest('.form-group').style.display = 'block';


        if (selectedUserTypeForCreation === 'TYPE_ETUD') {
            const studentFieldGroup = document.querySelector('.student-field')?.closest('.form-group');
            if (studentFieldGroup) studentFieldGroup.style.display = 'block';
            document.getElementById('user-form-date_naissance').closest('.form-group').style.display = 'block';
            document.getElementById('user-form-telephone_personnel').closest('.form-group').style.display = 'block';
        } else if (selectedUserTypeForCreation === 'TYPE_ENS') {
            const teacherFieldGroup = document.querySelector('.teacher-field')?.closest('.form-group');
            if (teacherFieldGroup) teacherFieldGroup.style.display = 'block';
            document.getElementById('user-form-date_naissance').closest('.form-group').style.display = 'block';
            document.getElementById('user-form-telephone_personnel').closest('.form-group').style.display = 'block';
            document.getElementById('user-form-email_professionnel').closest('.form-group').style.display = 'block';
        } else if (selectedUserTypeForCreation === 'TYPE_PERS_ADMIN' || selectedUserTypeForCreation === 'TYPE_ADMIN_SYS') {
            const staffFieldGroup = document.querySelector('.staff-field')?.closest('.form-group');
            if (staffFieldGroup) staffFieldGroup.style.display = 'block';
            document.getElementById('user-form-date_naissance').closest('.form-group').style.display = 'block';
            document.getElementById('user-form-telephone_personnel').closest('.form-group').style.display = 'block';
            document.getElementById('user-form-email_professionnel').closest('.form-group').style.display = 'block';
        }
    }

    window.changeUserFormStep = function(direction) {
        // Validate current step before proceeding (basic example)
        if (direction > 0) { // Moving to next step
            if (currentUserFormStep === 1 && !selectedUserTypeForCreation) {
                showGenericModal("Validation", "Veuillez sélectionner un type d'utilisateur.");
                return;
            }
            if (currentUserFormStep === 2) { // Validate profile info
                if (!document.getElementById('user-form-nom').value || !document.getElementById('user-form-prenom').value) {
                    showGenericModal("Validation", "Le nom et le prénom sont obligatoires."); return;
                }
                if (selectedUserTypeForCreation === 'TYPE_ETUD' && !document.getElementById('user-form-numero_carte_etudiant').value) {
                    showGenericModal("Validation", "Le numéro de carte étudiant est obligatoire."); return;
                }
                // Add more specific validations here
            }
            if (currentUserFormStep === 3) { // Validate account info
                if (!document.getElementById('user-form-login').value && userFormModeInput.value === 'edit') { // Login mandatory in edit
                    showGenericModal("Validation", "Le login est obligatoire."); return;
                }
                if (!document.getElementById('user-form-email_principal').value) {
                    showGenericModal("Validation", "L'email principal est obligatoire."); return;
                }
                // More validations
            }
        }

        currentUserFormStep += direction;
        updateUserFormStepUI();
    }

    function updateUserFormStepUI() {
        userFormStepIndicator.querySelectorAll('.step').forEach((stepEl, index) => {
            stepEl.classList.toggle('active', index + 1 === currentUserFormStep);
        });
        userFormStepContents.forEach((contentEl, index) => {
            contentEl.classList.toggle('active', index + 1 === currentUserFormStep);
        });

        userFormPrevBtn.style.display = currentUserFormStep > 1 ? 'inline-flex' : 'none';
        userFormNextBtn.style.display = currentUserFormStep < 4 ? 'inline-flex' : 'none';
        userFormSubmitBtn.style.display = currentUserFormStep === 4 ? 'inline-flex' : 'none';

        if (currentUserFormStep === 4) { // Confirmation step
            populateUserFormSummary();
        }

        // Disable type select after step 1 if creating
        if (userFormModeInput.value === 'create') {
            userFormTypeSelect.disabled = currentUserFormStep > 1;
        }
    }

    function populateUserFormSummary() {
        const summaryDiv = document.getElementById('user-form-summary');
        const type = mockUserTypes.find(t => t.id_type_utilisateur === selectedUserTypeForCreation)?.libelle_type_utilisateur;
        const nom = document.getElementById('user-form-nom').value;
        const prenom = document.getElementById('user-form-prenom').value;
        const login = document.getElementById('user-form-login').value || `${prenom}.${nom}`.toLowerCase(); // Auto-generate if empty
        const email = document.getElementById('user-form-email_principal').value;
        const group = mockUserGroups.find(g => g.id_groupe_utilisateur === document.getElementById('user-form-id_groupe_utilisateur').value)?.libelle_groupe_utilisateur;

        summaryDiv.innerHTML = `
            <p><strong>Type:</strong> ${type || 'N/A'}</p>
            <p><strong>Nom Complet:</strong> ${prenom} ${nom}</p>
            <p><strong>Login:</strong> ${login}</p>
            <p><strong>Email Principal:</strong> ${email}</p>
            <p><strong>Groupe:</strong> ${group || 'N/A'}</p>
            <!-- Add more fields from profile based on type -->
        `;
        if (selectedUserTypeForCreation === 'TYPE_ETUD') {
            summaryDiv.innerHTML += `<p><strong>N° Carte Étudiant:</strong> ${document.getElementById('user-form-numero_carte_etudiant').value}</p>`;
        }
        // ... other types
    }

    function handleUserFormSubmit(event) {
        event.preventDefault();
        const mode = userFormModeInput.value;
        const userIdToEdit = userFormEditIdInput.value;

        const userData = {
            id_type_utilisateur: selectedUserTypeForCreation,
            nom: document.getElementById('user-form-nom').value,
            prenom: document.getElementById('user-form-prenom').value,
            login_utilisateur: document.getElementById('user-form-login').value || `${document.getElementById('user-form-prenom').value.toLowerCase()}.${document.getElementById('user-form-nom').value.toLowerCase()}`,
            email_principal: document.getElementById('user-form-email_principal').value,
            statut_compte: document.getElementById('user-form-statut_compte').value,
            id_groupe_utilisateur: document.getElementById('user-form-id_groupe_utilisateur').value,
            id_niveau_acces_donne: document.getElementById('user-form-id_niveau_acces_donne').value,
            photo_profil: document.getElementById('user-form-photo_profil').value,
            date_naissance: document.getElementById('user-form-date_naissance')?.value,
            telephone_personnel: document.getElementById('user-form-telephone_personnel')?.value,
            email_professionnel: document.getElementById('user-form-email_professionnel')?.value,
        };

        // Add type specific ID fields
        if (selectedUserTypeForCreation === 'TYPE_ETUD') userData.numero_carte_etudiant = document.getElementById('user-form-numero_carte_etudiant').value;
        if (selectedUserTypeForCreation === 'TYPE_ENS') userData.numero_enseignant = document.getElementById('user-form-numero_enseignant').value;
        if (selectedUserTypeForCreation === 'TYPE_PERS_ADMIN' || selectedUserTypeForCreation === 'TYPE_ADMIN_SYS') {
            userData.numero_personnel_administratif = document.getElementById('user-form-numero_personnel_administratif')?.value;
        }


        if (mode === 'create') {
            userData.numero_utilisateur = `USR_${Date.now()}_${mockUsers.length}`;
            userData.date_creation = new Date().toISOString();
            const password = document.getElementById('user-form-password').value;
            if (password) userData.password_hash = `hashed(${password})`; // Simulate hashing
            else userData.password_hash = `hashed(GeneratedPass${Math.random().toString(36).slice(-8)})`;

            mockUsers.push(userData);
            addAuditLog('Création Utilisateur', `Utilisateur ${userData.login_utilisateur} créé.`);
            showGenericModal("Succès", `Utilisateur ${userData.prenom} ${userData.nom} créé avec succès.`);
        } else { // edit mode
            const userIndex = mockUsers.findIndex(u => u.numero_utilisateur === userIdToEdit);
            if (userIndex !== -1) {
                mockUsers[userIndex] = { ...mockUsers[userIndex], ...userData };
                addAuditLog('Modification Utilisateur', `Utilisateur ${userData.login_utilisateur} modifié.`);
                showGenericModal("Succès", `Utilisateur ${userData.prenom} ${userData.nom} mis à jour.`);
            } else {
                showGenericModal("Erreur", "Utilisateur non trouvé pour la mise à jour.");
                return;
            }
        }
        closeUserFormModal();
        renderUsersTable(document.querySelector('#users-tabs-container .users-tab.active')?.dataset.tab || 'all'); // Refresh current table
        renderDashboard(); // Update dashboard stats if needed
    }

    window.confirmDeleteUser = function(userId) {
        const user = mockUsers.find(u => u.numero_utilisateur === userId);
        if (!user) return;
        showAlertConfirmation(
            `Archiver Utilisateur`,
            `Êtes-vous sûr de vouloir archiver l'utilisateur ${user.prenom} ${user.nom} (${user.login_utilisateur})? Son statut sera changé en 'archive'.`,
            () => {
                user.statut_compte = 'archive';
                addAuditLog('Archivage Utilisateur', `Utilisateur ${user.login_utilisateur} archivé.`);
                showGenericModal("Confirmé", `Utilisateur ${user.prenom} ${user.nom} archivé.`);
                renderUsersTable(document.querySelector('#users-tabs-container .users-tab.active')?.dataset.tab || 'all');
                renderDashboard();
            }
        );
    }

    window.viewUserDetails = function(userId) { // Placeholder
        const user = mockUsers.find(u => u.numero_utilisateur === userId);
        if(!user) return;
        let details = `Détails pour ${user.prenom} ${user.nom}:\n`;
        for (const key in user) {
            details += `${key}: ${user[key]}\n`;
        }
        showGenericModal(`Détails Utilisateur: ${user.login_utilisateur}`, `<pre>${details.replace(/\n/g, '<br>')}</pre>`);
    }
    window.exportUsersData = function() {
        showGenericModal("Exportation", "Simulation d'exportation des données utilisateurs...");
    }


    // Permissions Section (C)
    function renderPermissionsSection(tabId = 'types') {
        const actionsContainer = document.getElementById('permissions-actions-container');
        actionsContainer.innerHTML = ''; // Clear previous actions

        let contentHtml = '';
        let entityType = '';
        let data = [];
        let columns = [];
        let addBtnLabel = '';

        switch (tabId) {
            case 'types': // C.1
                entityType = 'user_type'; addBtnLabel = 'Ajouter Type Utilisateur';
                data = mockUserTypes;
                columns = [
                    { key: 'id_type_utilisateur', label: 'ID Type' },
                    { key: 'libelle_type_utilisateur', label: 'Libellé' },
                    { key: 'count', label: 'Nb. Utilisateurs', render: (item) => mockUsers.filter(u => u.id_type_utilisateur === item.id_type_utilisateur).length }
                ];
                break;
            case 'groups': // C.2
                entityType = 'user_group'; addBtnLabel = 'Ajouter Groupe';
                data = mockUserGroups;
                columns = [
                    { key: 'id_groupe_utilisateur', label: 'ID Groupe' },
                    { key: 'libelle_groupe_utilisateur', label: 'Libellé' },
                    { key: 'count', label: 'Nb. Utilisateurs', render: (item) => mockUsers.filter(u => u.id_groupe_utilisateur === item.id_groupe_utilisateur).length }
                ];
                break;
            case 'access_levels': // C.3
                entityType = 'access_level'; addBtnLabel = 'Ajouter Niveau d\'Accès';
                data = mockAccessLevels;
                columns = [
                    { key: 'id_niveau_acces_donne', label: 'ID Niveau' },
                    { key: 'libelle_niveau_acces_donne', label: 'Libellé' },
                    { key: 'count', label: 'Nb. Utilisateurs', render: (item) => mockUsers.filter(u => u.id_niveau_acces_donne === item.id_niveau_acces_donne).length }
                ];
                break;
            case 'treatments': // C.4.1
                entityType = 'treatment'; addBtnLabel = 'Ajouter Fonctionnalité';
                data = mockTreatments;
                columns = [
                    { key: 'id_traitement', label: 'ID Traitement' },
                    { key: 'libelle_traitement', label: 'Libellé' }
                ];
                break;
            case 'assignments': // C.4.2
                renderPermissionAssignments(); return; // Special handling
            default:
                contentHtml = '<p>Sélectionnez un onglet.</p>';
        }

        if (entityType) {
            actionsContainer.innerHTML = `<button class="btn-primary" onclick="openGenericCrudModalForCreate('${entityType}', '${addBtnLabel}')"><span class="material-icons">add</span> ${addBtnLabel}</button>`;
            contentHtml = createGenericTableHTML(data, columns, entityType);
        }
        document.getElementById(`permissions-${tabId}-content`).innerHTML = `<div class="dashboard-card table-card">${contentHtml}</div>`;
    }

    function renderPermissionAssignments() { // C.4.2
        const container = document.getElementById('permissions-assignments-content');
        actionsContainer = document.getElementById('permissions-actions-container');
        actionsContainer.innerHTML = ''; // No global add button for this one

        let html = `
            <div class="dashboard-card">
                <h3>Assigner les Permissions aux Groupes</h3>
                <div class="form-group">
                    <label for="perm-assign-group-select">Sélectionner un Groupe :</label>
                    <select id="perm-assign-group-select" class="form-control" onchange="displayPermissionsForGroup(this.value)">
                        <option value="">-- Choisir un groupe --</option>
                        ${mockUserGroups.map(g => `<option value="${g.id_groupe_utilisateur}">${g.libelle_groupe_utilisateur}</option>`).join('')}
                    </select>
                </div>
                <div id="perm-assign-details" style="display:none; margin-top:1rem;">
                    <h4>Permissions pour <span id="perm-assign-selected-group-name"></span>:</h4>
                    <div class="perm-assignment-columns">
                        <div class="perm-column">
                            <h5>Permissions Assignées</h5>
                            <ul id="perm-assigned-list" class="data-list"></ul>
                        </div>
                        <div class="perm-column">
                            <h5>Permissions Disponibles</h5>
                            <ul id="perm-available-list" class="data-list"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .perm-assignment-columns { display: flex; gap: 1rem; }
                .perm-column { flex: 1; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--border-radius); }
                .perm-column h5 { margin-top: 0; }
                .perm-column ul li { cursor: pointer; }
                .perm-column ul li:hover { background-color: var(--primary-accent-light); }
            </style>
        `;
        container.innerHTML = html;
    }

    window.displayPermissionsForGroup = function(groupId) {
        const detailsDiv = document.getElementById('perm-assign-details');
        if (!groupId) {
            detailsDiv.style.display = 'none';
            return;
        }
        const group = mockUserGroups.find(g => g.id_groupe_utilisateur === groupId);
        document.getElementById('perm-assign-selected-group-name').textContent = group.libelle_groupe_utilisateur;

        const assignedList = document.getElementById('perm-assigned-list');
        const availableList = document.getElementById('perm-available-list');

        const groupPermissions = mockPermissionsAssignments
            .filter(pa => pa.id_groupe_utilisateur === groupId)
            .map(pa => pa.id_traitement);

        assignedList.innerHTML = mockTreatments
            .filter(t => groupPermissions.includes(t.id_traitement))
            .map(t => `<li onclick="togglePermissionAssignment('${groupId}', '${t.id_traitement}', false)" title="Cliquer pour retirer">${t.libelle_traitement} (ID: ${t.id_traitement})</li>`)
            .join('');

        availableList.innerHTML = mockTreatments
            .filter(t => !groupPermissions.includes(t.id_traitement))
            .map(t => `<li onclick="togglePermissionAssignment('${groupId}', '${t.id_traitement}', true)" title="Cliquer pour assigner">${t.libelle_traitement} (ID: ${t.id_traitement})</li>`)
            .join('');

        detailsDiv.style.display = 'block';
    }

    window.togglePermissionAssignment = function(groupId, treatmentId, assign) {
        if (assign) {
            if (!mockPermissionsAssignments.some(pa => pa.id_groupe_utilisateur === groupId && pa.id_traitement === treatmentId)) {
                mockPermissionsAssignments.push({ id_groupe_utilisateur: groupId, id_traitement: treatmentId });
                addAuditLog('Assignation Permission', `Permission ${treatmentId} assignée au groupe ${groupId}.`);
            }
        } else {
            mockPermissionsAssignments = mockPermissionsAssignments.filter(pa =>
                !(pa.id_groupe_utilisateur === groupId && pa.id_traitement === treatmentId)
            );
            addAuditLog('Retrait Permission', `Permission ${treatmentId} retirée du groupe ${groupId}.`);
        }
        displayPermissionsForGroup(groupId); // Refresh lists
        renderDashboardPermissionsOverview(); // Update dashboard
    }

    // System Configuration Section (D)
    function renderSystemConfigSection(referentialType = 'annee_academique') {
        const containerId = `system_config-referentiels_${referentialType}-content`;
        const container = document.getElementById(containerId);
        if (!container) { console.warn(`Container ${containerId} not found.`); return; }

        const actionsContainer = document.getElementById('system_config-actions-container');
        actionsContainer.innerHTML = ''; // Clear previous actions

        let data, columns, entityType, addBtnLabel, title;

        switch(referentialType) {
            case 'annee_academique': // D.1 (Année Académique)
                title = "Années Académiques"; entityType = 'academic_year'; addBtnLabel = 'Ajouter Année Académique';
                data = mockAcademicYears;
                columns = [
                    { key: 'id_annee_academique', label: 'ID Année' },
                    { key: 'libelle_annee_academique', label: 'Libellé' },
                    { key: 'date_debut', label: 'Date Début' },
                    { key: 'date_fin', label: 'Date Fin' },
                    { key: 'est_active', label: 'Active', render: item => item.est_active ? '<span class="status-badge actif">Oui</span>' : '<span class="status-badge inactif">Non</span>' }
                ];
                break;
            case 'niveaux_etude':
                title = "Niveaux d'Étude"; entityType = 'study_level'; addBtnLabel = 'Ajouter Niveau d\'Étude';
                data = mockStudyLevels;
                columns = [ {key: 'id_niveau_etude', label: 'ID Niveau'}, {key: 'libelle_niveau_etude', label: 'Libellé'} ];
                break;
            case 'specialites':
                title = "Spécialités"; entityType = 'speciality'; addBtnLabel = 'Ajouter Spécialité';
                data = mockSpecialities;
                columns = [ {key: 'id_specialite', label: 'ID Spécialité'}, {key: 'libelle_specialite', label: 'Libellé'} ];
                break;
            case 'statuts':
                title = "Statuts Divers"; entityType = 'generic_status'; addBtnLabel = 'Ajouter Statut';
                data = mockGenericStatuses; // This might need further filtering by 'type' in a real app
                columns = [ {key: 'id_statut', label: 'ID Statut'}, {key: 'libelle_statut', label: 'Libellé'}, {key: 'type', label: 'Contexte'} ];
                break;
            default: container.innerHTML = `<p>Type de référentiel '${referentialType}' non géré.</p>`; return;
        }

        actionsContainer.innerHTML = `<button class="btn-primary" onclick="openGenericCrudModalForCreate('${entityType}', '${addBtnLabel}')"><span class="material-icons">add</span> ${addBtnLabel}</button>`;
        if (referentialType === 'annee_academique') { // Special action for academic year
            actionsContainer.innerHTML += ` <button class="btn-secondary" onclick="promptActivateAcademicYear()"><span class="material-icons">check_circle_outline</span> Activer une Année</button>`;
        }
        container.innerHTML = `<div class="dashboard-card table-card"><h3>${title}</h3>${createGenericTableHTML(data, columns, entityType)}</div>`;
    }

    window.promptActivateAcademicYear = function() {
        // Simple prompt for simulation
        const yearIdToActivate = prompt("Entrez l'ID de l'année académique à activer (ex: AA_2025_2026):");
        if (yearIdToActivate) {
            const yearExists = mockAcademicYears.some(y => y.id_annee_academique === yearIdToActivate);
            if (yearExists) {
                mockAcademicYears.forEach(y => y.est_active = (y.id_annee_academique === yearIdToActivate));
                addAuditLog('Activation Année Acad.', `Année ${yearIdToActivate} activée.`);
                showGenericModal("Succès", `Année académique ${yearIdToActivate} activée.`);
                renderSystemConfigSection('annee_academique'); // Refresh table
            } else {
                showGenericModal("Erreur", `Année académique avec ID '${yearIdToActivate}' non trouvée.`);
            }
        }
    }

    // Academic Year specific section (might be redundant with above but follows nav structure)
    function renderAcademicYearSection() {
        const container = document.getElementById('academic_year-content');
        // This can reuse the logic from renderSystemConfigSection('annee_academique')
        // or have a more dedicated UI if needed. For now, let's keep it simple.
        let content = `
            <div class="dashboard-card">
                <p>La gestion détaillée des années académiques (création, modification) se trouve dans la section "Référentiels".</p>
                <p>Année Académique Actuellement Active: 
                    <strong>${mockAcademicYears.find(y => y.est_active)?.libelle_annee_academique || 'Aucune'}</strong>
                </p>
                <button class="btn-primary" onclick="navigateToSection('system_config', 'referentiels_annee_academique')">
                    Gérer les Années Académiques
                </button>
                 <button class="btn-secondary" onclick="promptActivateAcademicYear()" style="margin-left:10px;">
                    <span class="material-icons">check_circle_outline</span> Activer une Année
                </button>
            </div>`;
        container.innerHTML = content;
    }

    // Templates & Docs Section (D.3)
    function renderTemplatesDocsSection(tabId = 'pdf_templates') {
        const container = document.getElementById(`templates_docs-${tabId}-content`);
        const actionsContainer = document.getElementById('templates_docs-actions-container');
        actionsContainer.innerHTML = '';

        let data, columns, entityType, addBtnLabel, title;
        if (tabId === 'pdf_templates') {
            title = "Modèles PDF"; entityType = 'pdf_template'; addBtnLabel = 'Ajouter Modèle PDF';
            data = mockPdfTemplates;
            columns = [ {key: 'nom_template', label: 'Nom du Modèle'}, {key: 'date_creation', label: 'Date Création'}, {key: 'version', label: 'Version'} ];
        } else { // email_templates
            title = "Modèles Courriel (Table Message)"; entityType = 'email_template'; addBtnLabel = 'Ajouter Modèle Courriel';
            data = mockEmailTemplates;
            columns = [ {key: 'id_message', label: 'ID Message'}, {key: 'sujet_message', label: 'Sujet'}, {key: 'type_message', label: 'Type'} ];
        }
        actionsContainer.innerHTML = `<button class="btn-primary" onclick="openGenericCrudModalForCreate('${entityType}', '${addBtnLabel}')"><span class="material-icons">add</span> ${addBtnLabel}</button>`;
        container.innerHTML = `<div class="dashboard-card table-card"><h3>${title}</h3>${createGenericTableHTML(data, columns, entityType)}</div>`;
    }

    // App Settings Section (D.2)
    function renderAppSettingsSection() {
        const container = document.getElementById('app_settings_management_content');
        let formHtml = '<h3>Paramètres Applicatifs & Workflow</h3>';
        for (const key in mockAppSettings) {
            const value = mockAppSettings[key];
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            let inputType = 'text';
            if (typeof value === 'number') inputType = 'number';
            if (key.includes('email')) inputType = 'email';
            if (key.includes('server') || key.includes('formats')) inputType = 'text'; // Keep as text for list like formats

            formHtml += `
                <div class="form-group">
                    <label for="app-setting-${key}">${label}</label>
                    <input type="${inputType}" id="app-setting-${key}" class="form-control" value="${value}">
                </div>
            `;
        }
        formHtml += `<button type="button" class="btn-primary" onclick="saveAppSettings()">Sauvegarder les Paramètres</button>`;
        container.innerHTML = formHtml;
    }
    window.saveAppSettings = function() {
        for (const key in mockAppSettings) {
            const inputElement = document.getElementById(`app-setting-${key}`);
            if (inputElement) {
                mockAppSettings[key] = typeof mockAppSettings[key] === 'number' ? parseFloat(inputElement.value) : inputElement.value;
            }
        }
        addAuditLog('Configuration Système', 'Paramètres applicatifs mis à jour.');
        showGenericModal("Succès", "Paramètres applicatifs sauvegardés.");
    }

    // Supervision & Maintenance Sections (F)
    function renderWorkflowMonitoringSection() { // F.1
        const container = document.getElementById('workflow_monitoring_dashboard');
        // Simulate workflow stats
        const rapportSoumis = Math.floor(Math.random()*50);
        const rapportEnVerif = Math.floor(Math.random()*20);
        const rapportConforme = Math.floor(Math.random()*100);
        container.innerHTML = `
            <h3>Suivi des Workflows (Exemple Rapports)</h3>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-label">Rapports Soumis</div><p class="stat-value">${rapportSoumis}</p></div>
                <div class="stat-card"><div class="stat-label">En Vérification Conformité</div><p class="stat-value">${rapportEnVerif}</p></div>
                <div class="stat-card"><div class="stat-label">Rapports Conformes</div><p class="stat-value">${rapportConforme}</p></div>
            </div>
            <p><i>Plus de détails et graphiques à venir...</i></p>
        `;
    }
    function renderPVManagementSection() { // F.2
        const container = document.getElementById('pv_management_table_area');
        // Simulate PV data
        mockPVs = [
            { id_pv: 'PV_M2INFO_2024_001', date_session: '2024-06-15', type_session: 'Soutenance M2 Info', statut: 'Validé', nb_rapports: 5 },
            { id_pv: 'PV_L3ECO_2024_010', date_session: '2024-07-02', type_session: 'Délibération L3 Éco', statut: 'En attente validation', nb_rapports: 25 },
        ];
        const columns = [
            { key: 'id_pv', label: 'ID PV'},
            { key: 'date_session', label: 'Date Session'},
            { key: 'type_session', label: 'Type Session'},
            { key: 'statut', label: 'Statut'},
            { key: 'nb_rapports', label: 'Nb. Rapports/Étudiants'}
        ];
        container.innerHTML = `<h3>Gestion des PV (Admin)</h3>${createGenericTableHTML(mockPVs, columns, 'pv', false)}`; // No CRUD for now
    }
    function renderSystemNotificationsMgmtSection() { // F.3 (Table Recevoir)
        const container = document.getElementById('system_notifications_table_area');
        mockSystemNotifications = mockUsers.slice(0,20).map((user,i) => ({
            id_reception: `RECV_${Date.now()}_${i}`,
            numero_utilisateur: user.numero_utilisateur,
            id_message: mockEmailTemplates[i % mockEmailTemplates.length].id_message,
            date_envoi: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString(),
            lue: Math.random() > 0.5,
            archivee: Math.random() > 0.8
        }));
        const columns = [
            { key: 'numero_utilisateur', label: 'Utilisateur Dest.', render: item => getUserDisplay(item.numero_utilisateur)},
            { key: 'id_message', label: 'ID Message Modèle'},
            { key: 'date_envoi', label: 'Date Envoi', render: item => new Date(item.date_envoi).toLocaleDateString()},
            { key: 'lue', label: 'Lue', render: item => item.lue ? 'Oui' : 'Non'},
            { key: 'archivee', label: 'Archivée', render: item => item.archivee ? 'Oui' : 'Non'}
        ];
        container.innerHTML = `<h3>Gestion des Notifications Système (Table Recevoir)</h3>
            <p>Actions de purge/archivage en masse à implémenter.</p>
            ${createGenericTableHTML(mockSystemNotifications, columns, 'system_notification_received', false)}`;
    }
    function renderAuditLogsSection(tabId = 'actions_log') { // F.4
        const container = document.getElementById(`audit_logs-${tabId}-content`);
        let data, columns, title;
        if (tabId === 'actions_log') { // enregistrer
            title = "Journal des Actions Utilisateurs (enregistrer)";
            data = mockAuditLogsEnregistrer;
            columns = [
                { key: 'date_action', label: 'Date', render: item => new Date(item.date_action).toLocaleString()},
                { key: 'numero_utilisateur', label: 'Utilisateur', render: item => getUserDisplay(item.numero_utilisateur)},
                { key: 'id_action', label: 'Action'},
                { key: 'type_entite_concernee', label: 'Entité'},
                { key: 'id_entite_concernee', label: 'ID Entité'},
                { key: 'adresse_ip', label: 'IP'},
            ];
        } else { // pister
            title = "Traçabilité des Accès aux Fonctionnalités (pister)";
            data = mockAuditLogsPister;
            columns = [
                { key: 'date_pister', label: 'Date', render: item => new Date(item.date_pister).toLocaleString()},
                { key: 'numero_utilisateur', label: 'Utilisateur', render: item => getUserDisplay(item.numero_utilisateur)},
                { key: 'id_traitement', label: 'Fonctionnalité', render: item => mockTreatments.find(t=>t.id_traitement === item.id_traitement)?.libelle_traitement || item.id_traitement },
                { key: 'acceder', label: 'Accès Accordé', render: item => item.acceder ? 'Oui' : 'Non'}
            ];
        }
        // Add filter inputs
        let filterHtml = `
            <div class="filters-grid" style="margin-bottom:1rem; padding:1rem; background-color:#f9f9f9; border-radius:var(--border-radius);">
                <div class="filter-group">
                    <label>Filtrer par Utilisateur (ID/Login):</label>
                    <input type="text" class="form-control" id="audit-user-filter-${tabId}" onkeyup="filterAuditLog('${tabId}')">
                </div>
                <div class="filter-group">
                    <label>Filtrer par Date (YYYY-MM-DD):</label>
                    <input type="date" class="form-control" id="audit-date-filter-${tabId}" onchange="filterAuditLog('${tabId}')">
                </div>
            </div>
        `;
        container.innerHTML = `<div class="dashboard-card table-card"><h3>${title}</h3>${filterHtml}<div id="audit-table-container-${tabId}"></div></div>`;
        document.getElementById(`audit-table-container-${tabId}`).innerHTML = createGenericTableHTML(data, columns, `audit_${tabId}`, false);
    }

    window.filterAuditLog = function(tabId) {
        let dataToFilter = (tabId === 'actions_log') ? mockAuditLogsEnregistrer : mockAuditLogsPister;
        const userFilter = document.getElementById(`audit-user-filter-${tabId}`).value.toLowerCase();
        const dateFilter = document.getElementById(`audit-date-filter-${tabId}`).value;

        if (userFilter) {
            dataToFilter = dataToFilter.filter(log => {
                const user = mockUsers.find(u => u.numero_utilisateur === log.numero_utilisateur);
                return log.numero_utilisateur.toLowerCase().includes(userFilter) ||
                    (user && (user.login_utilisateur.toLowerCase().includes(userFilter) ||
                        `${user.prenom} ${user.nom}`.toLowerCase().includes(userFilter)));
            });
        }
        if (dateFilter) {
            dataToFilter = dataToFilter.filter(log => (log.date_action || log.date_pister).startsWith(dateFilter));
        }

        const columns = (tabId === 'actions_log') ?
            [ /* columns for enregistrer */ { key: 'date_action', label: 'Date', render: item => new Date(item.date_action).toLocaleString()}, { key: 'numero_utilisateur', label: 'Utilisateur', render: item => getUserDisplay(item.numero_utilisateur)}, { key: 'id_action', label: 'Action'}, { key: 'type_entite_concernee', label: 'Entité'}, { key: 'id_entite_concernee', label: 'ID Entité'}, { key: 'adresse_ip', label: 'IP'} ] :
            [ /* columns for pister */ { key: 'date_pister', label: 'Date', render: item => new Date(item.date_pister).toLocaleString()}, { key: 'numero_utilisateur', label: 'Utilisateur', render: item => getUserDisplay(item.numero_utilisateur)}, { key: 'id_traitement', label: 'Fonctionnalité', render: item => mockTreatments.find(t=>t.id_traitement === item.id_traitement)?.libelle_traitement || item.id_traitement }, { key: 'acceder', label: 'Accès Accordé', render: item => item.acceder ? 'Oui' : 'Non'} ];

        document.getElementById(`audit-table-container-${tabId}`).innerHTML = createGenericTableHTML(dataToFilter, columns, `audit_${tabId}`, false);
    }

    function renderDataToolsSection() { // F.5
        const container = document.getElementById('data_tools_interface');
        container.innerHTML = `
            <h3>Outils d'Import/Export Données</h3>
            <div class="form-group">
                <label>Importer des Données (ex: Étudiants CSV)</label>
                <input type="file" class="form-control" disabled>
                <button class="btn-secondary" style="margin-top:0.5rem;" disabled>Importer (Simulation)</button>
            </div>
            <div class="form-group">
                <label>Exporter des Données</label>
                <select class="form-control" disabled>
                    <option>Exporter Utilisateurs (CSV)</option>
                    <option>Exporter Rapports (XML)</option>
                    <option>Sauvegarde SQL Complète (Simulation)</option>
                </select>
                <button class="btn-secondary" style="margin-top:0.5rem;" disabled>Exporter (Simulation)</button>
            </div>
            <p><i>Fonctionnalités d'import/export non implémentées dans cette simulation.</i></p>
        `;
    }
    function renderTechnicalMaintenanceSection() { // F.6
        const container = document.getElementById('technical_maintenance_interface');
        container.innerHTML = `
            <h3>Maintenance Technique</h3>
            <button class="btn-secondary" disabled>Lancer Nettoyage BDD (Simulation)</button>
            <button class="btn-secondary" disabled style="margin-left:10px;">Gérer Sauvegardes (Simulation)</button>
            <p style="margin-top:1rem;"><i>Fonctionnalités de maintenance non implémentées.</i></p>
        `;
    }

    // Reporting & Analytics Section (G)
    function renderReportingAnalyticsSection() {
        const container = document.getElementById('reporting_analytics_interface');
        mockReports = [
            {id: 'REP_USER_ACTIVITY', name: 'Rapport d\'Activité Utilisateur', desc: 'Statistiques sur les connexions et actions des utilisateurs.'},
            {id: 'REP_RAPPORT_STATUS', name: 'Statut des Rapports de Soutenance', desc: 'Nombre de rapports par statut et par année.'},
            {id: 'REP_SYSTEM_PERF', name: 'Performance Système', desc: 'Indicateurs de performance du serveur et de la base de données.'}
        ];
        let html = `<h3>Rapports & Analytics</h3>
            <p>Sélectionnez un rapport à générer (simulation) :</p>
            <ul class="data-list">`;
        mockReports.forEach(report => {
            html += `
                <li class="data-list-item">
                    <div class="data-item-main">
                        <span class="data-item-libelle">${report.name}</span>
                        <span class="data-item-details">${report.desc}</span>
                    </div>
                    <div class="data-item-actions">
                        <button class="btn-primary" onclick="generateSimulatedReport('${report.id}')"><span class="material-icons">assessment</span> Générer</button>
                    </div>
                </li>`;
        });
        html += `</ul><p style="margin-top:1rem;"><i>Configuration de tableaux de bord personnalisés à venir...</i></p>`;
        container.innerHTML = html;
    }
    window.generateSimulatedReport = function(reportId) {
        const report = mockReports.find(r => r.id === reportId);
        showGenericModal("Génération de Rapport", `Simulation de la génération du rapport: "${report.name}". Le téléchargement commencerait ici.`);
    }


    // Generic CRUD Modal Logic
    window.openGenericCrudModalForCreate = function(entityType, title) {
        genericCrudForm.reset();
        genericCrudModalTitle.textContent = title || `Créer Nouvelle Entrée`;
        genericCrudModeInput.value = 'create';
        genericCrudEditIdInput.value = '';
        genericCrudEntityTypeInput.value = entityType;
        populateGenericCrudFormFields(entityType);
        genericCrudModal.classList.add('active');
    }
    window.openGenericCrudModalForEdit = function(entityType, itemId, title) {
        genericCrudForm.reset();
        genericCrudModalTitle.textContent = title || `Modifier Entrée`;
        genericCrudModeInput.value = 'edit';
        genericCrudEditIdInput.value = itemId;
        genericCrudEntityTypeInput.value = entityType;

        const item = findMockDataItem(entityType, itemId);
        if (!item) { showGenericModal("Erreur", "Élément non trouvé."); return; }

        populateGenericCrudFormFields(entityType, item);
        genericCrudModal.classList.add('active');
    }
    window.closeGenericCrudModal = function() {
        genericCrudModal.classList.remove('active');
    }

    function populateGenericCrudFormFields(entityType, itemData = null) {
        genericCrudFormFields.innerHTML = '';
        let fields = [];
        switch (entityType) {
            case 'user_type': fields = [{name: 'id_type_utilisateur', label: 'ID Type Utilisateur*', required: true}, {name: 'libelle_type_utilisateur', label: 'Libellé*', required: true}]; break;
            case 'user_group': fields = [{name: 'id_groupe_utilisateur', label: 'ID Groupe*', required: true}, {name: 'libelle_groupe_utilisateur', label: 'Libellé*', required: true}]; break;
            case 'access_level': fields = [{name: 'id_niveau_acces_donne', label: 'ID Niveau Accès*', required: true}, {name: 'libelle_niveau_acces_donne', label: 'Libellé*', required: true}]; break;
            case 'treatment': fields = [{name: 'id_traitement', label: 'ID Traitement*', required: true}, {name: 'libelle_traitement', label: 'Libellé*', required: true}]; break;
            case 'academic_year': fields = [
                {name: 'id_annee_academique', label: 'ID Année Académique*', required: true},
                {name: 'libelle_annee_academique', label: 'Libellé*', required: true},
                {name: 'date_debut', label: 'Date Début*', type: 'date', required: true},
                {name: 'date_fin', label: 'Date Fin*', type: 'date', required: true},
                // est_active is managed separately
            ]; break;
            case 'study_level': fields = [{name: 'id_niveau_etude', label: 'ID Niveau Étude*', required: true}, {name: 'libelle_niveau_etude', label: 'Libellé*', required: true}]; break;
            case 'speciality': fields = [{name: 'id_specialite', label: 'ID Spécialité*', required: true}, {name: 'libelle_specialite', label: 'Libellé*', required: true}]; break;
            case 'generic_status': fields = [
                {name: 'id_statut', label: 'ID Statut*', required: true},
                {name: 'libelle_statut', label: 'Libellé*', required: true},
                {name: 'type', label: 'Contexte/Type (ex: rapport, pv)*', required: true}
            ]; break;
            case 'pdf_template': fields = [
                {name: 'id_template', label: 'ID Template*', required: true, type: 'text', placeholder: 'Ex: PDF_ATTEST_01'},
                {name: 'nom_template', label: 'Nom du Modèle*', required: true},
                {name: 'version', label: 'Version', placeholder: 'Ex: 1.0'},
                {name: 'contenu_html', label: 'Contenu HTML/CSS (Simulation)', type: 'textarea', rows: 5}
            ]; break;
            case 'email_template': fields = [ // Table Message
                {name: 'id_message', label: 'ID Message*', required: true, placeholder: 'Ex: EMAIL_BIENVENUE_ETUD'},
                {name: 'sujet_message', label: 'Sujet du Courriel*', required: true},
                {name: 'type_message', label: 'Type de Message*', required: true, placeholder: 'Ex: EMAIL_CREATION_COMPTE'},
                {name: 'libelle_message', label: 'Corps du Courriel (HTML/Texte avec placeholders)*', type: 'textarea', rows: 8, required: true}
            ]; break;
            // Add more cases for other entities
        }

        fields.forEach(field => {
            const value = itemData && itemData[field.name] ? itemData[field.name] : '';
            const inputHtml = field.type === 'textarea' ?
                `<textarea id="crud-${field.name}" class="form-control" rows="${field.rows || 3}" ${field.required ? 'required' : ''}>${value}</textarea>` :
                `<input type="${field.type || 'text'}" id="crud-${field.name}" class="form-control" value="${value}" ${field.required ? 'required' : ''} ${field.placeholder ? `placeholder="${field.placeholder}"` : ''}>`;

            genericCrudFormFields.innerHTML += `
                <div class="form-group full-width">
                    <label for="crud-${field.name}">${field.label}</label>
                    ${inputHtml}
                </div>
            `;
            // Disable ID field in edit mode for most entities
            if (itemData && field.name.startsWith('id_') && entityType !== 'pdf_template' && entityType !== 'email_template') { // Allow edit for template IDs if needed
                document.getElementById(`crud-${field.name}`).disabled = true;
            }
        });
    }

    function handleGenericCrudFormSubmit(event) {
        event.preventDefault();
        const mode = genericCrudModeInput.value;
        const entityType = genericCrudEntityTypeInput.value;
        const editId = genericCrudEditIdInput.value;
        const formData = new FormData(genericCrudForm);
        const itemData = {};

        // Collect data from dynamic fields
        genericCrudFormFields.querySelectorAll('input, textarea, select').forEach(input => {
            itemData[input.id.replace('crud-', '')] = input.value;
        });

        let dataArray, idField, refreshFunction, activeTab;
        switch (entityType) {
            case 'user_type': dataArray = mockUserTypes; idField = 'id_type_utilisateur'; refreshFunction = () => renderPermissionsSection('types'); activeTab = 'types'; break;
            case 'user_group': dataArray = mockUserGroups; idField = 'id_groupe_utilisateur'; refreshFunction = () => renderPermissionsSection('groups'); activeTab = 'groups'; break;
            case 'access_level': dataArray = mockAccessLevels; idField = 'id_niveau_acces_donne'; refreshFunction = () => renderPermissionsSection('access_levels'); activeTab = 'access_levels'; break;
            case 'treatment': dataArray = mockTreatments; idField = 'id_traitement'; refreshFunction = () => renderPermissionsSection('treatments'); activeTab = 'treatments'; break;
            case 'academic_year': dataArray = mockAcademicYears; idField = 'id_annee_academique'; refreshFunction = () => renderSystemConfigSection('annee_academique'); activeTab = 'annee_academique'; break;
            case 'study_level': dataArray = mockStudyLevels; idField = 'id_niveau_etude'; refreshFunction = () => renderSystemConfigSection('niveaux_etude'); activeTab = 'niveaux_etude'; break;
            case 'speciality': dataArray = mockSpecialities; idField = 'id_specialite'; refreshFunction = () => renderSystemConfigSection('specialites'); activeTab = 'specialites'; break;
            case 'generic_status': dataArray = mockGenericStatuses; idField = 'id_statut'; refreshFunction = () => renderSystemConfigSection('statuts'); activeTab = 'statuts'; break;
            case 'pdf_template': dataArray = mockPdfTemplates; idField = 'id_template'; refreshFunction = () => renderTemplatesDocsSection('pdf_templates'); activeTab = 'pdf_templates'; break;
            case 'email_template': dataArray = mockEmailTemplates; idField = 'id_message'; refreshFunction = () => renderTemplatesDocsSection('email_templates'); activeTab = 'email_templates'; break;
            default: showGenericModal("Erreur", "Type d'entité non reconnu."); return;
        }

        // Uniqueness check for ID field in create mode
        if (mode === 'create') {
            const newId = itemData[idField];
            if (dataArray.some(item => item[idField] === newId)) {
                showGenericModal("Erreur de Validation", `L'ID '${newId}' existe déjà pour ce type d'entité.`);
                return;
            }
            // For academic year, ensure est_active is false by default on creation
            if (entityType === 'academic_year') itemData.est_active = false;
            if (entityType === 'pdf_template') itemData.date_creation = new Date().toISOString().split('T')[0];

            dataArray.push(itemData);
            addAuditLog(`Création ${entityType}`, `Élément ${itemData[idField]} créé.`);
            showGenericModal("Succès", "Élément créé avec succès.");
        } else { // edit
            const itemIndex = dataArray.findIndex(item => item[idField] === editId);
            if (itemIndex !== -1) {
                // Preserve the original ID if it was disabled in the form
                const originalId = dataArray[itemIndex][idField];
                dataArray[itemIndex] = { ...dataArray[itemIndex], ...itemData };
                dataArray[itemIndex][idField] = originalId; // Ensure ID is not changed if it was meant to be static
                addAuditLog(`Modification ${entityType}`, `Élément ${editId} modifié.`);
                showGenericModal("Succès", "Élément mis à jour.");
            } else {
                showGenericModal("Erreur", "Élément non trouvé pour la mise à jour."); return;
            }
        }
        closeGenericCrudModal();
        if (refreshFunction) refreshFunction();
        renderDashboard(); // Update dashboard overviews
    }

    window.confirmDeleteGenericItem = function(entityType, itemId) {
        let dataArray, idField, libelleField, refreshFunction;
        switch (entityType) {
            case 'user_type': dataArray = mockUserTypes; idField = 'id_type_utilisateur'; libelleField = 'libelle_type_utilisateur'; refreshFunction = () => renderPermissionsSection('types'); break;
            case 'user_group': dataArray = mockUserGroups; idField = 'id_groupe_utilisateur'; libelleField = 'libelle_groupe_utilisateur'; refreshFunction = () => renderPermissionsSection('groups'); break;
            // Add other cases
            default: showGenericModal("Erreur", "Suppression non gérée pour ce type."); return;
        }
        const item = dataArray.find(i => i[idField] === itemId);
        if (!item) return;

        // Basic dependency check simulation (extend as needed)
        let dependencyMessage = "";
        if (entityType === 'user_type' && mockUsers.some(u => u.id_type_utilisateur === itemId)) {
            dependencyMessage = "Ce type est utilisé par des utilisateurs et ne peut être supprimé.";
        } else if (entityType === 'user_group' && (mockUsers.some(u => u.id_groupe_utilisateur === itemId) || mockPermissionsAssignments.some(pa => pa.id_groupe_utilisateur === itemId))) {
            dependencyMessage = "Ce groupe est utilisé par des utilisateurs ou a des permissions assignées.";
        }

        if (dependencyMessage) {
            showGenericModal("Suppression Impossible", dependencyMessage);
            return;
        }

        showAlertConfirmation(
            `Supprimer ${item[libelleField]}`,
            `Êtes-vous sûr de vouloir supprimer "${item[libelleField]}" (ID: ${itemId})? Cette action est irréversible.`,
            () => {
                const itemIndex = dataArray.findIndex(i => i[idField] === itemId);
                if (itemIndex !== -1) {
                    dataArray.splice(itemIndex, 1);
                    addAuditLog(`Suppression ${entityType}`, `Élément ${itemId} (${item[libelleField]}) supprimé.`);
                    showGenericModal("Succès", `"${item[libelleField]}" supprimé.`);
                    if (refreshFunction) refreshFunction();
                    renderDashboard();
                }
            }
        );
    }

    function findMockDataItem(entityType, itemId) {
        switch (entityType) {
            case 'user_type': return mockUserTypes.find(item => item.id_type_utilisateur === itemId);
            case 'user_group': return mockUserGroups.find(item => item.id_groupe_utilisateur === itemId);
            case 'access_level': return mockAccessLevels.find(item => item.id_niveau_acces_donne === itemId);
            case 'treatment': return mockTreatments.find(item => item.id_traitement === itemId);
            case 'academic_year': return mockAcademicYears.find(item => item.id_annee_academique === itemId);
            case 'study_level': return mockStudyLevels.find(item => item.id_niveau_etude === itemId);
            case 'speciality': return mockSpecialities.find(item => item.id_specialite === itemId);
            case 'generic_status': return mockGenericStatuses.find(item => item.id_statut === itemId);
            case 'pdf_template': return mockPdfTemplates.find(item => item.id_template === itemId);
            case 'email_template': return mockEmailTemplates.find(item => item.id_message === itemId);
            default: return null;
        }
    }

    // Helper: Create Generic Table
    function createGenericTableHTML(data, columns, entityType, includeActions = true) {
        if (!data || data.length === 0) return '<p>Aucune donnée à afficher.</p>';
        let tableHtml = '<div class="table-container"><table class="gestionsoutenance-table">';
        // Headers
        tableHtml += '<thead><tr>';
        columns.forEach(col => tableHtml += `<th>${col.label}</th>`);
        if (includeActions) tableHtml += '<th>Actions</th>';
        tableHtml += '</tr></thead>';
        // Body
        tableHtml += '<tbody>';
        data.forEach(item => {
            tableHtml += '<tr>';
            columns.forEach(col => {
                const value = col.render ? col.render(item) : item[col.key];
                tableHtml += `<td>${value !== undefined && value !== null ? value : '-'}</td>`;
            });
            if (includeActions) {
                const itemId = item[columns[0].key]; // Assume first column key is the ID
                const itemLibelle = item[columns[1].key] || itemId; // Assume second is libelle
                tableHtml += `<td class="actions-cell">
                    <button onclick="openGenericCrudModalForEdit('${entityType}', '${itemId}', 'Modifier: ${escapeHtml(itemLibelle)}')"><span class="material-icons">edit</span></button>
                    <button class="delete-btn" onclick="confirmDeleteGenericItem('${entityType}', '${itemId}')"><span class="material-icons">delete_outline</span></button>
                </td>`;
            }
            tableHtml += '</tr>';
        });
        tableHtml += '</tbody></table></div>';
        return tableHtml;
    }

    // Helper: Pagination Renderer
    function renderPagination(container, totalPages, currentPage, callback) {
        if (!container) return;
        container.innerHTML = '';
        if (totalPages <= 1) return;

        const prevButton = document.createElement('button');
        prevButton.className = 'pagination-btn';
        prevButton.innerHTML = '<span class="material-icons">chevron_left</span>';
        prevButton.disabled = currentPage === 1;
        prevButton.onclick = () => callback(currentPage - 1);
        container.appendChild(prevButton);

        const pageInfo = document.createElement('span');
        pageInfo.className = 'pagination-info';
        pageInfo.textContent = `Page ${currentPage} sur ${totalPages}`;
        container.appendChild(pageInfo);

        const nextButton = document.createElement('button');
        nextButton.className = 'pagination-btn';
        nextButton.innerHTML = '<span class="material-icons">chevron_right</span>';
        nextButton.disabled = currentPage === totalPages;
        nextButton.onclick = () => callback(currentPage + 1);
        container.appendChild(nextButton);
    }

    // Helper: Time Ago
    function timeAgo(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.round((now - date) / 1000);
        const minutes = Math.round(seconds / 60);
        const hours = Math.round(minutes / 60);
        const days = Math.round(hours / 24);

        if (seconds < 60) return `il y a ${seconds} sec`;
        if (minutes < 60) return `il y a ${minutes} min`;
        if (hours < 24) return `il y a ${hours} heures`;
        return `il y a ${days} jours`;
    }

    // Helper: Escape HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Mobile Sidebar Toggle
    window.toggleMobileSidebar = function() {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    }

    // Logout
    window.logout = function() {
        showGenericModal("Déconnexion", "Vous avez été déconnecté (simulation).");
        // In a real app, redirect to login page
    }

    // Notification Panel
    window.showNotificationsPanel = function() {
        const panel = document.getElementById('notification-panel');
        panel.classList.add('active');
        renderNotificationPanelList();
    }
    window.hideNotificationsPanel = function() {
        document.getElementById('notification-panel').classList.remove('active');
    }
    function updateNotificationCount() {
        const unreadCount = mockNotifications.filter(n => !n.read).length;
        const badge = document.getElementById('notification-count');
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'flex' : 'none';
    }
    function renderNotificationPanelList() {
        const listEl = document.getElementById('notification-panel-list');
        if (mockNotifications.length === 0) {
            listEl.innerHTML = '<p class="no-notifications">Aucune notification.</p>';
            return;
        }
        listEl.innerHTML = mockNotifications.map(n => `
            <div class="activity-item ${n.read ? 'read' : 'unread'}" onclick="markNotificationAsRead(${n.id})">
                <div class="activity-icon ${n.type === 'system' ? 'icon-bg-blue' : (n.type === 'report' ? 'icon-bg-green' : 'icon-bg-orange')}">
                    <span class="material-icons">${n.type === 'system' ? 'settings_suggest' : (n.type === 'report' ? 'assessment' : 'warning')}</span>
                </div>
                <div class="activity-details">
                    <p class="activity-text">${n.text}</p>
                    <p class="activity-time">${n.time}</p>
                </div>
            </div>
        `).join('');
    }
    window.markNotificationAsRead = function(notificationId) {
        const notification = mockNotifications.find(n => n.id === notificationId);
        if (notification && !notification.read) {
            notification.read = true;
            updateNotificationCount();
            renderNotificationPanelList(); // Re-render to update style
        }
    }

    // Generic Modal for simple messages
    function showGenericModal(title, message) {
        // This could be a simpler modal or reuse alert-confirmation with only one button
        alertConfirmationTitle.textContent = title;
        alertConfirmationMessage.innerHTML = message; // Use innerHTML to allow basic formatting
        document.getElementById('alert-confirmation-cancel-btn').style.display = 'none';
        alertConfirmationConfirmBtn.textContent = 'OK';
        alertConfirmationCallback = null; // No specific callback for simple info
        alertConfirmationModal.classList.add('active');
    }
    window.closeAlertConfirmationModal = function() {
        alertConfirmationModal.classList.remove('active');
        // Reset buttons for next use
        document.getElementById('alert-confirmation-cancel-btn').style.display = 'inline-flex';
        alertConfirmationConfirmBtn.textContent = 'Confirmer';
    }

    // Alert/Confirmation Modal
    function showAlertConfirmation(title, message, onConfirm) {
        alertConfirmationTitle.textContent = title;
        alertConfirmationMessage.textContent = message;
        alertConfirmationCallback = onConfirm;
        document.getElementById('alert-confirmation-cancel-btn').style.display = 'inline-flex';
        alertConfirmationConfirmBtn.textContent = 'Confirmer';
        alertConfirmationModal.classList.add('active');
    }

    // Audit Log Helper
    function addAuditLog(action, details) {
        mockAuditLogsEnregistrer.unshift({ // Add to beginning
            id_enregistrement: `ENR_${Date.now()}_${mockAuditLogsEnregistrer.length}`,
            numero_utilisateur: currentAdminUser.login, // Or a more specific ID if available
            id_action: action,
            date_action: new Date().toISOString(),
            type_entite_concernee: 'Système/Administration', // Generic for now
            id_entite_concernee: '-',
            details_action: JSON.stringify({ details: details, performed_by: currentAdminUser.login }),
            adresse_ip: '127.0.0.1 (Simulé)',
            user_agent: 'Dashboard Simulator'
        });
        if (mockAuditLogsEnregistrer.length > 200) mockAuditLogsEnregistrer.pop(); // Keep log size manageable

        // Also add to dashboard activities
        mockDashboardActivities.unshift({
            icon: getIconForAction(action),
            bgColorClass: getBgColorForAction(action),
            text: `${action} par <strong>${currentAdminUser.login}</strong>: ${details.substring(0,50)}...`,
            time: timeAgo(new Date().toISOString()),
            type: 'system_config' // Or determine more accurately
        });
        if (mockDashboardActivities.length > 10) mockDashboardActivities.pop();

        if (document.getElementById('dashboard-content').classList.contains('active')) {
            renderDashboardActivities(); // Refresh if dashboard is visible
        }
        if (document.getElementById('audit_logs-actions_log-content')?.classList.contains('active')) {
            filterAuditLog('actions_log'); // Refresh audit log table if visible
        }
    }


    // Call initialization
    initializeApp();
});

