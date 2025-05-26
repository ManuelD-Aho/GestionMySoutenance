**Analyse Détaillée du Module Personnel Administratif** 

1. **Header Proposé** 
- Espace Personnel Administratif - MySoutenance 
2. **Sidebar Dynamique et fonctionnalité par slidebar Dynamiques** 

**Commun** 

- Tableau de Bord 
- Messagerie / Chat 
- Suivi Global & Reporting 
- Archives & Logs 

**Rôle : Agent de Contrôle de Conformité** 

- Rapports à Vérifier 
- Rapports Traités 

**Rôle : Gestionnaire Scolarité** 

- Gestion des Étudiants 
- Gestion des Comptes Utilisateurs Étudiants 
- Gestion des Inscriptions & Scolarité 
- Gestion des Notes 
- Génération de Documents 
3. **Fonctionnalités Détaillées** 

**Accès & Tableau de Bord (Commun)** 

- **Connexion Sécurisée :** Lorsqu'un membre du personnel administratif se connecte avec ses identifiants uniques, une authentification sécurisée est effectuée. 
- **Tableau de Bord Personnalisé :** Après connexion, l'agent accède à un tableau de bord personnalisé affichant les tâches en attente, les alertes, et des raccourcis vers les fonctionnalités principales correspondant à son rôle. 
- **Statistiques Clés :** Le tableau de bord peut présenter des statistiques clés, comme le nombre de dossiers à chaque étape du processus relevant de sa responsabilité. 

**Gestion de la Conformité des Soumissions (Rôle: Agent de Contrôle de Conformité)** 

- **Réception des Rapports Soumis :** Lorsqu'un étudiant soumet son rapport de stage, celui-ci apparaît dans la liste des travaux en attente de vérification pour le personnel chargé du contrôle de conformité. 
- **Consultation des Rapports et Pièces Jointes :** Lorsque l'agent sélectionne un rapport, il peut visualiser le document principal ainsi que toutes les informations et pièces complémentaires soumises par l'étudiant. 
- **Vérification Administrative et Réglementaire :** 
- Lorsque l'agent examine un dossier, il vérifie la complétude des informations soumises et le respect des normes de l'UFR-MI (format, nombre de pages, présence des sections obligatoires, etc.). 
- Le système peut assister l'agent en surlignant automatiquement les champs d'information obligatoires non remplis ou en affichant une alerte si des documents requis sont absents. 
- Le système pourrait croiser le numéro étudiant avec la base des inscriptions pour confirmer son statut administratif et son autorisation à soumettre, affichant un indicateur visuel. 
- **Décision de Conformité :** 
- Lorsque la vérification est terminée, l'agent marque le rapport comme "CONFORME" ou "INCOMPLET" (ou "Non Conforme"). 
- Lorsque le rapport est jugé "INCOMPLET", l'agent doit enregistrer les motifs précis de non-conformité dans un champ dédié. Ces motifs seront communiqués à l'étudiant. 
- **Notification à l'Étudiant :** Lorsque le statut de conformité est enregistré, le système envoie automatiquement une notification à l'étudiant concerné, l'informant de la décision et, en cas de non-conformité, des raisons précises et de la procédure à suivre. 
- **Transmission à la Commission Pédagogique :** Lorsqu'un rapport est marqué comme "CONFORME", le système le rend automatiquement accessible aux membres de la commission pédagogique et met à jour le statut du rapport. 

**Gestion de la Scolarité et des Étudiants (Rôle: Gestionnaire Scolarité)** 

- **Gestion des Dossiers Étudiants (CRUD) :** 
- Le Gestionnaire Scolarité dispose d'une interface pour créer, lire, mettre à jour et supprimer (CRUD) les fiches des étudiants. 
- **Création d'un étudiant :** Le Gestionnaire Scolarité remplit un formulaire avec les informations requises (nom, prénom, numéro d'étudiant, filière, année, etc.) pour ajouter un nouvel étudiant. Le système vérifie l'unicité du numéro d'étudiant. 
- **Modification d'un étudiant :** Le Gestionnaire Scolarité peut sélectionner un 

  étudiant et modifier les champs concernés pour corriger ou compléter des informations administratives. 

