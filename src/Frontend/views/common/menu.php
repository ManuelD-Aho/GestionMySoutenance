<nav class="main-sidebar">
    <ul class="sidebar-menu">
        <?php
        // $menu_items est passé par DashboardController
        if (isset($menu_items) && is_array($menu_items)) :
            foreach ($menu_items as $item) :
                ?>
                <li class="menu-item">
                    <a href="<?php echo htmlspecialchars($item['url']); ?>">
                        <i class="<?php echo htmlspecialchars($item['icon'] ?? 'fas fa-circle'); ?>"></i>
                        <span><?php echo htmlspecialchars($item['label']); ?></span>
                    </a>
                </li>
            <?php
            endforeach;
        endif;
        ?>
        <!-- Exemple d'éléments de menu statiques ou généraux si non gérés dynamiquement -->
        <li class="menu-item"><a href="/dashboard/profile"><i class="fas fa-user-circle"></i> <span>Mon Profil</span></a></li>
        <!-- ... autres liens généraux si besoin -->
    </ul>
</nav>