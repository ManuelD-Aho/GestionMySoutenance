<?php
// /src/Frontend/views/home/index.php

// Le layout `layout_auth.php` sera utilisé par le BaseController
// La variable $pageTitle est également fournie par le contrôleur.

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="text-center">
    <!-- Logo et Titre de l'application -->
    <div class="mb-12">
        <div class="inline-block p-4 bg-primary rounded-2xl shadow-lg">
            <span class="material-icons text-primary-content" style="font-size: 48px;">school</span>
        </div>
        <h1 class="text-4xl font-extrabold mt-4 font-montserrat">GestionMySoutenance</h1>
        <p class="text-base-content/70 mt-2">Votre plateforme centralisée pour un suivi académique d'excellence.</p>
    </div>

    <!-- Message de bienvenue -->
    <div class="prose lg:prose-lg mx-auto">
        <p>
            Bienvenue ! Connectez-vous pour accéder à votre tableau de bord, suivre l'avancement de votre rapport, collaborer avec votre commission et gérer l'ensemble de votre parcours de soutenance.
        </p>
    </div>

    <!-- Actions principales -->
    <div class="mt-8">
        <a href="/login" class="btn btn-primary btn-lg w-full max-w-xs animate-pulse-slow">
            <span class="material-icons mr-2">login</span>
            Accéder à mon espace
        </a>
    </div>

    <!-- Lien secondaire -->
    <div class="mt-6 text-sm">
        <p class="text-base-content/60">
            Un problème pour vous connecter ?
            <a href="/forgot-password" class="link link-hover text-primary">Récupérez votre mot de passe</a>.
        </p>
    </div>
</div>