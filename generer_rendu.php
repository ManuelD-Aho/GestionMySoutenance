<?php
/**
 * Générateur d'arborescence de projet PHP
 * Ce script crée automatiquement la structure de dossiers et fichiers vides
 */

class ProjectStructureGenerator {

    private $structure = [
        'node_modules/' => 'dir',
        'Public/' => 'dir',
        'Public/assets/' => 'dir',
        'Public/assets/css/' => 'dir',
        'Public/assets/css/app.css' => 'file',
        'Public/assets/js/' => 'dir',
        'Public/assets/js/app.js' => 'file',
        'Public/assets/images/' => 'dir',
        'Public/assets/images/auth/' => 'dir',
        'Public/assets/images/auth/soutenance1.jpg' => 'file',
        'Public/assets/images/logo/' => 'dir',
        'Public/assets/images/logo/logo.svg' => 'file',
        'Public/assets/images/logo/favicon.ico' => 'file',
        'Public/assets/fonts/' => 'dir',
        'Public/.htaccess' => 'file',
        'Public/index.php' => 'file',
        'src/' => 'dir',
        'src/css/' => 'dir',
        'src/css/components/' => 'dir',
        'src/css/components/_buttons.css' => 'file',
        'src/css/components/_cards.css' => 'file',
        'src/css/components/_forms.css' => 'file',
        'src/css/layout/' => 'dir',
        'src/css/layout/_header.css' => 'file',
        'src/css/layout/_layout.css' => 'file',
        'src/css/layout/_sidebar.css' => 'file',
        'src/css/pages/' => 'dir',
        'src/css/pages/_admin.css' => 'file',
        'src/css/pages/_auth.css' => 'file',
        'src/css/pages/_dashboard.css' => 'file',
        'src/css/input.css' => 'file',
        'src/js/' => 'dir',
        'src/js/modules/' => 'dir',
        'src/js/modules/admin.js' => 'file',
        'src/js/modules/chat.js' => 'file',
        'src/js/modules/dashboard-charts.js' => 'file',
        'src/js/modules/wysiwyg-editor.js' => 'file',
        'src/js/app.js' => 'file',
        'src/Frontend/' => 'dir',
        'src/Frontend/views/' => 'dir',
        'src/Frontend/views/Administration/' => 'dir',
        'src/Frontend/views/Administration/dashboard_admin.php' => 'file',
        'src/Frontend/views/Administration/gestion_academique.php' => 'file',
        'src/Frontend/views/Administration/gestion_annee_academique.php' => 'file',
        'src/Frontend/views/Administration/gestion_configuration.php' => 'file',
        'src/Frontend/views/Administration/_referential_details_panel.php' => 'file',
        'src/Frontend/views/Administration/gestion_habilitations.php' => 'file',
        'src/Frontend/views/Administration/fichiers/' => 'dir',
        'src/Frontend/views/Administration/fichiers/index.php' => 'file',
        'src/Frontend/views/Administration/logs/' => 'dir',
        'src/Frontend/views/Administration/logs/index.php' => 'file',
        'src/Frontend/views/Administration/logs/view.php' => 'file',
        'src/Frontend/views/Administration/queue/' => 'dir',
        'src/Frontend/views/Administration/queue/index.php' => 'file',
        'src/Frontend/views/Administration/referentiels/' => 'dir',
        'src/Frontend/views/Administration/referentiels/index.php' => 'file',
        'src/Frontend/views/Administration/referentiels/list_items.php' => 'file',
        'src/Frontend/views/Administration/reporting/' => 'dir',
        'src/Frontend/views/Administration/reporting/index.php' => 'file',
        'src/Frontend/views/Administration/reporting/view.php' => 'file',
        'src/Frontend/views/Administration/supervision.php' => 'file',
        'src/Frontend/views/Administration/transition/' => 'dir',
        'src/Frontend/views/Administration/transition/index.php' => 'file',
        'src/Frontend/views/Administration/utilisateurs/' => 'dir',
        'src/Frontend/views/Administration/utilisateurs/index.php' => 'file',
        'src/Frontend/views/Administration/utilisateurs/form.php' => 'file',
        'src/Frontend/views/Auth/' => 'dir',
        'src/Frontend/views/Auth/auth.php' => 'file',
        'src/Frontend/views/Commission/' => 'dir',
        'src/Frontend/views/Commission/dashboard_commission.php' => 'file',
        'src/Frontend/views/Commission/workflow_commission.php' => 'file',
        'src/Frontend/views/Commission/pv_editor.php' => 'file',
        'src/Frontend/views/Etudiant/' => 'dir',
        'src/Frontend/views/Etudiant/dashboard_etudiant.php' => 'file',
        'src/Frontend/views/Etudiant/profil_etudiant.php' => 'file',
        'src/Frontend/views/Etudiant/choix_modele.php' => 'file',
        'src/Frontend/views/Etudiant/redaction_rapport.php' => 'file',
        'src/Frontend/views/PersonnelAdministratif/' => 'dir',
        'src/Frontend/views/PersonnelAdministratif/dashboard_personnel.php' => 'file',
        'src/Frontend/views/PersonnelAdministratif/gestion_conformite.php' => 'file',
        'src/Frontend/views/PersonnelAdministratif/form_conformite.php' => 'file',
        'src/Frontend/views/PersonnelAdministratif/gestion_scolarite.php' => 'file',
        'src/Frontend/views/PersonnelAdministratif/_student_details_panel.php' => 'file',
        'src/Frontend/views/common/' => 'dir',
        'src/Frontend/views/common/chat_interface.php' => 'file',
        'src/Frontend/views/common/dashboard.php' => 'file',
        'src/Frontend/views/common/notifications_panel.php' => 'file',
        'src/Frontend/views/common/profile.php' => 'file',
        'src/Frontend/views/errors/' => 'dir',
        'src/Frontend/views/errors/403.php' => 'file',
        'src/Frontend/views/errors/404.php' => 'file',
        'src/Frontend/views/errors/500.php' => 'file',
        'src/Frontend/views/errors/503.php' => 'file',
        'src/Frontend/views/home/' => 'dir',
        'src/Frontend/views/home/index.php' => 'file',
        'src/Frontend/views/home/about.php' => 'file',
        'src/Frontend/views/layout/' => 'dir',
        'src/Frontend/views/layout/_flash_messages.php' => 'file',
        'src/Frontend/views/layout/header.php' => 'file',
        'src/Frontend/views/layout/menu.php' => 'file',
        'src/Frontend/views/layout/app.php' => 'file',
        'src/Frontend/views/layout/layout_auth.php' => 'file',
    ];

