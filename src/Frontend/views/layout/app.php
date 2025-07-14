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
// Note: Le BaseController démarre déjà la session. Ce bloc peut être redondant ici.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Les flash messages sont maintenant passés via $data de render(),
// donc ils sont disponibles directement comme $flash_messages dans ce scope.
// La ligne suivante peut être supprimée si $flash_messages est toujours passé explicitement.
// $flash_messages = $_SESSION['flash_messages'] ?? []; // Redondant si BaseController.php est à jour
$has_flash_messages = !empty($flash_messages); // Utiliser directement $flash_messages reçu via extract($data)

// Récupérer l'URL courante pour le menu actif
$current_url = $_SERVER['REQUEST_URI'];
$current_url = strtok($current_url, '?'); // Nettoyer les paramètres GET

// Déterminer si on est sur une page admin
$is_admin_page = strpos($current_url, '/admin') === 0 ||
    (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'); // Assurez-vous que user_role est défini

// Déterminer le type d'utilisateur pour les assets
$user_role = $_SESSION['user_data']['id_type_utilisateur'] ?? 'guest'; // Utiliser l'ID du type d'utilisateur
$current_user = $current_user ?? null; // current_user est passé via $data['user'] dans BaseController

// Classes CSS pour le body
$body_classes = ['sidebar-open'];
if ($is_admin_page) {
    $body_classes[] = 'admin-layout';
}
if (isset($body_class)) { // $body_class peut être défini par des vues spécifiques
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

    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <link rel="stylesheet" href="/assets/css/root.css<?= $cache_buster ?>">
    <link rel="stylesheet" href="/assets/css/style.css<?= $cache_buster ?>">
    <link rel="stylesheet" href="/assets/css/components.css<?= $cache_buster ?>">

    <?php if ($is_admin_page): ?>
        <link rel="stylesheet" href="/assets/css/admin-enhanced.css<?= $cache_buster ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/assets/css/gestionsoutenance-dashboard.css<?= $cache_buster ?>">

    <link rel="stylesheet" href="/assets/css/utilities.css<?= $cache_buster ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php if (isset($page_css) && is_array($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link rel="stylesheet" href="<?= e($css) ?><?= $cache_buster ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <meta name="theme-color" content="#28b707">
    <meta name="msapplication-navbutton-color" content="#28b707">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="preload" href="/assets/js/main.js" as="script">
    <?php if ($is_admin_page): ?>
        <link rel="preload" href="/assets/js/admin.js" as="script">
    <?php endif; ?>
</head>
<body class="<?= e(implode(' ', $body_classes)); ?>" data-user-role="<?= e($user_role); ?>">

<div id="app-loading" class="d-none">
    <div class="admin-loading">
        <div class="admin-spinner"></div>
        <span>Chargement...</span>
    </div>
</div>

<button class="mobile-sidebar-toggle d-md-none" onclick="window.GestionMySoutenance?.toggleMobileSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="app-layout">

    <?php
    // Inclusion du menu latéral
    // BaseController passe déjà $data['menu_items']
    $menu_data = [
        'current_url' => $current_url,
        'user_role' => $user_role,
        // user_permissions n'est plus directement dans la session ici car il est combiné dans $menu_items
        'is_admin_page' => $is_admin_page,
        'menu_items' => $menu_items // Passer les items de menu déjà construits par SecuriteService
    ];

    // Le menu n'est affiché que si l'utilisateur est connecté
    if (isset($user['numero_utilisateur'])): // Utiliser $user de `extract($data)`
        require_once __DIR__ . '/../common/menu.php'; // Ce fichier doit inclure les éléments de menu
    endif;
    ?>
    <div>
        <?php
        // Inclusion du header
        $header_data = [
            'pageTitle' => $pageTitle,
            'current_user' => $user, // Passer l'objet user complet ici
            'is_admin_page' => $is_admin_page,
            'notifications_count' => $_SESSION['notifications_count'] ?? 0 // Si toujours géré via session
        ];

        // Le header n'est affiché que si l'utilisateur est connecté
        if (isset($user['numero_utilisateur'])): // Utiliser $user de `extract($data)`
            require_once __DIR__ . '/../common/header.php';
        endif;
        ?>

        <main class="main-content-area<?= isset($user['numero_utilisateur']) ? '' : ' no-sidebar' ?>" id="mainContentArea">

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

            <?php if (!empty($flash_messages)): ?>
                <?php foreach ($flash_messages as $message_item): ?>
                    <?php
                    // Assurez-vous que chaque message_item est un tableau avec 'type' et 'message'
                    $msg_type = $message_item['type'] ?? 'info';
                    $msg_content = $message_item['message'] ?? 'Message inconnu.';
                    ?>
                    <div class="alert alert-<?= e($msg_type) ?> alert-dismissible fade show mb-4" role="alert">
                        <?= e($msg_content) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php elseif (isset($view_content)): // Alternative si le contenu est passé directement (moins courant) ?>
                <?= $view_content ?>
            <?php else: // Fallback en cas de problème de contenu ?>
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
</div>

<script src="/assets/js/main.js<?= $cache_buster ?>"></script>

<?php if ($is_admin_page): ?>
    <script src="/assets/js/admin.js<?= $cache_buster ?>"></script>
<?php endif; ?>

<?php if (isset($include_charts) && $include_charts): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<?php if (isset($page_js) && is_array($page_js)): ?>
    <?php foreach ($page_js as $js): ?>
        <script src="<?= e($js) ?><?= $cache_buster ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($has_flash_messages): // $has_flash_messages est basé sur $flash_messages, qui a déjà été vidé de la session ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($flash_messages as $message_item): // Parcourir correctement le tableau de tableaux ?>
            window.GestionMySoutenance?.showFlashMessage(
                '<?= e($message_item['type'] ?? 'info') ?>', // Utiliser le type réel
                '<?= e($message_item['message'] ?? 'Message flash vide.') ?>', // Utiliser le message réel
                '<?= e($message_item['title'] ?? '') ?>' // Utiliser le titre si disponible
            );
            <?php endforeach; ?>
        });
    </script>
<?php endif; ?>

<script>
    window.AppConfig = {
        baseUrl: '<?= rtrim(dirname(e($_SERVER['SCRIPT_NAME'])), '/') ?>', // Correction: Échapper l'URL
        csrfToken: '<?= e($_SESSION['csrf_token'] ?? '') ?>', // Correction: Échapper le token
        userId: <?= isset($_SESSION['user_id']) ? json_encode(e($_SESSION['user_id'])) : 'null' ?>, // Correction: Échapper et encoder
        userRole: '<?= e($user_role) ?>',
        isAdmin: <?= $is_admin_page ? 'true' : 'false' ?>,
        isDevelopment: <?= $is_development ? 'true' : 'false' ?>,
        version: '<?= e($asset_version) ?>'
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le titre de la page dans le header
        const pageTitle = <?= json_encode(e($pageTitle)) ?>; // Correction: Échapper et encoder

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
            version: '<?= e($asset_version) ?>'
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
// CE UNSET EST MAINTENANT REDONDANT ET PEUT ÊTRE SUPPRIMÉ CAR BaseController::render le fait déjà.
// Il ne fait pas de mal s'il est appelé après la page HTML rendue, mais il n'est pas nécessaire.
// unset($_SESSION['flash_messages']);
?>
</body>
</html>