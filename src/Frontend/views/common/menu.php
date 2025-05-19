<?php
// File: src/Frontend/views/common/menu.php
// Description: Menu de navigation latéral.
// Nécessite $menuItems (tableau d'items de menu) et $currentUri (URI actuelle).

// $menuItems est un tableau de tableaux, ex:
// [
//   ['label' => 'Tableau de bord', 'url' => '/dashboard', 'icon' => '<svg>...</svg>'],
//   ['label' => 'Utilisateurs', 'url' => '/admin/users', 'icon' => '<svg>...</svg>', 'role' => 'Administrateur Système'],
// ]
// $currentUri est l'URI de la page actuelle pour mettre en surbrillance l'élément actif.
?>
<aside id="sidebar" class="w-64 bg-neutral-dark text-gray-100 flex-shrink-0 h-full lg:h-screen flex flex-col transition-all duration-300 ease-in-out lg:sticky lg:top-0">
    <div class="h-16 flex items-center justify-center px-4 border-b border-gray-700">
        <img src="https://placehold.co/40x40/2563eb/white?text=GS" alt="Logo Gestion Soutenance" class="h-8 w-8 mr-2 rounded-full">
        <span class="sidebar-brand-text text-lg font-semibold">GestionSoutenance</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 space-y-1">
        <?php if (!empty($menuItems)): ?>
            <?php foreach ($menuItems as $item): ?>
                <?php
                // Vérifier si l'item est actif.
                // Un item est actif si son URL correspond exactement à l'URI actuelle,
                // ou si l'URI actuelle commence par l'URL de l'item (pour les sous-pages)
                // et que l'URL de l'item n'est pas juste "/" (pour éviter que le dashboard soit toujours actif).
                $isActive = ($item['url'] === $currentUri) ||
                    (strlen($item['url']) > 1 && strpos($currentUri, $item['url']) === 0) ||
                    ($item['url'] === '/dashboard' && $currentUri === '/'); // Cas spécial pour /dashboard et /
                ?>
                <a href="<?= htmlspecialchars($item['url']) ?>"
                   class="flex items-center py-2.5 px-4 rounded-md mx-2 transition-colors duration-200 hover:bg-gray-700 hover:text-white <?= $isActive ? 'bg-primary text-white font-medium shadow-lg' : 'text-gray-300' ?>">
                    <span class="sidebar-icon w-5 h-5 mr-3"><?= $item['icon'] // Ex: SVG string or FontAwesome class ?></span>
                    <span class="sidebar-text"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="p-4 text-gray-400 sidebar-text">Menu non disponible.</p>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-gray-700 hidden lg:block">
        <button id="desktopCollapseButton" title="Réduire/Agrandir le menu" class="w-full flex items-center justify-center p-2 text-gray-400 hover:bg-gray-700 hover:text-white rounded-md">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sidebar-icon-toggle-open">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sidebar-icon-toggle-closed">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</aside>
