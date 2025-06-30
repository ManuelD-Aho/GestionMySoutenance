<?php
// src/Frontend/views/Administration/Habilitations/gestion_rattachements.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (groupes, traitements, rattachements existants) proviendraient du contrôleur HabilitationController.
// Ces données sont des exemples pour structurer la vue.
//

$groupes_disponibles = $data['groupes_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Administrateur', 'code' => 'GRP_ADMIN'],
    ['id' => 2, 'libelle' => 'Responsable Scolarité', 'code' => 'GRP_RS'],
    ['id' => 3, 'libelle' => 'Agent de Conformité', 'code' => 'GRP_AGENT_CONFORMITE'],
    ['id' => 4, 'libelle' => 'Membre de Commission', 'code' => 'GRP_COMMISSION'],
    ['id' => 5, 'libelle' => 'Étudiant', 'code' => 'GRP_ETUDIANT'],
    ['id' => 6, 'libelle' => 'Enseignant', 'code' => 'GRP_ENSEIGNANT'],
];

$traitements_disponibles = $data['traitements_disponibles'] ?? [
    ['id' => 101, 'libelle' => 'Créer Année Académique', 'code' => 'TRAIT_ANNEE_CREATE'],
    ['id' => 102, 'libelle' => 'Modifier Année Académique', 'code' => 'TRAIT_ANNEE_UPDATE'],
    ['id' => 103, 'libelle' => 'Gérer Modèles Documents', 'code' => 'TRAIT_MODELE_DOC_MANAGE'],
    ['id' => 104, 'libelle' => 'Soumettre Rapport', 'code' => 'TRAIT_RAPPORT_SOUMETTRE'],
    ['id' => 105, 'libelle' => 'Vérifier Conformité Rapport', 'code' => 'TRAIT_RAPPORT_CONFORMITE_VERIFY'],
    ['id' => 106, 'libelle' => 'Voter sur Rapport', 'code' => 'TRAIT_COMMISSION_VOTER'],
    ['id' => 107, 'libelle' => 'Rédiger PV', 'code' => 'TRAIT_PV_REDIGER'],
    ['id' => 108, 'libelle' => 'Valider PV', 'code' => 'TRAIT_PV_VALIDER'],
    ['id' => 109, 'libelle' => 'Gérer Inscriptions', 'code' => 'TRAIT_INSCRIPTION_MANAGE'],
    ['id' => 110, 'libelle' => 'Gérer Notes', 'code' => 'TRAIT_NOTE_MANAGE'],
    ['id' => 111, 'libelle' => 'Activer Compte Étudiant', 'code' => 'TRAIT_COMPTE_ETUDIANT_ACTIVER'],
    ['id' => 112, 'libelle' => 'Accéder Tableau de Bord Admin', 'code' => 'TRAIT_ADMIN_DASHBOARD_ACCESS'],
    ['id' => 113, 'libelle' => 'Consulter Rapports Étudiant', 'code' => 'TRAIT_RAPPORT_CONSULTER'],
    ['id' => 114, 'libelle' => 'Gérer Pistes d\'Audit', 'code' => 'TRAIT_AUDIT_LOG_MANAGE'],
];

// Rattachements pour le groupe sélectionné (initiallement vide ou avec un exemple)
// En production, ce tableau serait récupéré via ServicePermissionsInterface::recupererPermissionsPourGroupe
$selected_group_id = $_GET['group_id'] ?? null;
$permissions_pour_groupe = $data['permissions_pour_groupe'] ?? []; // Liste des IDs de traitements pour le groupe sélectionné

