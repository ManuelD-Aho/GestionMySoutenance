<?php
/**
 * ==============================================
 * HELPERS PHP POUR LE SYSTÈME CSS/JS
 * GestionMySoutenance - Utilitaires d'interface
 * ==============================================
 */

/**
 * Classe utilitaire pour générer des éléments d'interface
 */
class UIHelper
{
    /**
     * Génère un bouton avec les classes appropriées
     */
    public static function button(string $text, array $options = []): string
    {
        $type = $options['type'] ?? 'primary';
        $size = $options['size'] ?? '';
        $icon = $options['icon'] ?? '';
        $attributes = $options['attributes'] ?? [];
        $classes = $options['classes'] ?? [];

        // Classes de base
        $buttonClasses = ['admin-btn', "admin-btn-{$type}"];

        // Ajouter la taille si spécifiée
        if ($size) {
            $buttonClasses[] = "admin-btn-{$size}";
        }

        // Ajouter les classes personnalisées
        $buttonClasses = array_merge($buttonClasses, $classes);

        // Construire les attributs
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
        }

        // Construire le contenu du bouton
        $content = '';
        if ($icon) {
            $content .= sprintf('<span class="material-icons">%s</span>', $icon);
        }
        $content .= $text;

        return sprintf(
            '<button class="%s"%s>%s</button>',
            implode(' ', $buttonClasses),
            $attributeString,
            $content
        );
    }

    /**
     * Génère un badge de statut
     */
    public static function badge(string $text, string $type = 'neutral'): string
    {
        return sprintf(
            '<span class="admin-badge %s">%s</span>',
            $type,
            htmlspecialchars($text)
        );
    }

    /**
     * Génère une icône Material Icons
     */
    public static function icon(string $name, array $options = []): string
    {
        $classes = $options['classes'] ?? [];
        $classes[] = 'material-icons';

        $attributes = '';
        if (isset($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                $attributes .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
            }
        }

        return sprintf(
            '<span class="%s"%s>%s</span>',
            implode(' ', $classes),
            $attributes,
            $name
        );
    }

    /**
     * Génère une alerte
     */
    public static function alert(string $message, string $type = 'info', string $title = ''): string
    {
        $icons = [
            'success' => 'check_circle',
            'warning' => 'warning',
            'danger' => 'error',
            'info' => 'info'
        ];

        $icon = $icons[$type] ?? 'info';

        $content = sprintf('<span class="material-icons">%s</span>', $icon);
        $content .= '<div class="admin-alert-content">';

        if ($title) {
            $content .= sprintf('<div class="admin-alert-title">%s</div>', htmlspecialchars($title));
        }

        $content .= sprintf('<div class="admin-alert-text">%s</div>', htmlspecialchars($message));
        $content .= '</div>';

        return sprintf(
            '<div class="admin-alert %s">%s</div>',
            $type,
            $content
        );
    }

    /**
     * Génère une carte de statistique
     */
    public static function statCard(array $options = []): string
    {
        $label = $options['label'] ?? '';
        $value = $options['value'] ?? '0';
        $icon = $options['icon'] ?? 'analytics';
        $iconColor = $options['iconColor'] ?? 'success';
        $trend = $options['trend'] ?? '';
        $trendType = $options['trendType'] ?? '';

        $trendHtml = '';
        if ($trend) {
            $trendIcon = $trendType === 'positive' ? 'trending_up' :
                ($trendType === 'negative' ? 'trending_down' : '');

            $trendHtml = sprintf(
                '<div class="admin-stat-trend %s">%s%s</div>',
                $trendType,
                $trendIcon ? sprintf('<span class="material-icons">%s</span>', $trendIcon) : '',
                htmlspecialchars($trend)
            );
        }

        return sprintf(
            '<div class="admin-stat-card">
                <div class="admin-stat-header">
                    <h3 class="admin-stat-label">%s</h3>
                    <div class="admin-stat-icon %s">
                        <span class="material-icons">%s</span>
                    </div>
                </div>
                <div class="admin-stat-value">%s</div>
                %s
            </div>',
            htmlspecialchars($label),
            $iconColor,
            $icon,
            htmlspecialchars($value),
            $trendHtml
        );
    }

    /**
     * Génère un champ de formulaire
     */
    public static function formField(array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $name = $options['name'] ?? '';
        $label = $options['label'] ?? '';
        $value = $options['value'] ?? '';
        $required = $options['required'] ?? false;
        $attributes = $options['attributes'] ?? [];
        $help = $options['help'] ?? '';
        $error = $options['error'] ?? '';

        $html = '<div class="form-group">';

        // Label
        if ($label) {
            $html .= sprintf(
                '<label class="form-label">%s%s</label>',
                htmlspecialchars($label),
                $required ? ' <span class="required">*</span>' : ''
            );
        }

        // Champ
        $fieldClasses = ['form-input'];
        if ($error) {
            $fieldClasses[] = 'is-invalid';
        }

        $attributeString = sprintf('class="%s"', implode(' ', $fieldClasses));
        foreach ($attributes as $key => $val) {
            $attributeString .= sprintf(' %s="%s"', $key, htmlspecialchars($val));
        }

        switch ($type) {
            case 'textarea':
                $html .= sprintf(
                    '<textarea name="%s" %s>%s</textarea>',
                    $name,
                    $attributeString,
                    htmlspecialchars($value)
                );
                break;

            case 'select':
                $options = $options['options'] ?? [];
                $html .= sprintf('<select name="%s" %s>', $name, $attributeString);
                foreach ($options as $optValue => $optLabel) {
                    $selected = ($optValue == $value) ? ' selected' : '';
                    $html .= sprintf(
                        '<option value="%s"%s>%s</option>',
                        htmlspecialchars($optValue),
                        $selected,
                        htmlspecialchars($optLabel)
                    );
                }
                $html .= '</select>';
                break;

            default:
                $html .= sprintf(
                    '<input type="%s" name="%s" value="%s" %s>',
                    $type,
                    $name,
                    htmlspecialchars($value),
                    $attributeString
                );
        }

        // Aide
        if ($help) {
            $html .= sprintf('<div class="form-help">%s</div>', htmlspecialchars($help));
        }

        // Erreur
        if ($error) {
            $html .= sprintf('<div class="form-error">%s</div>', htmlspecialchars($error));
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Génère un système d'onglets
     */
    public static function tabs(array $tabs, string $activeTab = ''): string
    {
        if (empty($tabs)) {
            return '';
        }

        if (!$activeTab) {
            $activeTab = array_key_first($tabs);
        }

        $html = '<div class="admin-tabs">';

        foreach ($tabs as $tabId => $tabData) {
            $label = $tabData['label'] ?? $tabId;
            $icon = $tabData['icon'] ?? '';
            $badge = $tabData['badge'] ?? '';
            $active = ($tabId === $activeTab) ? ' active' : '';

            $content = '';
            if ($icon) {
                $content .= sprintf('<span class="material-icons">%s</span>', $icon);
            }
            $content .= htmlspecialchars($label);
            if ($badge) {
                $content .= sprintf('<span class="nav-badge">%s</span>', htmlspecialchars($badge));
            }

            $html .= sprintf(
                '<button class="admin-tab%s" data-tab="%s">%s</button>',
                $active,
                $tabId,
                $content
            );
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Génère une table avec les bonnes classes
     */
    public static function table(array $data, array $options = []): string
    {
        $headers = $options['headers'] ?? [];
        $actions = $options['actions'] ?? [];
        $sortable = $options['sortable'] ?? [];
        $searchable = $options['searchable'] ?? false;
        $title = $options['title'] ?? '';

        $html = '<div class="admin-table-container">';

        // Header de la table
        if ($title || $searchable || !empty($actions)) {
            $html .= '<div class="admin-table-header">';

            if ($title) {
                $html .= sprintf('<h3 class="admin-table-title">%s</h3>', htmlspecialchars($title));
            }

            $html .= '<div class="admin-table-actions">';

            if ($searchable) {
                $html .= '<input type="text" class="search-input" placeholder="Rechercher..." data-search-target=".admin-table">';
            }

            foreach ($actions as $action) {
                $html .= $action;
            }

            $html .= '</div></div>';
        }

        // Table
        $html .= '<table class="admin-table"><thead><tr>';

        foreach ($headers as $key => $header) {
            $sortableAttr = in_array($key, $sortable) ? ' data-sortable data-sort-key="' . $key . '"' : '';
            $html .= sprintf('<th%s>%s</th>', $sortableAttr, htmlspecialchars($header));
        }

        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $key => $header) {
                $value = $row[$key] ?? '';
                $html .= sprintf('<td>%s</td>', htmlspecialchars($value));
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }
}

/**
 * Classe pour gérer les assets (CSS/JS)
 */
class AssetManager
{
    private static array $css = [];
    private static array $js = [];
    private static string $version = '1.0.0';

    /**
     * Ajoute un fichier CSS
     */
    public static function addCss(string $file, int $priority = 10): void
    {
        self::$css[] = ['file' => $file, 'priority' => $priority];
    }

    /**
     * Ajoute un fichier JS
     */
    public static function addJs(string $file, int $priority = 10): void
    {
        self::$js[] = ['file' => $file, 'priority' => $priority];
    }

    /**
     * Génère les balises CSS
     */
    public static function renderCss(): string
    {
        // Trier par priorité
        usort(self::$css, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        $html = '';
        foreach (self::$css as $css) {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s?v=%s">' . PHP_EOL,
                $css['file'],
                self::$version
            );
        }

        return $html;
    }

    /**
     * Génère les balises JS
     */
    public static function renderJs(): string
    {
        // Trier par priorité
        usort(self::$js, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        $html = '';
        foreach (self::$js as $js) {
            $html .= sprintf(
                '<script src="%s?v=%s"></script>' . PHP_EOL,
                $js['file'],
                self::$version
            );
        }

        return $html;
    }

    /**
     * Initialise les assets de base
     */
    public static function initBaseAssets(): void
    {
        // CSS de base
        self::addCss('/assets/css/root.css', 1);
        self::addCss('/assets/css/style.css', 2);
        self::addCss('/assets/css/components.css', 3);
        self::addCss('/assets/css/utilities.css', 9);

        // JS de base
        self::addJs('/assets/js/main.js', 1);
    }

    /**
     * Initialise les assets admin
     */
    public static function initAdminAssets(): void
    {
        self::addCss('/assets/css/admin-enhanced.css', 4);
        self::addJs('/assets/js/admin.js', 2);
    }

    /**
     * Définit la version des assets
     */
    public static function setVersion(string $version): void
    {
        self::$version = $version;
    }
}

/**
 * Classe pour générer des messages flash
 */
class FlashMessage
{
    /**
     * Ajoute un message flash
     */
    public static function add(string $type, string $message, string $title = ''): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'][$type] = [
            'message' => $message,
            'title' => $title,
            'timestamp' => time()
        ];
    }

    /**
     * Ajoute un message de succès
     */
    public static function success(string $message, string $title = ''): void
    {
        self::add('success', $message, $title);
    }

    /**
     * Ajoute un message d'erreur
     */
    public static function error(string $message, string $title = ''): void
    {
        self::add('error', $message, $title);
    }

    /**
     * Ajoute un message d'avertissement
     */
    public static function warning(string $message, string $title = ''): void
    {
        self::add('warning', $message, $title);
    }

    /**
     * Ajoute un message d'information
     */
    public static function info(string $message, string $title = ''): void
    {
        self::add('info', $message, $title);
    }

    /**
     * Récupère les messages flash
     */
    public static function get(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Génère le JavaScript pour afficher les messages
     */
    public static function renderJs(): string
    {
        $messages = self::get();

        if (empty($messages)) {
            return '';
        }

        $js = '<script>document.addEventListener("DOMContentLoaded", function() {';

        foreach ($messages as $type => $data) {
            $message = addslashes($data['message']);
            $title = addslashes($data['title'] ?? '');

            if ($title) {
                $js .= sprintf(
                    'window.GestionMySoutenance.showFlashMessage("%s", "%s", "%s");',
                    $type,
                    $message,
                    $title
                );
            } else {
                $js .= sprintf(
                    'window.GestionMySoutenance.showFlashMessage("%s", "%s");',
                    $type,
                    $message
                );
            }
        }

        $js .= '});</script>';

        return $js;
    }
}

/**
 * Classe pour la pagination
 */
class Paginator
{
    private int $currentPage;
    private int $totalItems;
    private int $itemsPerPage;
    private int $totalPages;

    public function __construct(int $currentPage, int $totalItems, int $itemsPerPage = 10)
    {
        $this->currentPage = max(1, $currentPage);
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalPages = (int) ceil($totalItems / $itemsPerPage);
    }

    /**
     * Génère les liens de pagination
     */
    public function render(string $baseUrl = ''): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination">';

        // Bouton précédent
        if ($this->currentPage > 1) {
            $prevUrl = $baseUrl . '?page=' . ($this->currentPage - 1);
            $html .= sprintf(
                '<a href="%s" class="pagination-item"><span class="material-icons">chevron_left</span></a>',
                $prevUrl
            );
        } else {
            $html .= '<span class="pagination-item disabled"><span class="material-icons">chevron_left</span></span>';
        }

        // Pages
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $this->currentPage) ? ' active' : '';
            $url = $baseUrl . '?page=' . $i;

            if ($i === $this->currentPage) {
                $html .= sprintf('<span class="pagination-item%s">%d</span>', $active, $i);
            } else {
                $html .= sprintf('<a href="%s" class="pagination-item%s">%d</a>', $url, $active, $i);
            }
        }

        // Bouton suivant
        if ($this->currentPage < $this->totalPages) {
            $nextUrl = $baseUrl . '?page=' . ($this->currentPage + 1);
            $html .= sprintf(
                '<a href="%s" class="pagination-item"><span class="material-icons">chevron_right</span></a>',
                $nextUrl
            );
        } else {
            $html .= '<span class="pagination-item disabled"><span class="material-icons">chevron_right</span></span>';
        }

        // Informations
        $start = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        $end = min($this->currentPage * $this->itemsPerPage, $this->totalItems);

        $html .= sprintf(
            '<div class="pagination-info">Affichage %d-%d sur %d éléments</div>',
            $start,
            $end,
            $this->totalItems
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Récupère l'offset pour la requête SQL
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * Récupère la limite pour la requête SQL
     */
    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }
}

/**
 * Fonctions utilitaires globales
 */

/**
 * Génère un token CSRF
 */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Génère un champ CSRF caché
 */
function csrf_field(): string
{
    return sprintf('<input type="hidden" name="csrf_token" value="%s">', csrf_token());
}

/**
 * Vérifie le token CSRF
 */
function verify_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formate une date pour l'affichage
 */
function format_date(string $date, string $format = 'd/m/Y H:i'): string
{
    return date($format, strtotime($date));
}

/**
 * Génère un ID unique pour les éléments HTML
 */
function unique_id(string $prefix = 'element'): string
{
    return $prefix . '_' . uniqid();
}

/**
 * Vérifie si l'utilisateur a une permission
 */
function has_permission(string $permission): bool
{
    $userPermissions = $_SESSION['user_permissions'] ?? [];
    return in_array($permission, $userPermissions) || in_array('*', $userPermissions);
}

/**
 * Vérifie si l'utilisateur a un rôle
 */
function has_role(string $role): bool
{
    $userRole = $_SESSION['user_role'] ?? '';
    return $userRole === $role || $userRole === 'admin';
}

/**
 * Tronque un texte
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Échappe du HTML pour l'affichage sécurisé
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Génère une URL absolue
 */
function url(string $path = ''): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

    return $protocol . '://' . $host . $basePath . '/' . ltrim($path, '/');
}

