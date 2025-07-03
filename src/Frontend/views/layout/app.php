<?php
/**
 * Layout principal modernisé - GestionMySoutenance
 * Version mise à jour avec le nouveau système CSS/JS
 */

// Fonction d'échappement HTML, au cas où elle ne serait pas déjà définie globalement
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// $pageTitle doit être défini par le contrôleur de la vue spécifique chargée
$pageTitle = $pageTitle ?? 'GestionMySoutenance - Tableau de Bord';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gestion des messages flash modernisée
$flash_messages = $_SESSION['flash_messages'] ?? [];
$has_flash_messages = !empty(array_filter($flash_messages));

// Récupérer l'URL courante pour le menu actif
$current_url = $_SERVER['REQUEST_URI'];
$current_url = strtok($current_url, '?'); // Nettoyer les paramètres GET

// Déterminer si on est sur une page admin
$is_admin_page = strpos($current_url, '/admin') === 0 ||
    (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

// Déterminer le type d'utilisateur pour les assets
$user_role = $_SESSION['user_role'] ?? 'guest';
$current_user = $current_user ?? null;

// Classes CSS pour le body
$body_classes = ['sidebar-open'];
if ($is_admin_page) {
    $body_classes[] = 'admin-layout';
}
if (isset($body_class)) {
    $body_classes[] = $body_class;
}

// Version des assets (pour le cache)
$asset_version = defined('ASSET_VERSION') ? ASSET_VERSION : '1.0.0';
$is_development = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$cache_buster = $is_development ? '?v=' . time() : '?v=' . $asset_version;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion MySoutenance - Plateforme de gestion académique">
    <meta name="author" content="GestionMySoutenance">
    <meta name="robots" content="noindex, nofollow">

    <title><?= e($pageTitle); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- CSS Principal - Ordre d'importance -->
    <link rel="stylesheet" href="/assets/css/root.css<?= $cache_buster ?>">
    <link rel="stylesheet" href="/assets/css/style.css<?= $cache_buster ?>">
    <link rel="stylesheet" href="/assets/css/components.css<?= $cache_buster ?>">

    <!-- CSS Admin (si nécessaire) -->
    <?php if ($is_admin_page): ?>
        <link rel="stylesheet" href="/assets/css/admin-enhanced.css<?= $cache_buster ?>">
    <?php endif; ?>

    <!-- CSS Legacy (conservation compatibilité) -->
    <link rel="stylesheet" href="/assets/css/gestionsoutenance-dashboard.css<?= $cache_buster ?>">

    <!-- CSS Utilitaires (toujours en dernier) -->
    <link rel="stylesheet" href="/assets/css/utilities.css<?= $cache_buster ?>">

    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- CSS spécifiques à la page -->
    <?php if (isset($page_css) && is_array($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?><?= $cache_buster ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Meta tags pour PWA (futur) -->
    <meta name="theme-color" content="#28b707">
    <meta name="msapplication-navbutton-color" content="#28b707">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Preload des ressources critiques -->
    <link rel="preload" href="/assets/js/main.js" as="script">
    <?php if ($is_admin_page): ?>
        <link rel="preload" href="/assets/js/admin.js" as="script">
    <?php endif; ?>
</head>
<body class="<?= implode(' ', $body_classes); ?>" data-user-role="<?= e($user_role); ?>">

<!-- Loading Screen (optionnel) -->
<div id="app-loading" class="d-none">
    <div class="admin-loading">
        <div class="admin-spinner"></div>
        <span>Chargement...</span>
    </div>
</div>

<!-- Mobile Sidebar Toggle -->
<button class="mobile-sidebar-toggle d-md-none" onclick="window.GestionMySoutenance?.toggleMobileSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="app-layout">

        <?php
        // Inclusion du header
        $header_data = [
            'pageTitle' => $pageTitle,
            'current_user' => $current_user,
            'is_admin_page' => $is_admin_page,
            'notifications_count' => $_SESSION['notifications_count'] ?? 0
        ];

        // Le header n'est affiché que si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])):
            require_once __DIR__ . '/../common/header.php';
        endif;
        ?>

        <?php
        // Inclusion du menu latéral
        $menu_data = [
            'current_url' => $current_url,
            'user_role' => $user_role,
            'user_permissions' => $_SESSION['user_permissions'] ?? [],
            'is_admin_page' => $is_admin_page
        ];

        // Le menu n'est affiché que si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])):
            require_once __DIR__ . '/../common/menu.php';
        endif;
        ?>



        <!-- Zone de contenu principal -->
        <main class="main-content-area<?= isset($_SESSION['user_id']) ? '' : ' no-sidebar' ?>" id="mainContentArea">

            <!-- Breadcrumb (si défini) -->
            <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                <div class="breadcrumb-container">
                    <nav class="breadcrumb">
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php if ($index > 0): ?>
                                <span class="breadcrumb-separator">
                                    <span class="material-icons">chevron_right</span>
                                </span>
                            <?php endif; ?>

                            <?php if (isset($item['url']) && $index < count($breadcrumb) - 1): ?>
                                <a href="<?= e($item['url']) ?>" class="breadcrumb-item">
                                    <?= e($item['label']) ?>
                                </a>
                            <?php else: ?>
                                <span class="breadcrumb-item active"><?= e($item['label']) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                </div>
            <?php endif; ?>

            <!-- Messages d'alerte système (si définis) -->
            <?php if (isset($system_alerts) && is_array($system_alerts)): ?>
                <?php foreach ($system_alerts as $alert): ?>
                    <div class="admin-alert <?= e($alert['type']) ?> mb-4">
                        <span class="material-icons"><?= e($alert['icon'] ?? 'info') ?></span>
                        <div class="admin-alert-content">
                            <?php if (isset($alert['title'])): ?>
                                <div class="admin-alert-title"><?= e($alert['title']) ?></div>
                            <?php endif; ?>
                            <div class="admin-alert-text"><?= e($alert['message']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Contenu de la page -->
            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php elseif (isset($view_content)): ?>
                <!-- Alternative si le contenu est passé directement -->
                <?= $view_content ?>
            <?php else: ?>
                <!-- Fallback en cas de problème -->
                <div class="admin-module-container">
                    <div class="admin-card text-center">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <span class="material-icons">error_outline</span>
                            </div>
                            <h2 class="empty-state-title">Contenu non trouvé</h2>
                            <p class="empty-state-description">
                                Le contenu de cette page n'a pas pu être chargé.
                                Veuillez vérifier le routeur ou contacter l'administrateur.
                            </p>
                            <button class="empty-state-action" onclick="location.reload()">
                                <span class="material-icons">refresh</span>
                                Actualiser la page
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>

</div>

<!-- Scripts JavaScript - Ordre d'importance -->
<script src="/assets/js/main.js<?= $cache_buster ?>"></script>

<!-- Scripts Admin (si nécessaire) -->
<?php if ($is_admin_page): ?>
    <script src="/assets/js/admin.js<?= $cache_buster ?>"></script>
<?php endif; ?>

<!-- Scripts Chart.js (si nécessaire) -->
<?php if (isset($include_charts) && $include_charts): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<!-- Scripts spécifiques à la page -->
<?php if (isset($page_js) && is_array($page_js)): ?>
    <?php foreach ($page_js as $js): ?>
        <script src="<?= $js ?><?= $cache_buster ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Initialisation des messages flash -->
<?php if ($has_flash_messages): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($flash_messages as $type => $message): ?>
            <?php if (!empty($message)): ?>
            <?php if (is_array($message)): ?>
            window.GestionMySoutenance?.showFlashMessage(
                '<?= e($type) ?>',
                '<?= e($message['message'] ?? '') ?>',
                '<?= e($message['title'] ?? '') ?>'
            );
            <?php else: ?>
            window.GestionMySoutenance?.showFlashMessage('<?= e($type) ?>', '<?= e($message) ?>');
            <?php endif; ?>
            <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>
<?php endif; ?>

<!-- Configuration globale JavaScript -->
<script>
    window.AppConfig = {
        baseUrl: '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>',
        csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>',
        userId: <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>,
        userRole: '<?= e($user_role) ?>',
        isAdmin: <?= $is_admin_page ? 'true' : 'false' ?>,
        isDevelopment: <?= $is_development ? 'true' : 'false' ?>,
        version: '<?= $asset_version ?>'
    };
</script>

<!-- Scripts d'initialisation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le titre de la page dans le header
        const pageTitle = <?= json_encode($pageTitle) ?>;

        // Marquer la page comme chargée
        document.body.classList.add('page-loaded');

        // Masquer le loading screen s'il était affiché
        const loadingScreen = document.getElementById('app-loading');
        if (loadingScreen) {
            loadingScreen.classList.add('d-none');
        }

        // Initialiser les tooltips
        if (window.GestionMySoutenance?.initTooltips) {
            window.GestionMySoutenance.initTooltips();
        }

        // Event personnalisé pour signaler que l'app est prête
        window.dispatchEvent(new CustomEvent('app:ready', {
            detail: {
                pageTitle: pageTitle,
                userRole: '<?= e($user_role) ?>',
                isAdmin: <?= $is_admin_page ? 'true' : 'false' ?>
            }
        }));

        // Debug info en développement
        <?php if ($is_development): ?>
        console.log('🚀 GestionMySoutenance initialisé', {
            pageTitle: pageTitle,
            userRole: '<?= e($user_role) ?>',
            isAdmin: <?= $is_admin_page ? 'true' : 'false' ?>,
            version: '<?= $asset_version ?>'
        });
        <?php endif; ?>
    });

    // Gestion des erreurs globales
    window.addEventListener('error', function(e) {
        console.error('Erreur JavaScript:', e.error);
        <?php if (!$is_development): ?>
        // En production, on peut envoyer l'erreur au serveur
        // fetch('/api/log-error', { method: 'POST', body: JSON.stringify({...}) });
        <?php endif; ?>
    });

    // Gestion des promesses rejetées
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Promesse rejetée:', e.reason);
    });
</script>

<?php
// Nettoyer les messages flash après affichage
unset($_SESSION['flash_messages']);
?>
</body>
</html>