// Exemple si un groupe est sélectionné (pour démo)
if ($selected_group_id == 1 && empty($permissions_pour_groupe)) { // GRP_ADMIN
    $permissions_pour_groupe = array_column($traitements_disponibles, 'id'); // Admin a tous les droits pour la démo
} elseif ($selected_group_id == 5 && empty($permissions_pour_groupe)) { // GRP_ETUDIANT
    $permissions_pour_groupe = [104, 113]; // Soumettre Rapport, Consulter Rapports Étudiant
}
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Rattachements (Permissions)</h1>

    <section class="section-selection-groupe admin-card">
        <h2 class="section-title">Sélectionner un Groupe d'Utilisateurs</h2>
        <div class="form-group">
            <label for="groupe_id_select">Groupe :</label>
            <select id="groupe_id_select" name="groupe_id_select" required>
                <option value="">Sélectionner un groupe</option>
                <?php foreach ($groupes_disponibles as $groupe): ?>
                    <option value="<?= e($groupe['id']); ?>"
                        <?= ($selected_group_id == $groupe['id']) ? 'selected' : ''; ?>>
                        <?= e($groupe['libelle']); ?> (<?= e($groupe['code']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="loadPermissions" class="btn btn-primary-blue mt-md">
                <span class="material-icons">refresh</span> Charger Permissions
            </button>
        </div>
    </section>

    <?php if ($selected_group_id): ?>
        <section class="section-permissions-management admin-card mt-xl">
            <h2 class="section-title">Permissions pour le Groupe : <span><?= e(array_values(array_filter($groupes_disponibles, fn($g) => $g['id'] == $selected_group_id))[0]['libelle'] ?? 'Non défini'); ?></span></h2>
            <p class="section-description">Cochez les traitements que ce groupe est autorisé à effectuer.</p>

            <form id="formRattachements" action="/admin/habilitations/rattachements/update/<?= e($selected_group_id); ?>" method="POST">
                <div class="permissions-grid">
                    <?php foreach ($traitements_disponibles as $traitement): ?>
                        <div class="permission-item">
                            <label>
                                <input type="checkbox" name="traitements_ids[]" value="<?= e($traitement['id']); ?>"
                                    <?= in_array($traitement['id'], $permissions_pour_groupe) ? 'checked' : ''; ?>>
                                <strong><?= e($traitement['libelle']); ?></strong> (<code><?= e($traitement['code']); ?></code>)
                            </label>
                            <p class="permission-description"><?= e($traitement['description'] ?? 'Pas de description.'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary-green mt-lg">
                    <span class="material-icons">save</span>
                    Sauvegarder les Permissions
                </button>
            </form>
        </section>
    <?php else: ?>
        <p class="text-center text-muted mt-xl">Veuillez sélectionner un groupe pour gérer ses permissions.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const groupeSelect = document.getElementById('groupe_id_select');
        const loadPermissionsButton = document.getElementById('loadPermissions');
        const formRattachements = document.getElementById('formRattachements');

        if (loadPermissionsButton) {
            loadPermissionsButton.addEventListener('click', function() {
                const selectedGroupId = groupeSelect.value;
                if (selectedGroupId) {
                    // Recharger la page avec l'ID du groupe dans l'URL pour que PHP puisse charger les permissions
                    window.location.href = `/admin/habilitations/rattachements?group_id=${selectedGroupId}`;
                } else {
                    alert('Veuillez sélectionner un groupe.');
                }
            });
        }

        if (formRattachements) {
            formRattachements.addEventListener('submit', function(event) {
                event.preventDefault(); // Empêche la soumission normale du formulaire

                const formData = new FormData(this);
                const traitementsSelectionnes = [];
                formData.forEach((value, key) => {
                    if (key === 'traitements_ids[]') {
                        traitementsSelectionnes.push(value);
                    }
                });

                const currentGroupId = <?= json_encode($selected_group_id); ?>;
                const dataToSubmit = {
                    group_id: currentGroupId,
                    traitements_ids: traitementsSelectionnes
                };

                console.log('Soumission des rattachements pour le groupe', dataToSubmit);

                // Simulation d'une requête AJAX pour la mise à jour des permissions en temps réel
                // Conforme à la description dans SIN.pdf
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': 'votre_token_csrf_ici' // Si vous utilisez CSRF
                    },
                    body: JSON.stringify(dataToSubmit)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Permissions mises à jour avec succès !');
                            // Optionnel: Mettre à jour l'UI ou recharger la page si nécessaire
                            // window.location.reload();
                        } else {
                            alert('Erreur lors de la mise à jour des permissions : ' + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX lors de la mise à jour des permissions:', error);
                        alert('Une erreur de communication est survenue lors de la mise à jour des permissions.');
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
    /* Styles spécifiques pour gestion_rattachements.php */
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

    /* Section de sélection de groupe */
    .section-selection-groupe .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
        align-items: flex-start; /* Aligne le label et le select à gauche */
    }

    .section-selection-groupe select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        width: 100%;
        max-width: 400px; /* Limiter la largeur du select */
    }

    .section-selection-groupe select:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .section-selection-groupe .btn {
        margin-top: var(--spacing-md);
    }

    /* Section de gestion des permissions */
    .section-permissions-management .section-title span {
        color: var(--primary-blue-dark);
    }

    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }

    .permission-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
    }

    .permission-item label {
        display: flex;
        align-items: flex-start;
        font-size: var(--font-size-base);
        color: var(--text-primary);
        cursor: pointer;
        margin-bottom: var(--spacing-xs);
    }

    .permission-item input[type="checkbox"] {
        margin-right: var(--spacing-sm);
        transform: scale(1.1); /* Rendre les checkboxes un peu plus grandes */
        flex-shrink: 0; /* Empêche le checkbox de se compresser */
        margin-top: 3px; /* Alignement visuel avec le texte */
    }

    .permission-item strong {
        font-weight: var(--font-weight-semibold);
        color: var(--primary-blue-dark);
    }

    .permission-item code {
        background-color: var(--primary-gray-light);
        padding: 0.1em 0.4em;
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .permission-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-left: calc(var(--spacing-sm) + 1.1em); /* Aligner avec le texte du label */
        margin-top: var(--spacing-xs);
    }

    /* Boutons de soumission */
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

    .btn-primary-blue { /* Pour le bouton "Charger Permissions" */
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary-green { /* Pour le bouton "Sauvegarder les Permissions" */
        color: var(--text-white);
        background-color: var(--primary-green);
    }

    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>