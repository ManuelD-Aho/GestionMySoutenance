<?php

use FastRoute\RouteCollector;

// Contrôleurs de base
use App\Backend\Controller\HomeController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\DashboardController;
use App\Backend\Controller\Common\NotificationController; // Assurez-vous que cette ligne est présente et correcte

// Contrôleurs de l'Administration
use App\Backend\Controller\Admin\AnneeAcademiqueController; // Nouveau contrôleur pour ServiceAnneeAcademique
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
// Nouveaux contrôleurs pour les nouveaux services d'administration
use App\Backend\Controller\Administration\NotificationConfigurationController; // Nouveau
use App\Backend\Controller\Administration\TransitionRoleController; // Nouveau
use App\Backend\Controller\Administration\FichierController; // Nouveau (pour gestion des fichiers génériques)
use App\Backend\Controller\Administration\LoggerController; // Nouveau (pour ServiceLogger)
use App\Backend\Controller\Administration\QueueController; // Nouveau (pour ServiceQueue)


// Contrôleurs de la Commission
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;

// Contrôleurs de l'Étudiant
use App\Backend\Controller\Etudiant\DocumentEtudiantController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController; // Nouveau contrôleur pour ServiceProfilEtudiant
use App\Backend\Controller\Etudiant\RapportController;
use App\Backend\Controller\Etudiant\ReclamationEtudiantController;
use App\Backend\Controller\Etudiant\RessourcesEtudiantController; // Nouveau contrôleur pour ServiceRessourcesEtudiant

// Contrôleurs du Personnel Administratif
use App\Backend\Controller\PersonnelAdministratif\CommunicationInterneController;
use App\Backend\Controller\PersonnelAdministratif\ConformiteController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;
// Nouveau contrôleur pour le nouveau service de documents administratifs
use App\Backend\Controller\PersonnelAdministratif\DocumentAdministratifController; // Nouveau


