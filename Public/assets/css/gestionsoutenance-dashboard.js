// Public/assets/css/gestionsoutenance-dashboard.js
document.addEventListener('DOMContentLoaded', function () {
    // Initialisation des données mockées (seront progressivement remplacées par des données réelles du backend)
    // Conserver les données mockées pour le développement local ou les tests unitaires frontend, si besoin.
    // Note : Pour les besoins de ce code, la plupart des données mockées existantes du fichier d'origine sont implicitement conservées ou non modifiées.
    // Seules les notifications mockées sont explicitement définies pour le test local de l'en-tête.
    let mockUsers = []; // Cette ligne est un exemple. Gardez vos données mockées réelles ici.
    let mockUserTypes = [];
    let mockUserGroups = [];
    let mockAccessLevels = [];
    let mockTreatments = [];
    let mockPermissionsAssignments = [];
    let mockAcademicYears = [];
    let mockStudyLevels = [];
    let mockSpecialities = [];
    let mockGenericStatuses = [];
    let mockPdfTemplates = [];
    let mockEmailTemplates = [];
    let mockAppSettings = {};
    let mockWorkflowItems = [];
    let mockPVs = [];
    let mockSystemNotifications = [];
    let mockAuditLogsEnregistrer = [];
    let mockAuditLogsPister = [];
    let mockReports = [];
    let mockDashboardActivities = [];
    // mockNotifications sera remplacé par des notifications réellement récupérées
    let mockNotifications = [
        { id: 'NOTIF_001', type_message: 'URGENT', sujet: "Maintenance système prévue ce soir à 23h.", message: "Une maintenance critique impactera le service de 23h à 01h.", date_reception: new Date(Date.now() - 10 * 60 * 1000).toISOString(), lue: false },
        { id: 'NOTIF_002', type_message: 'RAPPORT_STATUT', sujet: "Votre rapport a été validé.", message: "Félicitations, votre rapport de fin d'études a été approuvé par la commission.", date_reception: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(), lue: false },
        { id: 'NOTIF_003', type_message: 'COMPTE_UTILISATEUR', sujet: "Nouvel utilisateur inscrit.", message: "Un nouvel étudiant (Jean Dupont) s'est inscrit sur la plateforme.", date_reception: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(), lue: true },
    ];


    // Données dynamiques de PHP, lues depuis les balises script du HTML
    const phpUserDataElement = document.getElementById('php-user-data');
    const phpUserData = phpUserDataElement ? JSON.parse(phpUserDataElement.textContent) : {};

    const phpNotificationCountElement = document.getElementById('php-notification-count');
    const phpNotificationCount = phpNotificationCountElement ? parseInt(phpNotificationCountElement.textContent) : 0;

    // Objets des données utilisateur pour l'affichage de l'en-tête
    const currentUserForHeader = {
        login: phpUserData.login_utilisateur || 'Utilisateur',
        nom: phpUserData.nom || '',
        prenom: phpUserData.prenom || 'Nom d\'utilisateur',
        role: phpUserData.user_role_label || 'Rôle Inconnu', // Utilisez le libellé du rôle de PHP
        initials: (phpUserData.prenom ? phpUserData.prenom.charAt(0) : '') + (phpUserData.nom ? phpUserData.nom.charAt(0) : ''),
        avatar: phpUserData.photo_profil || null,
        email: phpUserData.email_principal || 'email@example.com'
    };

    // Variables d'état (conservez vos variables d'état existantes si vous les utilisez ailleurs)
    let currentUserFormStep = 1;
    let selectedUserTypeForCreation = '';
    let currentUsersTablePage = { 'all': 1, 'students': 1, 'teachers': 1, 'staff': 1 };
    let usersPerPage = { 'all': 25, 'students': 25, 'teachers': 25, 'staff': 25 };
    let currentSort = { 'all': { column: 'login_utilisateur', order: 'asc' } };


    // Éléments du DOM (mettre en cache les éléments fréquemment utilisés)
    const pageTitle = document.getElementById('pageTitle'); // Modifié de page-title pour correspondre à header.php
    const breadcrumbCurrentPage = document.getElementById('breadcrumb-current-page'); // En supposant que cela existe pour les fils d'Ariane
    const mainContentSections = document.querySelectorAll('.content-section'); // Assurez-vous que c'est le bon sélecteur
    const navItems = document.querySelectorAll('.nav-item'); // Assurez-vous que c'est le bon sélecteur

    const userFormModal = document.getElementById('user-form-modal'); // Exemple de modal, à conserver pour le contexte
    const userModalTitle = document.getElementById('user-modal-title');
    const userForm = document.getElementById('user-form');
    const userFormStepIndicator = document.getElementById('user-form-step-indicator');
    const userFormStepContents = userForm ? userForm.querySelectorAll('.step-content') : [];
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

    // Éléments du header
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationList = document.getElementById('notificationList');
    const headerNotificationCountSpan = document.getElementById('header-notification-count'); // Renommé pour éviter la confusion
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const userProfileSection = document.getElementById('userProfile');
    const userDropdown = document.getElementById('userDropdown');


    // Initialisation
    function initializeApp() {
        console.log("Initialisation du tableau de bord...");
        setupHeaderUserInfo(); // Configurer les informations utilisateur dynamiques
        generateMockData(); // Générer les données mockées (pour la simulation)
        setupEventListeners(); // Configurer les écouteurs d'événements
        renderDashboard(); // Rendre le tableau de bord (partie A)
        populateUserFormDropdowns(); // Remplir les listes déroulantes du formulaire utilisateur
        renderUsersTable('all', 1); // Rendu initial pour "Vue Générale"
        renderPermissionsSection(); // Rendre la section des permissions
        renderSystemConfigSection(); // Rendre la section de configuration système
        renderAcademicYearSection(); // Rendre la section des années académiques
        renderTemplatesDocsSection(); // Rendre la section des modèles et documents
        renderAppSettingsSection(); // Rendre la section des paramètres de l'application
        renderWorkflowMonitoringSection(); // Rendre la section de surveillance des workflows
        renderPVManagementSection(); // Rendre la section de gestion des PV
        renderSystemNotificationsMgmtSection(); // Rendre la section de gestion des notifications système
        renderAuditLogsSection(); // Rendre la section des journaux d'audit
        renderDataToolsSection(); // Rendre la section des outils de données
        renderTechnicalMaintenanceSection(); // Rendre la section de maintenance technique
        renderReportingAnalyticsSection(); // Rendre la section de rapports et d'analyses
        updateNotificationCountDisplay(phpNotificationCount); // Mettre à jour le badge des notifications au chargement
        console.log("Tableau de bord initialisé.");
    }

    /**
     * Configure les informations de l'utilisateur dans l'en-tête du tableau de bord.
     */
    function setupHeaderUserInfo() {
        const userAvatarElement = document.querySelector('#userProfile .user-avatar');
        const userAvatarLargeElement = document.querySelector('#userDropdown .user-avatar-large');

        // Avatar principal de l'en-tête
        if (userAvatarElement) {
            userAvatarElement.innerHTML = currentUserForHeader.avatar
                ? `<img src="${currentUserForHeader.avatar}" alt="Avatar de ${currentUserForHeader.prenom}">`
                : `<div class="avatar-placeholder">${currentUserForHeader.initials}</div>`;
        }

        // Avatar dans la liste déroulante du profil
        if (userAvatarLargeElement) {
            userAvatarLargeElement.innerHTML = currentUserForHeader.avatar
                ? `<img src="${currentUserForHeader.avatar}" alt="Avatar">`
                : `<div class="avatar-placeholder">${currentUserForHeader.initials}</div>`;
        }

        // Informations textuelles de l'utilisateur
        const userNameDisplays = document.querySelectorAll('.user-name');
        userNameDisplays.forEach(el => el.textContent = currentUserForHeader.prenom + ' ' + currentUserForHeader.nom);

        const userRoleDisplays = document.querySelectorAll('.user-role');
        userRoleDisplays.forEach(el => el.textContent = currentUserForHeader.role);

        const userEmailDisplays = document.querySelectorAll('.user-email');
        userEmailDisplays.forEach(el => el.textContent = currentUserForHeader.email);
    }

    /**
     * Génère les données mockées pour la simulation.
     */
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
            { id_niveau_acces_donne: 'ACCES_TOTAL', libelle_niveau_acces_donne: 'Accès Total (Admin)' },
            { id_niveau_acces_donne: 'ACCES_DEPARTEMENT', libelle_niveau_acces_donne: 'Accès Niveau Département' },
            { id_niveau_acces_donne: 'ACCES_PERSONNEL', libelle_niveau_acces_donne: 'Accès aux Données Personnelles Uniquement' },
        ];

        // Treatments (Fonctionnalités) (C.4.1)
        mockTreatments = [
            { id_traitement: 'TRAIT_ADMIN_DASHBOARD_ACCEDER', libelle_traitement: 'Accéder Tableau de Bord Admin' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', libelle_traitement: 'Lister Utilisateurs (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER', libelle_traitement: 'Créer Utilisateur (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER', libelle_traitement: 'Modifier Utilisateur (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_SUPPRIMER', libelle_traitement: 'Supprimer Utilisateur (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_CHANGER_STATUT', libelle_traitement: 'Changer Statut Compte (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_RESET_MDP', libelle_traitement: 'Réinitialiser MDP (Admin)' },
            { id_traitement: 'TRAIT_ADMIN_GERER_UTILISATEURS_IMPORTER', libelle_traitement: 'Importer Utilisateurs (Admin)' },

            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_ACCEDER', libelle_traitement: 'Accéder Habilitations' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_GROUPE_LISTER', libelle_traitement: 'Lister Groupes Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_GROUPE_CREER', libelle_traitement: 'Créer Groupe Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_GROUPE_MODIFIER', libelle_traitement: 'Modifier Groupe Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_GROUPE_SUPPRIMER', libelle_traitement: 'Supprimer Groupe Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_LISTER', libelle_traitement: 'Lister Types Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_CREER', libelle_traitement: 'Créer Type Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_MODIFIER', libelle_traitement: 'Modifier Type Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_SUPPRIMER', libelle_traitement: 'Supprimer Type Utilisateur' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_LISTER', libelle_traitement: 'Lister Niveaux Accès' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_CREER', libelle_traitement: 'Créer Niveau Accès' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_MODIFIER', libelle_traitement: 'Modifier Niveau Accès' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_SUPPRIMER', libelle_traitement: 'Supprimer Niveau Accès' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_LISTER', libelle_traitement: 'Lister Traitements' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_CREER', libelle_traitement: 'Créer Traitement' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_MODIFIER', libelle_traitement: 'Modifier Traitement' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_SUPPRIMER', libelle_traitement: 'Supprimer Traitement' },
            { id_traitement: 'TRAIT_ADMIN_HABILITATIONS_RATTACHEMENT_GERER', libelle_traitement: 'Gérer Rattachements Permissions' },

            { id_traitement: 'TRAIT_ADMIN_REFERENTIELS_ACCEDER', libelle_traitement: 'Accéder Référentiels' },
            { id_traitement: 'TRAIT_ADMIN_REFERENTIELS_LISTER', libelle_traitement: 'Lister Items Référentiels' },
            { id_traitement: 'TRAIT_ADMIN_REFERENTIELS_GERER', libelle_traitement: 'Gérer Items Référentiels (CRUD)' },

            { id_traitement: 'TRAIT_ADMIN_CONFIG_ACCEDER', libelle_traitement: 'Accéder Configuration Système' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_LISTER', libelle_traitement: 'Lister Années Académiques' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_CREER', libelle_traitement: 'Créer Année Académique' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_MODIFIER', libelle_traitement: 'Modifier Année Académique' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_SUPPRIMER', libelle_traitement: 'Supprimer Année Académique' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_ACTIVER', libelle_traitement: 'Activer Année Académique' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_PARAM_MAJ', libelle_traitement: 'Mettre à Jour Paramètres Généraux' },
            { id_traitement: 'TRAIT_ADMIN_CONFIG_MODELES_DOCS_GERER', libelle_traitement: 'Gérer Modèles Documents/Notifications' },

            { id_traitement: 'TRAIT_ADMIN_GESTION_ACAD_ACCEDER', libelle_traitement: 'Accéder Gestion Académique' },
            { id_traitement: 'TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_LISTER', libelle_traitement: 'Lister Inscriptions' },
            { id_traitement: 'TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_CREER', libelle_traitement: 'Créer Inscription' },
            { id_traitement: 'TRAIT_ADMIN_GESTION_ACAD_NOTE_LISTER', libelle_traitement: 'Lister Notes' },
            { id_traitement: 'TRAIT_ADMIN_GESTION_ACAD_NOTE_GERER', libelle_traitement: 'Gérer Notes' },

            { id_traitement: 'TRAIT_ADMIN_SUPERVISION_ACCEDER', libelle_traitement: 'Accéder Supervision' },
            { id_traitement: 'TRAIT_ADMIN_SUPERVISION_JOURNAUX_AUDIT_VOIR', libelle_traitement: 'Voir Journaux d\'Audit' },
            { id_traitement: 'TRAIT_ADMIN_SUPERVISION_WORKFLOWS_SUIVRE', libelle_traitement: 'Suivre Workflows' },
            { id_traitement: 'TRAIT_ADMIN_SUPERVISION_MAINTENANCE_GERER', libelle_traitement: 'Gérer Maintenance Technique' },

            { id_traitement: 'TRAIT_ADMIN_REPORTING_ACCEDER', libelle_traitement: 'Accéder Reporting' },
            { id_traitement: 'TRAIT_ADMIN_REPORTING_GENERER', libelle_traitement: 'Générer Rapports' },

            // Permissions Étudiant
            { id_traitement: 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER', libelle_traitement: 'Accéder Tableau de Bord Étudiant' },
            { id_traitement: 'TRAIT_ETUDIANT_PROFIL_ACCEDER', libelle_traitement: 'Accéder Profil Étudiant' },
            { id_traitement: 'TRAIT_ETUDIANT_PROFIL_MODIFIER', libelle_traitement: 'Modifier Profil Étudiant' },
            { id_traitement: 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', libelle_traitement: 'Soumettre Rapport' },
            { id_traitement: 'TRAIT_ETUDIANT_RAPPORT_SUIVRE', libelle_traitement: 'Suivre Statut Rapport' },
            { id_traitement: 'TRAIT_ETUDIANT_RECLAMATION_CREER', libelle_traitement: 'Soumettre Réclamation' },
            { id_traitement: 'TRAIT_ETUDIANT_RECLAMATION_LISTER', libelle_traitement: 'Lister Mes Réclamations' },
            { id_traitement: 'TRAIT_ETUDIANT_DOCUMENTS_LISTER', libelle_traitement: 'Lister Mes Documents' },
            { id_traitement: 'TRAIT_ETUDIANT_DOCUMENTS_TELECHARGER', libelle_traitement: 'Télécharger Mes Documents' },
            { id_traitement: 'TRAIT_ETUDIANT_RESSOURCES_CONSULTER', libelle_traitement: 'Consulter Ressources' },

            // Permissions Personnel Administratif
            { id_traitement: 'TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', libelle_traitement: 'Accéder Tableau de Bord Personnel' },
            { id_traitement: 'TRAIT_PERS_ADMIN_CONFORMITE_LISTER', libelle_traitement: 'Lister Rapports Conformité' },
            { id_traitement: 'TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER', libelle_traitement: 'Vérifier Conformité Rapport' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', libelle_traitement: 'Accéder Gestion Scolarité' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_LISTER', libelle_traitement: 'Lister Étudiants (Scolarité)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_ACTIVER_COMPTE', libelle_traitement: 'Activer Compte Étudiant (Scolarité)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_INSCRIPTION_LISTER', libelle_traitement: 'Lister Inscriptions (Scolarité)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_INSCRIPTION_GERER', libelle_traitement: 'Gérer Inscriptions (CRUD)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_NOTE_LISTER', libelle_traitement: 'Lister Notes (Scolarité)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_NOTE_GERER', libelle_traitement: 'Gérer Notes (CRUD)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_STAGE_VALIDER', libelle_traitement: 'Valider Stage' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_DOCUMENT_GENERER', libelle_traitement: 'Générer Documents (Scolarité)' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_RECLAMATION_TRAITER', libelle_traitement: 'Traiter Réclamations' },
            { id_traitement: 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER', libelle_traitement: 'Gérer Pénalités' },
            { id_traitement: 'TRAIT_PERS_ADMIN_COMMUNICATION_ACCEDER', libelle_traitement: 'Accéder Messagerie Interne' },
            { id_traitement: 'TRAIT_PERS_ADMIN_COMMUNICATION_ENVOYER_MESSAGE', libelle_traitement: 'Envoyer Message Interne' },

            // Permissions Commission
            { id_traitement: 'TRAIT_COMMISSION_DASHBOARD_ACCEDER', libelle_traitement: 'Accéder Tableau de Bord Commission' },
            { id_traitement: 'TRAIT_COMMISSION_VALIDATION_RAPPORT_LISTER', libelle_traitement: 'Lister Rapports à Valider' },
            { id_traitement: 'TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER', libelle_traitement: 'Voter sur Rapport' },
            { id_traitement: 'TRAIT_COMMISSION_PV_LISTER', libelle_traitement: 'Lister PV Commission' },
            { id_traitement: 'TRAIT_COMMISSION_PV_REDIGER', libelle_traitement: 'Rédiger PV' },
            { id_traitement: 'TRAIT_COMMISSION_PV_MODIFIER', libelle_traitement: 'Modifier PV' },
            { id_traitement: 'TRAIT_COMMISSION_PV_VALIDER', libelle_traitement: 'Valider PV' },
            { id_traitement: 'TRAIT_COMMISSION_CORRECTION_LISTER', libelle_traitement: 'Lister Rapports pour Correction' },
            { id_traitement: 'TRAIT_COMMISSION_CORRECTION_MODIFIER', libelle_traitement: 'Soumettre Corrections (Commission)' },
            { id_traitement: 'TRAIT_COMMISSION_COMMUNICATION_ACCEDER', libelle_traitement: 'Accéder Messagerie Commission' },
            { id_traitement: 'TRAIT_COMMISSION_COMMUNICATION_ENVOYER_MESSAGE', libelle_traitement: 'Envoyer Message Commission' },
            { id_traitement: 'TRAIT_COMMISSION_HISTORIQUE_CONSULTER', libelle_traitement: 'Consulter Historique Commission' },
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
            login_utilisateur: currentUserForHeader.login,
            email_principal: `${currentUserForHeader.login.toLowerCase()}@system.com`,
            nom: currentUserForHeader.nom,
            prenom: currentUserForHeader.prenom,
            id_type_utilisateur: 'TYPE_ADMIN_SYS',
            id_groupe_utilisateur: 'GRP_ADMIN_TECH',
            id_niveau_acces_donne: 'ACCES_TOTAL', // Corrigé pour correspondre à mockAccessLevels
            statut_compte: 'actif',
            date_creation: new Date().toISOString(),
            derniere_connexion: new Date().toISOString(),
            photo_profil: `https://i.pravatar.cc/40?u=${currentUserForHeader.login}`,
            email_valide: true,
            preferences_2fa_active: true,
            tentatives_connexion_echouees: 0,
            numero_personnel_administratif: `ADM001`
        });


        // Academic Years (D.1)
        mockAcademicYears = [
            { id_annee_academique: 'AA_2023_2024', libelle_annee_academique: '2023-2024', date_debut: '2023-09-01', date_fin: '2024-08-31', est_active: false },
            { id_annee_academique: 'AA_2024_2025', libelle_annee_academique: '2024-09-01', date_fin: '2025-08-31', est_active: true },
            { id_annee_academique: 'AA_2025_2026', libelle_annee_academique: '2025-09-01', date_fin: '2026-08-31', est_active: false },
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
            const user = mockUsers.find(u => u.numero_utilisateur === phpUserData.numero_utilisateur) || mockUsers[0]; // Utiliser l'utilisateur connecté ou le premier mocké
            mockAuditLogsEnregistrer.push({
                id_enregistrement: `ENR_${Date.now()}_${i}`,
                numero_utilisateur: user ? user.numero_utilisateur : 'SYSTEM', // Qui
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
                numero_utilisateur: user ? user.numero_utilisateur : 'SYSTEM',
                id_traitement: mockTreatments[Math.floor(Math.random() * mockTreatments.length)].id_traitement,
                date_pister: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString(),
                acceder: Math.random() > 0.1 // true for granted, false for denied
            });
        }

        // Dashboard Activities (A)
        mockDashboardActivities = mockAuditLogsEnregistrer.slice(0, 10).map(log => ({
            icon: getIconForAction(log.id_action),
            bgColorClass: getBgColorForAction(log.id_action),
            text: `${log.id_action} par <strong>${getUserDisplay(log.numero_utilisateur)}</strong> sur ${log.type_entite_concernee} ${escapeHtml(log.id_entite_concernee || '')}`,
            time: timeAgo(log.date_action),
            type: log.type_entite_concernee === 'Utilisateur' ? 'user_management' : (log.id_action.includes('Permission') ? 'security' : 'system_config')
        }));

        // Notifications (Header) - mockNotifications est déjà défini plus haut
    }

    /**
     * Fonctions utilitaires diverses.
     */
    function getUserDisplay(numeroUtilisateur) {
        const user = mockUsers.find(u => u.numero_utilisateur === numeroUtilisateur);
        return user ? `${escapeHtml(user.prenom || '')} ${escapeHtml(user.nom || '')} (${escapeHtml(user.login_utilisateur || '')})` : escapeHtml(numeroUtilisateur);
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

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Met à jour l'affichage du nombre de notifications dans le badge.
     * @param {number} count Le nombre de notifications non lues.
     */
    function updateNotificationCountDisplay(count) {
        if (headerNotificationCountSpan) {
            headerNotificationCountSpan.textContent = count > 99 ? '99+' : count;
            headerNotificationCountSpan.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    /**
     * Récupère les notifications depuis le backend via une API.
     */
    async function fetchNotifications() {
        try {
            // Point de terminaison d'API réel pour les notifications
            const response = await fetch('/api/notifications'); // Cette route devra être définie dans routes/web.php
            if (!response.ok) {
                // Si l'API renvoie une erreur, utilisez les mock data pour la démo
                console.warn('API notifications non disponible, utilisation des données mockées.');
                renderNotifications(mockNotifications);
                const unreadCountMock = mockNotifications.filter(notif => !notif.lue).length;
                updateNotificationCountDisplay(unreadCountMock);
                return;
            }
            const data = await response.json();

            renderNotifications(data); // Rendre les notifications obtenues
            // Mettre à jour le nombre total de notifications non lues après le fetch
            const unreadCount = data.filter(notif => !notif.lue).length;
            updateNotificationCountDisplay(unreadCount);

        } catch (error) {
            console.error('Erreur lors de la récupération des notifications:', error);
            if (notificationList) {
                notificationList.innerHTML = '<div class="no-notifications">Erreur de chargement des notifications.</div>';
            }
            updateNotificationCountDisplay(0); // Réinitialiser le compte en cas d'erreur
        }
    }

    /**
     * Rend les notifications dans la liste déroulante.
     * @param {Array} notifications La liste des objets notification.
     */
    function renderNotifications(notifications) {
        if (notificationList) {
            notificationList.innerHTML = ''; // Effacer les éléments existants
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="no-notifications">Aucune notification pour l\'instant.</div>';
                return;
            }

            notifications.forEach(notif => {
                const notifItem = document.createElement('div');
                notifItem.classList.add('notification-item');
                if (!notif.lue) {
                    notifItem.classList.add('unread');
                }
                notifItem.dataset.id = notif.id; // L'ID unique de la notification dans la table 'recevoir'

                notifItem.innerHTML = `
                    <div class="notification-icon">
                        <i class="${getNotificationIcon(notif.type_message)} ${getNotificationColorClass(notif.type_message)}"></i>
                    </div>
                    <div class="notification-content">
                        <p>${escapeHtml(notif.sujet)}</p>
                        <span class="notification-time">${timeAgo(notif.date_reception)}</span>
                    </div>
                `;
                notifItem.addEventListener('click', () => {
                    markNotificationAsRead(notif.id);
                    // Optionnel: Rediriger l'utilisateur vers la page de détails de la notification
                    // window.location.href = `/dashboard/notifications/${notif.id}`;
                });
                notificationList.appendChild(notifItem);
            });
        }
    }

    /**
     * Marque une notification spécifique comme lue côté backend.
     * @param {string} idNotification L'ID de la notification à marquer comme lue.
     */
    async function markNotificationAsRead(idNotification) {
        try {
            const response = await fetch(`/api/notifications/${idNotification}/mark-as-read`, { method: 'POST' });
            if (response.ok) {
                console.log(`Notification ${idNotification} marquée comme lue.`);
                const item = document.querySelector(`.notification-item[data-id="${idNotification}"]`);
                if (item) {
                    item.classList.remove('unread');
                    // Mettre à jour le compte en décrémentant
                    const currentCount = parseInt(headerNotificationCountSpan.textContent);
                    if (!isNaN(currentCount) && currentCount > 0) {
                        updateNotificationCountDisplay(currentCount - 1);
                    }
                }
            }
        } catch (error) {
            console.error('Erreur lors du marquage de la notification comme lue:', error);
        }
    }

    /**
     * Marque toutes les notifications comme lues côté backend.
     */
    async function markAllNotificationsAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', { method: 'POST' });
            if (response.ok) {
                console.log('Toutes les notifications marquées comme lues.');
                if (notificationList) {
                    notificationList.querySelectorAll('.notification-item').forEach(item => item.classList.remove('unread'));
                }
                updateNotificationCountDisplay(0); // Réinitialiser le compte à zéro
                if (notificationDropdown) {
                    notificationDropdown.classList.remove('show'); // Fermer la liste déroulante après l'action
                }
            }
        } catch (error) {
            console.error('Erreur lors du marquage de toutes les notifications comme lues:', error);
        }
    }

    /**
     * Configure les écouteurs d'événements pour les interactions de l'en-tête et du tableau de bord.
     */
    function setupEventListeners() {
        // Toggle menu latéral (communication avec un éventuel composant sidebar)
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                document.dispatchEvent(new CustomEvent('toggleSidebar')); // Événement personnalisé pour la barre latérale
            });
        }

        // Toggle notifications dropdown
        if (notificationBtn) {
            notificationBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                if (userDropdown) userDropdown.classList.remove('show'); // Fermer le menu utilisateur
                if (notificationDropdown) {
                    notificationDropdown.classList.toggle('show');
                    if (notificationDropdown.classList.contains('show')) {
                        fetchNotifications(); // Charger les notifications au moment de l'ouverture
                    }
                }
            });
        }

        // Toggle user profile dropdown
        if (userProfileSection) {
            userProfileSection.addEventListener('click', function(event) {
                event.stopPropagation();
                if (notificationDropdown) notificationDropdown.classList.remove('show'); // Fermer les notifications
                if (userDropdown) userDropdown.classList.toggle('show');
            });
        }

        // Fermer les dropdowns en cliquant en dehors
        document.addEventListener('click', function(event) {
            if (userDropdown && userDropdown.classList.contains('show') && !userDropdown.contains(event.target) && !userProfileSection.contains(event.target)) {
                userDropdown.classList.remove('show');
            }
            if (notificationDropdown && notificationDropdown.classList.contains('show') && !notificationDropdown.contains(event.target) && !notificationBtn.contains(event.target)) {
                notificationDropdown.classList.remove('show');
            }
        });

        // Marquer toutes les notifications comme lues
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', markAllNotificationsAsRead);
        }

        // Bouton de déconnexion
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Empêche la navigation directe pour potentiellement gérer une confirmation
                window.location.href = this.href; // Redirige vers la route de déconnexion gérée par le backend
            });
        }

        // Gestion du bouton plein écran (logique existante)
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', function() {
                toggleFullscreen();
            });
            document.addEventListener('fullscreenchange', updateFullscreenIcon);
        }

        // Gestion de la barre de recherche (logique existante)
        const searchInput = document.getElementById('searchInput');
        const searchClear = document.getElementById('searchClear');
        if (searchInput && searchClear) {
            searchInput.addEventListener('input', function() {
                searchClear.style.display = this.value.length > 0 ? 'block' : 'none';
            });
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                // Masquer les suggestions de recherche
                const searchSuggestions = document.getElementById('searchSuggestions');
                if (searchSuggestions) searchSuggestions.style.display = 'none';
            });
            searchInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    // Simuler l'affichage des suggestions existantes (si déjà une valeur)
                    const searchSuggestions = document.getElementById('searchSuggestions');
                    if (searchSuggestions) searchSuggestions.style.display = 'block';
                }
            });
            // Pour fermer les suggestions si on clique ailleurs après avoir focusé
            document.addEventListener('click', function(event) {
                const searchContainer = document.querySelector('.search-container');
                if (searchContainer && !searchContainer.contains(event.target)) {
                    const searchSuggestions = document.getElementById('searchSuggestions');
                    if (searchSuggestions) searchSuggestions.style.display = 'none';
                }
            });
        }

        // Écouteurs d'événements pour les onglets du tableau de bord (logique existante)
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionId = item.dataset.section;
                navigateToSection(sectionId);
            });
        });

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

        const permissionsTabsContainer = document.getElementById('permissions-tabs-container');
        if (permissionsTabsContainer) {
            permissionsTabsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('users-tab')) { // Re-using .users-tab class
                    const tabId = e.target.dataset.tab;
                    setActiveTab(permissionsTabsContainer, tabId);
                    renderPermissionsSection(tabId);
                }
            });
        }

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
        if (userForm) userForm.addEventListener('submit', handleUserFormSubmit);
        if (userFormTypeSelect) userFormTypeSelect.addEventListener('change', updateUserFormForType);

        // Generic CRUD Modal Form
        if (genericCrudForm) genericCrudForm.addEventListener('submit', handleGenericCrudFormSubmit);

        // Alert Confirmation Modal
        if (alertConfirmationConfirmBtn) {
            alertConfirmationConfirmBtn.addEventListener('click', () => {
                if (alertConfirmationCallback) alertConfirmationCallback();
                closeAlertConfirmationModal();
            });
        }

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

    // Dashboard Rendering (A) - Logic from your provided code
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
        const systemAlerts = mockNotifications.filter(n => !n.lue && n.type_message !== 'RAPPORT_STATUT').length; // Example

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

    // User Management (B) - Logic from your provided code
    function renderUsersTable(userTypeKey, page = 1) { // userTypeKey: 'all', 'students', 'teachers', 'staff'
        const tableIdSuffix = userTypeKey;
        const tableBody = document.getElementById(`users-table-body-${tableIdSuffix}`);
        const paginationControls = document.getElementById(`users-pagination-${tableIdSuffix}`);
        const tableTitle = document.getElementById(`users-table-title-${tableIdSuffix}`);

        if (!tableBody) {
            console.warn(`Table body for ${userTypeKey} not found.`);
            // Create the tab content if it doesn't exist (e.g., for students, teachers, staff)
            if (userTypeKey !== 'all') {
                const overviewContent = document.getElementById('users-overview-content')?.cloneNode(true); // Utilisation de ?.
                if (overviewContent) {
                    overviewContent.id = `users-${userTypeKey}-content`;
                    overviewContent.classList.remove('active');
                    overviewContent.querySelectorAll('[id]').forEach(el => el.id = el.id.replace('-all', `-${userTypeKey}`));
                    overviewContent.querySelector('h3').textContent = `Recherche et Filtres (${userTypeKey.charAt(0).toUpperCase() + userTypeKey.slice(1)})`;
                    overviewContent.querySelector(`#users-table-title-${userTypeKey}`).textContent = `Liste des ${userTypeKey.charAt(0).toUpperCase() + userTypeKey.slice(1)}`;

                    const usersContentDiv = document.getElementById('users-content');
                    if (usersContentDiv) usersContentDiv.appendChild(overviewContent);

                    // Re-fetch newly created elements
                    return renderUsersTable(userTypeKey, page); // Retry rendering
                }
            }
            return;
        }

        let filteredUsers = [...mockUsers];

        // Apply type filter if not 'all'
        if (userTypeKey === 'students') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_ETUD');
        else if (userTypeKey === 'teachers') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_ENS');
        else if (userTypeKey === 'staff') filteredUsers = filteredUsers.filter(u => u.id_type_utilisateur === 'TYPE_PERS_ADMIN' || u.id_type_utilisateur === 'TYPE_ADMIN_SYS');

        // Apply search and filters from THIS tab's controls
        const searchTermInput = document.getElementById(`user-search-${tableIdSuffix}`);
        const searchTerm = searchTermInput ? searchTermInput.value.toLowerCase() : '';
        const typeFilterSelect = document.getElementById(`user-type-filter-${tableIdSuffix}`);
        const typeFilter = typeFilterSelect ? typeFilterSelect.value : '';
        const statusFilterSelect = document.getElementById(`user-status-filter-${tableIdSuffix}`);
        const statusFilter = statusFilterSelect ? statusFilterSelect.value : '';
        const groupFilterSelect = document.getElementById(`user-group-filter-${tableIdSuffix}`);
        const groupFilter = groupFilterSelect ? groupFilterSelect.value : '';


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


        const itemsPerPageSelect = document.getElementById(`users-per-page-${tableIdSuffix}`);
        const itemsPerPage = itemsPerPageSelect ? parseInt(itemsPerPageSelect.value) : usersPerPage[userTypeKey];
        const totalItems = filteredUsers.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        currentUsersTablePage[userTypeKey] = Math.min(page, totalPages) || 1;

        const startIndex = (currentUsersTablePage[userTypeKey] - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedUsers = filteredUsers.slice(startIndex, endIndex);

        // Headers
        const tableHead = tableBody.parentElement.querySelector('thead tr');
        if (tableHead && tableHead.innerHTML === '') { // Populate headers once
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
                    <td>${escapeHtml(user.login_utilisateur)}</td>
                    <td>${escapeHtml(user.prenom)} ${escapeHtml(user.nom)}</td>
                    <td>${escapeHtml(user.email_principal)}</td>
                    <td>${userType ? escapeHtml(userType.libelle_type_utilisateur) : escapeHtml(user.id_type_utilisateur)}</td>
                    <td><span class="status-badge ${escapeHtml(user.statut_compte)}">${escapeHtml(user.statut_compte.replace('_', ' '))}</span></td>
                    <td>${user.derniere_connexion ? timeAgo(user.derniere_connexion) : 'Jamais'}</td>
                    <td class="actions-cell">
                        <button onclick="viewUserDetails('${escapeHtml(user.numero_utilisateur)}')" title="Voir Détails"><span class="material-icons">visibility</span></button>
                        <button onclick="openEditUserModal('${escapeHtml(user.numero_utilisateur)}')" title="Modifier"><span class="material-icons">edit</span></button>
                        <button class="delete-btn" onclick="confirmDeleteUser('${escapeHtml(user.numero_utilisateur)}')" title="Archiver/Supprimer"><span class="material-icons">archive</span></button>
                    </td>
                </tr>
            `;
        }).join('');

        if (paginationControls) {
            renderPagination(paginationControls, totalPages, currentUsersTablePage[userTypeKey], (p) => renderUsersTable(userTypeKey, p));
        }

        const countInfo = document.querySelector(`#users-${tableIdSuffix}-content .table-info`);
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

    // Le reste de vos fonctions existantes, adaptées ou non modifiées.

    // ... (Reste de vos fonctions : applyUsersTableFilters, sortUsersTable, populateUserFilterDropdowns,
    // resetUsersFilters, toggleSelectAllUsers, updateBulkActionsPanel, clearUsersBulkSelection,
    // bulkChangeUsersStatus, bulkSendUsersNotification, bulkArchiveUsers, openCreateUserModal,
    // openCreateUserModalWithType, openEditUserModal, closeUserFormModal, populateUserFormDropdowns,
    // updateUserFormForType, changeUserFormStep, updateUserFormStepUI, populateUserFormSummary,
    // handleUserFormSubmit, confirmDeleteUser, viewUserDetails, exportUsersData,
    // renderPermissionsSection, renderPermissionAssignments, displayPermissionsForGroup,
    // togglePermissionAssignment, renderSystemConfigSection, promptActivateAcademicYear,
    // renderAcademicYearSection, renderTemplatesDocsSection, renderAppSettingsSection,
    // saveAppSettings, renderWorkflowMonitoringSection, renderPVManagementSection,
    // renderSystemNotificationsMgmtSection, renderAuditLogsSection, filterAuditLog,
    // renderDataToolsSection, renderTechnicalMaintenanceSection, renderReportingAnalyticsSection,
    // generateSimulatedReport, openGenericCrudModalForCreate, openGenericCrudModalForEdit,
    // closeGenericCrudModal, populateGenericCrudFormFields, handleGenericCrudFormSubmit,
    // confirmDeleteGenericItem, findMockDataItem, createGenericTableHTML, renderPagination,
    // timeAgo, escapeHtml, toggleMobileSidebar, logout, showNotificationsPanel, hideNotificationsPanel,
    // updateNotificationCount, renderNotificationPanelList, markNotificationAsRead,
    // showGenericModal, closeAlertConfirmationModal, showAlertConfirmation, addAuditLog) ...

    // Fonctions d'aide d'en-tête (déplacées ici pour la cohérence)
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log('Erreur lors de l\'activation du plein écran:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }

    function updateFullscreenIcon() {
        const icon = document.querySelector('#fullscreenBtn i');
        if (icon) {
            if (document.fullscreenElement) {
                icon.className = 'fas fa-compress';
                // Assurez-vous que fullscreenBtn existe et a une propriété title
                const fullscreenBtn = document.getElementById('fullscreenBtn');
                if (fullscreenBtn) fullscreenBtn.title = 'Quitter le plein écran';
            } else {
                icon.className = 'fas fa-expand';
                const fullscreenBtn = document.getElementById('fullscreenBtn');
                if (fullscreenBtn) fullscreenBtn.title = 'Plein écran';
            }
        }
    }

    function getNotificationIcon(type) {
        switch (type) {
            case 'SYSTEME': return 'fas fa-cogs';
            case 'RAPPORT_STATUT': return 'fas fa-file-alt';
            case 'COMPTE_UTILISATEUR': return 'fas fa-user-plus';
            case 'URGENT': return 'fas fa-exclamation-triangle';
            default: return 'fas fa-info-circle';
        }
    }

    function getNotificationColorClass(type) {
        switch (type) {
            case 'SYSTEME': return 'text-blue';
            case 'RAPPORT_STATUT': return 'text-green';
            case 'COMPTE_UTILISATEUR': return 'text-purple'; // Assurez-vous que cette classe est définie dans votre CSS
            case 'URGENT': return 'text-red';
            default: return 'text-muted';
        }
    }

    // Initialisation de l'application
    initializeApp();
});