    private $basePath;
    private $createdDirs = [];
    private $createdFiles = [];
    private $errors = [];

    public function __construct($basePath = '.') {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Génère toute la structure de projet
     */
    public function generate() {
        echo "🚀 Génération de la structure de projet...\n";
        echo "📁 Répertoire de base: " . $this->basePath . "\n\n";

        foreach ($this->structure as $path => $type) {
            $fullPath = $this->basePath . '/' . $path;

            if ($type === 'dir') {
                $this->createDirectory($fullPath);
            } else {
                $this->createFile($fullPath);
            }
        }

        $this->showSummary();
    }

    /**
     * Crée un répertoire
     */
    private function createDirectory($path) {
        if (!is_dir($path)) {
            if (mkdir($path, 0755, true)) {
                $this->createdDirs[] = $path;
                echo "📁 Dossier créé: " . $this->getRelativePath($path) . "\n";
            } else {
                $this->errors[] = "Impossible de créer le dossier: " . $path;
                echo "❌ Erreur: Impossible de créer le dossier: " . $this->getRelativePath($path) . "\n";
            }
        } else {
            echo "📁 Dossier existe déjà: " . $this->getRelativePath($path) . "\n";
        }
    }

    /**
     * Crée un fichier vide
     */
    private function createFile($path) {
        // Crée le répertoire parent s'il n'existe pas
        $dir = dirname($path);
        if (!is_dir($dir)) {
            $this->createDirectory($dir);
        }

        if (!file_exists($path)) {
            if (touch($path)) {
                $this->createdFiles[] = $path;
                echo "📄 Fichier créé: " . $this->getRelativePath($path) . "\n";
            } else {
                $this->errors[] = "Impossible de créer le fichier: " . $path;
                echo "❌ Erreur: Impossible de créer le fichier: " . $this->getRelativePath($path) . "\n";
            }
        } else {
            echo "📄 Fichier existe déjà: " . $this->getRelativePath($path) . "\n";
        }
    }

    /**
     * Retourne le chemin relatif pour l'affichage
     */
    private function getRelativePath($path) {
        return str_replace($this->basePath . '/', '', $path);
    }

    /**
     * Affiche un résumé de la génération
     */
    private function showSummary() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 RÉSUMÉ DE LA GÉNÉRATION\n";
        echo str_repeat("=", 50) . "\n";

        echo "✅ Dossiers créés: " . count($this->createdDirs) . "\n";
        echo "✅ Fichiers créés: " . count($this->createdFiles) . "\n";

        if (!empty($this->errors)) {
            echo "❌ Erreurs: " . count($this->errors) . "\n";
            echo "\nDétails des erreurs:\n";
            foreach ($this->errors as $error) {
                echo "  - " . $error . "\n";
            }
        }

        echo "\n🎉 Génération terminée!\n";

        // Affiche quelques conseils
        echo "\n💡 Conseils:\n";
        echo "  - Les fichiers sont vides, ajoutez votre contenu selon vos besoins\n";
        echo "  - Pensez à configurer vos permissions selon votre environnement\n";
        echo "  - Le dossier 'node_modules' est créé mais sera géré par npm/yarn\n";
        echo "  - Les fichiers dans 'Public/assets' sont destinés aux fichiers compilés\n";
    }

    /**
     * Supprime toute la structure créée (utile pour les tests)
     */
    public function clean() {
        echo "🧹 Nettoyage de la structure...\n";

        // Supprime les fichiers en premier
        foreach (array_reverse($this->createdFiles) as $file) {
            if (file_exists($file)) {
                unlink($file);
                echo "🗑️  Fichier supprimé: " . $this->getRelativePath($file) . "\n";
            }
        }

        // Supprime les dossiers (en ordre inverse)
        foreach (array_reverse($this->createdDirs) as $dir) {
            if (is_dir($dir) && count(scandir($dir)) == 2) { // Dossier vide
                rmdir($dir);
                echo "🗑️  Dossier supprimé: " . $this->getRelativePath($dir) . "\n";
            }
        }

        echo "✅ Nettoyage terminé!\n";
    }
}

// Utilisation du script
try {
    // Vous pouvez changer le chemin de base ici
    $basePath = './mon-projet'; // ou '.' pour le répertoire courant

    $generator = new ProjectStructureGenerator($basePath);
    $generator->generate();

    // Décommentez cette ligne si vous voulez nettoyer après test
    // $generator->clean();

} catch (Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
}
?>