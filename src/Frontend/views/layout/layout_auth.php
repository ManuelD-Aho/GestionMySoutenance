<?php
// src/Frontend/views/layout/layout_auth.php

// Assurez-vous que les variables nécessaires sont définies, même si elles sont nulles par défaut.
// Ceci est une bonne pratique pour éviter les erreurs 'Undefined variable' si le contrôleur ne les passe pas.
$title = $title ?? 'GestionMySoutenance - Authentification';
$content = $content ?? ''; // Le contenu spécifique du formulaire sera injecté ici.
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/favicon.ico">

    <!-- Stylesheets : Tailwind CSS (via PostCSS) et DaisyUI -->
    <!-- Assurez-vous que votre pipeline PostCSS génère app.css à partir de input.css -->
    <link href="/assets/css/app.css" rel="stylesheet">

    <!-- Google Fonts pour une typographie élégante -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- GSAP pour les animations (chargement asynchrone pour ne pas bloquer le rendu) -->
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="https://unpkg.com/gsap@3.12.5/dist/ScrollTrigger.min.js" defer></script>

    <!-- Script JS spécifique à l'authentification (chargement asynchrone) -->
    <script src="/assets/js/auth.js" defer></script>

    <style>
        /* Styles personnalisés pour le fond avec défilement d'images */
        body {
            font-family: 'Poppins', sans-serif; /* Police principale */
            overflow: hidden; /* Empêche le défilement du body pour gérer le défilement interne */
        }

        #auth-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--fallback-b2, oklch(var(--b2))); /* Couleur de fond du layout */
            position: relative;
            overflow: hidden; /* Cache le débordement des images */
        }

        /* Conteneur des images défilantes (côté gauche) */
        .image-carousel-container {
            flex: 1; /* Prend la moitié de l'espace */
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--fallback-p, oklch(var(--p))); /* Couleur primaire pour le fond du carousel */
            min-width: 50%; /* Assure qu'il prend au moins la moitié */
        }

        .carousel-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Couvre l'espace sans déformer */
            opacity: 0; /* Caché par défaut, GSAP gérera l'affichage */
            transition: opacity 0.8s ease-in-out; /* Transition douce pour le fondu */
            filter: brightness(0.7); /* Assombrit légèrement les images pour le contraste */
        }

        .carousel-image.active {
            opacity: 1;
        }

        /* Overlay pour le texte sur les images */
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
            z-index: 10; /* Au-dessus des images */
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

        /* Conteneur du formulaire (côté droit) */
        .form-container {
            flex: 1; /* Prend l'autre moitié de l'espace */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            min-width: 50%; /* Assure qu'il prend au moins la moitié */
            position: relative;
            z-index: 20; /* Au-dessus du carousel si jamais il y a un chevauchement */
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) { /* Pour les écrans plus petits que large (lg) */
            #auth-container {
                flex-direction: column; /* Les éléments s'empilent */
            }
            .image-carousel-container, .form-container {
                min-width: 100%; /* Prennent toute la largeur */
                min-height: 50vh; /* Moins de hauteur pour le carousel sur mobile */
            }
            .carousel-overlay h1 {
                font-size: 2.5rem;
            }
            .carousel-overlay p {
                font-size: 1rem;
            }
            .form-container {
                padding: 1rem; /* Moins de padding sur mobile */
            }
        }
        @media (max-width: 768px) { /* Pour les écrans plus petits que medium (md) */
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
    <!-- Côté gauche : Images défilantes -->
    <div class="image-carousel-container">
        <!-- Les images seront chargées ici par JS ou directement en PHP si vous les avez en dur -->
        <!-- Exemple d'images (assurez-vous qu'elles existent dans votre dossier Public/assets/images/auth/) -->
        <img src="/assets/images/auth/auth-bg-1.jpg" alt="Campus View 1" class="carousel-image active">
        <img src="/assets/images/auth/auth-bg-2.jpg" alt="Campus View 2" class="carousel-image">
        <img src="/assets/images/auth/auth-bg-3.jpg" alt="Campus View 3" class="carousel-image">
        <img src="/assets/images/auth/auth-bg-4.jpg" alt="Campus View 4" class="carousel-image">

        <div class="carousel-overlay">
            <h1>Bienvenue sur GestionMySoutenance</h1>
            <p>Votre plateforme intégrée pour une gestion académique simplifiée et efficace. Connectez-vous pour accéder à votre parcours.</p>
        </div>
    </div>

    <!-- Côté droit : Conteneur du formulaire -->
    <div class="form-container">
        <!-- Le contenu du formulaire (login, 2fa, etc.) sera injecté ici par le BaseController -->
        <?= $content ?>
    </div>
</div>

<script>
    // JS pour le carousel d'images (peut être déplacé dans auth.js si vous préférez)
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.carousel-image');
        let currentIndex = 0;

        function showNextImage() {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        // Change d'image toutes les 5 secondes
        setInterval(showNextImage, 5000);

        // Animation d'entrée avec GSAP pour le formulaire
        gsap.from(".card", {
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: "power3.out",
            delay: 0.5
        });
        gsap.from(".carousel-overlay h1, .carousel-overlay p", {
            opacity: 0,
            y: -20,
            duration: 1,
            ease: "power3.out",
            stagger: 0.3,
            delay: 0.8
        });
    });
</script>

</body>
</html>