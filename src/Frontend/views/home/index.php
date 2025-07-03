<?php
// src/Frontend/views/home/index.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="home-container">
    <div class="welcome-section">
        <h1>Bienvenue sur GestionMySoutenance</h1>
        <p>Votre plateforme de gestion des soutenances et rapports.</p>

        <div class="auth-actions">
            <a href="/login" class="btn btn-primary">Se connecter</a>
        </div>
    </div>
</div>

<style>
    .home-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
        text-align: center;
    }

    .welcome-section h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .welcome-section p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }

    .auth-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        text-decoration: none;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #4b5563;
    }
</style>