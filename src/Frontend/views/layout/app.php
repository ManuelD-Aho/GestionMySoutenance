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
            <div class="flex-none gap-2">
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="/assets/images/avatars/<?= htmlspecialchars($utilisateurConnecte['photo_profil'] ?? 'default.png') ?>" alt="Avatar" />
                        </div>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="/profil">Profil</a></li>
                        <li><a>Paramètres</a></li>
                        <li class="divider"></li>
                        <li><a href="/logout">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Bannière d'impersonation -->
        <?php if ($estEnModeImpersonation && $impersonatorData): ?>
            <div class="bg-warning text-warning-content text-center p-2 sticky top-16 z-20">
                Vous agissez en tant que <strong><?= htmlspecialchars($utilisateurConnecte['prenom'] . ' ' . $utilisateurConnecte['nom']) ?></strong>.
                <a href="/stop-impersonating" class="btn btn-xs btn-outline ml-4">Arrêter l'impersonation</a>
            </div>
        <?php endif; ?>

        <main class="flex-1 p-4 lg:p-6">
            <?= $content ?? '' ?>
        </main>
    </div>

    <aside class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        <ul class="menu p-4 w-80 min-h-full bg-base-100 text-base-content">
            <li class="mb-4">
                <a href="/dashboard" class="text-2xl font-bold text-primary">GestionMySoutenance</a>
            </li>

            <!-- Menu dynamique généré par ServiceSecurite -->
            <?php foreach ($menuItems as $item): ?>
                <?php if (empty($item['enfants'])): ?>
                    <li><a href="<?= htmlspecialchars($item['url_associee']) ?>"><i class="<?= htmlspecialchars($item['icone_class']) ?> mr-2"></i><?= htmlspecialchars($item['libelle_menu']) ?></a></li>
                <?php else: ?>
                    <li>
                        <details>
                            <summary><i class="<?= htmlspecialchars($item['icone_class']) ?> mr-2"></i><?= htmlspecialchars($item['libelle_menu']) ?></summary>
                            <ul>
                                <?php foreach ($item['enfants'] as $enfant): ?>
                                    <li><a href="<?= htmlspecialchars($enfant['url_associee']) ?>"><?= htmlspecialchars($enfant['libelle_menu']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </aside>
</div>

<script src="/assets/js/app.js" defer></script>
</body>
</html>