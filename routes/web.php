<?php

use FastRoute\RouteCollector;
use App\Backend\Controller\HomeController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\DashboardController;
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\Admin\AnneeAcademiqueController;
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
use App\Backend\Controller\Administration\NotificationConfigurationController;
use App\Backend\Controller\Administration\TransitionRoleController;
use App\Backend\Controller\Administration\FichierController;
use App\Backend\Controller\Administration\LoggerController;
use App\Backend\Controller\Administration\QueueController;
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;
use App\Backend\Controller\Etudiant\DocumentEtudiantController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController;
use App\Backend\Controller\Etudiant\RapportController;
use App\Backend\Controller\Etudiant\ReclamationEtudiantController;
use App\Backend\Controller\Etudiant\RessourcesEtudiantController;
use App\Backend\Controller\PersonnelAdministratif\CommunicationInterneController;
use App\Backend\Controller\PersonnelAdministratif\ConformiteController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;
use App\Backend\Controller\PersonnelAdministratif\DocumentAdministratifController;

return function (RouteCollector $r) {
    $r->get('/', [HomeController::class, 'home']);

    $r->get('/login', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/login', [AuthentificationController::class, 'handleLogin']);
    $r->post('/logout', [AuthentificationController::class, 'logout']);
    $r->get('/forgot-password', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/forgot-password', [AuthentificationController::class, 'handleForgotPasswordRequest']);
    $r->get('/reset-password/{token}', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/reset-password', [AuthentificationController::class, 'handleResetPasswordSubmission']);
    $r->get('/validate-email/{token}', [AuthentificationController::class, 'validateEmail']);
    $r->get('/2fa', [AuthentificationController::class, 'showUnifiedAuthPage']);
    $r->post('/2fa', [AuthentificationController::class, 'handle2FASubmission']);

    $r->get('/assets/css/{filename:.+}', [AssetController::class, 'serveCss']);
    $r->get('/assets/js/{filename:.+}', [AssetController::class, 'serveJs']);
    $r->get('/assets/img/{filename:.+}', [AssetController::class, 'serveImg']);
    $r->get('/assets/img/carousel/{filename:.+}', [AssetController::class, 'serveCarImg']);
    $r->get('/assets/uploads/{type}/{filename:.+}', [AssetController::class, 'serveUpload']);

    $r->addGroup('/dashboard', function (RouteCollector $r) {
        $r->get('', [DashboardController::class, 'index']);

        $r->addGroup('/profile', function (RouteCollector $r) {
            $r->get('/change-password', [AuthentificationController::class, 'showChangePasswordForm']);
            $r->post('/change-password', [AuthentificationController::class, 'handleChangePassword']);
        });

        $r->addGroup('/notifications', function (RouteCollector $r) {
            $r->get('', [NotificationController::class, 'index']);
            $r->post('/mark-as-read/{idReception}', [NotificationController::class, 'markAsRead']);
            $r->post('/delete/{idReception}', [NotificationController::class, 'deleteNotification']);
        });

        $r->addGroup('/admin', function (RouteCollector $r) {
            $r->get('', [AdminDashboardController::class, 'index']);

            $r->addGroup('/utilisateurs', function (RouteCollector $r) {
                $r->get('', [UtilisateurController::class, 'index']);
                $r->get('/create/{type}', [UtilisateurController::class, 'create']);
                $r->post('/create/{type}', [UtilisateurController::class, 'create']);
                $r->get('/{id}/edit', [UtilisateurController::class, 'edit']);
                $r->post('/{id}/edit', [UtilisateurController::class, 'edit']);
                $r->post('/{id}/delete', [UtilisateurController::class, 'delete']);
                $r->post('/{id}/change-status', [UtilisateurController::class, 'changeStatus']);
                $r->post('/{id}/reset-password', [UtilisateurController::class, 'resetPassword']);
                $r->get('/import-students', [UtilisateurController::class, 'importStudents']);
                $r->post('/import-students', [UtilisateurController::class, 'importStudents']);
            });

            $r->addGroup('/habilitations', function (RouteCollector $r) {
                $r->get('', [HabilitationController::class, 'index']);
                $r->get('/groupes', [HabilitationController::class, 'listGroupes']);
                $r->get('/groupes/create', [HabilitationController::class, 'createGroupe']);
                $r->post('/groupes/create', [HabilitationController::class, 'createGroupe']);
                $r->get('/groupes/{id}/edit', [HabilitationController::class, 'editGroupe']);
                $r->post('/groupes/{id}/edit', [HabilitationController::class, 'editGroupe']);
                $r->post('/groupes/{id}/delete', [HabilitationController::class, 'deleteGroupe']);
                $r->get('/types-utilisateur', [HabilitationController::class, 'listTypesUtilisateur']);
                $r->get('/types-utilisateur/create', [HabilitationController::class, 'createTypeUtilisateur']);
                $r->post('/types-utilisateur/create', [HabilitationController::class, 'createTypeUtilisateur']);
                $r->get('/types-utilisateur/{id}/edit', [HabilitationController::class, 'editTypeUtilisateur']);
                $r->post('/types-utilisateur/{id}/edit', [HabilitationController::class, 'editTypeUtilisateur']);
                $r->post('/types-utilisateur/{id}/delete', [HabilitationController::class, 'deleteTypeUtilisateur']);
                $r->get('/niveaux-acces', [HabilitationController::class, 'listNiveauxAcces']);
                $r->get('/niveaux-acces/create', [HabilitationController::class, 'createNiveauAcces']);
                $r->post('/niveaux-acces/create', [HabilitationController::class, 'createNiveauAcces']);
                $r->get('/niveaux-acces/{id}/edit', [HabilitationController::class, 'editNiveauAcces']);
                $r->post('/niveaux-acces/{id}/edit', [HabilitationController::class, 'editNiveauAcces']);
                $r->post('/niveaux-acces/{id}/delete', [HabilitationController::class, 'deleteNiveauAcces']);
                $r->get('/traitements', [HabilitationController::class, 'listTraitements']);
                $r->get('/traitements/create', [HabilitationController::class, 'createTraitement']);
                $r->post('/traitements/create', [HabilitationController::class, 'createTraitement']);
                $r->get('/traitements/{id}/edit', [HabilitationController::class, 'editTraitement']);
                $r->post('/traitements/{id}/edit', [HabilitationController::class, 'editTraitement']);
                $r->post('/traitements/{id}/delete', [HabilitationController::class, 'deleteTraitement']);
                $r->get('/groupes/{idGroupe}/rattachements', [HabilitationController::class, 'manageRattachements']);
                $r->post('/groupes/{idGroupe}/rattachements/update', [HabilitationController::class, 'updateRattachements']);
            });

            $r->addGroup('/referentiels', function (RouteCollector $r) {
                $r->get('', [ReferentialController::class, 'index']);
                $r->get('/{referentielCode}/list', [ReferentialController::class, 'listItems']);
                $r->get('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']);
                $r->post('/{referentielCode}/create', [ReferentialController::class, 'handleItemForm']);
                $r->get('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']);
                $r->post('/{referentielCode}/edit/{id}', [ReferentialController::class, 'handleItemForm']);
                $r->post('/{referentielCode}/delete/{id}', [ReferentialController::class, 'deleteItem']);
            });

            $r->addGroup('/config', function (RouteCollector $r) {
                $r->get('', [ConfigSystemeController::class, 'index']);
                $r->get('/annee-academique', [AnneeAcademiqueController::class, 'index']);
                $r->get('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->post('/annee-academique/create', [AnneeAcademiqueController::class, 'create']);
                $r->get('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/edit', [AnneeAcademiqueController::class, 'edit']);
                $r->post('/annee-academique/{id}/delete', [AnneeAcademiqueController::class, 'delete']);
                $r->post('/annee-academique/{id}/set-active', [AnneeAcademiqueController::class, 'setActive']);
                $r->post('/general-parameters/update', [ConfigSystemeController::class, 'updateGeneralParameters']);
                $r->get('/templates', [ConfigSystemeController::class, 'showDocumentTemplates']);
                $r->get('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/create', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->get('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/edit/{id}', [ConfigSystemeController::class, 'handleDocumentTemplate']);
                $r->post('/templates/delete/{id}', [ConfigSystemeController::class, 'deleteDocumentTemplate']);
                $r->get('/notifications-config', [NotificationConfigurationController::class, 'index']);
                $r->post('/notifications-config/update-matrix', [NotificationConfigurationController::class, 'updateMatrix']);
            });

            $r->addGroup('/gestion-acad', function (RouteCollector $r) {
                $r->get('', [GestionAcadController::class, 'index']);
                $r->get('/inscriptions', [GestionAcadController::class, 'listInscriptions']);
                $r->get('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
                $r->post('/inscriptions/create', [GestionAcadController::class, 'createInscription']);
                $r->get('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/edit', [GestionAcadController::class, 'editInscription']);
                $r->post('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/edit', [GestionAcadController::class, 'editInscription']);
                $r->post('/inscriptions/{numeroCarteEtudiant}/{idNiveauEtude}/{idAnneeAcademique}/delete', [GestionAcadController::class, 'deleteInscription']);
                $r->get('/notes', [GestionAcadController::class, 'listNotes']);
                $r->get('/notes/create', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/create', [GestionAcadController::class, 'handleNoteForm']);
                $r->get('/notes/{numeroCarteEtudiant}/{idEcue}/edit', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/{numeroCarteEtudiant}/{idEcue}/edit', [GestionAcadController::class, 'handleNoteForm']);
                $r->post('/notes/{numeroCarteEtudiant}/{idEcue}/delete', [GestionAcadController::class, 'deleteNote']);
                $r->get('/stages', [GestionAcadController::class, 'listStages']);
                $r->get('/stages/create', [GestionAcadController::class, 'createStage']);
                $r->post('/stages/create', [GestionAcadController::class, 'createStage']);
                $r->get('/stages/{idEntreprise}/{numeroCarteEtudiant}/edit', [GestionAcadController::class, 'editStage']);
                $r->post('/stages/{idEntreprise}/{numeroCarteEtudiant}/edit', [GestionAcadController::class, 'editStage']);
                $r->post('/stages/{idEntreprise}/{numeroCarteEtudiant}/delete', [GestionAcadController::class, 'deleteStage']);
                $r->get('/enseignants/carrieres', [GestionAcadController::class, 'manageEnseignantCarrieres']);
                $r->post('/enseignants/carrieres/add-grade', [GestionAcadController::class, 'addEnseignantGrade']);
                $r->post('/enseignants/carrieres/add-fonction', [GestionAcadController::class, 'addEnseignantFonction']);
                $r->post('/enseignants/carrieres/add-specialite', [GestionAcadController::class, 'addEnseignantSpecialite']);
                $r->get('/ues', [GestionAcadController::class, 'listUes']);
                $r->get('/ues/create', [GestionAcadController::class, 'createUe']);
                $r->post('/ues/create', [GestionAcadController::class, 'createUe']);
                $r->get('/ecues', [GestionAcadController::class, 'listEcues']);
                $r->get('/ecues/create', [GestionAcadController::class, 'createEcue']);
                $r->post('/ecues/create', [GestionAcadController::class, 'createEcue']);
                $r->post('/ecues/{idEcue}/link-ue/{idUe}', [GestionAcadController::class, 'linkEcueToUe']);
            });

            $r->addGroup('/supervision', function (RouteCollector $r) {
                $r->get('', [SupervisionController::class, 'index']);
                $r->get('/journaux-audit', [SupervisionController::class, 'showAuditLogs']);
                $r->get('/suivi-workflows', [SupervisionController::class, 'showWorkflowTraces']);
                $r->get('/maintenance', [SupervisionController::class, 'showMaintenanceTools']);
                $r->post('/maintenance/archive-pv', [SupervisionController::class, 'archivePv']);
                $r->get('/logs', [LoggerController::class, 'index']);
                $r->post('/logs/clear', [LoggerController::class, 'clearLogs']);
                $r->get('/queue', [QueueController::class, 'index']);
                $r->post('/queue/process-next', [QueueController::class, 'processNextJob']);
            });

            $r->addGroup('/reporting', function (RouteCollector $r) {
                $r->get('', [ReportingController::class, 'index']);
                $r->post('/filter', [ReportingController::class, 'filterReports']);
            });

            $r->addGroup('/files', function (RouteCollector $r) {
                $r->get('', [FichierController::class, 'index']);
                $r->post('/upload', [FichierController::class, 'upload']);
                $r->post('/delete/{idFichier}', [FichierController::class, 'delete']);
            });

            $r->addGroup('/transition-roles', function (RouteCollector $r) {
                $r->get('', [TransitionRoleController::class, 'index']);
                $r->get('/detect-orphans/{idUser}', [TransitionRoleController::class, 'detectOrphanTasks']);
                $r->post('/reassign-task/{idTask}', [TransitionRoleController::class, 'reassignTask']);
                $r->get('/delegations', [TransitionRoleController::class, 'listDelegations']);
                $r->get('/delegations/create', [TransitionRoleController::class, 'createDelegation']);
                $r->post('/delegations/create', [TransitionRoleController::class, 'createDelegation']);
                $r->post('/delegations/{idDelegation}/cancel', [TransitionRoleController::class, 'cancelDelegation']);
            });
        });

        $r->addGroup('/etudiant', function (RouteCollector $r) {
            $r->get('', [EtudiantDashboardController::class, 'index']);
            $r->get('/profile', [ProfilEtudiantController::class, 'index']);
            $r->post('/profile', [ProfilEtudiantController::class, 'index']);
            $r->post('/profile/upload-photo', [ProfilEtudiantController::class, 'uploadPhoto']);
            $r->get('/profile/2fa', [ProfilEtudiantController::class, 'manage2FA']);
            $r->post('/profile/2fa/activate', [ProfilEtudiantController::class, 'manage2FA']);
            $r->post('/profile/2fa/deactivate', [ProfilEtudiantController::class, 'manage2FA']);

            $r->addGroup('/rapport', function (RouteCollector $r) {
                $r->get('/create-edit-draft', [RapportController::class, 'createOrEditDraft']);
                $r->post('/save-submit', [RapportController::class, 'saveOrSubmit']);
                $r->get('/{id}/submit-corrections', [RapportController::class, 'showCorrectionForm']);
                $r->post('/{id}/submit-corrections', [RapportController::class, 'submitCorrections']);
                $r->get('/{id}/edit', [RapportController::class, 'createOrEditDraft']);
                $r->post('/{id}/save-submit', [RapportController::class, 'saveOrSubmit']);
                $r->get('/{id}', [RapportController::class, 'index']);
                $r->get('', [RapportController::class, 'index']);
            });

            $r->addGroup('/reclamation', function (RouteCollector $r) {
                $r->get('', [ReclamationEtudiantController::class, 'index']);
                $r->get('/create', [ReclamationEtudiantController::class, 'create']);
                $r->post('/create', [ReclamationEtudiantController::class, 'create']);
            });

            $r->addGroup('/documents', function (RouteCollector $r) {
                $r->get('', [DocumentEtudiantController::class, 'index']);
                $r->get('/download/{id}', [DocumentEtudiantController::class, 'downloadDocument']);
            });

            $r->get('/ressources', [RessourcesEtudiantController::class, 'index']);
        });

        $r->addGroup('/personnel-admin', function (RouteCollector $r) {
            $r->get('', [PersonnelDashboardController::class, 'index']);

            $r->addGroup('/communication', function (RouteCollector $r) {
                $r->get('/create', [CommunicationInterneController::class, 'createConversation']);
                $r->post('/create', [CommunicationInterneController::class, 'createConversation']);
                $r->get('/{idConversation}', [CommunicationInterneController::class, 'index']);
                $r->post('/{idConversation}/send', [CommunicationInterneController::class, 'sendMessage']);
                $r->get('', [CommunicationInterneController::class, 'index']);
            });

            $r->addGroup('/conformite', function (RouteCollector $r) {
                $r->get('', [ConformiteController::class, 'index']);
                $r->get('/rapports/{idRapport}/verify', [ConformiteController::class, 'showVerificationForm']);
                $r->post('/rapports/{idRapport}/verify', [ConformiteController::class, 'submitVerification']);
            });

            $r->addGroup('/scolarite', function (RouteCollector $r) {
                $r->get('', [ScolariteController::class, 'index']);
                $r->get('/etudiants', [ScolariteController::class, 'listEtudiants']);
                $r->get('/etudiants/{idEtudiant}/validate-stage', [ScolariteController::class, 'validateStage']);
                $r->post('/etudiants/{idEtudiant}/validate-stage', [ScolariteController::class, 'validateStage']);
                $r->get('/etudiants/{idEtudiant}/penalites', [ScolariteController::class, 'managePenalites']);
                $r->post('/etudiants/{idEtudiant}/penalites/add', [ScolariteController::class, 'addPenalite']);
                $r->post('/etudiants/{idEtudiant}/penalites/regularize/{idPenalite}', [ScolariteController::class, 'regularizePenalite']);
                $r->get('/inscriptions', [GestionAcadController::class, 'listInscriptions']);
                $r->get('/notes', [GestionAcadController::class, 'listNotes']);
                $r->get('/reclamations', [ScolariteController::class, 'listReclamationsToProcess']);
                $r->get('/reclamations/{idReclamation}/process', [ScolariteController::class, 'showReclamationDetails']);
                $r->post('/reclamations/{idReclamation}/process', [ScolariteController::class, 'showReclamationDetails']);
                $r->get('/documents', [DocumentAdministratifController::class, 'showDocumentGenerationForm']);
                $r->post('/documents/generate', [DocumentAdministratifController::class, 'generateDocument']);
            });
        });

        $r->addGroup('/commission', function (RouteCollector $r) {
            $r->get('', [CommissionDashboardController::class, 'index']);

            $r->addGroup('/communication', function (RouteCollector $r) {
                $r->get('/create', [CommunicationCommissionController::class, 'createConversation']);
                $r->post('/create', [CommunicationCommissionController::class, 'createConversation']);
                $r->get('/{idConversation}', [CommunicationCommissionController::class, 'index']);
                $r->post('/{idConversation}/send', [CommunicationCommissionController::class, 'sendMessage']);
                $r->get('', [CommunicationCommissionController::class, 'index']);
            });

            $r->addGroup('/corrections', function (RouteCollector $r) {
                $r->get('', [CorrectionCommissionController::class, 'index']);
                $r->get('/{idRapport}/view', [CorrectionCommissionController::class, 'showReportCorrectionForm']);
                $r->post('/{idRapport}/submit', [CorrectionCommissionController::class, 'submitCorrection']);
            });

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
                $r->post('/{id}/delegate-redaction', [PvController::class, 'delegateRedaction']);
                $r->post('/{id}/manage-approvals', [PvController::class, 'manageApprovals']);
            });

            $r->addGroup('/validation/rapports', function (RouteCollector $r) {
                $r->get('', [ValidationRapportController::class, 'index']);
                $r->get('/{idRapport}/vote', [ValidationRapportController::class, 'showVoteInterface']);
                $r->post('/{idRapport}/vote', [ValidationRapportController::class, 'submitVote']);
                $r->post('/{idRapport}/new-round', [ValidationRapportController::class, 'newVoteRound']);
                $r->post('/{idRapport}/withdraw-from-session', [ValidationRapportController::class, 'withdrawFromSession']);
            });
        });
    });

    $r->addGroup('/api', function (RouteCollector $r) {
        $r->get('/notifications', [NotificationController::class, 'getNotifications']);
        $r->post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        $r->post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });
};