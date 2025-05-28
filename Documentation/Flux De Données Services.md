# Table des matières {#table-des-matières .TOC-Heading}

[**1. ServiceAuthentification.php** [1](#_Toc199281787)](#_Toc199281787)

[**2. ServiceCommission.php** [2](#_Toc199281788)](#_Toc199281788)

[**3. ServiceConfigurationSysteme.php**
[4](#_Toc199281789)](#_Toc199281789)

[**4. ServiceConformite.php** [5](#_Toc199281790)](#_Toc199281790)

[**5. ServiceGestionAcademique.php**
[6](#_Toc199281791)](#_Toc199281791)

[**6. ServiceMessagerie.php** [7](#_Toc199281792)](#_Toc199281792)

[**7. ServiceNotification.php** [8](#_Toc199281793)](#_Toc199281793)

[**8. ServicePermissions.php** [9](#_Toc199281794)](#_Toc199281794)

[**9. ServiceRapport.php** [10](#_Toc199281795)](#_Toc199281795)

[**10. ServiceReportingAdmin.php** [12](#_Toc199281796)](#_Toc199281796)

[**11. ServiceSupervisionAdmin.php**
[13](#_Toc199281797)](#_Toc199281797)

[**12. ServiceEmail.php** [14](#_Toc199281798)](#_Toc199281798)

[**13. ServiceReclamation.php** [15](#_Toc199281799)](#_Toc199281799)

[**14. ServiceDocumentGenerator.php**
[16](#_Toc199281800)](#_Toc199281800)

**Légende pour chaque service :**

-   **Déclencheurs / Données d\'Entrée :** Ce qui initie l\'action du service (souvent via un contrôleur ou un autre service) et les données principales reçues.

-   **Modèles Lus (R) :** Principaux modèles de données consultés par le service.

-   **Modèles Écrits/Modifiés (W/U) :** Principaux modèles de données créés ou mis à jour par le service.

-   **Services Appelants Fréquents :** Services qui typiquement appellent ce service.

-   **Services Appelés (Svc Call) :** Autres services que ce service appelle pour déléguer des tâches.

-   **Principales Sorties / Effets :** Ce que le service produit (objets de données, statuts, notifications, fichiers).

-   **Flux de Données Clés :** Description narrative du cheminement des données.

[]{#_Toc199281787 .anchor}**1. ServiceAuthentification.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Requête de connexion (login, mot de passe).

    -   Demande de création de compte (données utilisateur et profil).

    -   Demande de réinitialisation de mot de passe (email/login).

    -   Mise à jour de profil/mot de passe (données utilisateur).

    -   Actions d\'administration (création/modification/suppression d\'utilisateur).

-   **Modèles Lus (R) :**

    -   Utilisateur (pour vérification login, statut, tokens, permissions de base)

    -   Etudiant, Enseignant, PersonnelAdministratif (pour récupérer les détails du profil)

    -   TypeUtilisateur, GroupeUtilisateur, NiveauAccesDonne (pour informations de rôle/groupe)

    -   HistoriqueMotDePasse (pour vérifier les anciens mots de passe)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Utilisateur (W: nouveau compte; U: mot de passe, statut, dernière connexion, tokens, tentatives échouées, photo)

    -   Etudiant, Enseignant, PersonnelAdministratif (W: nouveau profil; U: détails du profil)

    -   HistoriqueMotDePasse (W: lors du changement de mot de passe)

-   **Services Appelants Fréquents :** Contrôleurs (LoginController, UserController, AdminUserController).

-   **Services Appelés (Svc Call) :**

    -   ServiceEmail (pour email de bienvenue, validation email, réinitialisation de mot de passe).

    -   ServiceGestionAcademique (pour vérifier statut scolarité avant création compte étudiant).

    -   ServiceSupervisionAdmin (ou directement Enregistrer) (pour journaliser les tentatives de connexion, créations de compte).

    -   ServicePermissions (pour récupérer les droits de base après connexion).

-   **Principales Sorties / Effets :**

    -   Session utilisateur établie/détruite.

    -   Objet Utilisateur avec son profil.

    -   Statut de succès/échec d\'authentification.

    -   Emails envoyés.

    -   Comptes créés/modifiés/supprimés.

-   **Flux de Données Clés :**

    i.  **Connexion :** Login/mdp -\> ServiceAuthentification -\> R: Utilisateur (vérif mdp haché, statut) -\> Si succès: U: Utilisateur (dernière connexion), création session, Svc Call: ServicePermissions (droits) -\> Retour Objet Utilisateur/Session. Si échec: U: Utilisateur (tentatives), Svc Call: ServiceSupervisionAdmin (log).

    ii. **Création Compte Étudiant :** Données formulaire -\> ServiceAuthentification -\> Svc Call: ServiceGestionAcademique (vérif scolarité) -\> Si OK: W: Utilisateur, W: Etudiant -\> Svc Call: ServiceEmail (bienvenue), Svc Call: ServiceSupervisionAdmin (log) -\> Retour Statut.

    iii. **Reset MDP :** Email -\> ServiceAuthentification -\> R: Utilisateur (trouver user) -\> U: Utilisateur (token reset) -\> Svc Call: ServiceEmail (lien reset) -\> Retour Statut.

[]{#_Toc199281788 .anchor}**2. ServiceCommission.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Rapports avec statut \"Transmis à la commission\" (via ServiceConformite).

    -   Actions des membres de la commission (vote, rédaction PV, validation PV).

    -   Soumission de corrections par l\'étudiant (via ServiceRapport).

    -   Données de planification de session.

-   **Modèles Lus (R) :**

    -   RapportEtudiant (détails du rapport à évaluer/corriger)

    -   DocumentSoumis (fichiers du rapport)

    -   Utilisateur, Enseignant (pour infos membres commission, directeurs mémoire)

    -   VoteCommission (pour suivre les votes)

    -   CompteRendu (PV existants)

    -   ValidationPv (pour suivi validation PV)

    -   SessionCommission (pour sessions planifiées)

    -   StatutRapportRef, StatutPvRef, DecisionVoteRef, DecisionValidationPvRef (référentiels)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   VoteCommission (W: enregistrement des votes)

    -   RapportEtudiant (U: directeur_memoire_confirme_par_commission, encadreur_pedagogique_confirme_par_commission, date_decision_commission, decision_commission_finale, recommandations_commission)

    -   CompteRendu (PV) (W: nouveau PV; U: statut PV, contenu)

    -   ValidationPv (W: enregistrement validation/rejet PV)

    -   SessionCommission (W: nouvelle session)

    -   PvSessionRapport (W: liaison PV de session et rapports)

    -   Affecter (U: pour confirmer rôles jury/commission)

-   **Services Appelants Fréquents :** Contrôleurs (CommissionController, MembreCommissionController), ServiceConformite (indirectement en changeant statut rapport), ServiceRapport (pour corrections).

-   **Services Appelés (Svc Call) :**

    -   ServiceRapport (pour mettre à jour le statut global du rapport).

    -   ServiceNotification (pour notifier étudiants des décisions, membres des tâches).

    -   ServiceEmail (pour emails de notification, via ServiceNotification).

    -   ServiceMessagerie (potentiellement pour concertation).

    -   ServiceConfigurationSysteme (pour lire nb max tours de vote, etc.).

    -   ServiceDocumentGenerator (pour générer le PDF du PV final).

-   **Principales Sorties / Effets :**

    -   Décisions de la commission enregistrées.

    -   Statuts des rapports et PV mis à jour.

    -   PV créés et validés (potentiellement en PDF).

    -   Notifications envoyées.

-   **Flux de Données Clés :**

    i.  **Vote sur Rapport :** Rapport \"Transmis\" -\> ServiceCommission -\> R: RapportEtudiant, DocumentSoumis (consultation) -\> Action membre: Vote -\> W: VoteCommission -\> Si fin vote: décision -\> Svc Call: ServiceRapport (U: statut rapport), Svc Call: ServiceNotification (étudiant).

    ii. **Rédaction PV :** Décision rapport -\> ServiceCommission -\> W: CompteRendu (brouillon) -\> Action membre: Soumission PV -\> U: CompteRendu (statut \"Soumis validation\") -\> Svc Call: ServiceNotification (membres pour validation).

    iii. **Validation PV :** PV \"Soumis validation\" -\> ServiceCommission -\> Action membre: Valide/Rejette PV -\> W: ValidationPv -\> Si PV validé: U: CompteRendu (statut \"Validé\"), Svc Call: ServiceRapport (U: statut rapport final), Svc Call: ServiceNotification (étudiant), Svc Call: ServiceDocumentGenerator (PDF PV).

[]{#_Toc199281789 .anchor}**3. ServiceConfigurationSysteme.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Actions d\'administration via interface de configuration (données de formulaires pour CRUD référentiels, paramètres, modèles).

-   **Modèles Lus (R) :**

    -   Tous les 14+ modèles de référentiels (pour affichage, mise à jour).

    -   ParametreApplication (pour affichage, mise à jour).

    -   ModeleDocumentPdf (pour affichage, mise à jour).

    -   Message (modèles de communication, pour affichage, mise à jour).

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Tous les 14+ modèles de référentiels (W/U/D: gestion complète).

    -   ParametreApplication (W/U/D).

    -   ModeleDocumentPdf (W/U/D).

    -   Message (W/U/D).

-   **Services Appelants Fréquents :** Contrôleurs (AdminConfigurationController). Tous les autres services peuvent potentiellement lire des configurations via ce service.

-   **Services Appelés (Svc Call) :** Aucun service majeur appelé. Pourrait appeler ServiceSupervisionAdmin pour journaliser les changements de configuration critiques.

-   **Principales Sorties / Effets :**

    -   Référentiels mis à jour.

    -   Paramètres applicatifs modifiés.

    -   Modèles de documents et de communication gérés.

    -   Configurations disponibles pour les autres services.

-   **Flux de Données Clés :**

    i.  **CRUD Référentiel :** Action Admin (ex: créer nouvelle Specialite) -\> ServiceConfigurationSysteme -\> W: Specialite -\> Retour Statut.

    ii. **Lire Paramètre :** Autre Service (ex: ServiceCommission a besoin de VOTE_COMMISSION_NB_TOURS_MAX) -\> ServiceConfigurationSysteme -\> R: ParametreApplication (cle_parametre = \'VOTE_COMMISSION_NB_TOURS_MAX\') -\> Retour valeur_parametre.

    iii. **Upload Modèle PDF :** Action Admin (upload fichier HTML) -\> ServiceConfigurationSysteme -\> Sauvegarde fichier sur serveur, W: ModeleDocumentPdf (chemin, métadonnées) -\> Retour Statut.

[]{#_Toc199281790 .anchor}**4. ServiceConformite.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Rapports avec statut \"Soumis pour vérification de conformité\" (via ServiceRapport).

    -   Actions de l\'agent de conformité (décision, motifs).

-   **Modèles Lus (R) :**

    -   RapportEtudiant (détails du rapport à vérifier)

    -   DocumentSoumis (fichiers du rapport)

    -   Utilisateur, Etudiant (pour infos étudiant)

    -   StatutConformiteRef, StatutRapportRef (référentiels)

    -   ParametreApplication (via ServiceConfigurationSysteme, pour les règles de conformité)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   DecisionConformite (anciennement Approuver) (W: enregistrement décision, motifs)

-   **Services Appelants Fréquents :** Contrôleurs (ConformiteController), ServiceRapport (indirectement en changeant statut rapport).

-   **Services Appelés (Svc Call) :**

    -   ServiceRapport (pour mettre à jour le statut global du rapport).

    -   ServiceNotification (pour notifier étudiant de la décision).

    -   ServiceEmail (via ServiceNotification).

    -   ServiceGestionAcademique (pour vérifier statut scolarité étudiant).

    -   ServiceConfigurationSysteme (pour lire les règles de conformité).

-   **Principales Sorties / Effets :**

    -   Décision de conformité enregistrée.

    -   Statut du rapport mis à jour (soit transmis à commission, soit retourné à étudiant pour corrections).

    -   Notifications envoyées.

-   **Flux de Données Clés :**

    i.  **Vérification Rapport :** Rapport \"Soumis\" -\> ServiceConformite -\> R: RapportEtudiant, DocumentSoumis, Svc Call: ServiceGestionAcademique (vérif statut) -\> Action Agent: Décision (Conforme/Incomplet) + Motifs -\> W: DecisionConformite -\> Svc Call: ServiceRapport (U: statut rapport) -\> Svc Call: ServiceNotification (étudiant) -\> Retour Statut.

[]{#_Toc199281791 .anchor}**5. ServiceGestionAcademique.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Actions du personnel de scolarité (inscription, saisie de notes, gestion stages, affectations enseignants).

    -   Demandes d\'autres services pour vérification de statut académique.

-   **Modèles Lus (R) :**

    -   Etudiant, Enseignant

    -   Inscrire, Evaluer, FaireStage, Acquerir, Occuper, Attribuer (pour consultation et mise à jour)

    -   NiveauEtude, AnneeAcademique, Entreprise, Grade, Fonction, Specialite, Ecue, Ue (référentiels)

    -   StatutPaiementRef, DecisionPassageRef

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Inscrire (W/U)

    -   Evaluer (W/U)

    -   FaireStage (W/U)

    -   Acquerir (W/U)

    -   Occuper (W/U)

    -   Attribuer (W/U)

    -   Potentiellement Etudiant, Enseignant pour des champs académiques spécifiques.

-   **Services Appelants Fréquents :** Contrôleurs (ScolariteController, AdminEnseignantController), ServiceAuthentification (pour vérif scolarité), ServiceConformite (pour vérif scolarité).

-   **Services Appelés (Svc Call) :**

    -   ServiceDocumentGenerator (pour générer attestations, relevés de notes).

    -   ServiceSupervisionAdmin (pour journaliser les modifications importantes).

-   **Principales Sorties / Effets :**

    -   Données académiques (inscriptions, notes, stages, affectations) créées/mises à jour.

    -   Informations de statut académique fournies à d\'autres services.

    -   Documents académiques générés (PDFs).

-   **Flux de Données Clés :**

    i.  **Inscription Étudiant :** Données formulaire scolarité -\> ServiceGestionAcademique -\> W: Inscrire -\> Retour Statut.

    ii. **Saisie Note :** Données formulaire notes -\> ServiceGestionAcademique -\> W: Evaluer -\> Retour Statut.

    iii. **Vérif Statut Scolarité (par autre service) :** ServiceAuthentification (besoin info pour numero_etudiant) -\> ServiceGestionAcademique -\> R: Inscrire, Etudiant (pour numero_etudiant sur annee_active) -\> Retour Statut Scolarité (booléen/objet).

Nous allons continuer avec les prochains services. Cela vous convient-il jusqu\'à présent ?

[]{#_Toc199281792 .anchor}**6. ServiceMessagerie.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Actions utilisateur (créer conversation, envoyer message, lire message).

    -   Demandes d\'autres services (ex: ServiceCommission pour créer un canal de discussion).

    -   Données de message (texte, fichier joint).

-   **Modèles Lus (R) :**

    -   Conversation (pour lister conversations, trouver une conversation existante)

    -   ParticipantConversation (pour vérifier appartenance, rôles, statut de lecture)

    -   MessageChat (pour récupérer historique des messages)

    -   Utilisateur (pour infos expéditeur/destinataire)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Conversation (W: nouvelle conversation; U: date_derniere_activite)

    -   ParticipantConversation (W: nouveaux participants; U: date_derniere_lecture, role_dans_conversation)

    -   MessageChat (W: nouveau message)

    -   LectureMessage (ou mise à jour dans ParticipantConversation) (W/U: pour marquer messages lus)

-   **Services Appelants Fréquents :** Contrôleurs (ChatController), ServiceCommission (potentiellement).

-   **Services Appelés (Svc Call) :**

    -   ServiceNotification (pour notifier les nouveaux messages si destinataire hors ligne ou pour mentions).

    -   ServiceEmail (via ServiceNotification).

    -   ServiceConfigurationSysteme (pour lire limites taille/type pièces jointes, durée rétention).

-   **Principales Sorties / Effets :**

    -   Conversations créées/mises à jour.

    -   Messages envoyés et stockés.

    -   Statuts de lecture mis à jour.

    -   Notifications de nouveaux messages.

-   **Flux de Données Clés :**

    i.  **Envoyer Message :** Données message (texte, id_conversation, numero_expediteur) -\> ServiceMessagerie -\> W: MessageChat -\> U: Conversation (date_derniere_activite) -\> Svc Call: ServiceNotification (si destinataire hors ligne) -\> Retour Statut Message (ou message lui-même pour affichage temps réel).

    ii. **Lire Conversation :** id_conversation -\> ServiceMessagerie -\> R: MessageChat (paginé), R: ParticipantConversation (pour infos membres) -\> U: ParticipantConversation (date_derniere_lecture pour l\'utilisateur actuel) -\> Retour Liste Messages.

    iii. **Créer Conversation Groupe :** Titre, liste participants -\> ServiceMessagerie -\> W: Conversation -\> W: ParticipantConversation (pour chaque participant) -\> Retour id_conversation.

[]{#_Toc199281793 .anchor}**7. ServiceNotification.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Appels d\'autres services métier suite à des événements (ex: rapport soumis, PV validé, nouveau message chat).

    -   Données de notification (numero_utilisateur destinataire, id_notification (type), message complémentaire, lien).

    -   Actions utilisateur (marquer comme lu).

-   **Modèles Lus (R) :**

    -   Notification (types de notif, pour récupérer les modèles de message associés, canal)

    -   Message (via ServiceConfigurationSysteme, pour le contenu formaté)

    -   Utilisateur (pour préférences email, email du destinataire)

    -   GroupeUtilisateur (pour identifier destinataires d\'un groupe)

    -   Recevoir (pour lister notifications, marquer comme lues)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Recevoir (W: nouvelle notification instanciée; U: lue, date_lecture)

-   **Services Appelants Fréquents :** Tous les autres services métier (ServiceRapport, ServiceCommission, ServiceConformite, ServiceMessagerie, ServiceAuthentification, ServiceReclamation).

-   **Services Appelés (Svc Call) :**

    -   ServiceEmail (pour envoyer la notification par email si configuré).

    -   ServiceConfigurationSysteme (pour récupérer les modèles Message).

-   **Principales Sorties / Effets :**

    -   Notifications enregistrées dans la base de données.

    -   Emails de notification envoyés.

    -   Nombre de notifications non lues disponible.

-   **Flux de Données Clés :**

    i.  **Un Service Déclenche Notification (ex: ServiceRapport après soumission) :** ServiceRapport (événement: rapport soumis) -\> Svc Call: ServiceNotification (avec numero_etudiant, id_notification_type = \'RAPPORT_SOUMIS\', lien vers rapport) -\> ServiceNotification:
        > a. R: Notification (type \'RAPPORT_SOUMIS\') pour obtenir id_message_template_systeme et id_message_template_email.
        > b. Svc Call: ServiceConfigurationSysteme pour R: Message (contenu des templates).
        > c. W: Recevoir (création de la notif in-app pour l\'étudiant).
        > d. R: Utilisateur (préférences email de l\'étudiant).
        > e. Si email activé: Svc Call: ServiceEmail (envoi de l\'email formaté).
        > f. Retour Statut à ServiceRapport.

    ii. **Consulter Notifications :** Requête utilisateur -\> ServiceNotification -\> R: Recevoir (notifications pour numero_utilisateur) -\> Retour Liste Notifications.

    iii. **Marquer Lu :** id_recevoir -\> ServiceNotification -\> U: Recevoir (lue=true, date_lecture) -\> Retour Statut.

[]{#_Toc199281794 .anchor}**8. ServicePermissions.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Vérification d\'accès par d\'autres services ou contrôleurs (ex: avant d\'exécuter une action de contrôleur).

    -   Demande de la liste des traitements autorisés (pour affichage menu).

    -   Actions d\'administration (gestion des groupes, types, permissions).

-   **Modèles Lus (R) :**

    -   Utilisateur (pour id_groupe_utilisateur, id_type_utilisateur)

    -   Rattacher (pour vérifier les associations groupe \<-\> traitement)

    -   Traitement (pour lister tous les traitements ou détails d\'un traitement)

    -   GroupeUtilisateur, TypeUtilisateur, NiveauAccesDonne (pour gestion CRUD et affichage)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Rattacher (W/D: pour accorder/révoquer permissions)

    -   GroupeUtilisateur (W/U/D)

    -   TypeUtilisateur (W/U/D)

    -   NiveauAccesDonne (W/U/D)

    -   Traitement (W/U/D, si géré ici et non seulement dans ServiceConfigurationSysteme)

-   **Services Appelants Fréquents :** Contrôleurs (avant chaque action sensible), ServiceAuthentification (pour charger droits de base), autres services (pour vérifier si une sous-action est permise).

-   **Services Appelés (Svc Call) :**

    -   ServiceSupervisionAdmin (pour journaliser les modifications de permissions).

-   **Principales Sorties / Effets :**

    -   Booléen (accès autorisé/refusé).

    -   Liste des traitements autorisés.

    -   Permissions, groupes, types, niveaux d\'accès mis à jour.

-   **Flux de Données Clés :**

    i.  **Vérifier Permission :** Contrôleur (avant action) ou Service (avant opération) -\> Svc Call: ServicePermissions (avec numero_utilisateur, code_traitement) -\> ServicePermissions:
        > a. R: Utilisateur (id_groupe_utilisateur pour numero_utilisateur).
        > b. R: Traitement (id_traitement pour code_traitement).
        > c. R: Rattacher (existe-t-il une ligne pour id_groupe_utilisateur et id_traitement ?).
        > d. Retour booléen (true/false).

    ii. **Accorder Permission :** Action Admin (formulaire) -\> ServicePermissions -\> W: Rattacher (nouvelle ligne id_groupe_utilisateur, id_traitement) -\> Svc Call: ServiceSupervisionAdmin (log) -\> Retour Statut.

[]{#_Toc199281795 .anchor}**9. ServiceRapport.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Actions de l\'étudiant (créer rapport, soumettre, téléverser fichiers, soumettre corrections).

    -   Décisions de ServiceConformite et ServiceCommission (changements de statut).

-   **Modèles Lus (R) :**

    -   RapportEtudiant (pour consultation, mise à jour)

    -   DocumentSoumis (pour consultation, gestion des versions)

    -   Etudiant, AnneeAcademique, Utilisateur (infos contextuelles)

    -   StatutRapportRef, TypeDocumentRef (référentiels)

    -   HistoriqueStatutRapport (pour consultation historique)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   RapportEtudiant (W: nouveau rapport; U: statut, dates, note finale, URL version validée)

    -   DocumentSoumis (W: nouveaux fichiers/versions)

    -   HistoriqueStatutRapport (W: à chaque changement de statut)

-   **Services Appelants Fréquents :** Contrôleurs (RapportController, EtudiantController), ServiceConformite, ServiceCommission.

-   **Services Appelés (Svc Call) :**

    -   ServiceNotification (pour accuser réception soumission, notifier changements de statut).

    -   ServiceEmail (via ServiceNotification).

    -   ServiceSupervisionAdmin (pour journaliser les actions importantes sur les rapports).

-   **Principales Sorties / Effets :**

    -   Rapports créés/mis à jour.

    -   Documents téléversés et versionnés.

    -   Statuts de rapport mis à jour de manière centralisée.

    -   Historique des statuts tracé.

-   **Flux de Données Clés :**

    i.  **Soumission Rapport Étudiant :** Données formulaire + Fichier(s) -\> ServiceRapport -\> W: RapportEtudiant (statut \"Brouillon\" ou \"Soumis\"), W: DocumentSoumis -\> U: RapportEtudiant (statut \"Soumis pour vérif conformité\"), W: HistoriqueStatutRapport -\> Svc Call: ServiceNotification (accusé réception) -\> Retour Statut.

    ii. **Changement Statut par Autre Service (ex: ServiceConformite valide) :** ServiceConformite (décision: \"Conforme\") -\> Svc Call: ServiceRapport (avec id_rapport, nouveau statut \"Transmis commission\") -\> ServiceRapport:
        > a. U: RapportEtudiant (id_statut_rapport).
        > b. W: HistoriqueStatutRapport.
        > c. Retour Statut à ServiceConformite.

    iii. **Soumettre Corrections :** Fichier(s) corrigé(s) + id_rapport -\> ServiceRapport -\> W: DocumentSoumis (nouvelle version) -\> U: RapportEtudiant (statut \"Corrections soumises - En attente re-validation\"), W: HistoriqueStatutRapport -\> Svc Call: ServiceNotification (notification à l\'instance concernée) -\> Retour Statut.

Cela couvre les 9 premiers services. Prêt pour les 3 derniers (ServiceReportingAdmin, ServiceSupervisionAdmin, ServiceEmail, ServiceReclamation, ServiceDocumentGenerator) ? Nous en ferons 3 pour le prochain lot si vous le souhaitez.

[]{#_Toc199281796 .anchor}**10. ServiceReportingAdmin.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Demandes de l\'administration/direction pour des rapports spécifiques (via une interface de reporting).

    -   Paramètres de rapport (période, filtres).

-   **Modèles Lus (R) :**

    -   RapportEtudiant (source principale pour les rapports)

    -   HistoriqueStatutRapport (crucial pour les délais, flux)

    -   AnneeAcademique, NiveauEtude, Filiere

    -   Utilisateur, Etudiant, Enseignant, PersonnelAdministratif (pour statistiques d\'acteurs)

    -   Enregistrer (logs d\'actions, pour utilisation plateforme)

    -   VoteCommission, DecisionConformite, CompteRendu (PV) (pour analyses de décisions)

    -   MotCle, RapportMotCle (pour analyse thématique)

    -   RapportSauvegarde (pour lister/charger des rapports pré-configurés)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   RapportSauvegarde (W: si l\'utilisateur sauvegarde une configuration de rapport)

    -   *Principalement en lecture, ne modifie pas les données métier sources.*

-   **Services Appelants Fréquents :** Contrôleurs (ReportingController, AdminDashboardController).

-   **Services Appelés (Svc Call) :**

    -   Aucun service majeur appelé pour la génération de données. Pourrait appeler ServiceDocumentGenerator si les rapports finaux sont des PDF formatés complexes, ou exporter directement en CSV/Excel.

-   **Principales Sorties / Effets :**

    -   Données statistiques agrégées.

    -   Tableaux de bord avec KPIs.

    -   Fichiers de rapport exportés (CSV, Excel, PDF).

-   **Flux de Données Clés :**

    i.  **Générer Rapport Délais Traitement :** Requête Admin (période, type rapport) -\> ServiceReportingAdmin -\> R: HistoriqueStatutRapport, R: RapportEtudiant (pour joindre infos) -\> Calculs statistiques (délais moyens, médians par étape) -\> Retour Données Agrégées (pour affichage tableau/graphique ou export).

    ii. **Générer Rapport Taux Validation :** Requête Admin (année académique) -\> ServiceReportingAdmin -\> R: RapportEtudiant (filtré par année, en comptant statuts \'Validé\', \'Refusé\', \'Soumis\') -\> Calculs de taux -\> Retour Données Agrégées.

[]{#_Toc199281797 .anchor}**11. ServiceSupervisionAdmin.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Accès de l\'administrateur aux tableaux de bord de supervision et aux journaux.

    -   Paramètres de filtrage pour les journaux.

    -   Demandes de lancement de tâches de maintenance.

-   **Modèles Lus (R) :**

    -   RapportEtudiant, HistoriqueStatutRapport (pour tableau de bord workflow)

    -   Enregistrer (pour journal des actions utilisateurs)

    -   Action (référentiel des types d\'actions)

    -   Pister (pour journal des accès aux fonctionnalités)

    -   Traitement (référentiel des fonctionnalités)

    -   CompteRendu (PV), StatutPvRef (pour supervision des PV)

    -   Utilisateur (pour infos sur les acteurs dans les logs)

    -   ArchivePV (pour lister PV archivés)

    -   AlerteSysteme (pour afficher alertes)

    -   LogTachePlanifiee (pour statut des tâches automatisées)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   ArchivePV (W: lors de l\'archivage officiel d\'un PV)

    -   AlerteSysteme (U: pour marquer statut \'lu\', \'résolu\')

    -   CompteRendu (U: pour changer statut en \'Archivé Officiellement\')

    -   *Principalement en lecture pour la supervision, écritures limitées à des actions de maintenance/archivage.*

-   **Services Appelants Fréquents :** Contrôleurs (AdminSupervisionController, AdminMaintenanceController). Tous les autres services peuvent appeler ce service (ou directement la table Enregistrer) pour journaliser des actions.

-   **Services Appelés (Svc Call) :**

    -   Peut appeler des scripts système ou des procédures stockées pour des tâches de maintenance lourdes (si applicable et exposé).

-   **Principales Sorties / Effets :**

    -   Affichage de tableaux de bord de supervision.

    -   Consultation des journaux d\'audit et d\'accès.

    -   PV archivés.

    -   Alertes système gérées.

-   **Flux de Données Clés :**

    i.  **Consulter Log Actions :** Requête Admin (filtres: utilisateur, date) -\> ServiceSupervisionAdmin -\> R: Enregistrer (avec jointure sur Utilisateur, Action) -\> Retour Liste Logs Actions.

    ii. **Archiver PV :** Requête Admin (id_compte_rendu à archiver) -\> ServiceSupervisionAdmin -\> R: CompteRendu (pour récupérer contenu) -\> W: ArchivePV (copie infos + contenu), U: CompteRendu (statut \'Archivé Officiellement\') -\> Retour Statut.

    iii. **Journaliser Action (par autre service) :** Autre Service (ex: ServiceRapport crée un rapport) -\> Svc Call: ServiceSupervisionAdmin (ou directement W: Enregistrer) avec (numero_utilisateur, id_action=\'CREATION_RAPPORT\', id_entite=id_nouveau_rapport, type_entite=\'RapportEtudiant\', détails JSON) -\> ServiceSupervisionAdmin -\> W: Enregistrer.

[]{#_Toc199281798 .anchor}**12. ServiceEmail.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Appels d\'autres services nécessitant l\'envoi d\'un email (ex: ServiceNotification, ServiceAuthentification).

    -   Données de l\'email (destinataire(s), sujet, corps, pièces jointes, ou code de modèle et variables).

-   **Modèles Lus (R) :**

    -   Message (via ServiceConfigurationSysteme, pour les templates d\'email)

    -   Utilisateur (pour récupérer l\'email du destinataire si seul numero_utilisateur est fourni)

    -   ParametreApplication (via ServiceConfigurationSysteme, pour config SMTP, expéditeur par défaut)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   LogEmail (W: pour journaliser chaque tentative d\'envoi)

-   **Services Appelants Fréquents :** ServiceNotification (principalement), ServiceAuthentification (pour emails directs de reset/bienvenue).

-   **Services Appelés (Svc Call) :**

    -   ServiceConfigurationSysteme (pour récupérer modèles Message et config SMTP).

    -   Bibliothèque d\'envoi d\'email externe (ex: PHPMailer).

-   **Principales Sorties / Effets :**

    -   Emails envoyés au serveur SMTP ou à l\'API d\'email.

    -   Logs d\'envoi créés.

-   **Flux de Données Clés :**

    i.  **Envoyer Email via Template (par ServiceNotification) :** ServiceNotification -\> Svc Call: ServiceEmail (avec email_destinataire, code_message_template, variables_template) -\> ServiceEmail:
        > a. Svc Call: ServiceConfigurationSysteme pour R: Message (code_message_template) et R: ParametreApplication (config SMTP).
        > b. Préparation email (sujet/corps avec variables injectées).
        > c. Appel librairie externe (PHPMailer) pour envoyer l\'email.
        > d. W: LogEmail (succès/échec).
        > e. Retour Statut à ServiceNotification.

[]{#_Toc199281799 .anchor}**13. ServiceReclamation.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Actions de l\'étudiant (soumettre réclamation, ajouter infos).

    -   Actions du personnel (assigner, commenter, répondre, clôturer).

    -   Données de la réclamation (sujet, description, type, pièces jointes).

-   **Modèles Lus (R) :**

    -   Reclamation (pour consultation, mise à jour)

    -   DocumentReclamation (pour consultation des PJs)

    -   Utilisateur, Etudiant (infos contextuelles)

    -   StatutReclamationRef, TypeReclamationRef (référentiels)

    -   CommentaireInterneReclamation (pour consultation)

-   **Modèles Écrits/Modifiés (W/U) :**

    -   Reclamation (W: nouvelle réclamation; U: statut, agent traitant, réponse, dates)

    -   DocumentReclamation (W: nouvelles PJs)

    -   CommentaireInterneReclamation (W: nouveau commentaire interne)

-   **Services Appelants Fréquents :** Contrôleurs (ReclamationController, EtudiantReclamationController, AdminReclamationController).

-   **Services Appelés (Svc Call) :**

    -   ServiceNotification (pour accusé réception, notifier changements de statut, assignation).

    -   ServiceEmail (via ServiceNotification).

    -   ServiceSupervisionAdmin (pour journaliser les actions importantes sur les réclamations).

-   **Principales Sorties / Effets :**

    -   Réclamations créées/mises à jour.

    -   Pièces jointes stockées.

    -   Notifications envoyées.

-   **Flux de Données Clés :**

    i.  **Soumission Réclamation Étudiant :** Données formulaire + Fichier(s) -\> ServiceReclamation -\> W: Reclamation (statut \"Soumise\"), W: DocumentReclamation -\> Svc Call: ServiceNotification (accusé réception à l\'étudiant) -\> Svc Call: ServiceNotification (notification au service concerné/admin) -\> Retour Statut.

    ii. **Réponse à Réclamation par Agent :** id_reclamation, texte réponse -\> ServiceReclamation -\> U: Reclamation (statut \"Réponse apportée\", reponse_officielle, date_reponse) -\> Svc Call: ServiceNotification (notification à l\'étudiant) -\> Retour Statut.

[]{#_Toc199281800 .anchor}**14. ServiceDocumentGenerator.php**

-   **Déclencheurs / Données d\'Entrée :**

    -   Appels d\'autres services nécessitant la génération d\'un PDF (ex: ServiceCommission pour PV, ServiceGestionAcademique pour attestations).

    -   Type de document à générer (code_modele).

    -   Identifiants des entités concernées (ex: id_rapport_etudiant, id_compte_rendu, numero_carte_etudiant).

-   **Modèles Lus (R) :**

    -   ModeleDocumentPdf (via ServiceConfigurationSysteme, pour obtenir le template HTML/CSS).

    -   *Tous les modèles de données sources* pour peupler le document, via les services métier correspondants (ex: RapportEtudiant, CompteRendu, Etudiant, Evaluer, Inscrire).

-   **Modèles Écrits/Modifiés (W/U) :**

    -   DocumentOfficielGenere (W: pour tracer le document PDF généré et stocké).

-   **Services Appelants Fréquents :** ServiceCommission (pour PV), ServiceGestionAcademique (pour attestations, relevés), ServiceRapport (pour version finale estampillée du rapport).

-   **Services Appelés (Svc Call) :**

    -   ServiceConfigurationSysteme (pour récupérer le ModeleDocumentPdf).

    -   Les services métier spécifiques pour récupérer les données dynamiques (ex: ServiceRapport pour les données d\'un rapport, ServiceAuthentification pour les données d\'un étudiant).

    -   Bibliothèque de conversion HTML vers PDF externe.

-   **Principales Sorties / Effets :**

    -   Fichier PDF généré (soit retourné en flux, soit stocké sur serveur).

    -   Enregistrement des métadonnées du document généré (si stocké).

-   **Flux de Données Clés :**

    i.  **Générer PV (par ServiceCommission) :** ServiceCommission (PV validé) -\> Svc Call: ServiceDocumentGenerator (avec code_modele=\'PV_COMMISSION\', id_compte_rendu) -\> ServiceDocumentGenerator:
        > a. Svc Call: ServiceConfigurationSysteme pour R: ModeleDocumentPdf (code_modele).
        > b. Svc Call: ServiceCommission pour R: CompteRendu (id_compte_rendu), et autres données liées (rapports, membres).
        > c. Population du template HTML avec les données.
        > d. Appel librairie externe de conversion HTML-\>PDF.
        > e. W: DocumentOfficielGenere (stockage et traçabilité du PDF).
        > f. Retour chemin/ID du PDF à ServiceCommission.
