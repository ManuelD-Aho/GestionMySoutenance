**Audit Complet et Feuille de Route pour "Gestion MySoutenance" ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAm4AAAAGCAYAAAB6kBINAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAHpJREFUaIHt2bEJwzAQBVAJpCECmSoLZBSpUOVhMkjWyCDnSsE4pI1jeK+6D1dc+eFSAgDgFPJ27r3nr5sAAPxUay1yzjFzmcMY41pKuR1zFgAAe8uyvFJKj5nfxa3WeomI+yFXAQDwISKeaVPcvEoBAP7U/lUKAMBJrCfDFzlVHu0qAAAAAElFTkSuQmCC)**

**Phase 1: Analyse de l'Existant et Vision Cible** 

**Analyse de l'Arborescence Actuelle** 

**L'arborescence fournie est celle d'une application PHP structurée avec un point d'entrée public, un système de routage, et une séparation entre le backend (logique, modèles) et le frontend (vues).** 

- **.idea/ : Contient les fichiers de configuration spécifiques à l'IDE PhpStorm (ou autre IDE JetBrains) et n'impacte pas le fonctionnement de l'application elle-même.**  
- **Public/ :**  
  - **index.php : Sert de point d'entrée unique (Front Controller) pour toutes les requêtes HTTP. Il initialise l'environnement (autoloading, variables d'environnement, gestion des erreurs), configure le routeur FastRoute, et distribue les requêtes aux contrôleurs appropriés.**  
- **routes/ :**  
  - **web.php : Définit les routes de l'application, associant des URI et des méthodes HTTP à des classes de contrôleurs et leurs méthodes.** 
- **src/ : Contient le code source principal de l'application.** 
- **Backend/ :**  
- **Controller/ :**  
  - **AssetController.php : Gère le service des fichiers CSS statiques.** 
  - **AuthentificationController.php : Gère la logique de connexion et de déconnexion des utilisateurs.** 
  - **BaseController.php : Fournit des fonctionnalités de base aux autres contrôleurs, notamment une méthode render pour afficher les vues.**  
  - **DashboardController.php : Responsable de l'affichage du tableau de bord principal après connexion, adaptant son contenu en fonction du rôle de l'utilisateur.** 
- **Model/ : Contient les classes d'accès aux données.** 
  - **BaseModel.php : Classe de base abstraite fournissant des méthodes CRUD génériques (findAll, find, create, update, delete, findBy, findOneBy, count, query, gestion des transactions) pour interagir avec la base de données via PDO. Elle est conçue pour être étendue par des modèles spécifiques à chaque table.** 
- **Les autres fichiers modèles (ex: Acquerir.php, Action.php, Affecter.php, AnneeAcademique.php, Approuver.php, Attribuer.php,**  

**CompteRendu.php, Donner.php, Ecue.php, Enregistrer.php, Enseignant.php, Entreprise.php, Etudiant.php, Evaluer.php, FaireStage.php, Fonction.php, Grade.php, GroupeUtilisateur.php, Inscrire.php, Message.php, NiveauAccesDonne.php, NiveauApprobation.php, NiveauEtude.php, Notification.php, Occuper.php, PersonnelAdministratif.php, Pister.php, RapportEtudiant.php, Rattacher.php, Recevoir.php, Rendre.php, Specialite.php, StatutJury.php, Traitement.php, TypeUtilisateur.php, Ue.php, Utilisateur.php, Valider.php ) sont destinés à représenter et manipuler les données de leurs tables respectives. Beaucoup ne sont pas encore correctement** 

**structurés (namespace, héritage).**  

- **Config/ :**  
- **Database.php : Gère la connexion à la base de données en utilisant PDO, implémentant un singleton pour assurer une instance unique de la connexion.**  
- **Frontend/ :**  
- **css/ : Contient les fichiers CSS.** 
  - **dashboard\_style.css : Styles spécifiques pour le tableau de** 

**bord.** ■ **style.css : Styles généraux et pour la page de connexion.** 

- **views/ : Contient les fichiers PHP pour l'affichage (templates HTML).** 
  - **AdministrateurSystem/ : Vues pour l'admin (ex: dashboard.php ).** 
  - **Auth/ : Vues pour l'authentification (ex: login.php ).** 
  - **common/ : Éléments de vue communs (ex: dashboard.php (semble être un placeholder), header.php, menu.php ).** 
  - **dashboards/ : Contenus spécifiques pour les tableaux de bord par rôle (ex: admin\_dashboard\_content.php, student\_dashboard\_content.php (contient aussi le contenu du teacher et default dashboard) ).** 
  - **errors/ : Vues pour les pages d'erreur (ex: 404.php, 405.php ).** 
  - **layout/ : Fichier de layout principal (ex: app.php ).** 
  - **Dossiers pour les modules (Etudiant, Enseignant, PersonnelAdministratif) sont présents mais vides.** 
- **composer.json : Définit les dépendances du projet (PHP, extensions, bibliothèques comme phpdotenv et fast-route) et la configuration de l'autoloading PSR-4.**  
- **docker-compose.yml : Configure les services Docker pour l'application (serveur web PHP, base de données MySQL, phpMyAdmin).** 
- **mysoutenance.sql : Script SQL pour la création du schéma de la base de données et l'insertion de données initiales.** 
- **php.ini : Fichier de configuration PHP pour l'environnement de développement, activant l'affichage des erreurs et les extensions nécessaires.** 

**Proposition d'Arborescence Cible Complète** 

**Basée sur les modules fonctionnels (Administration, Commission, Étudiant, Personnel Administratif) et MVC :** 

**Directory structure:**

└── **manueld-aho-gestionmysoutenance/**
**
`    `├── **README.md** 
**
`    `├── **apache-vhost.conf** 
**
`    `├── **composer.json**
**
`    `├── **composer.lock**
**
`    `├── **composer.phar** 
**
`    `├── **docker-compose.yml** 
**
`    `├── **Dockerfile** 
**
`    `├── **mysoutenance.sql**
**
`    `├── **mysoutenance2.sql**
**
`    `├── **php.ini** 
**
`    `├── **Public/** 
**
`    `│**   ├── **index.php** 
**
`    `│**   ├── **.htaccess** 
**
`    `│**   ├── **css/** 
**
`    `│**   │**   ├── **dashboard\_style.css**
**
`    `│**   │**   ├── **style.css** 
**
`    `│**   │**   └── **bulma/** 
**
`    `│**   │**       ├── **README.md** 
**
`    `│**   │**       ├── **bulma.scss** 
**
`    `│**   │**       ├── **LICENSE** 
**
`    `│**   │**       ├── **package.json** 
**
`    `│**   │**       ├── **css/** 
**
`    `│**   │**       │**   ├── **bulma.css** 
**
`    `│**   │**       │**   └── **versions/** 
**
`    `│**   │**       │**       ├── **bulma-no-dark-mode.css** 

│**   │**       │**       ├── **bulma-no-helpers-prefixed.css** │**   │**       │**       ├── **bulma-no-helpers.css** 

│**   │**       │**       └── **bulma-prefixed.css** 

│**   │**       ├── **sass/** 

│**   │**       │**   ├── **\_index.scss** 

│**   │**       │**   ├── **base/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **animations.scss**

│**   │**       │**   │**   ├── **generic.scss**

│**   │**       │**   │**   ├── **minireset.scss**

│**   │**       │**   │**   └── **skeleton.scss**

│**   │**       │**   ├── **components/**

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **breadcrumb.scss**

│**   │**       │**   │**   ├── **card.scss** 

│**   │**       │**   │**   ├── **dropdown.scss**

│**   │**       │**   │**   ├── **menu.scss** 

│**   │**       │**   │**   ├── **message.scss**

│**   │**       │**   │**   ├── **modal.scss** 

│**   │**       │**   │**   ├── **navbar.scss** 

│**   │**       │**   │**   ├── **pagination.scss**

│**   │**       │**   │**   ├── **panel.scss** 

│**   │**       │**   │**   └── **tabs.scss** 

│**   │**       │**   ├── **elements/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **block.scss** 

│**   │**       │**   │**   ├── **box.scss** 

│**   │**       │**   │**   ├── **button.scss** 

│**   │**       │**   │**   ├── **content.scss**

│**   │**       │**   │**   ├── **delete.scss**

│**   │**       │**   │**   ├── **icon.scss** 

│**   │**       │**   │**   ├── **image.scss** 

│**   │**       │**   │**   ├── **loader.scss** 

│**   │**       │**   │**   ├── **notification.scss**

│**   │**       │**   │**   ├── **progress.scss**

│**   │**       │**   │**   ├── **table.scss** 

│**   │**       │**   │**   ├── **tag.scss** 

│**   │**       │**   │**   └── **title.scss** 

│**   │**       │**   ├── **form/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **checkbox-radio.scss** │**   │**       │**   │**   ├── **file.scss** 

│**   │**       │**   │**   ├── **input-textarea.scss** │**   │**       │**   │**   ├── **select.scss**

│**   │**       │**   │**   ├── **shared.scss** 

│**   │**       │**   │**   └── **tools.scss** 

│**   │**       │**   ├── **grid/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **columns.scss**

│**   │**       │**   │**   └── **grid.scss** 

│**   │**       │**   ├── **helpers/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **aspect-ratio.scss** 

│**   │**       │**   │**   ├── **border.scss** 

│**   │**       │**   │**   ├── **color.scss** 

│**   │**       │**   │**   ├── **flexbox.scss** 

│**   │**       │**   │**   ├── **float.scss** 

│**   │**       │**   │**   ├── **gap.scss** 

│**   │**       │**   │**   ├── **other.scss** 

│**   │**       │**   │**   ├── **overflow.scss**

│**   │**       │**   │**   ├── **position.scss**

│**   │**       │**   │**   ├── **spacing.scss**

│**   │**       │**   │**   ├── **typography.scss**

│**   │**       │**   │**   └── **visibility.scss** 

│**   │**       │**   ├── **layout/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **container.scss**

│**   │**       │**   │**   ├── **footer.scss**

│**   │**       │**   │**   ├── **hero.scss** 

│**   │**       │**   │**   ├── **level.scss** 

│**   │**       │**   │**   ├── **media.scss** 

│**   │**       │**   │**   └── **section.scss**

│**   │**       │**   ├── **themes/** 

│**   │**       │**   │**   ├── **\_index.scss** 

│**   │**       │**   │**   ├── **dark.scss** 

│**   │**       │**   │**   ├── **light.scss** 
**
`    `│**   │**       │**   │**   └── **setup.scss** 
**
`    `│**   │**       │**   └── **utilities/** 
**
`    `│**   │**       │**       ├── **\_index.scss** 
**
`    `│**   │**       │**       ├── **controls.scss**
**
`    `│**   │**       │**       ├── **css-variables.scss**
**
`    `│**   │**       │**       ├── **derived-variables.scss**
**
`    `│**   │**       │**       ├── **extends.scss**
**
`    `│**   │**       │**       ├── **functions.scss**
**
`    `│**   │**       │**       ├── **initial-variables.scss**
**
`    `│**   │**       │**       └── **mixins.scss** 
**
`    `│**   │**       └── **versions/** 
**
`    `│**   │**           ├── **bulma-no-dark-mode.scss** 
**
`    `│**   │**           ├── **bulma-no-helpers-prefixed.scss**
**
`    `│**   │**           ├── **bulma-no-helpers.scss** 
**
`    `│**   │**           └── **bulma-prefixed.scss**
**
`    `│**   └── **js/** 
**
`    `│**       └── **main.js** 
**
`    `├── **routes/** 
**
`    `│**   └── **web.php** 
**
`    `└── **src/** 
**
`        `├── **Backend/** 
**
`        `│**   ├── **Controller/** 
**
`        `│**   │**   ├── **AssetController.php**
**
`        `│**   │**   ├── **AuthController.php** 
**
`        `│**   │**   ├── **AuthentificationController.php**
**
`        `│**   │**   ├── **BaseController.php** 
**
`        `│**   │**   ├── **DashboardController.php**
**
`        `│**   │**   ├── **Administration/** 
**
`        `│**   │**   │**   ├── **AdminDashboardController.php**
**
`        `│**   │**   │**   ├── **ConfigSystemeController.php**
**
`        `│**   │**   │**   ├── **GestionAcadController.php**
**
`        `│**   │**   │**   ├── **HabilitationController.php**
**
`        `│**   │**   │**   ├── **ReferentialController.php**
**
`        `│**   │**   │**   ├── **ReportingController.php**
**
`        `│**   │**   │**   ├── **SupervisionController.php**
**
`        `│**   │**   │**   └── **UtilisateurController.php**
**
`        `│**   │**   ├── **Commission/** 
**
`        `│**   │**   │**   ├── **CommissionDashboardController.php**
**
`        `│**   │**   │**   ├── **CommunicationCommissionController.php**         │**   │**   │**   ├── **CorrectionCommissionController.php**
**
`        `│**   │**   │**   ├── **PvController.php** 
**
`        `│**   │**   │**   └── **ValidationRapportController.php**
**
`        `│**   │**   ├── **Common/** 
**
`        `│**   │**   │**   └── **NotificationController.php**
**
`        `│**   │**   ├── **Etudiant/** 
**
`        `│**   │**   │**   ├── **DocumentEtudiantController.php**
**
`        `│**   │**   │**   ├── **EtudiantDashboardController.php**
**
`        `│**   │**   │**   ├── **ProfilEtudiantController.php**
**
`        `│**   │**   │**   ├── **RapportController.php** 
**
`        `│**   │**   │**   └── **ReclamationEtudiantController.php**
**
`        `│**   │**   └── **PersonnelAdministratif/**
**
`        `│**   │**       ├── **CommunicationInterneController.php**
**
`        `│**   │**       ├── **ConformiteController.php**
**
`        `│**   │**       ├── **PersonnelDashboardController.php**
**
`        `│**   │**       └── **ScolariteController.php**
**
`        `│**   ├── **Model/** 
**
`        `│**   │**   ├── **Acquerir.php** 
**
`        `│**   │**   ├── **Action.php** 
**
`        `│**   │**   ├── **AnneeAcademique.php**
**
`        `│**   │**   ├── **Approuver.php** 
**
`        `│**   │**   ├── **Attribuer.php** 
**
`        `│**   │**   ├── **BaseModel.php** 
**
`        `│**   │**   ├── **CompteRendu.php** 
**
`        `│**   │**   ├── **Conversation.php**
**
`        `│**   │**   ├── **DecisionPassageRef.php**
**
`        `│**   │**   ├── **DecisionValidationPvRef.php**
**
`        `│**   │**   ├── **DecisionVoteRef.php**
**
`        `│**   │**   ├── **DocumentSoumis.php**
**
`        `│**   │**   ├── **Ecue.php** 
**
`        `│**   │**   ├── **Enregistrer.php** 
**
`        `│**   │**   ├── **Enseignant.php** 
**
`        `│**   │**   ├── **Entreprise.php** 
**
`        `│**   │**   ├── **Etudiant.php** 
**
`        `│**   │**   ├── **Evaluer.php** 
**
`        `│**   │**   ├── **FaireStage.php** 
**
`        `│**   │**   ├── **Fonction.php** 
**
`        `│**   │**   ├── **Grade.php** 
**
`        `│**   │**   ├── **GroupeUtilisateur.php**
**
`        `│**   │**   ├── **Inscrire.php** 
**
`        `│**   │**   ├── **LectureMessage.php**
**
`        `│**   │**   ├── **MessageChat.php**
**
`        `│**   │**   ├── **MessageModele.php**
**
`        `│**   │**   ├── **NiveauAccesDonne.php**
**
`        `│**   │**   ├── **NiveauApprobation.php**
**
`        `│**   │**   ├── **NiveauEtude.php** 
**
`        `│**   │**   ├── **NotificationModele.php**
**
`        `│**   │**   ├── **Occuper.php** 
**
`        `│**   │**   ├── **ParticipantConversation.php**
**
`        `│**   │**   ├── **PersonnelAdministratif.php**
**
`        `│**   │**   ├── **Pister.php** 
**
`        `│**   │**   ├── **PvSessionRapport.php**
**
`        `│**   │**   ├── **RapportEtudiant.php** 
**
`        `│**   │**   ├── **Rattacher.php** 
**
`        `│**   │**   ├── **Recevoir.php** 
**
`        `│**   │**   ├── **Reclamation.php** 
**
`        `│**   │**   ├── **Rendre.php** 
**
`        `│**   │**   ├── **Specialite.php** 
**
`        `│**   │**   ├── **StatutConformiteRef.php**
**
`        `│**   │**   ├── **StatutJury.php** 
**
`        `│**   │**   ├── **StatutPaiementRef.php**
**
`        `│**   │**   ├── **StatutPvRef.php** 
**
`        `│**   │**   ├── **StatutRapportRef.php**
**
`        `│**   │**   ├── **StatutReclamationRef.php**
**
`        `│**   │**   ├── **Traitement.php** 
**
`        `│**   │**   ├── **TypeDocumentRef.php**
**
`        `│**   │**   ├── **TypeUtilisateur.php** 
**
`        `│**   │**   ├── **Ue.php** 
**
`        `│**   │**   ├── **Utilisateur.php** 
**
`        `│**   │**   ├── **ValidationPv.php** 
**
`        `│**   │**   ├── **Valider.php** 
**
`        `│**   │**   └── **VoteCommission.php**
**
`        `│**   ├── **Service/** 
**
`        `│**   │**   ├── **AuthService.php** 
**
`        `│**   │**   ├── **DocumentGeneratorService.php**
**
`        `│**   │**   ├── **NotificationService.php**
**
`        `│**   │**   └── **PermissionService.php**
**
`        `│**   └── **Util/** 
**
`        `│**       └── **FormValidator.php** 
**
`        `├── **Config/** 
**
`        `│**   └── **Database.php** 
**
`        `└── **Frontend/** 
**
`            `└── **views/** 
**
`                `├── **Administration/** 
**
`                `│**   ├── **dashboard\_admin.php**
**
`                `│**   ├── **reporting\_admin.php**
**
`                `│**   ├── **ConfigSysteme/**
**
`                `│**   │**   ├── **modeles\_documents.php**
**
`                `│**   │**   └── **parametres\_generaux.php**
**
`                `│**   ├── **GestionAcad/**
**
`                `│**   │**   ├── **form\_inscription.php**
**
`                `│**   │**   ├── **form\_note.php** 
**
`                `│**   │**   ├── **liste\_inscriptions.php**
**
`                `│**   │**   └── **liste\_notes.php**
**
`                `│**   ├── **Habilitations/** 
**
`                `│**   │**   ├── **form\_groupe.php** 
**
`                `│**   │**   ├── **form\_niveau\_acces.php**
**
`                `│**   │**   ├── **form\_traitement.php**
**
`                `│**   │**   ├── **form\_type\_utilisateur.php**
**
`                `│**   │**   ├── **gestion\_rattachements.php**
**
`                `│**   │**   ├── **liste\_groupes.php**
**
`                `│**   │**   ├── **liste\_niveaux\_acces.php**
**
`                `│**   │**   ├── **liste\_traitements.php**
**
`                `│**   │**   └── **liste\_types\_utilisateur.php**
**
`                `│**   ├── **Referentiels/** 
**
`                `│**   │**   ├── **crud\_referentiel\_generique.php**                 │**   │**   └── **liste\_referentiels.php**
**
`                `│**   ├── **Supervision/** 
**
`                `│**   │**   ├── **journaux\_audit.php** 
**
`                `│**   │**   ├── **maintenance.php**
**
`                `│**   │**   └── **suivi\_workflows.php**
**
`                `│**   └── **Utilisateurs/** 
**
`                `│**       ├── **form\_enseignant.php**
**
`                `│**       ├── **form\_etudiant.php**
**
`                `│**       ├── **form\_personnel.php**
**
`                `│**       ├── **liste\_enseignants.php**
**
`                `│**       ├── **liste\_etudiants.php**
**
`                `│**       └── **liste\_personnel.php**
**
`                `├── **Auth/** 
**
`                `│**   └── **login.php** 
**
`                `├── **Commission/** 
**
`                `│**   ├── **corrections\_commission.php**
**
`                `│**   ├── **dashboard\_commission.php**
**
`                `│**   ├── **historique\_commission.php**
**
`                `│**   ├── **PV/** 
**
`                `│**   │**   ├── **consulter\_pv.php**
**
`                `│**   │**   ├── **rediger\_pv.php** 
**
`                `│**   │**   └── **valider\_pv.php** 
**
`                `│**   └── **Rapports/** 
**
`                `│**       ├── **details\_rapport\_commission.php**                 │**       ├── **interface\_vote.php**
**
`                `│**       └── **liste\_rapports\_a\_traiter.php**
**
`                `├── **common/** 
**
`                `│**   ├── **chat\_interface.php**
**
`                `│**   ├── **dashboard.php** 
**
`                `│**   ├── **header.php** 
**
`                `│**   ├── **menu.php** 
**
`                `│**   └── **notifications\_panel.php**
**
`                `├── **dashboards/** 
**
`                `│**   ├── **admin\_dashboard\_content.php**
**
`                `│**   └── **student\_dashboard\_content.php**                 ├── **errors/** 
**
`                `│**   ├── **403.php** 
**
`                `│**   ├── **404.php** 
**
`                `│**   ├── **405.php** 
**
`                `│**   └── **500.php** 
**
`                `├── **Etudiant/** 
**
`                `│**   ├── **dashboard\_etudiant.php**
**
`                `│**   ├── **mes\_documents.php**
**
`                `│**   ├── **profil\_etudiant.php** 
**
`                `│**   ├── **ressources\_etudiant.php**
**
`                `│**   ├── **Rapport/** 
**
`                `│**   │**   ├── **soumettre\_corrections.php**
**
`                `│**   │**   ├── **soumettre\_rapport.php**
**
`                `│**   │**   └── **suivi\_rapport.php** 
**
`                `│**   └── **Reclamation/** 
**
`                `│**       ├── **soumettre\_reclamation.php**
**
`                `│**       └── **suivi\_reclamations.php**
**
`                `├── **layout/** 
**
`                `│**   └── **app.php** 
**
`                `└── **PersonnelAdministratif/**
**
`                    `├── **dashboard\_personnel.php**
**
`                    `├── **Conformite/** 
**
`                    `│**   ├── **details\_rapport\_conformite.php**
**
`                    `│**   ├── **liste\_rapports\_a\_verifier.php**
**
`                    `│**   └── **liste\_rapports\_traites\_conformite.php**                     └── **Scolarite/** 
**
`                        `├── **generation\_documents\_scolarite.php**                         ├── **gestion\_etudiants\_scolarite.php**
**
`                        `├── **gestion\_inscriptions\_scolarite.php**
**
`                        `└── **gestion\_notes\_scolarite.php**

![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnQAAABHCAYAAACOGNrlAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAsxJREFUeJzt2bGKXHUUx/HfuTsQEnYlIAhpbOx8BivfYRpbW7t9gOyD+ADKiGjtW6wPIRgLkUgkyd5jsbuVc5cUZuTg51Nd5pw7/Msv/5sAADBaJcnVVS/Pn/9zeDgcKvt99qc+FQAAOSTZJ70x7qrq5C7o/vjz9Zfp/mRjeVmrL5JleQ/nBADgiE7enKVfbuXcknxzfv7oOkl2SXKzrl9U1efb/1jZjkMAAP5tlWR9YN6d6+7+uararRsAwHCCDgBgOEEHADCcoAMAGE7QAQAMJ+gAAIYTdAAAwwk6AIDhBB0AwHCCDgBgOEEHADCcoAMAGE7QAQAMJ+gAAIYTdAAAwwk6AIDhBB0AwHCCDgBgOEEHADCcoAMAGE7QAQAMJ+gAAIYTdAAAwwk6AIDhBB0AwHCCDgBgOEEHADCcoAMAGE7QAQAMJ+gAAIbbJclvL359neSvYwvdXUmdpU56LgCA/7furqqbBxbWD84/TnIXdD9+/11XdR//r2RZlk5a0gEAnEgnnT7eZ/crl5eXSXJ77/bLi9+/TfqzY5tVS6X7Sfs8CwBwMp28rfSrzfmar5599PSHqupdkjx+/OTDpJ9tvtBdVS7oAABOpbtTVU+35tV5dP+8u31hrYeKTcwBAJzWXX+9U4T5jAoAMJygAwAYTtABAAwn6AAAhhN0AADDCToAgOEEHQDAcIIOAGA4QQcAMJygAwAYTtABAAwn6AAAhhN0AADDCToAgOEEHQDAcIIOAGA4QQcAMJygAwAYTtABAAwn6AAAhhN0AADDCToAgOEEHQDAcIIOAGA4QQcAMJygAwAYTtABAAwn6AAAhtslSdWyJr3+14cBAODd9NlZ3z/vbn9Zvk71Txv7S3q9SLnNAwA4le68qVpebs3f3ry6rrroJKkkuepePj0c6vj6Pvv9+zgmAAAPORyS5HB0tt/v16rqo0MAAGb5Gzrbfaf1PI0lAAAAAElFTkSuQmCC)

**Phase 2: Identification des Défauts et Plan de Refactorisation** 

**Identification des Défauts du Code Actuel** 

1\. **Modèles (src/Backend/Model/) :**  

- **Namespacing Incomplet/Incorrect : Plusieurs modèles n'ont pas de déclaration namespace (ex: Affecter.php, AnneeAcademique.php, etc.) ou ont un namespace mais manquent le use PDO; et use Backend\Model\BaseModel; (ex: Action.php avant correction ). Ceci empêche l'autoloading PSR-4 correct et peut causer des conflits.** 
- **Absence d'Héritage de BaseModel.php : De nombreux modèles (ex: Affecter.php, AnneeAcademique.php, etc.) réimplémentent des méthodes CRUD basiques (getAll, getById, create, update, delete) au lieu d'hériter de BaseModel.php.** 
- **Implications :**  
  - **Duplication massive de code.** 
    - **Manque de cohérence dans la gestion des erreurs et la préparation des requêtes.** 
    - **Difficulté de maintenance : toute amélioration des méthodes CRUD doit être répliquée partout.** 
    - **BaseModel.php offre déjà findAll, find (équivalent de getById), create, update, delete, findBy, findOneBy, count.** 
- **Gestion des Clés Composites :**  
  - **Acquerir.php (qui hérite correctement) a dû implémenter findByCompositeKey, updateByCompositeKey, deleteByCompositeKey car BaseModel.php est conçu pour une clé primaire simple ($primaryKey). Ce n'est pas un défaut en soi, mais une nécessité pour les clés composites.** ■ **Les modèles comme Affecter.php, Approuver.php, Attribuer.php, Donner.php, Evaluer.php, FaireStage.php, Inscrire.php, Occuper.php, Pister.php, Rattacher.php, Recevoir.php, Rendre.php, Valider.php qui ont des clés composites ou utilisent des clés non standard comme PK dans leurs méthodes getById (id\_enseignant pour Affecter alors que PK est composite) ne bénéficieront pas de BaseModel.find() sans adaptation.** 
  1. **Conventions de Nommage : Certains modèles utilisent le nom de la table directement (ex: Action.php ), d'autres ont un nom différent (aucun exemple flagrant dans ceux qui sont des classes, mais il faut veiller à la cohérence). Il est recommandé de suffixer par Model (ex: ActionModel.php).**  
2. **Authentification (src/Backend/Controller/AuthentificationController.php, src/Backend/Model/Utilisateur.php ) :** 
   1. **Faille de Sécurité Majeure : La méthode authenticate dans Utilisateur.php compare les mots de passe en clair.** 

**PHP**

**// if ($mot\_de\_passe\_fourni === $user['mot\_de\_passe']) { // Comparaison en clair**

**DANGEREUSE //     return $user; // }**

**Ceci est une vulnérabilité critique. Les mots de passe ne doivent jamais être stockés en clair. Ils doivent être hachés.** 

3. **Gestion des Rôles et Permissions (src/Backend/Controller/DashboardController.php ) :** 
- **getUserRoleLabel(?int $userTypeId) : Utilise un switch sur $userTypeId avec des valeurs codées en dur pour déterminer le libellé du rôle.**

  **PHP**

  **// switch ($userTypeId) {**

  **//     case 1: return 'Administrateur Système'; // Supposez que l'ID 1 est Admin //     // ...**

  **// }**

- **getMenuItemsForRole(string $role) : Utilise également un switch sur le libellé du rôle (obtenu précédemment) pour définir les items du menu.** 
- **Faiblesse : Cette approche est rigide, difficile à maintenir et ne correspond**  

**pas au système de permissions flexible décrit dans Module Administration.pdf (basé sur type\_utilisateur, groupe\_utilisateur, traitement, rattacher ). La sidebar et les accès devraient être dynamiquement construits à partir des permissions en base de données.**  

4. **Vues (src/Frontend/views/) :**  
   1. **Beaucoup de vues sont des placeholders, vides ou très basiques (ex: src/Frontend/views/AdministrateurSystem/dashboard.php, src/Frontend/views/common/dashboard.php, src/Frontend/views/common/header.php ).** 
   1. **Le layout app.php est une bonne base avec Tailwind CSS, mais le contenu dynamique des dashboards (admin\_dashboard\_content.php, student\_dashboard\_content.php ) est encore simulé.** 
4. **Routage (routes/web.php ) :**
   1. **Les routes sont minimales. Seules quelques routes pour la page d'accueil, les assets, la connexion/déconnexion, un exemple pour /admin/users, et le tableau de bord sont définies. Cela est largement insuffisant pour couvrir toutes les fonctionnalités des quatre modules.**  
6. **Cohérence Schéma SQL vs. PDF (Historique) :**  
   1. **Le mysoutenance.sql actuel (fourni sous mysoutenance.txt et [602-991]) utilise numero\_utilisateur VARCHAR(50) comme PK pour utilisateur, et de même pour etudiant (numero\_carte\_etudiant), enseignant (numero\_enseignant), et personnel\_administratif (numero\_personnel\_administratif). Les autres tables ont majoritairement des id\_... INT AUTO\_INCREMENT pour leurs PKs.**  
   1. **Les PDF (notamment Module Administration.pdf) ont été rédigés en supposant ce schéma SQL actuel, et les descriptions des fonctionnalités de gestion des utilisateurs y font référence. Par exemple, Module Administration.pdf mentionne numero\_utilisateur en parlant de la** 

      **création de compte.** 

- **Conclusion : Le schéma SQL mysoutenance.txt est la référence actuelle et les développements doivent s'y conformer. Les ajustements de PK (non auto-incrémenté pour les 4 tables utilisateurs, auto-incrémenté pour les autres) ont été faits dans ce SQL.** 

**Stratégie de Refactorisation Détaillée** 

1. **Modèles :**  
- **Namespace et Héritage :**  
  - **Pour chaque modèle PHP (ex: Affecter.php, AnneeAcademique.php, etc.) :**  
    - **Ajouter le namespace correct : namespace Backend\Model;.**  
    - **Ajouter les use PDO; et use Backend\Model\BaseModel;.**  
    - **Faire hériter la classe de BaseModel : class NomDeTable extends BaseModel.**  
    - **Définir protected string $table = 'nom\_de\_la\_table\_sql';.** 
    - **Définir protected string $primaryKey = 'nom\_de\_la\_cle\_primaire\_simple'; si la table a une clé primaire simple et qu'on souhaite utiliser BaseModel::find(), BaseModel::update(), BaseModel::delete(). Pour les 4 tables utilisateurs, cette variable ne sera pas utilisée par ces méthodes de base vu que la PK est numero\_... de type VARCHAR.** 
    - **Supprimer les méthodes CRUD redondantes (getAll, getById, create, update, delete) si elles ne contiennent pas de logique spécifique non couverte par BaseModel.** 
- **Gestion des Clés Primaires des Utilisateurs :**  
  - **Pour Utilisateur.php, Etudiant.php, Enseignant.php, PersonnelAdministratif.php :** 
    - **La variable $primaryKey de BaseModel (qui est 'id' par défaut) n'est pas pertinente si on n'utilise pas BaseModel::find($id), BaseModel::update($id, $data), BaseModel::delete($id) pour ces modèles.**  
    - **Ces modèles devront implémenter leurs propres méthodes find(string $numero\_...), update(string $numero\_..., array $data), delete(string $numero\_...) qui utilisent le champ numero\_... dans la clause WHERE. Par exemple, dans Utilisateur.php:**

**PHP**

**public function find(string $numero\_utilisateur, array $columns = ['\*']): ?array {**  

**$cols = implode(', ', $columns);**  

**$sql = "SELECT {$cols} FROM {$this->table} WHERE numero\_utilisateur = :numero\_utilisateur";**  

**$stmt = $this->db->prepare($sql);  $stmt->execute(['numero\_utilisateur' => $numero\_utilisateur]);**  

**$data = $stmt->fetch(PDO::FETCH\_ASSOC);**  

**return $data !== false ? $data : null;**  

**}**  

- **La méthode create(array $data) de BaseModel peut être utilisée, à condition que $data contienne la clé primaire numero\_... générée par l'application.**  
- **Gestion des Clés Composites :**  
  - **Pour les modèles avec clés composites (ex: Acquerir.php, Affecter.php, Rattacher.php ), ils doivent conserver ou implémenter des méthodes spécifiques pour find, update, delete basées sur ces clés composites, comme Acquerir::findByCompositeKey(). BaseModel n'est pas conçu pour gérer nativement les clés composites pour ses méthodes find/update/delete standards.** 
  - **BaseModel::create() peut toujours être utilisé si toutes les colonnes de la clé composite sont fournies dans $data.** 
2. **Authentification Sécurisée :**  
- **Modifier Utilisateur::create() (ou la logique de création d'utilisateur dans le contrôleur) :**  
- **Avant d'appeler parent::create(), hacher le mot de passe :**

**PHP**

**// Dans la méthode qui gère la création d'un utilisateur (contrôleur ou** 

**modèle) if (isset($data['mot\_de\_passe'])) {**  

**$data['mot\_de\_passe'] = password\_hash($data['mot\_de\_passe'],  PASSWORD\_ARGON2ID); // Ou PASSWORD\_DEFAULT**

**}**  

**// Ensuite, créer l'utilisateur en base**

- **Modifier Utilisateur::authenticate(string $login\_utilisateur, string $mot\_de\_passe\_fourni):**

**PHP**

**public function authenticate($login\_utilisateur, $mot\_de\_passe\_fourni) {**  

**$stmt = $this->db->prepare("SELECT \* FROM {$this->table} WHERE login\_utilisateur =** 

**:login\_utilisateur");**  

**$stmt->bindParam(':login\_utilisateur', $login\_utilisateur);**  

**$stmt->execute();**  

**$user = $stmt->fetch(PDO::FETCH\_ASSOC);**  

**if ($user && password\_verify($mot\_de\_passe\_fourni, $user['mot\_de\_passe'])) {**  

**// Le mot de passe est correct**

**// Important: Ne pas retourner le hash du mot de passe dans les données de session. unset($user['mot\_de\_passe']);**  

**return $user;  ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHcAAAAYCAYAAADAm2IFAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAM9JREFUaIHt2sEJwkAUhOEZjQZU7MQmBEE82VHYdlKNR8ECRLx4EAJB9nnQNGCQhWG+CgZ+3p4WMFkEgLZtp7vDcUOiKj3Ixqki+uVyfiHZEQAiYvZ49jcSq9Lj7HckIyJfJ5n79bo+D5dKAFMAs4LbbKSIiAAq1p8XeVJ6kP2P4wpzXGGOK8xxhTmuMMcV5rjCHFeY4wpzXGGOK8xxhTmuMMcV5rjCHFfY8BMjA3ECuCi6xkYhGQTurzzvgG/clFJummYLX7KCSCnl0iPsz95x9irBPlFzvgAAAABJRU5ErkJggg==)**

`    `**}**  

**return false;  ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGMAAAAYCAYAAADu3kOXAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAMVJREFUaIHt0T9qAlEcxPEZXVZBENJ5jRQ29p7FI3kLz7A38Bohi8RiA/6BHQsNSg7gG9j5VL/3qoEvEDb4d0ji6zveRgQAUhUASBr/dOcNibrsruGRcPrCdbcAvnn/UH3sLi2JeelxA3QQuf6Y1ftR6SXxlBhGEsNIYhhJDCOJYSQxjCSGkcQwkhhGEsNIYhhJDCOJYSQxjCSGkcQwUr3cAtCXGjJUBHsJAh4xmqbpP5erLcFp2WnD0gMg9Tth3ZbeEv/cAL1mKmsP8+UeAAAAAElFTkSuQmCC)}**  

1. **Assurer que la colonne mot\_de\_passe dans mysoutenance.sql est suffisamment longue pour stocker les hashs (actuellement VARCHAR(255), ce qui est bien).** 
3. **Permissions et Sidebar Dynamique :**  
   1. **Créer un PermissionService.php (ou un helper) injecté ou utilisé par DashboardController.php (et d'autres contrôleurs).** 
   1. **Refactoriser DashboardController::getUserRoleLabel():**  
- **La méthode doit interroger la table type\_utilisateur via TypeUtilisateurModel pour obtenir lib\_type\_utilisateur à partir de $\_SESSION['user']['id\_type\_utilisateur'].**

**PHP**

**// Dans PermissionService ou DashboardController**

**public function getUserRoleLabel(?string $numeroUtilisateur): string** 

**{**  

**if ($numeroUtilisateur === null) return 'Invité';**  

**$userModel = new Utilisateur($this->db); // Supposant $this->db est disponible**

**$userData = $userModel->find($numeroUtilisateur, ['id\_type\_utilisateur']);  if ($userData && isset($userData['id\_type\_utilisateur'])) {**  

**$typeUserModel = new TypeUtilisateur($this->db);**  

**$typeInfo = $typeUserModel->find($userData['id\_type\_utilisateur']);         return $typeInfo ? $typeInfo['lib\_type\_utilisateur'] : 'Rôle Inconnu';**  

`    `**}     return 'Rôle Non** 

**Défini';**  

**}   ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAXCAYAAAAyet74AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAIhJREFUKJHtkyEOwlAQRN98SmpIcAh0HcfiIt9hMISrcBlSiURVYJqwg2mhoL5CdZLNbjIvM2qhUAKwXXWP/mJ7+TaUbHw7HQ/7nHMIoG1db7Z9Z1NPQox9Xa/qnaRIpdUzOIP/ApsGYwzEZCwpRrAa9tNwFlqMhpPsiDtg+PyMhls/jV+pRXoBUWU048rBrXYAAAAASUVORK5CYII=)**

- **Refactoriser DashboardController::getMenuItemsForRole():**  
- **Cette méthode doit :** 
1. **Obtenir l'id\_groupe\_utilisateur de l'utilisateur connecté ($\_SESSION['user']['id\_groupe\_utilisateur']).** 
1. **Interroger la table rattacher via RattacherModel pour obtenir tous les id\_traitement associés à ce groupe.** 
1. **Avoir une structure (potentiellement en config, ou une table menu\_items liant id\_traitement à un label, URL, icône, ordre d'affichage, onglet parent).** 
1. **Construire dynamiquement le tableau $menuItems en ne sélectionnant que ceux dont l'id\_traitement est autorisé pour le** 

**PHP![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAgwAAACfCAYAAACV64HLAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABZ9JREFUeJzt3bFrJPcVwPH3ZleRcidzOv8FPoj/j1SBpDCkUOE6sQt3bpwyJP9ByH9gXJ1IlyZNuNTBlcFxYUMIhpgzPp18Pk7xSfNS6E5cMfIzknZXWn0+sDDMDrOvkEbf/c2AIgAAGjm1c39/f2ccN1/LzMn3L+ru3a2HmXm4iHMDAJdvPrUzN7Z+N4vh/TgjKC7q26dHb0XE3xZxbgDg8k0GQ0TOImIjIoZFfOhxHs8WcV4AYDEWEgQAwHoRDABASzAAAC3BAAC0BAMA0BIMAEBLMAAALcEAALQEAwDQEgwAQEswAAAtwQAAtAQDANASDABASzAAAC3BAAC0BAMA0BIMAEBrPrVzjIWXRFbVbG9vb7GfAtwYu7u7FRGVmbXqWWAd5dTO/Sf/+2NmvldVC+mGitobcvbFIs4N3Fi1kd9/dPv27f+uehBYR5MrDJm1FRF3Mxez0JCR70SMvgUAl2k8Osp/VNVXVhng8k0GwxJknLG6AXBuG6u6pMH689AjANASDABASzAAAC3BAAC0BAMA0BIMAEBLMAAALcEAALQEAwDQEgwAQEswAAAtwQAAtAQDANASDABASzAAAK3JYKgcbkVELnkWgAsZMm+tegZYV9MrDFXbS54D4MJqzO0/+LIDC+GWBLBWfr/qAWBNCQYAoCUYAICWYAAAWoIBAGgJBgCgJRgAgJZgAABaggEAaAkGAKAlGACAlmAAAFqCAQBoCQYAoCUYAIDWfNUDAFyiISKG+/fv56oHgVft7u5WRFRm1qpnOa/JX6qDp9//uareDSsQwPUxDpUfjlWfxzBc24sy62mIqLHG/9zZ/sleZo6rnuc8JlcYqsZnETkPwQBcI2PWbyKjIq7l9Zg1NkZUZP79QcRf4pr+gLolAayTjDNWTmHFKiLy56ue4gKsIAAALcEAALQEAwDQEgwAQEswAAAtwQAAtAQDANASDABASzAAAC3BAAC0BAMA0BIMAEBLMAAALcEAALQEAwDQmgyGyuFW+J/yAMAL0ysMVdtLngMAuMLckgAAWoIBAGgJBgCgJRgAgJZgAABaggEAaAkGAKAlGACAlmAAAFqCAQBoCQYAoCUYAICWYAAAWoIBAGgJBgCgNX+5UVW5t7f3MiDylWPqxQsAOJ+KiHrwYNVjnN9pMBwcxhu/+NWvdyMiMuvNqjo62Y5/1lifhmgAgPMZoqrqs6+/vr5/S09XEg6eHv6yKv/6yv6MiLHG+mDntc0/RcS4igEBYE1UZl7bYJi/sp0x8UzDMMQYEWNmCgYAuKE89AgAtAQDANASDABASzAAAC3BAAC0BAMA0BIMAEBLMAAALcEAALQEAwDQEgwAQEswAAAtwQAAtAQDANASDABASzAAAC3BAAC0ToNhPpvX1AHjGBkRubSJAIArZ/5yo6KeR+R+RERVbeSQs6iqHHLjyZMnO48fPx5XNyYAsGx1507tRHyXmUenKwePHtWdYfP5z06OqPcq4u08WVn4KiIeRcTkCgQAsLaO5xG/3d7e/OR0heH11/MgIj6OiHj83eE3GbkZJ7cs3njxAgBulufjMGxHeOgRAPgRBAMA0BIMAEBLMAAALcEAALQEAwDQEgwAQEswAAAtwQAAtAQDANASDABASzAAAC3BAAC0BAMA0BIMAEBLMAAALcEAALQEAwDQEgwAQGsyGDJivuxBAICr5/j4aCPijGAYI+9GRC51IgDgqsnZkDsRZ9+SEAsAQETMM8IzDADAjyAYAICWYAAAWoIBAGgJBgCgJRgAgJZgAABaggEAaAkGAKAlGACAlmAAAFqCAQBoCQYAoCUYAIDW/Afeq6VNAQBcORlRx3EcEWcEQ1Z8WRH/OjkWALiJKuJ4PhsOIs4Igv39/Z3Nzc3bz54JBgC4qba2fjo+fPjvR/fu3Ttc9SwAwDXwfzhB3lJvAahHAAAAAElFTkSuQmCC)**

` `**Dans  PermissionService  ou** 

**DashboardController**

**public function getMenuItemsForUserGroup(?string $numeroUtilisateur):** 

**array**

**if  ($numeroUtilisateur  ===  null)  return  [];  //  Ou  menu  de  base  pour  non connectés**

**groupe de l'utilisateur.** 

**//**

**{** 

**$userModel = new Utilisateur($this->db);  ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfMAAAArCAYAAABsFWG9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAZ9JREFUeJzt3TGO00AYhuFvbJOVFrHpKDgRJU1uAxL03CTUewBaGkok9goURptmCRkKsi2IBcn8yfNoJNsjWfq7VyMXTgCA0lqS9N5bkuH+GQD47/Ukh9Zan44b49fbu3eHlulXbwEAyxuSnrTPV48fvUqym5Lk5ibj02d53nouFp4PAPiN/nN9mOd5lWQ3LD0QAPB3xBwAihNzAChOzAGgODEHgOLEHACKE3MAKE7MAaA4MQeA4sQcAIoTcwAoTswBoDgxB4DixBwAihNzAChOzAGgODEHgOKmpQcAgH+gJ3nbko/H+5PX07+s1+tdIuYAnIY+tPH9k8vxOmcS8yRprfVEzAE4Efvs8yZjXh8Dd058MweA4sQcAIoTcwAoTswBoDgxB4DixBwAihNzAChOzAGgODEHgOLEHACKE3MAKE7MAaA4MQeA4sQcAIoTcwAoriVJ73017+7mJKuF5wGAhzi0Nr64uhyv2xn+z3w6Xr8PGV4eknHRaQDgYXr/tv90rhm7P5m3JG273baF5wGAP7bZbHqSfo6ncgDgBPwAJZFDds9uo5EAAAAASUVORK5CYII=)**

**$userData = $userModel->find($numeroUtilisateur, ['id\_groupe\_utilisateur']);     if (!$userData || !isset($userData['id\_groupe\_utilisateur'])) return [];**  

**$idGroupe = (int) $userData['id\_groupe\_utilisateur'];**  

**l  $rattacherModel  =  new  Rattacher($this->db);  //  Assumer  que ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAf8AAAC6CAYAAACgE0TVAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABnRJREFUeJzt2k9unQcVxuH3XN/cuI3VdEAREgyzAdQ9oA4ZsA0GrIAhG2DIjAGsgA2A6ChixIBSVUWkglJSmj9uY9f3OwycNEWiokDsL/Z5HsmSM3sVxfn5fN9NAIBRKknu37//2vbm0U+7l+3agwCAF6tq00l/dJCbPzk6qg8rSR496jeWOv1zJ7u1BwIAF6Hfr22+d/vw8N3zS/8o6eNUnj4JAACulU5Vnt34m3W3AACXTfwBYBjxB4BhxB8AhhF/ABhG/AFgGPEHgGHEHwCGEX8AGEb8AWAY8QeAYcQfAIYRfwAYRvwBYBjxB4BhxB8AhhF/ABhG/AFgmGfxr1VXAACXZpMkZ0+evLL2EADgQlVyUsnT+FeycfoDwPXV3XV6mufxBwDmEH8AGEb8AWAY8QeAYcQfAIYRfwAYRvwBYBjxB4BhxB8AhhF/ABhG/AFgGPEHgGHEHwCGEX8AGGa79gAA4OJVVe92u07+Nf799GuCTuph0vu1hwDAxatO9yed7JMvx78yJv1V2e+7f1hVv197CwBcuNNk++ru5GiXD5Ivx79TOf8V4NrrTpZN/fEbr+5+t/YWALhsPvAHAMOIPwAMI/4AMIz4A8Aw4g8Aw4g/AAwj/gAwjPgDwDDiDwDDiD8ADCP+ADCM+APAMOIPAMOIPwAMI/4AMMw2STrptYdcthudZe0NALCGTZLcODz8bO0hl6mqug5uPFh7BwCsYZMknWlXcGfJyX7tFQCwBu/8AWCYbZLkcZJNTqe8+V86++1S3/344affWnsLcD1sk95slvdu3br1YVUN+d+Uq2r77Jtekqo1p1yeSg72S36+OTjwAwq8EPtOL7X9UZKfZeCHqLlazuN/lNRxdkl26865ZH48gRekzj87dbD2Dvg6vPMHgGHEHwCGEX8AGEb8AWAY8QeAYcQfAIYRfwAYRvwBYBjxB4BhxB8AhhF/ABhG/AFgGPEHgGHEHwCGEX8AGEb8AWCYbZIcJf0g+bBSu7UHAReve0k29Vml/t7da8+5Fqpq2Sz9URJ/obz0tkny+HGqKt/stPjDBFVdnV/97S9/+sGdO3eWtedcB3fv3s2bb765r1p7Cfxn2yTnp/9xKol/tjBDL+ncuXPn86oSfxjGO38YalPl8TQMJf4w1NLtSR8MJf4wlMsf5hJ/GMrlD3OJPwzl8oe5xB+GcvnDXOIPQ7n8YS7xh6Fc/jCX+MNQLn+YS/xhKJc/zCX+MJTLH+YSfxjK5Q9ziT8M5fKHucQfhnL5w1zbtQfAFXclr+eqdPfV3A78/7ZJUsfHm2T3uLt3aw+Cq6SqHyf11+RqPULv7t5k897aO4B1bJPk7ODg1ez7qCriD19fJ/Xr27d2309ytvaY/0VVLWtvAC7fF4/9K1f0+SWsq5MsIgpcJT7wBwDDiD8ADCP+ADCM+APAMOIPAMOIPwAMI/4AMIz4A8Aw4g8Aw4g/AAwj/gAwjPgDwDDiDwDDiD8ADCP+ADCM+APAMOIPAMOIPwAMI/4AMEwlyafd3z45Pn2nkt3ag+AK6SR/SG1+kc6y9hiAr7Yk3Q+yv/nL11+vf1SSPOp+Y398ei/iD/+tTtJV1WsPAfgq3Z2qvJ+zfuv27cN3t2sPgiuuklS39gMvte5kUzdvJvHOHwDGEX8AGEb8AWAY8QeAYcQfAIYRfwAYRvwBYBjxB4BhxB8AhhF/ABhG/AFgGPEHgGHEHwCGEX8AGEb8AWAY8QeAYcQfAIYRfwAY5ln8a9UVAMCl2STJ2ZMnr6w9BAC4QJ1KTip5Gv9KNk5/ALi+Ol2np3kefwBgDvEHgGHEHwCGEX8AGEb8AWAY8QeAYcQfAIYRfwAYRvwBYBjxB4BhxB8AhhF/ABhG/AFgGPEHgGG+iH+vuQIAuFBV9UXqt0nS5+0/K08Crot9qpa1RwDwcuju1JKTXWdJnsb/xuHhZ58fn667jBfpx5t9/eYsn6+9A4CXwHZ7o5fuk4ef7D5Inl/+S51/v111HS9EL/t3Xnvt5m/jbQ4A/4bH/AAwjPgDwDDiDwDDiD8ADCP+ADCM+APAMOIPAMOIPwAMI/4AMIz4A8Aw4g8Aw4g/AAwj/gAwjPgDwDDiDwDDiD8ADCP+ADCM+APAMOIPAMNsk+QoOX2Qervq/M9cbV11P0mvvQOAl9Oz2D+8fevGW/fu3atV1/BCvP32d07X3gAAAAAAwBr+CbZILnmKC36DAAAAAElFTkSuQmCC)**

**RattacherMode**

**existe**

**// RattacherModel a besoin d'une méthode comme findTreatmentsByGroupId($idGroupe)**

**$authorizedTreatmentIds =   $rattacherModel->findTreatmentsByGroupId($idGroupe); // Retourne un array**

**d'id\_traitement //** 

**Structure de menu prédéfinie, idéalement depuis une config ou BDD**

**// Ex: $allMenuItems = $this->loadMenuStructureFromConfig(); // Chaque item a un 'required\_treatment\_id'**

**$allMenuItems = [  ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIQAAAAYCAYAAAA74FWfAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAMVJREFUaIHt2iEOwkAURdH32kkQBIFBoFkMG+hSSNhT2Q8GjSYERWg/ghD4G+iQcI/77ombjBkJ+GJJighLat43/kvf99F13Wg73kGU6+1+GK1Sexym16o5LuZlb3soknQ6qV2ttXVoVnscpjfEuNTrhRia2mPwWwgCCUEgIQgkBIGEIJAQBBKCQEIQSAgCCUEgIQgkBIGEIJAQBBKCQEIQSIokbTZ6XG6xk9XWHoTpufVZ0iB9PtU6Ir5v/JewHbVH4Ac9AZW/Jfr4zWlqAAAAAElFTkSuQmCC)**

`        `**['label' => 'Tableau de Bord', 'url' => '/dashboard', 'icon' => '...', 'treatment\_id' => 1], // Supposons traitement 1 = 'Accès Dashboard'![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAhoAAAA6CAYAAAD2ivA5AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAj9JREFUeJzt3c9qE2EUh+FzYiK1qK0uBF0IvR7XvYneWu/DK3AhUjcFsRVEBO2fNJnjpsWuWls9TGOfB7IYmMUPBpKXMPBFAAA0yYiIqpocHx+/PDnJB2MPAgBW19ra2vLwML5ubeVJxO/QmH0/mn+Litm48wCAlVZxPNTyzbMnj95mZk0jIvb2YvLiVUwr4uHY+wCA1VUZi6icXFxPrroZAOBvCA0AoI3QAADaCA0AoI3QAADaCA0AoI3QAADaCA0AoI3QAADaCA0AoI3QAADaCA0AoI3QAADaCA0AoI3QAADaCA0A4J/JjLp8PR1rCACsiGVE/oiIYewhq6GOKmpxcSU0AOBqnzInOxXDwdhDVkFVLofTnx/z6XpFCA0AuM7JMD97t7m5tp+Zdf3tXOYdDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANoIDQCgjdAAANpMxx4AsILq/MM9kBGDh317QgPghjLjICN2hrPFfs0Ex/9uOQynzzcff85Mz/oWhAbADVXFWQ7xfmNj/UP4Z+NeEBm3JzQAbiqjIqoiovwAwdW8DAoAtBEaAEAboQEAtBEaAEAboQEAtBEaAEAboQEAtBEaAEAboQEAtBEaAEAboQEAtDk/62Qvql5HOhwI4Fq+KOHPTSMi5vN5VdRBRszGHgRw51V+GYZYjD0DVkFGRFRVRkTu7u7myHsA7rzt7W0ntwIAwNh+AROxdeuCyUq4AAAAAElFTkSuQmCC)**

`        `**['label' => 'Gestion Utilisateurs', 'url' => '/admin/users', 'icon' => '...', 'treatment\_id'**

**=> 2, 'role\_restriction\_id' => 1], // Traitement 2 = 'Voir Module Admin Users', role\_restriction\_id pour type\_utilisateur 'Administrateur'![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB8AAAAXCAYAAADz/ZRUAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAALZJREFUSInt1j0KwkAQhuF3ssFFMCAWFtYpbDyP59ATmM7TiBcJWBsP4Q8WKtnPQtOKzSLIfjDFTPMMTDPwwxiAJHe63pbgXGRPWd5uC+/3Zqb8PXRYVhnqRZUlhYcdqjUNIANoGvnx5H6S8DFxILRq56NBf2NmIYuMfUzCE57whCc84X+GlyWSAFDscs6pw3OAuq5VTme7ro8YEcLxvcjrh1tJ2eLM0OwS+QyFioKLmd3jOl/kCQoeUj8J47npAAAAAElFTkSuQmCC)**

**// ... etc.**

`    `**];  ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAf4AAAB0CAYAAABkM1AvAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABCtJREFUeJzt3c1qnGUYx+H7nkySKu2iSFso0U17DB6DHoQnIbguWYob9TgKBcGNeALuFYRm125aKv1u7SQzt4smbRRE6UeeeXNfFwzDA7P4M5vfzLwDbwQA0EZGRFRVRsTs6Azv0DIza/QIAF6aHz5vPHqyuL7KV2d4a7OcrZYvHn8REQ9HbwHgpXlExN5ebFy8HJ9lxfboQZwmq+Xm5vmt0SsAeG02egAAcHKEHwAaEX4AaET4AaAR4QeARoQfABoRfgBoRPgBoBHhB4BGhB8AGhF+AGhE+AGgEeEHgEaEHwAaEX4AaET4eY8yIiKqKgcPAeDQfPQATq+qysXi+Znbtz84c+vWrdFzJmtnZ6ciYpmZ+6O3ANMn/LxPOT8zv352a3Ewm12q0WOm6tGz/arl6oeI+Gb0FmD6hJ/3Kavq08yIKt1/CxUbs19HjwBOB9f4AaAR4QeARoQfABoRfgBoRPgBoBHhB4BGhB8AGhF+AGhE+AGgEeEHgEaEHwAaEX4AaET4AaAR4QeARoQfABoRfgBoZD56APC/ZFX5oE5HlZk1esRpIvyw/jIiPn/4dPHj6CFw0jJm31bVT+L/7gg/TEHVx5m5M3oGnLRaHlyXqnfLuwnTkFWVo0fAidvYGL3g1HHNEAAaEX4AaET4AaAR4QeARoQfABoRfgBoRPgBoBHhB4BGhB8AGhF+AGhE+AGgEeEHgEaEHwAaEX4AaET4AaCR+egBAPBvMmL73r17Z+/erRq9ZaouXIhVRLzIzGVEREZE3LxZ2xcvLx5WxfbQdQBwXMVezurOajV6yHTNMh/vV3z50dmt3zOzfOMHYH1lXKnKK5mjh0xXRd6P1cG5o7PwA7DOJP/t5ebm69z7cx8ANCL8ANCI8ANAI8IPAI0IPwA0IvwA0IjwA0Ajwg8AjQg/ADQi/ADQiPADQCPCDwCNCD8ANCL8ANCI8ANAI/P/fskk1OEDAPibqqh81cjphz/zSVXdmEX+MXoKAKybinq+nG3eOTpPOvyZUVH1aFnx9flzW7+N3gMA6841fgBoRPgBoBHhB4BGhB8AGhF+AGhE+AGgEeEHgEaEHwAaEX4AaET4AaAR4QeARoQfABoRfgBo5PDufHtR9UnExO5pXxWREbGdMauqSd9pEIBTZ5mZa9fV47G8H5Hbw5a8gaqKyNw4iPjuwdPFs9F7AOBQZcy+r6qf1y3+h+G/GpmL81U1qfBnRhz+SHEpx04BgONWs6wb8fKH6bUKv2v8ANCI8ANAI8IPAI0IPwA0IvwA0IjwA0Ajwg8AjQg/ADQi/ADQiPADQCPCDwCNCD8ANCL8ANCI8ANAI8IPAI0IPwA0IvwA0Mg8IuLq1Th48KS+ioyN0YMA4BSo/T8Xv8SHmzV6yD/l0XNVHT8DAG9od3c3rl27Vpm5duEHABr5C4jywQFNXYI/AAAAAElFTkSuQmCC)**

**$finalMenuItems = [];     foreach**

**($allMenuItems as $item) {**  

**if (in\_array($item['treatment\_id'], $authorizedTreatmentIds)) {**  

**// Optionnel: vérifier aussi le type\_utilisateur si des restrictions plus globales existent![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAN4AAACKCAYAAADFePRWAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABAhJREFUeJzt3bFuW3UYhvH3O4njCpoWFiNl9tQ1NwErSGVk5hKYklwCd1B1YegKK0SIDWVg6ZKGIEBCgg5NlUqpU5+3Q5uQCqlBqY/f2Of5SZEcxUo+WX7i+EjfPxKAuauzG7brbXdEP1SV0zP0QUmS7ZWj48lnVVpJD4Qgy57659u3h78SYLdWJenRI62ONnTf1jA9EIJKrQbNl5IOJRFeh5r0AEAfER4QQHhAAOEBAYQHBBAeEEB4QADhAQGEBwQQHhBAeEAA4QEBhAcEEB4QQHhAAOEBAYQHBKymB7hGXKonKh3Y/Vy+rqq2qvlHbJ93jvAusn/8+6+1z8djtelREvb29rS5uTnlvJXuEd6/7EbteKzTqupleJgf3uMBAYQHBBAeEEB4QADhAQGEBwQQHhBAeEAA4QEBhAcEEB4QQHhAAOEBAYQHBBAeEMA+3kWtand3t9ne3k5PgiWztbXliwvGJUn7+x6ONiZHtoa50eIsaa+kr8XRB5ixKj89eTb8YTSqY4lXvItK0qalexx9gFlrWx0MBicf235WVSa8N5Wklb4edoQOlRrduHH+KRdXgADCAwIIDwggPCCA8IAAwgMCCA8IIDwggPCAAMIDAggPCCA8IIDwgADCAwIIDwhYlH08FuSw2CxJJ5Je7eQtQHh1JPlQYisci6ua+n3QvpicnW5w3cNzyT/9+f7ap3ekF+lhgHdRNWjPbl/38CTJd6S2qtrL7wosBi6uAAGEBwQQHhBAeEAA4QEBhAcEEB4QQHhAAOEBAYQHBBAeEEB4QADhAQGEBwQQHhBAeEBA14uwln2gqqe62rkpln3w4AFnrmC5dB/eSn1Vp2vf20dXiuf5ZHJ69+5oOuvBgKTOj36otjm+dUtPqj7g6Abgtbm8x9vZ2ZnHjwEWBhdXgADCAwIIDwggPCCA8IAAwgMCCA8IIDwggPCAAMIDAggPCCA8IIDwgADCAwIIDwgoSdrf93C0MTmyNZzx97eq7sm1r6sd/YD+cNtOH364Pvy2qpb+udL1BnrJ/uJVf8v/YOLqbHulqW8kface/JLu/OgHvf5z1l76xxLvplVTlR5iXniPBwQQHhBAeEAA4QEBhAcEEB4QQHhAAOEBAYQHBBAeEEB4QADhAQGEBwQQHhBAeEDA/9nHY5EO82C1/XmqXRbeiaSHIj50zyodqifPtbeFZ1l/nGjyyXt2O7eJ0FPrXl/X874cEXLJK56nH928+biqCA+YIS6uAAGEBwQQHhBAeEAA4QEBhAcEEB4QQHhAAOEBAYQHBBAeEEB4QADhAQGEBwQQHhDQSNJ4fP6fkv3mRz+WEoF5O1uEtaRfpBqcf6VkVf2WGApYdiVJtuvs9n/uwPY5AGAZvARTNNips8SlagAAAABJRU5ErkJggg==)**

**// if (isset($item['role\_restriction\_id']) && $item['role\_restriction\_id'] != $\_SESSION['user']['id\_type\_utilisateur']) {**

**//     continue;**

**// }**

**$finalMenuItems[] = $item;          }**  

`    `**}**  

**return $finalMenuItems;**  

**}**  

