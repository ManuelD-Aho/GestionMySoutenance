<?php
// src/Frontend/views/Administration/Supervision/maintenance.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour l'état des opérations de maintenance
// En production, ces données proviendraient du contrôleur SupervisionController.
//

$maintenance_status = $data['maintenance_status'] ?? [
    'derniere_sauvegarde' => ['date' => '2025-06-29 02:00:00', 'statut' => 'Succès'],
    'derniere_restauration' => ['date' => null, 'statut' => 'N/A'],
    'version_actuelle' => 'v1.0.3',
    'derniere_mise_a_jour' => '2025-06-15',
    'cache_clear_date' => '2025-06-30 09:00:00',
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Outils de Maintenance Système</h1>

    <section class="section-database-maintenance admin-card">
        <h2 class="section-title">Maintenance de la Base de Données</h2>
        <div class="maintenance-block">
            <h3>Sauvegarde de la Base de Données</h3>
            <p class="description">Crée une copie de sauvegarde complète de la base de données actuelle.</p>
            <p class="status-info">Dernière sauvegarde : <strong class="<?= $maintenance_status['derniere_sauvegarde']['statut'] === 'Succès' ? 'text-green' : 'text-red'; ?>"><?= e($maintenance_status['derniere_sauvegarde']['statut']); ?></strong> (<?= $maintenance_status['derniere_sauvegarde']['date'] ? e(date('d/m/Y H:i', strtotime($maintenance_status['derniere_sauvegarde']['date']))) : 'Jamais'; ?>)</p>
            <button id="backupDbBtn" class="btn btn-primary-green" onclick="confirmAction('Sauvegarde de la base de données', 'Êtes-vous sûr de vouloir lancer une sauvegarde complète de la base de données ? Cela peut prendre quelques instants.', '/admin/supervision/maintenance/backup-db');">
                <span class="material-icons">cloud_download</span> Lancer la Sauvegarde
            </button>
        </div>

        <div class="maintenance-block mt-xl">
            <h3>Restauration de la Base de Données</h3>
            <p class="description warning-text">
                <span class="material-icons warning-icon">warning_amber</span>
                La restauration de la base de données écrasera toutes les données actuelles. Utilisez avec extrême prudence.
            </p>
            <p class="status-info">Dernière restauration : <strong><?= e($maintenance_status['derniere_restauration']['statut']); ?></strong> (<?= $maintenance_status['derniere_restauration']['date'] ? e(date('d/m/Y H:i', strtotime($maintenance_status['derniere_restauration']['date']))) : 'Jamais'; ?>)</p>
            <form id="restoreDbForm" action="/admin/supervision/maintenance/restore-db" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="backup_file">Fichier de sauvegarde (.sql) :</label>
                    <input type="file" id="backup_file" name="backup_file" accept=".sql" required>
                    <small class="form-help">Sélectionnez un fichier de sauvegarde SQL généré précédemment.</small>
                </div>
                <button type="submit" class="btn btn-accent-red" onclick="return confirm('ATTENTION : Êtes-vous ABSOLUMENT sûr de vouloir RESTAURER la base de données ? TOUTES les données actuelles seront PERDUES et remplacées par celles de la sauvegarde.');">
                    <span class="material-icons">restore</span> Restaurer la Base de Données
                </button>
            </form>
        </div>
    </section>

    <section class="section-app-maintenance admin-card mt-xl">
        <h2 class="section-title">Maintenance de l'Application</h2>
        <div class="maintenance-block">
            <h3>Mise à Jour de l'Application</h3>
            <p class="description">Déploie la dernière version de l'application ou applique des correctifs.</p>
            <p class="status-info">Version actuelle : <strong><?= e($maintenance_status['version_actuelle']); ?></strong></p>
            <p class="status-info">Dernière mise à jour : <?= e(date('d/m/Y', strtotime($maintenance_status['derniere_mise_a_jour']))); ?></p>
            <button id="deployAppBtn" class="btn btn-primary-blue" onclick="confirmAction('Mise à Jour de l\'Application', 'Lancer le déploiement de la nouvelle version de l\'application ? Le système peut être indisponible brièvement.', '/admin/supervision/maintenance/deploy-app');">
                <span class="material-icons">update</span> Déployer/Mettre à Jour
            </button>
        </div>

        <div class="maintenance-block mt-xl">
            <h3>Vider le Cache Système</h3>
            <p class="description">Supprime les fichiers de cache de l'application pour forcer une reconstruction des données.</p>
            <p class="status-info">Dernier vidage du cache : <?= $maintenance_status['cache_clear_date'] ? e(date('d/m/Y H:i', strtotime($maintenance_status['cache_clear_date']))) : 'Jamais'; ?></p>
            <button id="clearCacheBtn" class="btn btn-secondary-gray" onclick="confirmAction('Vider le Cache', 'Êtes-vous sûr de vouloir vider le cache système ? Cela peut légèrement ralentir la première requête après le vidage.', '/admin/supervision/maintenance/clear-cache');">
                <span class="material-icons">cached</span> Vider le Cache
            </button>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction générique pour confirmer les actions critiques
        window.confirmAction = function(title, message, actionUrl) {
            if (confirm(message)) {
                // Dans un cas réel, vous feriez une requête AJAX POST ici
                // pour déclencher l'action sur le serveur, puis afficher un message de succès/erreur.
                // Pour la démonstration, nous simulons.
                console.log(`Action "${title}" déclenchée vers: ${actionUrl}`);
                alert(`Action "${title}" lancée ! (Vérifiez les logs pour le statut)`);
                // Simuler une requête POST
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': 'votre_token_csrf_ici' // Si vous utilisez CSRF
                    },
                    body: JSON.stringify({}) // Corps vide ou données spécifiques si besoin
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recharger la page pour refléter le nouvel état
                            window.location.reload();
                        } else {
                            alert('Erreur lors de l\'exécution de l\'action : ' + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur réseau/server:', error);
                        alert('Une erreur de communication est survenue.');
                    });
            }
            return false; // Empêche la soumission du formulaire si non-AJAX
        };

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour maintenance.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px;
        margin: var(--spacing-xl) auto;
    }

    .admin-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
    }

    .admin-card {
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .maintenance-block {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-md); /* Espace entre les blocs */
        box-shadow: var(--shadow-sm);
    }

    .maintenance-block h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
    }

    .maintenance-block .description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-md);
    }

    .maintenance-block .status-info {
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        margin-bottom: var(--spacing-md);
    }

    .status-info strong.text-green { color: var(--primary-green-dark); }
    .status-info strong.text-red { color: var(--accent-red-dark); }

    .warning-text {
        color: var(--accent-red-dark);
        font-weight: var(--font-weight-medium);
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
    }
    .warning-text .warning-icon {
        font-size: var(--font-size-xl);
        color: var(--accent-yellow-dark);
    }

    /* Formulaires de restauration */
    .maintenance-block form {
        margin-top: var(--spacing-md);
        padding-top: var(--spacing-md);
        border-top: 1px dashed var(--border-light);
    }

    .form-group {
        margin-bottom: var(--spacing-md);
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="file"] {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .form-group input[type="file"]:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }


    /* Boutons - réutilisation des styles existants */
    .btn {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
        text-decoration: none;
    }

    .btn-primary-green {
        color: var(--text-white);
        background-color: var(--primary-green);
    }

    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-accent-red {
        color: var(--text-white);
        background-color: var(--accent-red);
    }

    .btn-accent-red:hover {
        background-color: var(--accent-red-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }

    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }