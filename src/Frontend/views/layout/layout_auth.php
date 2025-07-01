<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'GestionMySoutenance - Authentification') ?></title>

    <!-- CSS Compilé via PostCSS (incluant Tailwind, DaisyUI, Bulma) -->
    <link href="/assets/css/app.css" rel="stylesheet">

    <!-- GSAP pour les animations -->
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
</head>
<body class="bg-base-200">

<div id="auth-container" class="min-h-screen flex items-center justify-center p-4">
    <!-- Le contenu du formulaire (login, 2fa, etc.) sera injecté ici par le BaseController -->
    <?= $content ?? '' ?>
</div>

<!-- Script JS spécifique à l'authentification -->
<script src="/assets/js/auth.js" defer></script>

</body>
</html>