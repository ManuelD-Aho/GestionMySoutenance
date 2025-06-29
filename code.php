<?php

// Désactiver la limite de temps d'exécution pour les projets volumineux
set_time_limit(0);

// L'arborescence complète sous forme de chaîne de caractères (HEREDOC)
$structure = <<<EOT
└── manueld-aho-gestionmysoutenance/
    ├── README.md
    ├── Commande.txt
    ├── composer.json
    ├── composer.lock
    ├── docker-compose.dev.yml
    ├── docker-compose.prod.yml
    ├── Dockerfile
    ├── Fonction.md
    ├── mysoutenance.sql
    ├── php.ini
    ├── render.yaml
    ├── seeds.php
    ├── .dockerignore
    ├── .env.dev
    ├── .env.example
    ├── .env.prod
    ├── .gitignore.dev
    ├── .gitignore.prod
    ├── docker/
    │   ├── apache/
    │   │   └── apache-vhost.conf
    │   ├── nginx/
    │   │   └── conf.d/
    │   │       └── default.conf
    │   └── php/
    │       └── php.ini
    ├── Public/
    │   ├── index.php
    │   ├── test-email.php
    │   ├── .htaccess
    │   └── assets/
    │       ├── css/
    │       │   ├── admin-module.html
    │       │   ├── admin_module.css
    │       │   ├── admin_module.js
    │       │   ├── dashboard_style.css
    │       │   ├── gestionsoutenance-dashboard.css
    │       │   ├── gestionsoutenance-dashboard.js
    │       │   ├── promage-dashboard-base.css
    │       │   ├── promage-dashboard-index.html
    │       │   ├── style.css
    │       │   └── styles.css
    │       └── js/
    │           └── main.js
    ├── routes/
    │   └── web.php
    └── src/
        ├── Backend/
        │   ├── Controller/
        │   │   ├── AssetController.php
        │   │   ├── AuthentificationController.php
        │   │   ├── BaseController.php
        │   │   ├── DashboardController.php
        │   │   ├── HomeController.php
        │   │   ├── Admin/
        │   │   │   └── AnneeAcademiqueController.php
        │   │   ├── Administration/
        │   │   │   ├── AdminDashboardController.php
        │   │   │   ├── ConfigSystemeController.php
        │   │   │   ├── FichierController.php
        │   │   │   ├── GestionAcadController.php
        │   │   │   ├── HabilitationController.php
        │   │   │   ├── LoggerController.php
        │   │   │   ├── NotificationConfigurationController.php
        │   │   │   ├── QueueController.php
        │   │   │   ├── ReferentialController.php
        │   │   │   ├── ReportingController.php
        │   │   │   ├── SupervisionController.php
        │   │   │   ├── TransitionRoleController.php
        │   │   │   └── UtilisateurController.php
        │   │   ├── Commission/
        │   │   │   ├── CommissionDashboardController.php
        │   │   │   ├── CommunicationCommissionController.php
        │   │   │   ├── CorrectionCommissionController.php
        │   │   │   ├── PvController.php
        │   │   │   └── ValidationRapportController.php
        │   │   ├── Common/
        │   │   │   └── NotificationController.php
        │   │   ├── Etudiant/
        │   │   │   ├── DocumentEtudiantController.php
        │   │   │   ├── EtudiantDashboardController.php
        │   │   │   ├── ProfilEtudiantController.php
        │   │   │   ├── RapportController.php
        │   │   │   ├── ReclamationEtudiantController.php
        │   │   │   └── RessourcesEtudiantController.php
        │   │   └── PersonnelAdministratif/
        │   │       ├── CommunicationInterneController.php
        │   │       ├── ConformiteController.php
        │   │       ├── DocumentAdministratifController.php
        │   │       ├── PersonnelDashboardController.php
        │   │       └── ScolariteController.php
        │   ├── Exception/
        │   │   ├── AuthenticationException.php
        │   │   ├── CompteBloqueException.php
        │   │   ├── CompteNonValideException.php
        │   │   ├── DoublonException.php
        │   │   ├── ElementNonTrouveException.php
        │   │   ├── EmailException.php
        │   │   ├── EmailNonValideException.php
        │   │   ├── IdentifiantsInvalidesException.php
        │   │   ├── ModeleNonTrouveException.php
        │   │   ├── MotDePasseInvalideException.php
        │   │   ├── OperationImpossibleException.php
        │   │   ├── PermissionException.php
        │   │   ├── TokenExpireException.php
        │   │   ├── TokenInvalideException.php
        │   │   ├── UtilisateurNonTrouveException.php
        │   │   └── ValidationException.php
        │   ├── Model/
        │   │   ├── Acquerir.php
        │   │   ├── Action.php
        │   │   ├── Affecter.php
        │   │   ├── AnneeAcademique.php
        │   │   ├── Approuver.php
        │   │   ├── Attribuer.php
        │   │   ├── BaseModel.php
        │   │   ├── CompteRendu.php
        │   │   ├── ConformiteRapportDetails.php
        │   │   ├── Conversation.php
        │   │   ├── CritereConformiteRef.php
        │   │   ├── DecisionPassageRef.php
        │   │   ├── DecisionValidationPvRef.php
        │   │   ├── DecisionVoteRef.php
        │   │   ├── Delegation.php
        │   │   ├── DocumentGenere.php
        │   │   ├── Ecue.php
        │   │   ├── Enregistrer.php
        │   │   ├── Enseignant.php
        │   │   ├── Entreprise.php
        │   │   ├── Etudiant.php
        │   │   ├── Evaluer.php
        │   │   ├── FaireStage.php
        │   │   ├── Fonction.php
        │   │   ├── Grade.php
        │   │   ├── GroupeUtilisateur.php
        │   │   ├── HistoriqueMotDePasse.php
        │   │   ├── Inscrire.php
        │   │   ├── LectureMessage.php
        │   │   ├── MatriceNotificationRegles.php
        │   │   ├── MessageChat.php
        │   │   ├── NiveauAccesDonne.php
        │   │   ├── NiveauEtude.php
        │   │   ├── Notification.php
        │   │   ├── Occuper.php
        │   │   ├── ParametreSysteme.php
        │   │   ├── ParticipantConversation.php
        │   │   ├── Penalite.php
        │   │   ├── PersonnelAdministratif.php
        │   │   ├── Pister.php
        │   │   ├── PvSessionRapport.php
        │   │   ├── QueueJobs.php
        │   │   ├── RapportEtudiant.php
        │   │   ├── RapportModele.php
        │   │   ├── RapportModeleAssignation.php
        │   │   ├── RapportModeleSection.php
        │   │   ├── Rattacher.php
        │   │   ├── Recevoir.php
        │   │   ├── Reclamation.php
        │   │   ├── Rendre.php
        │   │   ├── SectionRapport.php
        │   │   ├── Sequences.php
        │   │   ├── Sessions.php
        │   │   ├── SessionValidation.php
        │   │   ├── Specialite.php
        │   │   ├── StatutConformiteRef.php
        │   │   ├── StatutJury.php
        │   │   ├── StatutPaiementRef.php
        │   │   ├── StatutPenaliteRef.php
        │   │   ├── StatutPvRef.php
        │   │   ├── StatutRapportRef.php
        │   │   ├── StatutReclamationRef.php
        │   │   ├── Traitement.php
        │   │   ├── TypeDocumentRef.php
        │   │   ├── TypeUtilisateur.php
        │   │   ├── Ue.php
        │   │   ├── Utilisateur.php
        │   │   ├── ValidationPv.php
        │   │   └── VoteCommission.php
        │   ├── Service/
        │   │   ├── Interface/
        │   │   │   ├── AnneeAcademiqueServiceInterface.php
        │   │   │   ├── AuthenticationServiceInterface.php
        │   │   │   ├── AuditServiceInterface.php
        │   │   │   ├── CommissionServiceInterface.php
        │   │   │   ├── CompteUtilisateurServiceInterface.php
        │   │   │   ├── ConformiteServiceInterface.php
        │   │   │   ├── CursusServiceInterface.php
        │   │   │   ├── DocumentAdministratifServiceInterface.php
        │   │   │   ├── DocumentGeneratorServiceInterface.php
        │   │   │   ├── EmailServiceInterface.php
        │   │   │   ├── FichierServiceInterface.php
        │   │   │   ├── IdentifiantGeneratorInterface.php
        │   │   │   ├── InscriptionServiceInterface.php
        │   │   │   ├── LoggerServiceInterface.php
        │   │   │   ├── MessagerieServiceInterface.php
        │   │   │   ├── NotationServiceInterface.php
        │   │   │   ├── NotificationConfigurationServiceInterface.php
        │   │   │   ├── NotificationServiceInterface.php
        │   │   │   ├── ParametrageServiceInterface.php
        │   │   │   ├── PenaliteServiceInterface.php
        │   │   │   ├── PermissionsServiceInterface.php
        │   │   │   ├── PersonnelAcademiqueServiceInterface.php
        │   │   │   ├── ProcèsVerbalServiceInterface.php
        │   │   │   ├── ProfilEtudiantServiceInterface.php
        │   │   │   ├── QueueServiceInterface.php
        │   │   │   ├── RapportServiceInterface.php
        │   │   │   ├── ReclamationServiceInterface.php
        │   │   │   ├── ReferentielServiceInterface.php
        │   │   │   ├── ReportingServiceInterface.php
        │   │   │   ├── RessourcesEtudiantServiceInterface.php
        │   │   │   ├── StageServiceInterface.php
        │   │   │   ├── SupervisionAdminServiceInterface.php
        │   │   │   └── TransitionRoleServiceInterface.php
        │   │   ├── IdentifiantGenerator.php
        │   │   ├── ServiceAnneeAcademique.php
        │   │   ├── ServiceAuthentication.php
        │   │   ├── ServiceAudit.php
        │   │   ├── ServiceCommission.php
        │   │   ├── ServiceCompteUtilisateur.php
        │   │   ├── ServiceConformite.php
        │   │   ├── ServiceCursus.php
        │   │   ├── ServiceDocumentAdministratif.php
        │   │   ├── ServiceDocumentGenerator.php
        │   │   ├── ServiceEmail.php
        │   │   ├── ServiceFichier.php
        │   │   ├── ServiceInscription.php
        │   │   ├── ServiceLogger.php
        │   │   ├── ServiceMessagerie.php
        │   │   ├── ServiceNotation.php
        │   │   ├── ServiceNotification.php
        │   │   ├── ServiceNotificationConfiguration.php
        │   │   ├── ServiceParametrage.php
        │   │   ├── ServicePenalite.php
        │   │   ├── ServicePermissions.php
        │   │   ├── ServicePersonnelAcademique.php
        │   │   ├── ServiceProcesVerbal.php
        │   │   ├── ServiceProfilEtudiant.php
        │   │   ├── ServiceQueue.php
        │   │   ├── ServiceRapport.php
        │   │   ├── ServiceReclamation.php
        │   │   ├── ServiceReferentiel.php
        │   │   ├── ServiceReportingAdmin.php
        │   │   ├── ServiceRessourcesEtudiant.php
        │   │   ├── ServiceStage.php
        │   │   ├── ServiceSupervisionAdmin.php
        │   │   └── ServiceTransitionRole.php
        │   └── Util/
        │       ├── DatabaseSessionHandler.php
        │       └── FormValidator.php
        ├── Config/
        │   ├── Container.php
        │   └── Database.php
        └── Frontend/
            └── views/
                ├── Administration/
                │   ├── dashboard_admin.php
                │   ├── reporting_admin.php
                │   ├── ConfigSysteme/
                │   │   ├── annee_academique.php
                │   │   ├── modeles_documents.php
                │   │   ├── notification_configuration.php
                │   │   └── parametres_generaux.php
                │   ├── Fichier/
                │   │   ├── list_files.php
                │   │   └── upload_form.php
                │   ├── GestionAcad/
                │   │   ├── form_ecue.php
                │   │   ├── form_inscription.php
                │   │   ├── form_note.php
                │   │   ├── form_stage.php
                │   │   ├── form_ue.php
                │   │   ├── index.php
                │   │   ├── list_ecues.php
                │   │   ├── list_ues.php
                │   │   ├── liste_inscriptions.php
                │   │   ├── liste_notes.php
                │   │   ├── liste_stages.php
                │   │   └── manage_enseignant_carrieres.php
                │   ├── Habilitations/
                │   │   ├── form_groupe.php
                │   │   ├── form_niveau_acces.php
                │   │   ├── form_traitement.php
                │   │   ├── form_type_utilisateur.php
                │   │   ├── gestion_rattachements.php
                │   │   ├── index.php
                │   │   ├── liste_groupes.php
                │   │   ├── liste_niveaux_acces.php
                │   │   ├── liste_traitements.php
                │   │   └── liste_types_utilisateur.php
                │   ├── Referentiels/
                │   │   ├── crud_referentiel_generique.php
                │   │   ├── form_referentiel_generique.php
                │   │   └── liste_referentiels.php
                │   ├── Supervision/
                │   │   ├── index.php
                │   │   ├── journaux_audit.php
                │   │   ├── logs.php
                │   │   ├── maintenance.php
                │   │   ├── queue.php
                │   │   └── suivi_workflows.php
                │   ├── TransitionRole/
                │   │   ├── form_delegation.php
                │   │   ├── index.php
                │   │   └── list_delegations.php
                │   └── Utilisateurs/
                │       ├── form_enseignant.php
                │       ├── form_etudiant.php
                │       ├── form_personnel.php
                │       ├── form_utilisateur_generic.php
                │       ├── import_etudiants_form.php
                │       ├── liste_enseignants.php
                │       ├── liste_etudiants.php
                │       ├── liste_personnel.php
                │       └── liste_utilisateurs.php
                ├── Auth/
                │   ├── change_password_form.php
                │   ├── email_validation_result.php
                │   ├── forgot_password_form.php
                │   ├── form_2fa.php
                │   ├── form_2fa_setup.php
                │   ├── layout_auth.php
                │   ├── login.php
                │   └── reset_password_form.php
                ├── Commission/
                │   ├── corrections_commission.php
                │   ├── dashboard_commission.php
                │   ├── historique_commission.php
                │   ├── Communication/
                │   │   └── create_conversation_form.php
                │   ├── PV/
                │   │   ├── consulter_pv.php
                │   │   ├── rediger_pv.php
                │   │   └── valider_pv.php
                │   └── Rapports/
                │       ├── details_rapport_commission.php
                │       ├── interface_vote.php
                │       └── liste_rapports_a_traiter.php
                ├── common/
                │   ├── chat_interface.php
                │   ├── dashboard.php
                │   ├── header.php
                │   ├── menu.php
                │   └── notifications_panel.php
                ├── errors/
                │   ├── 403.php
                │   ├── 404.php
                │   ├── 405.php
                │   └── 500.php
                ├── Etudiant/
                │   ├── dashboard_etudiant.php
                │   ├── mes_documents.php
                │   ├── profil_etudiant.php
                │   ├── ressources_etudiant.php
                │   ├── Profile/
                │   │   └── upload_photo_form.php
                │   ├── Rapport/
                │   │   ├── soumettre_corrections.php
                │   │   ├── soumettre_rapport.php
                │   │   └── suivi_rapport.php
                │   └── Reclamation/
                │       ├── soumettre_reclamation.php
                │       └── suivi_reclamations.php
                ├── layout/
                │   └── app.php
                ├── ParametresGeneraux/
                │   ├── action.php
                │   ├── annee_academique.php
                │   ├── ecue.php
                │   ├── entreprise.php
                │   ├── fonction.php
                │   ├── grade.php
                │   ├── groupe_utilisateur.php
                │   ├── niv_acces_donnees.php
                │   ├── niveau_approbation.php
                │   ├── niveau_etude.php
                │   ├── specialite.php
                │   ├── statut_jury.php
                │   ├── traitement.php
                │   ├── type_utilisateur.php
                │   ├── ue.php
                │   └── utilisateur.php
                └── PersonnelAdministratif/
                    ├── dashboard_personnel.php
                    ├── Conformite/
                    │   ├── details_rapport_conformite.php
                    │   ├── liste_rapports_a_verifier.php
                    │   └── liste_rapports_traites_conformite.php
                    ├── DocumentAdministratif/
                    │   ├── generation_documents_form.php
                    │   └── list_generated_documents.php
                    └── Scolarite/
                        ├── generation_documents_scolarite.php
                        ├── gestion_etudiants_scolarite.php
                        ├── gestion_inscriptions_scolarite.php
                        ├── gestion_notes_scolarite.php
                        ├── index.php
                        ├── liste_reclamations.php
                        ├── manage_penalites.php
                        └── validate_stage_form.php
