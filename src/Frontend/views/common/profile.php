<?php
// /src/Frontend/views/common/profile.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$user = $user ?? [];
$pageTitle = 'Mon Profil';
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6"><?= e($pageTitle) ?></h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Colonne de gauche : Avatar et infos de base -->
        <div class="md:col-span-1">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body items-center text-center">
                    <div class="avatar online mb-4">
                        <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="<?= e($user['photo_profil'] ?? 'https://placehold.co/100x100') ?>" alt="Avatar" />
                        </div>
                    </div>
                    <h2 class="card-title"><?= e($user['prenom'] . ' ' . $user['nom']) ?></h2>
                    <p><?= e($user['email_principal']) ?></p>
                    <span class="badge badge-primary mt-2"><?= e($user['id_groupe_utilisateur']) ?></span>
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Onglets de gestion -->
        <div class="md:col-span-2">
            <div role="tablist" class="tabs tabs-lifted">
                <input type="radio" name="profile_tabs" role="tab" class="tab" aria-label="Infos Personnelles" checked />
                <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                    <h3 class="text-xl font-semibold mb-4">Informations Personnelles</h3>
                    <p>Ici se trouverait le formulaire pour modifier les informations de contact, comme dans `profil_etudiant.php`.</p>
                    <!-- Contenu du formulaire ici -->
                </div>

                <input type="radio" name="profile_tabs" role="tab" class="tab" aria-label="Sécurité" />
                <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                    <h3 class="text-xl font-semibold mb-4">Changer le mot de passe</h3>
                    <p>Ici se trouverait le formulaire de changement de mot de passe.</p>
                    <!-- Contenu du formulaire ici -->
                    <div class="divider"></div>
                    <h3 class="text-xl font-semibold mb-4">Authentification à deux facteurs (2FA)</h3>
                    <p>Ici se trouverait la section pour activer/désactiver la 2FA.</p>
                    <!-- Contenu 2FA ici -->
                </div>
            </div>
        </div>
    </div>
</div>