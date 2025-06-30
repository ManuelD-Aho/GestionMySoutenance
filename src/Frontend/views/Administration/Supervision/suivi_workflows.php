<?php
// src/Frontend/views/Administration/Supervision/suivi_workflows.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le suivi des workflows (proviennent du contrôleur SupervisionController)
// Ces données sont des exemples pour structurer la vue.
//

$workflows_en_cours = $data['workflows_en_cours'] ?? [
    ['id' => 1, 'type' => 'Soumission Rapport', 'entite_id' => 'RAP-2025-0045', 'entite_libelle' => 'Rapport de Dupont Jean', 'etape_actuelle' => 'Vérification Conformité', 'statut' => 'En cours', 'date_debut' => '2025-06-28 09:00:00', 'assigne_a' => 'Agent Conformité 001', 'historique' => [
        ['etape' => 'Soumis par Étudiant', 'date' => '2025-06-28 09:00', 'par' => 'ETU-2025-0123'],
        ['etape' => 'Reçu par Agent Conformité', 'date' => '2025-06-28 09:05', 'par' => 'Agent Conformité 001'],
    ]],
    ['id' => 2, 'type' => 'Validation PV', 'entite_id' => 'PV-2025-0010', 'entite_libelle' => 'PV Session Juin', 'etape_actuelle' => 'Approbation Membres Commission', 'statut' => 'En attente', 'date_debut' => '2025-06-25 15:00:00', 'assigne_a' => 'Commission XYZ', 'historique' => [
        ['etape' => 'Rédigé par Président', 'date' => '2025-06-25 15:00', 'par' => 'PRES-COMM-001'],
        ['etape' => 'Soumis pour Approbation', 'date' => '2025-06-25 15:10', 'par' => 'PRES-COMM-001'],
    ]],
    ['id' => 3, 'type' => 'Activation Compte', 'entite_id' => 'ETU-2025-0124', 'entite_libelle' => 'Compte de Marie Curie', 'etape_actuelle' => 'Régularisation Pénalités', 'statut' => 'Bloqué', 'date_debut' => '2025-06-20 10:00:00', 'assigne_a' => 'RS-001', 'historique' => [
        ['etape' => 'Demande d\'Activation', 'date' => '2025-06-20 10:00', 'par' => 'ETU-2025-0124'],
        ['etape' => 'Détection Pénalités', 'date' => '2025-06-20 10:05', 'par' => 'SYS'],
        ['etape' => 'Notification Étudiant', 'date' => '2025-06-20 10:10', 'par' => 'RS-001'],
    ]],
];

