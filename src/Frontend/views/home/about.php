<?php
// /src/Frontend/views/home/about.php

// Le layout `layout_auth.php` sera utilisé par le BaseController
// La variable $pageTitle est également fournie par le contrôleur.

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="text-center">
    <!-- Titre de la page -->
    <h1 class="text-4xl font-extrabold mb-6 font-montserrat">À Propos de GestionMySoutenance</h1>

    <!-- Contenu de la page -->
    <div class="prose lg:prose-lg mx-auto text-left">
        <p>
            <strong>GestionMySoutenance</strong> est une plateforme intégrée conçue pour moderniser et simplifier la gestion des processus de soutenance académique. Notre mission est de fournir un outil unique et performant pour les étudiants, les enseignants, les membres de commission et le personnel administratif.
        </p>

        <h3>Nos Objectifs</h3>
        <ul>
            <li><strong>Centraliser</strong> toutes les informations et actions relatives aux soutenances.</li>
            <li><strong>Fluidifier</strong> la communication entre tous les acteurs du processus.</li>
            <li><strong>Automatiser</strong> les tâches répétitives pour un gain de temps et d'efficacité.</li>
            <li><strong>Assurer</strong> la traçabilité, la sécurité et l'intégrité des données académiques.</li>
        </ul>

        <p>
            Développée avec les technologies les plus récentes, notre solution vise à offrir une expérience utilisateur intuitive, réactive et sécurisée.
        </p>
    </div>

    <!-- Action de retour -->
    <div class="mt-10">
        <a href="/" class="btn btn-ghost">
            <span class="material-icons mr-2">arrow_back</span>
            Retour à l'accueil
        </a>
    </div>
</div>