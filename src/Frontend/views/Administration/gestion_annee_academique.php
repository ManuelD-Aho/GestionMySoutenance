<?php
// /src/Frontend/views/Administration/gestion_annee_academique.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$title = $title ?? 'Gestion des Années Académiques';
$annees = $annees ?? [];
$csrf_token = $csrf_token ?? '';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold"><?= e($title) ?></h1>
        <button class="btn btn-primary" onclick="annee_modal.showModal()">
            <span class="material-icons">add</span>
            Nouvelle Année
        </button>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Libellé</th>
                        <th>Date de Début</th>
                        <th>Date de Fin</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($annees)): ?>
                        <tr><td colspan="5" class="text-center">Aucune année académique trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($annees as $annee): ?>
                            <tr class="hover">
                                <td class="font-semibold"><?= e($annee['libelle_annee_academique']) ?></td>
                                <td><?= e(date('d/m/Y', strtotime($annee['date_debut']))) ?></td>
                                <td><?= e(date('d/m/Y', strtotime($annee['date_fin']))) ?></td>
                                <td>
                                    <?php if ($annee['est_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-ghost">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <form action="/admin/config/annees" method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                        <input type="hidden" name="id_annee_academique" value="<?= e($annee['id_annee_academique']) ?>">
                                        <?php if (!$annee['est_active']): ?>
                                            <button type="submit" name="action" value="set_active" class="btn btn-xs btn-outline btn-success" title="Définir comme active">Activer</button>
                                        <?php endif; ?>
                                    </form>
                                    <button class="btn btn-xs btn-ghost" title="Modifier">Modifier</button>
                                    <form action="/admin/config/annees" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette année ?');">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                        <input type="hidden" name="id_annee_academique" value="<?= e($annee['id_annee_academique']) ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-xs btn-ghost text-error" title="Supprimer">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modale de création/modification -->
<dialog id="annee_modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Nouvelle Année Académique</h3>
        <form method="POST" action="/admin/config/annees" class="py-4 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-control">
                <label class="label"><span class="label-text">Libellé (ex: 2025-2026)</span></label>
                <input type="text" name="libelle_annee_academique" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Date de début</span></label>
                <input type="date" name="date_debut" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Date de fin</span></label>
                <input type="date" name="date_fin" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label cursor-pointer">
                    <span class="label-text">Définir comme active immédiatement</span>
                    <input type="checkbox" name="est_active" class="toggle toggle-primary" />
                </label>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Créer</button>
                <button type="button" class="btn" onclick="annee_modal.close()">Annuler</button>
            </div>
        </form>
    </div>
</dialog>