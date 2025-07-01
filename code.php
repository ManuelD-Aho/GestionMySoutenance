<?php

/**
 * Script pour générer l'arborescence complète du projet "GestionMySoutenance".
 * Crée tous les dossiers et fichiers vides nécessaires.
 * USAGE: php generate_project_structure.php
 */

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

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
    ├── generate_project_structure.php
    ├── mysoutenance.sql
    ├── package.json
    ├── php.ini
    ├── render.yaml
    ├── seeds.php
    ├── .dockerignore
    ├── .env.dev
    ├── .env.example
    ├── .env.prod
    ├── .gitignore
    ├── docker/
    │   ├── apache/
    │   │   └── apache-vhost.conf
    │   ├── nginx/
    │   │   └── conf.d/
    │   │       └── default.conf
    │   └── php/
    │       └── php.ini
    ├── node_modules/
    ├── Public/
    │   ├── .htaccess
    │   ├── index.php
    │   ├── test-email.php
    │   └── assets/
    │       ├── css/
    │       │   └── app.css
    │       └── js/
    │           ├── app.js
    │           ├── auth.js
    │           ├── editor-pv.js
    │           ├── editor-rapport.js
    │           ├── gestion-referentiels.js
    │           └── gestion-utilisateurs.js
    ├── routes/
    │   └── web.php
    ├── src/
    │   ├── Backend/
    │   │   ├── Controller/
    │   │   │   ├── Administration/
    │   │   │   │   ├── AdminDashboardController.php
    │   │   │   │   ├── ConfigurationController.php
    │   │   │   │   ├── SupervisionController.php
    │   │   │   │   └── UtilisateurController.php
    │   │   │   ├── Commission/
    │   │   │   │   ├── CommissionDashboardController.php
    │   │   │   │   └── WorkflowCommissionController.php
    │   │   │   ├── Etudiant/
    │   │   │   │   ├── EtudiantDashboardController.php
    │   │   │   │   ├── ProfilEtudiantController.php
    │   │   │   │   └── RapportController.php
    │   │   │   ├── PersonnelAdministratif/
    │   │   │   │   ├── PersonnelDashboardController.php
    │   │   │   │   └── ScolariteController.php
    │   │   │   ├── AssetController.php
    │   │   │   ├── AuthentificationController.php
    │   │   │   ├── BaseController.php
    │   │   │   ├── DashboardController.php
    │   │   │   └── HomeController.php
    │   │   ├── Exception/
    │   │   │   ├── AuthenticationException.php
    │   │   │   ├── CompteBloqueException.php
    │   │   │   ├── CompteNonValideException.php
    │   │   │   ├── DoublonException.php
    │   │   │   ├── ElementNonTrouveException.php
    │   │   │   ├── EmailException.php
    │   │   │   ├── EmailNonValideException.php
    │   │   │   ├── IdentifiantsInvalidesException.php
    │   │   │   ├── ModeleNonTrouveException.php
    │   │   │   ├── MotDePasseInvalideException.php
    │   │   │   ├── OperationImpossibleException.php
    │   │   │   ├── PermissionException.php
    │   │   │   ├── TokenExpireException.php
    │   │   │   ├── TokenInvalideException.php
    │   │   │   ├── UtilisateurNonTrouveException.php
    │   │   │   └── ValidationException.php
    │   │   ├── Model/
    │   │   │   ├── BaseModel.php
    │   │   │   ├── Delegation.php
    │   │   │   ├── GenericModel.php
    │   │   │   ├── HistoriqueMotDePasse.php
    │   │   │   ├── RapportEtudiant.php
    │   │   │   ├── Reclamation.php
    │   │   │   ├── Sessions.php
    │   │   │   └── Utilisateur.php
    │   │   ├── Service/
    │   │   │   ├── Communication/
    │   │   │   │   ├── ServiceCommunication.php
    │   │   │   │   └── ServiceCommunicationInterface.php
    │   │   │   ├── Document/
    │   │   │   │   ├── ServiceDocument.php
    │   │   │   │   └── ServiceDocumentInterface.php
    │   │   │   ├── ParcoursAcademique/
    │   │   │   │   ├── ServiceParcoursAcademique.php
    │   │   │   │   └── ServiceParcoursAcademiqueInterface.php
    │   │   │   ├── Securite/
    │   │   │   │   ├── ServiceSecurite.php
    │   │   │   │   └── ServiceSecuriteInterface.php
    │   │   │   ├── Supervision/
    │   │   │   │   ├── ServiceSupervision.php
    │   │   │   │   └── ServiceSupervisionInterface.php
    │   │   │   ├── Systeme/
    │   │   │   │   ├── ServiceSysteme.php
    │   │   │   │   └── ServiceSystemeInterface.php
    │   │   │   ├── Utilisateur/
    │   │   │   │   ├── ServiceUtilisateur.php
    │   │   │   │   └── ServiceUtilisateurInterface.php
    │   │   │   └── WorkflowSoutenance/
    │   │   │       ├── ServiceWorkflowSoutenance.php
    │   │   │       └── ServiceWorkflowSoutenanceInterface.php
    │   │   └── Util/
    │   │       ├── DatabaseSessionHandler.php
    │   │       └── FormValidator.php
    │   ├── Config/
    │   │   ├── Container.php
    │   │   └── Database.php
    │   └── Frontend/
    │       └── views/
    │           ├── Administration/
    │           │   ├── dashboard_admin.php
    │           │   ├── gestion_modeles_rapport.php
    │           │   ├── gestion_referentiels.php
    │           │   ├── gestion_utilisateurs.php
    │           │   └── supervision.php
    │           ├── Auth/
    │           │   ├── auth.php
    │           │   └── layout_auth.php
    │           ├── Commission/
    │           │   ├── dashboard_commission.php
    │           │   ├── gestion_pv.php
    │           │   └── workflow_commission.php
    │           ├── common/
    │           │   ├── dashboard.php
    │           │   ├── header.php
    │           │   ├── menu.php
    │           │   └── notifications_panel.php
    │           ├── errors/
    │           │   ├── 403.php
    │           │   ├── 404.php
    │           │   ├── 405.php
    │           │   └── 500.php
    │           ├── Etudiant/
    │           │   ├── dashboard_etudiant.php
    │           │   ├── profil_etudiant.php
    │           │   └── redaction_rapport.php
    │           ├── layout/
    │           │   └── app.php
    │           └── PersonnelAdministratif/
    │               ├── dashboard_personnel.php
    │               └── gestion_scolarite.php
    └── templates/
        ├── pdf/
        │   ├── attestation_scolarite.html
        │   ├── bulletin_notes.html
        │   └── pv_validation.html
        └── rapport/
            ├── modele_simple.json
            └── modele_detaille.json
