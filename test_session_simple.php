<?php
/**
 * Test simple des sessions PHP
 * À placer dans le dossier racine et accéder via le navigateur
 */

// Démarrer la session
session_start();

echo "<h1>Test Simple des Sessions</h1>";
echo "<p>Date: " . date('Y-m-d H:i:s') . "</p>";

// Test d'écriture en session
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;
$_SESSION['test_time'] = time();

echo "<h2>✅ Tests basiques</h2>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Status:</strong> " . session_status() . " (2 = active)</li>";
echo "<li><strong>Test Counter:</strong> " . $_SESSION['test_counter'] . "</li>";
echo "<li><strong>Test Time:</strong> " . date('H:i:s', $_SESSION['test_time']) . "</li>";
echo "</ul>";

// Test spécifique pour GestionMySoutenance
echo "<h2>🔍 Test spécifique à votre app</h2>";

// Simuler la création d'une session utilisateur comme dans votre app
if (isset($_GET['action']) && $_GET['action'] === 'create_user_session') {
    $_SESSION['user_id'] = 'TEST_USER_001';
    $_SESSION['user_data'] = [
        'numero_utilisateur' => 'TEST_USER_001',
        'nom' => 'Test',
        'prenom' => 'Utilisateur',
        'email_principal' => 'test@example.com',
        'id_groupe_utilisateur' => 'GRP_ADMIN_SYS',
        'id_type_utilisateur' => 'TYPE_ADMIN'
    ];
    $_SESSION['user_group_permissions'] = [
        'MENU_ADMINISTRATION',
        'MENU_DASHBOARDS',
        'TRAIT_ADMIN_DASHBOARD_ACCEDER',
        'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER'
    ];
    $_SESSION['last_activity'] = time();

    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Session utilisateur créée avec succès !";
    echo "</div>";
}

// Afficher l'état de la session utilisateur
if (isset($_SESSION['user_id'])) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
    echo "<h3>👤 Session utilisateur active</h3>";
    echo "<ul>";
    echo "<li><strong>User ID:</strong> " . $_SESSION['user_id'] . "</li>";
    echo "<li><strong>Nom:</strong> " . ($_SESSION['user_data']['nom'] ?? 'N/A') . "</li>";
    echo "<li><strong>Groupe:</strong> " . ($_SESSION['user_data']['id_groupe_utilisateur'] ?? 'N/A') . "</li>";
    echo "<li><strong>Permissions:</strong> " . count($_SESSION['user_group_permissions'] ?? []) . " trouvées</li>";
    echo "<li><strong>Dernière activité:</strong> " . date('H:i:s', $_SESSION['last_activity'] ?? 0) . "</li>";
    echo "</ul>";
    echo "<p><a href='?action=clear' style='background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Vider la session</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Aucune session utilisateur</h3>";
    echo "<p><a href='?action=create_user_session' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>Créer une session test</a></p>";
    echo "</div>";
}

// Action pour vider la session
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    session_destroy();
    session_start();
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "🧹 Session vidée ! <a href='?' style='color: #856404;'>Actualiser</a>";
    echo "</div>";
}

echo "<h2>📋 Session complète</h2>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>🔧 Actions</h2>";
echo "<p>";
echo "<a href='?' style='background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Actualiser</a>";
echo "<a href='?action=create_user_session' style='background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Créer session test</a>";
echo "<a href='?action=clear' style='background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px;'>Vider session</a>";
echo "</p>";

echo "<h2>ℹ️ Informations</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Session Save Path:</strong> " . session_save_path() . "</li>";
echo "<li><strong>Session Cookie Lifetime:</strong> " . ini_get('session.cookie_lifetime') . " secondes</li>";
echo "<li><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Inconnu') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>Accédez à ce script via votre navigateur à l'adresse : <strong>http://localhost:8000/test_session_simple.php</strong></em></p>";
?>