EOT;


function generateStructure(string $structure) {
    $lines = explode("\n", $structure);
    $baseDir = '';

    // Récupérer le nom du dossier racine de la première ligne
    if (preg_match('/└── (.*?)\//', $lines[0], $matches)) {
        $baseDir = trim($matches[1]);
        if (file_exists($baseDir)) {
            echo "Le dossier racine '$baseDir' existe déjà. Le script ne modifiera pas son contenu.\n";
        } else {
            mkdir($baseDir, 0777, true);
            echo "Créé le dossier racine : $baseDir\n";
        }
    } else {
        echo "Erreur : Impossible de déterminer le dossier racine.\n";
        return;
    }

    // La pile des chemins contiendra les composants du chemin actuel
    $pathStack = [$baseDir];

    // Commencer à partir de la deuxième ligne
    for ($i = 1; $i < count($lines); $i++) {
        $line = $lines[$i];
        if (trim($line) === '') continue;

        // Déterminer la profondeur en se basant sur l'indentation
        preg_match('/^([\s│├└─]*)/', $line, $prefixMatch);
        // Chaque niveau d'indentation est de 4 caractères (ex: "│   " ou "    ")
        $depth = mb_strlen($prefixMatch[0], 'UTF-8') / 4;

        // Nettoyer le nom du fichier/dossier
        $name = preg_replace('/^[\s│├└─]+/', '', $line);
        $name = trim($name);

        // Déterminer si c'est un dossier ou un fichier
        $isDir = substr($name, -1) === '/';
        $itemName = $isDir ? rtrim($name, '/') : $name;

        // --- LOGIQUE CORRIGÉE ---
        // Ajuster la pile pour qu'elle corresponde au parent de l'élément actuel
        // La profondeur (depth) est relative au dossier racine.
        // $pathStack[0] est le dossier racine, donc la taille de la pile doit être $depth + 1
        $pathStack = array_slice($pathStack, 0, $depth + 1);

        // Construire le chemin complet
        $parentPath = implode(DIRECTORY_SEPARATOR, $pathStack);
        $fullPath = $parentPath . DIRECTORY_SEPARATOR . $itemName;

        if ($isDir) {
            // C'est un dossier
            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0777, true)) {
                    echo "Créé dossier : $fullPath\n";
                } else {
                    echo "ERREUR dossier : $fullPath\n";
                }
            }
            // Ajouter le dossier courant à la pile pour ses enfants
            $pathStack[] = $itemName;
        } else {
            // C'est un fichier
            if (!file_exists($fullPath)) {
                // S'assurer que le dossier parent existe avant de créer le fichier
                if (!is_dir(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0777, true);
                }
                if (touch($fullPath)) {
                    echo "Créé fichier  : $fullPath\n";
                } else {
                    echo "ERREUR fichier  : $fullPath\n";
                }
            }
        }
    }
    echo "\nArborescence générée avec succès dans le dossier '$baseDir'!\n";
}

generateStructure($structure);