- **Suppression d'un étudiant :** Cette option est disponible selon des règles strictes et des autorisations spécifiques, avec potentiellement une étape de confirmation et une journalisation. 
- **Création des Comptes Utilisateurs Étudiants et Vérification du Statut de Scolarité :** 
- Lorsque le Gestionnaire Scolarité initie la création d'un compte utilisateur pour un étudiant, le système doit impérativement vérifier le statut de la scolarité de cet étudiant. 
- Cette vérification peut se faire par interrogation en temps réel du SI principal ou par consultation d'une base synchronisée. 
- Lorsque le statut de la scolarité confirme que l'étudiant est en règle, le Gestionnaire Scolarité peut procéder à la génération du compte utilisateur étudiant. Le processus inclut la création d'un login et d'un mot de passe initial avec notification à l'étudiant. 
- Lorsque le statut de la scolarité indique que l'étudiant n'est pas à jour, la création du compte utilisateur est bloquée. Le système affiche un message clair au Gestionnaire Scolarité indiquant la raison du blocage. 
- **Suivi Administratif des Soutenances :** Le Gestionnaire Scolarité peut consulter et gérer certains aspects administratifs des dossiers étudiants relatifs à leur rapport. 
- **Génération et Diffusion de Documents Officiels :** 
- Le Gestionnaire Scolarité sélectionne le type de document souhaité (attestations de dépôt, bulletins de notes, reçus de validation, reçu de paiement) pour un étudiant ou un groupe. 
- Le système utilise des modèles prédéfinis, fusionne les données pertinentes de l'étudiant, et produit le document au format PDF. 
- L'agent peut avoir une option de prévisualisation avant la génération finale. 
- **Gestion des Notes (CRUD) :** Le Gestionnaire Scolarité peut avoir les droits pour enregistrer, lire, mettre à jour ou supprimer les notes finales des étudiants, conformément aux décisions du jury et aux procédures. 

**Fonctionnalités Communes au Personnel Administratif** 

- **Suivi Global et Reporting :** Accéder à des tableaux de bord et générer des rapports sur l'état d'avancement des soutenances, les taux de conformité, les délais de traitement, etc. 
- **Communication Interne et Chat :** 
- La plateforme peut faciliter la communication entre les différents services 

  administratifs ou avec les membres de la commission via des commentaires internes sur les dossiers ou des notifications. 

- **Fonctionnalité de Chat Intégré :** Utiliser un système de messagerie instantanée (chat) intégré pour échanger rapidement des informations ou poser des questions. Ce chat permet des conversations individuelles ou de groupe avec d'autres membres du personnel administratif, les membres de la commission pédagogique et l'administrateur système. Une notification visuelle apparaît lors de la réception d'un nouveau message. L'interface peut ressembler à des applications de messagerie courantes, avec historique et envoi de messages texte. La possibilité d'envoyer des fichiers via le chat pourrait être une évolution. 
- **Archivage Numérique :** Le personnel administratif s'assure que tous les documents pertinents sont correctement archivés numériquement dans le système après la validation. 
- **Consultation des Logs et Historiques :** Le personnel peut consulter l'historique des actions et des changements de statut pour un dossier spécifique, assurant la traçabilité. 
4. **Tables SQL et Attributs Associés** 

*Basé sur mysoutenance.txt (référencé comme mysoutenance.sql dans le prompt).* **Table : personnel\_administratif** 