/**
 * Redirige vers une URL
 */
function redirect(string $url, int $statusCode = 302): void
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Retourne la valeur d'un tableau ou une valeur par défaut
 */
function array_get(array $array, string $key, $default = null)
{
    return $array[$key] ?? $default;
}

/**
 * Génère une URL avec des paramètres de requête
 */
function url_with_params(string $base, array $params = []): string
{
    if (empty($params)) {
        return $base;
    }

    $separator = strpos($base, '?') !== false ? '&' : '?';
    return $base . $separator . http_build_query($params);
}

/**
 * Exemple d'utilisation dans un contrôleur
 */

/*
// Dans votre contrôleur
class ExampleController extends BaseController
{
    public function index(): void
    {
        // Initialiser les assets
        AssetManager::initBaseAssets();

        if (has_role('admin')) {
            AssetManager::initAdminAssets();
        }

        // Ajouter un CSS spécifique
        AssetManager::addCss('/assets/css/custom-page.css');

        // Pagination
        $page = (int) ($_GET['page'] ?? 1);
        $total = 250; // Total d'éléments depuis la base
        $paginator = new Paginator($page, $total, 20);

        // Données
        $data = [
            'pageTitle' => 'Exemple de page',
            'users' => $this->getUsersPaginated($paginator->getOffset(), $paginator->getLimit()),
            'pagination' => $paginator->render('/admin/users'),
            'css_files' => AssetManager::renderCss(),
            'js_files' => AssetManager::renderJs(),
            'flash_js' => FlashMessage::renderJs()
        ];

        // Ajouter un message de succès
        FlashMessage::success('Page chargée avec succès !');

        $this->render('Admin/Users/index', $data);
    }
}

// Dans votre vue
echo UIHelper::button('Créer un utilisateur', [
    'type' => 'primary',
    'icon' => 'add',
    'attributes' => [
        'data-modal-target' => 'user-modal'
    ]
]);

echo UIHelper::statCard([
    'label' => 'Utilisateurs actifs',
    'value' => '1,247',
    'icon' => 'people',
    'iconColor' => 'success',
    'trend' => '+5% ce mois',
    'trendType' => 'positive'
]);
*/