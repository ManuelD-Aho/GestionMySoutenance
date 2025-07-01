<?php
// src/Frontend/views/layout/app.php

// Assurez-vous que les variables nécessaires sont définies, même si elles sont nulles par défaut.
$title = $title ?? 'GestionMySoutenance';
$content = $content ?? '';
$flash_messages = $flash_messages ?? [];
$user = $user ?? ['photo_profil' => '/assets/images/default-avatar.png', 'login_utilisateur' => 'Utilisateur']; // Valeurs par défaut
$is_impersonating = $is_impersonating ?? false;
$impersonator_data = $impersonator_data ?? ['login_utilisateur' => 'Admin'];
$menu_items = $menu_items ?? []; // Assurez-vous que c'est un tableau, même vide
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/favicon.ico">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/app.css"> <!-- Contient Tailwind/DaisyUI -->
    <link rel="stylesheet" href="/assets/css/dashboard_style.css"> <!-- Styles spécifiques au dashboard -->
    <link rel="stylesheet" href="/assets/css/root.css"> <!-- Variables CSS personnalisées -->

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- GSAP pour les animations (chargement asynchrone) -->
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="https://unpkg.com/gsap@3.12.5/dist/ScrollTrigger.min.js" defer></script>

    <!-- Scripts JS de l'application (chargement asynchrone) -->
    <script src="/assets/js/app.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