- numero\_personnel\_administratif (VARCHAR(50)) 
- nom (VARCHAR(100)) 
- prenom (VARCHAR(100)) 
- telephone\_professionnel (VARCHAR(20)) 
- email\_professionnel (VARCHAR(255)) 
- date\_affectation\_service (DATE) 
- responsabilites\_cles (TEXT) 
- numero\_utilisateur (VARCHAR(50)) 
- date\_naissance (DATE) 
- lieu\_naissance (VARCHAR(100)) 
- pays\_naissance (VARCHAR(50)) 
- nationalite (VARCHAR(50)) 
- sexe (ENUM('Masculin','Féminin','Autre')) 
- adresse\_postale (TEXT) 
- ville (VARCHAR(100)) 
- code\_postal (VARCHAR(20)) 
- telephone\_personnel (VARCHAR(20)) 
- email\_personnel (VARCHAR(255)) 
- photo\_profil\_chemin (VARCHAR(255)) 
- *Supporte : Identification du personnel, liaison au compte utilisateur, informations de contact.* 

**Table : utilisateur** 

- numero\_utilisateur (VARCHAR(50)) 
- login\_utilisateur (VARCHAR(100)) 
- mot\_de\_passe (VARCHAR(255)) 
- actif (TINYINT(1)) 
- id\_type\_utilisateur (INT) 
- id\_groupe\_utilisateur (INT) 
- *Supporte : Accès sécurisé, association au rôle "Personnel Administratif" via id\_type\_utilisateur.* 

**Table : type\_utilisateur** 

- id\_type\_utilisateur (INT AUTO\_INCREMENT) 
- lib\_type\_utilisateur (VARCHAR(100)) 
- *Supporte : Définition du rôle "Personnel Administratif".* 

**Table : rapport\_etudiant** 

- id\_rapport\_etudiant (INT AUTO\_INCREMENT) 
- numero\_carte\_etudiant (VARCHAR(50)) 
- id\_statut\_rapport (INT) 
- date\_soumission (DATETIME) 
- *Supporte : Réception des rapports, consultation de leur statut par l'Agent de Conformité.* 

**Table : statut\_rapport\_ref** 

- id\_statut\_rapport (INT AUTO\_INCREMENT) 
- libelle (VARCHAR(100)) 
- *Supporte : Compréhension et mise à jour du statut des rapports.* 

**Table : document\_soumis** 

- id\_document (INT AUTO\_INCREMENT) 
- id\_rapport\_etudiant (INT) 
- chemin\_fichier (VARCHAR(512)) 
- nom\_original (VARCHAR(255)) 
- id\_type\_document (INT) 
- *Supporte : Consultation des documents soumis par l'étudiant, archivage, génération de documents (si stockés ici).* 

**Table : type\_document\_ref** 

- id\_type\_document (INT AUTO\_INCREMENT) 
- libelle (VARCHAR(100)) 
- *Supporte : Identification des types de documents (rapport, attestation, etc.).* 

**Table : approuver** 

- numero\_personnel\_administratif (VARCHAR(50)) 
- id\_rapport\_etudiant (INT) 
- id\_statut\_conformite (INT) 
- commentaire\_conformite (TEXT) 
- date\_verification\_conformite (DATETIME) 
- *Supporte : Enregistrement de la décision de conformité et des motifs par l'Agent de Conformité.* 

**Table : statut\_conformite\_ref** 

- id\_statut\_conformite (INT AUTO\_INCREMENT) 
- libelle (VARCHAR(50)) 
- *Supporte : Définition des statuts "Conforme" / "Non Conforme".* 

**Table : etudiant** 

- numero\_carte\_etudiant (VARCHAR(50)) 
- nom (VARCHAR(100)) 
- prenom (VARCHAR(100)) 
- numero\_utilisateur (VARCHAR(50)) 
- (et autres informations personnelles) 
- *Supporte : Gestion des Dossiers Étudiants (CRUD) par le Gestionnaire Scolarité.* 

**Table : inscrire** 

- numero\_carte\_etudiant (VARCHAR(50)) 
- id\_niveau\_etude (INT) 
- id\_annee\_academique (INT) 
- montant\_inscription (DECIMAL(10,2)) 
- date\_inscription (DATETIME) 
- id\_statut\_paiement (INT) 
- id\_decision\_passage (INT) 
- *Supporte : Gestion des Inscriptions & Scolarité, Vérification du statut de scolarité.* 

