<!DOCTYPE html>
<html lang="fr" data-theme="academictheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'GestionMySoutenance', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Modern CSS Frameworks -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/root.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="/node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .auth-layout-container {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Styles spécifiques pour les alertes */
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            animation: slideIn 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 6px;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid #2ecc71;
            color: #166534;
        }

        .alert-error, .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid #e74c3c;
            color: #c0392b;
        }

        .alert-warning {
            background-color: rgba(243, 156, 18, 0.1);
            border-left: 4px solid #f39c12;
            color: #b45309;
        }

        .alert-info {
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 4px solid #3498db;
            color: #1d4ed8;
        }

        /* Styles pour le footer du layout */
        .auth-layout-footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            font-size: 14px;
            padding: 0 20px;
        }

        /* Message d'erreur si le contenu est manquant */
        .content-error {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 15px;
            }

            .auth-layout-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="auth-layout-main">
<div class="auth-layout-container">
    <main>
        <?php if (isset($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <div class="content-error">
                <h2>Erreur d'affichage</h2>
                <p>Le contenu de la page n'a pas pu être chargé. Veuillez réessayer.</p>
                <p><a href="/">Retour à la page d'accueil</a></p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="auth-footer">
        <p>&copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.</p>
    </footer>
</div>

<!-- GSAP Animation Library -->
<script src="/node_modules/gsap/dist/gsap.min.js"></script>

<!-- Auth JavaScript -->
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/auth-validation.js"></script>
<script src="/assets/js/auth-animations.js"></script>

<script>
// Initialize GSAP animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate container entrance
    gsap.from('.auth-layout-container', {
        duration: 0.8,
        y: 50,
        opacity: 0,
        ease: "power3.out"
    });
    
    // Initialize auth animations if available
    if (typeof AuthAnimations !== 'undefined') {
        AuthAnimations.init();
    }
});
</script>

</body>
</html>