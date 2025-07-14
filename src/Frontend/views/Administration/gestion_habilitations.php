<?php
// /src/Frontend/views/Administration/gestion_habilitations.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$title = $title ?? 'Gestion des Habilitations';
$groupes = $groupes ?? [];
$traitements = $traitements ?? [];
$rattachements = $rattachements ?? []; // Format: ['id_groupe']['id_traitement'] = true
$csrf_token = $csrf_token ?? '';
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold"><?= e($title) ?></h1>
    <p class="text-base-content/70">Attribuez des permissions (traitements) aux groupes d'utilisateurs. Cochez une case pour accorder un droit.</p>

    <form action="/admin/habilitations/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm w-full">
                        <thead>
                        <tr>
                            <th class="sticky left-0 bg-base-200 z-10">Groupe / Permission</th>
                            <?php foreach ($traitements as $traitement): ?>
                                <th class="text-center rotate-[-45deg] h-32">
                                    <div class="w-24">
                                        <span title="<?= e($traitement['libelle_traitement']) ?>"><?= e($traitement['id_traitement']) ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($groupes as $groupe): ?>
                            <tr>
                                <td class="font-semibold sticky left-0 bg-base-100 z-10"><?= e($groupe['libelle_groupe']) ?></td>
                                <?php foreach ($traitements as $traitement): ?>
                                    <td class="text-center">
                                        <input type="checkbox"
                                               name="rattachements[<?= e($groupe['id_groupe_utilisateur']) ?>][<?= e($traitement['id_traitement']) ?>]"
                                               class="checkbox checkbox-primary"
                                               <?php if (isset($rattachements[$groupe['id_groupe_utilisateur']][$traitement['id_traitement']])): ?>checked<?php endif; ?>
                                        >
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 text-right">
            <button type="submit" class="btn btn-primary btn-lg">Enregistrer les Modifications</button>
        </div>
    </form>
</div>