**Table : statut\_paiement\_ref** 

- id\_statut\_paiement (INT AUTO\_INCREMENT) 
- libelle (VARCHAR(50)) 
- *Supporte : Gestion des Inscriptions & Scolarité (statuts de paiement).* 

**Table : decision\_passage\_ref** 

- id\_decision\_passage (INT AUTO\_INCREMENT) 
- libelle (VARCHAR(100)) 
- *Supporte : Gestion des Inscriptions & Scolarité (décisions de passage).* 

**Table : evaluer** 

- numero\_carte\_etudiant (VARCHAR(50)) 
- numero\_enseignant (VARCHAR(50)) 
- id\_ecue (INT) 
- note (DECIMAL(5,2)) 
- date\_evaluation (DATETIME) 
- *Supporte : Gestion des Notes par le Gestionnaire Scolarité.* 

**Table : conversation** 

- id\_conversation (INT AUTO\_INCREMENT) 
- nom\_conversation (VARCHAR(255)) 
- type\_conversation (ENUM('Direct','Groupe')) 
- *Supporte : Messagerie / Chat.* 

**Table : participant\_conversation** 

- id\_conversation (INT) 
- numero\_utilisateur (VARCHAR(50)) 
- *Supporte : Messagerie / Chat.* 

**Table : message\_chat** 

- id\_message\_chat (INT AUTO\_INCREMENT) 
- id\_conversation (INT) 
- numero\_utilisateur\_expediteur (VARCHAR(50)) 
- contenu\_message (TEXT) 
- date\_envoi (DATETIME) 
- *Supporte : Messagerie / Chat.* 

**Table : lecture\_message** 

- id\_message\_chat (INT) 
- numero\_utilisateur (VARCHAR(50)) 
- date\_lecture (DATETIME) 
- *Supporte : Messagerie / Chat (statut de lecture).* 

**Table : recevoir** 

- numero\_utilisateur (VARCHAR(50)) 
- id\_notification (INT) 
- lue (TINYINT(1)) 
- *Supporte : Notifications au personnel administratif.* 

**Table : notification** 

- id\_notification (INT AUTO\_INCREMENT) 
- lib\_notification (VARCHAR(100)) 
- *Supporte : Contenu des notifications.* 

**Table : enregistrer** 

- numero\_utilisateur (VARCHAR(50)) 
- id\_action (INT) 
- date\_action (DATETIME) 
- details\_action (JSON) 
- *Supporte : Archives & Logs.* 

**Table : pister** 

- numero\_utilisateur (VARCHAR(50)) 
- id\_traitement (INT) 
- date\_pister (DATETIME) 
- *Supporte : Archives & Logs.* 

**Module Personnel Administratif : GestionMySoutenance** 

Le module Personnel Administratif est central dans la plateforme "GestionMySoutenance". Il est conçu pour permettre aux agents administratifs de gérer efficacement les différentes étapes du processus de soutenance, depuis la réception des travaux des étudiants jusqu'à la finalisation administrative. Ce module peut englober plusieurs rôles avec des responsabilités distinctes, notamment le contrôle de conformité et la gestion de la scolarité liée aux soutenances. 

1. **Accès Sécurisé à la Plateforme et Tableau de Bord** 
   1. **Lorsque** un membre du personnel administratif se connecte avec ses identifiants uniques, une authentification sécurisée est effectuée. 
   1. **Lorsque** la connexion est réussie, l'agent accède à un tableau de bord personnalisé qui affiche les tâches en attente, les alertes, et des raccourcis vers les fonctionnalités principales correspondant à son rôle (ex: rapports en attente de vérification de conformité, étudiants à créer, soutenances à programmer). 
   1. Le tableau de bord peut présenter des statistiques clés, comme le nombre de dossiers à chaque étape du processus relevant de sa responsabilité. 
