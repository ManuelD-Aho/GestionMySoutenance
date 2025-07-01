<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Interface d'authentification sécurisée pour GestionMySoutenance - Plateforme de gestion des soutenances académiques">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
    
    <title><?= htmlspecialchars($pageTitle ?? 'Authentification - GestionMySoutenance', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    
    <!-- CSS Framework Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.1/css/bulma.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="/assets/js/auth.js" as="script">
    <link rel="preload" href="/assets/js/auth-animations.js" as="script">
    <link rel="preload" href="/assets/js/auth-validation.js" as="script">
    
    <style>
        /* Enhanced auth layout styles with WCAG 2.1 compliance */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family-primary);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-green) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: var(--spacing-lg);
            color: var(--text-primary);
            line-height: var(--line-height-normal);
            overflow-x: hidden;
        }

        /* Accessibility improvements */
        body.reduce-motion * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }

        .auth-layout-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            animation: authFadeIn 0.6s ease-out;
        }

        .auth-layout-main {
            background: var(--bg-primary);
            border-radius: var(--border-radius-2xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: var(--border-width-thin) solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .auth-layout-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-green));
            z-index: 1;
        }

        .auth-layout-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
            padding-top: var(--spacing-md);
        }

        .auth-layout-header h1 {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-layout-header p {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }

        /* Loading state */
        .auth-layout-container.loading {
            pointer-events: none;
        }

        .auth-layout-container.loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            border-radius: var(--border-radius-2xl);
        }

        .auth-layout-footer {
            text-align: center;
            margin-top: var(--spacing-xl);
            color: rgba(255, 255, 255, 0.8);
            font-size: var(--font-size-xs);
            padding: 0 var(--spacing-lg);
        }

        /* Content error styling */
        .content-error {
            background-color: var(--bg-primary);
            border: var(--border-width-medium) solid var(--accent-red);
            color: var(--accent-red-dark);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-xl);
            text-align: center;
            animation: authShake 0.5s ease-in-out;
        }

        .content-error h2 {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            color: var(--accent-red);
        }

        .content-error p {
            margin-bottom: var(--spacing-md);
            line-height: var(--line-height-relaxed);
        }

        .content-error a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            transition: color var(--transition-fast);
        }

        .content-error a:hover {
            color: var(--primary-blue-dark);
            text-decoration: underline;
        }

        /* Animations */
        @keyframes authFadeIn {
            from { 
                opacity: 0; 
                transform: translateY(-20px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        @keyframes authShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: var(--spacing-md);
            }

            .auth-layout-container {
                max-width: 100%;
            }

            .auth-layout-main {
                padding: var(--spacing-xl);
            }

            .auth-layout-header h1 {
                font-size: var(--font-size-2xl);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: var(--spacing-sm);
            }

            .auth-layout-main {
                padding: var(--spacing-lg);
            }

            .auth-layout-header h1 {
                font-size: var(--font-size-xl);
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .auth-layout-main {
                border-width: var(--border-width-thick);
            }
            
            .auth-layout-header h1 {
                -webkit-text-fill-color: var(--primary-blue);
                background: none;
            }
        }

        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            html {
                scroll-behavior: auto;
            }
        }

        /* Focus styles for accessibility */
        *:focus-visible {
            outline: var(--border-width-medium) solid var(--primary-blue);
            outline-offset: 2px;
            border-radius: var(--border-radius-sm);
        }

        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-blue);
            color: var(--text-white);
            padding: var(--spacing-sm) var(--spacing-md);
            text-decoration: none;
            border-radius: var(--border-radius-md);
            z-index: 1000;
            font-weight: var(--font-weight-semibold);
        }

        .skip-link:focus {
            top: 6px;
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
<body>
    <!-- Skip link for accessibility -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <div class="auth-layout-container" id="auth-container">
        <main class="auth-layout-main" id="main-content" role="main" aria-live="polite">
            <!-- Header with logo -->
            <header class="auth-layout-header">
                <h1>GestionMySoutenance</h1>
                <p>Plateforme de gestion des soutenances</p>
            </header>

            <!-- Main content area -->
            <div class="auth-content-area">
                <?php if (isset($content)): ?>
                    <?= $content ?>
                <?php else: ?>
                    <div class="content-error" role="alert" aria-labelledby="error-title">
                        <h2 id="error-title">Erreur d'affichage</h2>
                        <p>Le contenu de la page n'a pas pu être chargé. Veuillez réessayer.</p>
                        <p><a href="/" aria-label="Retourner à la page d'accueil">Retour à la page d'accueil</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="auth-layout-footer" role="contentinfo">
            <p>
                &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
            </p>
        </footer>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/auth-validation.js" defer></script>
    <script src="/assets/js/auth-animations.js" defer></script>
    <script src="/assets/js/auth.js" defer></script>

    <!-- Initialize reduced motion detection -->
    <script>
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduce-motion');
        }
    </script>
</body>
</html>