EOT;

function generateStructure(string $structure) {
    $lines = explode("\n", $structure);
    $baseDir = '';

    // Récupérer le nom du dossier racine de la première ligne
    if (preg_match('/└── (.*?)\//', $lines[0], $matches)) {
        $baseDir = trim($matches[1]);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
            echo "Créé le dossier racine : $baseDir\n";
        }
    } else {
        echo "Erreur : Impossible de déterminer le dossier racine.\n";
        return;
    }

    $pathStack = [$baseDir];

    // Commencer à partir de la deuxième ligne
    for ($i = 1; $i < count($lines); $i++) {
        $line = rtrim($lines[$i]);
        if (trim($line) === '') continue;

        // Déterminer la profondeur en se basant sur l'indentation
        preg_match('/^([\s│├└─]*)/u', $line, $prefixMatch);
        // Chaque niveau d'indentation est de 4 caractères (ex: "│   " ou "    ")
        $depth = intval(mb_strlen($prefixMatch[0], 'UTF-8') / 4);

        // Nettoyer le nom du fichier/dossier
        $name = preg_replace('/^[\s│├└─]+/u', '', $line);
        $name = trim($name);

        // Ignorer les dossiers spéciaux
        if ($name === '...' || $name === 'node_modules/') {
            continue;
        }

        // Déterminer si c'est un dossier ou un fichier
        $isDir = substr($name, -1) === '/';
        $itemName = $isDir ? rtrim($name, '/') : $name;

        // Ajuster la pile pour qu'elle corresponde au parent de l'élément actuel
        // La profondeur (depth) est relative au dossier racine.
        // $pathStack[0] est le dossier racine, donc la taille de la pile doit être $depth + 1
        $pathStack = array_slice($pathStack, 0, $depth + 1);

        // Construire le chemin complet
        $parentPath = implode(DIRECTORY_SEPARATOR, $pathStack);
        $fullPath = $parentPath . DIRECTORY_SEPARATOR . $itemName;

        try {
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
        } catch (\Exception $e) {
            echo "Exception : " . $e->getMessage() . " pour le chemin " . $fullPath . "\n";
        }
    }
    echo "\nArborescence générée avec succès dans le dossier '$baseDir'!\n";
}

generateStructure($structure);