1. **Gestion de la Conformité des Soumissions (Rôle : Agent de Contrôle de Conformité)** 
- **Réception et Consultation des Rapports Soumis :** 
  - **Lorsque** un étudiant soumet son rapport de stage, celui-ci apparaît dans la liste des travaux en attente de vérification pour le personnel chargé du contrôle de conformité. 
  - **Lorsque** l'agent sélectionne un rapport, il peut visualiser le document principal ainsi que toutes les informations et pièces complémentaires soumises par l'étudiant. 
- **Vérification Administrative et Réglementaire :** 
  - **Lorsque** l'agent examine un dossier, il vérifie la complétude des informations soumises et le respect des normes de l'UFR-MI (format, nombre de pages, présence des sections obligatoires, etc.). 
  - Le système peut l'assister en surlignant automatiquement les champs d'information obligatoires non remplis par l'étudiant ou en affichant une alerte si des documents requis (selon une liste prédéfinie pour le type de soumission) sont absents. Pour la comparaison avec des référentiels, le système pourrait croiser le numéro étudiant avec la base des inscriptions pour confirmer son statut administratif et son autorisation à soumettre, affichant un indicateur visuel (vert/rouge) à l'agent. 
- **Décision de Conformité :** 
- **Lorsque** la vérification est terminée, l'agent marque le rapport comme "CONFORME" ou "INCOMPLET" (ou "Non Conforme"). 
- **Lorsque** le rapport est jugé "INCOMPLET", l'agent doit enregistrer les motifs précis de non-conformité dans un champ dédié. Ces motifs seront communiqués à l'étudiant. 
- **Notification à l'Étudiant :** 
  - **Lorsque** le statut de conformité est enregistré (CONFORME ou INCOMPLET), le système envoie automatiquement une notification à l'étudiant concerné, l'informant de la décision et, en cas de non-conformité, des raisons précises et de la procédure à suivre pour corriger son dossier. 
