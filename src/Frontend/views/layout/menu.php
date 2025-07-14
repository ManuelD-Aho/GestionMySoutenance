<?php
// /src/Frontend/views/layout/menu.php

// Fonctions helpers (déjà dans votre fichier original)
if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('hasPermission')) { function hasPermission($p, $perms) { return in_array($p, $perms) || in_array('*', $perms); } }
if (!function_exists('isActive')) { function isActive($url, $current) { if(empty($url)) return false; $url=rtrim($url,'/'); $current=rtrim(strtok($current,'?'),'/'); return $url===$current || strpos($current.'/',$url.'/')===0; } }
if (!function_exists('convertIconToMaterial')) {
    function convertIconToMaterial($faIcon) {
        $map = ['fas fa-tachometer-alt' => 'dashboard', 'fas fa-cogs' => 'settings', 'fas fa-users' => 'people', 'fas fa-shield-alt' => 'security', 'fas fa-eye' => 'visibility', 'fas fa-user-graduate' => 'school', 'fas fa-gavel' => 'gavel', 'fas fa-user-tie' => 'work'];
        return $map[$faIcon] ?? 'circle';
    }
}

// Fonction récursive pour rendre le menu
function renderMenuRecursive($item, $user_permissions, $current_url) {
    if (!hasPermission($item['id_traitement'], $user_permissions)) {
        return '';
    }

    $hasChildren = !empty($item['enfants']);
    $url = $item['url_associee'] ?? '#';
    $icon = convertIconToMaterial($item['icone_class'] ?? '');
    $label = e($item['libelle_menu'] ?? 'Menu Item');
    $isActive = isActive($url, $current_url);

    if ($hasChildren) {
        // C'est un sous-menu
        $html = '<li><details';
        // Vérifier si un enfant est actif pour ouvrir le parent
        $isParentActive = false;
        foreach ($item['enfants'] as $child) {
            if (isActive($child['url_associee'] ?? '', $current_url)) {
                $isParentActive = true;
                break;
            }
        }
        if ($isParentActive) {
            $html .= ' open';
        }
        $html .= '><summary><span class="material-icons">' . $icon . '</span>' . $label . '</summary><ul>';
        foreach ($item['enfants'] as $child) {
            $html .= renderMenuRecursive($child, $user_permissions, $current_url);
        }
        $html .= '</ul></details></li>';
        return $html;
    } else {
        // C'est un lien simple
        return '<li class="' . ($isActive ? 'active' : '') . '"><a href="' . e($url) . '"><span class="material-icons">' . $icon . '</span>' . $label . '</a></li>';
    }
}

$current_url = $_SERVER['REQUEST_URI'];
$user_permissions = $_SESSION['user_group_permissions'] ?? [];
?>
<ul class="menu p-4 w-80 min-h-full bg-base-200 text-base-content">
    <!-- Logo de la sidebar -->
    <li class="menu-title text-center text-lg font-bold p-4">
        <a href="/dashboard">GestionMySoutenance</a>
    </li>
    <div class="divider mt-0"></div>

    <?php
    if (!empty($menu_items)) {
        foreach ($menu_items as $item) {
            echo renderMenuRecursive($item, $user_permissions, $current_url);
        }
    } else {
        echo '<li><a>Aucun menu disponible</a></li>';
    }
    ?>
</ul>