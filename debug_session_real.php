<?php
/**
 * Script de débogage des sessions - GestionMySoutenance
 * À placer dans le dossier racine de votre projet
 */

// Démarrer la session
session_start();

// Fonction pour afficher les données de session
function debugSession() {
    echo "<h1>Debug Session GestionMySoutenance - " . date('Y-m-d H:i:s') . "</h1>";

    echo "<h2>🔧 Configuration de session</h2>";
    echo "<ul>";
    echo "<li>Session ID: " . session_id() . "</li>";
    echo "<li>Session Name: " . session_name() . "</li>";
    echo "<li>Session Status: " . session_status() . " (1=disabled, 2=active, 3=none)</li>";
    echo "<li>Session Save Path: " . session_save_path() . "</li>";
    echo "<li>Session Cookie Lifetime: " . ini_get('session.cookie_lifetime') . " secondes</li>";
    echo "<li>Session GC Maxlifetime: " . ini_get('session.gc_maxlifetime') . " secondes</li>";
    echo "</ul>";

    echo "<h2>📋 Données de session complètes</h2>";
    if (empty($_SESSION)) {
        echo "<p style='color: red;'>❌ Aucune donnée de session trouvée</p>";
    } else {
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
        print_r($_SESSION);
        echo "</pre>";
    }

    echo "<h2>✅ Vérifications spécifiques GestionMySoutenance</h2>";
    echo "<ul>";

    // Vérifier user_id
    if (isset($_SESSION['user_id'])) {
        echo "<li>✅ user_id présent: " . $_SESSION['user_id'] . "</li>";
    } else {
        echo "<li>❌ user_id absent</li>";
    }

    // Vérifier user_data
    if (isset($_SESSION['user_data'])) {
        $userData = $_SESSION['user_data'];
        echo "<li>✅ user_data présent</li>";
        echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;👤 Nom: " . ($userData['nom'] ?? 'N/A') . "</li>";
        echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;📧 Email: " . ($userData['email_principal'] ?? 'N/A') . "</li>";
        echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;🏷️ Groupe: " . ($userData['id_groupe_utilisateur'] ?? 'N/A') . "</li>";
        echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;🎭 Type: " . ($userData['id_type_utilisateur'] ?? 'N/A') . "</li>";
    } else {
        echo "<li>❌ user_data absent</li>";
    }

    // Vérifier user_group_permissions (la bonne variable)
    if (isset($_SESSION['user_group_permissions'])) {
        $permissions = $_SESSION['user_group_permissions'];
        echo "<li>✅ user_group_permissions présent (" . count($permissions) . " permissions)</li>";
        echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;🔐 Permissions: " . implode(', ', $permissions) . "</li>";
    } else {
        echo "<li>❌ user_group_permissions absent</li>";
    }

    // Vérifier user_delegations
    if (isset($_SESSION['user_delegations'])) {
        echo "<li>✅ user_delegations présent (" . count($_SESSION['user_delegations']) . " délégations)</li>";
    } else {
        echo "<li>❌ user_delegations absent</li>";
    }

    // Vérifier last_activity
    if (isset($_SESSION['last_activity'])) {
        echo "<li>✅ last_activity présent: " . date('Y-m-d H:i:s', $_SESSION['last_activity']) . "</li>";
    } else {
        echo "<li>❌ last_activity absent</li>";
    }

    echo "</ul>";

    echo "<h2>🚨 Diagnostic des problèmes</h2>";
    echo "<ul>";

    // Diagnostic automatique
    if (!isset($_SESSION['user_id'])) {
        echo "<li style='color: red;'>🔴 PROBLÈME: Aucun utilisateur connecté</li>";
    } elseif (!isset($_SESSION['user_data'])) {
        echo "<li style='color: orange;'>🟡 PROBLÈME: Session incomplète - user_data manquant</li>";
    } elseif (!isset($_SESSION['user_group_permissions'])) {
        echo "<li style='color: orange;'>🟡 PROBLÈME: Permissions manquantes - le menu ne s'affichera pas</li>";
    } else {
        echo "<li style='color: green;'>🟢 Session semble correcte</li>";
    }

    // Vérifier les variables incorrectes
    if (isset($_SESSION['user_permissions'])) {
        echo "<li style='color: red;'>🔴 ATTENTION: Variable 'user_permissions' trouvée - devrait être 'user_group_permissions'</li>";
    }

    echo "</ul>";

    echo "<h2>🔧 Actions de test</h2>";
    echo "<p>";
    echo "<a href='?action=simulate_admin' style='background: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Simuler Admin</a>";
    echo "<a href='?action=simulate_etudiant' style='background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Simuler Étudiant</a>";
    echo "<a href='?action=clear_session' style='background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Vider Session</a>";
    echo "<a href='?action=refresh' style='background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px;'>Actualiser</a>";
    echo "</p>";
}

