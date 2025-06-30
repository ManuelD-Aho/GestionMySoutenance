<?php
// src/Frontend/views/PersonnelAdministratif/DocumentAdministratif/list_generated_documents.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les documents générés (proviennent du DocumentAdministratifController)
//
//
//

$documents_generes = $data['documents_generes'] ?? [
    ['id' => 1, 'type_doc' => 'Attestation de Scolarité', 'destinataire' => 'Dupont Jean', 'date_generation' => '2025-06-20 10:00:00', 'url' => '/assets/generated_docs/attestation_jean_dupont.pdf', 'status' => 'Généré'],
    ['id' => 2, 'type_doc' => 'Reçu de Paiement', 'destinataire' => 'Curie Marie', 'date_generation' => '2025-06-22 14:30:00', 'url' => '/assets/generated_docs/recu_marie_curie.pdf', 'status' => 'Généré'],
    ['id' => 3, 'type_doc' => 'Bulletin de Notes Officiel', 'destinataire' => 'Voltaire François', 'date_generation' => '2025-06-25 09:00:00', 'url' => '/assets/generated_docs/bulletin_voltaire.pdf', 'status' => 'Généré'],
    ['id' => 4, 'type_doc' => 'Procès-Verbal', 'destinataire' => 'Rapport RAP-2025-0010', 'date_generation' => '2025-06-28 11:00:00', 'url' => '/assets/generated_docs/pv_rapport_0010.pdf', 'status' => 'Généré'],
];

// Options de filtrage (simulées)
$types_doc_filtre = ['ALL' => 'Tous les types', 'Attestation de Scolarité' => 'Attestation de Scolarité', 'Reçu de Paiement' => 'Reçu de Paiement', 'Bulletin de Notes Officiel' => 'Bulletin de Notes Officiel', 'Procès-Verbal' => 'Procès-Verbal'];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Documents Générés</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Documents</h2>
        <form id="documentFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_type">Type de Document :</label>
                <select id="filter_type" name="type">
                    <?php foreach ($types_doc_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['type'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_recipient">Destinataire / Étudiant :</label>
                <input type="text" id="filter_recipient" name="recipient" value="<?= e($_GET['recipient'] ?? ''); ?>" placeholder="Nom ou ID...">
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/documents/liste-generes'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <div class="section-header">
            <h2 class="section-title">Historique des Documents Générés</h2>
            <a href="/personnel/documents/generate" class="btn btn-primary-green">
                <span class="material-icons">add</span> Générer un Nouveau
            </a>
        </div>

        <?php if (!empty($documents_generes)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type de Document</th>
                        <th>Destinataire</th>
                        <th>Date de Génération</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents_generes as $doc): ?>
                        <tr>
                            <td><?= e($doc['type_doc']); ?></td>
                            <td><?= e(mb_strimwidth($doc['destinataire'], 0, 40, '...')); ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($doc['date_generation']))); ?></td>
                            <td>
                                <span class="status-indicator status-<?= strtolower(e($doc['status'])); ?>">
                                    <?= e($doc['status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= e($doc['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger le document">
                                    <span class="material-icons">download</span>
                                </a>
                                <form action="/personnel/documents/delete/<?= e($doc['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce document généré ?');">
                                    <button type="submit" class="btn-action delete-btn" title="Supprimer le document">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </form>
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
            <p class="text-center text-muted">Aucun document n'a été généré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/personnel/documents/generate" class="btn btn-primary-blue">Générer le premier document</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logique pour la gestion des filtres
    const documentFilterForm = document.getElementById('documentFilterForm');
    if (documentFilterForm) {
        documentFilterForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(documentFilterForm);
            const queryParams = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value && value !== 'ALL') {
                    queryParams.append(key, value);
                }
            }
            window.location.href = `/personnel/documents/liste-generes?${queryParams.toString()}`;
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
/* Styles spécifiques pour list_generated_documents.php */
/* Réutilisation des classes de root.css et style.css */

/* Conteneur et titres principaux - réutilisés */
.common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
    padding: var(--spacing-lg);
    background-color: var(--bg-primary);
    border-radius: var(--border-radius-md);
    box-shadow: var(--shadow-sm);
    max-width: 1200px;
    margin: var(--spacing-xl) auto;
}