// Options de filtrage pour les types et statuts de workflow
$types_workflow = ['ALL' => 'Tous les types', 'Soumission Rapport' => 'Soumission Rapport', 'Validation PV' => 'Validation PV', 'Activation Compte' => 'Activation Compte'];
$statuts_workflow = ['ALL' => 'Tous les statuts', 'En cours' => 'En cours', 'En attente' => 'En attente', 'Bloqué' => 'Bloqué', 'Terminé' => 'Terminé', 'Échoué' => 'Échoué'];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Suivi des Workflows</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Workflows</h2>
        <form id="workflowFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_type_workflow">Type de Workflow :</label>
                <select id="filter_type_workflow" name="type">
                    <?php foreach ($types_workflow as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['type'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_statut_workflow">Statut :</label>
                <select id="filter_statut_workflow" name="statut">
                    <?php foreach ($statuts_workflow as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_entite_id">ID Entité / Acteur :</label>
                <input type="text" id="filter_entite_id" name="entite_id" value="<?= e($_GET['entite_id'] ?? ''); ?>" placeholder="Ex: RAP-2025-0045">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/admin/supervision/suivi-workflows'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Workflows en Cours et Récents</h2>
        <?php if (!empty($workflows_en_cours)): ?>
            <div class="workflow-cards-grid">
                <?php foreach ($workflows_en_cours as $workflow): ?>
                    <div class="workflow-card workflow-status-<?= e(str_replace(' ', '-', strtolower($workflow['statut']))); ?>">
                        <div class="card-header">
                            <h3 class="card-title"><?= e($workflow['type']); ?> (<?= e($workflow['entite_id']); ?>)</h3>
                            <span class="status-indicator status-<?= e(str_replace(' ', '-', strtolower($workflow['statut']))); ?>"><?= e($workflow['statut']); ?></span>
                        </div>
                        <div class="card-body">
                            <p><strong>Entité :</strong> <?= e($workflow['entite_libelle']); ?></p>
                            <p><strong>Étape Actuelle :</strong> <?= e($workflow['etape_actuelle']); ?></p>
                            <p><strong>Démarré le :</strong> <?= e(date('d/m/Y H:i', strtotime($workflow['date_debut']))); ?></p>
                            <?php if (!empty($workflow['assigne_a'])): ?>
                                <p><strong>Assigné à :</strong> <?= e($workflow['assigne_a']); ?></p>
                            <?php endif; ?>
                            <button class="btn btn-secondary-gray btn-sm mt-md view-history-btn" data-workflow-id="<?= e($workflow['id']); ?>">
                                <span class="material-icons">history</span> Voir Historique
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun workflow en cours ne correspond aux critères.</p>
        <?php endif; ?>
    </section>
</div>

<div id="workflowHistoryModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Historique du Workflow <span id="modalWorkflowId"></span></h2>
        <h3 id="modalWorkflowTitle" class="modal-subtitle"></h3>
        <ul id="modalWorkflowHistoryList" class="workflow-history-list">
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const workflowFilterForm = document.getElementById('workflowFilterForm');
        const workflowHistoryModal = document.getElementById('workflowHistoryModal');
        const closeButton = workflowHistoryModal ? workflowHistoryModal.querySelector('.close-button') : null;
        const viewHistoryButtons = document.querySelectorAll('.view-history-btn');

        if (workflowFilterForm) {
            workflowFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(workflowFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/admin/supervision/suivi-workflows?${queryParams.toString()}`;
            });
        }

        // Gestion du modal d'historique
        if (workflowHistoryModal && closeButton) {
            closeButton.addEventListener('click', () => workflowHistoryModal.style.display = 'none');
            window.addEventListener('click', (event) => {
                if (event.target === workflowHistoryModal) {
                    workflowHistoryModal.style.display = 'none';
                }
            });

            viewHistoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const workflowId = this.dataset.workflowId;
                    const allWorkflows = <?= json_encode($workflows_en_cours); ?>;
                    const workflow = allWorkflows.find(w => w.id == workflowId);

                    if (workflow) {
                        document.getElementById('modalWorkflowId').textContent = e(workflow.entite_id);
                        document.getElementById('modalWorkflowTitle').textContent = e(workflow.type + ' - ' + workflow.entite_libelle);
                        const historyList = document.getElementById('modalWorkflowHistoryList');
                        historyList.innerHTML = ''; // Nettoyer l'ancien historique

                        workflow.historique.forEach(step => {
                            const li = document.createElement('li');
                            li.innerHTML = `
                            <strong>${e(step.etape)}</strong>
                            <span>le ${new Date(step.date).toLocaleString()}</span>
                            ${step.par ? ` par <span>${e(step.par)}</span>` : ''}
                        `;
                            historyList.appendChild(li);
                        });
                        workflowHistoryModal.style.display = 'block';
                    } else {
                        alert('Historique du workflow non trouvé.');
                    }
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
    /* Styles spécifiques pour suivi_workflows.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
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

    /* Grille des cartes de workflow */
    .workflow-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }

    .workflow-card {
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .workflow-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-xs);
    }

    .workflow-card .card-title {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin: 0;
        font-weight: var(--font-weight-semibold);
    }

    .workflow-card .card-body p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }

    .workflow-card .card-body strong {
        color: var(--text-primary);
    }

    /* Indicateurs de statut pour les workflows */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }

    .workflow-status-en-cours .status-indicator {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .workflow-status-en-attente .status-indicator {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .workflow-status-bloque .status-indicator {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .workflow-status-termine .status-indicator {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: var(--font-size-sm);
    }

    .view-history-btn {
        align-self: flex-end; /* Aligne le bouton en bas à droite de la carte */
        margin-top: var(--spacing-md);
    }


    /* Modal Styles - Réutilisés de logs.php */
    .modal {
        display: none;
        position: fixed;
        z-index: var(--z-modal);
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: var(--bg-overlay);
        padding-top: 60px;
    }

    .modal-content {
        background-color: var(--bg-primary);
        margin: 5% auto;
        padding: var(--spacing-xl);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-lg);
        width: 80%;
        box-shadow: var(--shadow-2xl);
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-content h2 {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-sm);
    }

    .modal-content h3.modal-subtitle {
        font-size: var(--font-size-xl);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-lg);
    }

    .workflow-history-list {
        list-style: none;
        padding: 0;
    }

    .workflow-history-list li {
        background-color: var(--primary-gray-light);
        border-left: 3px solid var(--primary-blue);
        padding: var(--spacing-sm) var(--spacing-md);
        margin-bottom: var(--spacing-sm);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: var(--spacing-xs);
    }

    .workflow-history-list li strong {
        color: var(--text-primary);
    }

    .workflow-history-list li span {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .close-button {
        color: var(--text-secondary);
        font-size: var(--font-size-3xl);
        position: absolute;
        top: var(--spacing-md);
        right: var(--spacing-md);
        cursor: pointer;
        transition: color var(--transition-fast);
    }

    .close-button:hover,
    .close-button:focus {
        color: var(--text-primary);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>