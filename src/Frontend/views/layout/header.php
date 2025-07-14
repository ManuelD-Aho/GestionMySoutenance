<?php
// /src/Frontend/views/layout/header.php

// Préparation des données avec des valeurs par défaut
$currentUser = $user ?? null;
$userName = $currentUser ? e($currentUser['prenom'] . ' ' . $currentUser['nom']) : 'Utilisateur';
$userRole = $currentUser ? e($this->mapGroupToRole($currentUser['id_groupe_utilisateur'])) : 'Invité';
$userAvatar = $currentUser['photo_profil'] ?? null;
$userInitials = '';
if ($currentUser) {
    $nameParts = explode(' ', trim($userName));
    $userInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $userInitials .= strtoupper(substr(end($nameParts), 0, 1));
    }
}

$notificationsCount = $_SESSION['notifications_count'] ?? 0;
?>
<header class="navbar bg-base-100 shadow-sm sticky top-0 z-30">
    <!-- Bouton pour ouvrir la sidebar sur mobile -->
    <div class="flex-none lg:hidden">
        <label for="my-drawer-2" class="btn btn-square btn-ghost">
            <span class="material-icons">menu</span>
        </label>
    </div>

    <!-- Titre de la page -->
    <div class="flex-1 px-2">
        <h1 class="text-xl font-semibold"><?= e($pageTitle ?? 'Tableau de Bord') ?></h1>
    </div>

    <div class="flex-none gap-2">
        <!-- Notifications -->
        <div class="dropdown dropdown-end">
            <button tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <span class="material-icons">notifications</span>
                    <?php if ($notificationsCount > 0): ?>
                        <span class="badge badge-sm badge-primary indicator-item"><?= $notificationsCount ?></span>
                    <?php endif; ?>
                </div>
            </button>
            <div tabindex="0" class="mt-3 z-[1] card card-compact dropdown-content w-80 bg-base-100 shadow">
                <div class="card-body">
                    <span class="font-bold text-lg"><?= $notificationsCount ?> Notifications</span>
                    <span class="text-info">Vous êtes à jour !</span>
                    <div class="card-actions">
                        <a href="/notifications" class="btn btn-primary btn-block">Voir toutes les notifications</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Utilisateur -->
        <div class="dropdown dropdown-end">
            <button tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <?php if ($userAvatar): ?>
                        <img src="<?= e($userAvatar) ?>" alt="Avatar de <?= $userName ?>" />
                    <?php else: ?>
                        <span class="text-xl"><?= $userInitials ?></span>
                    <?php endif; ?>
                </div>
            </button>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li class="menu-title"><span><?= $userName ?></span></li>
                <li><a href="/profile"><span class="material-icons text-base mr-2">person</span>Profil</a></li>
                <li><a href="/settings"><span class="material-icons text-base mr-2">settings</span>Paramètres</a></li>
                <?php if ($userRole === 'admin'): ?>
                    <div class="divider my-1"></div>
                    <li><a href="/admin/dashboard"><span class="material-icons text-base mr-2">admin_panel_settings</span>Administration</a></li>
                <?php endif; ?>
                <div class="divider my-1"></div>
                <li><a href="/logout"><span class="material-icons text-base mr-2">logout</span>Déconnexion</a></li>
            </ul>
        </div>
    </div>
</header>