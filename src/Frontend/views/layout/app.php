<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestionMySoutenance - <?php echo htmlspecialchars($pageTitle ?? 'Accueil', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard_style.css">
    <?php if (isset($assets_css)) : ?>
        <?php foreach ($assets_css as $css_file) : ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="dashboard-container">
    <?php
    // Inclusion du header
    // Le header est inclus ici mais sera responsable d'afficher les infos utilisateur, notifications, etc.
    // Il accède à $current_user et $flash_messages
    require_once ROOT_PATH . '/src/Frontend/views/common/header.php';
    ?>

    <?php
    // Inclusion du menu latéral
    // Le menu accède à $menu_items
    require_once ROOT_PATH . '/src/Frontend/views/common/menu.php';
    ?>

    <main class="main-content">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($pageTitle ?? 'Page', ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>

        <?php
        // Affichage des messages flash
        // CORRECTION : S'assurer que $type et $message ne sont jamais null
        if (isset($flash_messages) && is_array($flash_messages)) {
            foreach ($flash_messages as $type => $message) {
                // Utiliser des classes CSS pour styliser les alertes (success, error, warning)
                if (!empty($message)) { // S'assurer que le message n'est pas vide
                    echo '<div class="alert alert-' . htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') . '</div>';
                }
            }
        }
        ?>

        <div class="content-area">
            <?php
            // C'est ici que le contenu spécifique de chaque vue sera injecté
            // La variable $content est rendue disponible par le BaseController::render()
            echo $content;
            ?>
        </div>
    </main>
</div>

<script src="/assets/js/dashboard.js"></script>
<?php if (isset($assets_js)) : ?>
    <?php foreach ($assets_js as $js_file) : ?>
        <script src="<?php echo htmlspecialchars($js_file, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>