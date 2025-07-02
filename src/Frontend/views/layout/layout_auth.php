<?php
// src/Frontend/views/layout/layout_auth.php

$title = $title ?? 'GestionMySoutenance - Authentification';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/favicon.ico">

    <!-- Stylesheets : Tailwind CSS (via PostCSS) et DaisyUI -->
    <link href="/assets/css/app.css" rel="stylesheet">

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Google Fonts pour une typographie élégante -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- GSAP pour les animations (chargement asynchrone pour ne pas bloquer le rendu) -->
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="https://unpkg.com/gsap@3.12.5/dist/ScrollTrigger.min.js" defer></script>

    <!-- Script JS spécifique à l'authentification (chargement asynchrone) -->
    <script src="/assets/js/auth.js" defer></script>

    <style>
        /* Ces styles sont maintenant gérés par Tailwind ou déplacés dans input.css */
        /* Ils sont laissés ici pour référence si vous aviez des styles inline critiques */
        body {

            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }
        #auth-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--color-background-secondary); /* Utilisation directe de la variable CSS */
            position: relative;
            overflow: hidden;
        }
        .image-carousel-container {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-primary); /* Utilisation directe de la variable CSS */
            min-width: 50%;
        }
        .carousel-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            filter: brightness(0.7);
        }
        .carousel-image.active {
            opacity: 1;
        }
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 2rem;
            z-index: 10;
        }
        .carousel-overlay h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .carousel-overlay p {
            font-size: 1.2rem;
            font-weight: 300;
            max-width: 80%;
            line-height: 1.6;
        }
        .form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            min-width: 50%;
            position: relative;
            z-index: 20;
        }
        @media (max-width: 1024px) {
            #auth-container {
                flex-direction: column;
            }
            .image-carousel-container, .form-container {
                min-width: 100%;
                min-height: 50vh;
            }
            .carousel-overlay h1 {
                font-size: 2.5rem;
            }
            .carousel-overlay p {
                font-size: 1rem;
            }
            .form-container {
                padding: 1rem;
            }
        }
        @media (max-width: 768px) {
            .carousel-overlay h1 {
                font-size: 2rem;
            }
            .carousel-overlay p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div id="auth-container">
    <div class="image-carousel-container">
        <img src="/assets/images/auth/auth-bg-1.jpg" alt="Campus View 1" class="carousel-image active">
        <img src="/assets/images/auth/auth-bg-2.jpg" alt="Campus View 2" class="carousel-image">
        <img src="/assets/images/auth/auth-bg-3.jpg" alt="Campus View 3" class="carousel-image">
        <img src="/assets/images/auth/auth-bg-4.jpg" alt="Campus View 4" class="carousel-image">

        <div class="carousel-overlay">
            <h1>Bienvenue sur GestionMySoutenance</h1>
            <p>Votre plateforme intégrée pour une gestion académique simplifiée et efficace. Connectez-vous pour accéder à votre parcours.</p>
        </div>
    </div>

    <div class="form-container">
        <?= $content ?>
    </div>
</div>

</body>
</html>