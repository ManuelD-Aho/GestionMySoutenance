<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GestionMySoutenance') ?></title>
    <link href="/assets/css/app.css" rel="stylesheet">
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
</head>
<body class="bg-base-200">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

    <!-- Contenu principal de la page -->
    <div class="drawer-content flex flex-col">
        <!-- Barre de navigation -->
        <header class="navbar bg-base-100 shadow-sm sticky top-0 z-30">
            <div class="flex-none lg:hidden">
                <label for="my-drawer-2" aria-label="open sidebar" class="btn btn-square btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </label>
            </div>
            <div class="flex-1">
                <a class="btn btn-ghost text-xl normal-case"><?= htmlspecialchars($title ?? 'Dashboard') ?></a>
            </div>
            <div class="flex-none">
                <!-- Menu utilisateur -->
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full">
                            <img src="https://placehold.co/40x40/a0aec0/ffffff/png?text=<?= htmlspecialchars(strtoupper(substr($user['login_utilisateur'] ?? 'U', 0, 1))) ?>" alt="Avatar" />
                        </div>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li>
                            <a href="/etudiant/profil" class="justify-between">
                                Profil
                                <span class="badge">Nouveau</span>
                            </a>
                        </li>
                        <li><a>Paramètres</a></li>
                        <li><a href="/logout">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Contenu de la vue spécifique -->
        <main class="flex-1 p-4 lg:p-6">
            <!-- Affichage des alertes de session -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div role="alert" class="alert alert-success mb-4">
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div role="alert" class="alert alert-error mb-4">
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Menu latéral (Drawer) -->
    <aside class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        <ul class="menu p-4 w-80 min-h-full bg-base-100 text-base-content">
            <!-- Logo/Titre -->
            <li class="mb-4">
                <a href="/dashboard" class="text-2xl font-bold text-primary">GestionMySoutenance</a>
            </li>

            <!-- NOTE: Le menu ci-dessous doit être rendu dynamiquement en fonction des permissions de l'utilisateur -->
            <li><a href="/dashboard"><svg_icon>Dashboard</svg_icon></a></li>

            <?php if ($this->serviceSecurite->utilisateurPossedePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE')): ?>
                <li><a href="/etudiant/rapport"><svg_icon>Mon Rapport</svg_icon></a></li>
            <?php endif; ?>

            <?php if ($this->serviceSecurite->utilisateurPossedePermission('ADMIN_USERS_LIST')): ?>
                <li class="menu-title"><span>Administration</span></li>
                <li><a href="/admin/dashboard"><svg_icon>Dashboard Admin</svg_icon></a></li>
                <li><a href="/admin/users"><svg_icon>Utilisateurs</svg_icon></a></li>
                <li><a href="/admin/config"><svg_icon>Configuration</svg_icon></a></li>
                <li><a href="/admin/supervision/logs"><svg_icon>Logs</svg_icon></a></li>
            <?php endif; ?>

            <?php if ($this->serviceSecurite->utilisateurPossedePermission('COMMISSION_SESSIONS_LIST')): ?>
                <li class="menu-title"><span>Commission</span></li>
                <li><a href="/commission/dashboard"><svg_icon>Dashboard Commission</svg_icon></a></li>
                <li><a href="/commission/sessions"><svg_icon>Sessions</svg_icon></a></li>
            <?php endif; ?>
        </ul>
    </aside>
</div>

<!-- Scripts globaux de l'application -->
<script src="/assets/js/app.js" defer></script>
</body>
</html>