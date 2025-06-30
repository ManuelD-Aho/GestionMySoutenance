<?php
// src/Frontend/views/Administration/Supervision/journaux_audit.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les journaux d'audit (proviennent du contrôleur LoggerController ou SupervisionController)
// Ces données sont des exemples pour structurer la vue.
//
//
// (modèle pour les logs d'audit)

$journaux_audit = $data['journaux_audit'] ?? [
    ['id' => 1, 'numero_utilisateur' => 'ADM-2025-0001', 'id_action' => 'CONNEXION_REUSSIE', 'date_action' => '2025-06-30 14:00:00', 'type_entite_concernee' => 'Utilisateur', 'id_entite_concernee' => 'ADM-2025-0001', 'details_action' => 'Connexion réussie depuis IP 192.168.1.10', 'adresse_ip' => '192.168.1.10'],
    ['id' => 2, 'numero_utilisateur' => 'ETU-2025-0123', 'id_action' => 'RAPPORT_SOUMIS', 'date_action' => '2025-06-30 13:45:00', 'type_entite_concernee' => 'Rapport', 'id_entite_concernee' => 'RAP-2025-0045', 'details_action' => 'Rapport "Mon stage en entreprise X" soumis', 'adresse_ip' => '10.0.0.5'],
    ['id' => 3, 'numero_utilisateur' => 'AGENT-CONF-001', 'id_action' => 'RAPPORT_CONFORMITE_NON_CONFORME', 'date_action' => '2025-06-30 11:00:00', 'type_entite_concernee' => 'Rapport', 'id_entite_concernee' => 'RAP-2025-0040', 'details_action' => 'Non conforme: bibliographie non formatée', 'adresse_ip' => '192.168.1.20'],
    ['id' => 4, 'numero_utilisateur' => 'ADM-2025-0001', 'id_action' => 'CREATION_GROUPE_UTILISATEUR', 'date_action' => '2025-06-29 09:30:00', 'type_entite_concernee' => 'GroupeUtilisateur', 'id_entite_concernee' => 'GRP_TEMPORAIRE', 'details_action' => 'Création du groupe "Utilisateurs temporaires"', 'adresse_ip' => '192.168.1.10'],
    ['id' => 5, 'numero_utilisateur' => 'COMMISSION-005', 'id_action' => 'VOTE_RAPPORT', 'date_action' => '2025-06-28 16:10:00', 'type_entite_concernee' => 'Rapport', 'id_entite_concernee' => 'RAP-2025-0030', 'details_action' => 'Vote: Approuvé sous réserve', 'adresse_ip' => '10.0.0.15'],
];

// Options de filtrage (ces données pourraient venir d'un référentiel ou être des options fixes)
$types_action_disponibles = $data['types_action_disponibles'] ?? [
    ['code' => 'ALL', 'libelle' => 'Toutes les actions'],
    ['code' => 'CONNEXION_REUSSIE', 'libelle' => 'Connexion réussie'],
    ['code' => 'ECHEC_CONNEXION', 'libelle' => 'Échec de connexion'],
    ['code' => 'RAPPORT_SOUMIS', 'libelle' => 'Soumission de rapport'],
    ['code' => 'RAPPORT_CONFORMITE_NON_CONFORME', 'libelle' => 'Rapport non conforme'],
    ['code' => 'VOTE_RAPPORT', 'libelle' => 'Vote sur rapport'],
    ['code' => 'PV_VALIDATION', 'libelle' => 'Validation de PV'],
    ['code' => 'CREATION_COMPTE', 'libelle' => 'Création de compte'],
    ['code' => 'MODIF_PROFIL', 'libelle' => 'Modification de profil'],
];

// Fonction pour obtenir le libellé de l'action à partir du code (simulation)
function getActionLibelle($code, $options) {
    foreach ($options as $opt) {
        if ($opt['code'] === $code) {
            return $opt['libelle'];
        }
    }
    return $code; // Retourne le code si non trouvé
}

?>

