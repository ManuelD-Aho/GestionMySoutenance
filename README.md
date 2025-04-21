# Documentation Technique Exhaustive du Projet App-Soutenance

## Table des matières

1. [Vue d'ensemble de l'architecture](#1-vue-densemble-de-larchitecture)
2. [Infrastructure DevOps et déploiement](#2-infrastructure-devops-et-déploiement)
3. [Environnement Docker](#3-environnement-docker)
4. [Backend : Structure et fonctionnement](#4-backend--structure-et-fonctionnement)
5. [Frontend : Organisation et composants](#5-frontend--organisation-et-composants)
6. [Services partagés](#6-services-partagés)
7. [Point d'entrée et accès public](#7-point-dentrée-et-accès-public)
8. [Tests et qualité de code](#8-tests-et-qualité-de-code)
9. [Gestion des dépendances](#9-gestion-des-dépendances)
10. [Configuration et variables d'environnement](#10-configuration-et-variables-denvironnement)
11. [Flux de travail complet](#11-flux-de-travail-complet)
12. [Maintenance et évolution](#12-maintenance-et-évolution)

---

## 1. Vue d'ensemble de l'architecture

### 1.1 Philosophie architecturale

Le projet App-Soutenance utilise une **architecture MVC monolithique** avec une séparation claire des responsabilités entre backend, frontend et services partagés. Cette architecture a été choisie pour faciliter la maintenance et permettre une évolution progressive tout en maintenant la cohésion entre les composants.

### 1.2 Schéma global de l'architecture

```
                      ┌─────────────────┐
                      │  public/index.php │
                      │  (Point d'entrée) │
                      └────────┬────────┘
                               │
            ┌─────────────────┴─────────────────┐
            │                                   │
     ┌──────▼────────┐               ┌──────────▼────────┐
     │  service/Router │               │ service/Session  │
     │  (Routage)     │               │ (État utilisateur)│
     └──────┬─────────┘               └──────────────────┘
            │
     ┌──────▼─────────┐
     │  Contrôleurs   │
     │  (Backend)     │
     └──────┬─────────┘
            │                              ┌───────────────────┐
    ┌───────┴────────┐                     │ service/Database  │
    │                │                     │ (Accès données)   │
┌───▼───┐      ┌─────▼─────┐               └────────┬──────────┘
│ Modèles│      │   Vues    │                       │
│(Backend)│◄────►(Frontend) │                ┌──────▼──────┐
└────────┘      └───────────┘                │Base de données│
                                             └───────────────┘
```

### 1.3 Principes fondamentaux

- **Modèle MVC** : Séparation claire entre modèles (données), vues (interface) et contrôleurs (logique)
- **Approche monolithique** : Application unique plutôt que microservices
- **Centralisation des services** : Services partagés pour les fonctionnalités communes
- **Déploiement continu** : Intégration GitHub/Azure pour automatiser les mises en production
- **Testabilité** : Structure favorisant les tests à tous les niveaux

---

## 2. Infrastructure DevOps et déploiement

### 2.1 Configuration GitHub Actions (.github/)

#### 2.1.1 Structure et fonctionnement

Le dossier `.github/workflows/` contient des fichiers YAML qui définissent les workflows d'automatisation. Ces workflows sont déclenchés par des événements Git spécifiques (push, pull request) et exécutent une série d'actions.

Workflows principaux :
- `ci.yml` : Intégration continue (tests, linting)
- `deploy.yml` : Déploiement automatique vers Azure

#### 2.1.2 Exécution des workflows

Pour exécuter un workflow GitHub Actions :
1. Effectuez un push sur une branche configurée comme déclencheur
2. Le workflow démarre automatiquement
3. Les résultats sont visibles dans l'onglet "Actions" du dépôt GitHub

### 2.2 Configuration Azure (.azure/)

#### 2.2.1 Pipelines Azure DevOps

Les fichiers dans `.azure/pipelines/` définissent des pipelines Azure DevOps pour orchestrer le déploiement :

- `main-pipeline.yml` : Pipeline principal de déploiement
  - Stages : build → deploy-dev → deploy-test → deploy-prod
  - Chaque stage requiert des approbations spécifiques selon l'environnement

- `tests-pipeline.yml` : Pipeline de test approfondi
  - Tests unitaires, d'intégration et de vue exécutés en parallèle
  - Génération de rapports de couverture de tests

#### 2.2.2 Templates ARM

Les templates ARM (Azure Resource Manager) dans `.azure/templates/` définissent l'infrastructure en tant que code :

- `arm-webapp.json` : Configuration Azure App Service
  - Définition des plans App Service
  - Configuration des slots de déploiement
  - Paramètres de performances et de mise à l'échelle

- `arm-database.json` : Configuration MySQL
  - Spécifications du serveur
  - Configuration des bases de données
  - Règles de pare-feu et sécurité

- `arm-storage.json` : Configuration du stockage
  - Comptes de stockage pour les fichiers uploadés
  - Configuration CORS
  - Stratégies de rétention et backup

#### 2.2.3 Scripts d'automatisation

Les scripts dans `.azure/scripts/` automatisent des tâches spécifiques :

- `setup-environment.sh` : Initialisation d'un environnement
  ```bash
  # Exemple d'exécution
  cd .azure/scripts
  ./setup-environment.sh dev
  ```

- `database-migration.sh` : Migration de la base de données
  ```bash
  # Exemple d'exécution
  cd .azure/scripts
  ./database-migration.sh prod
  ```

#### 2.2.4 Configuration par environnement

Les fichiers dans `.azure/config/` définissent les paramètres spécifiques à chaque environnement :

- `dev.parameters.json` : Environnement de développement
  - Ressources limitées, logging verbeux
- `test.parameters.json` : Environnement de test
  - Configuration similaire à la production
- `prod.parameters.json` : Environnement de production
  - Haute disponibilité, performances optimales

### 2.3 Schéma du flux de déploiement

```
┌─────────────┐     ┌───────────────┐     ┌───────────────┐
│ Développeur │────►│ GitHub        │────►│GitHub Actions │
└─────────────┘     │ (Repository)  │     │ (CI/CD)       │
                    └───────────────┘     └───────┬───────┘
                                                  │
                                                  ▼
┌─────────────┐     ┌───────────────┐     ┌───────────────┐
│ Azure       │◄────┤ Azure DevOps  │◄────┤ Azure Container│
│ App Service │     │ (Pipelines)   │     │ Registry      │
└──────┬──────┘     └───────────────┘     └───────────────┘
       │                    ▲
       │                    │
┌──────▼──────┐     ┌───────────────┐
│ Azure       │     │ Azure         │
│ MySQL       │     │ Storage       │
└─────────────┘     └───────────────┘
```

---

## 3. Environnement Docker

### 3.1 Configuration de développement (docker/dev/)

Le dossier `docker/dev/` contient la configuration Docker pour l'environnement de développement :

- `Dockerfile` : Définition de l'image Docker pour le développement
  - Basé sur PHP avec Apache
  - Extensions PHP nécessaires préinstallées
  - Outils de développement (Xdebug, etc.)

- `php.ini` : Configuration PHP pour le développement
  - Affichage des erreurs activé
  - Limites de mémoire généreuses
  - Configuration Xdebug

### 3.2 Configuration de production (docker/prod/)

Le dossier `docker/prod/` contient la configuration Docker optimisée pour la production :

- `Dockerfile` : Définition de l'image Docker pour la production
  - Image PHP multi-stage pour réduire la taille
  - Exclusion des outils de développement
  - Optimisations de performances

- `php.ini` : Configuration PHP pour la production
  - Masquage des erreurs
  - Optimisations de cache et de performances
  - Paramètres de sécurité renforcés

### 3.3 Fichiers Docker Compose

- `docker-compose.dev.yml` : Configuration des services pour le développement
  - PHP/Apache
  - MySQL
  - phpMyAdmin (optionnel)
  - Volumes pour le développement en temps réel

- `docker-compose.prod.yml` : Configuration des services pour la production
  - PHP-FPM
  - Nginx comme serveur frontal
  - MySQL avec réplication (optionnel)
  - Redis pour le cache (optionnel)

### 3.4 Exécution de l'environnement Docker

#### 3.4.1 Développement local

```bash
# Démarrer l'environnement de développement
docker-compose -f docker-compose.dev.yml up -d

# Arrêter l'environnement
docker-compose -f docker-compose.dev.yml down

# Reconstruire après modification du Dockerfile
docker-compose -f docker-compose.dev.yml up -d --build
```

#### 3.4.2 Simulation de production

```bash
# Démarrer l'environnement de production en local
docker-compose -f docker-compose.prod.yml up -d

# Accéder aux logs
docker-compose -f docker-compose.prod.yml logs -f
```

---

## 4. Backend : Structure et fonctionnement

### 4.1 Architecture des contrôleurs (backend/controllers/)

#### 4.1.1 BaseController.php

Le `BaseController` est la classe parente de tous les autres contrôleurs. Il fournit :
- Méthodes de rendu des vues
- Gestion des redirections
- Vérifications d'authentification et d'autorisation
- Journalisation des actions

```php
// Usage interne dans les contrôleurs enfants
$this->render('etudiant/dashboard', ['data' => $data]);
$this->redirect('/login');
$this->isAuthenticated();
```

#### 4.1.2 Contrôleurs spécifiques

Chaque contrôleur gère un domaine fonctionnel spécifique :

- `AdminController.php` : Gestion administrative
  - Gestion des utilisateurs
  - Configuration du système
  - Rapports et statistiques

- `AuthController.php` : Authentification et sécurité
  - Login/logout
  - Réinitialisation de mot de passe
  - Gestion des sessions

- `EtudiantController.php` : Fonctionnalités étudiantes
  - Soumission de rapports
  - Suivi des évaluations

- `EnseignantController.php` : Fonctionnalités enseignantes
  - Évaluation des rapports
  - Gestion des notes

- `CommissionController.php` : Gestion des commissions
  - Planification des soutenances
  - Délibérations
  - Production des comptes-rendus

- `RapportController.php` : Gestion des rapports
  - Visualisation des rapports
  - Téléchargement/upload
  - Commentaires et annotations

### 4.2 Modèles de données (backend/models/)

#### 4.2.1 Structure générale des modèles

Les modèles encapsulent la logique d'accès aux données et les règles métier :

- `Utilisateur.php` : Modèle de base pour tous les utilisateurs
  - Propriétés communes (nom, email, etc.)
  - Méthodes d'authentification
  - Relations avec d'autres modèles

#### 4.2.2 Modèles spécifiques

- `Etudiant.php` : Gestion des données étudiantes
  - Informations académiques
  - Relations avec les rapports
  - Méthodes spécifiques aux étudiants

- `Enseignant.php` : Gestion des données enseignantes
  - Spécialités et disponibilités
  - Relations avec les évaluations
  - Participation aux commissions

- `PersonnelAdmin.php` : Gestion du personnel administratif
  - Niveaux d'accès administratifs
  - Permissions spéciales

- `Rapport.php` : Gestion des rapports de stage/mémoire
  - Métadonnées (titre, date, etc.)
  - État du rapport (soumis, évalué, etc.)
  - Relations avec évaluations et comptes-rendus

- `CompteRendu.php` : Gestion des comptes-rendus d'évaluation
  - Décisions de la commission
  - Notes et commentaires
  - État de validation

### 4.3 Point d'entrée backend (backend/index.php)

Ce fichier sert de point d'entrée alternatif pour des cas spécifiques :
- Appels AJAX nécessitant un traitement isolé
- API interne pour les services frontaux
- Traitement de tâches asynchrones

### 4.4 Schéma des interactions backend

```
┌────────────────┐     ┌────────────────┐     ┌────────────────┐
│ Router         │────►│ Contrôleur     │────►│ Modèle         │
│ (routes.php)   │     │ (Controller.php)│     │ (Model.php)    │
└────────────────┘     └───────┬────────┘     └───────┬────────┘
                               │                      │
                               │                      │
                       ┌───────▼────────┐     ┌───────▼────────┐
                       │ Vue            │     │ Database       │
                       │ (frontend/view)│     │ (service)      │
                       └────────────────┘     └────────────────┘
```

---

## 5. Frontend : Organisation et composants

### 5.1 Feuilles de style (frontend/css/)

#### 5.1.1 Structure CSS

- `styles.css` : Styles globaux de l'application
  - Variables CSS (couleurs, espacements)
  - Typographie et styles de base
  - Grille et mise en page responsive

- Styles spécifiques par profil utilisateur
  - `admin.css` : Styles pour l'interface administrateur
  - `etudiant.css` : Styles pour l'interface étudiant
  - `enseignant.css` : Styles pour l'interface enseignant

- `forms.css` : Styles pour tous les formulaires
  - Champs de saisie et validation
  - Messages d'erreur
  - Boutons et contrôles de formulaire

#### 5.1.2 Convention de nommage

Le projet utilise la convention BEM (Block, Element, Modifier) pour le CSS :
```css
.block {}
.block__element {}
.block--modifier {}

/* Exemple concret */
.form {}
.form__input {}
.form--error {}
```

### 5.2 Scripts JavaScript (frontend/js/)

#### 5.2.1 Organisation JavaScript

- `main.js` : Script principal
  - Initialisation de l'application
  - Fonctions globales
  - Gestion des événements communs

- `validation.js` : Validation côté client
  - Validation des formulaires
  - Feedback en temps réel
  - Messages d'erreur personnalisés

- `dashboard.js` : Fonctionnalités des tableaux de bord
  - Graphiques et statistiques
  - Widgets interactifs
  - Actualisation des données

- `upload.js` : Gestion des téléchargements
  - Upload asynchrone
  - Barre de progression
  - Validation des fichiers

#### 5.2.2 Exécution JavaScript

Les scripts sont chargés de manière optimisée :
- Scripts critiques chargés dans le `<head>` avec attribut `defer`
- Scripts non-critiques chargés en fin de page
- Minification pour la production

### 5.3 Ressources visuelles (frontend/images/)

Organisation des images :
- Icônes UI
- Logos de l'application
- Images de fond
- Médias spécifiques aux profils utilisateurs

### 5.4 Système de vues (frontend/views/)

#### 5.4.1 Organisation des vues

Les vues sont organisées par domaine fonctionnel :

- `admin/` : Vues administratives
  - `dashboard.php` : Tableau de bord admin
  - `users.php` : Gestion des utilisateurs
  - etc.

- `etudiant/` : Vues pour les étudiants
  - `dashboard.php` : Accueil étudiant
  - `submit-report.php` : Soumission de rapport
  - etc.

- `enseignant/` : Vues pour les enseignants
  - `dashboard.php` : Accueil enseignant
  - `evaluate.php` : Évaluation des rapports
  - etc.

- `commission/` : Vues pour la commission
  - `deliberation.php` : Page de délibération
  - `planning.php` : Planification des soutenances
  - etc.

- `rapport/` : Vues liées aux rapports
  - `view.php` : Visualisation d'un rapport
  - `comment.php` : Ajout de commentaires
  - etc.

- `auth/` : Vues d'authentification
  - `login.php` : Connexion
  - `register.php` : Inscription (si applicable)
  - `reset-password.php` : Réinitialisation de mot de passe

- `errors/` : Pages d'erreur
  - `404.php` : Page non trouvée
  - `403.php` : Accès refusé
  - `500.php` : Erreur serveur

#### 5.4.2 Composants de mise en page

- `layouts/` : Gabarits principaux
  - `main.php` : Structure de base des pages
  - `admin.php` : Layout spécifique admin
  - `auth.php` : Layout pour pages d'authentification

- `partials/` : Fragments réutilisables
  - `header.php` : En-tête des pages
  - `footer.php` : Pied de page
  - `sidebar.php` : Barre latérale de navigation
  - `alerts.php` : Messages système (succès, erreur, etc.)

#### 5.4.3 Utilisation des vues

Les vues sont instanciées par les contrôleurs :
```php
// Dans un contrôleur
public function dashboard() {
    $data = $this->etudiantModel->getDashboardData();
    return $this->render('etudiant/dashboard', [
        'user' => $this->currentUser,
        'data' => $data
    ]);
}
```

Dans la vue, les données sont disponibles directement :
```php
<!-- Dans etudiant/dashboard.php -->
<h1>Bonjour, <?= htmlspecialchars($user->prenom) ?></h1>

<div class="dashboard-stats">
    <?php foreach ($data as $stat): ?>
        <!-- Affichage des statistiques -->
    <?php endforeach; ?>
</div>
```

### 5.5 Schéma du flux frontend

```
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│ PHP Template  │────►│ HTML généré   │────►│ CSS Styling   │
│ (Vue)         │     │               │     │               │
└───────────────┘     └───────┬───────┘     └───────────────┘
                              │
                      ┌───────▼───────┐
                      │ JavaScript    │
                      │ Interactivité │
                      └───────────────┘
```

---

## 6. Services partagés

### 6.1 Configuration de l'application (service/config.php)

Ce fichier définit les constantes et paramètres globaux de l'application :
- Chemins de base
- Paramètres généraux
- Configuration des fonctionnalités

```php
// Utilisation des configurations
define('APP_NAME', 'App-Soutenance');
define('APP_VERSION', '1.0.0');
define('BASE_PATH', dirname(__DIR__));
```

### 6.2 Configuration de la base de données (service/database.php)

Ce fichier contient :
- Paramètres de connexion à la base de données
- Configuration du pool de connexions
- Options de sécurité

```php
// Paramètres de base de données (chargés depuis .env)
return [
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
```

### 6.3 Définition des routes (service/routes.php)

Ce fichier centralise la configuration des routes de l'application :
- Association URL → contrôleur/action
- Paramètres de route
- Middleware (authentification, autorisations)

```php
// Définition des routes
return [
    // Route, [Contrôleur, méthode], middleware
    ['GET', '/', ['AuthController', 'showLogin']],
    ['POST', '/login', ['AuthController', 'login']],
    ['GET', '/logout', ['AuthController', 'logout']],
    
    ['GET', '/etudiant/dashboard', ['EtudiantController', 'dashboard'], ['auth', 'etudiant']],
    ['GET', '/etudiant/rapport/soumettre', ['EtudiantController', 'showSoumettre'], ['auth', 'etudiant']],
    ['POST', '/etudiant/rapport/soumettre', ['EtudiantController', 'soumettre'], ['auth', 'etudiant']],
    
    // Plus de routes...
];
```

### 6.4 Classes utilitaires principales

#### 6.4.1 Router (service/Router.php)

Le routeur analyse les requêtes HTTP et dirige vers le contrôleur approprié :
- Analyse de l'URL
- Correspondance avec les routes définies
- Gestion des paramètres d'URL
- Exécution des middleware
- Instanciation et exécution du contrôleur

```php
// Initialisation du routeur dans index.php
$router = new Router();
$router->loadRoutes(require 'service/routes.php');
$router->dispatch();
```

#### 6.4.2 Session (service/Session.php)

Gère la session utilisateur et les données persistantes :
- Création/destruction de session
- Stockage de données en session
- Gestion des messages flash (notifications)
- Sécurisation des sessions

```php
// Utilisation de la classe Session
Session::start();
Session::set('user_id', $user->id);
Session::get('user_id');
Session::flash('success', 'Opération réussie');
```

#### 6.4.3 Database (service/Database.php)

Fournit une interface d'accès à la base de données :
- Connexion à la base de données
- Requêtes préparées
- Transactions
- Abstraction de la couche de données

```php
// Utilisation de la classe Database
$db = Database::getInstance();
$users = $db->query('SELECT * FROM utilisateurs WHERE role = ?', ['etudiant']);
$db->insert('rapports', ['titre' => 'Mon rapport', 'id_etudiant' => 42]);
```

### 6.5 Services métier

#### 6.5.1 AuditService (service/AuditService.php)

Journalise les actions importantes :
- Connexions/déconnexions
- Modifications de données sensibles
- Actions administratives
- Historique des opérations

```php
// Journalisation d'une action
AuditService::log(
    'submission', 
    'Soumission du rapport', 
    $userId,
    ['rapport_id' => $rapportId, 'titre' => $titre]
);
```

#### 6.5.2 EmailService (service/EmailService.php)

Gère l'envoi d'emails :
- Notifications
- Emails de confirmation
- Rappels
- Templates d'email personnalisables

```php
// Envoi d'un email
EmailService::send(
    $user->email,
    'Confirmation de soumission de rapport',
    'emails/confirmation.php',
    ['user' => $user, 'rapport' => $rapport]
);
```

#### 6.5.3 ValidationService (service/ValidationService.php)

Valide les données entrantes :
- Validation des formulaires
- Sanitisation des entrées
- Règles de validation personnalisables
- Messages d'erreur

```php
// Validation de données
$validator = new ValidationService($_POST);
$validator->required('titre', 'Le titre est obligatoire');
$validator->minLength('titre', 5, 'Le titre doit contenir au moins 5 caractères');
$validator->email('email', 'L\'email n\'est pas valide');

if ($validator->isValid()) {
    // Traitement des données
} else {
    $errors = $validator->getErrors();
}
```

### 6.6 Configurations d'environnement

- `development.php` : Paramètres pour le développement
  - Affichage des erreurs
  - Désactivation du cache
  - Données de test

- `testing.php` : Paramètres pour les tests
  - Base de données de test
  - Données simulées
  - Configurations spéciales pour PHPUnit

- `production.php` : Paramètres pour la production
  - Optimisations de performance
  - Journalisation minimale
  - Sécurité renforcée

### 6.7 Schéma des interactions entre services

```
┌─────────────────┐     ┌─────────────────┐
│ Router          │◄────┤ routes.php      │
└────────┬────────┘     └─────────────────┘
         │
         │                ┌─────────────────┐
         │                │ Session         │
         │                └────────┬────────┘
         │                         │
┌────────▼─────────┐     ┌────────▼────────┐
│ Contrôleurs      │◄────┤ ValidationService│
└────────┬─────────┘     └─────────────────┘
         │
         │                ┌─────────────────┐
         │                │ AuditService    │
         │                └─────────────────┘
         │
┌────────▼─────────┐     ┌─────────────────┐
│ Modèles          │◄────┤ Database        │
└────────┬─────────┘     └─────────────────┘
         │
         │                ┌─────────────────┐
         │                │ EmailService    │
         └───────────────►└─────────────────┘
```

---

## 7. Point d'entrée et accès public

### 7.1 Structure du dossier public

Le dossier `public/` est le seul accessible directement par le navigateur :
- Point d'entrée de l'application
- Ressources statiques
- Fichiers uploadés

### 7.2 Configuration Apache (.htaccess)

Le fichier `.htaccess` configure le serveur web :
- Règles de réécriture pour le routage front-controller
- Restrictions d'accès
- Configuration de cache
- En-têtes de sécurité

```apache
# Réécriture des URL
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Rediriger vers HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Ne pas appliquer les règles aux fichiers et dossiers existants
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Rediriger toutes les requêtes vers index.php
    RewriteRule ^ index.php [L]
</IfModule>

# En-têtes de sécurité
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

### 7.3 Point d'entrée principal (index.php)

Le fichier `public/index.php` est le point d'entrée unique de l'application :
- Initialisation de l'environnement
- Chargement des configurations
- Initialisation du routeur
- Gestion des requêtes

```php
<?php
// Définir le chemin de base
define('BASE_PATH', dirname(__DIR__));

// Charger l'autoloader
require BASE_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Déterminer l'environnement
$environment = $_ENV['APP_ENV'] ?? 'production';
require BASE_PATH . '/service/' . $environment . '.php';

// Initialiser la session
require BASE_PATH . '/service/Session.php';
\App\Service\Session::start();

// Charger la configuration globale
require BASE_PATH . '/service/config.php';

// Initialiser le routeur
require BASE_PATH . '/service/Router.php';
$router = new \App\Service\Router();
$router->loadRoutes(require BASE_PATH . '/service/routes.php');

// Dispatcher la requête
$router->dispatch();
```

### 7.4 Gestion des uploads

Le dossier `public/uploads/` stocke les fichiers téléchargés :
- Sous-dossier `rapports/` pour les rapports étudiants
- Structure organisée par année/filière/étudiant
- Permissions restreintes
- Vérification des types de fichiers

---

## 8. Tests et qualité de code

### 8.1 Organisation des tests

#### 8.1.1 Tests backend (tests/backend/)

Tests des contrôleurs et modèles :
- Tests unitaires pour les modèles
- Tests fonctionnels pour les contrôleurs
- Mocks et stubs pour isoler les dépendances

#### 8.1.2 Tests frontend (tests/frontend/)

Tests des vues et du JavaScript :
- Tests de rendu des vues
- Tests d'intégration des composants
- Tests des interactions utilisateur

#### 8.1.3 Tests des services (tests/service/)

Tests des services partagés :
- Tests unitaires pour chaque service
- Tests d'intégration entre services
- Tests de performance

### 8.2 Exécution des tests

#### 8.2.1 Tests unitaires et fonctionnels

```bash
# Exécuter tous les tests
./vendor/bin/phpunit

# Exécuter une suite de tests spécifique
./vendor/bin/phpunit --testsuite=backend

# Exécuter un fichier de test spécifique
./vendor/bin/phpunit tests/backend/controllers/AuthControllerTest.php
```

#### 8.2.2 Tests d'intégration

```bash
# Exécuter les tests d'intégration dans Docker
docker-compose -f docker-compose.dev.yml exec app ./vendor/bin/phpunit --testsuite=integration
```

### 8.3 Intégration continue

Les tests sont exécutés automatiquement dans le pipeline CI :
1. Chaque pull request déclenche les tests unitaires et d'intégration
2. Les tests de vue sont exécutés dans un environnement headless
3. La couverture de code est calculée et rapportée
4. Les problèmes de qualité sont détectés et signalés

---

## 9. Gestion des dépendances

### 9.1 Dépendances PHP (composer.json)

Le fichier `composer.json` définit les dépendances PHP :
- Framework et bibliothèques
- Outils de développement
- Autoloader PSR-4
- Scripts automatisés

```json
{
  "name": "manueld-aho/app-soutenance",
  "description": "Application de gestion des soutenances",
  "type": "project",
  "require": {
    "php": "^8.0",
    "vlucas/phpdotenv": "^5.3",
    "phpmailer/phpmailer": "^6.5",
    "monolog/monolog": "^2.3",
    "league/flysystem": "^2.4"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.5",
    "squizlabs/php_codesniffer": "^3.6",
    "symfony/var-dumper": "^5.3"
  },
  "autoload": {
    "psr-4": {
      "App\\": "."
    }
  },
  "scripts": {
    "test": "phpunit",
    "cs": "phpcs",
    "start": "php -S localhost:8000 -t public"
  }
}
```

### 9.2 Installation des dépendances

```bash
# Installation des dépendances de production
composer install --no-dev --optimize-autoloader

# Installation des dépendances de développement
composer install

# Mettre à jour les dépendances
composer update
```

### 9.3 Verrouillage des versions (composer.lock)

Le fichier `composer.lock` verrouille les versions exactes des dépendances :
- Garantit la cohérence entre environnements
- Commité dans le dépôt Git
- Utilisé lors du déploiement

---

## 10. Configuration et variables d'environnement

### 10.1 Fichier d'exemple (.env.example)

Le fichier `.env.example` sert de modèle pour les variables d'environnement :
```
# Application
APP_ENV=development
APP_DEBUG=true
APP_KEY=base64:votreclésecrete

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=app_soutenance
DB_USERNAME=app_user
DB_PASSWORD=password

# Mail
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=admin@app-soutenance.fr
MAIL_FROM_NAME="App Soutenance"

# Azure Storage (pour les uploads)
AZURE_STORAGE_NAME=appsoutenancestorage
AZURE_STORAGE_KEY=your-storage-key
AZURE_STORAGE_CONTAINER=uploads
```

### 10.2 Utilisation des variables d'environnement

```php
// Charger les variables d'environnement
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Accéder aux variables
$dbHost = $_ENV['DB_HOST'];
$debug = (bool) $_ENV['APP_DEBUG'];
```

### 10.3 Configuration Git (.gitignore)

Le fichier `.gitignore` exclut les fichiers sensibles et temporaires :
```
# Environnement
.env
.env.backup

# Dépendances
/vendor
/node_modules

# Uploads
/public/uploads/*
!/public/uploads/.gitkeep

# Cache
/.phpunit.result.cache
/.php_cs.cache

# Logs
/logs/*
!/logs/.gitkeep

# IDE
/.idea
/.vscode
```

### 10.4 Configuration Docker (.dockerignore)

Le fichier `.dockerignore` exclut les fichiers inutiles pour les images Docker :
```
.git
.github
.gitignore
.dockerignore
.env
.env.example
docker-compose*.yml
vendor
node_modules
tests
README.md
```

---

## 11. Flux de travail complet

### 11.1 Requête HTTP complète

Schéma de traitement d'une requête HTTP de bout en bout :

```
[Navigateur] → [HTTP Request] → [public/index.php] → [Router] → [Middleware] → [Contrôleur] → [Modèle] → [Base de données]
                                                                                   ↓
[Navigateur] ← [HTML Response] ← [Vue] ← [Données de la vue] ←  [Données du modèle]
```

### 11.2 Flux d'authentification

1. L'utilisateur accède à `/login`
2. Le routeur dirige vers `AuthController::showLogin()`
3. La vue `auth/login.php` est affichée
4. L'utilisateur soumet le formulaire à `/login` (POST)
5. Le routeur dirige vers `AuthController::login()`
6. Les credentials sont vérifiés contre la base de données
7. Si valides, la session est créée et l'utilisateur redirigé
8. Si invalides, retour au formulaire avec message d'erreur

### 11.3 Flux de soumission d'un rapport

1. L'étudiant accède à `/etudiant/rapport/soumettre`
2. Le middleware vérifie l'authentification et le type d'utilisateur
3. Le routeur dirige vers `EtudiantController::showSoumettre()`
4. La vue `etudiant/rapport/soumettre.php` est affichée
5. L'étudiant soumet le formulaire avec le fichier
6. Le routeur dirige vers `EtudiantController::soumettre()`
7. Le fichier est validé et uploadé
8. Le modèle `Rapport` enregistre les métadonnées en base
9. L'étudiant est redirigé avec message de confirmation

### 11.4 Cycle de déploiement

1. Le développeur pousse du code sur GitHub
2. GitHub Actions exécute les tests automatisés
3. Si les tests réussissent, GitHub Actions notifie Azure DevOps
4. Azure DevOps lance le pipeline de déploiement
5. L'application est déployée sur l'environnement cible
6. Les migrations de base de données sont exécutées
7. Les tests post-déploiement sont exécutés
8. Les utilisateurs accèdent à la nouvelle version

---

## 12. Maintenance et évolution

### 12.1 Ajout de nouvelles fonctionnalités

Pour ajouter une nouvelle fonctionnalité, suivez ce processus :

1. **Backend**
   - Créez ou modifiez les modèles nécessaires dans `backend/models/`
   - Ajoutez les méthodes nécessaires au contrôleur approprié dans `backend/controllers/`
   - Ajoutez les routes nécessaires dans `service/routes.php`

2. **Frontend**
   - Créez les vues nécessaires dans `frontend/views/`
   - Ajoutez les styles CSS dans `frontend/css/`
   - Ajoutez le JavaScript requis dans `frontend/js/`

3. **Tests**
   - Ajoutez des tests unitaires pour les nouveaux modèles et services
   - Ajoutez des tests fonctionnels pour les nouveaux contrôleurs
   - Ajoutez des tests de vue si nécessaire

### 12.2 Migration de base de données

Pour modifier la structure de la base de données :

1. Créez un script de migration dans `.azure/scripts/migrations/`
2. Testez la migration localement :
   ```bash
   cd .azure/scripts
   ./database-migration.sh dev
   ```
3. Incluez la migration dans le processus de déploiement

### 12.3 Branche du code

Pour contribuer au projet :

1. Créez une branche à partir de `develop` :
   ```bash
   git checkout develop
   git pull
   git checkout -b feature/ma-nouvelle-fonctionnalite
   ```
2. Effectuez vos modifications
3. Exécutez les tests localement
4. Poussez votre branche et créez une pull request
5. Après revue et approbation, la branche sera fusionnée

### 12.4 Documentation

Pour maintenir la documentation :

1. Documentez les fonctionnalités dans le `README.md`
2. Ajoutez des commentaires PHPDoc dans le code
3. Mettez à jour la documentation technique au besoin