- **Transmission à la Commission Pédagogique :** 
- **Lorsque** un rapport est marqué comme "CONFORME", le système le rend automatiquement accessible aux membres de la commission pédagogique pour évaluation, et le statut du rapport est mis à jour (ex: "EN\_COMMISSION"). 
3. **Gestion de la Scolarité et des Étudiants (Rôle : Gestionnaire Scolarité)** 
- **Gestion des Dossiers Étudiants (CRUD) :** 
- **Lorsque** le Gestionnaire Scolarité accède à la section de gestion des étudiants, il dispose d'une interface pour créer, lire, mettre à jour et supprimer (CRUD) les fiches des étudiants. 
- **Création d'un étudiant :** 
  - **Lorsque** un nouvel étudiant doit être ajouté (par exemple, non détecté par une synchronisation automatique ou pour un cas particulier), le Gestionnaire Scolarité remplit un formulaire avec les informations requises (nom, prénom, numéro d'étudiant, filière, année, etc.). 
  - Le système vérifie l'unicité du numéro d'étudiant pour éviter les doublons. 
- **Modification d'un étudiant :** 
  - **Lorsque** des informations administratives d'un étudiant doivent être corrigées ou complétées (celles qui ne sont pas automatiquement synchronisées et modifiables), le Gestionnaire Scolarité peut sélectionner l'étudiant et modifier les champs concernés. 
- **Suppression d'un étudiant :** 
  - **Lorsque** un dossier étudiant doit être supprimé (selon des règles strictes et des autorisations spécifiques, par exemple en cas d'erreur de saisie majeure et avant toute activité significative sur le compte), cette option est disponible, potentiellement avec une étape de confirmation et une journalisation de l'action. 
- **Création des Comptes Utilisateurs Étudiants et Vérification du Statut de Scolarité :** 
  - **Lorsque** le Gestionnaire Scolarité initie la création d'un compte utilisateur pour un étudiant (soit manuellement pour un étudiant spécifique, soit pour un lot), le système doit impérativement vérifier le statut de la scolarité de cet étudiant. 
  - Cette vérification peut se faire par une interrogation en temps réel du système d'information principal de l'établissement ou par la consultation d'une base de données synchronisée indiquant si l'étudiant est "à jour de sa scolarité". 
  - **Lorsque** le statut de la scolarité confirme que l'étudiant est en règle, le Gestionnaire Scolarité peut alors procéder à la génération du compte utilisateur étudiant sur la plateforme "GestionMySoutenance". Le processus inclut la création d'un login et d'un mot de passe initial (avec notification à l'étudiant comme décrit dans le module Étudiant). 
  - **Lorsque** le statut de la scolarité indique que l'étudiant n'est pas à jour (ex: frais de scolarité non payés, inscription administrative non finalisée), la création du compte utilisateur sur "GestionMySoutenance" est bloquée. Le système affiche un message clair au Gestionnaire Scolarité indiquant la raison du blocage et suggérant de vérifier le dossier de l'étudiant dans le système d'information principal. 
- **Suivi Administratif des Soutenances :** 
  - **Lorsque** nécessaire, le Gestionnaire Scolarité peut consulter et gérer certains aspects administratifs des dossiers étudiants relatifs à leur rapport (les prérequis administratifs pour le dépôt). 
- **Génération et Diffusion de Documents Officiels :** 
- Pour la génération d'autres documents (attestations de dépôt, bulletins de notes, reçus de validation, reçu de paiement), le Gestionnaire Scolarité sélectionne le type de document souhaité pour un étudiant ou un groupe d'étudiants. Le système utilise alors des modèles prédéfinis, fusionne les données pertinentes de l'étudiant, et produit le document au format PDF, prêt à être téléchargé ou envoyé par email. L'agent peut avoir une option de prévisualisation avant la génération finale. 
- **Gestion des Notes (CRUD) :** 
- **Lorsque** les évaluations sont finalisées et transmises par la commission, le Gestionnaire Scolarité peut avoir les droits pour enregistrer, lire, mettre à jour ou supprimer (CRUD) les notes finales des étudiants dans le système, conformément aux décisions du jury et aux procédures de l'établissement. 
4. **Fonctionnalités Communes au Personnel Administratif** 
- **Suivi Global et Reporting :** 
  - **Lorsque** le personnel administratif a besoin d'une vue d'ensemble, il peut accéder à des tableaux de bord et générer des rapports sur l'état d'avancement des soutenances, les taux de conformité, les délais de traitement, etc. 
- **Communication Interne et Chat :** 
  - **Lorsque** une coordination est nécessaire, la plateforme peut faciliter la communication entre les différents services administratifs ou avec les membres de la commission via des commentaires internes sur les dossiers ou des notifications. 
  - **Fonctionnalité de Chat Intégré :** 
    - **Lorsque** un membre du personnel administratif (y compris le Gestionnaire Scolarité et l'Agent de Contrôle de Conformité) a besoin d'échanger rapidement des informations ou de poser des questions, il peut utiliser un système de messagerie instantanée (chat) intégré à la plateforme. 
    - Ce chat permet des conversations individuelles ou de groupe avec d'autres membres du personnel administratif, les membres de la commission pédagogique et l'administrateur système. 
    - **Lorsque** un nouveau message est reçu, l'utilisateur reçoit une notification visuelle sur la plateforme. 
    - L'interface de chat peut ressembler à des applications de messagerie courantes (type WhatsApp), avec une liste de contacts/groupes, un historique des conversations, et la possibilité d'envoyer des messages texte. (La possibilité d'envoyer des fichiers via le chat pourrait être une évolution). 
- **Archivage Numérique :** 
  - **Lorsque** le processus de validation est terminé pour un étudiant, le personnel administratif s'assure que tous les documents pertinents (rapport validé, PV signé, etc.) sont correctement archivés numériquement dans le système. 
- **Consultation des Logs et Historiques :** 
- **Lorsque** un suivi détaillé est requis, le personnel peut consulter l'historique des actions et des changements de statut pour un dossier spécifique, assurant la traçabilité. 

Ce module est essentiel pour orchestrer les flux de travail, garantir le respect des procédures et faciliter la communication entre tous les acteurs impliqués dans le 

processus de soutenance. 