- **Chaque action de contrôleur appelée via une route doit également vérifier la permission (le traitement) avant d'exécuter la logique.** 
4. **Contrôleurs :**  
- **Créer des contrôleurs dédiés pour chaque module et sous-module fonctionnel comme proposé dans l'arborescence cible.** 
- **Chaque méthode de contrôleur qui représente une action protégée doit vérifier la permission de l'utilisateur (via le PermissionService) avant d'exécuter la logique métier.** 
5. **Injection de Dépendances :**  
- **La connexion PDO est actuellement injectée dans BaseModel.php via son constructeur, et les modèles enfants l'héritent. C'est une bonne approche.**  
- **Pour les services (comme AuthService, PermissionService), la connexion PDO**  

  **(ou les modèles nécessaires) devrait également être injectée via le constructeur ou des setters. ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAm4AAAAGCAYAAAB6kBINAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAEdJREFUaIHtzrENgDAQBEEfHdEb0pfwiXujJSKLjNRCmonush0DAIBfyBrdfSaZO2MAAHgluavqWv/YGQMAAAAAAAAAAAAfHujyBQSsBjgnAAAAAElFTkSuQmCC)**

**Phase 3: Plan de Finalisation du Projet (A à Z)** 

**Étapes de Construction du Code (A à Z pour finaliser)** 

