<?php
// src/Frontend/views/Administration/ConfigSysteme/notification_configuration.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour les événements et la matrice de diffusion
// En production, ces données proviendraient du contrôleur (NotificationConfigurationController)
// et des services (ServiceNotificationConfigurationInterface).
//
//

$evenements_notifications = $data['evenements_notifications'] ?? [
    ['id' => 1, 'code' => 'RAPPORT_SOUMIS', 'libelle' => 'Rapport de soutenance soumis'],
    ['id' => 2, 'code' => 'RAPPORT_CONFORME', 'libelle' => 'Rapport conforme aux règles'],
    ['id' => 3, 'code' => 'RAPPORT_NON_CONFORME', 'libelle' => 'Rapport non conforme'],
    ['id' => 4, 'code' => 'PV_VALIDE', 'libelle' => 'Procès-verbal de soutenance validé'],
    ['id' => 5, 'code' => 'COMPTE_ACTIVE', 'libelle' => 'Compte utilisateur activé'],
    ['id' => 6, 'code' => 'CHANGEMENT_STATUT_PERSONNEL', 'libelle' => 'Changement statut personnel'],
    ['id' => 7, 'code' => 'NOUVELLE_RECLAMATION', 'libelle' => 'Nouvelle réclamation étudiante'],
];

// Matrice de diffusion (événement_id => [destinataires, canaux])
// Ex: ['1' => ['ETUDIANT' => ['interne', 'email'], 'AGENT_CONFORMITE' => ['interne']]]
$matrice_diffusion_existante = $data['matrice_diffusion_existante'] ?? [
    '1' => ['ETUDIANT' => ['interne', 'email'], 'AGENT_CONFORMITE' => ['interne']],
    '2' => ['ETUDIANT' => ['interne', 'email']],
    '3' => ['ETUDIANT' => ['interne', 'email'], 'AGENT_CONFORMITE' => ['interne']],
    '4' => ['ETUDIANT' => ['interne', 'email'], 'COMMISSION' => ['interne'], 'ADMIN' => ['interne']],
    '5' => ['ETUDIANT' => ['interne', 'email']],
    '6' => ['PERSONNEL_ADMIN' => ['interne'], 'ENSEIGNANT' => ['interne']],
    '7' => ['PERSONNEL_ADMIN' => ['interne', 'email'], 'ADMIN' => ['interne']],
];

$roles_disponibles = $data['roles_disponibles'] ?? [
    ['code' => 'ETUDIANT', 'libelle' => 'Étudiant'],
    ['code' => 'ENSEIGNANT', 'libelle' => 'Enseignant'],
    ['code' => 'COMMISSION', 'libelle' => 'Membre Commission'],
    ['code' => 'AGENT_CONFORMITE', 'libelle' => 'Agent de Conformité'],
    ['code' => 'RESPONSABLE_SCOLARITE', 'libelle' => 'Responsable Scolarité'],
    ['code' => 'ADMIN', 'libelle' => 'Administrateur Système'],
];

$canaux_disponibles = ['interne', 'email'];

$notifications_critiques = $data['notifications_critiques'] ?? [
    'COMPTE_BLOQUE',
    'PASSWORD_CHANGE_REQUIRED',
    'RAPPORT_NON_CONFORME',
    'PV_VALIDE',
]; // Ces notifications ne peuvent pas être désactivées par l'utilisateur

?>

