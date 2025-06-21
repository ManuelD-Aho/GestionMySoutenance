<?php
// routes/web.php
use FastRoute\RouteCollector;

// Controllers
use App\Backend\Controller\HomeController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\AssetController;

// Admin Controllers
use App\Backend\Controller\Admin\AnneeAcademiqueController;
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;

// Commission Controllers
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;

// Common Controllers
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\DashboardController;

// Etudiant Controllers
use App\Backend\Controller\Etudiant\DocumentEtudiantController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController;
use App\Backend\Controller\Etudiant\RapportController;
use App\Backend\Controller\Etudiant\ReclamationEtudiantController;
use App\Backend\Controller\Etudiant\RessourcesEtudiantController; // Nouveau

// Personnel Administratif Controllers
use App\Backend\Controller\PersonnelAdministratif\CommunicationInterneController;
use App\Backend\Controller\PersonnelAdministratif\ConformiteController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;


return function (RouteCollector $r) {
    // --- Routes Publiques (non nécessitant une connexion) ---
    $r->get('/', [HomeController::class, 'home']);
    $r->get('/login', [AuthentificationController::class, 'showLoginForm']);
    $r->post('/login', [AuthentificationController::class, 'handleLogin']);
    $r->get('/logout', [AuthentificationController::class, 'logout']); // Utiliser GET pour la déconnexion simple, POST pour plus de sécurité (CSRF)

    // Routes pour la récupération/réinitialisation de mot de passe
    $r->get('/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
    $r->post('/forgot-password', [AuthentificationController::class, 'handleForgotPasswordRequest']);
    $r->get('/reset-password/{token}', [AuthentificationController::class, 'showResetPasswordForm']); // Le token est passé dans l'URL
    $r->post('/reset-password', [AuthentificationController::class, 'handleResetPasswordSubmission']);

    // Route pour la validation d'email (si besoin)
    $r->get('/validate-email/{token}', [AuthentificationController::class, 'validateEmail']); // Méthode à ajouter si pas déjà

    // Route pour l'authentification 2FA
    $r->get('/2fa', [AuthentificationController::class, 'show2FAForm']);
    $r->post('/2fa', [AuthentificationController::class, 'handle2FASubmission']);

    // --- Routes pour les Assets (CSS, JS, images) ---
    $r->get('/assets/css/{filename:.+}', [AssetController::class, 'serveCss']);
    $r->get('/assets/js/{filename:.+}', [AssetController::class, 'serveJs']);
    // Ajoutez d'autres routes pour les images, fonts, etc. si nécessaire (ex: /assets/img/{filename:.+})


    // --- Routes Protégées (nécessitant une connexion) ---
    $r->addGroup('/dashboard', function (RouteCollector $r) {
        // Dashboard principal (dispatcheur de rôles)
        $r->get('', [DashboardController::class, 'index']);

        // --- Routes pour la gestion du PROFIL UTILISATEUR (commun) ---
        $r->addGroup('/profile', function (RouteCollector $r) {
            // Changement de mot de passe (pour utilisateur connecté)
            $r->get('/change-password', [AuthentificationController::class, 'showChangePasswordForm']);
            $r->post('/change-password', [AuthentificationController::class, 'handleChangePassword']);
            // Gestion 2FA (pour utilisateur connecté)
            // Note: La vue pour générer le secret 2FA est maintenant dans ProfilEtudiantController
            // Si vous voulez une gestion 2FA pour TOUS les utilisateurs connectés, déplacez la logique dans ProfilController (à créer si besoin)
            // ou assurez-vous que les routes ci-dessous mènent à la gestion 2FA du profil.
            // Par simplification, les routes 2FA sont pour l'instant uniquement pour l'authentification.
            // La gestion du setup/disable 2FA sera dans les contrôleurs de profil spécifiques.
        });

        // --- Routes pour les NOTIFICATIONS (commun) ---
        $r->addGroup('/notifications', function (RouteCollector $r) {
            $r->get('', [NotificationController::class, 'index']);
            $r->post('/mark-as-read/{idNotificationTemplate}/{dateReception}', [NotificationController::class, 'markAsRead']); // Via AJAX
            $r->post('/delete/{idNotificationTemplate}/{dateReception}', [NotificationController::class, 'deleteNotification']); // Via AJAX
        });


        // --- Groupe de Routes pour l'ADMINISTRATION ---
        // Le doublon de /admin dans votre arborescence de routes a été retiré, ne gardons qu'un seul groupe /admin.
        $r->addGroup('/admin', function (RouteCollector $r) {
            $r->get('', [AdminDashboardController::class, 'index']); // Tableau de bord Admin

            // Gestion des Utilisateurs
            $r->addGroup('/utilisateurs', function (RouteCollector $r) {
                $r->get('', [UtilisateurController::class, 'index']); // Liste tous les utilisateurs
                $r->get('/create/{type}', [UtilisateurController::class, 'create']); // Formulaire création par type (etudiant, enseignant, personnel, admin)
                $r->post('/create/{type}', [UtilisateurController::class, 'create']); // Traitement création
                $r->get('/{id}/edit', [UtilisateurController::class, 'edit']); // Formulaire modification
                $r->post('/{id}/edit', [UtilisateurController::class, 'edit']); // Traitement modification
                $r->post('/{id}/delete', [UtilisateurController::class, 'delete']); // Suppression
                $r->post('/{id}/change-status', [UtilisateurController::class, 'changeStatus']); // Changer statut (actif/bloqué/etc.)
                $r->post('/{id}/reset-password', [UtilisateurController::class, 'resetPassword']); // Réinitialiser MDP
                $r->get('/import-students', [UtilisateurController::class, 'importStudents']); // Formulaire d'import
                $r->post('/import-students', [UtilisateurController::class, 'importStudents']); // Traitement import
            });

            // Gestion des Habilitations (RBAC)
            $r->addGroup('/habilitations', function (RouteCollector $r) {
                $r->get('', [HabilitationController::class, 'index']); // Page d'accueil des habilitations
                // Groupes d'Utilisateurs
                $r->get('/groupes', [HabilitationController::class, 'listGroupes']);
                $r->get('/groupes/create', [HabilitationController::class, 'createGroupe']);
                $r->post('/groupes/create', [HabilitationController::class, 'createGroupe']);
                $r->get('/groupes/{id}/edit', [HabilitationController::class, 'editGroupe']);
                $r->post('/groupes/{id}/edit', [HabilitationController::class, 'editGroupe']);
                $r->post('/groupes/{id}/delete', [HabilitationController::class, 'deleteGroupe']);
                // Types d'Utilisateurs
                $r->get('/types-utilisateur', [HabilitationController::class, 'listTypesUtilisateur']);
                $r->get('/types-utilisateur/create', [HabilitationController::class, 'createTypeUtilisateur']);
                $r->post('/types-utilisateur/create', [HabilitationController::class, 'createTypeUtilisateur']);
                $r->get('/types-utilisateur/{id}/edit', [HabilitationController::class, 'editTypeUtilisateur']);
                $r->post('/types-utilisateur/{id}/edit', [HabilitationController::class, 'editTypeUtilisateur']);
                $r->post('/types-utilisateur/{id}/delete', [HabilitationController::class, 'deleteTypeUtilisateur']);
                // Niveaux d'Accès aux Données
                $r->get('/niveaux-acces', [HabilitationController::class, 'listNiveauxAcces']);
                $r->get('/niveaux-acces/create', [HabilitationController::class, 'createNiveauAcces']);
                $r->post('/niveaux-acces/create', [HabilitationController::class, 'createNiveauAcces']);
                $r->get('/niveaux-acces/{id}/edit', [HabilitationController::class, 'editNiveauAcces']);
                $r->post('/niveaux-acces/{id}/edit', [HabilitationController::class, 'editNiveauAcces']);
                $r->post('/niveaux-acces/{id}/delete', [HabilitationController::class, 'deleteNiveauAcces']);
                // Traitements (Permissions)
                $r->get('/traitements', [HabilitationController::class, 'listTraitements']);
                $r->get('/traitements/create', [HabilitationController::class, 'createTraitement']);
                $r->post('/traitements/create', [HabilitationController::class, 'createTraitement']);
                $r->get('/traitements/{id}/edit', [HabilitationController::class, 'editTraitement']);
                $r->post('/traitements/{id}/edit', [HabilitationController::class, 'editTraitement']);
                $r->post('/traitements/{id}/delete', [HabilitationController::class, 'deleteTraitement']);
                // Rattachement des Permissions aux Groupes
                $r->get('/groupes/{idGroupe}/rattachements', [HabilitationController::class, 'manageRattachements']);
                $r->post('/groupes/{idGroupe}/rattachements/update', [HabilitationController::class, 'updateRattachements']);
            });

            // Gestion des Référentiels
            $r->addGroup('/referentiels', function (RouteCollector $r) {
                $r->get('', [ReferentialController::class, 'index']); // Liste des catégories de référentiels
                $r->get('/{referentielCode}/list', [ReferentialController::class, 'listItems']); // Liste items d'un référentiel
                $r->get('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']); // Formulaire création
                $r->post('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']); // Traitement création
                $r->get('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']); // Formulaire modification
                $r->post('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']); // Traitement modification
                $r->post('/{referentielCode}/delete/{id}', [ReferentialController::class, 'deleteItem']); // Suppression
            });

            // Configuration du Système
            $r->addGroup('/config', function (RouteCollector $r) {
                $r->get('', [ConfigSystemeController::class, 'index']); // Page principale de config
                // Années Académiques
                $r->get('/annee-academique', [AnneeAcademiqueController::class, 'index']); // Liste années académiques
                $r->get('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->post('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->get('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/delete', [AnneeAcademiqueController::class, 'delete']);
                $r->post('/annee-academique/{id}/set-active', [AnneeAcademiqueController::class, 'setActive']); // Nouvelle route pour activer (si ajoutée)
                // Paramètres Généraux
                $r->post('/general-parameters/update', [ConfigSystemeController::class, 'updateGeneralParameters']);
                // Modèles de Documents / Notifications
                $r->get('/templates', [ConfigSystemeController::class, 'showDocumentTemplates']);
                $r->get('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->get('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/delete/{id}', [ConfigSystemeController::class, 'deleteDocumentTemplate']);
            });

            // Gestion Académique
            $r->addGroup('/gestion-acad', function (RouteCollector $r) {
                $r->get('', [GestionAcadController::class, 'index']); // Page d'accueil Gestion Académique
                // Inscriptions
                $r->get('/inscriptions', [GestionAcadController::class, 'listInscriptions']);
                $r->get('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
                $r->post('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
                // Note: Pour edit/delete inscription, les clés composites doivent être passées dans l'URL.
                // C'est plus complexe. On peut les passer via des paramètres de requête ou les concaténer.
                // Exemple avec paramètres de requête (plus simple pour des clés composites)
                $r->get('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/edit', [GestionAcadController::class, 'editInscription']);
                $r->post('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/edit', [GestionAcadController::class, 'editInscription']);
                $r->post('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/delete', [GestionAcadController::class, 'deleteInscription']);
                // Notes
                $r->get('/notes', [GestionAcadController::class, 'listNotes']);
                $r->get('/notes/create', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/create', [GestionAcadController::class, 'handleNoteForm']);
                $r->get('/notes/{numeroCarteEtudiant}/{idEcue}/edit', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/{numeroCarteEtudiant}/{idEcue}/edit', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/{numeroCarteEtudiant}/{idEcue}/delete', [GestionAcadController::class, 'deleteNote']);
            });

            // Supervision et Audit
            $r->addGroup('/supervision', function (RouteCollector $r) {
                $r->get('', [SupervisionController::class, 'index']); // Tableau de bord Supervision
                $r->get('/journaux-audit', [SupervisionController::class, 'showAuditLogs']);
                $r->get('/suivi-workflows', [SupervisionController::class, 'showWorkflowTraces']);
                $r->get('/maintenance', [SupervisionController::class, 'showMaintenanceTools']);
                $r->post('/maintenance/archive-pv', [SupervisionController::class, 'archivePv']);
            });
        });


        // --- Groupe de Routes pour l'ÉTUDIANT ---
        $r->addGroup('/etudiant', function (RouteCollector $r) {
            $r->get('', [EtudiantDashboardController::class, 'index']); // Tableau de bord Étudiant

            // Gestion du Profil Étudiant
            $r->get('/profile', [ProfilEtudiantController::class, 'index']); // Afficher/Modifier le profil
            $r->post('/profile', [ProfilEtudiantController::class, 'index']); // Traiter la modification du profil
            $r->get('/profile/2fa', [ProfilEtudiantController::class, 'manage2FA']); // Gérer le setup 2FA
            $r->post('/profile/2fa/activate', [ProfilEtudiantController::class, 'manage2FA']); // Activer 2FA
            $r->post('/profile/2fa/deactivate', [ProfilEtudiantController::class, 'manage2FA']); // Désactiver 2FA

            // Gestion des Rapports
            $r->addGroup('/rapport', function (RouteCollector $r) {
                $r->get('', [RapportController::class, 'index']); // Suivi du rapport (dernier rapport ou ID spécifié)
                $r->get('/{id}', [RapportController::class, 'index']); // Suivi d'un rapport spécifique
                $r->get('/create-edit-draft', [RapportController::class, 'createOrEditDraft']); // Formulaire brouillon (nouveau)
                $r->get('/create-edit-draft/{id}', [RapportController::class, 'createOrEditDraft']); // Formulaire brouillon (édition)
                $r->post('/save-submit', [RapportController::class, 'saveOrSubmit']); // Sauvegarde brouillon / Soumission finale
                $r->post('/{id}/save-submit', [RapportController::class, 'saveOrSubmit']); // Sauvegarde brouillon / Soumission finale (pour rapport existant)
                $r->get('/{id}/submit-corrections', [RapportController::class, 'showCorrectionForm']); // Formulaire de soumission de corrections
                $r->post('/{id}/submit-corrections', [RapportController::class, 'submitCorrections']); // Traitement soumission corrections
            });

            // Gestion des Réclamations
            $r->addGroup('/reclamation', function (RouteCollector $r) {
                $r->get('', [ReclamationEtudiantController::class, 'index']); // Suivi des réclamations
                $r->get('/create', [ReclamationEtudiantController::class, 'create']); // Formulaire de soumission
                $r->post('/create', [ReclamationEtudiantController::class, 'create']); // Traitement soumission
            });

            // Documents Étudiants (générés)
            $r->addGroup('/documents', function (RouteCollector $r) {
                $r->get('', [DocumentEtudiantController::class, 'index']); // Liste des documents
                $r->get('/download/{id}', [DocumentEtudiantController::class, 'downloadDocument']); // Téléchargement d'un document
            });

            // Ressources Étudiants
            $r->get('/ressources', [RessourcesEtudiantController::class, 'index']);
        });


        // --- Groupe de Routes pour le PERSONNEL ADMINISTRATIF ---
        $r->addGroup('/personnel-admin', function (RouteCollector $r) {
            $r->get('', [PersonnelDashboardController::class, 'index']); // Tableau de bord Personnel Admin

            // Communication Interne
            $r->addGroup('/communication', function (RouteCollector $r) {
                $r->get('', [CommunicationInterneController::class, 'index']); // Liste des conversations
                $r->get('/{idConversation}', [CommunicationInterneController::class, 'index']); // Afficher conversation
                $r->post('/{idConversation}/send', [CommunicationInterneController::class, 'sendMessage']); // Envoyer message
                $r->get('/create', [CommunicationInterneController::class, 'createConversation']); // Créer conv (directe/groupe)
                $r->post('/create', [CommunicationInterneController::class, 'createConversation']); // Traiter création conv
            });

            // Vérification de Conformité
            $r->addGroup('/conformite', function (RouteCollector $r) {
                $r->get('', [ConformiteController::class, 'index']); // Liste rapports à vérifier/traités
                $r->get('/rapports/{idRapport}/verify', [ConformiteController::class, 'showVerificationForm']); // Formulaire de vérification
                $r->post('/rapports/{idRapport}/verify', [ConformiteController::class, 'submitVerification']); // Traitement du verdict
            });

            // Gestion de la Scolarité (pour RS)
            $r->addGroup('/scolarite', function (RouteCollector $r) {
                $r->get('', [ScolariteController::class, 'index']); // Page d'accueil Scolarité
                // Gestion des Étudiants (Simplifié: juste lister ici, le CRUD complet est dans Admin)
                $r->get('/etudiants', [ScolariteController::class, 'listEtudiants']);
                $r->get('/etudiants/{idEtudiant}/validate-stage', [ScolariteController::class, 'validateStage']); // Formulaire validation stage
                $r->post('/etudiants/{idEtudiant}/validate-stage', [ScolariteController::class, 'validateStage']); // Traitement validation stage
                // Gestion des Pénalités
                $r->get('/etudiants/{idEtudiant}/penalites', [ScolariteController::class, 'managePenalites']); // Liste et formulaire ajout/regul
                $r->post('/etudiants/{idEtudiant}/penalites/add', [ScolariteController::class, 'addPenalite']); // Traitement ajout
                $r->post('/etudiants/{idEtudiant}/penalites/regularize/{idPenalite}', [ScolariteController::class, 'regularizePenalite']); // Traitement régularisation

                // Inscriptions (CRUD est dans AdminGestionAcadController)
                $r->get('/inscriptions', [GestionAcadController::class, 'listInscriptions']); // Réutilise le contrôleur Admin pour la liste

                // Notes (CRUD est dans AdminGestionAcadController)
                $r->get('/notes', [GestionAcadController::class, 'listNotes']); // Réutilise le contrôleur Admin pour la liste

                // Réclamations (Traitement)
                $r->get('/reclamations', [ScolariteController::class, 'listReclamationsToProcess']);
                $r->get('/reclamations/{idReclamation}/process', [ScolariteController::class, 'showReclamationDetails']); // Détails et form traitement
                $r->post('/reclamations/{idReclamation}/process', [ScolariteController::class, 'showReclamationDetails']); // Traitement

                // Génération de Documents
                $r->get('/documents', [ScolariteController::class, 'showDocumentGenerationForm']);
                $r->post('/documents/generate', [ScolariteController::class, 'generateDocument']);
            });
        });


        // --- Groupe de Routes pour la COMMISSION ---
        $r->addGroup('/commission', function (RouteCollector $r) {
            $r->get('', [CommissionDashboardController::class, 'index']); // Tableau de bord Commission

            // Communication Commission
            $r->addGroup('/communication', function (RouteCollector $r) {
                $r->get('', [CommunicationCommissionController::class, 'index']);
                $r->get('/{idConversation}', [CommunicationCommissionController::class, 'index']);
                $r->post('/{idConversation}/send', [CommunicationCommissionController::class, 'sendMessage']);
                $r->get('/create', [CommunicationCommissionController::class, 'createConversation']);
                $r->post('/create', [CommunicationCommissionController::class, 'createConversation']);
            });

            // Corrections des Rapports (par la Commission)
            $r->addGroup('/corrections', function (RouteCollector $r) {
                $r->get('', [CorrectionCommissionController::class, 'index']); // Rapports à corriger
                $r->get('/{idRapport}/view', [CorrectionCommissionController::class, 'showReportCorrectionForm']); // Voir détails et soumettre corrections
                $r->post('/{idRapport}/submit', [CorrectionCommissionController::class, 'submitCorrection']); // Traitement soumission
            });

            // Gestion des PV
            $r->addGroup('/pv', function (RouteCollector $r) {
                $r->get('', [PvController::class, 'index']); // Liste des PV
                $r->get('/create', [PvController::class, 'create']); // Formulaire création
                $r->post('/create', [PvController::class, 'create']); // Traitement création
                $r->get('/edit/{id}', [PvController::class, 'create']); // Formulaire modification (réutilise create)
                $r->post('/edit/{id}', [PvController::class, 'create']); // Traitement modification
                $r->post('/submit-for-validation/{id}', [PvController::class, 'submitForValidation']); // Soumettre pour validation
                $r->get('/validate/{id}', [PvController::class, 'validatePv']); // Formulaire validation (par membre)
                $r->post('/validate/{id}', [PvController::class, 'validatePv']); // Traitement validation (par membre)
                $r->post('/delete/{id}', [PvController::class, 'delete']); // Suppression
            });

            // Validation des Rapports (Vote)
            $r->addGroup('/validation/rapports', function (RouteCollector $r) {
                $r->get('', [ValidationRapportController::class, 'index']); // Rapports à voter/valider
                $r->get('/{idRapport}/vote', [ValidationRapportController::class, 'showVoteInterface']); // Interface de vote
                $r->post('/{idRapport}/vote', [ValidationRapportController::class, 'submitVote']); // Traitement du vote
                $r->post('/{idRapport}/new-round', [ValidationRapportController::class, 'newVoteRound']); // Nouveau tour de vote
            });
        });
    });
};