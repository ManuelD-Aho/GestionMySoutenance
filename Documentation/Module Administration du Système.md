**Analyse** **Complète** **et** **Précise** **du** **Module**
**Administration**

**1.** **Header** **Proposé**

> ● Panneau de Contrôle Principal - Gestion MySoutenance

**2.** **Sidebar** **Dynamique** **et** **Fonctionnalités**
**Associées**

> ● **Tableau** **de** **Bord** **Principal** **:**
>
> ○ Visualiser les statistiques d'utilisation (nombre d'utilisateurs
> actifs, nombre de rapports soumis/validés, etc.).
>
> ○ Consulter les alertes système critiques (erreurs BDD, problèmes
> serveur, accès suspects, processus automatisés en échec).
>
> ○ Accéder aux raccourcis vers les sections de gestion les plus
> fréquemment utilisées (gestion des utilisateurs, configuration des
> référentiels, etc.).
>
> ● **Gestion** **des** **Utilisateurs** **:** ○ **Étudiants** **:**
>
> ■ Lister tous les étudiants, filtrer et rechercher par divers
> critères.
>
> ■ Créer un nouvel étudiant : saisir informations personnelles (nom,
> prénom, date et lieu de naissance, nationalité, sexe, coordonnées,
> photo, contact d'urgence).
>
> ■ Le système crée automatiquement un compte utilisateur associé
> (utilisateur) avec login unique, mot de passe initial, date création,
> statut actif, lié au type "Étudiant" et groupe approprié.
>
> ■ Aficher les détails complets d'un profil étudiant et les
> informations du compte utilisateur lié.
>
> ■ Mettre à jour les informations personnelles d'un étudiant.
>
> ■ Modifier les informations du compte utilisateur associé
> (réinitialiser mot de passe, changer statut, photo).
>
> ■ Initier la suppression d'un profil étudiant (selon règles strictes,
> conditionnée à l'absence de données critiques liées).
>
> ■ Désactiver le compte utilisateur et archiver le profil étudiant si
> données critiques existent.
>
> ■ *(Droits* *de* *gestion* *des* *étudiants* *peuvent* *être*
> *accordés* *au* *Responsable* *Scolarité)*.
>
> ○ **Personnel** **Administratif** **:**
>
> ■ Lister les membres du personnel administratif.
>
> ■ Créer un nouveau membre du personnel : saisir informations (nom,
> prénom, date de naissance, téléphone, email, date d'affectation,
>
> responsabilités).
>
> ■ Le système crée automatiquement un compte utilisateur associé avec
> login, mot de passe initial, statut actif, lié au type "Personnel
> Administratif", groupe spécifique et niveau d'accès.
>
> ■ Aficher les détails d'un profil spécifique et les informations du
> compte utilisateur lié.
>
> ■ Mettre à jour les informations d'un membre du personnel et de son
> compte utilisateur.
>
> ■ Supprimer ou archiver un profil, désactiver ou supprimer son compte
> utilisateur.
>
> ○ **Enseignants** **:**
>
> ■ Lister les enseignants.
>
> ■ Créer un nouvel enseignant : saisir informations (nom, prénom, date
> de naissance, téléphone, email professionnel).
>
> ■ Le système crée automatiquement un compte utilisateur associé avec
> login, mot de passe initial, statut actif, lié au type "Enseignant",
> groupe et niveau d'accès.
>
> ■ Aficher les détails d'un profil enseignant et les informations du
> compte utilisateur lié.
>
> ■ Consulter l'historique des rapports supervisés ou des évaluations
> participées (avec droits appropriés).
>
> ■ Mettre à jour les informations personnelles d'un enseignant et de
> son compte utilisateur.
>
> ■ Supprimer ou archiver un profil, désactiver ou supprimer son compte
> utilisateur.

● **Gestion** **des** **Habilitations** **:**

> ○ **Types** **Utilisateur** **(Rôles)** **:**
>
> ■ Ajouter un nouveau type d'utilisateur avec libellé et description. ■
> Lister et aficher les détails des types d'utilisateurs existants.
>
> ■ Modifier le libellé ou la description d'un type d'utilisateur.
>
> ■ Supprimer un type d'utilisateur (si aucun utilisateur assigné). ○
> **Groupes** **Utilisateur** **(Permissions)** **:**
>
> ■ Ajouter un nouveau groupe d'utilisateurs avec libellé et
> description. ■ Lister et aficher les détails des groupes existants.
>
> ■ Modifier le libellé ou la description d'un groupe.
>
> ■ Supprimer un groupe (si non lié à des utilisateurs ou permissions).
> ○ **Niveaux** **d'Accès** **aux** **Données** **:**
>
> ■ Définir un nouveau niveau d'accès avec libellé et description. ■
> Lister et aficher les détails des niveaux d'accès existants.
>
> ■ Modifier le libellé ou la description d'un niveau d'accès. ■
> Supprimer un niveau d'accès (s'il n'est plus utilisé).
>
> ○ **Permissions** **(Associations** **rattacher)** **:**
>
> ■ Associer un groupe_utilisateur à un ou plusieurs traitement pour
> définir une permission.
>
> ■ Visualiser les permissions (liste des traitement autorisés) pour
> chaque groupe_utilisateur.
>
> ■ Dissocier un traitement d'un groupe_utilisateur pour révoquer une
> permission.

● **Configuration** **Système** **:** ○ **Référentiels** **:**

> ■ Accéder aux interfaces CRUD (Créer, Lire, Mettre à jour, Supprimer)
> pour chacun des 14 référentiels:
>
> 1\. specialite (Spécialités des enseignants)
>
> 2\. fonction (Fonctions des enseignants/personnel) 3. grade (Grades
> académiques des enseignants) 4. ue (Unités d'Enseignement)
>
> 5\. ecue (Éléments Constitutifs d'UE)
>
> 6\. annee_academique (Années académiques) 7. niveau_etude (Niveaux
> d'étude)
>
> 8\. entreprise (Entreprises pour les stages)
>
> 9\. niveau_approbation (Étapes/niveaux workflow validation) 10.
> statut_jury (Rôles au sein d'un jury/commission)
>
> 11\. action (Types d'actions système pour audit)
>
> 12\. traitement (Fonctionnalités/traitements pour permissions) 13.
> message (Modèles de messages)
>
> 14\. notification (Types/modèles de notifications) ■ Pour chaque
> référentiel :
>
> ■ Créer une nouvelle entrée via un formulaire.
>
> ■ Lister toutes les entrées, filtrer, rechercher, aficher détails. ■
> Modifier les informations d'une entrée existante.
>
> ■ Supprimer une entrée (en tenant compte des dépendances). ○
> **Paramètres** **Généraux** **Applicatifs** **&** **Workflow** **:**
>
> ■ Configurer les dates limites (soumission rapports, corrections,
> etc.).
>
> ■ Configurer les règles de validation pour le module Conformité
> (documents obligatoires, formats, tailles).
>
> ■ Configurer les paramètres des alertes système (délai avant alerte
> pour dossier en attente).
>
> ■ Configurer les paramètres du vote en ligne pour la commission
> (nombre
>
> de tours de vote avant escalade).
>
> ■ Configurer les options du chat intégré (création de groupes par
> défaut pour la commission).
>
> ○ **Modèles** **de** **Documents** **&** **Notifications** **:**
>
> ■ Téléverser, prévisualiser et gérer les modèles HTML/CSS pour la
> génération PDF (attestations, reçus, PV, etc.).
>
> ■ Créer, modifier et gérer les modèles de notifications par courriel
> (bienvenue, changement statut, rappel).

● **Gestion** **Académique** **&** **Administrative** **:**

> ○ **(Principalement** **Responsable** **Scolarité,** **Admin**
> **Système** **pour** **config/droits)**.
>
> ○ **Inscriptions** **Administratives** **(inscrire)** **:**
> (Responsable Scolarité)
>
> ■ Enregistrer une inscription (étudiant, niveau, année, montant, date,
> statut paiement, reçu).
>
> ■ Consulter l'historique des inscriptions (par étudiant, année,
> niveau). ■ Mettre à jour une inscription (paiement, décision passage).
>
> ■ Supprimer une inscription (action contrôlée).
>
> ○ **Évaluations** **/** **Notes** **(evaluer)** **:** (Responsable
> Scolarité) ■ Saisir la note d'un étudiant pour une ECUE.
>
> ■ Consulter les notes (RS, enseignant, étudiant ; Admin pour vue
> globale). ■ Modifier une note (avec justification).
>
> ■ Supprimer une note (action exceptionnelle, tracée, droits élevés). ○
> **Stages** **(faire_stage)** **:** (Responsable Scolarité)
>
> ■ Enregistrer les informations d'un stage (étudiant, entreprise,
> dates, sujet, tuteur).
>
> ■ Lister tous les stages et consulter les détails. ■ Mettre à jour les
> informations d'un stage.
>
> ■ Supprimer un enregistrement de stage.
>
> ○ **Associations** **Enseignants** **:** (Admin Système / Responsable
> Scolarité)
>
> ■ **Enseignant-Grade** **(acquerir)** **:** Enregistrer acquisition
> grade, consulter grades, modifier date, supprimer enregistrement.
>
> ■ **Enseignant-Fonction** **(occuper)** **:** Assigner fonction avec
> dates, consulter fonctions, modifier dates, retirer fonction.
>
> ■ **Enseignant-Spécialité** **(attribuer)** **:** Lier enseignant à
> spécialité, lister spécialités par enseignant ou vice-versa, retirer
> spécialité.

● **Supervision** **&** **Maintenance** **:** (Principalement
Administrateur Système)

> ○ **Suivi** **des** **Workflows** **:** Accéder à des tableaux de bord
> de supervision (état des rapports, goulots d'étranglement, charge de
> travail).
>
> ○ **Gestion** **des** **PV** **(Admin)** **:** Accéder aux PV validés
> pour consultation et
>
> archivage oficiel. Gérer le signalement des rapports éligibles à
> publication externe.
>
> ○ **Gestion** **des** **Notifications** **Système** **(recevoir)**
> **:** (Maintenance) Archiver ou supprimer en masse les anciennes
> notifications. *Le* *système* *enregistre* *l'envoi* *et* *les*
> *utilisateurs* *marquent* *comme* *lues.*
>
> ○ **Journaux** **d'Audit** **(enregistrer,** **pister)** **:**
>
> ■ Consulter le journal des actions utilisateurs (enregistrer) : qui,
> quoi, quand, IP, user_agent, détails.
>
> ■ Consulter la traçabilité des accès aux fonctionnalités (pister) :
> utilisateur, traitement, quand, accès accordé.
>
> ○ **Outils** **d'Import/Export** **Données** **:** Importer ou
> exporter des données (CSV, XML, SQL) pour migrations ou sauvegardes.
>
> ○ **Maintenance** **Technique** **:**
>
> ■ Lancer des scripts de maintenance (nettoyage, optimisation BDD,
> réindexation).
>
> ■ Gérer les sauvegardes et procédures de restauration.
>
> ■ Consulter et appliquer les mises à jour des composants (si intégré).
> ● **Reporting** **&** **Analytique** **:**
>
> ○ Générer/configurer des rapports statistiques avancés (taux
> validation, délais, performance encadreurs/commission, tendances
> thématiques, corrections fréquentes).
>
> ○ Configurer des tableaux de bord spécifiques pour la direction ou
> responsables de filière.

**3.** **Fonctionnalités** **Détaillées** **(par** **Section** **du**
**Document)**

**Section** **1** **:** **Accès** **Sécurisé** **et** **Tableau** **de**
**Bord** **Principal** **de** **l'Administration**

> ● **Authentification** **Robuste** **:** Connexion des administrateurs
> via identifiants spécifiques et hautement sécurisés, potentiellement
> avec authentification à deux facteurs.
>
> ● **Accès** **au** **Tableau** **de** **Bord** **Centralisé** **:**
> Une fois connecté, l'administrateur accède à un tableau de bord
> d'administration centralisé.
>
> ● **Vue** **d'Ensemble** **du** **Système** **:** Le tableau de bord
> afiche :
>
> ○ Statistiques d'utilisation (utilisateurs actifs, rapports
> soumis/validés).
>
> ○ Alertes système critiques (erreurs BDD, problèmes serveur, accès
> suspects, processus automatisés en échec).
>
> ○ Raccourcis vers les sections de gestion les plus utilisées.

**Section** **2** **:** **Gestion** **des** **Entités** **Principales,**
**Utilisateurs** **Associés** **et** **Habilitations**

> ● *(Rôles* *:* *Principalement* *Administrateur* *Système;*
> *Responsable* *Scolarité* *peut* *avoir* *droits* *sur* *gestion*
> *étudiants)*.
>
> ● **Gestion** **des** **Étudiants** **(etudiant)** **et** **Comptes**
> **Utilisateurs** **Associés** **(utilisateur)** **:**
>
> ○ **Création** **:** Saisie des informations personnelles de
> l'étudiant. Création automatique et liaison d'un compte utilisateur
> (login unique, mot de passe initial, type "Étudiant", groupe).
>
> ○ **Lecture** **:** Listage, filtrage et recherche d'étudiants.
> Afichage des détails complets du profil étudiant et du compte
> utilisateur lié.
>
> ○ **Mise** **à** **Jour** **:** Modification des informations
> personnelles de l'étudiant et des informations du compte utilisateur
> associé (reset mot de passe, statut, photo).
>
> ○ **Suppression** **:** Initiation de la suppression d'un profil
> étudiant selon des règles strictes. Conditionnée à l'absence de
> données critiques (rapports, notes). Si données existent,
> désactivation du compte et archivage du profil.
>
> ● **Gestion** **du** **Personnel** **Administratif**
> **(personnel_administratif)** **et** **Comptes** **Utilisateurs**
> **Associés** **(utilisateur)** **:**
>
> ○ **Création** **:** Saisie des informations du membre du personnel
> (nom, prénom, etc.). Création automatique et liaison d'un compte
> utilisateur (login, mot de passe initial, type "Personnel
> Administratif", groupe spécifique, niveau d'accès).
>
> ○ **Lecture** **:** Consultation de la liste des membres du personnel
> et afichage des détails d'un profil et compte lié.
>
> ○ **Mise** **à** **Jour** **:** Modification des informations d'un
> membre du personnel et de son compte utilisateur.
>
> ○ **Suppression** **:** Suppression ou archivage du profil et
> désactivation/suppression du compte utilisateur lors du départ d'un
> membre.
>
> ● **Gestion** **des** **Enseignants** **(enseignant)** **et**
> **Comptes** **Utilisateurs** **Associés** **(utilisateur)** **:**
>
> ○ **Création** **:** Saisie des informations de l'enseignant. Création
> automatique et liaison d'un compte utilisateur (login, mot de passe
> initial, type "Enseignant", groupe, niveau d'accès).
>
> ○ **Lecture** **:** Consultation de la liste des enseignants, afichage
> des détails d'un profil et compte lié. Consultation de l'historique
> des rapports supervisés ou évaluations participées (avec droits).
>
> ○ **Mise** **à** **Jour** **:** Modification des informations
> personnelles d'un enseignant et de son compte utilisateur.
>
> ○ **Suppression** **:** Suppression ou archivage du profil et
>
> désactivation/suppression du compte utilisateur si l'enseignant n'est
> plus afilié.
>
> ● **Gestion** **des** **type_utilisateur** **(Rôles)** **:**
>
> ○ **Création** **:** Ajout d'un nouveau type d'utilisateur avec
> libellé et description. ○ **Lecture** **:** Listage et afichage des
> détails de tous les types d'utilisateurs. ○ **Mise** **à** **Jour**
> **:** Modification du libellé ou de la description.
>
> ○ **Suppression** **:** Suppression possible uniquement si aucun
> utilisateur n'y est assigné.
>
> ● **Gestion** **des** **groupe_utilisateur** **(Groupes** **de**
> **Permissions)** **:**
>
> ○ **Création** **:** Ajout d'un nouveau groupe avec libellé et
> description. ○ **Lecture** **:** Listage et afichage des détails des
> groupes.
>
> ○ **Mise** **à** **Jour** **:** Modification du libellé ou de la
> description.
>
> ○ **Suppression** **:** Suppression possible uniquement si aucun
> utilisateur ou permission n'y est lié.
>
> ● **Gestion** **des** **niveau_acces_donne** **:**
>
> ○ **Création** **:** Définition d'un nouveau niveau d'accès avec
> libellé et description. ○ **Lecture** **:** Listage et afichage des
> détails des niveaux d'accès.
>
> ○ **Mise** **à** **Jour** **:** Modification du libellé ou de la
> description. ○ **Suppression** **:** Suppression possible s'il n'est
> plus utilisé.
>
> ● **Gestion** **des** **Associations** **rattacher** **(Groupe**
> **Utilisateur** **\<-\>** **Traitement)** **:** ○ **Création** **:**
> Association d'un groupe_utilisateur à un ou plusieurs traitement. ○
> **Lecture** **:** Visualisation des permissions pour chaque
> groupe_utilisateur.
>
> ○ **Suppression** **:** Dissociation d'un traitement d'un
> groupe_utilisateur.

**Section** **3** **:** **Configuration** **Générale** **et**
**Paramétrage** **du** **Système**

> ● *(CRUD* *intégral* *par* *l'Administrateur* *Système* *pour* *la*
> *plupart* *des* *référentiels)*. ● **Gestion** **des**
> **Référentiels** **(Paramètres** **Généraux)** **:** Accès à des
> interfaces
>
> CRUD dédiées pour 14 référentiels: specialite, fonction, grade, ue,
> ecue, annee_academique, niveau_etude, entreprise, niveau_approbation,
> statut_jury, action, traitement, message, notification.
>
> ○ **Création** **:** Ajout d'une nouvelle entrée dans un référentiel.
>
> ○ **Lecture** **:** Listage, filtrage, recherche et afichage des
> détails des entrées d'un référentiel.
>
> ○ **Mise** **à** **Jour** **:** Modification des informations d'une
> entrée existante. ○ **Suppression** **:** Suppression d'une entrée, en
> tenant compte des
>
> dépendances.
>
> ● **Paramètres** **Généraux** **du** **Workflow** **et** **de**
> **l'Application** **:**
>
> ○ Configuration des dates limites (soumission, corrections).
>
> ○ Configuration des règles de validation pour la Conformité (documents
>
> obligatoires, formats, tailles).
>
> ○ Configuration des paramètres des alertes système (délai avant
> alerte).
>
> ○ Configuration des paramètres du vote en ligne de la commission
> (nombre de tours).
>
> ○ Configuration des options du chat intégré (groupes par défaut). ●
> **Gestion** **des** **Modèles** **de** **Documents** **et** **de**
> **Notifications** **:**
>
> ○ Téléversement, prévisualisation et gestion des modèles HTML/CSS pour
> la génération PDF.
>
> ○ Création, modification et gestion des modèles de notifications par
> courriel.

**Section** **4** **:** **Gestion** **Académique** **et**
**Administrative** **Spécifique**

> ● *(Principalement* *géré* *par* *le* *Responsable* *Scolarité* *pour*
> *opérations* *courantes,* *Admin* *Système* *pour* *config/droits)*.
>
> ● **Gestion** **des** **Inscriptions** **Administratives**
> **(inscrire)** **:** (Responsable Scolarité) ○ **Création** **:**
> Saisie de l'inscription d'un étudiant (niveau, année, montant, date,
>
> statut paiement, reçu).
>
> ○ **Lecture** **:** Consultation de l'historique des inscriptions.
>
> ○ **Mise** **à** **Jour** **:** Mise à jour des informations
> d'inscription (paiement, décision passage).
>
> ○ **Suppression** **:** Suppression contrôlée d'une inscription.
>
> ● **Gestion** **des** **Évaluations** **/** **Notes** **des**
> **Étudiants** **(evaluer)** **:** (Responsable Scolarité)
>
> ○ **Création** **:** Saisie de la note d'un étudiant pour une ECUE.
>
> ○ **Lecture** **:** Consultation des notes (RS, enseignant, étudiant ;
> Admin pour vue globale).
>
> ○ **Mise** **à** **Jour** **:** Modification d'une note (avec
> justification).
>
> ○ **Suppression** **:** Suppression d'une note (exceptionnelle,
> tracée, droits élevés). ● **Gestion** **des** **Stages**
> **(faire_stage)** **:** (Responsable Scolarité)
>
> ○ **Création** **:** Enregistrement des informations du stage
> (étudiant, entreprise, dates, sujet, tuteur).
>
> ○ **Lecture** **:** Listage et consultation des détails des stages. ○
> **Mise** **à** **Jour** **:** Mise à jour des informations d'un stage.
>
> ○ **Suppression** **:** Suppression d'un enregistrement de stage.
>
> ● **Gestion** **des** **Associations** **Enseignant-Grade**
> **(acquerir)** **:** (Admin Système / RS) ○ **Création** **:**
> Enregistrement de l'acquisition d'un grade par un enseignant.
>
> ○ **Lecture** **:** Consultation des grades détenus.
>
> ○ **Mise** **à** **Jour** **:** Modification de la date d'acquisition.
> ○ **Suppression** **:** Suppression de l'enregistrement.
>
> ● **Gestion** **des** **Associations** **Enseignant-Fonction**
> **(occuper)** **:** (Admin Système /
>
> RS)
>
> ○ **Création** **:** Assignation d'une fonction à un enseignant avec
> dates. ○ **Lecture** **:** Consultation des fonctions occupées.
>
> ○ **Mise** **à** **Jour** **:** Modification des dates d'occupation.
>
> ○ **Suppression** **:** Retrait d'une fonction ou marquage date de
> fin.
>
> ● **Gestion** **des** **Associations** **Enseignant-Spécialité**
> **(attribuer)** **:** (Admin Système / RS)
>
> ○ **Création** **:** Liaison d'un enseignant à une spécialité.
>
> ○ **Lecture** **:** Listage des spécialités par enseignant ou
> enseignants par spécialité.
>
> ○ **Suppression** **:** Retrait d'une spécialité à un enseignant.

**Section** **5** **:** **Supervision,** **Maintenance,**
**Notifications** **et** **Audit**

> ● *(Principalement* *pour* *l'Administrateur* *Système)*.
>
> ● **Suivi** **Global** **des** **Workflows** **et** **des**
> **Processus** **:** Accès à des tableaux de bord de supervision (état
> des rapports, goulots d'étranglement, charge de travail).
>
> ● **Gestion** **des** **Procès-Verbaux** **(Aspects**
> **Administratifs)** **:** Accès aux PV validés pour consultation,
> archivage oficiel. Gestion du signalement des rapports pour
> publication externe.
>
> ● **Gestion** **des** **Notifications** **Envoyées/Reçues**
> **(recevoir)** **:** ○ Le système enregistre l'envoi de notifications.
>
> ○ Les utilisateurs consultent et marquent comme lues leurs
> notifications. ○ L'administrateur peut archiver ou supprimer en masse
> les anciennes
>
> notifications.
>
> ● **Consultation** **des** **Journaux** **d'Audit** **(enregistrer,**
> **pister)** **:**
>
> ○ Consultation du journal des actions des utilisateurs (enregistrer) :
> qui, quoi, quand, IP, user_agent, détails JSON.
>
> ○ Consultation de la traçabilité des accès aux fonctionnalités
> (pister) : utilisateur, traitement, quand, accès accordé.
>
> ● **Outils** **d'Import/Export** **de** **Données** **:** Outils pour
> importer/exporter des données (CSV, XML, SQL).
>
> ● **Maintenance** **Technique** **de** **Base** **:**
>
> ○ Lancement de scripts de maintenance (nettoyage, optimisation BDD,
> réindexation).
>
> ○ Gestion des sauvegardes et procédures de restauration.
>
> ○ Consultation et application des mises à jour des composants (si
> intégré).

**Section** **6** **:** **Reporting** **et** **Analytique** **Avancés**

> ● Génération ou configuration de rapports statistiques avancés (taux
> de validation,
>
> délais, performance, tendances thématiques, corrections fréquentes).
>
> ● Configuration de tableaux de bord spécifiques pour la direction ou
> responsables de filière.

**Section** **7** **:** **Fonctionnalités** **à** **Ajouter**
**(Évolutions** **Futures)**

> ● **Gestion** **Avancée** **des** **Sauvegardes** **:** Planification,
> types, destinations externes, restaurations sélectives.
>
> ● **Intégration** **Annuaire** **Existant** **(LDAP/AD)** **:**
> Synchronisation des comptes utilisateurs.
>
> ● **Module** **de** **Personnalisation** **de** **l'Interface** **:**
> Modification thèmes, logo, polices, textes.
>
> ● **Outils** **de** **Communication** **de** **Masse** **Améliorés**
> **:** Emails/notifications ciblées, suivi délivrabilité, modèles
> enrichis.
>
> ● **Gestion** **des** **Interconnexions** **Autres** **Systèmes**
> **:** Configuration et gestion des points d'intégration (API).
>
> ● **Tableau** **de** **Bord** **Sécurité** **Détaillé** **:** Suivi
> tentatives connexion échouées, IP suspectes, alertes, gestion IP
> bloquées/autorisées, configuration alertes email.
>
> ● **Module** **Gestion** **Consentements** **(RGPD)** **:**
> Configuration politiques, traçage consentement, gestion demandes
> utilisateurs.
>
> ● **Outils** **Débogage/Diagnostic** **Poussés** **:** Tracing SQL,
> analyse performances, simulation actions.
>
> ● **Paramétrage** **Fin** **Notifications/Workflows** **:** Définition
> de triggers, canaux, contenu via interface graphique ; configuration
> des étapes/transitions/acteurs des workflows.
>
> ● **Gestion** **Maintenance** **Programmée** **:** Activation mode
> maintenance, définition portée, programmation, notification préalable.

**4.** **Tables** **SQL** **et** **Attributs** **Associés**
**(Exhaustif)**

*Basé* *sur* *mysoutenance.txt* *\[602-991\].*

**Table** **:** **utilisateur**

> ● numero_utilisateur (VARCHAR(50)) : Identifiant unique de
> l'utilisateur (non auto-incrémenté).
>
> ● login_utilisateur (VARCHAR(100)) : Identifiant de connexion de
> l'utilisateur. ● mot_de_passe (VARCHAR(255)) : Mot de passe haché de
> l'utilisateur.
>
> ● date_creation (DATETIME) : Date de création du compte utilisateur.
>
> ● photo_profil (VARCHAR(255)) : Chemin vers la photo de profil de
> l'utilisateur. ● actif (TINYINT(1)) : Statut d'activité du compte (1
> pour actif, 0 pour inactif).
>
> ● id_niveau_acces_donne (INT) : Clé étrangère vers niveau_acces_donne,
> définit le niveau de visibilité des données.
>
> ● id_groupe_utilisateur (INT) : Clé étrangère vers groupe_utilisateur,
> assigne l'utilisateur à un groupe.
>
> ● id_type_utilisateur (INT) : Clé étrangère vers type_utilisateur,
> définit le rôle principal de l'utilisateur.
>
> ● *Supporte* *:* *Gestion* *des* *Utilisateurs* *(Étudiants,*
> *Personnel,* *Enseignants),* *Accès* *sécurisé,* *Habilitations.*

**Table** **:** **etudiant**

> ● numero_carte_etudiant (VARCHAR(50)) : Identifiant unique de
> l'étudiant (non auto-incrémenté).
>
> ● nom (VARCHAR(100)) : Nom de famille de l'étudiant. ● prenom
> (VARCHAR(100)) : Prénom de l'étudiant.
>
> ● date_naissance (DATE) : Date de naissance.
>
> ● lieu_naissance (VARCHAR(100)) : Lieu de naissance. ● pays_naissance
> (VARCHAR(50)) : Pays de naissance. ● nationalite (VARCHAR(50)) :
> Nationalité.
>
> ● sexe (ENUM('Masculin','Féminin','Autre')) : Sexe de l'étudiant. ●
> adresse_postale (TEXT) : Adresse postale.
>
> ● ville (VARCHAR(100)) : Ville de résidence. ● code_postal
> (VARCHAR(20)) : Code postal.
>
> ● telephone (VARCHAR(20)) : Numéro de téléphone. ● email
> (VARCHAR(255)) : Adresse e-mail personnelle.
>
> ● photo_profil_chemin (VARCHAR(255)) : Chemin vers la photo de profil.
> ● numero_utilisateur (VARCHAR(50)) : Clé étrangère vers utilisateur.
>
> ● contact_urgence_nom, contact_urgence_telephone,
> contact_urgence_relation : Informations du contact d'urgence.
>
> ● *Supporte* *:* *Gestion* *des* *Étudiants* *(CRUD* *par*
> *Admin/RS).*

**Table** **:** **personnel_administratif**

> ● numero_personnel_administratif (VARCHAR(50)) : Identifiant unique du
> personnel (non auto-incrémenté).
>
> ● nom, prenom : Nom et prénom.
>
> ● telephone_professionnel, email_professionnel : Coordonnées
> professionnelles. ● date_affectation_service, responsabilites_cles :
> Informations administratives.
>
> ● numero_utilisateur (VARCHAR(50)) : Clé étrangère vers utilisateur. ●
> date_naissance, lieu_naissance, pays_naissance, nationalite, sexe,
>
> adresse_postale, ville, code_postal, telephone_personnel,
> email_personnel,
>
> photo_profil_chemin : Informations personnelles détaillées.
>
> ● *Supporte* *:* *Gestion* *du* *Personnel* *Administratif* *(CRUD*
> *par* *Admin).*

**Table** **:** **enseignant**

> ● numero_enseignant (VARCHAR(50)) : Identifiant unique de l'enseignant
> (non auto-incrémenté).
>
> ● nom, prenom : Nom et prénom.
>
> ● telephone_professionnel, email_professionnel : Coordonnées
> professionnelles. ● numero_utilisateur (VARCHAR(50)) : Clé étrangère
> vers utilisateur.
>
> ● date_naissance, lieu_naissance, pays_naissance, nationalite, sexe,
> adresse_postale, ville, code_postal, telephone_personnel,
> email_personnel, photo_profil_chemin : Informations personnelles
> détaillées.
>
> ● *Supporte* *:* *Gestion* *des* *Enseignants* *(CRUD* *par* *Admin),*
> *Associations* *Enseignant-Grade/Fonction/Spécialité.*

**Table** **:** **type_utilisateur**

> ● id_type_utilisateur (INT AUTO_INCREMENT) : Identifiant unique du
> type d'utilisateur (rôle).
>
> ● lib_type_utilisateur (VARCHAR(100)) : Libellé du rôle (ex:
> "Étudiant", "Administrateur").
>
> ● description_type_utilisateur (TEXT) : Description du rôle. ●
> *Supporte* *:* *Gestion* *des* *Habilitations* *(CRUD* *Rôles).*

**Table** **:** **groupe_utilisateur**

> ● id_groupe_utilisateur (INT AUTO_INCREMENT) : Identifiant unique du
> groupe. ● lib_groupe_utilisateur (VARCHAR(100)) : Libellé du groupe
> (ex: "Agents de
>
> Conformité").
>
> ● description_groupe (TEXT) : Description du groupe.
>
> ● *Supporte* *:* *Gestion* *des* *Habilitations* *(CRUD* *Groupes).*

**Table** **:** **niveau_acces_donne**

> ● id_niveau_acces_donne (INT AUTO_INCREMENT) : Identifiant unique du
> niveau d'accès.
>
> ● lib_niveau_acces_donne (VARCHAR(100)) : Libellé du niveau d'accès. ●
> description_niveau_acces (TEXT) : Description du niveau.
>
> ● *Supporte* *:* *Gestion* *des* *Habilitations* *(CRUD* *Niveaux*
> *d'Accès).*

**Table** **:** **traitement**

> ● id_traitement (INT AUTO_INCREMENT) : Identifiant unique du
> traitement/fonctionnalité système.
>
> ● lib_trait (VARCHAR(100)) : Libellé du traitement (ex: "Accès Module
> Admin"). ● *Supporte* *:* *Gestion* *des* *Habilitations*
> *(Référentiel* *des* *fonctionnalités* *pour*
>
> *permissions).*

**Table** **:** **rattacher**

> ● id_groupe_utilisateur (INT) : Clé étrangère vers groupe_utilisateur.
> ● id_traitement (INT) : Clé étrangère vers traitement.
>
> ● *Supporte* *:* *Gestion* *des* *Habilitations* *(Liaison* *Groupe*
> *\<-\>* *Traitement* *pour* *définir* *permissions).*

**Table** **:** **annee_academique**

> ● id_annee_academique (INT) : Identifiant de l'année académique (non
> auto-incrémenté).
>
> ● lib_annee_academique (VARCHAR(50)) : Libellé (ex: "2024-2025"). ●
> date_debut, date_fin : Dates de l'année académique.
>
> ● est_active (TINYINT(1)) : Indique si l'année est active.
>
> ● *Supporte* *:* *Configuration* *Système* *(CRUD* *Référentiels),*
> *Gestion* *Académique* *(Inscriptions).*

**Table** **:** **niveau_etude**

> ● id_niveau_etude (INT AUTO_INCREMENT) : Identifiant du niveau
> d'étude. ● lib_niveau_etude (VARCHAR(100)) : Libellé (ex: "Master 2").
>
> ● code_niveau_etude (VARCHAR(20)) : Code du niveau.
>
> ● *Supporte* *:* *Configuration* *Système* *(CRUD* *Référentiels),*
> *Gestion* *Académique* *(Inscriptions).*

*(Listing* *des* *12* *autres* *référentiels* *:* *specialite,*
*fonction,* *grade,* *ue,* *ecue,* *entreprise,* *niveau_approbation,*
*statut_jury,* *action,* *message,* *notification* *et* *leurs*
*attributs* *pertinents,* *avec* *leur* *support* *aux*
*fonctionnalités* *d'administration* *comme* *la* *configuration,*
*l'audit,* *etc.* *Ces* *tables* *sont* *gérées* *via* *les*
*interfaces* *CRUD* *de* *la* *section* *"Référentiels".)*

**Table** **:** **specialite**

> ● id_specialite (INT AUTO_INCREMENT) : ID de la spécialité. ●
> lib_specialite (VARCHAR(100)) : Libellé de la spécialité.
>
> ● numero_enseignant_specialite (VARCHAR(50)) : Optionnel, responsable
> de
>
> spécialité.
>
> ● *Supporte* *:* *Configuration* *Référentiels,* *Gestion*
> *Associations* *Enseignant-Spécialité.*

**Table** **:** **fonction**

> ● id_fonction (INT AUTO_INCREMENT) : ID de la fonction. ● lib_fonction
> (VARCHAR(100)) : Libellé de la fonction.
>
> ● *Supporte* *:* *Configuration* *Référentiels,* *Gestion*
> *Associations* *Enseignant-Fonction.*

**Table** **:** **grade**

> ● id_grade (INT AUTO_INCREMENT) : ID du grade. ● lib_grade
> (VARCHAR(50)) : Libellé du grade.
>
> ● *Supporte* *:* *Configuration* *Référentiels,* *Gestion*
> *Associations* *Enseignant-Grade.*

**Table** **:** **ue**

> ● id_ue (INT AUTO_INCREMENT) : ID de l'Unité d'Enseignement. ● lib_ue
> (VARCHAR(100)) : Libellé de l'UE.
>
> ● *Supporte* *:* *Configuration* *Référentiels* *(structure*
> *académique).*

**Table** **:** **ecue**

> ● id_ecue (INT AUTO_INCREMENT) : ID de l'Élément Constitutif d'UE. ●
> lib_ecue (VARCHAR(100)) : Libellé de l'ECUE.
>
> ● id_ue (INT) : Clé étrangère vers ue.
>
> ● *Supporte* *:* *Configuration* *Référentiels,* *Gestion*
> *Académique* *(Notes).*

**Table** **:** **entreprise**

> ● id_entreprise (INT AUTO_INCREMENT) : ID de l'entreprise. ●
> lib_entreprise (VARCHAR(200)) : Nom de l'entreprise.
>
> ● *Supporte* *:* *Configuration* *Référentiels,* *Gestion*
> *Académique* *(Stages).*

**Table** **:** **niveau_approbation**

> ● id_niveau_approbation (INT AUTO_INCREMENT) : ID du niveau
> d'approbation. ● lib_niveau_approbation (VARCHAR(100)) : Libellé du
> niveau (étape workflow). ● *Supporte* *:* *Configuration*
> *Référentiels* *(workflow* *de* *validation).*

**Table** **:** **statut_jury**

> ● id_statut_jury (INT AUTO_INCREMENT) : ID du statut/rôle dans un
> jury. ● lib_statut_jury (VARCHAR(100)) : Libellé (ex: "Président",
> "Rapporteur").
>
> ● *Supporte* *:* *Configuration* *Référentiels* *(composition*
> *jury/commission).*

**Table** **:** **action** **(déjà** **listée** **pour**
**Habilitations,** **mais** **aussi** **référentiel)**

> ● id_action (INT AUTO_INCREMENT) : ID de l'action système.
>
> ● lib_action (VARCHAR(100)) : Libellé de l'action (ex: "Création
> Utilisateur"). ● *Supporte* *:* *Configuration* *Référentiels* *(pour*
> *audit),* *Audit.*

**Table** **:** **message**

> ● id_message (INT AUTO_INCREMENT) : ID du modèle de message. ●
> code_message (VARCHAR(50)) : Code unique du modèle.
>
> ● lib_message (TEXT) : Contenu du modèle.
>
> ● *Supporte* *:* *Configuration* *Référentiels* *(modèles*
> *communication),* *Gestion* *des* *Modèles.*

**Table** **:** **notification**

> ● id_notification (INT AUTO_INCREMENT) : ID du type de notification. ●
> lib_notification (VARCHAR(100)) : Libellé du type de notification.
>
> ● *Supporte* *:* *Configuration* *Référentiels* *(modèles*
> *notifications),* *Gestion* *des* *Modèles.*

**Table** **:** **inscrire**

> ● numero_carte_etudiant (VARCHAR(50)) : Clé étrangère vers etudiant. ●
> id_niveau_etude (INT) : Clé étrangère vers niveau_etude.
>
> ● id_annee_academique (INT) : Clé étrangère vers annee_academique. ●
> montant_inscription, date_inscription, id_statut_paiement,
>
> numero_recu_paiement, id_decision_passage : Détails de l'inscription.
> ● *Supporte* *:* *Gestion* *Académique* *(Inscriptions* *par* *RS).*

**Table** **:** **evaluer**

> ● numero_carte_etudiant (VARCHAR(50)) : Clé étrangère vers etudiant. ●
> numero_enseignant (VARCHAR(50)) : Clé étrangère vers enseignant. ●
> id_ecue (INT) : Clé étrangère vers ecue.
>
> ● note, date_evaluation : Détails de l'évaluation.
>
> ● *Supporte* *:* *Gestion* *Académique* *(Notes* *par* *RS).*

**Table** **:** **faire_stage**

> ● id_entreprise (INT) : Clé étrangère vers entreprise.
>
> ● numero_carte_etudiant (VARCHAR(50)) : Clé étrangère vers etudiant.
>
> ● date_debut_stage, date_fin_stage, sujet_stage, nom_tuteur_entreprise
> : Détails du stage.
>
> ● *Supporte* *:* *Gestion* *Académique* *(Stages* *par* *RS).*

**Table** **:** **acquerir**

> ● id_grade (INT) : Clé étrangère vers grade.
>
> ● numero_enseignant (VARCHAR(50)) : Clé étrangère vers enseignant. ●
> date_acquisition : Date d'acquisition du grade.
>
> ● *Supporte* *:* *Gestion* *Académique* *(Associations*
> *Enseignant-Grade).*

**Table** **:** **occuper**

> ● id_fonction (INT) : Clé étrangère vers fonction.
>
> ● numero_enseignant (VARCHAR(50)) : Clé étrangère vers enseignant.
>
> ● date_debut_occupation, date_fin_occupation : Dates d'occupation de
> la fonction. ● *Supporte* *:* *Gestion* *Académique* *(Associations*
> *Enseignant-Fonction).*

**Table** **:** **attribuer**

> ● numero_enseignant (VARCHAR(50)) : Clé étrangère vers enseignant. ●
> id_specialite (INT) : Clé étrangère vers specialite.
>
> ● *Supporte* *:* *Gestion* *Académique* *(Associations*
> *Enseignant-Spécialité).*

**Table** **:** **rapport_etudiant**

> ● id_rapport_etudiant (INT AUTO_INCREMENT) : ID du rapport. ●
> id_statut_rapport (INT) : Clé étrangère vers statut_rapport_ref.
>
> ● *Supporte* *:* *Supervision* *&* *Maintenance* *(Suivi* *des*
> *workflows,* *Gestion* *des* *PV* *admin).*

**Table** **:** **compte_rendu**

> ● id_compte_rendu (INT AUTO_INCREMENT) : ID du PV. ● id_statut_pv
> (INT) : Clé étrangère vers statut_pv_ref.
>
> ● *Supporte* *:* *Supervision* *&* *Maintenance* *(Gestion* *des* *PV*
> *admin).*

**Table** **:** **recevoir**

> ● numero_utilisateur (VARCHAR(50)) : Clé étrangère vers utilisateur. ●
> id_notification (INT) : Clé étrangère vers notification.
>
> ● date_reception, lue, date_lecture : Détails de la réception de
> notification.
>
> ● *Supporte* *:* *Supervision* *&* *Maintenance* *(Gestion* *des*
> *notifications* *système).*

**Table** **:** **enregistrer** **(déjà** **listée** **pour**
**Habilitations,** **mais** **aussi** **Audit)**

> ● numero_utilisateur (VARCHAR(50)) : Auteur de l'action. ● id_action
> (INT) : Type d'action effectuée.
>
> ● date_action, adresse_ip, user_agent, id_entite_concernee,
> type_entite_concernee, details_action, session_id_utilisateur :
> Détails de l'audit.
>
> ● *Supporte* *:* *Supervision* *&* *Maintenance* *(Journaux*
> *d'Audit).*

**Table** **:** **pister**

> ● numero_utilisateur (VARCHAR(50)) : Utilisateur ayant accédé. ●
> id_traitement (INT) : Fonctionnalité accédée.
>
> ● date_pister, acceder : Détails de l'accès.
>
> ● *Supporte* *:* *Supervision* *&* *Maintenance* *(Journaux*
> *d'Audit).*

*(Les* *tables* *de* *chat* *conversation,* *participant_conversation,*
*message_chat,* *lecture_message* *et* *les* *tables* *de* *validation*
*de* *la* *commission* *comme* *vote_commission,* *validation_pv,*
*affecter* *sont* *principalement* *utilisées* *par* *d'autres*
*modules* *mais* *peuvent* *être* *consultées/supervisées* *par*
*l'Administrateur.)*

**Module** **Administration** **:** **GestionMySoutenance**

Le module Administration est le centre de contrôle névralgique de la
plateforme "GestionMySoutenance". Il est destiné aux administrateurs
système et fonctionnels qui ont les droits les plus élevés pour
configurer, superviser, maintenir et gérer l'ensemble de l'application,
ses utilisateurs, ses habilitations et ses processus. Ce module garantit
le bon fonctionnement technique, académique et opérationnel de la
plateforme.

> 1\. **Accès** **Sécurisé** **et** **Tableau** **de** **Bord**
> **Principal** **de** **l'Administration**
>
> ○ **Lorsque** un administrateur se connecte avec ses identifiants
> spécifiques et hautement sécurisés, une authentification robuste est
> effectuée, potentiellement avec des mesures de sécurité
> supplémentaires (ex: authentification à deux facteurs si implémentée).
>
> ○ **Lorsque** la connexion est réussie, l'administrateur accède à un
> tableau de bord d'administration centralisé. Ce tableau de bord offre
> une vue d'ensemble de l'état du système :
>
> ■ Statistiques d'utilisation (nombre d'utilisateurs actifs, nombre de
> rapports soumis/validés, etc.).
>
> ■ Alertes système critiques (ex: erreurs de base de données, problèmes
> de serveur, tentatives d'accès suspectes, processus automatisés en
> échec).
>
> ■ Raccourcis vers les sections de gestion les plus fréquemment
> utilisées (gestion des utilisateurs, configuration des référentiels,
> etc.).
>
> 2\. Gestion des Entités Principales, Utilisateurs Associés et
> Habilitations (Principalement géré par l'Administrateur Système; le
> Responsable Scolarité peut avoir des droits sur la gestion des
> étudiants).
>
> ○ **Gestion** **des** **Étudiants** **(etudiant)** **et** **du**
> **Compte** **Utilisateur** **Lié** **:** ■ **Création** **:**
>
> ■ **Lorsque** l'administrateur (ou le Responsable Scolarité habilité)
> enregistre un nouvel étudiant, il saisit toutes ses informations
> personnelles (nom, prénom, date et lieu de naissance, nationalité,
> sexe, coordonnées, photo de profil, contact d'urgence).
>
> ■ **Lorsque** la fiche étudiant est créée, le système crée
> automatiquement un compte utilisateur associé (utilisateur) avec un
> login unique, un mot de passe initial (à changer par l'étudiant), la
> date de création, un statut actif, et le lie au type_utilisateur
> "Étudiant", à un groupe_utilisateur approprié.
>
> ■ **Lecture** **:**
>
> ■ **Lorsque** l'administrateur consulte la gestion des étudiants, il
> peut lister tous les étudiants et filtrer/rechercher par divers
> critères.
>
> ■ Il peut aficher les détails complets d'un profil étudiant spécifique
> ainsi que les informations du compte utilisateur qui lui est lié.
>
> ■ **Mise** **à** **jour** **:**
>
> ■ **Lorsque** des informations doivent être modifiées,
> l'administrateur peut mettre à jour les informations personnelles d'un
> étudiant.
>
> ■ Il peut également modifier les informations du compte utilisateur
> associé (ex: réinitialiser le mot de passe, changer le statut du
> compte – actif/inactif, mettre à jour la photo de profil).
>
> ■ **Suppression** **:**
>
> ■ **Lorsque** un profil étudiant doit être supprimé (selon des règles
> strictes, par exemple, jamais si l'étudiant a des données de rapport
> ou de notes liées), l'administrateur peut initier la suppression.
>
> ■ La suppression du profil étudiant est conditionnée à l'absence de
> données liées critiques (rapports, notes). Si des données existent, la
> procédure standard est la désactivation du compte utilisateur associé
> et l'archivage du profil étudiant (plutôt que sa suppression
> physique), conformément à la politique de conservation des données de
> l'établissement.
>
> ○ **Gestion** **du** **Personnel** **Administratif**
> **(personnel_administratif)** **et** **du** **Compte** **Utilisateur**
> **Lié** **:**
>
> ■ **Création** **:**
>
> ■ **Lorsque** l'administrateur enregistre un nouveau membre du
> personnel administratif, il saisit ses informations (nom, prénom, date
> de naissance, téléphone, email, date d'affectation, responsabilités).
>
> ■ **Lorsque** la fiche est créée, un compte utilisateur associé est
> automatiquement généré (login, mot de passe initial, date création,
> statut actif), lié au type_utilisateur "Personnel Administratif", à un
> groupe_utilisateur spécifique (ex: "Agents de Conformité",
> "Gestionnaires Scolarité") et à un niveau_acces_donne approprié.
>
> ■ **Lecture** **:**
>
> ■ **Lorsque** l'administrateur accède à cette section, il peut
> consulter la liste des membres du personnel administratif et aficher
> les détails d'un profil spécifique, ainsi que les informations du
> compte utilisateur lié.
>
> ■ **Mise** **à** **jour** **:**
>
> ■ **Lorsque** nécessaire, l'administrateur peut mettre à jour les
> informations d'un membre du personnel et modifier les informations de
> son compte utilisateur associé.
>
> ■ **Suppression** **:**
>
> ■ **Lorsque** un membre du personnel quitte l'établissement, son
> profil peut être supprimé ou archivé, et son compte utilisateur
> désactivé ou supprimé.
>
> ○ **Gestion** **des** **Enseignants** **(enseignant)** **et** **du**
> **Compte** **Utilisateur** **Lié** **:** ■ **Création** **:**
>
> ■ **Lorsque** l'administrateur enregistre un nouvel enseignant, il
> saisit ses informations (nom, prénom, date de naissance, téléphone,
> email professionnel).
>
> ■ **Lorsque** la fiche est créée, un compte utilisateur associé est
> généré (login, mot de passe initial, date création, statut actif), lié
> au type_utilisateur "Enseignant", à un groupe_utilisateur (ex:
> "Enseignants Chercheurs", "Membres Commission") et à un
> niveau_acces_donne.
>
> ■ **Lecture** **:**
>
> ■ **Lorsque** l'administrateur navigue dans cette section, il peut
> consulter la liste des enseignants, aficher les détails d'un profil
> enseignant et les informations du compte utilisateur lié.
>
> ■ Il peut également (avec les droits appropriés) consulter
> l'historique des rapports supervisés par un enseignant (en tant que
> directeur de mémoire) ou les évaluations auxquelles il a participé (en
> tant que membre de commission), principalement à des fins de
> supervision ou d'audit.
>
> ■ **Mise** **à** **jour** **:**
>
> ■ **Lorsque** des changements surviennent, l'administrateur peut
> mettre à jour les informations personnelles d'un enseignant et
> modifier les informations de son compte utilisateur associé.
>
> ■ **Suppression** **:**
>
> ■ **Lorsque** un enseignant n'est plus afilié, son profil peut être
> supprimé ou archivé, et son compte utilisateur désactivé ou supprimé.
>
> ○ **Gestion** **des** **type_utilisateur** **(Rôles)** **:**
>
> ■ **Création** **:** **Lorsque** un nouveau rôle fonctionnel est
> nécessaire, l'administrateur peut ajouter un nouveau type
> d'utilisateur (ex: "Auditeur Externe", "Responsable Qualité") avec un
> libellé et une description.
>
> ■ **Lecture** **:** Il peut lister et aficher les détails de tous les
> types d'utilisateurs existants.
>
> ■ **Mise** **à** **jour** **:** Il peut modifier le libellé ou la
> description d'un type d'utilisateur.
>
> ■ **Suppression** **:** Il peut supprimer un type d'utilisateur
> uniquement si aucun utilisateur n'y est actuellement assigné.
>
> ○ **Gestion** **des** **groupe_utilisateur** **(Groupes** **de**
> **Permissions)** **:**
>
> ■ **Création** **:** **Lorsque** une nouvelle segmentation des droits
> est requise, l'administrateur peut ajouter un nouveau groupe
> d'utilisateurs avec un libellé et une description de son objectif.
>
> ■ **Lecture** **:** Il peut lister et aficher les détails des groupes
> existants. ■ **Mise** **à** **jour** **:** Il peut modifier le libellé
> ou la description d'un groupe. ■ **Suppression** **:** Il peut
> supprimer un groupe uniquement si aucun
>
> utilisateur ou aucune permission (via rattacher) n'y est lié. ○
> **Gestion** **des** **niveau_acces_donne** **:**
>
> ■ **Création** **:** **Lorsque** une granularité d'accès aux données
> doit être afinée, l'administrateur peut définir un nouveau niveau
> d'accès avec un libellé et une description de ce qu'il permet ou
> restreint.
>
> ■ **Lecture** **:** Il peut lister et aficher les détails des niveaux
> d'accès existants. ■ **Mise** **à** **jour** **:** Il peut modifier le
> libellé ou la description d'un niveau
>
> d'accès.
>
> ■ **Suppression** **:** Il peut supprimer un niveau d'accès s'il n'est
> plus utilisé par aucun compte utilisateur.
>
> ○ **Gestion** **des** **Associations** **rattacher** **(Groupe**
> **Utilisateur** **\<-\>** **Traitement)** **:** ■ **Création** **:**
> **Lorsque** des droits spécifiques doivent être accordés,
>
> l'administrateur associe un groupe_utilisateur à un ou plusieurs
> traitement (actions ou fonctionnalités spécifiques du système,
> définies dans les référentiels) pour définir une permission.
>
> ■ **Lecture** **:** Il peut visualiser les permissions (la liste des
> traitement autorisés) pour chaque groupe_utilisateur.
>
> ■ **Suppression** **:** Il peut dissocier un traitement d'un
> groupe_utilisateur pour révoquer une permission.

3\. Configuration Générale et Paramétrage du Système

> (CRUD intégral par l'Administrateur Système pour la plupart des
> référentiels) ○ **Gestion** **des** **Référentiels** **(Paramètres**
> **Généraux)** **:**
>
> ■ **Lorsque** l'administrateur doit gérer les listes de valeurs et
> entités de base du système, il accède à des interfaces CRUD dédiées
> pour chacun des référentiels suivants :
>
> 1\. specialite (Spécialités des enseignants)
>
> 2\. fonction (Fonctions des enseignants/personnel) 3. grade (Grades
> académiques des enseignants) 4. ue (Unités d'Enseignement)
>
> 5\. ecue (Éléments Constitutifs d'UE)
>
> 6\. annee_academique (Années académiques)
>
> 7\. niveau_etude (Niveaux d'étude, ex: Master 1, Master 2) 8.
> entreprise (Entreprises pour les stages)
>
> 9\. niveau_approbation (Étapes/niveaux dans un workflow de validation
> de rapport)
>
> 10\. statut_jury (Rôles au sein d'un jury/commission, ex: Président,
> Rapporteur, Membre)
>
> 11\. action (Types d'actions système enregistrables pour l'audit, ex:
> "Création Utilisateur", "Soumission Rapport")
>
> 12\. traitement (Fonctionnalités/traitements du système auxquels les
> permissions peuvent être liées, ex: "Accès Module Admin", "Valider
> Rapport")
>
> 13\. message (Modèles de messages pour les communications internes ou
> les notifications)
>
> 14\. notification (Types/modèles de notifications système) ■ Pour
> chacun de ces 14 référentiels :
>
> ■ **Création** **:** **Lorsque** une nouvelle entrée est nécessaire
> (ex: nouvelle spécialité, nouvelle année académique), l'administrateur
> utilise un formulaire pour ajouter l'élément avec ses attributs.
>
> ■ **Lecture** **:** Il peut lister toutes les entrées d'un
> référentiel, les filtrer, les rechercher, et aficher les détails d'une
> entrée spécifique.
>
> ■ **Mise** **à** **jour** **:** Il peut modifier les informations
> d'une entrée existante. ■ **Suppression** **:** Il peut supprimer une
> entrée, en tenant compte des
>
> dépendances et des contraintes d'intégrité référentielle (ex: ne pas
> supprimer une annee_academique si des inscriptions y sont liées).
>
> ○ **Paramètres** **Généraux** **du** **Workflow** **et** **de**
> **l'Application** **:**
>
> ■ **Lorsque** les règles de fonctionnement doivent être ajustées,
> l'administrateur peut configurer :
>
> ■ Les dates limites pour la soumission des rapports, les corrections,
> etc. ■ Les règles de validation pour le module Conformité (ex: liste
> des
>
> documents obligatoires, formats de fichiers autorisés, tailles
> maximales).
>
> ■ Les paramètres des alertes système (ex: délai avant qu'un dossier en
> attente ne génère une alerte).
>
> ■ Les paramètres de la fonctionnalité de vote en ligne pour la
> commission (ex: nombre de tours de vote avant escalade).
>
> ■ Les options de configuration du chat intégré (ex: création de
> groupes de discussion par défaut pour la commission).
>
> ○ **Gestion** **des** **Modèles** **de** **Documents** **et** **de**
> **Notifications** **(complément** **aux** **référentiels** **message**
> **et** **notification)** **:**
>
> ■ **Lorsque** les formats des documents PDF générés ou des
> communications par courriel doivent être gérés, l'administrateur peut
> :
>
> ■ Téléverser, prévisualiser et gérer les modèles HTML/CSS utilisés
> pour la génération automatique des documents PDF (attestations, reçus,
> page de garde de PV, etc.).
>
> ■ Créer, modifier et gérer les modèles de notifications par courriel
> envoyées par le système (ex: email de bienvenue, notification de
> changement de statut, rappel), en utilisant potentiellement les
> message et notification des référentiels comme base.

4\. Gestion Académique et Administrative Spécifique

> (Principalement géré par le Responsable Scolarité pour les opérations
> courantes, l'Administrateur Système pour la configuration et les
> droits d'accès)
>
> ○ **Gestion** **des** **Inscriptions** **Administratives**
> **(inscrire)** **:** (Responsable Scolarité)
>
> ■ **Création** **:** **Lorsque** une inscription doit être
> enregistrée, le RS saisit l'inscription d'un etudiant à un
> niveau_etude pour une annee_academique, incluant le montant, la date,
> le statut du paiement, le numéro de reçu, etc.
>
> ■ **Lecture** **:** Il peut consulter l'historique des inscriptions
> d'un étudiant spécifique ou lister toutes les inscriptions par année
> académique ou niveau d'étude.
>
> ■ **Mise** **à** **jour** **:** Il peut mettre à jour les informations
> d'une inscription (ex: marquer un paiement comme effectué, enregistrer
> la décision de passage en année supérieure).
>
> ■ **Suppression** **:** La suppression d'un enregistrement
> d'inscription doit être une action contrôlée, avec des précautions et
> justifications.
>
> ○ **Gestion** **des** **Évaluations** **/** **Notes** **des**
> **Étudiants** **(evaluer)** **:** (Responsable Scolarité )
>
> ■ **Création** **:** **Lorsque** une note doit être enregistrée, le
> Responsable Scolarité saisit la note d'un etudiant pour une ecue
> donnée.
>
> ■ **Lecture** **:** Le Responsable Scolarité, l'enseignant concerné et
> l'étudiant concerné (via son module) peuvent consulter les notes.
> L'administrateur peut avoir une vue globale.
>
> ■ **Mise** **à** **jour** **:** Le Responsable Scolarité peut modifier
> une note (ex: en cas d'erreur de saisie, avec justification).
>
> ■ **Suppression** **:** Le Responsable Scolarité (avec des droits
> élevés) peut supprimer une note, cette action devant être
> exceptionnelle et tracée.
>
> ○ **Gestion** **des** **Stages** **(faire_stage)** **:** (Responsable
> Scolarité)
>
> ■ **Création** **:** **Lorsque** un étudiant effectue un stage, le RS
> enregistre les informations du stage : etudiant, entreprise
> partenaire, dates de début et de fin, sujet du stage, nom du tuteur en
> entreprise.
>
> ■ **Lecture** **:** Il peut lister tous les stages enregistrés et
> consulter les détails
>
> d'un stage spécifique.
>
> ■ **Mise** **à** **jour** **:** Il peut mettre à jour les informations
> d'un stage (ex: prolongation, changement de tuteur).
>
> ■ **Suppression** **:** Il peut supprimer un enregistrement de stage
> si nécessaire (ex: stage annulé).
>
> ○ **Gestion** **des** **Associations** **Enseignant-Grade**
> **(acquerir)** **:** (Administrateur Système / Responsable Scolarité)
>
> ■ **Création** **:** **Lorsque** un enseignant obtient un nouveau
> grade, on enregistre l'acquisition de ce grade par l'enseignant à une
> date_acquisition donnée.
>
> ■ **Lecture** **:** Il est possible de consulter les grades détenus
> par chaque enseignant.
>
> ■ **Mise** **à** **jour** **:** La date d'acquisition d'un grade peut
> être modifiée.
>
> ■ **Suppression** **:** L'enregistrement d'un grade pour un enseignant
> peut être supprimé.
>
> ○ **Gestion** **des** **Associations** **Enseignant-Fonction**
> **(occuper)** **:** (Administrateur Système / Responsable Scolarité)
>
> ■ **Création** **:** **Lorsque** un enseignant prend une nouvelle
> fonction, on assigne cette fonction à l'enseignant avec des dates de
> début et potentiellement de fin d'occupation.
>
> ■ **Lecture** **:** Il est possible de consulter les fonctions
> actuelles et passées occupées par les enseignants.
>
> ■ **Mise** **à** **jour** **:** Les dates d'occupation d'une fonction
> peuvent être modifiées.
>
> ■ **Suppression** **:** Une fonction peut être retirée à un enseignant
> (ou une date de fin est marquée).
>
> ○ **Gestion** **des** **Associations** **Enseignant-Spécialité**
> **(attribuer)** **:** (Administrateur Système / Responsable Scolarité)
>
> ■ **Création** **:** **Lorsque** un enseignant est compétent dans une
> spécialité, on lie l'enseignant à cette specialite.
>
> ■ **Lecture** **:** Il est possible de lister les spécialités
> maîtrisées par chaque enseignant, ou les enseignants par spécialité.

■ **Suppression** **:** Une spécialité peut être retirée à un
enseignant. 5. Supervision, Maintenance, Notifications et Audit

> (Principalement pour l'Administrateur Système)
>
> ○ **Suivi** **Global** **des** **Workflows** **et** **des**
> **Processus** **:**
>
> ■ **Lorsque** l'administrateur a besoin d'une vue d'ensemble des
> opérations, il peut accéder à des tableaux de bord de supervision
> montrant l'état de tous les rapports en cours, les goulots
> d'étranglement potentiels, et la
>
> charge de travail des différents services (conformité, commission). ○
> **Gestion** **des** **Procès-Verbaux** **(Aspects**
> **Administratifs)** **:**
>
> ■ **Lorsque** les PV sont validés par la commission, l'administrateur
> peut y avoir accès pour consultation, archivage oficiel.
>
> ■ Il peut gérer le signalement des mémoires/rapports éligibles à une
> publication externe (ex: sur DUMAS), basé sur les décisions de la
> commission.
>
> ○ **Gestion** **des** **Notifications** **Envoyées/Reçues**
> **(recevoir)** **:**
>
> ■ **(Système** **Automatique)** **Création** **:** **Lorsque** le
> système envoie une notification (basée sur un modèle de notification
> du référentiel) à un utilisateur, il enregistre cette instance dans
> recevoir avec la date_reception et un statut initial (ex: "Non lue").
>
> ■ **(Utilisateur)** **Lecture/Mise** **à** **jour** **:** Les
> utilisateurs (Étudiant, Enseignant, etc.) consultent leurs
> notifications et peuvent les marquer comme lues (le système enregistre
> la date_lecture).
>
> ■ **(Administrateur** **Système** **-** **pour** **maintenance)**
> **Suppression/Archivage** **:** **Lorsque** les notifications
> deviennent anciennes, l'administrateur peut disposer d'outils pour
> archiver ou supprimer en masse les notifications (ex: celles lues
> depuis plus de X mois) pour maintenir la performance de la table.
>
> ○ **Consultation** **des** **Journaux** **d'Audit** **(enregistrer,**
> **pister)** **:** ■ **Lecture** **:**
>
> ■ **Lorsque** l'administrateur a besoin de tracer des activités, il
> peut consulter le journal des actions des utilisateurs (enregistrer) :
> qui (quel utilisateur) a fait quoi (quelle action sur quelle entité et
> avec quel identifiant), quand (date_action), depuis quelle adresse_ip,
> avec quel user_agent et avec quels details_action (format JSON).
>
> ■ Il peut également consulter la traçabilité des accès aux
> fonctionnalités/traitements système (pister) : quel utilisateur a
> accédé à quel traitement et quand (date_pister), et si l'accès a été
> accordé (acceder).
>
> ○ **Outils** **d'Import/Export** **de** **Données** **:**
>
> ■ **Lorsque** des migrations de données ou des sauvegardes spécifiques
> sont nécessaires, l'administrateur peut disposer d'outils pour
> importer ou exporter des données dans des formats standards (CSV, XML,
> SQL).
>
> ○ **Maintenance** **Technique** **de** **Base** **:**
>
> ■ **Lorsque** des opérations de maintenance sont requises,
> l'administrateur peut (selon les fonctionnalités de la plateforme) :
>
> ■ Lancer des scripts de maintenance (ex: nettoyage de fichiers
>
> temporaires, optimisation de la base de données, réindexation).
>
> ■ Gérer les sauvegardes et les procédures de restauration (voir
> section 7 pour des améliorations).
>
> ■ Consulter l'état des mises à jour des composants de la plateforme et
> potentiellement les appliquer (si un mécanisme de mise à jour est
> intégré).

6\. **Reporting** **et** **Analytique** **Avancés**

> ○ **Lorsque** des analyses sur l'eficacité du processus sont
> demandées, l'administrateur peut générer ou configurer la génération
> de rapports statistiques avancés :
>
> ■ Taux de validation des rapports, délais moyens par étape,
> performance des encadreurs ou des membres de commission (de manière
> agrégée et anonymisée si nécessaire).
>
> ■ Tendances sur les thématiques de rapports, les types de corrections
> les plus fréquentes, etc.
>
> ○ Il peut configurer des tableaux de bord spécifiques pour la
> direction ou les responsables de filière.

7\. **Fonctionnalités** **à** **Ajouter** **(Évolutions** **Futures)** ○
**Gestion** **Avancée** **des** **Sauvegardes** **:**

> ■ **Lorsque** l'administrateur souhaite plus de contrôle sur les
> sauvegardes, il pourrait planifier des sauvegardes automatiques à des
> fréquences personnalisées (quotidiennes, hebdomadaires), choisir des
> types de sauvegarde (complète, incrémentielle), configurer des
> destinations de sauvegarde externes, et initier des restaurations
> sélectives de données ou des restaurations complètes en cas de besoin
> via une interface dédiée.
>
> ○ **Intégration** **avec** **un** **Système** **d'Annuaire**
> **Existant** **(LDAP/AD)** **:**
>
> ■ **Lorsque** l'établissement utilise un annuaire centralisé,
> permettre la synchronisation (uni ou bidirectionnelle, selon les
> besoins) des comptes utilisateurs (personnel, enseignants) avec cet
> annuaire pour simplifier la gestion des identités, des mots de passe
> et des accès.
>
> ○ **Module** **de** **Personnalisation** **de** **l'Interface**
> **Utilisateur** **(Thèmes/Branding)** **:**
>
> ■ **Lorsque** l'administrateur souhaite adapter l'apparence de la
> plateforme à l'image de l'établissement, il pourrait disposer d'outils
> pour modifier les couleurs principales, le logo afiché, les polices de
> caractères et certains textes d'accueil ou pieds de page, sans
> nécessiter de modification de code.
>
> ○ **Outils** **de** **Communication** **de** **Masse** **Améliorés**
> **:**
>
> ■ **Lorsque** des annonces importantes doivent être faites, permettre
> à
>
> l'administrateur d'envoyer des courriels ou des notifications ciblées
> à des groupes spécifiques d'utilisateurs (ex: tous les étudiants d'une
> filière, tous les membres de commission) avec des options de suivi de
> la délivrabilité (ouvertures, clics) et la possibilité d'utiliser des
> modèles enrichis.
>
> ○ **Gestion** **des** **Interconnexions** **avec** **d'Autres**
> **Systèmes** **:**
>
> ■ **Lorsque** d'autres systèmes de l'établissement (ex: système de
> gestion de la scolarité avancé, plateforme e-learning, système
> d'archivage institutionnel) ont besoin d'interagir avec
> "GestionMySoutenance", l'administrateur pourrait configurer et gérer
> les points d'intégration et les protocoles d'échange pour un transfert
> de données sécurisé et contrôlé, en définissant les permissions
> d'accès pour chaque système externe.
>
> ○ **Tableau** **de** **Bord** **de** **Sécurité** **Détaillé** **et**
> **Configuration** **des** **Alertes** **de** **Sécurité** **:**
>
> ■ **Lorsque** une surveillance proactive de la sécurité est
> nécessaire, un tableau de bord dédié pourrait aficher les tentatives
> de connexion échouées répétées, les activités suspectes (ex: accès
> depuis des IP inhabituelles), les alertes de sécurité des composants
> logiciels, et permettre de gérer les listes d'adresses IP bloquées ou
> autorisées. L'administrateur pourrait configurer des seuils pour
> recevoir des alertes de sécurité par email.
>
> ○ **Module** **de** **Gestion** **des** **Consentements**
> **(Conformité** **RGPD/Protection** **des** **Données)** **:**
>
> ■ **Lorsque** la gestion du consentement des utilisateurs pour le
> traitement de leurs données personnelles est requise, un module
> permettrait de configurer les politiques de confidentialité, de tracer
> le consentement donné par chaque utilisateur lors de son inscription
> ou pour des traitements spécifiques, et de gérer les demandes d'accès,
> de rectification ou de suppression des données personnelles
> conformément à la réglementation.
>
> ○ **Outils** **de** **Débogage** **et** **de** **Diagnostic** **Plus**
> **Poussés** **:**
>
> ■ **Lorsque** des problèmes techniques complexes surviennent,
> l'administrateur pourrait accéder à des outils de diagnostic plus
> avancés pour tracer les requêtes SQL, analyser les performances des
> différentes parties de l'application en temps réel, simuler des
> actions utilisateur pour reproduire des erreurs, et identifier plus
> rapidement la source des problèmes.
>
> ○ **Paramétrage** **Fin** **des** **Notifications** **et** **des**
> **Workflows** **:**
>
> ■ **Lorsque** un contrôle plus granulaire des notifications est
> souhaité, permettre à l'administrateur de définir via une interface
> graphique les
>
> conditions précises (triggers) qui déclenchent quelles notifications,
> pour quels rôles/utilisateurs, et par quels canaux (email,
> notification in-app, chat), avec la possibilité de personnaliser le
> contenu de chaque notification.
>
> ■ De même, pour les workflows (ex: validation de rapport), permettre
> une certaine configuration des étapes, des transitions et des acteurs
> impliqués sans modification de code.
>
> ○ **Gestion** **des** **Périodes** **de** **Maintenance**
> **Programmée** **et** **Communication** **Utilisateurs** **:**
>
> ■ **Lorsque** une maintenance planifiée du système est nécessaire (ex:
> mise à jour majeure, intervention sur la base de données),
> l'administrateur pourrait :
>
> 1\. Activer un "mode maintenance" via l'interface, afichant un message
> personnalisé (configurable) aux utilisateurs tentant d'accéder à la
> plateforme.
>
> 2\. Définir la portée du mode maintenance (ex: blocage total, accès en
> lecture seule, accès limité à certains rôles administratifs).
>
> 3\. Programmer l'activation et la désactivation automatique de ce
> mode. 4. Envoyer une notification préalable aux utilisateurs concernés
> pour les
>
> informer de l'indisponibilité à venir.

Ce module Administration est crucial pour assurer la pérennité, la
sécurité, la configurabilité et l'eficacité de la plateforme
"GestionMySoutenance", en fournissant les outils nécessaires pour gérer
tous les aspects du système et de ses utilisateurs.
