<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestionMySoutenance</title>
    <!-- Incluez Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Optionnel: Lien vers vos fichiers CSS globaux existants -->
    <link rel="stylesheet" href="/Public/assets/css/style.css">
    <link rel="stylesheet" href="/Public/assets/css/dashboard_style.css">
    <link rel="stylesheet" href="/Public/assets/css/gestionsoutenance-dashboard.css">
    <!-- Remplacez les styles de menu et d'en-tête que nous avons mis en ligne par ces liens si vous les externalisez -->
    <!-- <link rel="stylesheet" href="/Public/assets/css/dynamic_menu.css"> -->
    <!-- <link rel="stylesheet" href="/Public/assets/css/dashboard_header.css"> -->
    <style>
        /* Styles pour le corps et la structure principale du layout */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f2f5f9;
            display: flex;
            min-height: 100vh;
            overflow: hidden; /* Empêche le défilement global non désiré */
        }

        .sidebar {
            width: 250px; /* Largeur fixe pour la barre latérale */
            flex-shrink: 0; /* Empêche la barre latérale de rétrécir */
            background-color: #2c3e50; /* Couleur de fond sombre pour le menu */
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Ombre à droite de la sidebar */
            z-index: 1000; /* Assure que la sidebar est au-dessus d'autres éléments */
            transition: transform 0.3s ease-in-out; /* Animation pour l'ouverture/fermeture sur mobile */
            border-radius: 0 8px 8px 0; /* Coins arrondis côté droit */
        }

        /* DEBUG: Ajout de bordures pour voir le conteneur du sidebar */
        .sidebar {
            border: 2px solid red !important; /* Temporaire pour le débogage */
        }
        .main-content-wrapper {
            border: 2px solid blue !important; /* Temporaire pour le débogage */
        }


        .sidebar.collapsed {
            transform: translateX(-100%); /* Cache la sidebar sur mobile */
            position: absolute; /* Permet de la cacher hors de l'écran */
        }

        .sidebar-header {
            padding: 0 20px 20px 20px;
            display: flex;
            align-items: center;
            color: #ecf0f1;
            font-size: 1.4em;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header .logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            margin-right: 10px;
            background-color: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2em;
        }

        .main-content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding: 0 15px 15px 15px;
            padding-left: 0;
            box-sizing: border-box;
        }

        .main-content-area {
            flex-grow: 1;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                position: fixed;
                height: 100vh;
                top: 0;
                left: 0;
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content-wrapper {
                padding-left: 0;
                width: 100%;
            }
            .dashboard-header .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">GS</div>
        GestionMySoutenance
    </div>
    <?php
    // Inclure votre menu dynamique ici
    // CORRECTION DU CHEMIN: Remonter d'un dossier (..) pour trouver 'common/menu.php'
    require_once __DIR__ . '/../common/menu.php';
    ?>

</div>

<div class="main-content-wrapper">
    <?php
    // Inclure l'en-tête du tableau de bord
    require_once __DIR__ . '/../common/header.php';
    ?>
    <div class="main-content-area">
        <?php
        if (isset($content) && !empty($content)) {
            echo $content;
        } else {
            echo '<h1>Bienvenue dans votre tableau de bord !</h1>';
            echo '<p>Sélectionnez une option dans le menu latéral.</p>';
        }
        ?>
    </div>
</div>

<script>
    // Script pour gérer le toggle de la sidebar sur mobile
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggleButton = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');

        if (menuToggleButton && sidebar) {
            menuToggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Fermer la sidebar si on clique en dehors quand elle est ouverte (sur mobile)
            document.addEventListener('click', function(event) {
                if (!sidebar.contains(event.target) && !menuToggleButton.contains(event.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            });
        }
    });
</script>
</body>
</html>