1. **Étape 1: Finalisation des Fondations Solides** 
- **Valider et Finaliser le Schéma SQL : S'assurer que mysoutenance.sql (mysoutenance.txt) est complet, cohérent avec tous les PDF, et que toutes les clés primaires/étrangères et types de données sont corrects. (Principalement fait).** 
- **Refactorisation de l'Authentification :**  
  - **Implémenter le hachage des mots de passe (argon2id recommandé) à la création/modification des utilisateurs dans Utilisateur.php ou le contrôleur concerné.** 
  - **Mettre à jour la méthode Utilisateur::authenticate() pour utiliser password\_verify().** 
  - **Sécuriser les sessions (régénération d'ID, HttpOnly, Secure pour les cookies).**  
- **Refactorisation Complète des Modèles :**  
  - **Appliquer les namespaces et l'héritage de BaseModel.php à tous les fichiers modèles de src/Backend/Model/.** 
  - **Adapter les modèles pour gérer correctement les clés primaires numero\_... VARCHAR(50) pour utilisateur, etudiant, enseignant, personnel\_administratif.** 
  - **S'assurer que les modèles pour les tables à clés composites ont des méthodes CRUD spécifiques (findByCompositeKey, etc.) si BaseModel n'est pas suffisant.** 
- **Structure de Base des Contrôleurs et Vues : Mettre en place l'arborescence de base pour les contrôleurs et les vues par module.** 
2. **Étape 2: Implémentation du Module Administration (Cœur)** ○ ***(Se référer à Module Administration.pdf pour les fonctionnalités)*.**  
- **CRUD Utilisateurs :**  
- **Développer les interfaces (vues) et la logique (contrôleurs, modèles)** 

**pour le CRUD complet des entités etudiant, personnel\_administratif, enseignant, et leurs comptes utilisateur associés.** ○ **CRUD Habilitations :**  

- **Interfaces et logique pour type\_utilisateur (rôles).** 
- **Interfaces et logique pour groupe\_utilisateur.** 
- **Interfaces et logique pour niveau\_acces\_donne.** 
- **Interfaces et logique pour traitement (définition des fonctionnalités** 

**système).**  

- **Interface pour rattacher : Permettre à l'administrateur d'assigner des traitement aux groupe\_utilisateur.** 
- **CRUD pour les 14 Référentiels : Créer des interfaces génériques ou spécifiques pour gérer chaque référentiel listé dans Module Administration.pdf (Specialite, Fonction, Grade, UE, ECUE, AnneeAcademique, NiveauEtude, Entreprise, NiveauApprobation, StatutJury, Action, Traitement (déjà fait), Message, Notification).** 
- **Configuration Système :**  
- **Interface pour les paramètres généraux (dates limites, règles workflow, etc.).**  
- **Interface pour la gestion des modèles de documents et emails.** 
  1. **Gestion Académique (par Admin/RS) : Interfaces pour inscrire, evaluer, faire\_stage, acquerir, occuper, attribuer.** 
  1. **Supervision & Maintenance : Interfaces pour suivi workflows, gestion PV (admin), gestion notifications, consultation audit logs (enregistrer, pister ), outils import/export, maintenance technique.** 
  1. **Reporting : Développement initial des outils de reporting.** 
3. **Étape 3: Implémentation de la Sidebar et Contrôle d'Accès Dynamique** 
   1. **Refactoriser DashboardController.php et créer le PermissionService (ou équivalent).**  
   1. **La sidebar doit être générée dynamiquement en fonction des traitement autorisés pour le groupe\_utilisateur de l'utilisateur connecté, via la table rattacher.**  
   1. **Mettre en place des vérifications de permission robustes au début de chaque méthode de contrôleur accédant à une fonctionnalité protégée.** 

4\.  **Étape 4: Développement du Module Étudiant** 

- ***(Se référer à Module Etudiant.pdf pour les fonctionnalités)*.**  
- **Développer les contrôleurs (ex: EtudiantDashboardController,** 

  **ProfilEtudiantController, RapportController, DocumentEtudiantController, ReclamationEtudiantController).** 

  1. **Implémenter la logique métier et les interactions avec les modèles (ex: Etudiant, Utilisateur, RapportEtudiant, DocumentSoumis, Reclamation, \_ref tables).**  
  1. **Créer les vues correspondantes pour :** 
     1. **Accès et gestion de profil.** 
     1. **Soumission de rapport et annexes, gestion des versions.** 
     1. **Suivi du processus de validation (statuts, commentaires).** 
     1. **Centre de notifications.** 
     1. **Accès aux documents officiels.** 
     1. **Gestion des corrections.** 
     1. **Soumission et suivi des réclamations.** 
     1. **Consultation des ressources et FAQ.** 
  1. **Intégrer les vérifications de permission pour chaque action.** 
5. **Étape 5: Développement du Module Personnel Administratif** 
   1. ***(Se référer à Module Personnel Administratif.pdf pour les fonctionnalités)*.**  
   1. **Développer les contrôleurs pour les rôles (ex: ConformiteController, ScolariteController).** 
   1. **Logique métier et modèles (PersonnelAdministratif, Approuver, RapportEtudiant, Etudiant, Inscrire, Evaluer, etc.).** 
   1. **Vues pour :**  
- **Agent de Contrôle de Conformité :**  
  - **Tableau de bord spécifique, liste des rapports à vérifier.** 
  - **Interface de consultation de rapport et vérification.** 
  - **Formulaire de décision de conformité (avec motifs).** 
  - **Notification à l'étudiant (automatisée) et transmission.** 
- **Gestionnaire Scolarité :**  
  - **CRUD étudiants (si non couvert par Admin ou droits spécifiques).** 
  - **Interface de création de comptes utilisateurs étudiants avec vérification scolarité.** 
  - **Suivi administratif des soutenances.** 
  - **Génération de documents PDF.** 
  - **Gestion des notes (CRUD).** 
- **Fonctionnalités Communes :** ■ 

**Suivi et reporting simplifié.** 

- **Interface de chat.** 
- **Accès aux archives et logs pertinents.** 
  1. **Permissions granulaires pour chaque rôle/action.** 
6. **Étape 6: Développement du Module Commission** 
   1. ***(Se référer à Module Commission.pdf pour les fonctionnalités)*.**  
   1. **Contrôleurs (ex: CommissionDashboardController, ValidationRapportController, PvController).** 
   1. **Logique métier et modèles (Enseignant, RapportEtudiant, VoteCommission, CompteRendu, ValidationPv, etc.).** 
   1. **Vues pour :**  
      1. **Tableau de bord de la commission.** 
      1. **Consultation et examen des rapports.** 
      1. **Interface de validation en ligne (vote structuré, commentaires, gestion des divergences, concertation via chat).** 
      1. **Gestion des sessions présentielles (si applicable).** 
      1. **Rédaction et validation des PV (individuels et de session, pré - remplissage, approbation).** 
      1. **Gestion des corrections demandées/reçues.** 
      1. **Chat et notifications spécifiques à la commission.** 
      1. **Historique et archives.** 
   1. **Permissions pour les membres.** 
6. **Étape 7: Finalisation des Fonctionnalités Transverses** 
   1. **Système de Notification Complet : Assurer que toutes les notifications (email et plateforme) décrites dans les modules sont implémentées via NotificationService.** 
   1. **Chat Intégré : Finaliser l'implémentation du chat pour tous les rôles concernés.**  
   1. **Audit Avancé : Vérifier que toutes les actions critiques sont correctement loguées dans enregistrer et pister.** 
   1. **Reporting : Développer les rapports prévus pour chaque module.** 
6. **Étape 8: Tests et Débogage** 
   1. **Tests Unitaires : Pour les modèles et services critiques (surtout authentification, permissions).** 
   1. **Tests d'Intégration : Vérifier l'interaction entre les composants.** 
   1. **Tests Fonctionnels (Manuels ou Automatisés) : Couvrir tous les cas d'utilisation pour chaque rôle.** 
   1. **Tests de Sécurité : Vérifier les failles communes (XSS, CSRF, injections SQL** 

**-**  

**bien que PDO prévienne beaucoup, SQL Injection via ORDER BY est possible si non géré), contrôle d'accès.** 

- **Débogage et correction des anomalies.** 

9\.  **Étape 9: Documentation Finale et Déploiement** ○ **Documentation Utilisateur :** 

**Pour chaque module.** 

- **Documentation Technique : Architecture, base de données, API (si applicable).**  
- **Préparation pour le déploiement (configuration serveur, variables d'environnement de production). ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAm4AAAAGCAYAAAB6kBINAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAHtJREFUaIHt1rENAjEQBMCzhJsgoB0kGkIuwR3RAiFVUMLrE5voJeufiMRCmolutcmGFwEAwF9I21FrPUfEdeIWAAAGrbV3KeWx5dPQXXrv9wmbAAD4IqX0jIjj45Zzfq3repuyCgCAg9baMua06/cZAIB5+uwBAAD84APP0xYnxjBibAAAAABJRU5ErkJggg==)**

**Ce plan d'action détaillé devrait fournir une base solide pour la refactorisation et la finalisation du projet "Gestion MySoutenance". Il met l'accent sur la correction des défauts identifiés, l'alignement avec les spécifications fonctionnelles et la construction d'une application robuste et sécurisée.** 

**Définition des Piliers du Système ![ref1]**

Le système de sécurité et de permissions de "Gestion MySoutenance" repose sur plusieurs entités interconnectées pour contrôler finement ce que chaque utilisateur peut voir et faire. 

- **utilisateur**: Représente la personne physique qui se connecte à la plateforme. Chaque **utilisateur** possède un login unique, un mot de passe, et est associé à un type d'utilisateur, un groupe d'utilisateurs, et un niveau d'accès aux données. 
- *Interrelation*: C'est l'entité centrale à laquelle les droits sont finalement appliqués. 
- **type\_utilisateur (Rôles)**: Définit une classification fonctionnelle globale des utilisateurs. Par exemple, "Étudiant", "Enseignant", "Personnel Administratif", "Administrateur". Cela permet de regrouper les utilisateurs selon leur fonction principale dans l'établissement. 
- *Interrelation*: Un **utilisateur** appartient à un seul **type\_utilisateur**. 
- **groupe\_utilisateur (Groupes de Permissions)**: Permet une segmentation plus fine des droits. Un groupe rassemble des utilisateurs qui partagent des besoins d'accès similaires à certaines fonctionnalités. Exemples : "Agents de Conformité", "Gestionnaires Scolarité", "Membres Commission". 
- *Interrelation*: Un **utilisateur** appartient à un **groupe\_utilisateur**. Ce groupe sera lié à des **traitements** via la table rattacher. 
- **traitement (Fonctionnalités)**: Représente une action ou une fonctionnalité spécifique du système à laquelle des permissions peuvent être liées. Par exemple, "Accès Module Admin", "Valider Rapport", "Créer Utilisateur". 
- *Interrelation*: Les **traitements** sont les "choses" que les utilisateurs peuvent faire. Ils sont liés aux **groupes d'utilisateurs** via la table rattacher. 
- **rattacher (Liaison Groupe-Traitement)**: C'est la table de jonction qui matérialise les permissions. Elle associe un **groupe\_utilisateur** à un ou plusieurs **traitements**, signifiant que les membres de ce groupe ont le droit d'effectuer ces traitements. 
- *Interrelation*: Connecte directement groupe\_utilisateur et traitement. C'est ici que les droits d'accès aux fonctionnalités sont explicitement définis. 
- **niveau\_acces\_donne**: Ajoute une granularité sur l'accès aux données elles-mêmes, au-delà des fonctionnalités. Il peut permettre de restreindre la visibilité des données à un sous-ensemble pertinent pour l'utilisateur, même s'il a accès à une fonctionnalité (par exemple, un gestionnaire de scolarité pourrait voir les étudiants de sa filière uniquement). 
- *Interrelation*: Un **utilisateur** est associé à un **niveau\_acces\_donne**. ![ref1]

**Explication du Mécanisme d'Octroi des Permissions** 

Le flux logique pour qu'un utilisateur se voie attribuer des droits est le suivant : 

1. **Identification de l'Utilisateur**: Lorsqu'un **utilisateur** se connecte, le système l'identifie via son numero\_utilisateur (ou login\_utilisateur). 
1. **Détermination du Rôle et du Groupe**: Le système récupère son id\_type\_utilisateur (son rôle fonctionnel global) et, de manière cruciale pour les permissions de fonctionnalités, son id\_groupe\_utilisateur. 
1. **Récupération des Traitements Autorisés**: Grâce à l'id\_groupe\_utilisateur, le système interroge la table rattacher. Il recherche toutes les entrées où cet id\_groupe\_utilisateur est présent pour obtenir la liste des id\_traitement qui lui sont associés. Chaque id\_traitement représente une fonctionnalité ou une action spécifique que l'utilisateur est autorisé à exécuter. 
1. **Application du Type d'Utilisateur**: Le type\_utilisateur (rôle) offre une classification fonctionnelle et peut être utilisé pour des vérifications de droits de haut niveau ou pour conditionner l'affichage de modules entiers (par exemple, un utilisateur de type "Étudiant" ne verra pas les options du module d'administration). 
1. **Application du Niveau d'Accès aux Données**: Indépendamment des fonctionnalités accessibles, le niveau\_acces\_donne associé à l'**utilisateur** peut filtrer les *données* que l'utilisateur voit. Par exemple, deux gestionnaires de scolarité (même type\_utilisateur et groupe\_utilisateur) pourraient avoir accès à la fonctionnalité "Lister Étudiants", mais l'un ne verrait que les étudiants de la filière A et l'autre ceux de la filière B, grâce à des niveau\_acces\_donne différents. 

En résumé, un **utilisateur** appartient à un **groupe\_utilisateur**. Ce **groupe\_utilisateur** est lié à plusieurs **traitements** via rattacher. Ainsi, l'utilisateur hérite des droits d'accès à ces **traitements**. Le type\_utilisateur catégorise l'utilisateur, et le niveau\_acces\_donne affine la portée des données visibles. ![ref1]

**Illustration du Fonctionnement Inter-Modules** 

Voyons comment cela s'applique concrètement aux différents modules : 

- **Module Étudiant**: 
- **Traitements spécifiques**: "Soumettre Rapport de Stage", "Consulter Statut Rapport", "Modifier Profil Étudiant". 
- **Groupe\_utilisateurs typique**: "Étudiants". 
- **Accès refusé**: Un utilisateur du groupe "Enseignants" essayant d'accéder à la page de soumission de rapport (liée au traitement "Soumettre Rapport de Stage") se verrait refuser l'accès car son groupe n'est pas lié à ce traitement dans rattacher. 
- **Visibilité conditionnelle**: Au sein du module étudiant, le bouton "Soumettre Corrections" ne pourrait être visible que si le rapport\_etudiant.id\_statut\_rapport est "Corrections Demandées" ET si l'étudiant a la permission (traitement) de le faire. 
- **Module Personnel Administratif**: 
- **Traitements spécifiques**: 
  - Agent de Conformité : "Vérifier Conformité Rapport", "Marquer Rapport Conforme/Non Conforme". 
  - Gestionnaire Scolarité : "Créer Fiche Étudiant", "Gérer Inscription Administrative", "Saisir Note Étudiant". 
- **Groupe\_utilisateurs typiques**: "Agents de Conformité", "Gestionnaires Scolarité" (ces groupes seraient créés dans groupe\_utilisateur). 
- **Accès refusé**: Un étudiant (groupe "Étudiants") ne pourrait pas accéder à la fonctionnalité "Vérifier Conformité Rapport". 
- **Visibilité conditionnelle**: Un Gestionnaire Scolarité pourrait avoir le traitement "Gérer Inscription Administrative", mais le bouton "Valider Paiement Inscription" pourrait être désactivé si le inscrire.id\_statut\_paiement est déjà "Payé". 
- **Module Commission**: 
- **Traitements spécifiques**: "Consulter Rapport Assigné", "Voter sur Rapport", "Rédiger PV Soutenance", "Valider PV Soutenance". 
- **Groupe\_utilisateurs typique**: "Membres Commission". 
- **Accès refusé**: Un Agent de Conformité (groupe "Agents de Conformité") ne pourrait pas voter sur un rapport. 
- **Visibilité conditionnelle**: Le bouton "Rédiger PV" ne serait actif pour un rapport que si celui-ci a atteint un statut spécifique (ex: "Décision Commission Prise") et si l'utilisateur a le rôle de rédacteur désigné dans la commission pour ce rapport. 
- **Module Administration**: 
- **Traitements spécifiques**: "Gérer Utilisateurs", "Configurer Référentiels", "Voir Journaux Audit". 
- **Groupe\_utilisateurs typique**: "Administrateur\_systeme", "Administrateur\_Fonctionnel". Le "Responsable Scolarité" peut avoir accès à des traitements spécifiques de gestion étudiante au sein de ce module. 
- **Accès refusé**: Un membre de commission n'aurait pas accès à "Configurer Référentiels". 
- **Visibilité conditionnelle**: L'option "Supprimer Utilisateur" pourrait être désactivée pour un utilisateur ayant des données critiques liées, même si l'admin a le traitement "Gérer Utilisateurs". ![ref1]

**Implémentation de la Sidebar Dynamique** 

La sidebar dynamique, qui affiche uniquement les options de menu pertinentes pour l'utilisateur connecté, est un élément clé de l'expérience utilisateur et de la sécurité. Voici comment elle serait implémentée : 

1. **Identification de l'Utilisateur et de son Groupe (Côté Serveur)**: 
   1. Lors de la connexion réussie, le serveur identifie l'**utilisateur** (numero\_utilisateur) et récupère son id\_groupe\_utilisateur à partir de la table utilisateur. 
1. **Récupération des Traitements Autorisés (Côté Serveur)**: 
   1. Le serveur interroge la table rattacher en utilisant l'id\_groupe\_utilisateur de l'utilisateur connecté. 
   1. Cette requête retourne une liste de tous les id\_traitement auxquels le groupe de l'utilisateur a accès. 
1. **Mapping Traitements-Éléments de Menu (Côté Serveur/Application)**: 
   1. L'application possède une configuration (qui peut être en base de données dans traitement ou en dur dans le code, mais idéalement configurable) qui mappe chaque id\_traitement (ou lib\_trait) à un élément de menu spécifique (un nom d'onglet, une URL, une icône, etc.). 
   1. Par exemple, le traitement avec lib\_trait = "Consulter Rapports à Vérifier" pourrait être mappé à l'élément de menu "Rapports à Vérifier" dans la sidebar de l'Agent de Conformité. 
1. **Construction et Affichage de la Sidebar (Côté Serveur/Client)**: 
   1. Le serveur (ou une logique applicative) compare la liste des **traitements autorisés** pour l'utilisateur avec la liste complète des éléments de menu possibles. 
   1. Seuls les éléments de menu dont le **traitement mappé** est présent dans la liste des traitements autorisés de l'utilisateur sont inclus dans la structure de la sidebar qui est envoyée au client (navigateur). 
   1. Le client reçoit donc une sidebar déjà filtrée et n'affiche que les options accessibles. 
1. **Contrôle d'Accès aux Fonctionnalités Post-Clic (Côté Serveur)**: 
- Lorsqu'un utilisateur clique sur un élément de la sidebar (par exemple, "Créer Étudiant"), une requête est envoyée au serveur pour accéder à cette fonctionnalité. 
- **Crucialement**, le serveur ne se fie pas uniquement au fait que le lien était présent dans la sidebar. Il doit **systématiquement revérifier** que l'utilisateur connecté (via sa session et son id\_groupe\_utilisateur) a bien le **traitement** requis (associé à "Créer Étudiant") dans la table rattacher. 
- Si la permission est confirmée, la fonctionnalité est exécutée. Sinon, l'accès est refusé (par exemple, redirection vers une page d'erreur ou message d'accès non autorisé), même si l'utilisateur avait réussi à "deviner" l'URL. 

Ce double contrôle (filtrage de la sidebar pour l'affichage et vérification serveur pour l'exécution) assure que les utilisateurs ne voient que ce à quoi ils ont droit et ne peuvent pas contourner les permissions en accédant directement à des URL. Le type\_utilisateur peut également être utilisé pour afficher/masquer des sections entières de la sidebar ou des modules complets de manière plus globale avant même de vérifier les traitements spécifiques. 

 	


- 



||` `#$|'|0	$	+2	13+41|   	18|
| - | - | - | - | - |
|=<br>><?@A=B+ KLMN OP MNQ R|U<V|YZU|||
|SU] [^B @XZ:>BS\<br>U;\_BS?U<AU +TV+CDKLFMEN GOHPJIEMFNQR|<br>A`f@ghkAla<br>j@cdeib|WYZUXS|||
|=<br>AVB<br>Z>AUV<gm\_<<]|=<br>AVB|YZU|||
n2o



<table><tr><th colspan="1">'  +822r+o</th><th colspan="1">` `#$</th><th colspan="1">uv8</th><th colspan="1"> $ 13</th><th colspan="1"></th><th colspan="1">	218	38</th><th colspan="1">n
1	  </th><th colspan="1">'</th><th colspan="1">   	18</th></tr>
<tr><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2">``</td><td colspan="1" rowspan="2">] <</td><td colspan="1" rowspan="2">YZU</td><td colspan="1">=<br>><?@A=B</td><td colspan="1">k</td><td colspan="1"></td><td colspan="1">YZU</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1" valign="bottom">SU] [^B @XZ:>BS\<br>U;_BS?UT<AUV</td><td colspan="1" valign="bottom">dk</td><td colspan="1" valign="bottom">}</td><td colspan="1" valign="bottom">WYZUXS</td></tr>
<tr><td colspan="1">=<<br>>AgmB]@<>BU_B ?U<br><AUV</td><td colspan="1">``</td><td colspan="1">YZU</td><td colspan="1">YZU</td><td colspan="1">U] ^B @Z>B<br>U_B?U<AUV</td><td colspan="1">k</td><td colspan="1"></td><td colspan="1">YZU</td><td colspan="1"></td></tr>
</table>
- 



||` `#$|'|0	$	+2	13+41|   	18|
| - | - | - | - | - |
|=<br>><AZUgV<+ KLMN OP MNQ R|U<V|YZU|||
|<><br>AZ:UgV<XTS|<br>A`f@ghAa<br>@ikblde|WYZUXS|||
|=B\_g@Z<UV<><br>AZUgV<|VBV|] <|||
|gA<br>VB?ZTX@B;<>AZ<br>UgV<:XTS|<br>A`f@ghkAla<br>j@cdeib|] <|||
n2o



|'  +822r+o|` `#$|uv8| $ 13||	218	38|n
1	  |'|   	18|
| - | - | - | - | - | - | - | - | - |
||``|] <|YZU|=<br>><AZUgV<|k||YZU||
 ¡
 ¡



|||` `#$|'|0	$	+2	13+41|   	8 1|
| - | :- | - | - | - | - |
|U] ^B @Z>B<br>U\_B?U<AU V+KLMN OP MNQR||<br>Af@ghkAl<br>j@i|YZU|||
|=>9<br><@:AXZ:@TV>9<br>BA=V]<SU +TV+CDKLFMEN GOHPJIEMFNQR||US<VT|WYZUXS|||
|<br>=>@<\_VA]¥+V>V]¤ KLMON PMNQR||U<V|YZU|||
|@9=<BgVB@]>T: [^B[^XZ @<B||UUST£SbV<¥<lTVie|WYZUXS|dk||
|B=<br>AgVAZ <br>VBU<br>V<¦>A ¦||=<br>AVBV< ^B|YZU|ª«Y> ¬ ||
n2o



|'  +822r+o||` `#$|uv8| $ 13|||	218	38|n
1	| |'|   	18|
| - | :- | - | - | - | :- | - | - | - | - | - | - |
|||||||U] ^B @Z>B<br>U\_B?U<AUV|k|||YZU||
|||``|] <|YZU||=>9<br><@:AXZ:@TV>9<br>BA=V]<SUTV|dk|}||WYZUXS||
|||||||<br>=>@<\_VA]¥V>V]¤|k|||YZU||
|gVB=@9 <¦T<br>>A:@<br>: ¦AXZ|:@TV>B9A<br>=V]<USTV|``z|WYZUXS|WYZUXS||=>9<br><@:AXZ:@TV>9<br>BA=V]<SUTV|dk|}||WYZUXS||
|gVB=@<br>` `<¦<br>>\_VA V¦V]|@]¥>¤|``|YZU|YZU||<br>=>@<\_VA]¥V>V]¤|k|||YZU||



­® ¯



|||` `#$|'|0	$	+2	13+41|   	18 |
| - | :- | - | - | - | - |
|=<br>><AUUB<br>B>A<br>gA=B^|m<B]+ KLMN OP MNQR|U<V|YZU|||
|<><br>A:SUSUB<br>B:>A<br>gA9=B|[^<mB]|<br>A`f@ghkAla<br>j@cdeib|WYZUXS|||
|=<br>AVB>=BV]||=<br>AVB|] <|||
|S9=U<br>A<TVB :>¦||9=<br>ATVB|] <|||
|<br>B\_V>BAfgV<||UUV<¥<lVi|YZU|k||
n2o

