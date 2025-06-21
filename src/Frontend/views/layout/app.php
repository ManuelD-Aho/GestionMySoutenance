<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestionMySoutenance - <?php echo $page_title ?? 'Accueil'; ?></title>
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Vos styles CSS via AssetController -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard_style.css">
    <!-- Ajouter d'autres CSS spécifiques si nécessaire -->
    <?php if (isset($assets_css)) : ?>
        <?php foreach ($assets_css as $css_file) : ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="dashboard-container">
    <?php
    // Inclusion du header
    // Le header est inclus ici mais sera responsable d'afficher les infos utilisateur, notifications, etc.
    // Il accède à $current_user et $flash_messages
    require __DIR__ . '/../common/header.php';
    ?>

    <?php
    // Inclusion du menu latéral
    // Le menu accède à $menu_items
    require __DIR__ . '/../common/menu.php';
    ?>

    <main class="main-content">
        <div class="page-header">
            <h1><?php echo $page_title ?? 'Page'; ?></h1>
        </div>

        <?php
        // Affichage des messages flash
        if (isset($flash_messages) && is_array($flash_messages)) {
            foreach ($flash_messages as $type => $message) {
                // Utiliser des classes CSS pour styliser les alertes (success, error, warning)
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            }
        }
        ?>

        <div class="content-area">
            <?php
            // C'est ici que le contenu spécifique de chaque vue sera injecté
            echo $content;
            ?>
        </div>
    </main>
</div>

<!-- Vos scripts JS via AssetController -->
<script src="/assets/js/dashboard.js"></script>
<!-- Ajouter d'autres JS spécifiques si nécessaire -->
<?php if (isset($assets_js)) : ?>
    <?php foreach ($assets_js as $js_file) : ?>
        <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>