<?php
// src/Frontend/views/layout/layout_auth.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'GestionMySoutenance';
$asset_version = '1.0.1'; // Version pour le cache busting
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?></title>

    <!-- CSS Principal (Tailwind + DaisyUI) -->
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $asset_version ?>">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        /* Styles spécifiques pour le layout d'authentification */
        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden; /* Empêche le scroll sur la page d'auth */
        }
        .auth-container {
            display: flex;
            width: 100vw;
            height: 100vh;
        }
        .image-carousel-container {
            width: 55%;
            position: relative;
            background-color: #1A5E63; /* Couleur de fond si les images ne chargent pas */
        }
        .form-container {
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: hsl(var(--b1)); /* Utilise la couleur de base de DaisyUI */
        }
        .form-wrapper {
            width: 100%;
            max-width: 450px;
        }
        /* Styles pour le carrousel (déjà dans input.css) */
        .carousel-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            filter: brightness(0.6);
        }
        .carousel-image.active {
            opacity: 1;
        }
        .carousel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 4rem;
            color: white;
        }
        .carousel-overlay h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }
        .carousel-overlay p {
            font-size: 1.1rem;
            max-width: 80%;
            line-height: 1.6;
        }
        .carousel-indicators {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.75rem;
        }
        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .indicator.active {
            background-color: white;
            transform: scale(1.2);
        }

        @media (max-width: 1024px) {
            .image-carousel-container {
                display: none; /* Cacher le carrousel sur les écrans plus petits */
            }
            .form-container {
                width: 100%;
            }
        }
    </style>
</head>
<body class="antialiased">

<div class="auth-container">
    <!-- Colonne de gauche avec le carrousel d'images -->
    <div class="image-carousel-container">
        <img src="/assets/images/auth/soutenance1.jpg" alt="Soutenance" class="carousel-image active">
        <img src="/assets/images/auth/soutenance2.jpg" alt="Jury" class="carousel-image">
        <img src="/assets/images/auth/soutenance3.jpg" alt="Etudiants" class="carousel-image">
        <div class="carousel-overlay">
            <div id="carousel-text-content">
                <h1>Gérez vos soutenances avec excellence.</h1>
                <p>Une plateforme centralisée pour simplifier chaque étape du processus académique.</p>
            </div>
        </div>
        <div class="carousel-indicators">
            <button class="indicator active" data-slide="0"></button>
            <button class="indicator" data-slide="1"></button>
            <button class="indicator" data-slide="2"></button>
        </div>
    </div>

    <!-- Colonne de droite avec le contenu du formulaire -->
    <div class="form-container">
        <div class="form-wrapper">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <div class="text-center p-8 bg-error text-error-content rounded-box">
                    <h2 class="font-bold text-xl">Erreur de chargement</h2>
                    <p>Le contenu de cette page n'a pas pu être chargé.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- GSAP pour les animations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<!-- Script pour le carrousel et les animations -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Animation d'entrée du formulaire
        gsap.from('.form-wrapper', { duration: 0.8, opacity: 0, y: 50, ease: 'power3.out', delay: 0.2 });

        // Logique du carrousel
        const slides = document.querySelectorAll('.carousel-image');
        const indicators = document.querySelectorAll('.indicator');
        const textContent = document.getElementById('carousel-text-content');
        const slideTexts = [
            { title: "Gérez vos soutenances avec excellence.", text: "Une plateforme centralisée pour simplifier chaque étape du processus académique." },
            { title: "Collaboration fluide entre acteurs.", text: "Étudiants, enseignants et administration connectés pour un suivi optimal." },
            { title: "Atteignez vos objectifs académiques.", text: "Des outils conçus pour la réussite de chaque soutenance." }
        ];
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });

            // Animation du texte
            gsap.to(textContent, { duration: 0.5, opacity: 0, y: 20, ease: 'power2.in', onComplete: () => {
                    textContent.querySelector('h1').textContent = slideTexts[index].title;
                    textContent.querySelector('p').textContent = slideTexts[index].text;
                    gsap.to(textContent, { duration: 0.5, opacity: 1, y: 0, ease: 'power2.out' });
                }});

            currentSlide = index;
        }

        indicators.forEach(indicator => {
            indicator.addEventListener('click', () => showSlide(parseInt(indicator.dataset.slide)));
        });

        setInterval(() => {
            showSlide((currentSlide + 1) % slides.length);
        }, 7000); // Change toutes les 7 secondes
    });
</script>
</body>
</html>