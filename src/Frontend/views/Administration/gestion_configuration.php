<?php
// /src/Frontend/views/Administration/gestion_configuration.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$title = $title ?? 'Configuration du Système';
$system_parameters = $data['system_parameters'] ?? [];
$academic_years = $data['academic_years'] ?? [];
$referentials = $data['referentials'] ?? [];
$document_models = $data['document_models'] ?? [];
$notification_templates = $data['notification_templates'] ?? [];
$notification_rules = $data['notification_rules'] ?? [];
$all_actions = $data['all_actions'] ?? [];
$all_user_groups = $data['all_user_groups'] ?? [];
$csrf_tokens = $data['csrf_tokens'] ?? [];
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold"><?= e($title) ?></h1>

    <div role="tablist" class="tabs tabs-lifted">
        <!-- Onglet Paramètres -->
        <input type="radio" name="config_tabs" role="tab" class="tab" aria-label="Paramètres" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Paramètres Généraux du Système</h2>
            <form action="/admin/configuration/parametres" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_tokens['params']) ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($system_parameters as $param): ?>
                        <div class="form-control">
                            <label class="label" for="param-<?= e($param['cle']) ?>"><span class="label-text"><?= e($param['description']) ?></span></label>
                            <input type="text" id="param-<?= e($param['cle']) ?>" name="<?= e($param['cle']) ?>" value="<?= e($param['valeur']) ?>" class="input input-bordered">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-right">
                    <button type="submit" class="btn btn-primary">Enregistrer les Paramètres</button>
                </div>
            </form>
        </div>

        <!-- Onglet Années Académiques -->
        <input type="radio" name="config_tabs" role="tab" class="tab" aria-label="Années Académiques" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <!-- Le contenu de gestion_annee_academique.php peut être inclus ou dupliqué ici -->
            <p>Voir la vue dédiée `gestion_annee_academique.php` pour le contenu complet.</p>
        </div>

        <!-- Onglet Référentiels -->
        <input type="radio" name="config_tabs" role="tab" class="tab" aria-label="Référentiels" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Gestion des Référentiels</h2>
            <div class="flex gap-4">
                <div class="w-1/3">
                    <label class="label"><span class="label-text">Choisir un référentiel</span></label>
                    <select id="referential-selector" class="select select-bordered w-full">
                        <option disabled selected>Sélectionner...</option>
                        <?php foreach ($referentials as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="referential-details-container" class="w-2/3">
                    <!-- Le contenu de _referential_details_panel.php sera chargé ici via AJAX -->
                    <div class="p-8 border border-dashed rounded-box text-center text-base-content/50">
                        Veuillez sélectionner un référentiel pour voir les détails.
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglet Modèles de Documents -->
        <input type="radio" name="config_tabs" role="tab" class="tab" aria-label="Modèles Documents" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Gestion des Modèles de Documents</h2>
            <!-- Contenu pour l'upload et la gestion des modèles -->
            <p>Interface pour uploader des fichiers Word et gérer les modèles existants.</p>
        </div>

        <!-- Onglet Notifications -->
        <input type="radio" name="config_tabs" role="tab" class="tab" aria-label="Notifications" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Configuration des Notifications</h2>
            <!-- Contenu pour la matrice de notification et les templates -->
            <p>Interface pour gérer quand et comment les notifications sont envoyées.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selector = document.getElementById('referential-selector');
        const container = document.getElementById('referential-details-container');

        selector.addEventListener('change', async (e) => {
            const entityName = e.target.value;
            if (!entityName) return;

            container.innerHTML = '<div class="text-center p-8"><span class="loading loading-spinner"></span></div>';

            try {
                const response = await fetch(`/admin/configuration/referentiels/${entityName}`);
                if (!response.ok) throw new Error('Erreur réseau');
                const html = await response.text();
                container.innerHTML = html;
            } catch (error) {
                container.innerHTML = '<div class="alert alert-error"><span>Erreur de chargement des données.</span></div>';
                console.error('Erreur AJAX:', error);
            }
        });
    });
</script>