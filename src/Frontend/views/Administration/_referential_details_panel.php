<?php
// /src/Frontend/views/Administration/_referential_details_panel.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$entityName = $entityName ?? 'inconnu';
$entries = $entries ?? [];
$csrf_token_refs = $csrf_token_refs ?? '';

// Déterminer les clés primaires et les champs à afficher (simplifié, à améliorer)
$primaryKey = !empty($entries) ? array_keys($entries[0])[0] : 'id';
$labelKey = !empty($entries) ? array_keys($entries[0])[1] : 'libelle';
?>

<div class="card bg-base-200 shadow-inner">
    <div class="card-body">
        <h3 class="card-title">Items pour "<?= e(ucfirst(str_replace('_', ' ', $entityName))) ?>"</h3>

        <!-- Tableau des entrées -->
        <div class="overflow-x-auto max-h-96">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Libellé</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><code><?= e($entry[$primaryKey]) ?></code></td>
                        <td><?= e($entry[$labelKey]) ?></td>
                        <td class="text-right">
                            <form action="/admin/configuration/referentiels" method="POST" onsubmit="return confirm('Supprimer cet item ?');">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token_refs) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="entity_name" value="<?= e($entityName) ?>">
                                <input type="hidden" name="id" value="<?= e($entry[$primaryKey]) ?>">
                                <button type="submit" class="btn btn-xs btn-ghost text-error">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="divider">Ajouter une nouvelle entrée</div>

        <!-- Formulaire d'ajout -->
        <form action="/admin/configuration/referentiels" method="POST" class="space-y-2">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token_refs) ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="entity_name" value="<?= e($entityName) ?>">

            <div class="form-control">
                <label class="label"><span class="label-text">Nouveau libellé</span></label>
                <input type="text" name="libelle" placeholder="Libellé de la nouvelle entrée" class="input input-bordered input-sm" required>
            </div>
            <!-- Ajouter d'autres champs si nécessaire pour des référentiels complexes -->
            <div class="text-right">
                <button type="submit" class="btn btn-sm btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>