<div class="admin-module-container">
    <h1 class="admin-title">Journaux d'Audit</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Journaux d'Audit</h2>
        <form id="auditLogFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_user_id">Utilisateur (ID ou nom) :</label>
                <input type="text" id="filter_user_id" name="user_id" value="<?= e($_GET['user_id'] ?? ''); ?>" placeholder="Ex: ADM-2025-0001">
            </div>
            <div class="form-group">
                <label for="filter_action_type">Type d'Action :</label>
                <select id="filter_action_type" name="action_type">
                    <?php foreach ($types_action_disponibles as $type): ?>
                        <option value="<?= e($type['code']); ?>"
                            <?= (($_GET['action_type'] ?? '') === $type['code']) ? 'selected' : ''; ?>>
                            <?= e($type['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_date_debut">Date de Début :</label>
                <input type="date" id="filter_date_debut" name="date_debut" value="<?= e($_GET['date_debut'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="filter_date_fin">Date de Fin :</label>
                <input type="date" id="filter_date_fin" name="date_fin" value="<?= e($_GET['date_fin'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/admin/supervision/journaux-audit'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Historique des Actions Auditées</h2>
        <?php if (!empty($journaux_audit)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Entité Concernée</th>
                    <th>ID Entité</th>
                    <th>Adresse IP</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($journaux_audit as $log): ?>
                    <tr>
                        <td><?= e(date('d/m/Y H:i:s', strtotime($log['date_action']))); ?></td>
                        <td><?= e($log['numero_utilisateur']); ?></td>
                        <td><?= e(getActionLibelle($log['id_action'], $types_action_disponibles)); ?></td>
                        <td><?= e($log['type_entite_concernee']); ?></td>
                        <td><?= e($log['id_entite_concernee']); ?></td>
                        <td><?= e($log['adresse_ip']); ?></td>
                        <td class="log-details-cell" title="<?= e($log['details_action']); ?>">
                            <?= e(mb_strimwidth($log['details_action'], 0, 50, '...')); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination-controls mt-lg text-center">
                <button class="btn btn-secondary-gray" disabled>Précédent</button>
                <span class="current-page">Page 1 de X</span>
                <button class="btn btn-secondary-gray">Suivant</button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun journal d'audit ne correspond aux critères.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const filterForm = document.getElementById('auditLogFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(event) {
                // Empêche la soumission classique du formulaire pour permettre la construction de l'URL
                event.preventDefault();
                const formData = new FormData(filterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value) { // N'ajoute que les champs avec une valeur
                        queryParams.append(key, value);
                    }
                }
                // Recharge la page avec les paramètres de filtre dans l'URL
                window.location.href = `/admin/supervision/journaux-audit?${queryParams.toString()}`;
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
    /* Styles spécifiques pour journaux_audit.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1400px; /* Plus large pour les logs */
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

    /* Filtres - réutilisés et adaptés */
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        align-items: flex-end; /* Aligne les boutons avec les champs */
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .filter-form button {
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

    /* Tableaux de données - réutilisation */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--spacing-md);
        font-size: var(--font-size-sm); /* Un peu plus petit pour les logs détaillés */
    }

    .data-table th,
    .data-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-light);
        text-align: left;
        color: var(--text-primary);
        vertical-align: top; /* Pour les cellules de détails */
    }

    .data-table th {
        background-color: var(--bg-secondary);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        position: sticky;
        top: 0;
        z-index: 1; /* Pour que l'en-tête reste visible lors du défilement */
    }

    .data-table tbody tr:nth-child(even) {
        background-color: var(--primary-gray-light);
    }

    .data-table tbody tr:hover {
        background-color: var(--border-medium);
        transition: background-color var(--transition-fast);
    }

    .log-details-cell {
        max-width: 250px; /* Limite la largeur pour les détails */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap; /* Empêche le texte de passer à la ligne */
    }
    .log-details-cell:hover {
        white-space: normal; /* Permet le retour à la ligne au survol */
        overflow: visible;
        max-width: none; /* Supprime la limite de largeur */
        position: relative;
        z-index: 2; /* S'assure que le texte déborde au-dessus d'autres éléments */
        background-color: var(--primary-white); /* Fond pour le texte qui déborde */
        box-shadow: var(--shadow-md); /* Légère ombre pour le pop-up */
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }

    /* Pagination */
    .pagination-controls button {
        margin: 0 var(--spacing-xs);
    }
    .pagination-controls .current-page {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }
</style>