|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WIXYOOZZXYY[WZ \I H]Z|\_|<|MNO||
eghifcdde



|&

||||!|!-m'),l',!|&('
,	-|
| - | :- | :- | - | - | - | - |
|OH\Z NNXrtOZsrOZu|XYWw\YIvrvtOI||[YYrr\_|MNO|||
|WPQQXKEFpRIY`SXPvH`pNZaFLsrvWrYOI|pv{yxz|}~z{||OLvpFI|JMKLNO|||
|WYHIXvNv[tOwNr|\I Zv||OvI|MNO|||
|NT[K U\\ZUSZSXQNrRaLO[YTOpFKvIwLq|KNar U\IFSZpv||SZpvpv|DGEHFI|||
|NOWYI[XvY[ZwIXvIZr|NOwNr \I Zv||WYIZv \Z|MNO|||
0



<table><tr><th colspan="1">``	
<br></th><th colspan="1"></th><th colspan="1">
!	 </th><th colspan="1">&)(('</th><th colspan="1">&

</th><th colspan="1">&)'	,
-,	</th><th colspan="1">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1">&'
,	-</th></tr>
<tr><td colspan="1" rowspan="2">89: ;< 9=</td><td colspan="1" rowspan="2">AB9CC</td><td colspan="1" rowspan="2">GHI</td><td colspan="1" rowspan="2">MNO</td><td colspan="1">OH\Z NNXrtOZsrOXZuYW w\YIvrvtOI</td><td colspan="1">_</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">WPQQXKEFpRIY`SXPvH`pNZaFLsrvpWrYOIv</td><td colspan="1">^_</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td></tr>
<tr><td colspan="1">WIXNYHrsYXZsr XvHNZsvWrYOIv</td><td colspan="1">AB9CC</td><td colspan="1">MNO</td><td colspan="1">MNO</td><td colspan="1">WXIYXvHNZsrvWrYOIv</td><td colspan="1">_</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1"></td></tr>
<tr><td colspan="1">qwQKXRaEN`QRE`bSYaHnprsYHXvZtr QXNpvT[KOKLwNqar U\IFSZpv</td><td colspan="1">>A?3B@9C@C</td><td colspan="1">JMKLNO</td><td colspan="1">JMKLNO</td><td colspan="1">WPQYREHFIXpvNnTvK[tOKLwNqar U\IFSZpv</td><td colspan="1">^_</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td><td colspan="1"></td></tr>
</table>
cegi



|&

||!|!-m'),l',!|&'
,	- (|
| - | - | - | - | - |
|OH\Z NXOr ZYOZOIt v|[YYrr\_|MNO|||
|SWYIPpQuFSRXZIsn`T[Fot |{yxz ~z}{|OLvpFI|JMKLNO|||
Zv

0



<table><tr><th colspan="1">``	
<br></th><th colspan="1"></th><th colspan="1">
!	 </th><th colspan="1">&)(('</th><th colspan="1">&

</th><th colspan="1">&)'	,
-,	</th><th colspan="1">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1">& '
,	-</th></tr>
<tr><td colspan="1" rowspan="2">89: ;< 9=</td><td colspan="1" rowspan="2">AB9CC</td><td colspan="1" rowspan="2">GHI</td><td colspan="1" rowspan="2" valign="top"><p>MNO</p><p>Z</p></td><td colspan="1">OH\Z NXOr ZYOZOItv</td><td colspan="1">_</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">SWYIvPpQuFSRXZIsn`T[Fot</td><td colspan="1">^_</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td></tr>
<tr><td colspan="1">W¡HXZsZ[tIrIXrYvv ZvYIuI</td><td colspan="1">AB9CC</td><td colspan="1">MNO</td><td colspan="1">MNO Z</td><td colspan="1">WYIvuXZIs[t</td><td colspan="1">_</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1"></td></tr>
</table>
¢f £dg¥¦i¤ei



|&

||||!|-!m'),l',!|&
(',	-|
| - | :- | :- | - | - | - | - |
|WIXN[ \s ZZXvOrWH|||OvI|MNO|||
|WPQQXKEFpRIY`SXPvH`pNZaFLsrvWrYOI|pv||OLvpFI|DGEHFI|||
|sZX¨vs|||ZOH \NO®IW¬WtZ:­tIH¬I¬Zu|MNO|OWW:IHIZu||
|X[NFIQouTK U\s` SZQZSXpvLOaPrEWH|||SZpvpv|JMKLNO|||
|WNYZOYXIvrXv[s|||WYIZv \Z|MNO|±²99CMBXB: ;C ®B< ;8||
|WPQYREHFIXbpvn`vst|||OLvpFI|JMKLNO|||
|WIZXWrZYHv[r|||[YYrr\_|GHI|||
¡

0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WIXN[ \s ZZXvOrWH|\_|<|MNO||
|WPFIQXTNK[ U\s` SZQZSXpvLOaPrEWH XQYQXKEvHpRNZ`SsP`prvaFLWrYpOIv|>A?3B@9C@C|JMKLNO|JMKLNO|WPQQXKEFpRIY`SXPvH`pNZaFLsrvpWrYOIv|^\_|6<|DGEHFI||
|WIXN[ \s ZZXvOrWH ZXWrZYHv[r|AB9CC|MNO|MNO|WIZXWrZYHv[r|\_|<|GHI||
|qwQXTNK[ U\s` SZQZSXpvLOaPrEWQHX YHXQREvbvpsn`t|>A?3B@9C@C|JMKLNO|JMKLNO|WPQYREHFIXbpvn`vst|^\_|6<|JMKLNO||
¥cf³e¥hi¢f

- 

  ! -!m'),l',! &('
,	-![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMwAAABCCAYAAAAbmDlaAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAYpJREFUeJzt3TGK1VAYgNEvwwOtLO0UWzsRXIDgGmYd7sptTCO4AJdhq2gzsXiZR+bJiH/jOHgOBC5Jitt83DS5t4A/tpyNl7tehP/Yul2nQJbqXfW6+nZPk4J/1efqqloP242lelM9qz621QRU9bVjI6dg6riyfKo+JBjYO32SHe54cP23ZwQPwcV9TwAeEsHAgGBgQDAwIBgYEAwMCAYGBAMDgoEBwcCAYGBAMDAgGBgQDAwIBgYEAwOCgQHBwO/d2k1pH8z36kf+54cbS/Wqet/Wyj6Yx9Wj7E0Ge0+qF21d+CSDAcHAgGBgQDAwIBgYEAwMCAYGBAMDgoEBwcCAYGBAMDAgGBgQDAwIBgYEAwOCgQHBwMA+mOscNw7n1nZd7IO5yIoD55Z2XQgEBgQDA4KBAcHAgGBgQDAwIBgYEAwMCAYGBAMDgoEBwcCAYGBAMDAgGBgQDAwcduOlelq9zEnKUMcmnrc7KPkmmLX6Ul1WbxMM1DGUQ3XV1sRy9tCR4/CrNYsIzP0Eam4javnV2kcAAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAN8AAABCCAYAAADXAQOxAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAZFJREFUeJzt3D1u1EAYgOHXJAWigwrRQQHiAtSIy1BwEsR1uA4FB6Ci4yemWC+KIogoCJ+zPI+0mrW3mWJee5uZAkYsvxmBm7Fun863G8+rV4mP2+v7Nt5pv+t4rT5W76v1GN+X6lOHicNtdNFhcZ9NT+Qaa/W5w8Nh9beTU7Fu457X8HplBAAAAAAAAAAAAAAAAAAAgL9oz1vu4U9dXsd7PqLhOM+1HJjEaTir3lZP2u8LZaleVu/aDnkSH6dgqZ5V96Ynco2lelA93b6LD/6xn29m8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8cEQ8XEqvrXvw5PqML9vxwvxcSrO2+/hSUdLh3lW4oMx4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMh4oMhx41996uH7X8zIvzKWXW3elxdtM8d7Uv16PKNY3wvqjd5E3I7LdXX6vV2vdf4lupD2/yWKz8AN2ttnw8H+H/8AHLyIne8nuzHAAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAATwAAABCCAYAAADKWmoDAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAkBJREFUeJzt3bFqFFEYhuFvZlfNNgkWAQkBS/UqvBF7e3Mb3kJsJVU6JaZJZ+UdpJWksQhJwDFkjkU2goIIQrIw//M0w8IWB2b2nRkW/pMAFNH95QgwJS1Jmy8/zJK8TtKvbj1w71qSMTfXvZv9dLUkn5N8uQ1el+RbbsIHldwGj+lqSb4nv9/VurjLAdPT/jgCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP/FtgY1/DrPNi+hqi7JyyRvY/OqqXuT5FWSzP/xRZiqLsnjJM/iKW/qnmbZOk94VOaVtoZf51jwgDIEDyhD8IAyBA8oQ/CAMgQPKEPwgDIEDyhD8IAyBA8oQ/CAMgQPKEPwgDIEDyhD8IAybgeA9kmexGww6uhzMwB0LclWkuvVLoc7tMiybfMk2draWjs9Pf0QE5Cpo0syb61tdl33MUlb9YK4G621WWvtOFkG7vDwsF8sFs+TPFzpyuCejOOYg4ODi729vQe7u7svZjPbWkzVzs7O1/39/TFZBm9zczOXl5eJV1qK6LourbU+N7+Brutc+lPVWpv1fd+P4+hPC6AOwQPKEDygDMEDyhA8oAzBA8oQPKAMwQPKEDygDMEDyhA8oAzBA8oQPKAMwQPKEDygDMEDypgnydHRUTY2Nn6sejFwX1prOTk5ubq4uJgfHx9f9717/1SdnZ1dj+OYZDnheHt7ezEMw6fW2oOVrgzu0TAMV8MwPFpfXx9XvRbuzvn5+fkwDO+TvLuda93FeHdgulps1ARU8hM5dG5+4X93IQAAAABJRU5ErkJggg==)

WIXN[OYItvZr  OvI MNO

LOKNU\XQNT[KOKYILnRFLtbSvapZr bRTa[YYRµ´rar DGEHFI 

|		
|
||
"! #|''
 
#()|
| - | - | - | - | - |
|867<9=>?;895:67|<87567 A8|C=>|NOPPQCR9RS TQ URV TW||
|X234-0Z+,\9:=>?8;/Y[.,7-|3^-8>e`bg`i@A,g/7-8:f0a\_1h;<`g cj;0kd82=g^Ye\`-|BC2=3> ,|/8:7-0ah;1<||
=:

=><67]

`	
 `lm



|'	
( mp|
|s	t
(|' 
!))|		
|	!(# (|`	
`#lw
)) '
 #||'' 	
#(|
| - | - | - | - | - | - | - | - | - |
|WPS TV Py|{RPQQ|}e <|C=> =>|=]67<5>98:?;<||V|C=>||


		
 
  
"! # ''
 
#( ) ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbQAAABWCAYAAACnz2N7AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAA1BJREFUeJzt3T9PJHUcx/HPb7OA2tibE3PJRZ8AWKm9j8CWytL6Wh4KiRVXwAO5mBhjKGzojCFGlJx3HMuOBbvcgn/uFskN8+X1Sia7zDbfYTbz3h1IfgkAAAB3Q1t4/DjJgyTn/Y0DDMjLJKt9D8G99yzJ0yTdeLajJfkqyadJfuxrKmAwuiR/Jnkvrz4YM0zd7HGo5/HnJN8lOR8v7PwjyZMk3+bVAQLAXdbNtoz/5YXp254IAP6PUd8DAMBtEDQAShA0AEoQNABKEDQAShA0AEoQNABKEDQAShA0AEoQNABKEDQAShA0AEoQNABKEDQAShA0AEoQNABKEDTgJlpcP6oYZbjnsi1sVw7ibLZ1PQwFDEdL8lmSL2fPGbaNJI/7HuKGPsjF7KPkatBWZps3KPA6HyX5JK4XFTxI8kXfQ9xAS/J+ks/zD0EDWIaY1XB5y27oBA2AEgQNgBIEDYASBA2AEgQNgBIEDYASBA2AEgQNgBIEDYASBA2AEgQNgBIEDYASBA2AEgQNgBIEDYASBA2AEgQNgBIEDeCea62l67rRkLbd3d3R6urqKAurbY8XjulFktMk3dv+ZQKDc5rked9DcCtONzc3Hx0eHj7te5BlbGxsZG9vL1tbW0dHR0dJrgbtnSRruaidqAH/ZS3Ju30Pwa1YW1lZ+TDJw74HWdZ4PH45Go1+uPy5z2EAuBNaFm7dDUlr7XJuf0MDoARBA6AEQQOgBEEDoARBA6AEQQOgBEEDoARBA6AEQQOgBEEDoARBA6AEQQOgBEEDoARBA6AEQQOgBOuhAdxzZ2dnL1prx33PsYzpdNomk0k3mUzO5/uuB81K1cCbcr2ooTs4OPi+tfZ134MsYzqdtv39/YfHx8ffzPddD9ogVywFeuF6UUM7OTl5tr6+ftD3IMvY3t5uOzs7mUwmk/k+txwBSGttiN+4r8zsn0IAKEHQAChB0AAoQdAAKEHQAChB0AAoQdAAKEHQAChB0AAoQdAAKEHQAChB0AAoQdAAKEHQAChB0AAoQdAAKOF60LpYVh14M64VdZQ4l/MVq7skvyT5vcdZgOH4NT4AV/Fbkp/6HuKGnudi9i5J2sIL8+feoMDruF7UMeRzudiwIc4PAH/3F39B9SU0BotQAAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAS8AAABWCAYAAACehBBlAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAuVJREFUeJzt3TFrJGUcx/Hf7M2diWYRK0GIoqQykMomhRYh+A5SiYV5A/a2eR+CpUkvgr29hSJHlBRbhEQIQS8mx2bGws15Kt5pYWb+5POBZXZ2WfgXD99hm+dJAAC4Hc3i+lKSd566Bxijx0m+SfKoXXwwTfJ+kvYffwL/n35x9fDkWfokPyf5Icmjm8Xy1yvAWPX544EHAAAAAAAAAAAAAAAAAAAAAAAAANwV94YeAJJM8vsuvnbH5Hkmf3sDA/o4ybtDD8HoNUk+SfJaIl6Mw2aSN4cegtFrkryX5OVEvBiHJg5/4d95sk7ECyhJvICSxAsoSbyAksQLKEm8gJLECyhJvICSxAsoSbyAksQLKEm8gJLECyhJvICSxAsoSbyAktrF9V6SpdgQjmG0SV5IsjL0IIzanzatbJNkaWnpjaurq08nk4kDObh1Xde9nmR9Mpl8MPQsjFpzfX19P4uAtUmyv7+/3Pf9W0nuDzkZd9Pe3t6vm5ubK9vb29OhZ2G8uq7rd3d3H56dnc2TRbzW19cnTdO82jSNeHHrptPpL6urqysbGxtDj8KIdV2Xtm2Ps+hW+9R3DkFgKE3f99Yez9Q0TR8HcADViRdQkngBJYkXUJJ4ASWJF1CSeAEliRdQkngBJYkXUJJ4ASWJF1CSeAEliRdQkngBJbVJMpvN8uDBg8dJ+oHn4Q66vLzszs/P58fHx93QszBeXdf18/n8SaOaJFlbW1ufz+dfxzbQDODk5ORqeXn5xel0ej30LIzbbDb7fj6ff5jk2zZJDg8Pv0vyys7Ojt0suXUHBwefX1xcfLG1tfXZ0LMwXqenp83R0dGX8Q+RETlI8tHQQzB6kyRfJXn75gagHPECShIvoCTxAkoSL6Ak8QJKEi+gJPECShIvoCTxAkoSL6Ak8QJKEi+gJPECShIvoCTxAkoSL8agj90x+Y/EizH4MclPQw/B6PVJHia5HHoQuNEsXvA81gpQ22/XgHqlmFV19QAAAABJRU5ErkJggg==)6=]>6]<9\]5<958<: 8  >7< C=>

`  `8¢£8¢< ¢ §f6;k¦:~¤~\_+d0/64+?;0 BC2=3>

=><7\<;:58] 8©77 }e < ­®¯

`	
 `lm



|'	
( mp|
|s	t
(|' 
!))|		
|	!(# (|`	
`#lw
)) '
 #||' '
 
#(|
| - | - | - | - | - | - | - | - | - |
|WPS TV Py|{RPQQ|}e <|C=> 6]|<=>9\6]85<958<:|±|V|C=>||
`  `²³´ ²

		
 
  
"! # ''
 #( ) ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAb8AAABCCAYAAADHaNgBAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAA15JREFUeJzt3b9rnHUAx/HP93nuzrREMAFNLXQRAgWl/4M6OHRz6iAtWYo4SFcnCw7OBSeliwhKl0IW9y5xy9LNrV0EEaSDXLgfj4OX9K4taEH6XPJ9veDhHnIZPkfg3nlygScBgMqUpcePk3yQZNzfHIDnHCV5re8RrJgvHpteV7ycLsm3SX5N0h0PL0neTfJOngYRAM6kwdL5kyQ/JvkhT6sOAGfOabpkBYD/hfgBUB3xA6A64gdAdcQPgOqIHwDVET8AqiN+AFRH/ACojvgBUB3xA6A64gdAdcQPgOqIHwDVET8AqiN+AFRH/ACozuDfvwWgN2XpvOttBctO48+kLI5ucaxc+c0WB8C6eDPJl/FXqnXSJvk6yVbfQ15Cm+SLJO9lEe/mmSfbHkYBvEhJspnkw3hvWidtko+SnMvqVeA6a5O8n+TC8Rf8NgVAdcQPgOqIHwDVET8AqiN+AFRH/ACojvgBUB3xA6A64gdAdcQPgOqIHwDVET8AqiN+AFRH/ACojvgBUB3xA6A64gdAdcQPgOoMjk+apukuXbr0+v7+/sXt7e2uz1EA4/G43L59+6179+5lMpn0PYclw+EwN2/e3Ll161bZ2NhY+14cHByMrl+/3o7H45OtJ/Fr27bcuHHj083Nzc9ms9navxjgbGuapuzt7Q0ePHjQPH78uO85LNnZ2bl47dq175umKaehF1euXOkuX7584eHDh5lOp0lWr/zajY2Nt0sp21239q8FOOOapsloNOoGg8FvfW9h1XA4PD8YDHabphmekl7MRqPRPEsf9Q2Wny2lJEl5xaMAntN1XU7JG2t1Sinpuq50XVcW3Vhri5ErQ/3DCwDVET8AqiN+AFRH/ACojvgBUB3xA6A64gdAdcQPgOqIHwDVET8AqiN+AFRH/ACojvgBUB3xA6A64gdAdU7u5zebzbqjo6M/u677q5TiJlpA38p0Om2nx7feZm1MJpPJfD7/I/9cQK19L+bzeabT6VaWth7f3K+0bftJ27ZPDg8Pf+5nHsCqq1evXnz06NFX8/l8L4kIrodhKeWn3d3dz+/fv/9732P+izt37gzu3r37zWw2+y7JL3km2OeTnOtlGcCLtUm28sxduOlVSbKd0/WxWUnyRpJR30MAAAB4Vf4GX/KcnWo8l58AAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASQAAABCCAYAAAD+I6sfAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAkZJREFUeJzt3LFKHWkYx+H/Nycn2TVoICDIsmBjEGJhIeQGUqWzDGyxVe5AttvWwotYb2CxTaWX4cI2y7IQUiWaxiORmRQeE7P9cV7weWAYpnuL4TcfU7wJQBFtfn+Z5NWtZ1iUz0mmYw9BKUOSt0mOu7EnAQAAAAAAAAAAAAAAAAAAAAAAAAAAoCo7tLkrt9+1YbQpqOhmlXY/GXUM7pMuyZskT5P8M/Is1PJrkp+T/G3JP3elJXmR5FmczPneTpLnybejEsDoBAkoQ5CAMgQJKEOQgDIECShDkIAyBAkoQ5CAMgQJKEOQgDIECShDkIAyBAkoQ5CAMgQJKEOQgDIezO+TXG/xs8mPRZncuqZJ+nHHoZCvB6MHSbK2tvZLkjetNScmFqLv+3Z+fv5kOp3uLC0tve46rxrXzs7OHl9cXPybzIN0eHj402Qy2el7S/9ZjGEYhv39/Q9bW1s/7O7uLsVpnLmDg4P3JycnSeZB2tzc7FprD4dhECQWou/7YXl5ebq6ujrd2Nh45ITEjZWVlWnXdV3f91//ISVJa81Hi4VprbXcvuD/fKaAMgQJKEOQgDIECShDkIAyBAkoQ5CAMgQJKEOQgDIECShDkIAyBAkoQ5CAMgQJKEOQgDJu9iF9SvJfbPFjcdpsNutns9ksycckw9gDUcPl5eXnvr9esd6SZL4psjs9PRUkFuL4+Ljt7e393nXdX0dHR3+ur68LEkmS7e3t366urt4l+WPsWbg/WpKVJD+OPQjlLCd5PPYQAAA1fQGQ6l1VL69iTgAAAABJRU5ErkJggg==) 56=<><96?¢]5<958<: =>?<9\7  >7< C=>

`  `8¢£8¢< ¢ ¶fk6;¦:~¤\_+d0/64+?;0 BC2=3>

`	
 `lm



|'	
( mp|
|s	t
(|' 
!))|		
|	!(# (|`	
`#lw
)) '
 #||' '
 
#(|
| - | - | - | - | - | - | - | - | - |
|WPS TV Py|{RPQQ|}e <|C=> =>?< 9\567|]=>9?6¢<5<958<:|¸|V|C=>||
²´



|		
|
||
"! #|''
 
#()|
| - | - | - | - | - |
|]<>9?=785<958<: |>7<|C=>|||
|1£8¢< ¢ ¶fk6;¦|µ:~¤\_+d0/64+?;0|BC2=3>|||
`  `8

`	
 `lm



|'	
( mp|
|s	t
(|' 
!))|		
|	!(# (|`	
`#lw
)) '
 #||' '
 
#(|
| - | - | - | - | - | - | - | - | - |
|WPS TV Py|{RPQQ|}e <|C=>|]<>9?=785<958<:|º|V|C=>||
» ¼´» ¼



|		
||
||
"! #|''
 #( )|
| - | :- | - | - | - | - |
|59e5=<: A8 >7  ||>7<|C=>|||
|0+Y.6\=\*.5Y9;,1<20 ,7||3,>71<|BC2=3>|||
|8¦: A8;<¦>:9½<|k¶§¸f6;¦|:6?;|C=>|||
|3>2= @A9.>3+<6¢ <12=0;|¶k¸f6;¦|·µ:¤d\_+0/64+?;0|BC2=3>|||
|\89[7 AA<8|§f6;¦|:k6?;|}e <|­®¯||
|+1¢½<6<,7||3,>71<|^|}e 1<|ª«­®¬¬¯||
|` `=65¢895e\67||<87567 A8|C=>|NOPPQCR9RS TQ URV TW||
|23]<=>Z1084-?;||3,>71<|BC2=3>|¥§||
|[\895=:e597< A8 >7||>7<|C=>|||
|2+\*^3Y>^e. ^=0-65¢Z9e\+,18e;@A67] <81¢-<=^,9e72.0;|¶fk6;¦|µ~:¤\_+d0/64+?;0|BC2=3>|||
6>e5<9877;\

8;<¦ :89 

`	
 `lm

|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|VWIVNXH YZ O[|]|<|MNO||
|VPbWQ^FIPKVRNQaK`UX\_WdeN[cc|>A?3B@9C@C|JMKLNO|JMKLNO|VPWQaKF`UQIdH\_eN[dTOcZLVUEc`U[PIF|\]|6<|JMKLNO||
|VbWIVNXWHgZc|AB9CC|MNO|MNO|OHYZHWgd[ZcNHIdVeiNciWIH[|]|<|MNO||
|kQPKjmnWRQaTVUlNXWoeZ[|>A?3B@9C@C|JMKLNO|JMKLNO|VPWQaToeZFIQWUl[PKVRNEXH SYTZ LOU[|\]|6<|JMKLNO||
tsprqr

- 

  ! -!w,x'),',! &'
,	- ( OHYZNcWZzOgZdOI![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbYAAABrCAYAAAAb3qc1AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAA/hJREFUeJzt3T9vG3Ucx/HPOW7T0kbIyoDUCfFnQAydO8AjQGJmzJiNlYGVB5GZx0CY0gGpM2JgKRISA0JIIFHUxE2wzZBLe4maRCkO5/v29ZJ+0l3Okb7Wne5tW5acAEAhzZntUbu96GEWAPgv5smLsDVJPknyUZJpXxMBpR0kud33EFxoyOfocZKvkyzG7R+aJB/muHbfxTs2YPmOktzoewgudJjkZt9DvIJFkj9PdsadA9MkPybZS/t2DgAGZJGcDlv3gHdsAAzS6PKHAMBwCBsApQgbAKUIGwClCBsApQgbAKUIGwClCBsApQgbAKUIGwClCBsApQgbAKUIGwClCBsApQgbAKUIGwCldMM2axfAsq0ludP3EJyrSbKe5Ha7PTTjdK6vl71j8+vZwLK9l+TLDPOm+Tpoknya5PMM8xy9n+SLtLN3w7bWriE+KWC1vZHjuLm/rK63krydYZ6jO0nezUvCBnCdhnjDZDieX1/CBkApwgZAKcIGQCnCBkApwgZAKcIGQCnCBkApwgZAKcIGQCnCBkApwgZAKcIGQCnCBkApwgZAKcIGQCnCBkAp4872ol0A18H9ZbUtJpPJ3b29vXc2NzfnfQ9zFdvb2/d2d3cznx+PPb7k8QC8BkajUfPgwYOPNzY2vp3NZn2PcyVbW1ujhw8ffr+/v5/kdNia+Ol24Pq4v6y2Zn19/W6SN/se5KrG4/HRaDT64fl+n8MAsHIG9wKkaZo0zYuxfXkEgFKEDYBShA2AUoQNgFKEDYBShA2AUoQNgFKEDYBShA2AUoQNgFKEDYBShA2AUoQNgFKEDYBShA2AUoQNgFL80CgASZKDg4O/kvzaNM2i71mu4vDwcDybzeYn+92wTZM8SzKoJwQMwizJ330Pwfnm8/nBo0ePvnn69OlXk8lkfvl/rI6dnZ0PptPpZyf73bDdSrKe458FFzdgmdaSbPQ9BBe69eTJk/H9+/d/y/ELkSG5l+TuyY6PIgE4seiswfLlEQBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYASumG7Z92ASzbIsmzvofgQkdJDvse4hXN07m+umEbtwtg2Zok630PwYVuJLnZ9xCvaJTO9eWjSABKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEoRNgBKETYAShE2AEo5G7ZFuwCWzb1ltQ35/Jxq11rnwDTJL0n++L8nAsqbJfk9yeO+B+Fc0yQ/t2toTq6vn84eaNoFsGzuL6tvyOdoyLMDwMX+BbcufStcBzFtAAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAS4AAABrCAYAAADJom8oAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABC1JREFUeJzt3b9ra3UYx/HPc0yaa68F5SKkSGPxLpJRFxen+i8UFG6Lm+AQO9yOQjq4lG4OjsU6OnWzQxGXcqdb7VBaWtqhQwdDwSAmoTnn69DkmlRSdMn3PJz3C0J6Sodn+PLOj8JzJABwxu79bJP+EAByIJP+CZVJ+kTSx9HGAYCHnUv6QVIojfzyUlKIMw8APChIuhle3P+oCAB5xpsrAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/D9zkl6LPQRyryTpcewhAOluXfj3kt6JPQhy731J32iwYj6JOwsKziTVJT2KPQhy77GkpyJcyAlu0oL/6tVZIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcKQ2eTXcRY4Uupml47kqSZiLPgnwr/eui2Wzazs7OczP7Ms5MKKIQgl1dXb1erVZ/npmZuY09D/Kr0+lk19fXL0MIkgbhqtfrtr29/aakWszhUCwhBK2urrY3NzefzM/PswkVE52cnPzVaDSs1+tJGnn7VavVNKwZMA0hBJXL5aRardrCwkLscZBjNzc3ppHVzWOfG8140cP0jL5QcvbwkPvng/8qAnCHcAFwh3ABcIdwAXCHcAFwh3ABcIdwAXCHcAFwh3ABcIdwAXCHcAFwh3ABcIdwAXCHcAFwh3ABcIdwAXCnJEnHx8ehUqn0zawTeyAURwhBnU4nOz097bbbbdbvYqLz8/Pe2OLJwXMyOzv7RQjhWZyxUFDW7XafVCqVP8yMm2VgohBC0u12zyR9Likb3Yc6ttMZmIJE0gtJn0q6iDwL8u1DSc8lfSYpG905HwYPYNqCpCz2EMi1sfPBl/MA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3BkuErRKpfKemb0bdRoUjfV6vdkkST4ql8ucPUx0e3v7NE3TVxuaS5LUbDbt4uLimaSvok2GwgkhaHd3t7e0tPTt3Nxc7HGQY61Wq7e3t/dLmqaSBuGq1+u2srLySNJbMYdDsYQQdHBw8Gej0XijVqvFHgc5dnR01Nnf36+MhUuSkoSvuzBdWXa3RtzMOH94kNn4fXw4LQDcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcIVwA3CFcANwhXADcKUnS8vJyuLy8zJIk6cceCIViIYRMUmpmIfYwyLV09GK4ATWY2Y+Sjqc/D4oqyzJrtVpfn52dfbe4uPh77HmQX4eHhwv9fv+D4XVJkswshBB+29jYOIo3Goqo3W7319fXf1pbW2vHngX5tbW19Xaapr9K4p05csEGD+AhnBMAvv0NBAjf1yBEg2EAAAAASUVORK5CYII=) [  Xdcdc] MNO

VPWQFILFOTEZ`QKIdHa\_`W`Ndecd NOIKUF[L ~}|{}~ OL[UFI JMKLNO

ZVWd[NVOgIZXI IZV[d[ YZ MNO

RXKNSYSYTZZWcT\_QdIVLOFPU`TZ[RXFI NKFOLfgI Zb[UT[U^ DGEHFI 

0



<table><tr><th colspan="1">``	
<br></th><th colspan="1"></th><th colspan="1">
!	 </th><th colspan="1">&)(('</th><th colspan="1">&

</th><th colspan="1">&)'	,
-,	</th><th colspan="1">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1">&'
,	-</th></tr>
<tr><td colspan="1" rowspan="2">89: ;< 9=</td><td colspan="1" rowspan="2">AB9CC</td><td colspan="1" rowspan="2">GHI</td><td colspan="1" rowspan="2">MNO</td><td colspan="1">OHYZNcWZzOgZdOI[</td><td colspan="1">]</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1" valign="bottom">VPWQFILFOTEZ`QKIdHa_`KWUFNL`NOdecId[</td><td colspan="1" valign="bottom">\]</td><td colspan="1" valign="bottom">6<</td><td colspan="1" valign="bottom">JMKLNO</td></tr>
<tr><td colspan="1">VbWIVNOOWZcOZIdH WNNOdecId[</td><td colspan="1">AB9CC</td><td colspan="1">MNO</td><td colspan="1">MNO</td><td colspan="1">VWIOZIdHWNNOdecId[</td><td colspan="1">]</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1"></td></tr>
</table>
ss



|&

||||!|-!w,x'),',!|&('
,	-|
| - | :- | :- | - | - | - | - |
|VWIZXHZ|||O[I|MNO|||
|IWFQZhiXTRHEZT|||RX\_`d]c\_]`\ \dc|JMKLNO|||
|VWIHZ|||O[I|MNO|||
|gWQRTXZ[UfZ\_PTVcFXRIEHTZ|||OL[UFI|DGEHFI|||
|INOWVe[ZXgIHcZ|||Zb[[|GHI|||
0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|VWIZXHZ|]|<|MNO||
|VPbWQ^FITRZEXTHQZEWTHZ|>A?3B@9C@C|JMKLNO|JMKLNO|VPWQFIETHZ|\]|6<|JMKLNO||
£¤¢¡strs



|&

|||||!|-!w,x'),',!|&'
,	- (|
| - | :- | :- | - | :- | - | - | - |
|OHYZHgd[ZcINciWIH[|||Xdcdc]||MNO|||
|VPNWQFKIRO`UFILdX[ ~}|{|``}~ ||OL[UFI||JMKLNO|||
|ZVNWd[OIdX[|||IZV[d[|YZ|MNO|||
|TTfQPa`\_fdVFZgZcWeI|||RX\_`d¥c\_`¦dc||DGEHFI|||
|HWgZcdzZO[|||Zb[[||GHI|||
|VZWPWQ[FITXUTLZIQNUFORO[KXLOZRcZTL\_T|TZ||OL[UFI||DGEHFI|||
|oeZWW[[XZINOO[XZc|OZZ||Xdcdc]||GHI|||
|fQRKiWdINOX`UFLh[PVdTIFU`Z[|||KgNfLO§¨||DGEHFI|||
|gd[ZNgZOIgIWViWIIH[|Hc||Xdcªdc||GHI|||
g

0



<table><tr><th colspan="1" valign="bottom">``	
<br></th><th colspan="1"></th><th colspan="1" valign="bottom">
!	 </th><th colspan="1">&)(('</th><th colspan="1" valign="bottom">&

</th><th colspan="1" valign="bottom">&)'	,
-,	</th><th colspan="1" valign="bottom">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1" valign="bottom">& '
,	-</th></tr>
<tr><td colspan="1" rowspan="3">89: ;< 9=</td><td colspan="1" rowspan="3">AB9CC</td><td colspan="1" rowspan="3">GHI</td><td colspan="1" rowspan="3">MNO</td><td colspan="1">OHYZHgd[ZcINciWIH[</td><td colspan="1">]</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1" rowspan="3"></td></tr>
<tr><td colspan="1">VPNWQFKIRO`UFILdX[</td><td colspan="1">\]</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td></tr>
<tr><td colspan="1">ZVNWd[OIdX[</td><td colspan="1">]</td><td colspan="1"><</td><td colspan="1">MNO</td></tr>
<tr><td colspan="1">VZWNPbWcOQ^TQFIKdTU_RX`g[TfUFZL[_yOFZzIc</td><td colspan="1">>A?3B@9C@C</td><td colspan="1">JMKLNO</td><td colspan="1">JMKLNO</td><td colspan="1">VPNWQFKIRO`UFILdX[</td><td colspan="1">\]</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td><td colspan="1"></td></tr>
</table>
- 
  `		`



||||||!" |,"10+/&.,"- , |55 -76,1|
| - | :- | :- | - | :- | - | - | - |
|=> ?@ABC@=D@F=EG=H&|QRTS UVWSTX||G`AbaGdefAc||hB=|||
|=B9;?|||^\_iGY`\AbefZaG[jA:c||gh;B=|||
|A@l=B?|||G`AbefaGjAc||hB=|||
|H@ @m ob B;lk[n= @;<k:CAB@lpDD|B;E==@ om||q^\_GY`\AbZaGef[rA:c||st>8 E|uxvywzw||
|@?GCAB@lopEDDBE==@|o||G`AbaGdrfAc||t> E|xyz||
|=>8 9?@ <8;m:ADBEGC o>H@HE8:>A|||^\_]GY`\AbZaG[defA:c||gh;B=|||
|G|H@C=GDEDG=@a|||G|H@||t> E|xyz||
|@Em o>C8=<GDEDG=@aZ |||^\_iGY`\AbefZaG[jA:c||st>8 E|uxvywzw||
|Gl~DC=GDEDG=@a|||G`AbaGdefAc||t> E|xyz||
|=GBEH@=GE;om |||^\_]GY`\AbZaG[defA:c||st>8 E|uxvywzw||
|D@@|||@=> ?c G=&DE>ao|?E=&E>HA@f|t> E|xyz||
| GA@|{:DD @<k;CBlDm HG@o|||H@ H||st>8 E|uxvywzw||
|o@E`|||G`AbefaGjAc||t> E|xyz||
|@{B Za;|<CkB;lDHGmo|||q^\_GY`\AbZaGef[rA:c||st>8 E|uxvywzw||
|H@@obBl=@C@lADB==|@o||G`AbaGefrAc||t> E|xyz||
| @9?GC@lom<EAkD B;:==@ mo|||]\_q]GY`\AbZaGd[rfA:c||st>8 E|uxvywzw||
|bBlCHBba@oCABElp?|=E||G`AbaGdrfAc||t> E|xyz||
.



|! 5&.&6.||6 "|5-7/||1/6.,-,|17-, 5 1|!" |55 -6,1|
| - | - | - | - | - | - | - | - | - |
| |¤¥¦¦|t> E|hB=|=> ?@ABC@=D@F=EG=H|e||hB=||
|8>C§<¨ @=D@ F=EG=HC=><8 9?@ <8;m:ADBEGC o>H@HE8:>A|¡¢¤¥£¦£¦|st>8 E|gh;B=|=>8 9?@ <8;m:ADBEGC o>H@HE8:>A|^e||gh;B=||
- ©©
  `
`ª



|||||!" ||,"10+/&.,"- , |55 -76,1|
| - | :- | :- | - | - | :- | - | - |
|C@|E=HA@DAE@l&|QRUTS|VWSTX|=HE|hB=||||
|¬CE@m«o< =HA @D kA:E@l|||q^\_GY`\AbZaGef[rA:c|gh;B=||||
|D@H@a>AH@CEG`HEa|||G`AbefaGjAc|t> E||xyz||
| GA@|{:DD @< C @ k:=HA@DAE@l|||H@ H|st>8 E||uxvywzw||
|Ba=HGHCa=B?|||G`AbefaGjAc|t> E||xyz||
|BZa;=HGHCaZ@< |9?GomE||]\_q]GY`\AbZaGd[rfA:c|st>8 E||uxvywzw||
|Ba=HGHCaH@@obBl=@|||G`AbaGefrAc|t> E||xyz||
.



|! 5&.&6.||6 "|5-7/||/16.,-,|17-, 5 1|!" |55 -6,1|
| - | - | - | - | - | - | - | - | - |
| |¤¥¦¦|t> E|hB=|C@|E=HA@DAE@l|e||hB=||
®­
`	`



||||||!" |,"10+/&.,"- , |55 -76,1|
| - | :- | :- | - | :- | - | - | - |
|=> ?@ABCGaAH@C@H>||GE=H& QRTS UVWSTX||G`AbaGdefAc||hB=|||
|=B9;?|||^\_iGY`\AbefZaG[jA:c||gh;B=|||
|A@l=B?|||G`AbefaGjAc||hB=|||
|G{|H@ C=<GDEDG=@aZ |||G{|H@ ||st>8 E|uxvywzw||
|@Eo>C=GDEDG=@a|||G`AbefaGjAc||t> E|xyz||
|Gkl~DC}=<GDEDG=@aZ |||^\_]GY`\AbZaG[defA:c||st>8 E|uxvywzw||
|=GBEH@=GEo|||G`AbaGdefAc||t> E|xyz||
|D@  @||| @=>8 9?c\ G=&DE>aZo8m&|9?E=&E>&HA@ 8\_f:|st>8 E|uxvywzw||
|GA@|DD@CBlDHG@o|||H@H||t> E|xyz||
|||||||||

|		
|||
||
"! #|''
 
#()|
| - | :- | :- | - | - | - | - |
|0/,\*+.||?@>;:|7;<=@A:869452\*3.|BDCE +/|FIJKGKHH||
|<RS1TURVW0:||Y>;:|;<=@A:.|DE /|IJKK||
|O5LZ,-Q-W1U10=R1[||Y>;:|X;<=@A:64528\*39.|BDCE +/|FIJKGKHH||
|1]: 0/||Y>\_;:|;<=A:.|DE /|IJKK||
|OUN5=T\L45R-<QL=0,W1N]+ROLT/R3`Ua;|[Z+/|Y>\_;:|X;<=A:6452^\*39.|BDCE +/|FIJKGKHH||
|E[ ]1VW:1ER/;T0;EW/||\_>;:|;<=@A:.|cR[|||
|4L<RZW<[W24QN:<e1[TCZE[dN4-;3Z|LR\]|?@>;:|7;<=@A:869452\*3.|BDCE +/|FIJKGKHH||
|<RW<[W:<e1[TEW;|1U10=R1[|Y>;:|;<=@A:.|DE /|IJKK||
|4L<RZW<[W24QN:<e1[TCZE;dN4-;33|LR2ZQ+[W:/,-10|\_>;:|^8;<=@A9:6452\*3.|BDCE +/|FIJKGKHH||
10

`	
`f g



|'	
( 
 gj|
|m	n(
|' 
!))|		
|	!(# (|`	
`f#q
)) '
 #||''
 
#(|
| - | - | - | - | - | - | - | - | - |
|xyz {| y}|y|DE /|cR[|E[ ]1 WR1WT[;1;<W:ES/|@|||cR[||
|CEWTN[1TQCW:E-[MES2Z/+QNZC \]-1VW:1EPC2R/;Q-+3LT03N;EWC,/Q+|~sy|BDCE +/|bcRLZ[|EZ[C \]-1VW:1EPC2R/;Q-+3LT03N;EWC,/Q+|8@|v||bcRLZ[||




|		
||
||
"! #|''
 #( )|
| - | :- | - | - | - | - |
|E[ ]1 WR1T;1;<W:ES W[: / |\_>;:|;<=@A:.|cR[|||
|EZ[C \]-1[R:e[LTV3N;1d[Z1/-+2PZ- QW|\_>;:|^;<=@A89:6452\*3.|bcRLZ[|||
|ST1<E/1 ||[W/|cR[|||
|R[EW:W/MS0C:L1:T2ZQ+N12.Q-\*2,-||WMS:1WQ+2/Q- \]-1|bcRLZ[|||
|R[W1||S1</ ]: \_>Y0A|DE /|IJKK||
`	
`f g



|'	
( 
 gj|
|m	n(
|' 
!))||		
|	!(# (||`	
`f#q
)) '
 #||''
 
#(|
| - | - | - | - | :- | - | - | :- | - | - | - |
||||||E[ ]1 WR1WT[;1;<W:ES/|@||||cR[||
|xyz {| y}|y|DE /|cR[||EZ[C \]-1W[R:e[LTV3N;1d[Z1/-+2PZ-Q|8@||v||bcRLZ[||
||||||ST1<E/1|@||||cR[||
|S0E;T[V1/M:T+/1.NCd3Z-+PN-\*2,- W[:Z[2ZQ|~sy|bcRLZ[|bcRLZ[||EZ[C \]-1W[R:e[LTV3N;1d[Z1/-+2PZ-Q|8@||v||bcRLZ[||
|S0;T<E1:T/1.|y|cR[|cR[||ST1<E/1|@||||cR[||
e

¡¢£¤ ¥



|		
|||
||
"! #|''
 #( )|
| - | :- | :- | - | - | - | - |
|STU/V1W1[;/| ||[W/|cR[|||
|EZ[C \]-1 WR1TL;13;<WN:-QCE423-SM|W[:2Z+Q/ |\_>;:|^;<=@A89:6452\*3.|bcRLZ[|||
|WS:1TS1§EWTVWe1:|||WS:1|cR[|||
|[TVWe1MSZ/:1NT+Na2Q-PQ2d`-|||WMS:12Q-|BDCE +/|FIJKGKHH||
|VE1©WTVWe1:|||W1W|DE /|IJKK||
|RZ[L \]NT QCWCNQ-OE3Z-W1-TUQ3W1E[;|V1P/-+3;|?@>;:|7;<=@A:869452\*3.|BDCE +/|FIJKGKHH||
`	
`f g



<table><tr><th colspan="1">'	
( 
 gj</th><th colspan="1">
</th><th colspan="1">m	n(
</th><th colspan="1">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">`	
`f#q
)) '
 #</th><th colspan="1"></th><th colspan="1">''
 
#(</th></tr>
<tr><td colspan="1" rowspan="2">xyz {| y}</td><td colspan="1" rowspan="2">y</td><td colspan="1" rowspan="2">DE /</td><td colspan="1" rowspan="2">cR[</td><td colspan="1">STU/V1W1[;/</td><td colspan="1">@</td><td colspan="1">|</td><td colspan="1">cR[</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">EZ[C \]-1 WR1WTL[;13;<W:N-QCE423-SM/2Z+Q</td><td colspan="1">8@</td><td colspan="1">v|</td><td colspan="1">bcRLZ[</td></tr>
<tr><td colspan="1" valign="top">S/T1W:T/a W[:ES/</td><td colspan="1" valign="top">y</td><td colspan="1" valign="top">cR[</td><td colspan="1" valign="top">cR[</td><td colspan="1" valign="top">E[ ]1 WR1WT[;1;<W:ES/</td><td colspan="1" valign="top">@</td><td colspan="1" valign="top">|</td><td colspan="1" valign="top">cR[</td><td colspan="1"></td></tr>
</table>
1TV;e:

«¡«¬¤ª 

		
 
  
"! # ''
 
#() ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAY8AAABDCAYAAABkx4mcAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAwBJREFUeJzt3TFrZFUcxuH33sTYObsBEVmx0sJWq5SChZZCvsUWqYTFxlpIYRPyKdIJVvoRbAxWghCsBC2GuDtrwhyLzG6um7DyHxOvM3keGCYzmeIeOHN/99wpTgIARd3g7/6F1wAwNE/SkmRz8UaX5NMkHy/+CSynLZ5diN1t54vnzZd+arW0JJ8n+T35ezzeTfJbkm9z+QUAasSD5PIivB/1KG5WS/L42YthFWdJfkzyXaw+ALjq+cLiuiVVi5UHAC+xTksqAP4j4gFAmXgAUCYeAJSJBwBl4gFAmXgAUCYeAJSJBwBl4gFAmXgAUCYeAJSJBwBl4gFAmXgAUCYeAJSJBwBl4gE3q4/9y7mYA+s0D66MZxiPPxcPW9DCcvok3yR5a+wDYVQbSb5M8kHWJyD3k3yd5EEWYxrGY2vxWJfBwhheT7I59kEwqi7JJBfn03XR52JubwzfAOBmrdttqyvEA4Ay8QCgTDwAKBMPAMrEA4Ay8QCgTDwAKBMPAMrEA4Ay8QCgTDwAKBMPAMrEA4Ay8QCgTDwAKLNpDcDtWds9PYbxeBrb0MK/NU0yH/sgGFff93/s7OxMDg4O3tze3l75c+rh4eG9/f39x2dnZ8/HMozHq7nchnblBwsjeS1uB995W1tb9/b29o4mk8kr8/nqX0vs7u4+PTo6mp+cnPSz2SyJ21YAt6Hruq5rrW3880f//1prfV5YVLhCAqBMPAAoEw8AysQDgDLxAKBMPAAoEw8AysQDgDLxAKBMPAAoEw8AysQDgDLxAKBMPAAoEw8AysQDgLLrNoPqssb77sIt6gbPvkN3V5ck7dLKz4XWWnKxGdSVbWhbkp+TvJ3ko9iGFpbRJfk+yftJ3hn5WBhP31r79fj4+JfpdHq/61a+HTk9PX1yfn7+w2w2e5JFH4aj6pN8luTDuGqCZbQkD5N8keSNkY+F8bQkX/V9/0nXde9lDc6n8/l82lp7lOSnXBOPZ0ttv4PA8uZx2+quG965WZd50HIxt92VAmB5fwE3x4OiRd8XLgAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALgAAABDCAYAAAAvZs8dAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAZZJREFUeJzt3bFq21AYR/FzFdPJQ6dApr5Dtuyd+hDFe/eSp8jct/EeiAnukt1boXTSULD0ZZDcmq4NGP05PxASmu5wuEjDJ4EUrM3n7uxaWro6Hav5xmfg7nLrkd7UM/AN4BT4I3DAXVwZfjC1XKegDVtp6tILkCRJkiRJkiRJkiRJkiRJkiRJkiQtXcMRTIVqwC3wAFzB9D0UKck74D3zLm7gimbgimbgimbgimbgimbgimbgimbgimbgimbgimbgimbgimbgimbgimbgimbginb6jeA1sMZRHy1bA24427hXAOv1+sswDJ8wcC3cMAzd8Xh8GscRmAPf7/cfmGbZDFyLVVXsdrvfm83mpe97YA68tQZOIyvA3PIfvmQqmoErmoErmoErmoErmoErmoErmoErmoErmoErmoErmoErmoErmoErmoErmoEr2gqmSYiquvRapP9VZwcwB77dbvuq+tn+HYeQFqSqOBwOv4DvzJFPv1rruq+ttY84sqaFq6qXcRzvgR7+Bt3h87gyFDBy9pgixXoF789LJO8+NkcAAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAABDCAYAAAAbHw4BAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAY1JREFUeJzt3bGqE0EcRvEzc7cQbuBa6TMIgtjY+EDWeQEri9v7JAHBLi9gEwTLC3Z2FmmzM2Oz4eYFsh+S84Mlm26KwwwL+WdBCioXnzW5EN2cAfRp+fIO+IQRah0D2AHfzzvgK+AtBqh1DOAJ+H15BEtrG+kFSJIkSZIkSZIkSZIkSZIkSZL0fynLxV14IbpNX4A3wA+n4JTwAGzAMUyFGaCiDFBRBqgoA1SUASrKABVlgIoyQEUZoKIMUFEGqCgDVJQBKsoAFWWAijq/J+QeeI3/lq913J9vJoDNZvOhtfaIP9HXCk6nU5nn+ScsAR4Oh5e11vc874jS1Wy32z+73e4FLMFN00TvvZRSPIJ1daWUUmul9+5DiLIMUFEGqCgDVJQBKsoAFWWAijJARRmgogxQUQaoKANUlAEqygAVZYCKMkBFTQDzPFNKYYyRXo9uw+i9D1gC3O/3p1LK31qrMyG6uuPx+At4gmUKbpqmj621z7VWj2St4Wtr7RvQzzMglYu310hX1pdLyvoHIjBBf15XNKUAAAAASUVORK5CYII=)RSRT<[W[a//  W[/ cR[

RR§T<[W[a//,0 ?@><@;=:;7A:869452\*3. bcRLZ[

		
 
  
"! # ''
 
#()![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAY8AAAAsCAYAAACdZTQTAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAaxJREFUeJzt3U2uDGEUgOG32m8MzMTcTMI6LMMWWIMlSOzCWsxNJYgJiYGf614GfUVjIOfqKNrzTKrzdQ9OJ1/1W9WTWtraVA+qu9WbgLM6OT1uVp2CtX08PV5cdYr9OqnuVC+qltPFTXW/+lQ9rj6vMhoAf7OX1XHV+Z3F4+p19bxvV08A8BO31gCMiQcAY+IBwJh4ADAmHgCMiQcAY+IBwJh4ADAmHgCMiQcAY+IBwJh4ADAmHgCMiQcAY+IBwJh4ADAmHrBfy68/Av+k7/b2bjxO2j5N0CNo4WyW6nZ1ee1BWNVS3aiurj3IHl2oblWXvi78eOex5MoJfsej6vraQ7CqTXWvutnh/J5erR5W174u7MZjk7+x4Hcs1ZWcR/+7pe3d5/m1B9mj3b29lE0OwBmIBwBj4gHAmHgAMCYeAIyJBwBj4gHAmHgAMCYeAIyJBwBj4gHAmHgAMCYeAIyJBwBj4gHAmHgAMCYeAIyJBwBj53Zen1TPqlcrzQKH4Kh6Ur1fexBWdVQ9rd6uPcgevWu7tz/8+MbS4TysHdbiPKIOcx8c2vcB4E/7AvlAIAnfcy+7AAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALgAAAAsCAYAAADWxHKSAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAMBJREFUeJzt3bFJRFEURdH9VBgQDKzBwG4sZrAFy7IRm9DMSDSYsYMPn7mzVgUn2LzscVcnb9VLtYLL914dq5//oB+r+/32wKa+q8/qd+8hAAAAAAAAAAAAAAAAAFyyh+qp8//im323wKZW9Vy9dm5b4ExzVx3ygnMNBM5oAmc0gTOawBlN4IwmcEYTOKMJnNEEzmgCZzSBM5rAGU3gjCZwRhM4owmc0QTOaLd7D4ANrU63Mb+qj9zJZKCVi91ciz9BqArUL3PRZgAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAAAsCAYAAADivbOOAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAALZJREFUeJzt2zEuBVAURdH9SQSdIRiLORiEwWiUhqY1Ar8mCqJTSMgprJW85iU3ucVu76EPN9V9dRr8vbfqoXo8fH5cVFfV4dsR+F0v1XG9BAAAAAAAAAAAAADAjx0+nxsQJq6ry+p4st6Ef+muuq0SIAvn1VkJkDEBMiVApgTIlACZEiBTAmRKgEwJkCkBMiVApgTIlACZEiBTAmRKgEwJkCkBMuUoiYXX6ql6Xi/C//R1lglT7wGwCd0CE3elAAAAAElFTkSuQmCC)

\>@?<;A>?9=;6789: 7C== FG ; KLMM

N	
 O



|'	
 
( RO|
|U	V(
|' 
!))|		
|	!(# (|N	
#Y
)) '
 #||''
 
#(|
| - | - | - | - | - | - | - | - | - |
|`ab cd ae|ajikk|FG ;|m>?|6@>?;A>?9=|o|d|m>?||
uvtsr



|		
|
||
"! #|''
 
#()|
| - | - | - | - | - |
|6@x6;w7:  |?=;|m>?|||
|q\*+4@p./;x6w7:|-9q.nqx.:o|l2m3>?|||
|>?@x;6xw7=x7:;:|9x:o|FG ;|KLMM||
N	
 O



|'	
 
( RO|
|U	V(
|' 
!))|		
|	!(# (|N	
#Y
)) '
 #||'' 	
#(|
| - | - | - | - | - | - | - | - | - |
|`ab cd ae|ajikk|FG ;|m>?|6@x6;w7:|o|d|m>?||
srvvt s

		
 
  
"! # ''
 
#( ) ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbMAAABWCAYAAABFE3gCAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAxxJREFUeJzt3b9qZGUcxvHn5EzYP+BqvICgoEUIm0JLb8BKhLUWi72A3Zux8A5yC5aWYp0UggTBYKEwKRZ1d3Nem0mcXTQ4w5CX3+znAy+TOWkeCMw3kxOYBACKG5YeP07ysOMWoI6/ktzpPYI33jzJt0mezRYXhiSfJfk8ya9JWqdhQA0XSd7uPYKNafnnzU0lPyf5Lsmzqws7SZ4k+XLxNQCUIVwAlCdmAJQnZgCUJ2YAlCdmAJQnZgCUJ2YAlCdmAJQnZgCUJ2YAlCdmAJQnZgCUJ2YAlCdmAJQnZgCUJ2YAlCdmAJT3esza4gD8l2Fx2A6Vf57Xu5dj9meS57e/BSjmoySf9h7BxnyY5IveI9YwS/JVkreSV2N2N8md1C00cDs+SfKo9wg25mGSx71HrGE3ydMkDxL3zADYAmIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHmz3gMA6GsYhmGaprH3jlUcHh6OJycn18+XY3a5OAA3uUzysvcINuby4ODg/bOzs296D1nF8fHxdHR09Ly1luTVmI2LA3CTMf6qs03Gvb2995J80HvIKmaz2R/DMPx0FTP3zAAoT8wAKE/MAChPzAAoT8wAKE/MAChPzAAoT8wAKE/MAChPzAAoT8wAKE/MAChPzAAoT8wAKE/MAChPzAAoT8wAKM+nxQK84aZpmpK86L3j/2qtpbX2cvnacsymxQG4ideK7dJOT0+/H8fxae8hqzg/P99trX199fzf3pm1W9wD1OR1Ynu0+Xx+sb+//0PvISu6l6Vfqpbvme0sznDbi4BSdpKMvUewMTtJdnuPWNP1bv8AAkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAeWIGQHliBkB5YgZAebPFY0vye5J5fBw6cLPfkvzSewQbc5HkrPeINUxJfkzy4vVv3E9y79bnANXcT/Kg9wg25m6Sd3qPWMOQ5N0kY+8hAAAAJMnfe7ld/Bp1knYAAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAATAAAABWCAYAAABIIcoAAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAArZJREFUeJzt3a9vJHUcxvFn9rZ34iAhwRF6wWCowFCQRzhVhWjKn7ACja9A4vs3UFWH7bqapjSXNFB1BZITVNKGEHbmi2D5EUQphtlP9vUyk8xWPOKb97YjpglAUd3yOknyLMmbI25hvbXltbvzp+D3s3KW5Pn0bzefJtkeZw+kX14fjLqCClqSn5M8H3sIAAAAAAAAAAAAAAAAAAAAAAAAAAAArJcu3oXP/f15XiYjD4EkeZLkkziP3M+zJO8nDgyr4e0ks/iHHtzPx0k+TAQMKEzAgLIEDChLwICyBAwoS8CAsgQMKEvAgLIEDChLwICyBAwoS8CAsgQMKEvAgLIEDChLwICyBAwoa5oke3t7D46Ojj5P8sHIe1hDrbVJa+2dyWTyVXyp8i/6vn+9tfZlsgzY1tZWt729/W5r7aNxp7GOLi8vfzw+Pn48m82eTiaTjbH3sNoODw9/ODs7WyTLgO3v7+fq6mrUUayv+XzeXVxcPNzd3c3Ghn5xt5OTk+n5+fl0GAa/rgN1CRhQloABZQkYUJaAAWUJGFCWgAFlCRhQloABZQkYUJaAAWUJGFCWgAFlCRhQloABZQkYUJaAAWVNk2Q+n2dzc7O11trYg1g/fd+n7/ssFot0XecMcqdhGNowDEmWATs4OGinp6cvWmtfj7qMtXR7e9vf3Ny8tbOz813Xdf4q4E7X19ePkgxJ0i3vdUk2k7wy1ijW2ntJZkk+TbIYeQur77Mk3yT5Yrq80ZJ8n7+CBv+nN5L8kuTbJL+OvIXVd5vl8/vpPz7w/IExtTiD/AeeNwBlCRhQloABZQkYUJaAAWUJGFCWgAFlCRhQloABZQkYUJaAAWUJGFCWgAFlCRhQloABZQkYUJaAsQp+SvIiXmbI/bxMcj32CPjDwySvjT2CMl5N8njsEQAA6+k3DOSAF5/5nuUAAAAASUVORK5CYII=)6@>;7wG8x=:<7@;G;= G:  ?=; m>?

E0+4@p4/.E/;1>7wG8x=:<7@;G;= EG.: -9q.nqx.:o l2m3>?

\>?@>;<wG=:<7;6789: 7C== FG ; KLMM

N	
 O



|'	
 
( RO|
|U	V(
|' 
!))|		
|	!(# (|N	
#Y
)) '
 #||' '
 
#(|
| - | - | - | - | - | - | - | - | - |
|`ab cd ae|ajikk|FG ;|m>?|6@>;7wG8x=<:7@;G;=||d|m>?||
vs¢s ¡



|		
|||
|||
"! #|''
 #( )|
| - | :- | :- | - | :- | - | - | - |
|?G ¤7>7@=:G9x7:=6|x?=; ||9x:o||m>?|||
|\*43/+q6@E47+x\*G1+/?;@G7=67|zy|{}~{|||31?=/;||l2m3>?|||
|6@;x?7@9x67¤|G¦7; ||?=;||m>?|||
|01>?/£¤;<.2>=-3,;4?89:@/qx3?=13?=1;|||\*/6+79;-|£¤qx ¨no§©ª|l2m3>?|||
|;=7?89:6@x=;|||;76=x=|¤7|m>?|||
|\*4,16@Gx@=/;8=7<x;|£¤+7 3?1=||31?=/;||l2m3>?|||
|76@x=7<x;|¤7 ?=||;76=x=|¤7|FG ;|KLMM||
|3?GE £¤+7+E+-40q/2>4.@79G.:@:7<x;|£¤+7 3?=1||-9q.nqx.:o||DFGE /;|HKIJLMM||
|68;>?@<8x;679;|w7||?=;||FG ;|KLMM||
\>?;<

N	
 O



|'	
 
( RO||
|U	V(
|' 
!))|		
||	!(# (|N	
#Y
))|'
 #||''
 
#(|
| - | :- | - | - | - | - | :- | - | - | - | - | - |
||||||?G ¤7>7@=x:?=G9x7:=6;||o|d||m>?||
|`ab cd ae||ajikk|FG ;|m>?|\*43/+qE6@E47+x\*G1+/?;@G7=67||no|^d||l2m3>?||
||||||6@;x?7@9x67|¤;G¦7|o|d||m>?||
|-;@?G3,EG?89:@¥4/¦;|£¤+7 E.:+-2>4.@79G:|ajfgik[hkh|DFGE /;|l2m3>?|3?GE £¤+7+E+-40q/2>4.@79G.:@:7<x;|£¤+7 3?=1|no|^d||DFGE /;||
|6C?89:@;;|@G7=67|ajikk|m>?|m>?|6@7xG?;@G7=67||o|d||m>?||
|\*B3C?8:@9x647//;;|£¤/;G¥¦7E+|ajfgik[hkh|l2m3>?|l2m3>?|\*4q6@3++q/;4\*xq+?-7@9x67|£¤/;G¥¦7E+|no|^d||l2m3>?||
|;8G=@<7x;A¬?89:@;|¤7 ?=|ajikk|m>?|m>?|6@Gx@=;8=7<x;|¤7 ?=|o|d||m>?||
|3;@69w7«4/5A¬?89:@;||ajfgik[hkh|l2m3>?|l2m3>? qp+2,340q,/,|4\*+/68;>?@<-8xw/;679;7||no|^d||DFGE /;||
7:

7@7x:G?;;

7@:x?7;

7x:

\>?@78;<:8x;


`` 		





| |$%& |)\*|&6784\*9.435\*.45 |==  ? 54>9|
| - | - | - | - | - |
|DCE FG HH	GJI
EK<br>LMI.  VWXYZ[ ]XY\|\_MC|bc\_|||
|^\_d FGHCMIcEG.deMfdaeC@ @	
. NVOWQPXYRZ[ST]PXQUY\|KLmnoepIg
lBk
jih|`ba^c\_|||
|DM
IGEKfMGde|DM
IGMC FG|bc\_|{}|}~bE ~  ||
``6 



<table><tr><th colspan="1">) =.>6 .6 </th><th colspan="1">$%& </th><th colspan="1">>* </th><th colspan="1">=&?7 5</th><th colspan="1"> </th><th colspan="1">645>794></th><th colspan="1">5 4? 9 =  9</th><th colspan="1">)*</th><th colspan="1">= =   54>9</th></tr>
<tr><td colspan="1" rowspan="2">} }</td><td colspan="1" rowspan="2">}~~</td><td colspan="1" rowspan="2">d C</td><td colspan="1" rowspan="2">bc_</td><td colspan="1">DCE FG HH	GJI
EK<br>LMI </td><td colspan="1">o</td><td colspan="1"></td><td colspan="1">bc_</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">^_d FGHCMIcEGdeMfdaeC@ @	
</td><td colspan="1">jo</td><td colspan="1">y</td><td colspan="1">`ba^c_</td></tr>
<tr><td colspan="1">DCEf FE dHG	e</td><td colspan="1">}~~</td><td colspan="1">bc_</td><td colspan="1">bc_</td><td colspan="1">_d FGHCMIcEGdeMfdeC 
</td><td colspan="1">o</td><td colspan="1"></td><td colspan="1">bc_</td><td colspan="1"></td></tr>
</table>
- 	

  



| ||$%& |)\*|&6784\*9.435\*.45 |==  ? 54>9|
| - | :- | - | - | - | - |
|DCE FG HH	GJI
.<br>` `VWXYZ[ ]XY\||\_MC|bc\_|||
|KcDaAGE FG	HH	GJI
<br>||KLmnoepIg
lBk
jih|`ba^c\_|||
|`	`HdGME FG HH	GJI
||KLmeI
lnp |d C|¡ ¢||
|C¤E @f£ FG	HH	GJI
<br>||MGM|`ba^c\_|||
|MG§¨E FG HH	GJI
||KLmnoepI
l |d C|¡ ¢||
``6 



|) =.>6 .6 |$%& |>\* |=&?7 5| |645>794>|5 4? 9 =  9|)\*|= =   54>9|
| - | - | - | - | - | - | - | - | - |
|} }|}~~|d C|bc\_|DCE FG HH	GJI
|o||bc\_||
|Edª© FG	HH	GJI
EK<br>cDGaA|ust}~~|d @C|`ba^c\_|KcDaAGE FG	HH	GJI
<br>|jo|y|`ba^c\_||
- 	

  «




| |||$%& |)\*|&6784\*9.435\*.45 |==  4>9 ? 5|
| - | :- | :- | - | - | - | - |
|DCE FG HH	GJI
EK<br>LMI. |VWXYZ[ ]XY\||\_MC|bc\_|||
|D@CAEKGHMlc\_eICa^g	
@a^|||\_M@C^|`ba^c\_|||
|\_d FGHCMIcEGdeMfdeC 
|EGG¨DMCGde||KLmnoepI
l |bc\_|||
|Kc\_a^MG^\_dE FG	HH	GJI
<br>|||MGM|`ba^c\_|||
|DM
IGEGcC\_l|||DM
IGMC FG|bc\_|{}|}~bE ~  ||
``6 



|) =.>6 .6 |$%& |>\* |=&?7 5| |645>794>|5 4? 9 =  9|)\*|= =   54>9|
| - | - | - | - | - | - | - | - | - |
|} }|}~~|d C|bc\_|DCE FG HH	GJI
EK<br>LMI |o||bc\_||
|D@CAE FKEKc\_la^g|ust}~~|`ba^c\_|`ba^c\_|D@CAEKGHMlc\_eICa^g	
@a^|jo|y|`ba^c\_||
|DCE FK EdHG	e|}~~|bc\_|bc\_|\_d FGHCMIcEGEdeMfdGeCG¨DMC	G
de|o||bc\_||
®¬­

	¯¬°¬



| |$%& |)\*|&6784\*9.435\*.45 |==  54>9 ? |
| - | - | - | - | - |
|DCGEdlE\_IKCIKGH
ED
  c\_G. VWXYZ[ ]XY\|\_MC|bc\_|||
|¤\_lGIdEKCIK @fG£H^@Eg

	 ADac^\_G|KLm²opeIg
lB
kj±jh|`ba^c\_|||
|DGHMG	c\_¨ECdKl\_ICe  EKIK
GH	|MGM|d C|¡ ¢||
``6 



|) =.>6 .6 |$%& |>\* |=&?7 5| |645>794>|5 4? 9 =  9|)\*|==   54>9|
| - | - | - | - | - | - | - | - | - |
|} }|}~~|d C|bc\_|DCGEdlE\_IKCIKGH
EDc\_G
 |||bc\_||

``	






|"! |&"('|+ , ;6,89:06(|,07"56 |??" 6@A"7;!|
| - | - | - | - | - |
|DCEFGCHEJKIKMLNMOCIF0 
<br>`
 `XY[Z \] ^\_Z[|FCO|a
MF|||
|CN<br>EbcFGCHE JKIKMLNMOCIF	
<br>
 |mlnLokIGjLI	deghi	f|`a
M F|||
|`
`M	DLH	LMEscMsu
tL
	|FCO|wJ C|{}|||
|BDHK~Cd	LMOCjF
EFG CHJI  EKIKMLN	
|OHO|vwJ C|x{}|yz||
8!"



|+ ?0@8!" 80" |&"('|@!",|?( A"7A9|"! |9@8;67@6! |"76A ";! ?" ;!|+ ,|? ?" 6@"7;!|
| - | - | - | - | - | - | - | - | - |
|||wJ C|a
MF|DCEFGCHEJKIKMLNMOCIF 
<br>
|n||a
MF||
`` 



|"! |&"('|+ , ;6,89:06(|,07"56 |??" 6@A"7;!|
| - | - | - | - | - |
|DCEFGCHEJIHDOJH0  XY[Z\] ^\_Z[|FCO|a
MF|||
|CN<br>EbcFGCHE JIHDOJHB|mlnLokIGjLI	deghi	f|`a
M F|||
|M
jDHEFGCHEJIHDOJ  H|¢nolLkIGjLI		|wJ C|{}|||
8!"



|+ ?0@8!" 80" |&"('|@!",|?( A"7A9|"! |9@8;67@6! |"76A ";! ?" ;!|+ ,|? ?" 6@"7;!|
| - | - | - | - | - | - | - | - | - |
|||wJ C|a
MF|DCEFGCHEJIHDOJH |n||a
MF||
|¤JEFGC£HEJIH DOJHEB Md
jDBH||vwJ C|`a
M F|Md
jDBHEFGCH EJIHDOJHB|hn||vwJ C||

¦
¥

"!  &"(' + , ;6,89:06(707,"56  ??" 6@A"7;! DCMOCEIFMjFC0u![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZoAAABCCAYAAABuHBCVAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAqtJREFUeJzt3UFrI3Ucx+HvPy3KrAGhvdg9KuxFwUtPHi2UIp7qQfYNefSwlx6W3gTtK+ir8CV41h4MorBY0mS8ZHG6FgW3v0yTPg8MMwlz+IVM5pMJgUkAoFAbrE+TfJ7kz/HGAVYWq/XOqFOwbq+SPBl7iHvQJ/khyY9J+snqyZbk4yQf5u/4AMBb2x1s/57k+yTfJVmOMw4A22by37sAwP8nNACUEhoASgkNAKWEBoBSQgNAKaEBoJTQAFBKaAAoJTQAlBIaAEoJDQClhAaAUkIDQCmhAaCU0ABQSmgAKCU08PC0wcLjsU3vecugL8PQLJLcrH0c4E3vJvkmyftjD8LaTJJ8neSrbP4FQEtykuSz1fatF7STZHeEoYDb3klynKQbexDWpiX5NMkn2fyrmpbkWZKPckdoAODeCQ0ApYQGgFJCA0ApoQGglNAAUEpoACglNACUEhoASgkNAKWEBoBSQgNAKaEBoJTQAFBKaAAoJTQAlBIaAEoJDQClhrduXq4WYHw3Yw/A2i339/e7y8vLpwcHBxt7Lp7NZu34+Hh6dXU1e/3c7h379WucCfgnn8FHaDKZ5OTk5Iu9vb0vF4vFxh4D0+k0p6en87Ozs2+zOpaHoZmslhYHOoyp5e4vgWy3na7r9ltrH/T95p6CW2t913U/Z9ATBzPAA9H3fVtttn/d8eG7Nb8/AwBQSmgAKCU0AJQSGgBKCQ0ApYQGgFJCA0ApoQGglNAAUEpoACglNACUEhoASgkNAKWEBoBSQgNAKfejAXgg5vP5H33fz7PhN5+8vr5+NXz8OjR9kl+T/JYNf4GwBW6S/LRa8zj0y+Xyl4uLixeHh4cvj46ONvo8fH5+/jzJLHf05EmSbu0TAW+aJNmLn7Yfm2mS98Ye4p7oCQAAsCX+AhCRWJ/mCK8OAAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUoAAABCCAYAAAAxDZ/cAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAlhJREFUeJzt3bFqHFcYhuFvtLtGCwEjgwXpBILEIAJ2HzD4HlS6EipC0gmEwYVU6h7cqskVpBa6ANVGXVIEFoxUCJYZpBkXu5KMDT52GNiseB5YDgNTnOLwzgxb/AkAX1XN10GSN0meLHAv8F+083VlobvgIXqf5F2SzuECAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMpv5tJL72U/Ql9uzlcRAJpZXleTnJG+TjBa8Fx6ejSS/Z/4QFkqW2dMkrzKbIgp9epLk1wglwLcRSoACoQQoEEqAAqEEKBBKgAKhBCgQSoACoQQoEEqAAqEEKBBKgAKhBCgQSoACoQQoEEqAAqEEKBjO19v5EMLJsqgyO78r89U4CPo0/OJie3t75fT09M+qqn5czJ7g+7Rtm6Zpbq6urn5aW1v7azAYGDBGb+q6Hl5cXPzddV2SeSi3trYGOzs7v2Q2UAeWQXd2dvbh+Pj4h6Ojo+ej0cgbJb05Pz+f7u3t/dM0TZL7N8qMx+NRkkeL3Bx8h24ymYzG4/Gjzc3NrK6uCiW9mU6nN1VV3Q2tu/sOrypfLiyPtm2rqqry6Q/68vl58ucNQIFQAhQIJUCBUAIUCCVAgVACFAglQIFQAhQIJUCBUAIUCCVAgVACFAglQIFQAhQIJUDBMEkmk0m7sbHxb5JB4X74v6iaprmq63q167oP8dCnR9fX112S7vZ6mCQnJydtkpfr6+sOG0tjf3//xeXl5W8HBwd/HB4eNoveDw/H7u7us7quX+eTWMKyGiVZy2wiI/RplOTxojcBAMBD8RFiImW4bxgd8wAAAABJRU5ErkJggg==)
OC
 \XY[Z ^\_Z[] FCO a
MF

NCM
OCEIbcFMjFC uOC
dr
  mlnLokIGjLI	deghi	f `a
M F

8!"



|+ ?0@8!" 80" |&"('|@!",|?( A"7A9|"! |9@8;67@6! |"76A ";! ?" ;!|+ ,|?? 6@"7;!"|
| - | - | - | - | - | - | - | - | - |
|||wJ C|a
MF|DCMOCEIFMjFCu
OC
|n||a
MF||
- ¦¦	



|"! |&"('|+ , ;6,89:06(|07,"56 |??" 6@"7;! A|
| - | - | - | - | - |
|DCM
EuMOCFjF
0 XY[Z \] [^\_Z|FCO|a
MF|||
| FJ§©HM	L
EHF HFIª~C ¨  O00QPXYRS[Z T\]U^\_ZRVS[W|¬nolLkIGjLI	de«hi	f|`a
M F|||
|DOHIEDHNOEJKMJj<br>j 
 MOCIF
|DOHI|a
MF|||
|BDOHIFCEKJMuOCjI jr
d
  F|BDOHI|vwJ C|x{}|yz||
8!"



<table><tr><th colspan="1">+ ?0@8!" 80" </th><th colspan="1">&"('</th><th colspan="1">@!",</th><th colspan="1">?( A"7A9</th><th colspan="1">"! </th><th colspan="1">9@8;67@6! </th><th colspan="1">"76A ";! ?" ;!</th><th colspan="1">+ ,</th><th colspan="1">? ?" 6@"7;!</th></tr>
<tr><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2">wJ C</td><td colspan="1" rowspan="2">a
MF</td><td colspan="1" valign="bottom">DCM
EuMOCFjF
</td><td colspan="1">n</td><td colspan="1"></td><td colspan="1" valign="bottom">a
MF</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1"> FJ§©HM	L
EHF HFIOª~C ¨ </td><td colspan="1">hn</td><td colspan="1"></td><td colspan="1">`a
M F</td></tr>
<tr><td colspan="1">DCEKMJ
jHjELHFH	C FIOª </td><td colspan="1"></td><td colspan="1">a
MF</td><td colspan="1">a
MF</td><td colspan="1">FJ ©HM	L
EHFHFIOªC </td><td colspan="1">n</td><td colspan="1"></td><td colspan="1">a
MF</td><td colspan="1"></td></tr>
</table>
	¦¦
­	




|"! |&"('|+ , ;6,89:06(|,07"56 |??" 6@A"7;!||
| - | - | - | - | - | :- |
|DCMEj
FGHLMOCIF0	
 XY[Z \] ^\_Z[|FCO|a
MF||||
|||||||
|		
|
||
"! #|''
 
#()|
| - | - | - | - | - |
|\*6+7+-.451,8-@2>93A?97:2=0<+>7/1;=.: KBCJMLDE FNGODHEMPLIQ|U]^\_VSTRWY\5.ZA5.A[:X:|\*`a6/;|||
`	`b
 c



<table><tr><th colspan="1">'	

(  cf</th><th colspan="1">
</th><th colspan="1">i	j
(</th><th colspan="1">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">`	`b
#m
)) '
 #</th><th colspan="1"></th><th colspan="1">''
 
#(</th></tr>
<tr><td colspan="1" rowspan="2">utv wx uy</td><td colspan="1" rowspan="2">}~u</td><td colspan="1" rowspan="2">7 ></td><td colspan="1" rowspan="2">a6;</td><td colspan="1">><@6ZY6;9;:A>=</td><td colspan="1">^</td><td colspan="1">x</td><td colspan="1">a6;</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">*6+7+-.451,8-@2>93A?97:2=0<+>7/1;=.:</td><td colspan="1">W^</td><td colspan="1">rx</td><td colspan="1">*`a6/;</td></tr>
<tr><td colspan="1">><Z<7@9:</td><td colspan="1">}~u</td><td colspan="1">a6;</td><td colspan="1">a6;</td><td colspan="1">67 8@>9A?97:=<>7;=:</td><td colspan="1">^</td><td colspan="1">x</td><td colspan="1">a6;</td><td colspan="1"></td></tr>
</table>
`` 



|		
||||
|||
"! #|'' 	
#()|
| - | :- | :- | :- | - | :- | - | - | - |
|67 89<@;69:;?|<A 8>A@>6=>:=|KJML NOMPLQ||]^\_Y\ZAA[::||a6;|||
|,8\*6/;||||UST^RY\\_5W.ZA5.A[::WX||\*`a6/;|||
|896;:||||^Y\\_ZAA[::||a6;|||
|3T\*0/-469-/<@9;?.[1=@:|3\*-6/9;?2>|||UST^\_RY\5W.ZA5.A[::X||+7 2>|¡¡  ||
|98A9<@;?@:6>9;>|?|||\_]]Y\ZAA[::||7 >|¡¡ ||
|/\*0425105S-1-5A19<=A6<;@9AZ>==|S-R2YZ9.>-9:|||-5A19=||+7 2>|¡¡  ||
|``9@:9@6@=;>9<AZ?£>|@|||9==||7 >|¡¡ ||
|\*6+7+-.451,8-@2>93A?97:2=0<+>7/1;=.:||||U]^\_VSTRWY\5.ZA5.A[:X:||\*`a6/;|||
|A9<=6A@>@A6Z9||||A9=||7 >|¡¡ ||
|+0245\*S->97<6A@3?>@A6Z9||||UST^RY\\_5W.ZA5.A[::WX||+7 2>|¡¡  ||
|``A¥@<6A@>@A6Z9||||]^\_Y\ZAA[::||7 >|¡¡ ||
|/\*532=96>A2;\*65A1?>=||||U]^\_VSTRWY\5.ZA5.A[:X:||+7 2>|¡¡  ||
|@99||||967 8\ ªw¬­Aª«@6ª>Z7?|\_9ªx:7=8ª>«6ª6>|7 >|¡¡ ||
|-0534--415A/9@.:@9<@;9A=?||||-91==||+7 2>|¡¡  ||
|?9Y?>||||^Y\\_ZAA[::||7 >|¡¡ ||
|SZ/05;3-941</@;A=?||||UST^\_RY\5W.ZA5.A[::X||+7 2>|¡¡  ||
|``69<9;?@[=69:;|9?|||^\_Y\ZAA[::||7 >|¡¡ ||
|-9,85A3\*-4/0<@3.?-69:2>;?||||U\_S]T]RY\5V.ZA5.A[V::X||+7 2>|¡¡  ||
|>?<Z[98;;=:;[|6>|||\_]]Y\ZAA[::||7 >|¡¡ ||
`	`b
 c



|'	

(  cf|
|i	j
(|' 
!))|		
|	!(# (|`	`b
#m
)) '
 #||''
 
#(|
| - | - | - | - | - | - | - | - | - |
|utv wx uy|}~u|7 >|a6;|67 89<@;<A69:;? 8>A@>6=>:=|^|x|a6;||
|0\*+7¯,®<3\*-@4/<067689:;?.- +-.4512@3>A20?9+7:=/1<>7;=.-9:|z}{~o|u||+7 2>|\*`a6/;|\*6+7+-.451,8-@2>93A?97:2=0<+>7/1;=.:|W^|rx|\*`a6/;||
``



|		
|
||
"! #|''
 
#()|
| - | - | - | - | - |
|67 8@>9A?97:=<>7;=: KJML NOMPLQ|]^\_Y\ZAA[::|a6;|||
|521A9=.>20:1<= ,8-9 \*61= KBCJMLNFDE GODHEMPLIQ|\*16=2>|\*`a6/;|||
|A9<=@>9:= KJML NOMPLQ|A>9== 89|a6;|||
|5AS-.Z-Z99: \|¤2\*1UX¥>6=\_2>1=|\*`a6/;|||
`	`b
 c



<table><tr><th colspan="1">'	

(  cf</th><th colspan="1">
</th><th colspan="1">i	j
(</th><th colspan="1">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">`	`b
#m
)) '
 #</th><th colspan="1"></th><th colspan="1">' '
 
#(</th></tr>
<tr><td colspan="1" rowspan="3">utv wx uy</td><td colspan="1" rowspan="3">}~u</td><td colspan="1" rowspan="3">7 ></td><td colspan="1" rowspan="3">a6;</td><td colspan="1" valign="top">67 8@>9A?97:=<>7;=:</td><td colspan="1" valign="top">^</td><td colspan="1" valign="top">x</td><td colspan="1" valign="top">a6;</td><td colspan="1" rowspan="3"></td></tr>
<tr><td colspan="1">521A9=.>20:1<= ,8-9 *61=</td><td colspan="1">W^</td><td colspan="1">rx</td><td colspan="1">*`a6/;</td></tr>
<tr><td colspan="1">A9<=@>9:=</td><td colspan="1">^</td><td colspan="1">x</td><td colspan="1">a6;</td></tr>
<tr><td colspan="1">0\.12>9.A=-0<>41:<@=2>9:= ,8-9 *61=</td><td colspan="1">z}{~o|u|</td><td colspan="1">*`a6/;</td><td colspan="1">*`a6/; -</td><td colspan="1">521A9=.>20:1<= ,8-9 *61=</td><td colspan="1">W^</td><td colspan="1">rx</td><td colspan="1">*`a6/;</td><td colspan="1"></td></tr>
</table>
°

		
 
  
"! # ''
 
#()![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZIAAABACAYAAAAwA/FqAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAArRJREFUeJzt3bFqW2cYx+H/d2ScxItDi4dAC54LQWt6JxkKWTp07dTVEDx3DfgCMgVyDVl6A8FDh44eAqlriF3cKDoZJBPVQ0L7Wj629DwgJD5peMWB78eRhq9lpkvyNMmTJH8FYLn6zPaa+0nawLNch+n8uRt0iqvzR5Ifk7xJPl3ALsnPSSZJXmR2kQGWqc96RGQVvU/yNsmHJNlYeONDkuMkR/lUTwD4rFW5zQJgIEICQImQAFAiJACUCAkAJUICQImQAFAiJACUCAkAJUICQImQAFAiJACUCAkAJUICQImQAFAiJACUCAkwFKcjrojFkEwzOyXRMbvAsn2V5JusR0y6JN8luTv0IFdklORhks2Lhct3JC3rcWGBYX2f5Iehh7gmoyS/JnmQ1dhfuyTPkny9uLD42k9dwLK1JBtJ7mQ1NtYvaUnuZRaUVdAl2Zo/t4sFAPjfhASAEiEBoERIACgREgBKhASAEiEBoERIACgREgBKhASAEiEBoERIACgREgBKhASAEiEBoERIACgREgBKhASAko2F1+fzRz/QLMD6+CfJ2dBDXJednZ3u4ODgl/F4/G7oWarOzs7aeDw+mUwm04u1xZDcSbJ5/WMBa2gzs3PM18LW1ta329vbj5K01m73MfWj0Wjadd3vWfhFa+PSZ9r84a4EWLbbvaP+B22u7/tb/3dCa62/HMNb/6UAGJaQAFAiJACUCAkAJUICQImQAFAiJACUCAkAJUICQImQAFAiJACUCAkAJUICQImQAFAiJACUXD6PBIAr1s+11qZf/vTNNv8q/1oTEoAlOzk5OT46Onq+u7v759CzVJ2fn48mk8njxbWLkPRJXiZ5H6cjAsvVJ/ktyWHWY7+ZnJ6e/rS/v/96b2/v76GHqTo8PGxd172aTqdvsx7XDwAAuNE+AkRYaCbovL2lAAAAAElFTkSuQmCC)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALcAAABACAYAAABY+eY+AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAYBJREFUeJzt3DGKFEEUx+F/twMGsp7ACQeMBG9gtsfQyANoMOgNDOYkcwsDA9ELGAxqYiDeQOoZOCPugsm6MPTj+5KiqpMX/IJKqqf89izJiyRzYLkqyeckz5N8m46HF0nun2siuEU/k/w4rgAAAAAAAAAAAAAAAMC/nF6XeTNJK1OSR0nunnsQuG1zkvdJHpw20Mm9HLsWN22Jm7bETVvipi1x05a4aUvctCVu2hI3bYmbtsRNW+KmLXHTlrhpS9y0JW7aWh3X6doKS3Sl31PcD5M8vv4RFmbKXw2vkmS73T4ZY7xKcudcU8H/qqra7XZfqmokx8oPh8PLJG/iDs6CVVVtNptPY4zLJF//3LnneU5VuZawdP5bQn/ipi1x05a4aUvctCVu2hI3bYmbtsRNW+KmLXHTlrhpS9y0JW7aEjdtrZJknucaY9Q0TePcA8FNVdWV/Snut2OM1/GGkgUbY0xV9fS0XyXJer3+kOTjfr8XN4tWVe+SfD/3HADcxC9alkS9xQrHmQAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJ8AAABACAYAAAAEc6UaAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAYxJREFUeJzt2yGuE1EcRvHzn0dCQIGoQGFB4/AYEmxtFUkXwwIqazCtYwfVRaPeCgi468ibi+g8UhbQfknn/Mw0VZ84aWeSO8XJHfAF+IB0OR34BXwG7mv6soCXwLPUKs3GA/Ab+JMeIkmSJEmSJEmSJEmSJEmSJGlGHk/PMyRXaHYKeAs8Tw/R/BTwDXgD/vLpuorTS2oDGJ+CjE8xxqcY41OM8SnG+BRjfIoxPsUYn2KMTzHGpxjjU4zxKcb4FGN8ijE+xTw5+1ycna+XLuC/vh7jG4D3wOurz9GcFPB0up7i2+12dTweV8DH4DDduN5732w29621B5jiWywWtV6vX1TVq+w83bJxHNlutz9ba3dwds9XVVSV93y6mGEY+nljPu0qxvgUY3yKMT7FGJ9ijE8xxqcY41OM8SnG+BRjfIoxPsUYn2KMTzHGp5jzdzg6MKaG6Pb13uHUGTDFdzgcxtVq9RX4HtqlGRjHsVprn5gC/HeqtPde+/3ev2Fd1HK5fAf8AFp6iyRd31/Q3z6pH/yJMAAAAABJRU5ErkJggg==)

789:6 ;< >@8=?7A KJML NOMPLQ @6= S@: 8A3>U0+7.=8,?7<6@?\*6<:= KBCJMLDE FNGODHEMPLIQ 4@1\*6= RS@4:.

V	
 W



<table><tr><th colspan="1">'	
 
( ZW</th><th colspan="1" valign="bottom">
</th><th colspan="1">]	^(
</th><th colspan="1" valign="bottom">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">V	
#a
)) '
 #</th><th colspan="1"></th><th colspan="1">' '
 
#(</th></tr>
<tr><td colspan="1" rowspan="2">hij kl mi</td><td colspan="1" rowspan="2">qirs</td><td colspan="1" rowspan="2">uA 6</td><td colspan="1" rowspan="2">S@:</td><td colspan="1">789:6 ;< >@8=?7A</td><td colspan="1">w</td><td colspan="1">l</td><td colspan="1">S@:</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">T318>AU0+7.=8,?7<6@?*6<:=</td><td colspan="1">vw</td><td colspan="1">fl</td><td colspan="1">RS@4:.</td></tr>
<tr><td colspan="1">}8U<?=7:8{<|6</td><td colspan="1">qirs</td><td colspan="1">S@:</td><td colspan="1">S@:</td><td colspan="1">8>AU7=8?7<6@?6<:=</td><td colspan="1">w</td><td colspan="1">l</td><td colspan="1">S@:</td><td colspan="1"></td></tr>
</table>
~~



|		
||
|||
"! #|''
 
#( )|
| - | :- | - | :- | - | - | - |
|8>AU7=8?7<6@?6<:|=KJML NOMPLQ|@6=||S@:|||
|,21T33\*60>.82AU>,=8?<?<:|UT+4716@\*=|2x1>={1=||RS@4:.|||
|=> ;>||U9?|U?||uA 6|||
|>25Az}3? /;2>||2x1>={1=||tu5A \*6|||
|@A;>@:U8>U6=}:===?|U8=}>|U9?|wU w?||uA 6|||
|4@5A/;2>5+212,T1-3>8,9U8A=>:.=?73|TU41\*6@=|U9?yT-|U3T3?wv||RS@4:.|||
|@: ; >8<?U>}||@6=||uA 6|||
|zT51+7U8A,}=8=?\*6<=?<:||4@1\*6=||RS@4:.|||
|7U>A8=:}|;6@:6}|7U6>=|;>|uA 6|||
|2,3+4+\*72U3T,>18@=7>>?8?6|/;.: +7¡¢\*6|\*+72UT16>1=|/;2>|tu5A \*6|||
V	
 W



|'	
 
( ZW|
|]	^(
|' 
!))|		
|	!(# (|V	
#a
)) '
 #||''
 
#(|
| - | - | - | - | - | - | - | - | - |
|hij kl mi|qirs|uA 6|S@:|8>AU7=8?7<6@?6<:=|w|l|S@:||
|T318>AU0+7=.8{?x,7<6?\*6<: T5+412\*1,4@18A>U=76@=|nqiorscpp|RS@4:.|RS@4:.|4@5A/;T2>5+412\*12,T1-3>8,9U8A=>U:.=?736@=|vw|fl|RS@4:.||
|8}UA=¤8?=<?¢<:|qirs|S@:|S@:|7U8A}=8=?6<=?<:|w|l|S@:||
~¥¦~

		
 
  
"! # ''
 
#() 78AU}:6><?>=68A6=![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZgAAABCCAYAAABq6cCoAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAuJJREFUeJzt3TFrJHUYx/HffxzvCi200UKQM5LDziNVCsUm5XWCryD4AoR01xypfQMBCxtrX4mVpLARkiaFCBZX3Hq7Y5HNMTlOD+58MruTzweGTTZbPA8L8yXT/BMAKNBGr12StyacBYDtt0yySjL06zdakkdJvk7yx1RTAaz9vX59e9IpbsbTJHenHuJ/9EOSn5KkH735JMmPSX5OMkwwFMCVq3tQ+89PzcMql0+Q5uLPrL+/cWCerf/wewQGgNfzvB/9qz4AAK9jTv+WAbBBBAaAEgIDQAmBAaCEwABQQmAAKCEwAJQQGABKCAwAJQQGgBICA0AJgQGghMAAUEJgACghMACUEBgASggMsIluw1HJyfz2vLbPODBPkyziNEtgWi3JgyTvTj3IDXg/yf3MJzQfJPkk633Ggbmb5E7msyiwnfokj5Pcy7zvRy3J50m+zTyeJrUkXyX5Ji8JDMCmmHNYxlrmteu1fQQGgBICA0AJgQGghMAAUEJgACghMACUEBgASggMACUEBoASAgNACYEBoITAAFBCYAAoITAAlBAYAEoIDAAl+qkHALjNuq5rh4eH3cHBwdSjvJHT09N2fHzclsvl8/fGgVmsr+GmBwN4wZMkq6mHuAHP9vf3Pz06Ovqu7/utvvfu7e3l4uJi5+Tk5LfV6vKrGwfmzvpqERlgWu/kdjzC73d2dr7suu7h1IO8qa7rsru7+1ff998vFoskHpEBTK0NwzCLmLbW2vj3WSwFwOYRGABKCAwAJQQGgBICA0AJgQGghMAAUEJgACghMACUEBgASggMACUEBoASAgNACYEBoITAAFDCeTAA0xpaa6sXjlLZOsMwpLV27bDKcWCGXB5R6jRLYErD6Jq71fn5+a/L5fKXruu2et9hGHJ2dvbR6uq85JGW5H6Se+ufAabSJfkiyXtTD1KsJfkwyYPM477bknyc5LP8yz5zWBLYfrflXjS3Pee2DwCb6B/nqWDVFEO/ygAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUsAAABCCAYAAADez/TiAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAkpJREFUeJzt3bFqHEccx/HfrHVIxAHrCtcGNwLjwpikUgh5DcWFa3UG12rzAnkMtQoCF25d6AFSp4pBEISalexox82dEbjwCEssu/58mmWOK/7F8L2bZjYB4KvK6nkvSXdtDUBSk1wlGTZWH7xM8iJiyfR8XD0Xo07BXP2X5HWSf9ZxXCbZjlgyPXX1tHe5C/8neZ/kw/UNZrMBfKl+/SsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfHfc8M9d+ry/ujGngG9UkjxL8uPYgzBLJcnz2F/MQJfkryRPxx6EWeqSvEnyZL2AKStxFOfuOIYD3IRYAjQQS4AGYgnQQCwBGoglQAOxBGgglgANxBKggVgCNBBLgAZiCdBALAEaiCVAA7EEaCCWAA02kmRra+txkkdxiSrTUi4vL+93XffTYrF4OPYwzE65uLhYrBcbSbK3t/f71dXVq/inybTUo6Ojf3d3d/9YLpebYw/D7NTDw8O/+74fklUsDw4ONpMsa61iyWQMw5CTk5Pz/f397Z2dna2x52Feaq31+Pj4h77v7yWrWCYppZSU4hTO9JRS0nV+57ldtdbEO3gAbkYsARqIJUADsQRoIJYADcQSoIFYAjQQS4AGYgnQQCwBGoglQAOxBGgglgANxBKggVgCNFjfZ1lLKcOok8ANre9frbXav9y6YRiSpK7XG0nSdd27Wuuf8Q4eJqTWmrOzs19OT0/f1lrPx56H+en7/udcCyZMVUnya5LtsQdhlkqS35I8GHkOuBVOQ9wl+wvgJj4BHDhhjdpY774AAAAASUVORK5CYII=) A?KJML NOMPLQ @6= S@:

\>U=1?+768,=\*6 /;2> 4@1=KBCJMLNFDE GODHEMPLIQ 4@1\*6= RS@4:.

V	
 W



<table><tr><th colspan="1">'	
 
( ZW</th><th colspan="1">
</th><th colspan="1">]	^(
</th><th colspan="1">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">V	
#a
)) '
 #</th><th colspan="1"></th><th colspan="1">' '
 
#(</th></tr>
<tr><td colspan="1" rowspan="2">hij kl mi</td><td colspan="1" rowspan="2">qirs</td><td colspan="1" rowspan="2">uA 6</td><td colspan="1" rowspan="2">S@:</td><td colspan="1">78AU}:6><A?>=68A?6=</td><td colspan="1">w</td><td colspan="1">l</td><td colspan="1">S@:</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">13T*U=>?+768,=*6 /;2> 4@1=</td><td colspan="1">vw</td><td colspan="1">fl</td><td colspan="1">RS@4:.</td></tr>
<tr><td colspan="1">>7U=8{?9=?68=>? ;> @=</td><td colspan="1">qirs</td><td colspan="1">S@:</td><td colspan="1">S@:</td><td colspan="1">U=>?768=6 ;> @=</td><td colspan="1">w</td><td colspan="1">l</td><td colspan="1">S@:</td><td colspan="1"></td></tr>
</table>
~¥§~



|		
|||
|||
"! #|''
 
#()|
| - | :- | :- | - | :- | - | - | - |
|@A;>U}>A=68A?:6=?|KJML NOMPLQ||U9?|U?w||S@:|||
|4\.1\*9U=6@¢+78,@6\*=:|KBCJMLNFDE GODHEMPLIQ||4@1\*6=||RS@4:.|||
|@7U:>9>8=6?<=|KJL MNOMPLQ||7U6>=|;>|S@:|||
|5A2>||41|4¨\*@\*61=@©6 =||RS@4:.|vw||
|7U>8A=>9=?|||7U6>=|;>|uA 6|||
:

V	
 W



<table><tr><th colspan="1">'	
 
( ZW</th><th colspan="1">
</th><th colspan="1">]	^(
</th><th colspan="1">' 
!))</th><th colspan="1">		
</th><th colspan="1">	!(# (</th><th colspan="1">V	
#a
)) '
 #</th><th colspan="1"></th><th colspan="1">''
 
#(</th></tr>
<tr><td colspan="1" rowspan="2" valign="top">hij kl mi</td><td colspan="1" rowspan="2" valign="top">qirs</td><td colspan="1" rowspan="2" valign="top">uA 6</td><td colspan="1" rowspan="2" valign="top">S@:</td><td colspan="1">@A;>U}>A=68A?:6=?</td><td colspan="1">w</td><td colspan="1">l</td><td colspan="1">S@:</td><td colspan="1" rowspan="1"></td></tr>
<tr><td colspan="4"></td></tr>
</table>


<table><tr><th colspan="1">``	
<br></th><th colspan="1"></th><th colspan="1">
!	 </th><th colspan="1">&)(('</th><th colspan="1">&

</th><th colspan="1">&)'	,
-,	</th><th colspan="1">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1">&'
,	-</th></tr>
<tr><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2"></td><td colspan="1" rowspan="2" valign="top">?</td><td colspan="1" valign="bottom">82<=ABC@;>345?@672;</td><td colspan="1" valign="bottom">DE</td><td colspan="1" valign="bottom">FG</td><td colspan="1" valign="bottom">HI?>65</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1"><C@M=NMBM?>O@;</td><td colspan="1">E</td><td colspan="1">G</td><td colspan="1">I?></td></tr>
<tr><td colspan="1">82<R3P=QK29J4N5M26;7B2?>CB@MNA;=;>?@S?;</td><td colspan="1">TXUYVZW[ W</td><td colspan="1">HI?>65</td><td colspan="1">HI?>65 65279:?</td><td colspan="1">82<=ABC@;>345?@672;</td><td colspan="1">DE</td><td colspan="1">FG</td><td colspan="1">HI?>65</td><td colspan="1"></td></tr>
</table>
ba\_`^ cbgfed



|&

|||! -|,!k'),j',!|&'
,	- (|
| - | :- | - | - | - | - |
|<=NMC;Bl mC?>@; wvyx z{}|xy||>@;|I?>|||
|5>~ ]mJM~347JKN67?=49:BCN@M=M@< C57:>@2;||QSKC9:KNB:DCNE|HI?>65|||
|M@=NMCBl mC?>@;||SCNBCN|I?>|||
|654K9\J32<JLM79K2?>O=NB@MCN;mBl 6527?>:C@;||PJ77@MR@|HI?>65|||
|<C@M=? m;?>;||<C@M@; mM|I?>|ZZ[IY=Y [ YG ||
|~74KJ9\<=34:2;@7C@@=NMCBl ]m:C?>76@52;||>@572;|HI?>65|||
|NMO?>M=NMCBl mC@;?>||@MR@|;|¡¡ ||
|4KLJ6753<:CJ@M=NMO?>M||J7273<:C@M@; ]mJM|~2;|¡¡  ||
|> mMN?=OMN?>>Ml =@CNC>;@||SCNBCNE|;|¡¡ ||
0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|ZGZ£|XYZ[|;|I?>|<=NMC;Bl mC?>@;|E|G|I?>||
|<R3P=4K9\NJM2;CBl ]m4=5:~327JM@C><; 7@|TXUYVZW[ W|HI?>65|HI?>65|5>~ ]mJM57:~3247JKN67?=49:BCN@M=M@C>@<;|DE|FG|HI?>65||
|<R=NM;CBl m= OMN?> >Ml|XYZ[|I?>|I?>|> mMN?=OMN?>>=M@CNCl>;@|E|G|;||
|¤4K9\J8A¥=NMCBl ]m4=~77:7@C@@|TXUYVZW[ W|HI?>65|HI?>65 :|~74KJ9\<=374:2;@7C@@=NMCBl ]m:C65?>27@;|DE|FG|HI?>65||
\_^\_g¦^



|&

||!|,!-k'),j',!|&
,	- ('|
| - | - | - | - | - |
|> mMN?=M>>¨CM>; @wvyx z{}|xy|SCNBCNE|I?>|||
|<=3B496?2; ]mLO4K5J3~7@M=NM>< nvoqwpyx rzs{}ut|pxqy|>@572;|HI?>65|||
|<C@M=C?>=B@;N=O S|<C@M@; mM|;|ZZ[IY=Y [ YG ||
0



<table><tr><th colspan="1">``	
<br></th><th colspan="1"></th><th colspan="1">
!	 </th><th colspan="1">&)(('</th><th colspan="1">&

</th><th colspan="1">&)'	,
-,	</th><th colspan="1">`
`(1,0-' 
-</th><th colspan="1">!</th><th colspan="1">& '
,	-</th></tr>
<tr><td colspan="1" rowspan="2">ZGZ£</td><td colspan="1" rowspan="2">XYZ[</td><td colspan="1" rowspan="2">;</td><td colspan="1" rowspan="2">I?></td><td colspan="1">> mMN?=M>>¨CM>@;</td><td colspan="1">E</td><td colspan="1">G</td><td colspan="1">I?></td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1" valign="bottom"><=3B496?2; ]mLO4K5J3~7@M=NM><</td><td colspan="1" valign="bottom">DE</td><td colspan="1" valign="bottom">FG</td><td colspan="1" valign="bottom">HI?>65</td></tr>
<tr><td colspan="1"><R=NM;><NM=B? mO@M=NM><</td><td colspan="1">XYZ[</td><td colspan="1">I?></td><td colspan="1">I?></td><td colspan="1"><=B?; mO@M=NM><</td><td colspan="1">E</td><td colspan="1">G</td><td colspan="1">I?></td><td colspan="1"></td></tr>
</table>
\_debae©\_`ª



|&

||||!|,!-k'),j',!|&'
,	- (|
| - | :- | :- | - | - | - | - |
|<=@M;O;MClB;|zwvyx }|x{y||>@;|I?>|||
|«4L:O;@M92JMC\llB;|||QSKC9:KNDB:­DCN®EE|HI?>65|||
|<M?>O=B@N;OMCB;|@M; l||@MR@|;|¡¡ ||
|2796B34\J?<:ML92J=@MO;MClB;|||QSKC9:KNB:DCNE|~2;|¡¡  ||
|> mMN?=M>>¨CM>;|@=@MO;MClB;||SCNBCNE|;|¡¡ ||
¬=

0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|ZGZ£|XYZ[|;|I?>|<=@M;O;MClB;|E|G|I?>||
|273~¯4\=@:°LM92JO;=MBC?<lMB;|TXUYVZW[ W|~2;|HI?>65 J|2796B34\J?<:ML92J=@MO;MClB;|DE|FG|~2;||
|A¥=@MO;=MMClB>;>¨CM>@;|XYZ[|I?>|I?>|> mMN?=M>>¨CM>@;=@MO;MClB;|E|G|;||

	
 
	



|!#$"! |\*)$(|""-. =8.<;:|\*8929$.78"|!  AA$ $C#89B=|
| - | - | - | - | - |
|KGLFIMGEIJHNIOMP 
 QER2I^]\[ \_`ab]^|NEI|dMN|||
|fehgREgeeg|strLqpPioJ
mjl
kn|cdMN|||
#$v:u



|-! A#$Bv:y2"$2:|\*)$(|#$}|B.|!  A\* $C9C;|!#$"! |B=;#B8:"8 9|8#$C9=uC"$ A$ #=|""-.|A!  A$ $#89B=|
| - | - | - | - | - | - | - | - | - |
|||KE|dMN|KGLFIMGEIJHNIOMP 
 QERI||``|dMN||





|!#$"! |\*)$(|""-. =8.<;:|\*8929$.78"|!  AA$ $C#89B=|
| - | - | - | - | - |
|KGKFIGEIJPH2I  \_^]\[ ab]^`|NEI|dMN|||
|fhIGKEJP HegI
|LqtpPsioJ
j
kmmn|cdMN|||
|MEGFKNRIIHG IJLEHPI
 KP
|R¢II|KE|§¦¨¨||
#$v:u



|-! A#$Bv:y2"$2:|\*)$(|#$}|B.|!  A\* $C9C;|!#$"! |B=;#B8:"8 9|8#$C9=uC"$ A$ #=|""-.|!  AA $#89B=|
| - | - | - | - | - | - | - | - | - |
|||KE|dMN|KGKFIGEIJPHI |s|``|dMN||
© 
	



|!#$"! |\*)$(|""-. =8.<;:|\*8929$.78"|!  AA$ $C#89B=|
| - | - | - | - | - |
|KGJER FIGEIJHI  QRN2^I]\[ \_`ab]^|NEI|dMN|||
|fehgREgeeg|strLqpPioJ
mjl
kn|cdMN|||
|MEFNRIH LEP
|R¢II|KE|§¦¨¨||
#$v:u



|-! A#$Bv:y2"$2:|\*)$(|#$}|B.|!  A\* $C9C;|!#$"! |B=;#B8:"8 9|8#$C9=uC"$ A$ #=|""-.|A!  A$ $#89B=|
| - | - | - | - | - | - | - | - | - |
|||KE|dMN|KGJER FIGEIJHI  QRNI|«|``|dMN||
©¬
	

#!#$"!  \*)$( ""-. =8.<;:\*28929$.78" !  AA$ $C#89B= ![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAY8AAABCCAYAAACvm1o5AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAshJREFUeJzt3b1uI1Uch+HfnNhBWQsQsKstiKAnEgVtCkSzTept0tBwAxQo9RZbRLQ04SrS0dFTIm4hFVXQSpGjXWco4sBIREh/4djr4Xmko9gTF2c0H+84KU4CAEXd4Oc3SZ4n6ZcDeHivk0w3PQlW7k1u76NjOrZ9kq+T/J4kk+XGLslHSX5L8nPEA9alz98PcYzH3T10TMe2T/Lq7s1k8It5kl+T/JTkZs2TAmCLtE1PAIDtIx4AlIkHAGXiAUCZeABQJh4AlIkHAGXiAUCZeABQJh4AlIkHAGXiAUCZeABQJh4AlIkHAGXiAUCZeABQJh4AlA3jcRPLz8K6dEkeJ3m06Ymwcl2SD5K8s+mJrNBOkqcZLF1+3zeP/p5twGq1JC+SfJnbmw3j0ZJ8m+SzjOfYvpfkhyRP7jYM49GWYyw7C2+zLsnHSd6Na25sutw+pe9teiIrtJPk0yTTLM9X//MAWL0uI38oEA8AysQDgDLxAKBMPAAoEw8AysQDgDLxAKBMPAAoEw8AysQDgDLxAKBMPAAoEw8AysQDgDLxAKBMPAAomwxeW8Mc1utNXHOj1FpbHB0dfXJ6evrHbDbb+qW9z8/P3z85OWnz+fyvbcN49IMBPDzX2kjt7u5Ojo+Pv9/b2+v6fvsP8+Hh4ev9/f1HFxcXuQvIMB47y9HFSQ3rMI0/HY/VdDKZPO77frfrRrEa7XVrbZHB0rqTf/kwAP/NKL553MdTDwBl4gFAmXgAUCYeAJSJBwBl4gFAmXgAUCYeAJSJBwBl4gFAmXgAUCYeAJSJBwBl4gFAmXgAUGY9D4AVWywWN5eXl78keTWG9Tyur6+7q6urL+bz+T92pkvyPMmzDFaKAh5MS/IyyVdxzY1Na619N5vNPj87O5v2fT/Z9nFwcPBha+3HJE/v2+EuTmJYpxbX3FiN8djubHoCAADA/82fLY/wEz0e+UgAAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALgAAABCCAYAAADkOhy4AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAZtJREFUeJzt3TGqE1EYhuFvYrgit1LwYp9Cl5HC1gW4iFTRFVgILiELyAbEyiadtUXcQcC0KZILIXNsErWzMBDz+zzNMAMDf/FyOAwzTAKFdcfjmyQvLzkInNGXJO+S9MPjhc9Jvl1uHjir75ceAAAAAAAAAAAAAAAAAADgH/cwyeMcP6gfXHYWOKsuyYskb3NsW+BU8yjJs1jB+R8InNIETmkCpzSBU5rAKU3glCZwShM4pQmc0gROaQKnNIFTmsApTeCUJnBKO/1G8EmS2/z6byZcq6f5beEeJsloNHrfWnsdgXPl7u/v9+v1+uPhcEhyDHw+nz/o+/6m6zqBc7Vaa225XPaTyaTb7XZJjoHf3d0Nk9y01gTO1WqtZbVatcFgcNp6/9yDJ0ks4FTjKQqlCZzSBE5pAqc0gVOawClN4JQmcEoTOKUJnNIETmkCpzSBU5rAKU3glHZ6H/xr13Wf+r73QjhXbbPZ3O73+8PpvEuS1tpgsVgMxuPxpeaCvzabzbrpdPp8u92+6vv+Q5LDH2+CK9PF1huggB+sP0xw9v3gCQAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAABCCAYAAADQQ92kAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAXRJREFUeJzt3TFKK1EYR/FzZzTE1z0LiX02YGvrIlyEuAQhWxGDuALJVkQXoKRIkUokd14zeegCMn9kzq+Zme4rDgMD32VACir99RK4+/YsHVIH3ANP++BmwAUGqGF0wCvwlh5EkiRJkiRJkiRJkiRJkiRJkqRfqk0PoFH6C0yAzyY9iUbpBrgGOAoPonE6A74AfAMq4f/xXwNUlAEqygAVZYCKMkBFGaCiDFBRBqgoA1SUASrKABVlgIoyQEUZoKIMUFH7hdQ/wCkGqWGc0O8EHgHM5/Or3W73UErxjIgObr1er7fb7Qv0AS6XywY4xhV9DWCxWDSr1Qrog5vNZk2tdYIBagDT6XTSNE1ba/0ZXCn+qUvD8qNDUQaoKANUlAEqygAVZYCKMkBFGaCiDFBRBqgoA1SUASrKABVlgIoyQEXt9wHfSynPXde5kq+D22w257VWoD8Y0nVd09+7kaqDa9v2ttb6ATymZ9E47V94kjRi/wCQKTOzvhUvDQAAAABJRU5ErkJggg==)Go2K FIGEIJHI ^]\[ \_`ab]^ NEI dMN

``RhgRfEgeeg tsrLqpPioJ
mjl
kn cdMN

#$v:u



|-! A#$Bv:y2"$2:|\*)$(|#$}|B.|!  A\* $C9C;|!#$"! |B=;#B8:"8 9|8#$C9=uC"$ A$ #=|""-.|!  AA $#89B=|
| - | - | - | - | - | - | - | - | - |
|||KE|dMN|KGo FIGEIJHI|®|``|dMN||

©©

	



|!#$"! |\*)$(|""-. =8.<;:|\*8929$.78"|!  AA$ $C#89B=|
| - | - | - | - | - |
|KGJ MPIPFIGEIJHI   \[ ^] \_`ab]^|NEI|dMN|||
|fehgREgeeg|LqtpPsioJ
j
kmmn|cdMN|||
|MEFNRIH LEP
|R¢II|KE|§¦¨¨||
|R GJR±IMMOg±²P¯
	e¯°|NEI|KE|§£¦¨¨¤¥||
2 

#$v:u



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WZNIX]]H\[YZX[Z\|\_|<|MNO||
ihfgffe nkgrjpqiof



|&

||!|!-,vu'),',!|&('
,	-|
| - | - | - | - | - |
|WIXHw[YZX[Zxy\ z[ NZOI |OZI|MNO|||
|bywFI`yby|x\[\T[UaTU|JMKLNO|||
|NOIWZ]wIYx\|wZZ|GHI|  ||
0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|& '
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WIXHw[YZX[Zxy\ z[ NOIZ|¢|<|MNO||
fgojf nk qf

- 

  ! -!,vu'),',! &('
,	-![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZcAAABCCAYAAACb4pslAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAstJREFUeJzt3bFuHFUYhuHveBYScBAUOCIlsRuoSQm4QEK5AHcpKaJcArUbCtrchIt0seRbQIKGC3BJBY60DWD7UHitrC2gCD8z2t3nkUbetVz8uzvjd842JwGAYm3pZ0syJOnTjQPACrvMVUP6bOmX3yR5nOTVJCPBZpsnuTf1EIzu+ma+/etfrY4fkjxPkuu4tCQfJPkpyctYvcDYzvP6emRzrFNcepLfrp8sn8x/JPklyY8RFwDeTE/++U5JXAB4Y1tTDwDA+hEXAMqJCwDlxAWAcuICQDlxAaCcuABQTlwAKCcuAJQTFwDKiQsA5cQFgHLiAkA5cQGgnLgAUE5cACh3Oy42CYNxtSQPkrwz9SCM7t0kH2U9tjhuSe4l2Vk8tnKBib2d5Pskn049CKNqSR4l+S7JMPEsFVqSr5N8m0VXbsdlHQoKq+R65XJ36kEYVcvVyuVB1uf/7ntJ7sfKBWByLesTlxuvRVwAKCcuAJQTFwDKiQsA5cQFgHLiAkA5cQGgnLgAUE5cACgnLgCUExcAyokLAOXEBYBy4gJAOXEBoJy4AFBOXAAm0lrL3t5e672v9HFwcNCGYbix6dls6fHvi6OP+u4Cr5JcTj0Eozvf2dnZOjo6+ur09PR86mH+i8PDw635fP7w+Ph4nkVDluNyZ3G0CAyM6f34FmHjDMPw1u7u7ufb29tfZsW3Op7NZtnf3//z5OTkxcXFRUtuxgWAcbVc3VisdFwWbrwOd0sAlBMXAMqJCwDlxAWAcuICQDlxAaCcuABQTlwAKCcuAJQTFwDKiQsA5cQFgHLiAkA5cQGgnLgAUE5cACi3vFlYXzqA8bjmNk9P0ufz+a+995+nHqbC2dnZh733y9w6n1uSL5J8lvXYEQ1WxSzJsyQfTz0Io2pJPknyNMkw8SwVWpJHSZ7kb74RaxEWmIJrb3Ot0+fuPAbg//UXvajhDwDrCFMAAAAASUVORK5CYII=)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUwAAABCCAYAAAA8E++bAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAjtJREFUeJzt3bFqW2cYx+H/d2QbE+pi2kIw2bJm6tKtU0F7O7SX4KWbb6Szd3cLeMgF5Aa6dfVWQg01WHZbWiSdk8Ey0VLyukQRxzzPIh3Q8A3id74jwfcmAJS0tddu7RqAd/ok/c7q4iDJT7mLJozNn0k+2fYieLSGJC+TvLrfUe4leRHBZJwWSXbe+yn4f4Ykb5L8vv4I7nEc4L8N214AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAI/QQZJPt70IHrXPkxwmZvgwbi3JNMm3MWKFzfkxyQ+JwVGMW8vdDvNg9d7MFTbhiyT/JHaYjJ+dJR+NYAIUCSZAkWACFAkmQJFgAhQJJkCRYAIUCSZAkWACFAkmQJFgAhQJJkCRYAIUCSZAkWACFAkmQNH9iet7u7u7L+IwVsalzefzZ13XPZlMJl/GietswHw+P0zyW7IK5nQ6PZzNZj8Pw7C31ZXBA11eXv7d931/dHT0fdzw2YCLi4u/rq6ufklWwTw9PZ0sl8vnSQSTMRnOzs7+uL29XR4fHz9trQkmH9zJycmb8/Pz/WRtCFrX+TmTcen7vrXW0lprXddFL9mQ1nVd+r73pw9AlWACFAkmQJFgAhQJJkCRYAIUCSZAkWACFAkmQJFgAhQJJkCRYAIUCSZAkWACFAkmQJFgAhTtJMlkMvl3sVi8ztqBwjAG19fX+zc3N90wDL86cZ1NmM1mn/V9b14Uo9eSfJXk65jnw+Z8l+SbbS8CPoQWsWSzfMcAHuotEh9c7v0TStMAAAAASUVORK5CYII=)

wZWI[IX\Z zw OZ   OZI MNO

ZI[XFI\QZUSbTySF ¤x\[\T[UaTU£ JMKLNO

0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|&'
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WZwI[IX\Z zw OZ||<|MNO||
hpl¨§ki¦f nkkrjiqf

- 

  ! -!,vu'),',! &'
,	- (![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAasAAABWCAYAAABxarkeAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAABDtJREFUeJzt3c+LF3Ucx/HXzHddlr5figWFJLpEGF5DOhshCB1CBKlDeekm9IPas39AFPQ/5MUFZW+Jp/Ug4qlLdIowNaKFQF0l98dMh13XXbFCW53PfL+PBwzf736/e3gPM/N58v3uwiQAAAD8P9W2x9eTvJqk6W4coANtHq4FjJf1JIOuh3hKbZJfklxP0k5tvlgl+SDJW0l+7GgwoBtNNtYAwRovbZKVJNPp57FtklxIciPbYpUkt5PMJ/kuGzsJAF1qN7dM/cMbvgoEoBh11wMAwH8RKwCKJ1YAFE+sACieWAFQPLECoHhiBUDxxAqA4okVAMUTKwCKJ1YAFE+sACieWAFQPLECoHhiBUDxxAqA4okVTLY6/bzlOf+uSr+PbbVtS7IzVitJVuOW9jApqiTvJznY9SDsuirJXJI3089gVUk+TPLu5vMdsZpOsif93DHgyVXZWMxe7noQdl2d5O0kr6Sfa/qDc/NgHhMrYPLs+KqFsdL3Y1ttfxQrAIonVgAUT6wAKJ5YAVA8sQKgeGIFQPHECoDiiRUAxRMrAIonVgAUT6wAKJ5YAVA8sQKgeGIFQPHECoDiiRUAxZt68KSu63bv3r0zi4uLs6PRyK3tYcwtLy9Xhw4dmrp7927T9Sw8E2v79u2bPn/+/HB2drZXa/q1a9fqY8eOVffv3986N7diNRgMqlOnTn0+MzPz8draWjcTAs/N9PR0dfTo0ZcWFhYWXPPjp6qqzM3Nfbl///5Pq6pfNww+cOBADh8+/MLFixd/bZqmTXZ+shoMh8PX2rbd07cdA57OcDhcHgwGA7EaP3VdT49GozeSvNi3Nb2qqnY4HP6eZJDN29pPPfILycP73gNjrm1b1/t4q9LfNX3H3P7BAoDiiRUAxRMrAIonVgAUT6wAKJ5YAVA8sQKgeGIFQPHECoDiiRUAxRMrAIonVgAUT6wAKJ5YAVA8sQKgeGIFQPG2br64vr6e1dXVO0maqqraDmcCno9qbW0t6+vrrvcx1DRNVlZW/kxyp29retM01erq6l/bX5va9mZz7ty5b44fP/59Xde92jHgyTVNU126dOmTrufg2Wjbtj179uxXR44cWRyNRr1a0+/du1dfuXLli6ZptubeEaurV69eP3PmzA+nT5/u1Y4BT+7EiRP1zZs3l5I0Xc/CM7F2+fLlG/Pz8z/1bU0/efLkYGlp6VYec27WST5L8lH8HQsmRZ3k6yTvdD0Iu24qyYUk76Wfa/ogybdJ5jaf93InAJgwYgVA8cQKgOKJFQDFEysAiidWABRPrAAonlgBUDyxAqB4YgVA8cQKgOKJFQDFEysAiidWABRPrAAonlgBULxHY9VubsBkcL2Ptz4f33b749S2H5aS3OpiIqATbZLfktzuehB2XZvk52wc2z4G68G5+cfj3qw2N2ByuO7HV9+Pbd/nB2DS/A0JBtJdNEPJUQAAAABJRU5ErkJggg==)![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAATgAAABWCAYAAABb9or0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAudJREFUeJzt3b9qLGUcx+HvTDYKJk1OQFKcKziSKt6DiI1NOgm2QsqAVSCNjY1F+tzA3oFtwNYqhZV48BCT9ME4mR2Ls/mjeNQ9CrP58TywvOwyxVv8+LCzA+8mAAA8Lc18fZZk+9F7GNswX80kixqS/Jjk5WT+wftJPkqyMtqW4I/6+dpG5FjMLMm3SV7eDc6fV4CnbMjDXQAAAAAAAAAAAAAAAAAAAAAAAAAAAPw/nODLMno8l05lZVHtfJ21f3sZjKNN8lWSjbE3wpP0WZJPkofSwTJZSfJxkvfiLoPFfZjkRSJwLCdR47+4nx+BA8oSOKAsgQPKEjigLIEDyhI4oCyBA8oSOKAsgQPKEjigLIEDyhI4oCyBA8oSOKAsgQPKmszXJmLH8mjnr5X5y7HlLOL+PLhJkqyvr3/Q9/0XwzCIHKMbhmGl67pnk8nkqG3bX8feD09L13Uv+r7/KZkH7uTk5HmSz/PwjQ5G0/f9cHBwcHt4eLi7sbGx2jQO+OXfOz4+/uX09DTJPGg7OzttkneaphE4Rtd13bC2ttZub29na2tL4FjI5ubmal7/tPHwja1pmhgklsXdPJpL3kbbtpnNZh4sAHUJHFCWwAFlCRxQlsABZQkcUJbAAWUJHFCWwAFlCRxQlsABZQkcUJbAAWUJHFCWwAFlCRxQ1iRJLi4uZk3T/JZkNvJ+IF3X5ebmJpeXl7dJBgdesojr6+vZbPY6ZZMk2dvbG25vb/s8+jcaGMswDDk/P2/29/f7yWRiJlnI1dXV/b+w3Q1Pc3R01JydnRkmRjedTleTfJfk093d3Vdj74enZTqdfpPk5yRfj70X+CvvJvk+yfO4q2Bxx0m+TDxkAAoTOKAsgQPKEjigLIEDyhI4oCyBA8oSOKAsgQPKEjigLIEDyhI4oCyBA8oSOKAsgQPKEjigLIFjGQ3/fAm80f38CBzLaEjyQ5IuYsfiXiW5HHsT8CZNHFXO2zM/QH2/A4M7fMiItNCwAAAAAElFTkSuQmCC)

WIX©Zw]XWNxH zw OZ OZI MNO

wwyFbI`yby ¤x\[\T[UaTU£ JMKLNO

wY«H\XINHXONO OO©Z¤IIZ GHI 

0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|& '
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WIX©Zw]XWNxH zw OZ|¢|<|MNO||
mooh§fki¦f



|&

||!|!-,vu'),',!|&('
,	-|
| - | - | - | - | - |
|WYwIZX[H©ZwI]\XyHIZ |OZI|MNO|||
|RXYZw[FEIH©QT`FUZwI¥VbSy]\SQbXy`EFHSIZ|¤x\[\T[UaTU£|JMKLNO|||
|NOIWZX]©Zw]wIXYHx\ YwZ[HI\yIZ|wZZ|GHI|  ||
0



|``	
<br>||
!	 |&)(('|&

|&)'	,
-,	|`
`(1,0-' 
-|!|& '
,	-|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|WYwIZX[H©ZwI]\XyHIZ|¢|<|MNO||
hk



|&

||!|!-,vu'),',!|&('
,	-|
| - | - | - | - | - |
|WIXHw |OZI|MNO|||
|XFIQHbyEw`|¤x\[\T[UaTU£|JMKLNO|||
|wYWZx\XHIw|OZI|GHI|  ||
|NOKVIPRWFZX`ULaF]SQHwEIw`Yx\|`wSZZ|DGEHFI|  ||
 



|		
<br>		
<br>|<br>| "! |' \*<br>)()|'|(-\*'
-. |<br>) 1)-(.<br>` `<br>.|"|' <br>(-<br>.|
| - | - | - | - | - | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO|STHIU|W|<|MNO||
\_`\_^]



|'|||<br>|"|g-	<br>".(-\*h		
|' <br>-. <br>()|
| - | :- | :- | - | - | - | - |
|OHjUUHnolkmINkTIHl|`	`{yxz |} ~z{||okokW|MNO|||
|nolUHkKYNmIOLZXRTIQF[IHFEYE\lXF|||\[\oVk[VokWW|JMKLNO|||
|jNTSUlTonU|||okok|MNO|||
|UNPXRSTO[olIQKURkXFLo[l\|||IUPXRSl[olXF ijRU|JMKLNO|3939@CMJ?QB?4TB: 5;C@ 6?B< 5;82||
|NNTlmNkI|||okok|GHI|¡¡ ||
|I[oXFl||Ol|O£ILX¢FXl|JMKLNO|||
|STOUIIoHToUnTS|NOOU||OlI|MNO|||
|STPQUFINKkHnolE\mIRUZXRQ[TIFYHEXFl|EH\k||OLlXFI|JMKLNO|||
|ST£mIHkUlTIHl|||OlI|MNO|||
Unol

 



|		
<br>		
<br>|<br>| "! |' \*<br>)()||'||(-\*'
-. |<br>) 1)-(.<br>` `<br>.|"|' <br>(-<br>.|
| - | - | - | - | :- | - | :- | - | - | - | - |
|89: ;< 9=|AB9CC|GHI|MNO||OHjUUHnolkmINkTIHl|||<|MNO||
|nolUHkTmNOmIEH¤ZXRQI[¥TIFEKYEH\XFLlF|>A?3B@9C@C|DGEHFI|JMKLNO||nolUHkKYNmIOLZXRTIQF[IHFEYE\lXF|||6<|JMKLNO||
|S§TImUHIl oHToUn|AB9CC|MNO|MNO||STOUIIoHToUnTSNOOU|||<|MNO||
|Z[XRQS§TImkNHFEKPYHE¦\IXFEl\ RU|>A?3B@9C@C|JMKLNO|JMKLNO||STPQUFINKkHHnolEk\mIRUZXRQ[TIFEYHE\XFl|||6<|JMKLNO||
|S§TImHIl|AB9CC|MNO|MNO|Unol|ST£mIHkUlTIHl|||<|MNO||
UHnoTlkOI

UHnoTlkI

U£HnoTlUklI

«¬¨ª­©\_\_b^b`¨



|'|||<br>|"|g-	<br>".(-\*h		
|' <br>-. <br>()|
| - | :- | :- | - | - | - | - |
|STIN jUTUlkOSH		|x{yz |} ~z{||OlI|MNO|||
|LOEHijRUKNQ\kTRULZORnUFOLI[oO|`	`X	ls{qyprxz t|}uvw~rzs{||\[\oVk[okW|JMKLNO|||
|STNSOUnITSoIomI|NOITl||OlI|MNO|||
|NOUIPXRTS[KoQlPXFLo[mIFY[|||IUPXRSl[olXF ijRU|JMKLNO|3939@CMJ?QB?4TB: 5;C@ 6?B< 5;82||
|NjjU UkTSoIOomIl|NOITol||U§ll|GHI|¡¡ ||
 



<table><tr><th colspan="1">		
<br>		
<br></th><th colspan="1"><br></th><th colspan="1"> "! </th><th colspan="1">' *<br>)()</th><th colspan="1">'</th><th colspan="1">(-*'
-. </th><th colspan="1"><br>) 1)-(.<br>` `<br>.</th><th colspan="1">"</th><th colspan="1">' <br>(-<br>.</th></tr>
<tr><td colspan="1" rowspan="2">89: ;< 9=</td><td colspan="1" rowspan="2">AB9CC</td><td colspan="1" rowspan="2">GHI</td><td colspan="1" rowspan="2">MNO</td><td colspan="1">STIN jUTUlkOSH</td><td colspan="1">W</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">LOEHijRUKNQ\kTRULZORnUFOLI[XoOl</td><td colspan="1">VW</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td></tr>
<tr><td colspan="1" valign="top">S§TIomTUOnUOI oOl</td><td colspan="1" valign="top">AB9CC</td><td colspan="1" valign="top">MNO</td><td colspan="1" valign="top">MNO</td><td colspan="1" valign="top">OHjU NkTUOnUOIoOl</td><td colspan="1" valign="top">W</td><td colspan="1" valign="top"><</td><td colspan="1" valign="top">MNO</td><td colspan="1"></td></tr>
<tr><td colspan="1">®¯TQomY[NTQSOUnIPRKZIFLF</td><td colspan="1">>A?3B@9C@C</td><td colspan="1">JMKLNO</td><td colspan="1">JMKLNO</td><td colspan="1">STNPOSQOUIFnITPTSRolKIZoFmILFKQPXFL[FQY[</td><td colspan="1">VW</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td><td colspan="1"></td></tr>
</table>
©\_cdb`¨



|'|<br>|"|g-	<br>".(-\*h		
|' <br>(-<br>. )|
| - | - | - | - | - |
|OHjU NkTUOnUOIoO 	l{yxz |} ~z{|okokW|MNO|||
|SToP[kQFIoTHO\XQUKSNIRl\LkXE[PF X	ls{qyprxz t|}uvw~rzs{|OLlXFI|JMKLNO|||
|NOUITSolomI|USol|MNO|||
|KNijijRU URkTSQ\PoILOoFmIYX[l NOIKXFL[ol|U§lXXRl¦|DGEHFI|¡¡  ||
 



<table><tr><th colspan="1">		
<br>		
<br></th><th colspan="1"><br></th><th colspan="1"> "! </th><th colspan="1">' *<br>)()</th><th colspan="1">'</th><th colspan="1">(-*'
-. </th><th colspan="1"><br>) 1)-(.<br>` `<br>.</th><th colspan="1">"</th><th colspan="1">' <br>(-<br>.</th></tr>
<tr><td colspan="1" rowspan="2">89: ;< 9=</td><td colspan="1" rowspan="2">AB9CC</td><td colspan="1" rowspan="2">GHI</td><td colspan="1" rowspan="2">MNO</td><td colspan="1" valign="top">OHjU NkTUOnUOIoOl</td><td colspan="1" valign="top">W</td><td colspan="1" valign="top"><</td><td colspan="1" valign="top">MNO</td><td colspan="1" rowspan="2"></td></tr>
<tr><td colspan="1">SToP[kQFIoTHO\XQUKSlNIRl\LkXE[XPF</td><td colspan="1">VW</td><td colspan="1">6<</td><td colspan="1">JMKLNO</td></tr>
<tr><td colspan="1">§TSUToIokmIoTHOUSlNIlk</td><td colspan="1">AB9CC</td><td colspan="1">MNO</td><td colspan="1">MNO</td><td colspan="1">STokIoTHOUSlNIlk</td><td colspan="1">W</td><td colspan="1"><</td><td colspan="1">MNO</td><td colspan="1"></td></tr>
</table>

`` 		




||||#"$ |('|4562(7$,1232,3(|;; 32<=7|
| - | :- | :- | - | - | - | - |
|@?ABCDE, NOQP RSTPQU|||VD
?|XCV
|||
|@>?^DA\_CEY]@Z[D`^V[YD?>Z\
|||VD
?|WXCV
|||
|`
`V` bECA]EVdVEc?^V
	

|D|l|kmno^]jB^]|XCV
|||
|@>?CAV@>?cA?EBjC	DE
 |||VD
?|WXCV
|||
|Cj bbE EA]
VBCD^?DE|||DEqD|s` ?|wxy||
|>@Z^DEAB CDE|||>@Z^DED? bE|WXCV
|z{}||W~~XA }~ ||
|ADCB]`CDE|||VD
?|XCV
|||
4



|' ;, 4<4,|#"$ |<(|;$ 3==5||423<572<|732== ; 7|('|; ; 32<7|
| - | - | - | - | - | - | - | - | - |
|||s` ?|XCV
|@?ABCDE|n||XCV
||
|@>?pqAB CDE^DA\_C]YZ[[Y|~}|}|WXCV
|WXCV
|@>?^DA\_CEY]@Z[D`^V[YD?>Z\
|hn||WXCV
||
|@?qABCDEAEVdVEc?^
	
 VD||XCV
|XCV
|`
`V` bECA]EVdVEc?^VD
	

|n||XCV
||
|¡ £A ¢BCDECAV@?c>?Ej	
|~}|}|WXCV
|WXCV
|@>?CAV@>?cA?EBjC	DE
 |hn||WXCV
||


[ref1]: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAm4AAAAECAYAAAA3WLMGAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAEJJREFUaIHtzrENgDAQBEEfHdEb0pfwiXujJSKLEizQTHSX7RgAAHxC1ujuM8ncGQMAwCvJXVXX+sfOGAAAAAD4nQdbewUE9G/GPgAAAABJRU5ErkJggg==
