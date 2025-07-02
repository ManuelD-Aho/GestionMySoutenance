<?php
// src/Frontend/views/home/index.php

$pageTitle = $title ?? 'Bienvenue';
?>

<div class="hero min-h-screen bg-base-200">
    <div class="hero-content text-center">
        <div class="max-w-md">
            <h1 class="text-5xl font-bold mb-4 text-primary font-montserrat animate-fade-in-down">Bienvenue sur GestionMySoutenance</h1>
            <p class="py-6 text-lg text-base-content/80 animate-fade-in-up">Votre plateforme intégrée pour la gestion des soutenances académiques.</p>
            <a href="/login" class="btn btn-primary btn-lg shadow-xl hover:shadow-2xl transition-all duration-300 ease-in-out transform hover:-translate-y-1 animate-fade-in-up delay-200">Se connecter</a>
            <p class="mt-4 text-sm text-base-content/60 animate-fade-in-up delay-400">Simplifiez votre parcours de soutenance, de la rédaction à la validation.</p>
        </div>
    </div>
</div>