.dashboard-title { /* Réutilisé de dashboard.php */
    font-size: var(--font-size-2xl);
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
    text-align: center;
    font-weight: var(--font-weight-semibold);
    padding-bottom: var(--spacing-xs);
    border-bottom: 1px solid var(--border-light);
}

.admin-card { /* Réutilisé des modules d'administration */
    background-color: var(--bg-secondary);
    border-radius: var(--border-radius-md);
    box-shadow: var(--shadow-sm);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.section-header { /* Réutilisé des listes d'administration */
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--border-medium);
    padding-bottom: var(--spacing-sm);
}

.section-title { /* Réutilisé des formulaires admin */
    font-size: var(--font-size-xl);
    color: var(--text-primary);
    font-weight: var(--font-weight-medium);
    margin: 0;
}

/* Filtres - réutilisés et adaptés */
.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    align-items: flex-end;
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

/* Boutons de filtre */
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

.btn-primary-blue {
    color: var(--text-white);
    background-color: var(--primary-blue);
}

.btn-primary-blue:hover {
    background-color: var(--primary-blue-dark);
    box-shadow: var(--shadow-sm);
}

.btn-primary-green { /* Pour le bouton "Générer un Nouveau" */
    color: var(--text-white);
    background-color: var(--primary-green);
}
.btn-primary-green:hover {
    background-color: var(--primary-green-dark);
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

.ml-md { margin-left: var(--spacing-md); }

/* Tableaux de données - réutilisés */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: var(--spacing-md);
    font-size: var(--font-size-base);
}

.data-table th,
.data-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--border-light);
    text-align: left;
    color: var(--text-primary);
}

.data-table th {
    background-color: var(--bg-secondary);
    font-weight: var(--font-weight-semibold);
    color: var(--text-secondary);
}

.data-table tbody tr:nth-child(even) {
    background-color: var(--primary-gray-light);
}

.data-table tbody tr:hover {
    background-color: var(--border-medium);
    transition: background-color var(--transition-fast);
}

.actions {
    text-align: center;
    white-space: nowrap;
}

.btn-action {
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--spacing-xs);
    border-radius: var(--border-radius-sm);
    transition: background-color var(--transition-fast);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    font-size: var(--font-size-xl);
    text-decoration: none;
}

.btn-action:hover {
    background-color: var(--primary-gray-light);
}

.btn-action.download-btn { /* Bouton de téléchargement */
    color: var(--primary-green);
}
.btn-action.download-btn:hover {
    background-color: rgba(16, 185, 129, 0.1);
}

.btn-action.delete-btn { /* Bouton de suppression */
    color: var(--accent-red);
}
.btn-action.delete-btn:hover {
    background-color: rgba(239, 68, 68, 0.1);
}

/* Statuts de document */
.status-indicator {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-full);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    display: inline-block;
    min-width: 70px;
    text-align: center;
}

.status-généré { /* Couleur pour le statut "Généré" */
    background-color: var(--primary-green-light);
    color: var(--primary-green-dark);
}
.status-en-attente { /* Si un document était en attente de génération */
    background-color: var(--accent-yellow-light);
    color: var(--accent-yellow-dark);
}
.status-echec { /* Si la génération a échoué */
    background-color: var(--accent-red-light);
    color: var(--accent-red-dark);
}

/* Utilitaires */
.text-center { text-align: center; }
.text-muted { color: var(--text-light); }
.mt-xl { margin-top: var(--spacing-xl); }
.mt-lg { margin-top: var(--spacing-lg); }
</style>