// Traiter les actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'simulate_admin':
            $_SESSION['user_id'] = 'ADM001';
            $_SESSION['user_data'] = [
                'numero_utilisateur' => 'ADM001',
                'nom' => 'Admin',
                'prenom' => 'Test',
                'email_principal' => 'admin@test.com',
                'id_groupe_utilisateur' => 'GRP_ADMIN_SYS',
                'id_type_utilisateur' => 'TYPE_ADMIN',
                'statut_compte' => 'actif',
                'email_valide' => 1
            ];
            $_SESSION['user_group_permissions'] = [
                'MENU_ADMINISTRATION',
                'MENU_DASHBOARDS',
                'TRAIT_ADMIN_DASHBOARD_ACCEDER',
                'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER',
                'TRAIT_ADMIN_CONFIG_ACCEDER',
                'TRAIT_ADMIN_SUPERVISION_ACCEDER',
                'TRAIT_ADMIN_REPORTING_ACCEDER'
            ];
            $_SESSION['user_delegations'] = [];
            $_SESSION['last_activity'] = time();
            echo "<script>alert('Session admin simulée créée !'); window.location.href='?';</script>";
            break;

        case 'simulate_etudiant':
            $_SESSION['user_id'] = 'ETU001';
            $_SESSION['user_data'] = [
                'numero_utilisateur' => 'ETU001',
                'nom' => 'Étudiant',
                'prenom' => 'Test',
                'email_principal' => 'etudiant@test.com',
                'id_groupe_utilisateur' => 'GRP_ETUDIANT',
                'id_type_utilisateur' => 'TYPE_ETUD',
                'statut_compte' => 'actif',
                'email_valide' => 1
            ];
            $_SESSION['user_group_permissions'] = [
                'MENU_ETUDIANT',
                'MENU_DASHBOARDS',
                'TRAIT_ETUDIANT_DASHBOARD_ACCEDER',
                'TRAIT_ETUDIANT_PROFIL_GERER',
                'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE',
                'TRAIT_ETUDIANT_RAPPORT_SUIVRE'
            ];
            $_SESSION['user_delegations'] = [];
            $_SESSION['last_activity'] = time();
            echo "<script>alert('Session étudiant simulée créée !'); window.location.href='?';</script>";
            break;

        case 'clear_session':
            session_destroy();
            session_start();
            echo "<script>alert('Session vidée !'); window.location.href='?';</script>";
            break;

        case 'refresh':
            header('Location: ?');
            exit;
    }
}

// Afficher le debug
debugSession();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Session - GestionMySoutenance</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #343a40;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        pre {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }
        ul {
            line-height: 1.8;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📖 Instructions d'utilisation</h2>
    <ol>
        <li><strong>Placez ce fichier</strong> dans le dossier racine de votre projet</li>
        <li><strong>Accédez-y</strong> via votre navigateur : <code>http://votre-domaine/debug_session_real.php</code></li>
        <li><strong>Testez</strong> d'abord une simulation (Admin ou Étudiant) pour voir si les sessions fonctionnent</li>
        <li><strong>Connectez-vous</strong> normalement sur votre application</li>
        <li><strong>Revenez ici</strong> pour voir si les données de session sont correctement stockées</li>
    </ol>

    <h2>🔍 Diagnostic des problèmes courants</h2>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
        <tr style="background: #f8f9fa;">
            <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Symptôme</th>
            <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Cause probable</th>
            <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Solution</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Session ID change à chaque rechargement</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Problème de configuration PHP</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Vérifier les permissions du dossier session</td>
        </tr>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">user_id présent mais user_data absent</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Problème dans ServiceSecurite::demarrerSessionUtilisateur()</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Vérifier la méthode de création de session</td>
        </tr>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">user_group_permissions vide</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Problème dans la récupération des permissions</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Vérifier la table 'rattacher'</td>
        </tr>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Menu ne s'affiche pas</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">DashboardController utilise 'user_permissions' au lieu de 'user_group_permissions'</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Corriger le nom de la variable dans le contrôleur</td>
        </tr>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Redirection en boucle après connexion</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Session non créée correctement</td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">Vérifier ServiceSecurite::tenterConnexion()</td>
        </tr>
        </tbody>
    </table>

    <h2>🎯 Checklist de vérification</h2>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3>✅ Session fonctionnelle</h3>
        <ul style="list-style: none; background: transparent; box-shadow: none;">
            <li>☐ Session ID stable (ne change pas au rechargement)</li>
            <li>☐ user_id présent et correct</li>
            <li>☐ user_data complet avec toutes les informations utilisateur</li>
            <li>☐ user_group_permissions présent avec la liste des permissions</li>
            <li>☐ last_activity mis à jour</li>
        </ul>

        <h3>🔐 Authentification</h3>
        <ul style="list-style: none; background: transparent; box-shadow: none;">
            <li>☐ Redirection vers /dashboard après connexion réussie</li>
            <li>☐ Pas de redirection vers /login après connexion</li>
            <li>☐ Message "Connexion réussie !" affiché</li>
        </ul>

        <h3>📋 Menu dynamique</h3>
        <ul style="list-style: none; background: transparent; box-shadow: none;">
            <li>☐ Menu s'affiche avec les éléments appropriés au rôle</li>
            <li>☐ Permissions correctement vérifiées</li>
            <li>☐ Liens du menu fonctionnels</li>
        </ul>
    </div>
</div>
</body>
</html>