# Table des matières {#table-des-matières .TOC-Heading}

[**Service: ServiceAuthentification.php**
[1](#_Toc199281842)](#_Toc199281842)

[**Service: ServiceCommission.php** [4](#_Toc199281843)](#_Toc199281843)

[**Service: ServiceConfigurationSysteme.php**
[7](#_Toc199281844)](#_Toc199281844)

[**Service: ServiceConformite.php**
[10](#_Toc199281845)](#_Toc199281845)

[**Service: ServiceGestionAcademique.php**
[12](#_Toc199281846)](#_Toc199281846)

[**Service: ServiceMessagerie.php**
[15](#_Toc199281847)](#_Toc199281847)

[**Service: ServiceNotification.php**
[17](#_Toc199281848)](#_Toc199281848)

[**Service: ServicePermissions.php**
[20](#_Toc199281849)](#_Toc199281849)

[**Service: ServiceRapport.php** [22](#_Toc199281850)](#_Toc199281850)

[**Service: ServiceReportingAdmin.php**
[25](#_Toc199281851)](#_Toc199281851)

[**Service: ServiceSupervisionAdmin.php**
[27](#_Toc199281852)](#_Toc199281852)

[**Service: ServiceEmail.php** [29](#_Toc199281853)](#_Toc199281853)

[**Service: ServiceReclamation.php**
[31](#_Toc199281854)](#_Toc199281854)

[**Service: ServiceDocumentGenerator.php**
[33](#_Toc199281855)](#_Toc199281855)

[]{#_Toc199281842 .anchor}**Service: ServiceAuthentification.php**

Objectif: Gérer l\'authentification des utilisateurs, la création et la gestion de leurs comptes et profils spécifiques (étudiant, enseignant, personnel administratif), ainsi que la gestion des sessions et la sécurité associée.

Fonctionnalités:

-   **Gestion des Connexions:**

    -   Traiter une tentative de connexion utilisateur (login/mot de passe).

    -   Valider les identifiants contre la base de données (après hachage sécurisé du mot de passe fourni).

    -   Vérifier le statut du compte utilisateur (actif, bloqué, en attente de validation).

    -   Gérer le compteur de tentatives de connexion échouées.

    -   Bloquer temporairement un compte après un nombre défini de tentatives échouées.

    -   Débloquer automatiquement un compte après expiration du délai de blocage.

    -   Mettre en œuvre (si configuré) et vérifier un code d\'authentification à deux facteurs (2FA) (ex: TOTP, SMS).

    -   Permettre à l\'utilisateur de configurer/réinitialiser ses options 2FA.

-   **Gestion des Sessions:**

    -   Établir une session sécurisée pour un utilisateur après une connexion réussie.

    -   Stocker les informations minimales nécessaires en session (ex: numero_utilisateur, rôles/permissions de base).

    -   Régénérer l\'ID de session après la connexion pour prévenir la fixation de session.

    -   Détruire la session d\'un utilisateur lors d\'une déconnexion explicite.

    -   Gérer l\'expiration automatique des sessions après une période d\'inactivité définie.

    -   Vérifier si une session utilisateur est valide et active.

-   **Gestion des Comptes et Profils (CRUD et opérations associées):**

    -   **Création de Compte:**

        -   Générer un numero_utilisateur unique et non séquentiel.

        -   Créer un enregistrement de base dans la table utilisateur (login, mot de passe haché initial, sel, date création, statut, type, groupe, niveau d\'accès).

        -   Créer le profil spécifique associé (étudiant, enseignant, personnel administratif) avec toutes les informations personnelles requises.

        -   Assurer la liaison transactionnelle entre la création de l\'utilisateur et son profil.

        -   Envoyer un courriel de bienvenue avec les identifiants initiaux et/ou un lien de validation/configuration (via ServiceEmail).

        -   Permettre la création de comptes en masse depuis une source externe (ex: import CSV pour les étudiants, via un script d\'administration utilisant ce service).

    -   **Lecture/Récupération d\'Informations:**

        -   Récupérer les informations complètes (compte + profil) de l\'utilisateur actuellement connecté.

        -   Récupérer les informations complètes (compte + profil) d\'un utilisateur spécifique par son numero_utilisateur (pour les administrateurs).

        -   Lister tous les utilisateurs (ou par type: étudiants, enseignants, personnel) avec options de pagination, filtrage (par statut, groupe, etc.) et recherche (par nom, login, email).

    -   **Mise à Jour de Compte/Profil:**

        -   Permettre à un utilisateur de modifier son propre mot de passe (après vérification de l\'ancien mot de passe ou via un token sécurisé).

        -   Permettre à un utilisateur de mettre à jour ses informations de contact modifiables et sa photo de profil.

        -   Permettre aux administrateurs de réinitialiser le mot de passe d\'un utilisateur (génération d\'un nouveau mot de passe temporaire ou envoi d\'un lien de réinitialisation).

        -   Permettre aux administrateurs de modifier toutes les informations d\'un compte utilisateur (login, statut, type, groupe, niveau d\'accès) et de son profil (informations personnelles, photo).

    -   **Gestion du Cycle de Vie du Compte:**

        -   Désactiver un compte utilisateur (le marquer comme \'inactif\', empêchant la connexion mais conservant les données).

        -   Réactiver un compte utilisateur désactivé.

        -   Archiver un profil utilisateur (marquer comme archivé, souvent lié à la désactivation, pour conservation à long terme sans être dans les listes actives).

        -   Supprimer un profil utilisateur et son compte associé (opération destructive, conditionnée à des règles strictes d\'intégrité référentielle et de politique de conservation des données, potentiellement après une période de \"soft delete\" ou d\'archivage).

-   **Sécurité des Mots de Passe:**

    -   Hacher les mots de passe avec un algorithme robuste et un sel unique par utilisateur (ex: Argon2id, bcrypt).

    -   Vérifier la robustesse des mots de passe lors de la création ou de la modification (longueur minimale, complexité si requise par la politique).

    -   Gérer la procédure de réinitialisation de mot de passe oublié :

        -   Vérifier l\'existence de l\'email/login fourni.

        -   Générer un token de réinitialisation unique, sécurisé et à durée de vie limitée.

        -   Stocker le token haché et sa date d\'expiration dans la table utilisateur.

        -   Envoyer un email à l\'utilisateur avec un lien contenant le token (via ServiceEmail).

        -   Vérifier la validité et l\'expiration du token lors de son utilisation.

        -   Permettre la saisie et la mise à jour du nouveau mot de passe après validation du token.

        -   Invalider le token après son utilisation ou son expiration.

-   **Intégrations et Vérifications Externes:**

    -   Interagir avec ServiceGestionAcademique (ou une source de données externe) pour vérifier le statut de la scolarité d\'un étudiant avant la création de son compte.

-   **Audit et Journalisation:**

    -   Enregistrer les événements d\'authentification (connexions réussies, échouées), les créations de compte, les modifications de mot de passe, les changements de statut de compte (via ServiceSupervisionAdmin ou directement dans la table enregistrer).

*Modèles Utilisés et Mises à Jour Potentielles:* \* Utilisateur: numero_utilisateur (PK), login_utilisateur (UNIQUE), mot_de_passe (VARCHAR 255), sel_hash (VARCHAR, si utilisation de sel individuel explicite), date_creation, photo_profil (VARCHAR, chemin/URL), actif (TINYINT/BOOLEAN, avec des statuts plus fins comme statut_compte ENUM(\'actif\', \'inactif\', \'bloque\', \'en_attente_validation_email\', \'archive\')), id_niveau_acces_donne (FK), id_groupe_utilisateur (FK), id_type_utilisateur (FK), derniere_connexion (DATETIME), token_reset_mdp (VARCHAR, haché), date_expiration_token_reset (DATETIME), token_validation_email (VARCHAR, haché), date_expiration_validation_email (DATETIME), email_valide (BOOLEAN), tentatives_connexion_echouees (INT), compte_bloque_jusqua (DATETIME), date_archivage (DATETIME, nullable), preferences_2fa_active (BOOLEAN), secret_2fa (VARCHAR, chiffré).
\* Etudiant, Enseignant, PersonnelAdministratif: Tous les champs nécessaires pour les informations personnelles et spécifiques au profil. Liaison 1-to-1 avec Utilisateur via numero_utilisateur.
\* TypeUtilisateur: id_type_utilisateur (PK), code_type_utilisateur (UNIQUE, ex: \'ETU\', \'ENS\', \'ADM\'), lib_type_utilisateur.
\* GroupeUtilisateur: id_groupe_utilisateur (PK), code_groupe_utilisateur (UNIQUE), lib_groupe_utilisateur.
\* NiveauAccesDonne: id_niveau_acces_donne (PK), code_niveau_acces (UNIQUE), lib_niveau_acces_donne.
\* HistoriqueMotDePasse (Nouvelle table potentielle): id_historique_mdp (PK), numero_utilisateur (FK), mot_de_passe_hache (VARCHAR), date_changement (DATETIME) - pour empêcher la réutilisation des N derniers mots de passe.

Je vais continuer avec les autres services en gardant ce niveau de détail. Cela prendra un peu de temps pour chaque service. Souhaitez-vous que je procède service par service de cette manière, ou préférez-vous que je me concentre sur des aspects spécifiques que vous jugez moins couverts ?

[]{#_Toc199281843 .anchor}**Service: ServiceCommission.php**

Objectif: Gérer l\'ensemble du processus de validation des rapports (thème inclus) par la commission pédagogique, incluant l\'assignation des rapports, la gestion des votes et délibérations, la planification des sessions, la confirmation de l\'encadrement, la rédaction et la validation des Procès-Verbaux (PV), ainsi que la gestion des corrections post-commission.

Fonctionnalités:

-   **Gestion des Rapports à Traiter:**

    -   Récupérer et lister les rapports de stage ayant le statut \"Transmis à la commission\" (provenant de ServiceConformite).

    -   Afficher les détails d\'un rapport pour examen (informations de l\'étudiant, titre, thème, résumé, documents soumis, historique des statuts précédents).

    -   Permettre l\'assignation (optionnelle) de rapports spécifiques à des membres de la commission (rapporteurs) pour un examen approfondi et une proposition initiale.

-   **Processus de Vote et Délibération en Ligne:**

    -   Initialiser un tour de vote pour un rapport spécifique, en définissant les membres votants.

    -   Permettre à chaque membre assigné de soumettre son vote électronique (\"Approuver le rapport\", \"Refuser le rapport\", \"Demander corrections\", \"Demander discussion complémentaire\").

    -   Exiger/permettre un commentaire justificatif pour chaque vote, surtout pour les votes non approbateurs.

    -   Enregistrer de manière sécurisée et horodatée chaque vote individuel.

    -   Gérer la visibilité des votes en cours (configurable: votes visibles par tous immédiatement, ou seulement après la clôture du tour de vote).

    -   Calculer le résultat d\'un tour de vote (unanimité, majorité, divergence).

    -   En cas de divergence ou de demande de discussion, notifier les membres et faciliter la concertation (potentiellement via ServiceMessagerie pour créer un canal de discussion dédié au rapport).

    -   Permettre l\'initiation de tours de vote successifs après une phase de concertation (nombre de tours maximum configurable via ServiceConfigurationSysteme).

    -   Clôturer le processus de vote pour un rapport et enregistrer la décision finale de la commission (Validé, Refusé, Corrections demandées).

    -   Mettre à jour le statut du rapport (via ServiceRapport) en fonction de la décision finale.

-   **Gestion des Sessions de Validation en Présentiel:**

    -   Permettre la création et la planification d\'une session de commission (date, heure, lieu, ordre du jour, membres convoqués).

    -   Associer une liste de rapports/étudiants à une session planifiée.

    -   Enregistrer la présence des membres à la session.

    -   Permettre la saisie des décisions et recommandations pour chaque rapport discuté pendant la session.

-   **Formalisation de l\'Encadrement:**

    -   Permettre à la commission de confirmer ou de désigner officiellement le Directeur de mémoire et l\'Encadreur pédagogique pour un rapport, une fois le thème/rapport validé ou en voie de l\'être.

    -   Enregistrer ces affectations dans la table affecter (ou une table dédiée si la sémantique est différente de l\'affectation initiale pour évaluation).

-   **Rédaction et Gestion des Procès-Verbaux (PV):**

    -   **Création de PV:**

        -   Générer un nouveau PV individuel pour un rapport (suite à une validation en ligne ou une décision en session).

        -   Générer un nouveau PV de session regroupant les décisions pour plusieurs rapports traités en présentiel.

        -   Pré-remplir automatiquement le PV avec les informations disponibles (étudiant(s), rapport(s), membres de la commission, date, décision(s) enregistrée(s), recommandations initiales).

    -   **Édition de PV:**

        -   Fournir un éditeur de texte riche pour la saisie et la mise en forme du contenu détaillé du PV (délibérations, justifications, recommandations finales).

        -   Permettre la sauvegarde des brouillons de PV en cours de rédaction.

    -   **Soumission et Statut du PV:**

        -   Permettre au rédacteur (Président, secrétaire) de soumettre le PV finalisé à la validation des autres membres.

        -   Mettre à jour le statut du PV (\"Brouillon\", \"Soumis pour validation\", \"En cours de validation\", \"Validé\", \"Rejeté pour modification\", \"Archivé\").

-   **Validation des Procès-Verbaux:**

    -   Notifier les membres concernés qu\'un PV est en attente de leur validation.

    -   Permettre aux membres de consulter le PV soumis.

    -   Gérer un processus d\'approbation formelle du PV:

        -   Option 1: Vote individuel des membres sur le contenu du PV (\"Approuver le PV\", \"Demander modification du PV avec commentaires\").

        -   Option 2: Validation directe par une autorité désignée (ex: Président de la commission) après consultation.

    -   Enregistrer la décision de validation (ou la demande de modification) pour chaque membre/validateur.

    -   Considérer le PV comme officiellement validé lorsque les conditions d\'approbation sont remplies (unanimité, majorité qualifiée, ou validation par l\'autorité).

    -   Une fois le PV validé, mettre à jour le statut du ou des rapport(s) concerné(s) de manière définitive (ex: \"Officiellement Validé\", \"Corrections Officiellement Demandées\").

-   **Gestion des Corrections Post-Commission:**

    -   Recevoir la notification de soumission de corrections par un étudiant (via ServiceRapport).

    -   Permettre aux membres de la commission (ou à un sous-groupe désigné) d\'accéder à la version corrigée du rapport et aux notes explicatives de l\'étudiant.

    -   Gérer un processus de décision (vote simplifié ou discussion) pour statuer sur l\'acceptation des corrections.

    -   Mettre à jour le statut final du rapport après l\'examen des corrections (ex: passer de \"Corrections Demandées\" à \"Validé après corrections\").

    -   Potentiellement, générer un addendum au PV initial ou un nouveau PV simplifié pour acter la validation des corrections.

-   **Suivi et Communication:**

    -   Afficher un tableau de bord pour chaque membre avec ses tâches en attente (rapports à examiner, votes en attente, PV à rédiger/valider).

    -   Permettre la consultation de l\'historique des activités d\'un membre (votes émis, PV validés, commentaires).

    -   Permettre l\'accès aux PV archivés le concernant.

    -   Notifier l\'étudiant de la décision finale de la commission et des recommandations détaillées, une fois le PV correspondant validé (via ServiceNotification et ServiceEmail).

    -   Notifier le service de scolarité ou l\'administration de la validation d\'un PV pour archivage officiel et mise à jour du dossier étudiant (via ServiceNotification).

*Modèles Utilisés et Mises à Jour Potentielles:* \* RapportEtudiant: Ajouter directeur_memoire_confirme_par_commission (FK Enseignant), encadreur_pedagogique_confirme_par_commission (FK Enseignant), date_decision_commission (DATETIME), decision_commission_finale (FK vers une table de référence des décisions), recommandations_commission (TEXT).
\* Affecter: S\'assurer que id_statut_jury couvre tous les rôles (membre votant, rapporteur, président, etc.). Le champ directeur_memoire (TINYINT) est probablement mieux géré par un id_statut_jury spécifique ou dans la table RapportEtudiant pour la confirmation finale.
\* VoteCommission: tour_vote (INT), id_decision_vote (FK vers DecisionVoteRef), date_vote (DATETIME).
\* DecisionVoteRef: id_decision_vote (PK), libelle_decision (VARCHAR, ex: \"Approuvé\", \"Refusé\", \"Corrections demandées\", \"Discussion complémentaire\").
\* CompteRendu (PV): id_session_commission (FK vers SessionCommission, nullable si PV individuel), type_pv (ENUM: \'individuel_rapport\', \'session_commission\'), numero_redacteur (FK Utilisateur), date_soumission_validation (DATETIME), date_validation_finale_pv (DATETIME), numero_validateur_principal (FK Utilisateur, si validation par Président).
\* ValidationPv: id_validation_pv (PK), id_compte_rendu (FK), numero_validateur (FK Utilisateur), id_decision_validation_pv (FK vers DecisionValidationPvRef), commentaire_validation (TEXT), date_action_validation (DATETIME).
\* DecisionValidationPvRef: id_decision_validation_pv (PK), libelle_decision (VARCHAR, ex: \"PV Approuvé\", \"PV Demande Modification\").
\* PvSessionRapport (Table de liaison N-N): id_compte_rendu (FK vers CompteRendu de type \'session\'), id_rapport_etudiant (FK).
\* SessionCommission (Nouvelle table): id_session_commission (PK), date_session (DATETIME), lieu_session (VARCHAR), ordre_du_jour_session (TEXT), president_session_numero (FK Utilisateur), statut_session (ENUM: \'Planifiée\', \'Tenue\', \'Annulée\').
\* StatutRapportRef, StatutPvRef.
\* Utilisateur (pour identifier les membres, rédacteurs, validateurs).

[]{#_Toc199281844 .anchor}**Service: ServiceConfigurationSysteme.php**

Objectif: Gérer la configuration globale et fondamentale du système, incluant la gestion exhaustive de tous les référentiels de base, la définition des paramètres applicatifs critiques et des règles de workflow, ainsi que l\'administration des modèles de documents et de communication.

Fonctionnalités:

-   **Gestion Complète des Référentiels (CRUD exhaustif pour chaque):**

    -   Pour specialite: Créer, lire (lister, rechercher, afficher détails), mettre à jour, supprimer des spécialités d\'enseignants.

    -   Pour fonction: Créer, lire, mettre à jour, supprimer des fonctions (postes) pour enseignants ou personnel.

    -   Pour grade: Créer, lire, mettre à jour, supprimer des grades académiques.

    -   Pour ue (Unités d\'Enseignement): Créer, lire, mettre à jour, supprimer des UE.

    -   Pour ecue (Éléments Constitutifs d\'UE): Créer, lire, mettre à jour, supprimer des ECUE, les lier à des UE.

    -   Pour annee_academique: Créer (libellé, dates début/fin), lire, mettre à jour, supprimer des années académiques. Définir une année académique comme \"active\".

    -   Pour niveau_etude: Créer, lire, mettre à jour, supprimer des niveaux d\'étude (ex: Master 1, Master 2).

    -   Pour entreprise: Créer, lire, mettre à jour, supprimer des fiches d\'entreprises (pour les stages).

    -   Pour niveau_approbation: Créer, lire, mettre à jour, supprimer des étapes ou niveaux d\'un workflow de validation (usage générique).

    -   Pour statut_jury: Créer, lire, mettre à jour, supprimer des rôles possibles au sein d\'un jury ou d\'une commission (ex: Président, Rapporteur, Membre).

    -   Pour action (audit): Créer, lire, mettre à jour, supprimer des types d\'actions système enregistrables pour l\'audit (ex: \'CONNEXION_UTILISATEUR\', \'CREATION_RAPPORT\').

    -   Pour traitement (permissions): Créer (libellé, code unique, description), lire, mettre à jour, supprimer des fonctionnalités/traitements du système qui peuvent être soumis à des droits d\'accès.

    -   Pour message (modèles de communication): Créer (code unique, sujet, corps HTML, corps texte, canal), lire, mettre à jour, supprimer des modèles de messages pour emails et notifications système. Associer à des événements.

    -   Pour notification (types de notifications système): Créer (code unique, libellé, description), lire, mettre à jour, supprimer des types/catégories de notifications. Associer un type de notification à un modèle de message.

    -   *Contrôles d\'intégrité référentielle lors de la suppression pour tous les référentiels.*

-   **Gestion des Paramètres Applicatifs et de Workflow (via table ParametreApplication):**

    -   Définir et mettre à jour des paramètres globaux (clé-valeur avec typage et description) tels que :

        -   Dates limites générales (ex: DATE_LIMITE_SOUMISSION_RAPPORT_S1_AAAA, DELAI_MAX_CORRECTION_JOURS).

        -   Règles de validation pour la conformité (ex: CONFORMITE_DOCS_REQUIS_LISTE, CONFORMITE_TAILLE_MAX_FICHIER_MO, CONFORMITE_FORMATS_AUTORISES).

        -   Paramètres des alertes système (ex: ALERTE_DOSSIER_EN_ATTENTE_JOURS, EMAIL_ADMIN_POUR_ALERTES_CRITIQUES).

        -   Paramètres du vote en ligne de la commission (ex: VOTE_COMMISSION_NB_TOURS_MAX_AVANT_ESCALADE, VOTE_COMMISSION_TYPE_MAJORITE_REQUISE).

        -   Options du chat intégré (ex: CHAT_CREATION_GROUPE_COMMISSION_AUTO (booléen), CHAT_RETENTION_MESSAGES_JOURS).

        -   Seuils pour politiques de sécurité (ex: AUTH_MAX_TENTATIVES_ECHOUEES_AVANT_BLOCAGE, AUTH_DUREE_BLOCAGE_COMPTE_MINUTES).

        -   Configuration de l\'expéditeur email par défaut (MAILER_FROM_EMAIL, MAILER_FROM_NAME).

-   **Gestion des Modèles de Documents PDF:**

    -   Permettre le téléversement de fichiers modèles (HTML, CSS, potentiellement des images associées) pour la génération de documents PDF.

    -   Prévisualiser l\'apparence d\'un modèle (potentiellement avec des données exemples).

    -   Associer un nom ou un code unique à chaque modèle.

    -   Lier un modèle à un type de document officiel spécifique (ex: \'ATTESTATION_DEPOT\', \'PV_VALIDATION_INDIVIDUEL\').

    -   Gérer les versions des modèles de documents.

-   **Gestion Avancée des Notifications et Workflows (si fonctionnalités poussées):**

    -   Interface pour définir des déclencheurs d\'événements dans l\'application.

    -   Pour chaque déclencheur, configurer les actions à entreprendre (ex: envoyer une notification, changer un statut).

    -   Choisir le canal de notification (système, email).

    -   Sélectionner le modèle de message à utiliser.

    -   Définir les destinataires (rôles spécifiques, utilisateur initiateur, etc.).

    -   Configurer les étapes, transitions, acteurs et conditions pour des workflows plus complexes que les simples changements de statut.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Les 14 modèles de référentiels listés ci-dessus, chacun avec des champs pertinents (ex: id\_\... (PK), code\_\... (UNIQUE si applicable), lib\_\..., description\_\..., dates de création/modification, etc.).
\* Message: id_message (PK), code_message (UNIQUE), nom_template (VARCHAR, pour identification facile), evenement_declencheur (VARCHAR, optionnel), sujet_template (VARCHAR), corps_html_template (TEXT), corps_texte_template (TEXT), canal_notification (ENUM: \'email\', \'systeme\', \'les_deux\'), est_actif (BOOLEAN).
\* Notification (types de notifications): id_notification (PK), code_notification (UNIQUE), lib_notification (VARCHAR), description_notification (TEXT), id_message_template_associe (FK vers Message.id_message, nullable), priorite (INT, optionnel).
\* ParametreApplication (Nouvelle table): id_parametre (PK), cle_parametre (VARCHAR, UNIQUE), valeur_parametre (TEXT), type_valeur (ENUM: \'string\', \'int\', \'boolean\', \'date\', \'json\'), description_parametre (TEXT), groupe_parametre (VARCHAR, pour organisation UI), date_derniere_modification.
\* ModeleDocumentPdf (Nouvelle table): id_modele_pdf (PK), code_modele (VARCHAR, UNIQUE), nom_modele (VARCHAR), type_document_officiel_associe (VARCHAR, ex: \'ATTESTATION_DEPOT\'), chemin_template_html (VARCHAR), chemin_template_css (VARCHAR, nullable), description_modele (TEXT), version_modele (INT), est_actif (BOOLEAN), date_creation, date_derniere_modification.

C\'est un travail de spécification très détaillé ! Nous continuerons avec les deux prochains services lorsque vous serez prêt.

[]{#_Toc199281845 .anchor}**Service: ServiceConformite.php**

Objectif: Gérer l\'intégralité du processus de vérification de la conformité administrative et réglementaire des rapports soumis par les étudiants. Ce service est principalement utilisé par le personnel administratif ayant le rôle d\'\"Agent de Contrôle de Conformité\".

Fonctionnalités:

-   **Réception et Affichage des Rapports à Vérifier:**

    -   Récupérer et afficher une liste paginée des rapports étudiants ayant le statut \"Soumis pour vérification de conformité\" (ou un statut équivalent indiquant une nouvelle soumission).

    -   Permettre le filtrage et le tri des rapports en attente (par date de soumission, par étudiant, etc.).

    -   Afficher les informations clés de chaque rapport dans la liste (étudiant, titre, date de soumission).

-   **Consultation Détaillée du Dossier de Soumission:**

    -   Permettre à l\'agent de sélectionner un rapport pour ouvrir une vue détaillée.

    -   Afficher toutes les informations saisies par l\'étudiant lors de la soumission (titre, thème, résumé, numéro d\'attestation de stage, etc.).

    -   Permettre l\'accès et la visualisation de tous les documents téléversés par l\'étudiant (fichier principal du rapport, annexes, justificatifs).

    -   Afficher l\'historique des soumissions précédentes pour ce rapport si c\'est une resoumission après demande de corrections de conformité.

-   **Processus de Vérification de Conformité:**

    -   Fournir une checklist ou des directives claires à l\'agent concernant les points de conformité à vérifier (basées sur les règles configurées dans ServiceConfigurationSysteme). Ces points incluent typiquement :

        -   Complétude des informations obligatoires.

        -   Présence de toutes les sections requises dans le rapport (page de garde, résumé, table des matières, bibliographie, etc.).

        -   Respect du format de fichier autorisé (ex: PDF).

        -   Respect de la taille maximale du fichier.

        -   Respect des normes de pagination, de police, de marges (si spécifié).

        -   Présence et validité des documents administratifs requis (ex: attestation de stage).

    -   Assister l\'agent en mettant en évidence (automatiquement si possible) les champs d\'information manquants ou les documents absents.

    -   Permettre à l\'agent de croiser les informations de l\'étudiant avec la base de données des inscriptions pour vérifier son statut administratif et son autorisation à soumettre (interaction avec ServiceGestionAcademique ou ServiceAuthentification pour les données de base de l\'étudiant).

-   **Enregistrement de la Décision de Conformité:**

    -   Permettre à l\'agent de prendre une décision formelle sur la conformité du rapport :

        -   **\"CONFORME\":** Le rapport respecte toutes les exigences administratives et réglementaires.

        -   **\"INCOMPLET\" / \"NON CONFORME\":** Le rapport ne respecte pas une ou plusieurs exigences.

    -   Si la décision est \"INCOMPLET\" / \"NON CONFORME\":

        -   Obliger l\'agent à enregistrer des motifs précis et clairs de non-conformité dans un champ dédié.

        -   Permettre de sélectionner des points de non-conformité à partir d\'une liste prédéfinie (maintenue via ServiceConfigurationSysteme) et/ou d\'ajouter des commentaires libres.

-   **Mise à Jour du Statut et Notifications:**

    -   Mettre à jour le statut du rapport dans le système (via ServiceRapport) pour refléter la décision de conformité (ex: \"Conformité Vérifiée - Conforme\" ou \"Conformité Vérifiée - Incomplet\").

    -   Enregistrer la date de la vérification et l\'identifiant de l\'agent ayant effectué la vérification (dans la table Approuver ou DecisionConformite).

    -   Notifier automatiquement l\'étudiant de la décision de conformité (via ServiceNotification et ServiceEmail):

        -   Si \"CONFORME\", informer que le rapport a été transmis à la commission pédagogique.

        -   Si \"INCOMPLET\", informer des motifs précis de non-conformité et des actions attendues (ex: resoumettre avec corrections avant une date limite).

-   **Transmission à la Commission Pédagogique:**

    -   Lorsqu\'un rapport est marqué comme \"CONFORME\", changer son statut pour le rendre automatiquement visible et accessible aux membres de la commission pédagogique pour la phase d\'évaluation (ex: statut \"Transmis à la commission\").

-   **Suivi et Historique:**

    -   Permettre à l\'agent de consulter la liste des rapports déjà traités (conformes ou non conformes) par lui ou par le service.

    -   Conserver un historique des décisions de conformité pour chaque rapport, y compris les motifs de rejet antérieurs si le rapport a été resoumis.

*Modèles Utilisés et Mises à Jour Potentielles:* \* RapportEtudiant: Le champ id_statut_rapport (FK) doit pouvoir refléter les étapes spécifiques du processus de conformité. Ajouter potentiellement date_derniere_verification_conformite (DATETIME), agent_derniere_verification_conformite_numero (FK Utilisateur).
\* DocumentSoumis: Pour la consultation des fichiers et de leurs métadonnées (type, taille).
\* Approuver (ou idéalement renommer en DecisionConformite pour plus de clarté sémantique): id_decision_conformite (PK), id_rapport_etudiant (FK), numero_personnel_administratif (FK vers Utilisateur de l\'agent), id_statut_conformite (FK vers StatutConformiteRef), commentaire_decision (TEXT, pour les motifs détaillés), date_decision_conformite (DATETIME), liste_points_non_conformes (TEXT ou JSON, pour une liste structurée des problèmes).
\* StatutConformiteRef: id_statut_conformite (PK), code_statut_conformite (UNIQUE, ex: \'CFM_OK\', \'CFM_KO\'), libelle_statut_conformite (VARCHAR, ex: \"Conforme\", \"Incomplet - Corrections requises\").
\* StatutRapportRef: Doit inclure des libellés clairs pour chaque étape, par exemple: \"Soumis pour vérification de conformité\", \"Conformité Vérifiée - En attente de corrections\", \"Conformité Vérifiée - Conforme et transmis à la commission\".
\* Utilisateur (pour identifier l\'agent de conformité).
\* Etudiant (pour les informations de base de l\'étudiant soumettant le rapport).
\* ParametreApplication (via ServiceConfigurationSysteme): Pour récupérer les règles de conformité (documents requis, formats, tailles max).

[]{#_Toc199281846 .anchor}**Service: ServiceGestionAcademique.php**

Objectif: Gérer centralement les aspects administratifs et académiques fondamentaux liés aux étudiants et aux enseignants, servant de source de référence pour d\'autres services et assurant la cohérence des données académiques. Principalement utilisé par le personnel du service de scolarité.

Fonctionnalités:

-   **Gestion des Dossiers Étudiants (en complément de ServiceAuthentification pour les aspects purement académiques):**

    -   Consulter les informations académiques spécifiques d\'un étudiant (parcours, inscriptions antérieures, statut académique actuel).

    -   Mettre à jour des informations académiques spécifiques qui ne relèvent pas du profil utilisateur standard (ex: changement de filière validé, dispenses d\'ECUE).

-   **Gestion des Inscriptions Administratives et Pédagogiques (table inscrire):**

    -   Enregistrer une nouvelle inscription administrative pour un étudiant à une année académique et à un niveau d\'étude/filière spécifique.

    -   Saisir/valider les informations relatives au paiement des frais d\'inscription (montant, date, numéro de reçu, statut du paiement).

    -   Enregistrer la décision de passage en année supérieure ou de redoublement.

    -   Consulter l\'historique complet des inscriptions d\'un étudiant.

    -   Mettre à jour le statut d\'une inscription (ex: paiement confirmé, inscription validée pédagogiquement).

    -   Gérer l\'annulation ou la suppression contrôlée d\'une inscription (avec traçabilité et respect des règles métier).

-   **Gestion des Évaluations et des Notes (table evaluer):**

    -   Saisir les notes obtenues par un étudiant pour chaque ECUE (Élément Constitutif d\'UE) d\'une UE (Unité d\'Enseignement) donnée, pour une session d\'examen (initiale, rattrapage) et une année académique.

    -   Permettre la saisie de commentaires ou d\'appréciations associés à une note.

    -   Consulter l\'ensemble des notes d\'un étudiant (relevé de notes).

    -   Permettre la modification d\'une note (avec justification obligatoire, droits spécifiques et traçabilité).

    -   Gérer la suppression exceptionnelle d\'une note (action strictement contrôlée et tracée).

    -   Calculer et enregistrer les moyennes par ECUE, par UE, et la moyenne générale (si cette logique est centralisée ici).

    -   Gérer la publication des notes aux étudiants (potentiellement en changeant un statut de visibilité).

-   **Gestion des Stages (table faire_stage):**

    -   Enregistrer les informations détaillées d\'un stage effectué par un étudiant (entreprise d\'accueil, sujet du stage, dates de début et de fin, nom et contact du maître de stage externe, enseignant tuteur interne).

    -   Lister tous les stages enregistrés avec filtres (par étudiant, par année, par entreprise).

    -   Consulter les détails complets d\'un enregistrement de stage.

    -   Mettre à jour les informations d\'un stage (ex: si prolongation, changement de tuteur).

    -   Supprimer un enregistrement de stage (si erreur de saisie, par exemple).

-   **Gestion des Affectations et Carrières des Enseignants:**

    -   **Association Enseignant-Grade (table acquerir):**

        -   Enregistrer l\'acquisition d\'un nouveau grade par un enseignant avec la date d\'effet.

        -   Consulter l\'historique des grades d\'un enseignant.

        -   Modifier la date d\'acquisition d\'un grade.

        -   Supprimer un enregistrement de grade (en cas d\'erreur).

    -   **Association Enseignant-Fonction (table occuper):**

        -   Assigner une fonction administrative ou pédagogique à un enseignant avec dates de début et de fin (optionnelle pour les fonctions permanentes).

        -   Consulter les fonctions actuelles et passées d\'un enseignant.

        -   Modifier les dates d\'occupation ou clôturer une fonction (en ajoutant une date de fin).

        -   Retirer une affectation de fonction.

    -   **Association Enseignant-Spécialité (table attribuer):**

        -   Lier un enseignant à une ou plusieurs spécialités d\'enseignement ou de recherche.

        -   Lister les spécialités par enseignant, et les enseignants par spécialité.

        -   Retirer une spécialité à un enseignant.

    -   **Association Enseignant-Département/Structure (si applicable):**

        -   Gérer l\'appartenance d\'un enseignant à un département, laboratoire ou autre structure interne.

-   **Fourniture d\'Informations Académiques à d\'Autres Services:**

    -   Exposer des méthodes sécurisées pour que d\'autres services (ServiceAuthentification, ServiceConformite, ServiceRapport) puissent vérifier :

        -   Le statut de scolarité d\'un étudiant (est-il régulièrement inscrit pour l\'année en cours ?).

        -   L\'autorisation d\'un étudiant à soumettre un rapport pour son niveau d\'étude.

        -   L\'appartenance d\'un enseignant à un groupe spécifique pour des droits d\'accès (ex: chef de département).

-   **Génération de Documents Académiques Officiels (en collaboration avec ServiceDocumentGenerator):**

    -   Fournir les données nécessaires pour générer des attestations d\'inscription, des relevés de notes officiels, des attestations de réussite.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Etudiant: Doit contenir des champs comme id_niveau_etude_actuel (FK), id_filiere_actuelle (FK, si vous avez une table Filiere).
\* Enseignant: Pourrait avoir un champ id_departement_principal (FK).
\* Inscrire: id_inscription (PK), numero_carte_etudiant (FK), id_annee_academique (FK), id_niveau_etude (FK), id_filiere (FK, si applicable), date_inscription (DATE), montant_inscription_paye (DECIMAL), date_paiement (DATE), numero_recu_paiement (VARCHAR), id_statut_paiement (FK vers StatutPaiementRef), id_decision_passage (FK vers DecisionPassageRef), type_inscription (ENUM: \'premiere\', \'reinscription\').
\* StatutPaiementRef: id_statut_paiement (PK), libelle_statut_paiement (VARCHAR).
\* DecisionPassageRef: id_decision_passage (PK), libelle_decision_passage (VARCHAR, ex: \"Admis\", \"Redouble\", \"Ajourné\").
\* Evaluer: id_evaluation (PK), numero_carte_etudiant (FK), id_ecue (FK), id_annee_academique (FK), id_session_examen (FK vers SessionExamenRef, ex: \'Normale\', \'Rattrapage\'), note_obtenue (DECIMAL), date_evaluation (DATE), commentaire_evaluation (TEXT), est_validee_par_jury_notes (BOOLEAN).
\* SessionExamenRef (Nouvelle table potentielle): id_session_examen (PK), libelle_session_examen.
\* FaireStage: id_stage (PK), numero_carte_etudiant (FK), id_entreprise (FK, nullable), nom_entreprise_externe (VARCHAR), sujet_stage (TEXT), date_debut_stage, date_fin_stage, enseignant_tuteur_interne_numero (FK vers Enseignant.numero_enseignant, nullable), maitre_stage_externe_nom (VARCHAR), maitre_stage_externe_contact (VARCHAR), rapport_de_stage_associe_id (FK vers RapportEtudiant.id_rapport_etudiant, nullable).
\* Acquerir: id_acquisition_grade (PK), numero_enseignant (FK), id_grade (FK), date_acquisition_grade (DATE), document_justificatif_url (VARCHAR, nullable).
\* Occuper: id_occupation_fonction (PK), numero_enseignant (FK), id_fonction (FK), date_debut_fonction (DATE), date_fin_fonction (DATE, nullable).
\* Attribuer (Spécialité Enseignant): id_attribution_specialite (PK), numero_enseignant (FK), id_specialite (FK).
\* NiveauEtude, AnneeAcademique, Entreprise, Grade, Fonction, Specialite, Ue, Ecue.
\* Filiere (Nouvelle table potentielle): id_filiere (PK), code_filiere (UNIQUE), lib_filiere, id_departement (FK).
\* Departement (Nouvelle table potentielle): id_departement (PK), code_departement (UNIQUE), lib_departement.

Nous avons couvert 4 services en détail. Prêt pour les deux suivants lorsque vous l\'êtes !

[]{#_Toc199281847 .anchor}**Service: ServiceMessagerie.php**

Objectif: Fournir une fonctionnalité de messagerie instantanée (chat) sécurisée, en temps réel et intégrée, pour faciliter la communication directe et en groupe entre les différents utilisateurs du système (étudiants, enseignants, personnel administratif, membres de commission), avec gestion de l\'historique et des notifications.

Fonctionnalités:

-   **Gestion des Conversations:**

    -   **Création de Conversation:**

        -   Démarrer une nouvelle conversation directe (1-to-1) avec un autre utilisateur identifiable du système.

        -   Créer une nouvelle conversation de groupe en spécifiant un titre pour le groupe et en sélectionnant les participants initiaux.

        -   Permettre aux administrateurs (via ServiceConfigurationSysteme ou une interface dédiée) de créer des groupes de chat prédéfinis ou par défaut (ex: groupe pour chaque commission, groupe pour le personnel de la scolarité).

    -   **Gestion des Participants (pour les groupes):**

        -   Ajouter un ou plusieurs participants à une conversation de groupe existante (par le créateur du groupe ou les administrateurs du groupe).

        -   Retirer un participant d\'une conversation de groupe (par le créateur/administrateurs du groupe).

        -   Permettre à un utilisateur de quitter volontairement une conversation de groupe.

        -   Promouvoir/rétrograder des participants en administrateurs de groupe (si une telle fonctionnalité est prévue).

    -   **Consultation des Conversations:**

        -   Lister toutes les conversations (directes et de groupe) auxquelles l\'utilisateur connecté participe, triées par activité récente.

        -   Afficher un aperçu du dernier message et l\'heure pour chaque conversation dans la liste.

        -   Indiquer le nombre de messages non lus pour chaque conversation.

-   **Gestion des Messages:**

    -   **Envoi de Messages:**

        -   Envoyer un message texte dans une conversation sélectionnée.

        -   Permettre l\'envoi de messages contenant des emojis.

        -   Permettre le téléversement et l\'envoi de petits fichiers (images, documents PDF) en tant que pièces jointes à un message (avec validation de type et de taille via ServiceConfigurationSysteme).

        -   Permettre l\'envoi de liens hypertextes (qui pourraient être prévisualisés côté client).

    -   **Réception et Affichage des Messages:**

        -   Récupérer les messages d\'une conversation sélectionnée, avec pagination pour charger l\'historique des messages plus anciens au fur et à mesure du défilement.

        -   Afficher les messages de manière chronologique, indiquant l\'expéditeur, le contenu et l\'heure d\'envoi.

        -   Afficher les pièces jointes avec une option de prévisualisation (pour les images) et de téléchargement.

    -   **Statut des Messages:**

        -   Indiquer le statut de chaque message envoyé par l\'utilisateur (ex: \"Envoyé\", \"Délivré au serveur\", \"Délivré au(x) destinataire(s)\", \"Lu par \[nom/nombre\]\").

        -   Mettre à jour le statut de lecture d\'un message lorsque le destinataire ouvre la conversation et que le message devient visible.

    -   **Gestion des Messages Lus/Non Lus:**

        -   Marquer automatiquement les messages d\'une conversation comme lus pour un utilisateur lorsque celui-ci ouvre et visualise la conversation.

        -   Permettre manuellement de marquer une conversation comme \"non lue\" si l\'utilisateur souhaite y revenir plus tard.

-   **Notifications de Messagerie:**

    -   Notifier visuellement l\'utilisateur en temps réel (si connecté et actif sur la plateforme) des nouveaux messages reçus (ex: badge sur l\'icône de messagerie, notification toast).

    -   Si l\'utilisateur est hors ligne ou inactif, déclencher une notification système (enregistrée dans la table recevoir via ServiceNotification) et potentiellement un email (via ServiceEmail et ServiceNotification) pour les nouveaux messages dans des conversations directes ou les mentions dans des groupes (si la fonctionnalité de mention est implémentée).

-   **Historique et Recherche:**

    -   Conserver l\'historique complet des messages pour toutes les conversations.

    -   Permettre à l\'utilisateur de rechercher des messages par mots-clés au sein d\'une conversation spécifique ou dans toutes ses conversations (fonctionnalité avancée).

-   **Intégration avec d\'Autres Modules:**

    -   Être utilisable par ServiceCommission pour faciliter la concertation lors de votes divergents sur un rapport.

    -   Permettre potentiellement de lier une conversation à une entité spécifique (ex: un rapport, un PV) pour un contexte de discussion clair.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Conversation: id_conversation (PK), titre_conversation (VARCHAR, pour les groupes, nullable pour les directs), type_conversation (ENUM: \'direct\', \'groupe\'), date_creation_conversation (DATETIME), createur_conversation_numero_utilisateur (FK vers Utilisateur.numero_utilisateur, nullable pour les conversations système ou auto-générées), date_derniere_activite (DATETIME, pour tri).
\* ParticipantConversation: id_participant_conversation (PK), id_conversation (FK), numero_utilisateur (FK), date_ajout_participant (DATETIME), role_dans_conversation (ENUM: \'membre\', \'admin_groupe\', nullable), date_derniere_lecture (DATETIME, nullable, pour savoir jusqu\'où l\'utilisateur a lu).
\* MessageChat: id_message_chat (PK), id_conversation (FK), numero_utilisateur_expediteur (FK vers Utilisateur.numero_utilisateur), contenu_message (TEXT), type_contenu (ENUM: \'texte\', \'image\', \'fichier\', \'lien\', \'systeme\'), date_envoi_message (DATETIME), url_piece_jointe (VARCHAR, nullable), nom_fichier_piece_jointe (VARCHAR, nullable), type_mime_piece_jointe (VARCHAR, nullable), taille_fichier_piece_jointe (INT, nullable), id_message_repondu (FK vers MessageChat.id_message_chat, nullable, pour les réponses en fil).
\* LectureMessage (ou renommer en StatutLectureMessageParParticipant): id_lecture_message (PK), id_message_chat (FK), numero_utilisateur_lecteur (FK vers Utilisateur.numero_utilisateur), date_lecture (DATETIME). (Alternative: Mettre date_derniere_lecture dans ParticipantConversation et comparer avec date_envoi_message).
\* Utilisateur: Pour identifier les participants, expéditeurs, et gérer les préférences de notification de chat.
\* ParametreApplication (via ServiceConfigurationSysteme): Pour les limites de taille/type des pièces jointes, durée de rétention des messages.

[]{#_Toc199281848 .anchor}**Service: ServiceNotification.php**

Objectif: Gérer de manière centralisée la création, l\'enregistrement, la récupération et la gestion de l\'état des notifications système pour les utilisateurs. Ce service agit comme un hub, coordonnant l\'envoi effectif des alertes via différents canaux (notifications in-app, email via ServiceEmail) en fonction des configurations et des préférences utilisateur.

Fonctionnalités:

-   **Création et Enregistrement de Notifications:**

    -   Fournir une méthode pour enregistrer une notification destinée à un utilisateur spécifique dans la base de données (table recevoir).

        -   Prendre en entrée: numero_utilisateur destinataire, id_notification (type de notification), message complémentaire/personnalisé, lien optionnel vers la ressource concernée.

    -   Fournir une méthode pour enregistrer une notification destinée à tous les utilisateurs d\'un groupe spécifique (ex: tous les membres d\'une commission, tous les étudiants d\'un niveau).

    -   Fournir une méthode pour enregistrer une notification \"broadcast\" à tous les utilisateurs actifs du système (pour annonces importantes par les administrateurs).

    -   Formater le contenu final de la notification en se basant sur le type de notification (qui peut référencer un modèle de Message) et le message complémentaire fourni.

-   **Distribution des Notifications:**

    -   **Notifications In-App:** Les notifications enregistrées dans recevoir sont la source pour les affichages de notifications directement dans l\'interface utilisateur.

    -   **Notifications par Email:**

        -   Vérifier si le type de notification (Notification.id_notification) est configuré pour un envoi par email (via son Message.canal_notification associé).

        -   Vérifier les préférences de l\'utilisateur (Utilisateur.preferences_notification_email) avant d\'envoyer un email.

        -   Si les conditions sont remplies, appeler ServiceEmail avec le contenu formaté (sujet et corps récupérés du modèle Message associé) et les informations du destinataire.

-   **Gestion et Récupération des Notifications pour l\'Utilisateur:**

    -   Récupérer la liste des notifications (paginée) pour l\'utilisateur connecté, en distinguant les lues des non lues.

    -   Permettre de trier les notifications (par date, par statut de lecture).

    -   Marquer une notification spécifique comme \"lue\" pour un utilisateur (met à jour Recevoir.lue et Recevoir.date_lecture).

    -   Marquer toutes les notifications d\'un utilisateur comme \"lues\".

    -   Permettre de marquer une notification comme \"non lue\" (moins courant, mais possible).

    -   Compter le nombre total de notifications non lues pour un utilisateur (pour affichage d\'un badge, par exemple).

-   **Gestion des Liens Associés:**

    -   Stocker et fournir un lien URL pertinent avec chaque notification (si applicable), permettant à l\'utilisateur d\'accéder directement à la ressource concernée en cliquant sur la notification (ex: un rapport spécifique, un PV, une conversation de chat).

-   **Maintenance des Notifications:**

    -   Fournir une fonctionnalité (pour administrateurs, via ServiceSupervisionAdmin) pour archiver ou supprimer en masse les anciennes notifications lues après un certain délai configurable (via ServiceConfigurationSysteme), afin de maintenir la performance de la table recevoir.

-   **Point d\'Entrée Centralisé pour les Événements Notifiables:**

    -   Servir de point d\'appel unique pour tous les autres services du système qui ont besoin de générer une notification suite à un événement métier. Exemples :

        -   ServiceRapport appelle ce service lors d\'un changement de statut d\'un rapport.

        -   ServiceMessagerie appelle ce service pour un nouveau message si l\'utilisateur est hors ligne.

        -   ServiceConformite appelle ce service après une décision de conformité.

        -   ServiceCommission appelle ce service pour notifier d\'un vote requis ou d\'un PV validé.

        -   ServiceAuthentification appelle ce service pour des alertes de sécurité (si configuré).

        -   ServiceReclamation appelle ce service pour les mises à jour de statut d\'une réclamation.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Notification (table des types/catégories de notifications): id_notification (PK), code_notification (VARCHAR, UNIQUE, ex: \'RAPPORT_SOUMIS_CFM\', \'NOUVEAU_MSG_CHAT\', \'PV_VALIDE\'), lib_notification (VARCHAR, titre par défaut ou description courte), description_detaillee_type (TEXT, optionnel), id_message_template_systeme_associe (FK vers Message.id_message, pour le formatage in-app), id_message_template_email_associe (FK vers Message.id_message, pour le formatage email), niveau_importance (ENUM: \'info\', \'warning\', \'error\', \'critical\', nullable).
\* Recevoir (notifications instanciées pour les utilisateurs): id_recevoir (PK), numero_utilisateur (FK), id_notification (FK vers Notification.id_notification), message_personnalise (TEXT, contenu spécifique à cette instance, ex: \"Le rapport \'Titre du rapport\' a été validé.\"), date_creation_notification (DATETIME), lue (BOOLEAN, default FALSE), date_lecture (DATETIME, nullable), lien_associe (VARCHAR(512), URL pour redirection), id_entite_concernee (VARCHAR/INT, nullable, ex: id_rapport_etudiant ou id_compte_rendu), type_entite_concernee (VARCHAR, nullable, ex: \'rapport\', \'pv\').
\* Utilisateur: Pour numero_utilisateur destinataire et pour vérifier les preferences_notification_email (qui pourrait être plus granulaire: preferences_notifications sous forme de JSON pour activer/désactiver par type/canal).
\* GroupeUtilisateur: Pour identifier les groupes d\'utilisateurs destinataires.
\* Message (via ServiceConfigurationSysteme): Pour récupérer les modèles de contenu pour les notifications système et les emails.
\* ParametreApplication (via ServiceConfigurationSysteme): Pour la durée de rétention des notifications lues.

Nous avons détaillé 6 services. Prêt pour la suite quand vous l\'êtes, Manuel !

[]{#_Toc199281849 .anchor}**Service: ServicePermissions.php**

Objectif: Gérer de manière centralisée et robuste les habilitations et les droits d\'accès des utilisateurs aux différentes fonctionnalités (traitements) et potentiellement aux données spécifiques du système. Ce service se base sur les rôles (types d\'utilisateur), les groupes d\'utilisateurs et les permissions explicitement associées.

Fonctionnalités:

-   **Vérification des Permissions d\'Accès aux Fonctionnalités (Traitements):**

    -   Fournir une méthode principale pour vérifier si l\'utilisateur actuellement connecté (ou un utilisateur spécifié par son numero_utilisateur) a la permission d\'accéder à un traitement (fonctionnalité) donné. Un traitement peut être identifié par son id_traitement ou son code_traitement.

    -   La logique de vérification implique typiquement :

        a.  Récupérer le(s) id_groupe_utilisateur de l\'utilisateur.

        b.  Vérifier dans la table rattacher si une association existe entre l\'un de ces groupes et le traitement demandé.

    -   Prendre en compte un éventuel \"super-administrateur\" qui aurait accès à tous les traitements, indépendamment des associations dans rattacher.

-   **Récupération des Traitements Autorisés:**

    -   Fournir une méthode pour récupérer la liste complète des traitement (ou leurs codes/libellés) auxquels l\'utilisateur connecté a accès.

    -   Cette liste est utilisée pour construire dynamiquement les menus, les barres latérales et pour activer/désactiver des éléments d\'interface utilisateur.

    -   Permettre de filtrer les traitements par module ou catégorie si ces informations sont stockées dans la table Traitement.

-   **Gestion des Associations entre Groupes et Traitements (Permissions effectives - table rattacher):**

    -   Associer un groupe_utilisateur à un ou plusieurs traitement pour accorder des permissions.

    -   Dissocier un traitement d\'un groupe_utilisateur pour révoquer une permission.

    -   Lister toutes les permissions (associations groupe_utilisateur \<-\> traitement).

    -   Visualiser tous les traitement associés à un groupe_utilisateur spécifique.

    -   Visualiser tous les groupe_utilisateur associés à un traitement spécifique.

-   **Gestion des Types d\'Utilisateur (Rôles) (CRUD - si non exclusivement dans ServiceConfigurationSysteme):**

    -   Créer un nouveau type_utilisateur avec un libellé et une description (ex: \"Étudiant Avancé\", \"Secrétaire de Commission\").

    -   Lire/Lister tous les types d\'utilisateurs existants.

    -   Afficher les détails d\'un type d\'utilisateur.

    -   Modifier le libellé ou la description d\'un type d\'utilisateur.

    -   Supprimer un type d\'utilisateur (avec vérification quaucun utilisateur n\'y est assigné).

-   **Gestion des Groupes d\'Utilisateur (CRUD - si non exclusivement dans ServiceConfigurationSysteme):**

    -   Créer un nouveau groupe_utilisateur avec un libellé, un code unique et une description.

    -   Lire/Lister tous les groupes d\'utilisateurs existants.

    -   Afficher les détails d\'un groupe d\'utilisateur, y compris la liste des traitements qui lui sont rattachés.

    -   Modifier le libellé, le code ou la description d\'un groupe.

    -   Supprimer un groupe d\'utilisateur (avec vérification qu\'il n\'est lié à aucun utilisateur et qu\'aucune permission n\'y est attachée dans rattacher).

-   **Gestion des Niveaux d\'Accès aux Données (CRUD - si non exclusivement dans ServiceConfigurationSysteme):**

    -   Définir un nouveau niveau_acces_donne avec un libellé, un code unique et une description.

    -   Lire/Lister tous les niveaux d\'accès aux données existants.

    -   Modifier un niveau d\'accès.

    -   Supprimer un niveau d\'accès (s\'il n\'est plus utilisé par aucun utilisateur).

-   **Logique de Permissions Avancée (si nécessaire):**

    -   Implémenter une logique de vérification de permission plus granulaire si NiveauAccesDonne est utilisé en conjonction avec les traitement pour contrôler non seulement l\'accès à une fonctionnalité, mais aussi le *niveau* d\'interaction avec les données au sein de cette fonctionnalité (ex: lecture seule vs. écriture pour certains champs, accès à un sous-ensemble de données basé sur le niveau). Ceci nécessiterait des règles plus complexes et potentiellement des tables de mapping supplémentaires.

    -   Gérer l\'héritage de permissions si des groupes peuvent être imbriqués (fonctionnalité avancée non explicitement demandée mais possible dans des systèmes complexes).

-   **Audit des Permissions:**

    -   Journaliser les modifications importantes liées aux permissions (création/suppression de groupes, modification d\'associations dans rattacher) via ServiceSupervisionAdmin.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Utilisateur: Contient id_groupe_utilisateur (FK) et id_type_utilisateur (FK), id_niveau_acces_donne (FK). Pourrait avoir plusieurs groupes si une liaison N-N est préférée (via une table UtilisateurGroupe).
\* Rattacher (Table de liaison N-N): id_rattachement (PK), id_groupe_utilisateur (FK), id_traitement (FK). On pourrait ajouter une colonne type_permission (ENUM: \'allow\', \'deny\') si on veut gérer des interdictions explicites, bien que ce soit plus complexe.
\* Traitement: id_traitement (PK), code_traitement (VARCHAR, UNIQUE, ex: \'USER_CREATE\', \'RAPPORT_VIEW_ALL\'), lib_trait (VARCHAR), description_trait (TEXT), module_associe (VARCHAR, pour regroupement), url_menu_associe (VARCHAR, nullable), icone_menu (VARCHAR, nullable), ordre_affichage_menu (INT, nullable), necessite_permission_specifique (BOOLEAN, default TRUE).
\* GroupeUtilisateur: id_groupe_utilisateur (PK), code_groupe (VARCHAR, UNIQUE), lib_groupe_utilisateur (VARCHAR), description_groupe (TEXT).
\* TypeUtilisateur: id_type_utilisateur (PK), code_type_utilisateur (VARCHAR, UNIQUE), lib_type_utilisateur (VARCHAR), description_type_utilisateur (TEXT).
\* NiveauAccesDonne: id_niveau_acces_donne (PK), code_niveau_acces (VARCHAR, UNIQUE), lib_niveau_acces_donne (VARCHAR), description_niveau_acces (TEXT).
\* PermissionSpecifiqueSurDonnee (Nouvelle table potentielle si granularité fine): id_permission_specifique (PK), id_groupe_utilisateur (FK) ou id_utilisateur (FK), id_traitement (FK), nom_entite_concernee (VARCHAR, ex: \'RapportEtudiant\'), id_entite_specifique (VARCHAR/INT, nullable, pour permission sur un objet précis), type_d\_acces (ENUM: \'lecture\', \'ecriture\', \'suppression\', \'champ_specifique_lecture\', \'champ_specifique_ecriture\'), nom_champ_specifique (VARCHAR, nullable).

[]{#_Toc199281850 .anchor}**Service: ServiceRapport.php**

Objectif: Gérer l\'intégralité du cycle de vie des rapports étudiants, depuis leur création et soumission initiale, le suivi de leur validation par les différentes instances (conformité, commission), la gestion des versions et des corrections, jusqu\'à l\'enregistrement de la décision finale et des documents associés.

Fonctionnalités:

-   **Création et Soumission Initiale du Rapport par l\'Étudiant:**

    -   Permettre à un étudiant de créer un nouveau dossier de rapport (enregistrement initial dans RapportEtudiant avec un statut \"Brouillon\" ou \"En cours de rédaction\").

    -   Permettre la saisie des informations initiales du rapport: titre, thème proposé, résumé/abstract, mots-clés, année académique concernée, (optionnel) directeur de mémoire pressenti.

    -   Permettre à l\'étudiant de téléverser le fichier principal de son rapport (ex: PDF) et des pièces jointes éventuelles (annexes, code source, etc.). Chaque fichier est enregistré dans DocumentSoumis et lié au RapportEtudiant.

    -   Gérer un versionnement simple des documents soumis (ex: version dans DocumentSoumis).

    -   Permettre à l\'étudiant de soumettre formellement son rapport pour le processus de vérification. Cela change le statut du rapport (ex: de \"Brouillon\" à \"Soumis pour vérification de conformité\") et enregistre la date_soumission_initiale.

    -   Fournir un accusé de réception de la soumission à l\'étudiant (via ServiceNotification).

-   **Gestion des Statuts du Rapport:**

    -   Mettre à jour le statut d\'un rapport de manière centralisée tout au long de son cycle de vie. Ce service est appelé par ServiceConformite et ServiceCommission pour refléter les décisions prises.

    -   Les statuts typiques incluent : \"Brouillon\", \"Soumis pour vérification de conformité\", \"Conformité Vérifiée - En attente de corrections (étudiant)\", \"Conformité Vérifiée - Conforme et transmis à la commission\", \"En cours d\'évaluation par la commission\", \"Commission - Corrections demandées (étudiant)\", \"Commission - Validé\", \"Commission - Refusé\", \"Archivé\".

    -   Enregistrer chaque changement de statut dans une table d\'historique (HistoriqueStatutRapport) avec la date, l\'ancien statut, le nouveau statut, et l\'acteur du changement (si disponible).

-   **Gestion des Corrections Demandées à l\'Étudiant:**

    -   Si ServiceConformite ou ServiceCommission demande des corrections, le statut du rapport est mis à jour en conséquence (ex: \"Conformité - Corrections demandées\", \"Commission - Corrections demandées\").

    -   Permettre à l\'étudiant de consulter les motifs des corrections demandées.

    -   Permettre à l\'étudiant de téléverser une nouvelle version de son rapport et/ou des documents corrigés. La nouvelle version est enregistrée dans DocumentSoumis (avec un numéro de version incrémenté ou un lien vers la version précédente id_document_precedent).

    -   Permettre à l\'étudiant de joindre une note explicative des modifications apportées.

    -   Lors de la resoumission des corrections, mettre à jour le statut du rapport (ex: \"Corrections soumises - En attente de re-vérification conformité\" ou \"Corrections soumises - En attente de re-validation commission\").

-   **Consultation et Récupération d\'Informations du Rapport:**

    -   Récupérer toutes les informations d\'un rapport spécifique par son ID (détails du rapport, informations de l\'étudiant associé, statut actuel, historique complet des statuts, tous les documents soumis avec leurs versions et types).

    -   Permettre à l\'étudiant de consulter l\'historique de ses propres dépôts et des versions soumises pour son rapport.

    -   Permettre aux acteurs habilités (personnel administratif, membres de commission) de consulter les rapports relevant de leur périmètre.

    -   Lister les rapports selon divers critères (par statut, par étudiant, par année académique, par date de soumission, etc.) avec pagination et options de recherche.

-   **Gestion Post-Validation Finale:**

    -   Après la décision finale de la commission (et validation du PV correspondant), enregistrer la note_finale obtenue pour le rapport (si applicable).

    -   Enregistrer la date_validation_finale du rapport.

    -   Stocker l\'URL ou le chemin d\'accès à la version_finale_validee_url du document PDF du rapport (qui pourrait être une version spécifique dans DocumentSoumis ou un document généré par ServiceDocumentGenerator après estampillage).

-   **Interaction avec d\'Autres Services:**

    -   Fournir des informations sur les rapports à ServiceConformite, ServiceCommission, ServiceReportingAdmin, ServiceSupervisionAdmin.

    -   Être notifié par ServiceConformite et ServiceCommission des décisions prises pour mettre à jour les statuts.

-   **Archivage des Rapports:**

    -   Mettre en place une logique pour archiver les rapports après une certaine période suivant leur validation finale ou refus définitif (changement de statut vers \"Archivé\"). Les documents peuvent être déplacés vers un stockage à long terme ou simplement marqués.

*Modèles Utilisés et Mises à Jour Potentielles:* \* RapportEtudiant: id_rapport_etudiant (PK), numero_carte_etudiant (FK), titre_rapport (TEXT), theme_rapport (VARCHAR(255)), resume_rapport (TEXT), mots_cles (TEXT, ou table N-N RapportMotCle), id_annee_academique (FK), id_statut_rapport (FK vers StatutRapportRef), date_creation_dossier (DATETIME), date_soumission_initiale (DATETIME, nullable), date_derniere_modification_etudiant (DATETIME, nullable), date_validation_finale (DATETIME, nullable), note_finale (DECIMAL, nullable), url_version_finale_validee (VARCHAR, nullable), directeur_memoire_propose_numero (FK Enseignant, nullable), encadreur_pedagogique_propose_numero (FK Enseignant, nullable), numero_attestation_stage (VARCHAR, nullable), nombre_pages (INT, nullable).
\* DocumentSoumis: id_document (PK), id_rapport_etudiant (FK), chemin_fichier_stockage (VARCHAR), nom_fichier_original (VARCHAR), type_mime (VARCHAR), taille_octet (BIGINT), date_upload (DATETIME), version_document (INT, default 1), id_type_document (FK vers TypeDocumentRef), numero_utilisateur_upload (FK vers Utilisateur.numero_utilisateur), est_correction_de_conformite (BOOLEAN, default FALSE), est_correction_de_commission (BOOLEAN, default FALSE), id_document_precedent_correction (FK vers DocumentSoumis.id_document, nullable), description_document_par_etudiant (TEXT, nullable).
\* TypeDocumentRef: id_type_document (PK), code_type_document (UNIQUE, ex: \'RAPPORT_PRINCIPAL\', \'ANNEXE\', \'ATTESTATION_STAGE\', \'NOTE_CORRECTIONS\'), libelle_type_document (VARCHAR).
\* StatutRapportRef: id_statut_rapport (PK), code_statut_rapport (UNIQUE), libelle_statut_rapport (VARCHAR), description_statut (TEXT), prochaine_etape_possible (TEXT, indicatif).
\* HistoriqueStatutRapport (Nouvelle table ou à confirmer): id_historique_statut (PK), id_rapport_etudiant (FK), id_statut_precedent (FK vers StatutRapportRef, nullable pour le premier statut), id_statut_actuel (FK vers StatutRapportRef), date_changement_statut (DATETIME), acteur_changement_numero_utilisateur (FK vers Utilisateur.numero_utilisateur, nullable si action système), commentaire_changement_statut (TEXT, nullable, ex: motif de la décision).
\* Etudiant, AnneeAcademique, Utilisateur.
\* MotCle (Nouvelle table potentielle): id_mot_cle (PK), libelle_mot_cle (VARCHAR, UNIQUE).
\* RapportMotCle (Table de liaison N-N): id_rapport_etudiant (FK), id_mot_cle (FK).

Nous avons maintenant détaillé 8 services. Plus que 4 principaux si je ne m\'abuse (ServiceReportingAdmin, ServiceSupervisionAdmin, ServiceEmail, ServiceReclamation, et le ServiceDocumentGenerator que nous avions ajouté). Prêt pour la suite ?

[]{#_Toc199281851 .anchor}**Service: ServiceReportingAdmin.php**

Objectif: Fournir des fonctionnalités de génération de rapports statistiques et analytiques avancés pour l\'administration, la direction et les responsables de filière, afin de suivre les performances globales, les tendances, les délais et l\'efficacité du processus de gestion des soutenances.

Fonctionnalités:

-   **Rapports sur les Soumissions et Validations de Rapports:**

    -   Générer des statistiques sur le nombre de rapports soumis par période (mois, année académique, semestre).

    -   Calculer les taux de validation des rapports (nombre de rapports validés / nombre de rapports soumis ou évalués) par :

        -   Année académique.

        -   Niveau d\'étude ou filière (si applicable et si ces données sont disponibles).

        -   Type de thème ou catégorie de sujet (si les rapports sont catégorisés).

    -   Analyser le nombre de rapports nécessitant des corrections (par l\'instance de conformité et par la commission).

    -   Suivre le nombre de rapports refusés et les motifs principaux de refus (nécessite une structuration des motifs).

-   **Analyse des Délais de Traitement:**

    -   Calculer le délai moyen (et médian, min, max) de traitement à chaque étape clé du workflow (nécessite une journalisation précise des dates de changement de statut dans HistoriqueStatutRapport):

        -   Délai entre soumission par l\'étudiant et vérification de conformité.

        -   Délai de traitement par le service de conformité.

        -   Délai entre transmission à la commission et décision de la commission.

        -   Délai de rédaction et validation des PV.

        -   Délai de correction par les étudiants.

    -   Identifier les goulots d\'étranglement potentiels en analysant les étapes avec les délais les plus longs.

-   **Statistiques d\'Utilisation de la Plateforme:**

    -   Nombre d\'utilisateurs actifs (par type: étudiants, enseignants, personnel) sur une période donnée.

    -   Nombre de connexions à la plateforme.

    -   Fréquence d\'utilisation des fonctionnalités clés (ex: nombre de soumissions, nombre de votes, nombre de messages de chat envoyés) - nécessite une journalisation des actions via ServiceSupervisionAdmin.

-   **Analyse de Performance (Agrégée et Anonymisée si nécessaire):**

    -   Performance des encadreurs (directeurs de mémoire, tuteurs) : nombre de rapports encadrés, taux de validation des rapports encadrés, délai moyen de retour (si suivi).

    -   Performance des membres de la commission : nombre de rapports évalués/votés, délai moyen de vote (avec prudence sur l\'interprétation et l\'anonymisation).

-   **Analyse des Contenus et Tendances:**

    -   Identifier les thématiques de rapports les plus fréquentes (basé sur les mots-clés ou titres, si des outils d\'analyse de texte sont intégrés ou si les données sont exportables).

    -   Analyser la fréquence et les types de corrections les plus souvent demandées (à la fois par la conformité et la commission), pour identifier des points d\'amélioration dans les consignes aux étudiants.

-   **Configuration et Génération de Rapports:**

    -   Permettre la sélection de la période pour laquelle le rapport est généré.

    -   Offrir des filtres pour affiner les données du rapport (par année académique, niveau d\'étude, statut, etc.).

    -   Présenter les rapports sous forme de tableaux, graphiques (barres, lignes, secteurs) et KPIs.

    -   Permettre l\'exportation des données brutes et des rapports générés dans des formats courants (CSV, Excel, PDF).

    -   Permettre la configuration de tableaux de bord personnalisés pour la direction ou les responsables, affichant une sélection d\'indicateurs clés de performance (KPIs) pertinents pour leur rôle.

    -   Planifier la génération automatique de certains rapports périodiques (fonctionnalité avancée).

*Modèles Utilisés et Mises à Jour Potentielles:* \* RapportEtudiant: Source principale pour les informations sur les rapports.
\* HistoriqueStatutRapport: Crucial pour l\'analyse des délais et des flux.
\* AnneeAcademique, NiveauEtude, Filiere (si existe).
\* Utilisateur (et ses types/profils associés Etudiant, Enseignant, PersonnelAdministratif): Pour les statistiques d\'utilisation et de performance.
\* Enregistrer (logs d\'actions): Pour les statistiques d\'utilisation des fonctionnalités.
\* VoteCommission, DecisionConformite (anciennement Approuver), CompteRendu (PV): Pour analyser les décisions et les acteurs.
\* MotCle, RapportMotCle (si implémenté): Pour l\'analyse thématique.
\* Des vues matérialisées ou des tables de faits/dimensions (Data Warehouse simplifié) pourraient être envisagées pour optimiser les performances des requêtes de reporting complexes sur de grands volumes de données.
\* RapportSauvegarde (Nouvelle table potentielle): id_rapport_sauvegarde (PK), nom_rapport (VARCHAR), description_rapport (TEXT), parametres_generation (JSON), type_rapport (VARCHAR), date_generation (DATETIME), genere_par_numero_utilisateur (FK), format_export (ENUM: \'csv\', \'pdf\', \'xlsx\'), chemin_fichier_sauvegarde (VARCHAR, nullable).

[]{#_Toc199281852 .anchor}**Service: ServiceSupervisionAdmin.php**

Objectif: Offrir des outils de supervision globale du système en temps réel et différé, de consultation exhaustive des journaux d\'audit et de traçabilité, de suivi des workflows critiques, et de gestion de certaines tâches de maintenance administrative de base.

Fonctionnalités:

-   **Tableau de Bord de Supervision Globale:**

    -   Afficher en temps réel (ou quasi réel) des indicateurs clés sur l\'état du système :

        -   Nombre de rapports à chaque statut principal du workflow (ex: \"En attente conformité\", \"En attente vote commission\").

        -   Alertes sur les rapports en attente depuis une durée anormale à une étape donnée (goulots d\'étranglement).

        -   Charge de travail estimée pour les différents services/rôles (ex: nombre de rapports par agent de conformité, nombre de rapports par membre de commission).

        -   Nombre d\'utilisateurs connectés actuellement.

-   **Consultation des Journaux d\'Audit (table enregistrer):**

    -   Fournir une interface pour consulter le journal détaillé des actions des utilisateurs.

    -   Permettre des filtres avancés sur les logs : par numero_utilisateur, par id_action (type d\'action), par plage de dates, par type_entite_concernee et id_entite_concernee (ex: toutes les actions sur un rapport spécifique).

    -   Visualiser les détails complets de chaque action enregistrée: qui a fait quoi, quand, depuis quelle adresse IP, avec quel user-agent, et les données contextuelles (payload JSON).

-   **Consultation de la Traçabilité des Accès aux Fonctionnalités (table pister):**

    -   Fournir une interface pour consulter les logs d\'accès aux traitement (fonctionnalités).

    -   Permettre des filtres par numero_utilisateur, id_traitement, plage de dates.

    -   Vérifier si l\'accès à une fonctionnalité a été accordé ou refusé (si la journalisation inclut les tentatives refusées par ServicePermissions).

-   **Gestion Administrative des Procès-Verbaux (PV) (aspects de supervision):**

    -   Accéder à une vue d\'ensemble de tous les PV, quel que soit leur statut (Brouillon, Soumis, Validé, Archivé).

    -   Rechercher et filtrer les PV (par étudiant, par date, par rédacteur, par statut).

    -   Lister les PV validés éligibles pour un archivage officiel à long terme (ex: PV validés depuis plus de X mois/ans).

    -   Gérer le signalement (ou une action administrative) pour les rapports (et leurs PV associés) qui sont éligibles à une publication externe (si une telle politique existe).

    -   Initier une action d\'archivage officiel des PV (changement de statut vers \"Archivé Officiellement\", potentiellement déplacement des fichiers vers un stockage sécurisé à long terme).

-   **Gestion des Alertes Système:**

    -   Consulter une liste des alertes système critiques qui auraient été générées (erreurs BDD non gérées, problèmes de connexion aux services externes, tentatives d\'accès suspectes détectées, échecs de processus automatisés importants comme la génération de rapports planifiés).

    -   Nécessite un système de logging d\'erreurs applicatives plus global et/ou une table AlerteSysteme dédiée.

    -   Permettre de marquer des alertes comme \"lues\", \"en cours de traitement\", \"résolues\".

-   **Outils de Maintenance Technique de Base (si exposés via interface admin):**

    -   Lancer manuellement certains scripts de maintenance (ex: nettoyage de données temporaires, purge des sessions expirées, réindexation de tables spécifiques si pertinent et sécurisé).

    -   Visualiser l\'état des sauvegardes (si des logs de sauvegarde sont accessibles) et potentiellement initier une sauvegarde manuelle (si l\'infrastructure le permet via une API).

    -   Consulter les versions des composants clés de l\'application et des dépendances (si cette information est disponible).

-   **Suivi des Processus Automatisés:**

    -   Si des tâches planifiées (cron jobs) existent (ex: envoi de rappels, archivage automatique), afficher leur statut d\'exécution (dernière exécution, succès/échec, logs).

*Modèles Utilisés et Mises à Jour Potentielles:* \* RapportEtudiant, HistoriqueStatutRapport: Pour le suivi des workflows.
\* Enregistrer: id_enregistrement (PK), numero_utilisateur (FK, nullable si action système), id_action (FK vers Action.id_action), date_action (DATETIME), adresse_ip (VARCHAR), user_agent (TEXT), type_entite_concernee (VARCHAR, nullable), id_entite_concernee (VARCHAR/INT, nullable), details_action_json (JSON, pour stocker le contexte de l\'action).
\* Action (Référentiel): id_action (PK), code_action (UNIQUE), lib_action.
\* Pister: id_pistage (PK), numero_utilisateur (FK), id_traitement (FK), date_acces_traitement (DATETIME), acces_accorde (BOOLEAN, si on logue aussi les refus).
\* Traitement (Référentiel).
\* CompteRendu (PV), StatutPvRef: Pour la gestion administrative des PV.
\* Utilisateur: Pour identifier les acteurs.
\* ArchivePV (Nouvelle table, comme suggéré précédemment): id_archive_pv (PK), id_compte_rendu_original (FK), contenu_pv_archive_text (LONGTEXT, pour recherche), chemin_fichier_pv_archive (VARCHAR), date_archivage (DATETIME), raison_archivage (TEXT, nullable), numero_agent_archivage (FK Utilisateur).
\* AlerteSysteme (Nouvelle table): id_alerte (PK), code_alerte (VARCHAR, UNIQUE), type_alerte (ENUM: \'erreur_bdd\', \'erreur_service_externe\', \'securite\', \'processus_echec\'), message_alerte (TEXT), details_techniques_json (JSON), date_occurrence (DATETIME), statut_alerte (ENUM: \'nouveau\', \'lu\', \'en_cours\', \'resolu\', \'ignore\'), priorite_alerte (INT).
\* LogTachePlanifiee (Nouvelle table potentielle): id_log_tache (PK), nom_tache (VARCHAR), date_debut_execution (DATETIME), date_fin_execution (DATETIME, nullable), statut_execution (ENUM: \'succes\', \'echec\', \'en_cours\'), message_log (TEXT).

[]{#_Toc199281853 .anchor}**Service: ServiceEmail.php**

Objectif: Encapsuler et centraliser toute la logique d\'envoi d\'e-mails transactionnels et de notification de l\'application, en utilisant une bibliothèque d\'envoi d\'email (comme PHPMailer) et en s\'intégrant avec les modèles d\'email définis dans ServiceConfigurationSysteme.

Fonctionnalités:

-   **Configuration de l\'Envoi d\'Email:**

    -   Permettre la configuration centralisée des paramètres SMTP (hôte, port, utilisateur, mot de passe, type de chiffrement - SSL/TLS) ou d\'autres méthodes d\'envoi (ex: API d\'un service d\'email transactionnel comme SendGrid, Mailgun). Cette configuration est typiquement chargée depuis des fichiers de configuration de l\'application et injectée dans le service.

    -   Définir l\'adresse email et le nom de l\'expéditeur par défaut pour les emails envoyés par le système (peut être surchargé).

-   **Composition et Envoi d\'Emails Simples:**

    -   Fournir une méthode pour envoyer un email simple en spécifiant :

        -   Un ou plusieurs destinataires (To).

        -   Des destinataires en copie (Cc) et en copie cachée (Bcc) (optionnel).

        -   Le sujet de l\'email.

        -   Le corps de l\'email en format HTML.

        -   Le corps de l\'email en format texte alternatif (pour les clients mail ne supportant pas HTML).

-   **Utilisation de Modèles d\'Emails:**

    -   S\'intégrer avec ServiceConfigurationSysteme (ou directement avec le modèle Message) pour :

        -   Charger un modèle d\'email (sujet, corps HTML, corps texte) par son code_message ou nom_template.

        -   Permettre l\'injection de variables dynamiques (placeholders) dans le sujet et le corps du modèle (ex: nom de l\'utilisateur, titre du rapport, lien de validation).

    -   Fournir une méthode pour envoyer un email basé sur un modèle et un ensemble de données pour les variables.

-   **Gestion des Pièces Jointes:**

    -   Permettre d\'ajouter une ou plusieurs pièces jointes à un email, à partir d\'un chemin de fichier sur le serveur ou d\'un flux de données binaire.

    -   Spécifier le nom du fichier tel qu\'il apparaîtra pour le destinataire et potentiellement le type MIME.

-   **Gestion des Erreurs et Journalisation:**

    -   Capturer les erreurs lors de la tentative d\'envoi d\'un email (ex: échec de connexion SMTP, adresse destinataire invalide).

    -   Journaliser de manière détaillée les succès et les échecs d\'envoi d\'emails (destinataire, sujet, date, statut, message d\'erreur si échec). Ces logs sont cruciaux pour le diagnostic.

    -   Potentiellement, mettre en place un système de file d\'attente pour l\'envoi d\'emails (surtout pour les envois en masse ou pour découpler l\'envoi de la requête principale) si le volume d\'emails est important (fonctionnalité avancée).

-   **Prévention du Spam et Bonnes Pratiques:**

    -   S\'assurer que les emails envoyés respectent les bonnes pratiques pour éviter d\'être marqués comme spam (ex: en-têtes corrects, lien de désinscription si applicable pour certains types de mails marketing - moins pertinent ici pour des mails transactionnels).

    -   Gérer les rebonds (bounces) et les plaintes (complaints) si un service d\'email transactionnel externe est utilisé et fournit ces informations via webhooks (fonctionnalité avancée).

-   **Internationalisation (i18n):**

    -   Si l\'application est multilingue, les modèles d\'emails doivent pouvoir être gérés en plusieurs langues. Le service devrait pouvoir sélectionner le bon modèle en fonction de la langue préférée de l\'utilisateur destinataire.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Aucun modèle de données direct pour le stockage interne si ce service est purement un orchestrateur d\'envoi. Il consomme les modèles Message via ServiceConfigurationSysteme.
\* LogEmail (Nouvelle table potentielle pour la journalisation détaillée, si non géré par des logs fichiers ou un système de logging externe): id_log_email (PK), date_envoi_tentative (DATETIME), destinataire_email (VARCHAR), sujet_email (VARCHAR), statut_envoi (ENUM: \'succes\', \'echec\'), message_erreur (TEXT, nullable), id_message_template_utilise (FK vers Message.id_message, nullable), type_email (VARCHAR, ex: \'bienvenue\', \'reset_mdp\', \'notification_rapport\').
\* Utilisateur: Pour récupérer l\'adresse email du destinataire et ses préférences de langue.
\* Message (via ServiceConfigurationSysteme): Pour les templates.
\* ParametreApplication (via ServiceConfigurationSysteme): Pour la configuration SMTP, expéditeur par défaut.

Nous avons couvert 9 services. Il nous reste ServiceReclamation et ServiceDocumentGenerator. Prêt pour ces deux derniers ?

[]{#_Toc199281854 .anchor}**Service: ServiceReclamation.php**

Objectif: Gérer l\'ensemble du processus de soumission, de suivi, de traitement et de résolution des réclamations formulées par les étudiants concernant des aspects de la plateforme ou du processus de soutenance.

Fonctionnalités:

-   **Soumission d\'une Nouvelle Réclamation par l\'Étudiant:**

    -   Fournir une interface (ou les méthodes back-end pour une interface) permettant à un étudiant de créer une nouvelle réclamation.

    -   Exiger la saisie d\'un sujet clair pour la réclamation.

    -   Permettre la saisie d\'une description détaillée du problème ou de l\'objet de la réclamation.

    -   Permettre à l\'étudiant de sélectionner une catégorie ou un type de réclamation (ex: \"Erreur sur les notes\", \"Problème technique plateforme\", \"Désaccord avec décision de conformité\", \"Autre\") - nécessite un référentiel TypeReclamationRef.

    -   Permettre le téléversement de pièces jointes justificatives (captures d\'écran, documents) associées à la réclamation (stockées dans DocumentReclamation).

    -   Enregistrer la réclamation avec un statut initial (ex: \"Soumise\", \"En attente de traitement\") et assigner un numéro de suivi unique.

    -   Envoyer un accusé de réception automatique à l\'étudiant (via ServiceNotification et ServiceEmail), incluant le numéro de suivi.

-   **Gestion et Traitement des Réclamations par le Personnel Habilité:**

    -   Lister les réclamations soumises, avec des options de filtrage (par statut, par date, par type, par étudiant) et de tri pour le personnel administratif ou un rôle dédié au traitement des réclamations.

    -   Permettre l\'assignation d\'une réclamation à un agent traitant spécifique au sein du personnel.

    -   Permettre à l\'agent traitant de consulter tous les détails de la réclamation (informations de l\'étudiant, sujet, description, pièces jointes, historique des statuts).

    -   Permettre à l\'agent d\'ajouter des commentaires internes ou des notes de suivi non visibles par l\'étudiant.

    -   Mettre à jour le statut de la réclamation au fur et à mesure de son traitement (ex: \"En cours d\'investigation\", \"En attente de réponse d\'un tiers\", \"Résolution proposée\").

    -   Enregistrer la réponse officielle et détaillée à la réclamation.

    -   Clôturer la réclamation avec un statut final (ex: \"Résolue favorablement\", \"Résolue défavorablement\", \"Clôturée sans suite\", \"Transférée à un autre service\").

-   **Suivi des Réclamations par l\'Étudiant:**

    -   Permettre à l\'étudiant de consulter la liste de ses réclamations soumises.

    -   Afficher le statut actuel de chaque réclamation et l\'historique des changements de statut.

    -   Permettre à l\'étudiant de visualiser la réponse officielle apportée à sa réclamation une fois celle-ci traitée.

    -   Permettre à l\'étudiant d\'ajouter des informations complémentaires à une réclamation déjà soumise si le statut le permet (ex: si l\'agent demande plus de détails).

-   **Notifications et Communication:**

    -   Notifier l\'étudiant des changements importants de statut de sa réclamation (ex: \"En cours d\'investigation\", \"Réponse apportée\") via ServiceNotification et ServiceEmail.

    -   Notifier l\'agent traitant lorsqu\'une nouvelle réclamation lui est assignée ou lorsqu\'un étudiant ajoute des informations.

    -   Potentiellement, permettre un échange de messages simples entre l\'étudiant et l\'agent traitant au sein du contexte de la réclamation (mini-chat ou système de commentaires visibles).

-   **Archivage et Reporting sur les Réclamations:**

    -   Archiver les réclamations traitées et clôturées après une certaine période.

    -   Fournir des statistiques sur les réclamations (nombre par type, délai moyen de résolution, taux de satisfaction si mesuré) pour ServiceReportingAdmin.

*Modèles Utilisés et Mises à Jour Potentielles:* \* Reclamation: id_reclamation (PK), numero_carte_etudiant (FK), id_type_reclamation (FK vers TypeReclamationRef), sujet_reclamation (VARCHAR), description_reclamation (TEXT), date_soumission (DATETIME), id_statut_reclamation (FK vers StatutReclamationRef), reponse_officielle (TEXT, nullable), date_reponse_officielle (DATETIME, nullable), numero_agent_traitant (FK vers Utilisateur.numero_utilisateur, nullable), date_assignation_agent (DATETIME, nullable), date_cloture (DATETIME, nullable), priorite_reclamation (ENUM: \'basse\', \'moyenne\', \'haute\', nullable).
\* StatutReclamationRef: id_statut_reclamation (PK), code_statut_reclamation (UNIQUE), libelle_statut_reclamation (VARCHAR), description_statut (TEXT).
\* TypeReclamationRef (Nouvelle table référentiel): id_type_reclamation (PK), code_type_reclamation (UNIQUE), libelle_type_reclamation (VARCHAR).
\* DocumentReclamation (Nouvelle table pour pièces jointes): id_document_reclamation (PK), id_reclamation (FK), chemin_fichier_stockage (VARCHAR), nom_fichier_original (VARCHAR), type_mime (VARCHAR), taille_octet (BIGINT), date_upload (DATETIME), numero_utilisateur_upload (FK vers Utilisateur.numero_utilisateur).
\* CommentaireInterneReclamation (Nouvelle table potentielle): id_commentaire_interne (PK), id_reclamation (FK), numero_agent_commentaire (FK Utilisateur), commentaire (TEXT), date_commentaire (DATETIME).
\* Utilisateur: Pour identifier l\'étudiant soumettant et l\'agent traitant.

[]{#_Toc199281855 .anchor}**Service: ServiceDocumentGenerator.php**

Objectif: Gérer la génération dynamique et centralisée de documents PDF officiels (tels que attestations, reçus, bulletins de notes, procès-verbaux formatés) en utilisant des modèles HTML/CSS et les données spécifiques de l\'application.

Fonctionnalités:

-   **Récupération des Modèles de Documents:**

    -   Accéder aux modèles de documents HTML/CSS (stockés et gérés via ServiceConfigurationSysteme dans la table ModeleDocumentPdf ou sur le système de fichiers).

    -   Sélectionner le modèle approprié en fonction du type de document à générer (ex: \'ATTESTATION_DEPOT_RAPPORT\', \'PV_VALIDATION_COMMISSION_INDIVIDUEL\', \'RELEVE_NOTES_SEMESTRIEL\').

-   **Collecte des Données Nécessaires:**

    -   Interagir avec les services métier appropriés pour récupérer toutes les données dynamiques nécessaires à la population du modèle. Exemples :

        -   Pour une attestation de dépôt : ServiceRapport (détails du rapport, date de soumission), ServiceAuthentification (informations de l\'étudiant).

        -   Pour un PV de commission : ServiceCommission (détails du PV, décisions, recommandations), ServiceRapport (détails du rapport concerné), ServiceAuthentification (informations des membres de la commission).

        -   Pour un relevé de notes : ServiceGestionAcademique (notes de l\'étudiant, détails des ECUE/UE), ServiceAuthentification (informations de l\'étudiant).

    -   Formater les données récupérées si nécessaire pour qu\'elles correspondent aux placeholders attendus par le modèle HTML.

-   **Population du Modèle (Templating):**

    -   Utiliser un moteur de templating (ex: Twig, Blade si intégré dans un framework, ou une simple substitution de placeholders) pour injecter les données collectées dans le modèle HTML.

    -   Gérer la logique conditionnelle simple (ex: afficher une section si une donnée est présente) et les boucles (ex: lister les notes dans un relevé) au sein des modèles.

-   **Conversion HTML vers PDF:**

    -   Utiliser une bibliothèque de conversion HTML vers PDF robuste (ex: Dompdf, TCPDF, wkhtmltopdf) pour transformer le document HTML populé en un fichier PDF.

    -   Gérer la configuration de la conversion PDF (format de page, orientation, marges, en-têtes/pieds de page si définis dans le modèle ou globalement).

    -   Assurer la bonne restitution des styles CSS, des images embarquées et des polices de caractères dans le PDF.

-   **Stockage et/ou Retour du Document Généré:**

    -   **Option 1 (Stockage):**

        -   Sauvegarder le fichier PDF généré sur le serveur dans un emplacement sécurisé et organisé.

        -   Enregistrer les métadonnées du document généré dans une table dédiée (ex: DocumentOfficielGenere) incluant le type de document, l\'utilisateur concerné, la date de génération, le chemin du fichier, et potentiellement les paramètres de génération pour reproductibilité.

        -   Renvoyer l\'identifiant ou le chemin du document stocké.

    -   **Option 2 (Flux direct):**

        -   Renvoyer directement le flux binaire du fichier PDF au service appelant (ex: pour un téléchargement immédiat par l\'utilisateur via un contrôleur), sans forcément le stocker de manière persistante à chaque génération (sauf si requis pour archivage).

-   **Gestion des Erreurs:**

    -   Capturer et journaliser les erreurs pouvant survenir durant le processus (modèle non trouvé, données manquantes, échec de la conversion PDF).

-   **Sécurité et Accès:**

    -   S\'assurer que la génération de documents est initiée par des services ou des utilisateurs ayant les droits appropriés.

    -   Protéger l\'accès aux modèles et aux documents générés.

-   **Personnalisation et Estampillage (Fonctionnalités Avancées):**

    -   Permettre l\'ajout dynamique d\'éléments comme des codes QR, des numéros de version, des dates de génération, ou des signatures numérisées (si disponibles et sécurisées) sur les documents.

    -   Appliquer un filigrane \"Brouillon\" ou \"Non Officiel\" si le document est généré avant une validation finale.

*Modèles Utilisés et Mises à Jour Potentielles:* \* ModeleDocumentPdf (défini et géré par ServiceConfigurationSysteme): id_modele_pdf (PK), code_modele (UNIQUE), nom_modele, type_document_officiel_associe, chemin_template_html, chemin_template_css, description_modele, version_modele.
\* DocumentOfficielGenere (Nouvelle table pour tracer les documents effectivement générés et stockés): id_doc_genere (PK), numero_utilisateur_concerne (FK vers Utilisateur.numero_utilisateur, nullable si document non lié à un utilisateur unique), id_entite_principale_concernee (VARCHAR/INT, ex: id_rapport_etudiant, id_compte_rendu, id_inscription), type_entite_principale (VARCHAR), type_document_officiel (VARCHAR, ex: \'ATTESTATION_DEPOT\', \'PV_INDIVIDUEL\'), date_generation (DATETIME), chemin_fichier_pdf_stocke (VARCHAR), taille_octet_pdf (BIGINT), checksum_pdf (VARCHAR, pour intégrité), parametres_generation_json (JSON, pour audit/reproductibilité), genere_par_numero_utilisateur_systeme (FK Utilisateur, si action manuelle d\'un admin).
\* Tous les modèles de données de l\'application qui fournissent les informations à injecter dans les documents (ex: Etudiant, RapportEtudiant, CompteRendu, Evaluer, Inscrire, etc.).