return function (RouteCollector $r) {
    // --- Routes Publiques (ne nécessitant pas de connexion) ---
    $r->get('/', [HomeController::class, 'home']);

    // Authentification
    $r->get('/login', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/login', [AuthentificationController::class, 'handleLogin']);
    $r->post('/logout', [AuthentificationController::class, 'logout']); // Utilisation de POST pour la sécurité CSRF

    // Récupération/Réinitialisation de mot de passe
    $r->get('/forgot-password', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/forgot-password', [AuthentificationController::class, 'handleForgotPasswordRequest']);
    $r->get('/reset-password/{token}', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/reset-password', [AuthentificationController::class, 'handleResetPasswordSubmission']);

    // Validation d'email
    $r->get('/validate-email/{token}', [AuthentificationController::class, 'validateEmail']); // Méthode à implémenter dans AuthentificationController

    // Authentification 2FA
    $r->get('/2fa', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/2fa', [AuthentificationController::class, 'handle2FASubmission']);

    // --- Routes pour les Assets (CSS, JS, images) ---
    $r->get('/assets/css/{filename:.+}', [AssetController::class, 'serveCss']);
    $r->get('/assets/js/{filename:.+}', [AssetController::class, 'serveJs']);
    $r->get('/assets/img/{filename:.+}', [AssetController::class, 'serveImg']); // Pour les images (ex: photos de profil, logos)
    $r->get('/assets/img/carousel/{filename:.+}', [AssetController::class, 'serveCarImg']); // Nouvelle route pour les images du carrousel
    $r->get('/assets/uploads/{type}/{filename:.+}', [AssetController::class, 'serveUpload']); // Pour les fichiers uploadés (sécurisé par AssetController)


    // --- Routes Protégées (nécessitant une connexion) ---
    $r->addGroup('/dashboard', function (RouteCollector $r) {
        // Dashboard principal (dispatcheur de rôles)
        $r->get('', [DashboardController::class, 'index']);

        // --- Routes pour la gestion du PROFIL UTILISATEUR (commun) ---
        $r->addGroup('/profile', function (RouteCollector $r) {
            // Changement de mot de passe (pour utilisateur connecté)
            $r->get('/change-password', [AuthentificationController::class, 'showChangePasswordForm']);
            $r->post('/change-password', [AuthentificationController::class, 'handleChangePassword']);
            // Note: La gestion du setup/disable 2FA est maintenant dans les contrôleurs de profil spécifiques (ex: ProfilEtudiantController)
        });

        // --- Routes pour les NOTIFICATIONS (commun) ---
        // Ces routes sont désormais des API pour être consommées par le frontend JS
        $r->addGroup('/notifications', function (RouteCollector $r) {
            $r->get('', [NotificationController::class, 'index']); // Pour la page complète des notifications
            $r->post('/mark-as-read/{idReception}', [NotificationController::class, 'markAsRead']); // Utilise idReception comme PK
            $r->post('/delete/{idReception}', [NotificationController::class, 'deleteNotification']); // Utilise idReception comme PK
        });


        // --- Groupe de Routes pour l'ADMINISTRATION ---
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

            // Gestion des Référentiels (Générique)
            $r->addGroup('/referentiels', function (RouteCollector $r) {
                $r->get('', [ReferentialController::class, 'index']); // Liste des catégories de référentiels
                $r->get('/{referentielCode}/list', [ReferentialController::class, 'listItems']); // Liste items d'un référentiel
                $r->get('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']); // Formulaire création
                $r->post('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']); // Traitement création
                $r->get('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']); // Formulaire modification
                $r->post('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']); // Traitement modification
                $r->post('/{referentielCode}/delete/{id}', [ReferentialController::class, 'deleteItem']); // Suppression
            });

            // Configuration du Système (Paramètres Généraux, Modèles de Docs/Notifications, Années Académiques)
            $r->addGroup('/config', function (RouteCollector $r) {
                $r->get('', [ConfigSystemeController::class, 'index']); // Page principale de config
                // Années Académiques (maintenant géré par un contrôleur dédié)
                $r->get('/annee-academique', [AnneeAcademiqueController::class, 'index']); // Liste années académiques
                $r->get('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->post('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->get('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/delete', [AnneeAcademiqueController::class, 'delete']);
                $r->post('/annee-academique/{id}/set-active', [AnneeAcademiqueController::class, 'setActive']); // Nouvelle route pour activer
                // Paramètres Généraux
                $r->post('/general-parameters/update', [ConfigSystemeController::class, 'updateGeneralParameters']);
                // Modèles de Documents / Notifications (géré par ConfigSystemeController)
                $r->get('/templates', [ConfigSystemeController::class, 'showDocumentTemplates']);
                $r->get('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->get('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/delete/{id}', [ConfigSystemeController::class, 'deleteDocumentTemplate']);
                // Configuration des Notifications (Matrice de diffusion, préférences)
                $r->get('/notifications-config', [NotificationConfigurationController::class, 'index']); // Nouvelle route
                $r->post('/notifications-config/update-matrix', [NotificationConfigurationController::class, 'updateMatrix']); // Nouvelle route
            });

            // Gestion Académique (Inscriptions, Notes, Stages, Pénalités, Carrières Enseignants)
            $r->addGroup('/gestion-acad', function (RouteCollector $r) {
                $r->get('', [GestionAcadController::class, 'index']); // Page d'accueil Gestion Académique
                // Inscriptions
                $r->get('/inscriptions', [GestionAcadController::class, 'listInscriptions']);
                $r->get('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
                $r->post('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
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
                // Stages (CRUD complet si nécessaire, sinon juste validation par ScolariteController)
                $r->get('/stages', [GestionAcadController::class, 'listStages']); // Nouvelle route
                $r->get('/stages/create', [GestionAcadController::class, 'createStage']); // Nouvelle route
                $r->post('/stages/create', [GestionAcadController::class, 'createStage']); // Nouvelle route
                $r->get('/stages/{idEntreprise}/{numeroCarteEtudiant}/edit', [GestionAcadController::class, 'editStage']); // Nouvelle route
                $r->post('/stages/{idEntreprise}/{numeroCarteEtudiant}/edit', [GestionAcadController::class, 'editStage']); // Nouvelle route
                $r->post('/stages/{idEntreprise}/{numeroCarteEtudiant}/delete', [GestionAcadController::class, 'deleteStage']); // Nouvelle route
                // Carrières Enseignants (Grades, Fonctions, Spécialités)
                $r->get('/enseignants/carrieres', [GestionAcadController::class, 'manageEnseignantCarrieres']); // Nouvelle route
                $r->post('/enseignants/carrieres/add-grade', [GestionAcadController::class, 'addEnseignantGrade']); // Nouvelle route
                $r->post('/enseignants/carrieres/add-fonction', [GestionAcadController::class, 'addEnseignantFonction']); // Nouvelle route
                $r->post('/enseignants/carrieres/add-specialite', [GestionAcadController::class, 'addEnseignantSpecialite']); // Nouvelle route
                // Gestion des UE/ECUEs
                $r->get('/ues', [GestionAcadController::class, 'listUes']); // Nouvelle route
                $r->get('/ues/create', [GestionAcadController::class, 'createUe']); // Nouvelle route
                $r->post('/ues/create', [GestionAcadController::class, 'createUe']); // Nouvelle route
                $r->get('/ecues', [GestionAcadController::class, 'listEcues']); // Nouvelle route
                $r->get('/ecues/create', [GestionAcadController::class, 'createEcue']); // Nouvelle route
                $r->post('/ecues/create', [GestionAcadController::class, 'createEcue']); // Nouvelle route
                $r->post('/ecues/{idEcue}/link-ue/{idUe}', [GestionAcadController::class, 'linkEcueToUe']); // Nouvelle route
            });

            // Supervision et Audit
            $r->addGroup('/supervision', function (RouteCollector $r) {
                $r->get('', [SupervisionController::class, 'index']); // Tableau de bord Supervision
                $r->get('/journaux-audit', [SupervisionController::class, 'showAuditLogs']);
                $r->get('/suivi-workflows', [SupervisionController::class, 'showWorkflowTraces']);
                $r->get('/maintenance', [SupervisionController::class, 'showMaintenanceTools']);
                $r->post('/maintenance/archive-pv', [SupervisionController::class, 'archivePv']);
                // Gestion des logs PHP (via ServiceLogger)
                $r->get('/logs', [LoggerController::class, 'index']); // Nouvelle route
                $r->post('/logs/clear', [LoggerController::class, 'clearLogs']); // Nouvelle route
                // Gestion des tâches en file d'attente (via ServiceQueue)
                $r->get('/queue', [QueueController::class, 'index']); // Nouvelle route
                $r->post('/queue/process-next', [QueueController::class, 'processNextJob']); // Nouvelle route
            });

            // Reporting
            $r->addGroup('/reporting', function (RouteCollector $r) {
                $r->get('', [ReportingController::class, 'index']); // Page des rapports
                $r->post('/filter', [ReportingController::class, 'filterReports']); // Filtrage des rapports
            });

            // Gestion des Fichiers (générique pour l'admin)
            $r->addGroup('/files', function (RouteCollector $r) {
                $r->get('', [FichierController::class, 'index']); // Nouvelle route: Lister les fichiers uploadés
                $r->post('/upload', [FichierController::class, 'upload']); // Nouvelle route: Upload de fichier
                $r->post('/delete/{idFichier}', [FichierController::class, 'delete']); // Nouvelle route: Suppression de fichier
            });

            // Gestion des Transitions de Rôles et Délégations
            $r->addGroup('/transition-roles', function (RouteCollector $r) {
                $r->get('', [TransitionRoleController::class, 'index']); // Nouvelle route: Tableau de bord des transitions
                $r->get('/detect-orphans/{idUser}', [TransitionRoleController::class, 'detectOrphanTasks']); // Nouvelle route
                $r->post('/reassign-task/{idTask}', [TransitionRoleController::class, 'reassignTask']); // Nouvelle route
                $r->get('/delegations', [TransitionRoleController::class, 'listDelegations']); // Nouvelle route
                $r->get('/delegations/create', [TransitionRoleController::class, 'createDelegation']); // Nouvelle route
                $r->post('/delegations/create', [TransitionRoleController::class, 'createDelegation']); // Nouvelle route
                $r->post('/delegations/{idDelegation}/cancel', [TransitionRoleController::class, 'cancelDelegation']); // Nouvelle route
            });
        });


        // --- Groupe de Routes pour l'ÉTUDIANT ---
        $r->addGroup('/etudiant', function (RouteCollector $r) {
            $r->get('', [EtudiantDashboardController::class, 'index']); // Tableau de bord Étudiant

            // Gestion du Profil Étudiant
            $r->get('/profile', [ProfilEtudiantController::class, 'index']); // Afficher/Modifier le profil
            $r->post('/profile', [ProfilEtudiantController::class, 'index']); // Traiter la modification du profil
            $r->post('/profile/upload-photo', [ProfilEtudiantController::class, 'uploadPhoto']); // Nouvelle route pour upload photo
            $r->get('/profile/2fa', [ProfilEtudiantController::class, 'manage2FA']); // Gérer le setup 2FA
            $r->post('/profile/2fa/activate', [ProfilEtudiantController::class, 'manage2FA']); // Activer 2FA
            $r->post('/profile/2fa/deactivate', [ProfilEtudiantController::class, 'manage2FA']); // Désactiver 2FA

            // Gestion des Rapports
            $r->addGroup('/rapport', function (RouteCollector $r) {
                $r->get('/create-edit-draft', [RapportController::class, 'createOrEditDraft']); // Formulaire brouillon (nouveau)
                $r->post('/save-submit', [RapportController::class, 'saveOrSubmit']); // Sauvegarde brouillon / Soumission finale

                $r->get('/{id}/submit-corrections', [RapportController::class, 'showCorrectionForm']); // Formulaire de soumission de corrections
                $r->post('/{id}/submit-corrections', [RapportController::class, 'submitCorrections']); // Traitement soumission corrections

                $r->get('/{id}/edit', [RapportController::class, 'createOrEditDraft']); // Pour l'édition d'un brouillon existant
                $r->post('/{id}/save-submit', [RapportController::class, 'saveOrSubmit']); // Sauvegarde brouillon / Soumission finale (pour rapport existant)

                $r->get('/{id}', [RapportController::class, 'index']); // Suivi d'un rapport spécifique
                $r->get('', [RapportController::class, 'index']); // Suivi du rapport (dernier rapport ou ID spécifié)
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
                $r->get('/create', [CommunicationInterneController::class, 'createConversation']); // Créer conv (directe/groupe)
                $r->post('/create', [CommunicationInterneController::class, 'createConversation']); // Traiter création conv
                $r->get('/{idConversation}', [CommunicationInterneController::class, 'index']); // Afficher conversation
                $r->post('/{idConversation}/send', [CommunicationInterneController::class, 'sendMessage']); // Envoyer message
                $r->get('', [CommunicationInterneController::class, 'index']); // Liste des conversations
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

                // Génération de Documents (pour RS)
                $r->get('/documents', [DocumentAdministratifController::class, 'showDocumentGenerationForm']); // Utilise le nouveau contrôleur
                $r->post('/documents/generate', [DocumentAdministratifController::class, 'generateDocument']); // Utilise le nouveau contrôleur
            });
        });


        // --- Groupe de Routes pour la COMMISSION ---
        $r->addGroup('/commission', function (RouteCollector $r) {
            $r->get('', [CommissionDashboardController::class, 'index']); // Tableau de bord Commission

            // Communication Commission
            $r->addGroup('/communication', function (RouteCollector $r) {
                $r->get('/create', [CommunicationCommissionController::class, 'createConversation']);
                $r->post('/create', [CommunicationCommissionController::class, 'createConversation']);
                $r->get('/{idConversation}', [CommunicationCommissionController::class, 'index']);
                $r->post('/{idConversation}/send', [CommunicationCommissionController::class, 'sendMessage']);
                $r->get('', [CommunicationCommissionController::class, 'index']);
            });

            // Corrections des Rapports (par la Commission)
            $r->addGroup('/corrections', function (RouteCollector $r) {
                $r->get('', [CorrectionCommissionController::class, 'index']);
                $r->get('/{idRapport}/view', [CorrectionCommissionController::class, 'showReportCorrectionForm']);
                $r->post('/{idRapport}/submit', [CorrectionCommissionController::class, 'submitCorrection']);
            });

            // Gestion des PV
            $r->addGroup('/pv', function (RouteCollector $r) {
                $r->get('', [PvController::class, 'index']);
                $r->get('/create', [PvController::class, 'create']);
                $r->post('/create', [PvController::class, 'create']);
                $r->get('/edit/{id}', [PvController::class, 'create']);
                $r->post('/edit/{id}', [PvController::class, 'create']);
                $r->post('/submit-for-validation/{id}', [PvController::class, 'submitForValidation']);
                $r->get('/validate/{id}', [PvController::class, 'validatePv']);
                $r->post('/validate/{id}', [PvController::class, 'validatePv']);
                $r->post('/delete/{id}', [PvController::class, 'delete']);
                $r->post('/{id}/delegate-redaction', [PvController::class, 'delegateRedaction']); // Nouvelle route
                $r->post('/{id}/manage-approvals', [PvController::class, 'manageApprovals']); // Nouvelle route pour gérer les approbations bloquées
            });

            // Validation des Rapports (Vote)
            $r->addGroup('/validation/rapports', function (RouteCollector $r) {
                $r->get('', [ValidationRapportController::class, 'index']);
                $r->get('/{idRapport}/vote', [ValidationRapportController::class, 'showVoteInterface']);
                $r->post('/{idRapport}/vote', [ValidationRapportController::class, 'submitVote']);
                $r->post('/{idRapport}/new-round', [ValidationRapportController::class, 'newVoteRound']);
                $r->post('/{idRapport}/withdraw-from-session', [ValidationRapportController::class, 'withdrawFromSession']); // Nouvelle route
            });
        });
    });

    // --- Routes API (pour AJAX) ---
    $r->addGroup('/api', function (RouteCollector $r) {
        // API Notifications pour le header
        $r->get('/notifications', [NotificationController::class, 'getNotifications']);
        $r->post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        $r->post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

        // Ajoutez d'autres routes API ici si nécessaire
        // Par exemple, pour la recherche dynamique :
        // $r->get('/search', [SearchController::class, 'search']);
    });
};