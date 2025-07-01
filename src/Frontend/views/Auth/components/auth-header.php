<?php
/**
 * Composant En-tête pour les pages d'authentification
 * Utilisé dans toutes les pages auth pour une interface cohérente
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'GestionMySoutenance';
$pageSubtitle = $pageSubtitle ?? 'Plateforme de gestion des soutenances académiques';
$showLogo = $showLogo ?? true;
?>

<div class="auth-header">
    <?php if ($showLogo): ?>
    <div class="auth-logo">
        <div class="logo-container">
            <img src="/assets/img/ufhb.jpeg" alt="Logo UFHB" class="logo-img" loading="lazy">
            <div class="logo-text">
                <h1 class="logo-title"><?= e($pageTitle) ?></h1>
                <p class="logo-subtitle"><?= e($pageSubtitle) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="auth-breadcrumb">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/" class="breadcrumb-link">
                        <span class="material-icons" aria-hidden="true">home</span>
                        <span>Accueil</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span><?= e($pageTitle) ?></span>
                </li>
            </ol>
        </nav>
    </div>
</div>

<style>
/* Styles pour le composant auth-header */
.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    position: relative;
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    flex-wrap: wrap;
}

.logo-img {
    width: 60px;
    height: 60px;
    border-radius: var(--border-radius-full);
    border: 2px solid var(--primary-blue);
    object-fit: cover;
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-fast);
}

.logo-img:hover {
    transform: scale(1.05);
}

.logo-text {
    text-align: left;
}

.logo-title {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--primary-blue);
    margin: 0;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.logo-subtitle {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: var(--spacing-xs) 0 0 0;
    font-weight: var(--font-weight-medium);
}

.auth-breadcrumb {
    margin-top: var(--spacing-md);
}

.breadcrumb {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: var(--font-size-sm);
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item:not(:last-child)::after {
    content: '›';
    margin: 0 var(--spacing-sm);
    color: var(--text-light);
    font-weight: var(--font-weight-bold);
}

.breadcrumb-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: var(--primary-blue);
    text-decoration: none;
    transition: all var(--transition-fast);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-sm);
}

.breadcrumb-link:hover {
    background-color: var(--primary-blue);
    color: var(--text-white);
    transform: translateY(-1px);
}

.breadcrumb-link .material-icons {
    font-size: 16px;
}

.breadcrumb-item.active span {
    color: var(--text-secondary);
    font-weight: var(--font-weight-medium);
}

/* Responsive */
@media (max-width: 576px) {
    .logo-container {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .logo-text {
        text-align: center;
    }
    
    .logo-title {
        font-size: var(--font-size-xl);
    }
    
    .breadcrumb {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .breadcrumb-link span:not(.material-icons) {
        display: none;
    }
}

@media (max-width: 320px) {
    .logo-img {
        width: 50px;
        height: 50px;
    }
    
    .logo-title {
        font-size: var(--font-size-lg);
    }
}
</style>