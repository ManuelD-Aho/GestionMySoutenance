<?php
// /src/Frontend/views/Administration/gestion_academique.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$title = $title ?? 'Gestion Académique';
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold"><?= e($title) ?></h1>

    <div role="tablist" class="tabs tabs-lifted">
        <input type="radio" name="acad_tabs" role="tab" class="tab" aria-label="UEs / ECUEs" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Gestion des Unités d'Enseignement et Éléments Constitutifs</h2>
            <p>Cette section permettra de créer, lister, modifier et supprimer les UEs et les ECUEs qui composent les parcours de formation.</p>
            <!-- Le contenu du tableau et des formulaires pour les UEs/ECUEs sera chargé ici -->
            <div class="mockup-code mt-4">
                <pre><code>Tableau des UEs...</code></pre>
                <pre><code>Formulaire de création d'UE...</code></pre>
            </div>
        </div>

        <input type="radio" name="acad_tabs" role="tab" class="tab" aria-label="Carrières Enseignants" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Suivi des Carrières Enseignants</h2>
            <p>Interface pour assigner des grades et des fonctions aux enseignants et suivre leur historique.</p>
            <!-- Le contenu pour la gestion des carrières sera chargé ici -->
            <div class="mockup-code mt-4">
                <pre><code>Tableau des enseignants avec leurs grades/fonctions...</code></pre>
            </div>
        </div>
    </div>
</div>