<div class="admin-module-container">
    <h1 class="admin-title">Configuration des Notifications</h1>

    <section class="section-matrice admin-card">
        <h2 class="section-title">Matrice de Diffusion des Notifications</h2>
        <p class="section-description">Définissez pour chaque événement quels rôles sont notifiés et via quels canaux.</p>

        <?php foreach ($evenements_notifications as $evenement): ?>
            <div class="notification-event-block">
                <h3 class="event-title"><?= e($evenement['libelle']); ?> (<code><?= e($evenement['code']); ?></code>)</h3>
                <form class="form-diffusion" data-event-id="<?= e($evenement['id']); ?>">
                    <input type="hidden" name="event_code" value="<?= e($evenement['code']); ?>">
                    <div class="roles-channels-grid">
                        <?php foreach ($roles_disponibles as $role): ?>
                            <div class="role-item">
                                <span class="role-label"><?= e($role['libelle']); ?></span>
                                <div class="channels-checkboxes">
                                    <?php foreach ($canaux_disponibles as $canal):
                                        $isChecked = isset($matrice_diffusion_existante[$evenement['id']][$role['code']]) && in_array($canal, $matrice_diffusion_existante[$evenement['id']][$role['code']]);
                                        ?>
                                        <label>
                                            <input type="checkbox" name="channels[<?= e($role['code']); ?>][]" value="<?= e($canal); ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                            <?= e(ucfirst($canal)); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary-blue btn-save-diffusion">
                        <span class="material-icons">save</span>
                        Sauvegarder Diffusion
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="section-critiques admin-card mt-xl">
        <h2 class="section-title">Notifications Critiques (Non désactivables par l'utilisateur)</h2>
        <p class="section-description">Ces notifications sont considérées comme essentielles et les utilisateurs ne peuvent pas les désactiver depuis leurs préférences personnelles.</p>
        <?php if (!empty($notifications_critiques)): ?>
            <ul class="critical-notifications-list">
                <?php foreach ($notifications_critiques as $critique_code):
                    // Trouver le libellé correspondant
                    $libelle = array_values(array_filter($evenements_notifications, fn($e) => $e['code'] === $critique_code));
                    $libelle = !empty($libelle) ? $libelle[0]['libelle'] : $critique_code;
                    ?>
                    <li>
                        <span class="material-icons info-icon">info</span>
                        <strong><?= e($libelle); ?></strong> (<code><?= e($critique_code); ?></code>)
                        <span class="status-indicator status-critical">Critique</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification critique définie pour le moment.</p>
        <?php endif; ?>
    </section>

    <section class="section-preferences admin-card mt-xl">
        <h2 class="section-title">Paramètres des Préférences Utilisateur</h2>
        <p class="section-description">Gérez les options de personnalisation que les utilisateurs peuvent définir pour leurs notifications (ex: résumé quotidien).</p>
        <form action="/admin/config/notification-preferences-settings" method="POST">
            <div class="form-group">
                <label for="allow_daily_summary">Permettre le résumé quotidien par email :</label>
                <input type="checkbox" id="allow_daily_summary" name="allow_daily_summary" checked>
                <small class="form-help">Si coché, les utilisateurs pourront activer un email de résumé quotidien de leurs notifications.</small>
            </div>
            <div class="form-group">
                <label for="default_channel_internal_only">Canal par défaut pour les nouvelles notifications non critiques (uniquement interne si possible) :</label>
                <input type="checkbox" id="default_channel_internal_only" name="default_channel_internal_only">
                <small class="form-help">Si coché, les nouvelles notifications non critiques seront par défaut envoyées uniquement en interne, laissant l'email comme option manuelle.</small>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">save</span>
                Sauvegarder les Paramètres Globaux
            </button>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour soumettre les formulaires de diffusion de notification via AJAX
        document.querySelectorAll('.form-diffusion').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Empêche la soumission classique du formulaire

                const eventId = this.dataset.eventId;
                const eventCode = this.querySelector('input[name="event_code"]').value;
                const checkboxes = this.querySelectorAll('input[type="checkbox"]:checked');
                const channels = {};

                checkboxes.forEach(checkbox => {
                    const roleCode = checkbox.name.match(/\[(.*?)\]/)[1];
                    if (!channels[roleCode]) {
                        channels[roleCode] = [];
                    }
                    channels[roleCode].push(checkbox.value);
                });

                const formData = {
                    event_id: eventId,
                    event_code: eventCode,
                    channels: channels
                };

                console.log('Soumission de la configuration pour l\'événement :', formData);

                // Simuler un appel AJAX (remplacez par votre logique fetch/XMLHttpRequest)
                // Exemple avec fetch API
                fetch('/admin/config/notification-matrix/update', { // Ajustez cette URL à votre route de contrôleur
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': 'votre_token_csrf_ici' // Si vous utilisez CSRF
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Configuration de diffusion sauvegardée avec succès !');
                            // Actualiser la page ou mettre à jour l'UI si nécessaire
                            // window.location.reload();
                        } else {
                            alert('Erreur lors de la sauvegarde : ' + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la requête AJAX:', error);
                        alert('Une erreur de communication est survenue.');
                    });
            });
        });

        // Logique pour la soumission du formulaire des paramètres globaux de préférences
        const globalPreferencesForm = document.querySelector('section.section-preferences form');
        if (globalPreferencesForm) {
            globalPreferencesForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                const data = {};
                formData.forEach((value, key) => {
                    // Pour les checkboxes, le navigateur n'envoie la valeur que si cochée.
                    // Ici, nous voulons un booléen clair.
                    data[key] = (value === 'on' || value === 'true');
                });
                console.log('Soumission des paramètres globaux de préférences :', data);

                fetch('/admin/config/notification-preferences-settings/update', { // Ajustez l'URL
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Paramètres globaux des préférences sauvegardés avec succès !');
                        } else {
                            alert('Erreur lors de la sauvegarde : ' + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la requête AJAX:', error);
                        alert('Une erreur de communication est survenue.');
                    });
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour notification_configuration.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1200px;
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

    .section-description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-lg);
    }

    /* Styles spécifiques à la matrice de diffusion */
    .notification-event-block {
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-md);
        background-color: var(--primary-white); /* Plus clair que le fond de la carte */
        box-shadow: var(--shadow-sm);
    }

    .event-title {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-md);
        font-weight: var(--font-weight-semibold);
    }

    .event-title code {
        background-color: var(--primary-gray-light);
        padding: 0.1em 0.4em;
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .form-diffusion {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .roles-channels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .role-item {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-sm);
        padding: var(--spacing-sm);
    }

    .role-label {
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        display: block;
    }

    .channels-checkboxes label {
        display: block; /* Chaque checkbox sur une nouvelle ligne */
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-xs);
    }

    .channels-checkboxes input[type="checkbox"] {
        margin-right: var(--spacing-xs);
    }

    .btn-save-diffusion {
        align-self: flex-end; /* Aligne le bouton à droite */
        margin-top: var(--spacing-md);
    }

    /* Section Notifications Critiques */
    .critical-notifications-list {
        list-style: none;
        padding: 0;
    }

    .critical-notifications-list li {
        background-color: var(--primary-white);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        margin-bottom: var(--spacing-sm);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        box-shadow: var(--shadow-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
    }

    .critical-notifications-list li strong {
        color: var(--primary-blue-dark);
    }

    .critical-notifications-list li code {
        background-color: var(--primary-gray-light);
        padding: 0.1em 0.4em;
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .info-icon {
        color: var(--info-color);
        font-size: var(--font-size-xl);
    }

    .status-critical {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        margin-left: auto; /* Aligne à droite */
    }

    /* Section Paramètres Préférences Utilisateur */
    .section-preferences .form-group {
        flex-direction: row; /* Pour les checkboxes */
        align-items: center;
        gap: var(--spacing-md);
    }

    .section-preferences .form-group label {
        margin-bottom: 0;
        flex-grow: 1; /* Permet à l'étiquette de prendre plus de place */
    }

    .section-preferences .form-group input[type="checkbox"] {
        width: auto;
        margin: 0;
    }

    .form-help {
        flex-basis: 100%; /* S'assure que le texte d'aide prend toute la largeur */
        margin-top: var(--spacing-xs);
    }

    /* Boutons - réutilisation */
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
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
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
</style>