</head>
<body class="font-poppins antialiased bg-base-300 text-base-content">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col min-h-screen">
        <!-- Navbar / Header -->
        <header class="navbar bg-base-100 shadow-lg z-20 p-4 sticky top-0">
            <div class="flex-none lg:hidden">
                <label for="my-drawer-2" class="btn btn-square btn-ghost text-primary-content">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </label>
            </div>
            <div class="flex-1 px-2 mx-2 text-3xl font-extrabold text-primary font-montserrat">
                <i class="fas fa-graduation-cap mr-3 text-primary-focus"></i>GestionMySoutenance
            </div>
            <div class="flex-none">
                <?php if ($is_impersonating): ?>
                    <div class="badge badge-warning badge-lg mr-4 p-3 font-semibold animate-pulse-slow">
                        <i class="fas fa-mask mr-2"></i>Impersonation: <span class="font-bold"><?= htmlspecialchars($impersonator_data['login_utilisateur']) ?></span>
                        <a href="/admin/utilisateurs/stop-impersonating" class="ml-2 text-sm underline text-warning-content hover:text-white transition-colors duration-200">Arrêter</a>
                    </div>
                <?php endif; ?>

                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar tooltip tooltip-bottom" data-tip="<?= htmlspecialchars($user['login_utilisateur'] ?? 'Utilisateur') ?>">
                        <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="<?= htmlspecialchars($user['photo_profil'] ?? '/assets/images/default-avatar.png') ?>" alt="Avatar de l'utilisateur" class="object-cover w-full h-full" />
                        </div>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-xl bg-base-100 rounded-box w-56 border border-base-200 animate-fade-in-down">
                        <li class="menu-title text-base-content/70 text-sm font-semibold">
                            <?= htmlspecialchars($user['login_utilisateur'] ?? 'Utilisateur') ?>
                            <span class="text-xs text-base-content/50 block"><?= htmlspecialchars($user['email_principal'] ?? '') ?></span>
                        </li>
                        <li>
                            <a href="/profil" class="justify-between hover:bg-primary-focus/10 transition-colors duration-200">
                                <i class="fas fa-user-circle mr-2"></i>Profil
                                <span class="badge badge-primary badge-outline text-xs">Nouveau</span>
                            </a>
                        </li>
                        <li>
                            <a href="/parametres" class="hover:bg-primary-focus/10 transition-colors duration-200">
                                <i class="fas fa-cog mr-2"></i>Paramètres
                            </a>
                        </li>
                        <li class="border-t border-base-200 my-1"></li>
                        <li>
                            <form action="/logout" method="post" class="w-full"> <!-- Utilisation de POST pour la déconnexion sécurisée -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token_logout'] ?? '') ?>"> <!-- Assurez-vous de générer ce token -->
                                <button type="submit" class="w-full text-left hover:bg-error/10 text-error-content transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <button class="btn btn-ghost btn-circle ml-2 tooltip tooltip-bottom" id="toggle-notifications" data-tip="Notifications">
                    <div class="indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-content" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="badge badge-sm badge-error indicator-item font-bold text-xs">99+</span> <!-- Exemple, à remplacer par le vrai compte -->
                    </div>
                </button>
            </div>
        </header>

        <!-- Flash Messages Container -->
        <div class="p-6 pt-4">
            <?php if (!empty($flash_messages)): ?>
                <div class="space-y-3">
                    <?php foreach ($flash_messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($msg['type']) ?> shadow-lg rounded-lg animate-fade-in-up">
                            <div>
                                <i class="fas fa-<?= $msg['type'] === 'success' ? 'check-circle' : ($msg['type'] === 'error' ? 'times-circle' : ($msg['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?> text-xl"></i>
                                <span class="font-medium"><?= htmlspecialchars($msg['message']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-grow p-6 bg-base-200">
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-200 shadow-inner">
            <aside>
                <p class="text-sm">Copyright © <?= date('Y') ?> - Tous droits réservés par <span class="font-semibold text-primary">GestionMySoutenance</span></p>
            </aside>
        </footer>
    </div>

    <!-- Sidebar / Menu -->
    <div class="drawer-side z-30">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        <ul class="menu p-4 w-80 min-h-full bg-base-200 text-base-content shadow-xl border-r border-base-300">
            <!-- Sidebar content here -->
            <li class="text-3xl font-extrabold text-primary mb-6 flex items-center justify-center py-4 border-b border-base-300">
                <i class="fas fa-graduation-cap mr-3 text-primary-focus"></i>GMS
            </li>
            <?php foreach ($menu_items as $item): ?>
                <?php if (empty($item['enfants'])): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['url_associee'] ?? '#') ?>" class="flex items-center py-3 px-4 rounded-lg hover:bg-primary-focus/10 active:bg-primary-focus/20 transition-colors duration-200 text-base font-medium">
                            <i class="<?= htmlspecialchars($item['icone_class'] ?? 'fas fa-circle') ?> text-lg mr-3"></i>
                            <?= htmlspecialchars($item['libelle_menu']) ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <details open class="group">
                            <summary class="flex items-center py-3 px-4 rounded-lg hover:bg-primary-focus/10 active:bg-primary-focus/20 transition-colors duration-200 text-base font-medium cursor-pointer">
                                <i class="<?= htmlspecialchars($item['icone_class'] ?? 'fas fa-folder') ?> text-lg mr-3"></i>
                                <?= htmlspecialchars($item['libelle_menu']) ?>
                                <span class="ml-auto transform transition-transform duration-200 group-open:rotate-90">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </span>
                            </summary>
                            <ul class="ml-6 mt-2 space-y-1">
                                <?php foreach ($item['enfants'] as $child): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($child['url_associee'] ?? '#') ?>" class="flex items-center py-2 px-3 rounded-lg hover:bg-primary-focus/10 active:bg-primary-focus/20 transition-colors duration-200 text-sm">
                                            <i class="<?= htmlspecialchars($child['icone_class'] ?? 'fas fa-dot-circle') ?> text-base mr-3 text-primary-focus/70"></i>
                                            <?= htmlspecialchars($child['libelle_menu']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Notifications Panel -->
<div id="notifications-panel" class="fixed inset-y-0 right-0 w-80 bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out p-6 border-l border-base-200">
    <div class="flex justify-between items-center mb-6 border-b pb-4 border-base-200">
        <h3 class="text-2xl font-bold text-primary font-montserrat">Notifications</h3>
        <button class="btn btn-sm btn-ghost text-base-content/70 hover:text-primary transition-colors duration-200" id="close-notifications" aria-label="Fermer le panneau de notifications">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="space-y-4 overflow-y-auto h-[calc(100vh-120px)] pr-2">
        <!-- Exemple Notification (will be populated by JS) -->
        <div class="alert alert-info shadow-md rounded-lg animate-fade-in-right">
            <div>
                <i class="fas fa-info-circle text-xl"></i>
                <span class="font-medium">Bienvenue sur votre tableau de bord !</span>
            </div>
            <div class="text-xs text-base-content/60 mt-1">Il y a 5 minutes</div>
        </div>
        <div class="alert alert-warning shadow-md rounded-lg animate-fade-in-right">
            <div>
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <span class="font-medium">Votre email n'est pas validé. Veuillez vérifier votre boîte de réception.</span>
            </div>
            <div class="text-xs text-base-content/60 mt-1">Il y a 2 heures</div>
        </div>
        <div class="alert alert-success shadow-md rounded-lg animate-fade-in-right">
            <div>
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-medium">Votre rapport a été validé avec succès !</span>
            </div>
            <div class="text-xs text-base-content/60 mt-1">Hier</div>
        </div>
        <div class="alert alert-error shadow-md rounded-lg animate-fade-in-right">
            <div>
                <i class="fas fa-times-circle text-xl"></i>
                <span class="font-medium">Erreur lors de la soumission de votre rapport.</span>
            </div>
            <div class="text-xs text-base-content/60 mt-1">Il y a 3 jours</div>
        </div>
        <!-- Plus de notifications ici... -->
    </div>
    <div class="absolute bottom-0 left-0 w-full p-4 bg-base-100 border-t border-base-200">
        <button class="btn btn-sm btn-block btn-outline btn-primary">Voir toutes les notifications</button>
    </div>
</div>

<script>
    // Script pour le panneau de notifications
    document.addEventListener('DOMContentLoaded', () => {
        const toggleNotificationsBtn = document.getElementById('toggle-notifications');
        const closeNotificationsBtn = document.getElementById('close-notifications');
        const notificationsPanel = document.getElementById('notifications-panel');

        if (toggleNotificationsBtn && notificationsPanel) {
            toggleNotificationsBtn.addEventListener('click', () => {
                notificationsPanel.classList.toggle('translate-x-full');
            });
        }

        if (closeNotificationsBtn && notificationsPanel) {
            closeNotificationsBtn.addEventListener('click', () => {
                notificationsPanel.classList.add('translate-x-full');
            });
        }

        // Fermer le panneau si on clique en dehors (pour les petits écrans)
        document.addEventListener('click', (event) => {
            if (!notificationsPanel.contains(event.target) && !toggleNotificationsBtn.contains(event.target) && !notificationsPanel.classList.contains('translate-x-full')) {
                if (window.innerWidth < 1024) { // Seulement pour les écrans non-lg
                    notificationsPanel.classList.add('translate-x-full');
                }
            }
        });

        // Animation des messages flash (similaire à auth.php)
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            gsap.from(alert, {
                opacity: 0,
                y: -20,
                duration: 0.5,
                ease: "power2.out",
                onComplete: () => {
                    gsap.to(alert, {
                        opacity: 0,
                        y: -20,
                        delay: 7, // Disparaît après 7 secondes
                        duration: 0.5,
                        ease: "power2.in",
                        onComplete: () => alert.remove()
                    });
                }
            });
        });

        // Animation d'entrée du contenu principal
        gsap.from("main", {
            opacity: 0,
            y: 20,
            duration: 0.7,
            ease: "power2.out",
            delay: 0.2
        });
    });
</script>