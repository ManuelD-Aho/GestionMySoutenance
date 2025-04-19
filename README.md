# README - Plateforme de Gestion des Soutenances MIAGE

![Logo Plateforme Soutenances MIAGE](https://placeholder-for-logo-image.com)

## Table des matières

- [Introduction](#introduction)
- [Architecture du système](#architecture-du-système)
    - [Vue d'ensemble](#vue-densemble)
    - [Pattern MVC](#pattern-mvc)
    - [Structure des dossiers](#structure-des-dossiers)
- [Workflow d'approbation des soutenances](#workflow-dapprobation-des-soutenances)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Installation et configuration](#installation-et-configuration)
- [Guide d'utilisation](#guide-dutilisation)
- [Sécurité et contrôle d'accès](#sécurité-et-contrôle-daccès)
- [Personnalisation](#personnalisation)
- [Bonnes pratiques](#bonnes-pratiques)
- [FAQ](#faq)
- [Support technique](#support-technique)
- [Crédits et licence](#crédits-et-licence)

## Introduction

La **Plateforme de Gestion des Soutenances MIAGE** est une application web complète conçue pour gérer le processus de validation des rapports de stage et des soutenances pour les étudiants en MIAGE (Méthodes Informatiques Appliquées à la Gestion des Entreprises).

Cette plateforme s'adresse à quatre types d'utilisateurs principaux :
- **Étudiants** : dépôt de rapports, suivi de leur progression
- **Enseignants** : évaluation des rapports, participation aux jurys
- **Personnel administratif** : vérification et traitement des dossiers
- **Administrateurs** : configuration et supervision du système

L'application est conçue pour fonctionner en environnement local, avec une architecture MVC robuste et sécurisée, tout en offrant une expérience utilisateur optimisée grâce aux URLs masquées.

## Architecture du système

### Vue d'ensemble

L'architecture de la plateforme est basée sur le modèle MVC (Modèle-Vue-Contrôleur) avec Docker pour faciliter le déploiement et la maintenance.

```
┌─────────────────────────────────────────────────────────────────┐
│                         Navigateur Client                        │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Serveur Web (Nginx)                      │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Point d'entrée (index.php)                  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Routeur (Core/Router)                     │
└───────────────┬─────────────────────────────────┬───────────────┘
                │                                 │
                ▼                                 ▼
┌─────────────────────────┐             ┌─────────────────────────┐
│       Contrôleurs       │             │    Authentification     │
└───────────┬─────────────┘             └───────────┬─────────────┘
            │                                       │
            ▼                                       ▼
┌─────────────────────────┐             ┌─────────────────────────┐
│        Services         │◄────────────┤    Contrôle d'accès     │
└───────────┬─────────────┘             └─────────────────────────┘
            │
            ▼
┌─────────────────────────┐             ┌─────────────────────────┐
│          DAOs           │◄────────────┤      Base de données    │
└───────────┬─────────────┘             └─────────────────────────┘
            │
            ▼
┌─────────────────────────┐
│          Vues           │
└─────────────────────────┘
```

### Pattern MVC

Notre application suit strictement le pattern MVC pour garantir une séparation claire des responsabilités :

```
┌─────────────────────────────────────────────────────────────────┐
│                           MODÈLE                                │
│                                                                 │
│  ┌───────────────┐      ┌───────────────┐     ┌───────────────┐ │
│  │    Entités    │◄────►│     DAO       │◄───►│   Services    │ │
│  └───────────────┘      └───────────────┘     └───────────────┘ │
│                                                                 │
│   • Représentation des données           • Logique métier       │
│   • Accès à la base de données           • Règles de validation │
└─────────────────────────────┬───────────────────────────────────┘
                              │
                              │
┌─────────────────────────────▼───────────────────────────────────┐
│                         CONTRÔLEUR                              │
│                                                                 │
│  ┌───────────────┐      ┌───────────────┐     ┌───────────────┐ │
│  │BaseControleur │◄────►│Contrôleurs    │◄───►│     Core      │ │
│  └───────────────┘      │spécifiques    │     └───────────────┘ │
│                         └───────────────┘                       │
│                                                                 │
│   • Traitement des requêtes              • Routage              │
│   • Coordination Modèle-Vue              • Sécurité             │
└─────────────────────────────┬───────────────────────────────────┘
                              │
                              │
┌─────────────────────────────▼───────────────────────────────────┐
│                             VUE                                 │
│                                                                 │
│  ┌───────────────┐      ┌───────────────┐     ┌───────────────┐ │
│  │    Layout     │◄────►│   Templates   │◄───►│  Composants   │ │
│  └───────────────┘      └───────────────┘     └───────────────┘ │
│                                                                 │
│   • Présentation des données            • Interface utilisateur │
│   • Éléments réutilisables              • Rendu HTML           │
└─────────────────────────────────────────────────────────────────┘
```

### Structure des dossiers

Notre architecture de dossiers est organisée pour maximiser la maintenabilité et l'évolutivité du projet :

```
/app-soutenance-miage/
│
├── public/                  # Point d'entrée accessible
│   ├── index.php            # Contrôleur frontal
│   ├── .htaccess            # Configuration URL rewriting
│   └── assets/              # Ressources statiques
│
├── src/                     # Code source (non accessible directement)
│   ├── Config/              # Configuration de l'application
│   ├── Modele/              # Couche Modèle (données et logique)
│   │   ├── Entites/         # Classes entités (tables DB)
│   │   ├── DAO/             # Data Access Objects
│   │   └── Services/        # Services métier
│   │
│   ├── Vue/                 # Couche Vue (présentation)
│   │   ├── Layout/          # Structures de page
│   │   ├── Templates/       # Templates par module
│   │   ├── Partials/        # Fragments réutilisables
│   │   └── Composants/      # Composants d'interface
│   │
│   ├── Controleur/          # Couche Contrôleur (traitement)
│   │   ├── BaseControleur.php
│   │   └── [Contrôleurs spécifiques]
│   │
│   ├── Core/                # Noyau de l'application
│   └── Outils/              # Classes utilitaires
│
├── config/                  # Configuration externe
├── var/                     # Données variables (logs, cache)
├── docker/                  # Configuration Docker
└── [fichiers racine]        # Fichiers de configuration
```

## Workflow d'approbation des soutenances

Le processus de validation des mémoires et soutenances suit un workflow précis que le système implémente de bout en bout :

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Étudiant   │────►│Administration │────►│ Commission   │────►│  Prof Monsan │
│              │     │   (Miss Seri)│     │de Validation │     │              │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                    │                    │
       │ 1. Dépôt rapport   │                    │                    │
       ├───────────────────►│                    │                    │
       │                    │                    │                    │
       │                    │ 2. Vérification    │                    │
       │                    ├───────────────────►│                    │
       │                    │                    │                    │
       │                    │                    │ 3. Étude rapport   │
       │                    │                    ├────────────────────┤
       │                    │                    │                    │
       │                    │                    │ 4. Compte-rendu    │
       │                    │                    ├───────────────────►│
       │                    │                    │                    │
       │                    │                    │ 5. Attribution     │
       │                    │                    │ encadreur et       │
       │                    │                    │ directeur mémoire  │
       │                    │◄───────────────────┼────────────────────┤
       │                    │                    │                    │
       │                    │ 6. Notification    │                    │
       │◄───────────────────┤                    │                    │
       │                    │                    │                    │
┌──────┴───────┐     ┌──────┴───────┐     ┌──────┴───────┐     ┌──────┴───────┐
│   Étudiant   │     │Administration │     │ Commission   │     │  Prof Monsan │
│              │     │   (Miss Seri)│     │de Validation │     │              │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
```

## Fonctionnalités principales

### Pour les étudiants
- **Dépôt de rapport** : Interface sécurisée pour téléverser les rapports de stage
- **Suivi de validation** : Tableau de bord montrant l'état d'avancement
- **Notifications** : Alertes sur les changements de statut et commentaires
- **Gestion de profil** : Mise à jour des informations personnelles

### Pour les enseignants
- **Évaluation des rapports** : Interface de consultation et d'annotation
- **Gestion des jurys** : Planification et organisation des soutenances
- **Notation** : Système standardisé pour l'évaluation des soutenances
- **Communication** : Échange avec les étudiants et autres membres du jury

### Pour le personnel administratif
- **Vérification des rapports** : Contrôle de conformité avant transmission
- **Gestion des étudiants** : Inscription, suivi administratif
- **Organisation des soutenances** : Planification des salles et horaires
- **Édition de documents** : Génération automatique d'attestations et PV

### Pour les administrateurs
- **Gestion des utilisateurs** : Création et configuration des comptes
- **Paramétrage du système** : Adaptation aux besoins spécifiques
- **Rapports et statistiques** : Tableaux de bord analytiques
- **Audit de sécurité** : Suivi des activités et détection d'anomalies

## Installation et configuration

### Prérequis
- Docker et Docker Compose
- Git (optionnel pour le clonage du dépôt)
- Minimum 4GB de RAM et 10GB d'espace disque

### Installation avec Docker
1. Clonez le dépôt ou téléchargez l'archive
   ```bash
   git clone https://github.com/votre-organisation/app-soutenance-miage.git
   cd app-soutenance-miage
   ```

2. Configurez les variables d'environnement
   ```bash
   cp .env.example .env
   # Éditez le fichier .env avec vos paramètres
   ```

3. Lancez l'application avec Docker Compose
   ```bash
   # Sur Linux/macOS
   docker-compose up -d
   
   # Sur Windows
   docker/scripts/entrypoint.bat
   ```

4. Accédez à l'application
   ```
   http://localhost:8080
   ```

### Configuration initiale

Après l'installation, connectez-vous avec les identifiants par défaut :
- **Utilisateur** : admin
- **Mot de passe** : admin123

Ensuite, suivez l'assistant de configuration pour :
1. Changer le mot de passe administrateur
2. Configurer les paramètres de l'établissement
3. Importer les données initiales (années académiques, niveaux d'études)
4. Créer les premiers comptes utilisateurs

## Guide d'utilisation

### Comment les URLs masquées fonctionnent

La plateforme utilise un système d'URLs masquées pour améliorer l'expérience utilisateur et la sécurité :

```
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  URL visible dans le navigateur :                                │
│  http://localhost:8080/rapports/consulter/42                     │
│                                                                  │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  En coulisses :                                                  │
│                                                                  │
│  1. Le serveur web reçoit : /rapports/consulter/42               │
│                                                                  │
│  2. .htaccess redirige vers : /public/index.php                  │
│                                                                  │
│  3. Core/Router décompose l'URL :                                │
│     - contrôleur : RapportControleur                             │
│     - méthode    : consulter                                     │
│     - paramètre  : 42 (ID du rapport)                            │
│                                                                  │
│  4. Le contrôleur vérifie les droits d'accès                     │
│                                                                  │
│  5. Le contrôleur demande les données au modèle                  │
│                                                                  │
│  6. La vue correspondante est rendue                             │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

Ce mécanisme permet de :
- Masquer la structure technique de l'application
- Présenter des URLs conviviales et significatives
- Centraliser la sécurité et le contrôle d'accès
- Faciliter la maintenance et l'évolution du système

### Flux de travail typique pour chaque utilisateur

#### Pour un étudiant
1. Connexion à la plateforme avec ses identifiants
2. Consultation du tableau de bord personnel
3. Téléversement d'un nouveau rapport
4. Suivi de l'état d'avancement de la validation
5. Réception des notifications de changement de statut
6. Consultation des commentaires et demandes de modification

#### Pour un administrateur (Miss Seri)
1. Réception d'une notification de nouveau dépôt
2. Vérification de la conformité du rapport
3. Validation administrative ou demande de modifications
4. Transmission du rapport à la commission de validation
5. Suivi du processus de validation

#### Pour un membre de la commission
1. Consultation de la liste des rapports à évaluer
2. Étude détaillée du rapport
3. Saisie des commentaires et évaluations
4. Participation à la délibération
5. Génération du compte-rendu
6. Attribution d'un encadreur et d'un directeur de mémoire

## Sécurité et contrôle d'accès

### Niveaux d'accès hiérarchiques

La plateforme implémente un système de contrôle d'accès à plusieurs niveaux :

```
┌─────────────────────────────────────────────────────────────────┐
│                      NIVEAUX D'ACCÈS                            │
└─────────────────────────────────────────────────────────────────┘

  Niveau 4 : Administration système
  ┌─────────────────────────────────────────────────────────────┐
  │                                                             │
  │  • Accès complet à toutes les fonctionnalités              │
  │  • Configuration du système                                 │
  │  • Gestion des utilisateurs                                 │
  │  • Audit et journalisation                                  │
  │                                                             │
  └──────────────────────────┬──────────────────────────────────┘
                             │
                             ▼
  Niveau 3 : Personnel administratif
  ┌─────────────────────────────────────────────────────────────┐
  │                                                             │
  │  • Gestion des dossiers étudiants                           │
  │  • Vérification des rapports                                │
  │  • Organisation des soutenances                             │
  │  • Édition des documents officiels                          │
  │                                                             │
  └──────────────────────────┬──────────────────────────────────┘
                             │
                             ▼
  Niveau 2 : Enseignants
  ┌─────────────────────────────────────────────────────────────┐
  │                                                             │
  │  • Évaluation des rapports                                  │
  │  • Participation aux jurys                                  │
  │  • Notation des soutenances                                 │
  │  • Encadrement des étudiants                                │
  │                                                             │
  └──────────────────────────┬──────────────────────────────────┘
                             │
                             ▼
  Niveau 1 : Étudiants
  ┌─────────────────────────────────────────────────────────────┐
  │                                                             │
  │  • Consultation de leur profil                              │
  │  • Dépôt de rapports                                        │
  │  • Suivi de leur validation                                 │
  │  • Accès à leurs évaluations                                │
  │                                                             │
  └─────────────────────────────────────────────────────────────┘
```

### Groupes d'utilisateurs et permissions

En plus des niveaux d'accès hiérarchiques, le système utilise des groupes qui définissent des permissions spécifiques :

- **Administration** : Gestion globale du système
- **Commission de validation** : Évaluation des rapports avant soutenance
- **Jury** : Organisation et évaluation des soutenances
- **Support technique** : Assistance aux utilisateurs
- **Consultation** : Accès en lecture seule à certaines ressources

### Journalisation et piste d'audit

Toutes les actions sensibles sont enregistrées dans une piste d'audit qui permet de suivre :
- Qui a fait quoi
- Quand l'action a été réalisée
- Depuis quelle adresse IP
- Sur quelle ressource

Cette journalisation est essentielle pour :
- Garantir la traçabilité des décisions
- Détecter les activités suspectes
- Résoudre les problèmes techniques
- Se conformer aux exigences réglementaires

## Personnalisation

### Configuration du système

La plateforme peut être adaptée à différents besoins via le fichier `config/parameters.yml` :
- Informations de l'établissement
- Organisation des années académiques
- Étapes du processus de validation
- Règles de nommage des fichiers
- Notifications et alertes

### Extension des fonctionnalités

Pour les développeurs souhaitant étendre le système, voici les points d'extension principaux :
- Ajout de nouveaux contrôleurs dans `src/Controleur/`
- Création de services supplémentaires dans `src/Modele/Services/`
- Extension des entités existantes pour inclure de nouveaux champs
- Développement de modules d'intégration avec d'autres systèmes

## Bonnes pratiques

### Pour les administrateurs
- Effectuez des sauvegardes régulières de la base de données
- Mettez à jour les mots de passe régulièrement
- Vérifiez régulièrement les journaux d'audit
- Formez les nouveaux utilisateurs avant de leur donner accès

### Pour les développeurs
- Respectez le pattern MVC lors de l'ajout de fonctionnalités
- Documentez tout nouveau code ou modification
- Utilisez le système de journalisation pour les opérations sensibles
- Validez rigoureusement les données entrantes

## FAQ

**Q: Comment réinitialiser le mot de passe d'un utilisateur ?**
R: Les administrateurs peuvent réinitialiser les mots de passe depuis l'interface d'administration (Menu Utilisateurs > Gérer > Réinitialiser le mot de passe).

**Q: Que faire si un rapport ne s'affiche pas correctement ?**
R: Vérifiez le format du fichier (PDF recommandé). Si le problème persiste, utilisez l'option "Signaler un problème" accessible depuis la page du rapport.

**Q: Comment configurer les notifications par email ?**
R: Accédez à la section Configuration > Notifications et renseignez les paramètres SMTP de votre serveur de messagerie.

**Q: Est-il possible d'exporter les données des étudiants ?**
R: Oui, les administrateurs peuvent générer des exports au format Excel ou CSV depuis le menu Rapports > Exportation.

## Support technique

En cas de problème technique, plusieurs options sont disponibles :

- **Documentation** : Consultez les guides détaillés dans le dossier `docs/`
- **Journaux** : Examinez les fichiers de log dans `var/logs/`
- **Support** : Contactez l'équipe technique à support@miage-soutenances.fr

## Crédits et licence

Cette application a été développée pour les besoins spécifiques de la formation MIAGE.

**Équipe de développement**
- Direction de projet : [Nom du directeur]
- Analyse fonctionnelle : [Noms des analystes]
- Développement : [Noms des développeurs]
- Tests et validation : [Noms des testeurs]

**Licence**
Ce logiciel est distribué sous licence [type de licence] - voir le fichier LICENSE pour plus de détails.

---

© 2025 - MIAGE Université - Tous droits réservés