<?php
// /src/Frontend/views/Auth/auth.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Variables passées par le contrôleur
$form = $form ?? 'login';
$csrf_token = $csrf_token ?? '';
$token = $token ?? ''; // Pour le reset de mot de passe
$flash_messages = $flash_messages ?? [];
?>

<div class="w-full">
    <!-- Logo -->
    <div class="text-center mb-8">
        <a href="/" class="inline-block">
            <div class="inline-block p-3 bg-primary rounded-xl shadow-md">
                <span class="material-icons text-primary-content" style="font-size: 36px;">school</span>
            </div>
        </a>
    </div>

    <!-- Messages Flash spécifiques à l'authentification -->
    <?php require_once __DIR__ . '/../layout/_flash_messages.php'; ?>

    <!-- =================== FORMULAIRE DE CONNEXION =================== -->
    <?php if ($form === 'login'): ?>
        <div id="login-form" class="animate-fade-in">
            <h1 class="text-2xl font-bold text-center mb-2">Bon retour parmi nous !</h1>
            <p class="text-center text-base-content/60 mb-6">Connectez-vous pour accéder à votre espace.</p>

            <form action="/login" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="form-control w-full mb-4">
                    <label class="label" for="identifiant"><span class="label-text">Identifiant ou Email</span></label>
                    <input type="text" id="identifiant" name="identifiant" placeholder="Votre identifiant" class="input input-bordered w-full" required autofocus />
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label" for="mot_de_passe"><span class="label-text">Mot de passe</span></label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" class="input input-bordered w-full" required />
                </div>

                <div class="flex justify-between items-center mb-6 text-sm">
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="remember_me" class="checkbox checkbox-sm" />
                        <span class="label-text">Se souvenir de moi</span>
                    </label>
                    <a href="/forgot-password" class="link link-hover text-primary">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn btn-primary w-full">Se connecter</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- =================== FORMULAIRE 2FA =================== -->
    <?php if ($form === '2fa'): ?>
        <div id="2fa-form" class="animate-fade-in">
            <h1 class="text-2xl font-bold text-center mb-2">Vérification Requise</h1>
            <p class="text-center text-base-content/60 mb-6">Veuillez entrer le code de votre application d'authentification.</p>

            <form action="/2fa" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="form-control w-full mb-4">
                    <label class="label" for="code_totp"><span class="label-text">Code de vérification</span></label>
                    <input type="text" id="code_totp" name="code_totp" placeholder="123456" class="input input-bordered w-full text-center tracking-[0.5em]" required maxlength="6" inputmode="numeric" pattern="[0-9]*" />
                </div>

                <button type="submit" class="btn btn-primary w-full">Vérifier</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- =================== FORMULAIRE MOT DE PASSE OUBLIÉ =================== -->
    <?php if ($form === 'forgot_password'): ?>
        <div id="forgot-password-form" class="animate-fade-in">
            <h1 class="text-2xl font-bold text-center mb-2">Mot de passe oublié ?</h1>
            <p class="text-center text-base-content/60 mb-6">Pas de panique. Entrez votre email et nous vous enverrons un lien pour le réinitialiser.</p>

            <form action="/forgot-password" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="form-control w-full mb-4">
                    <label class="label" for="email"><span class="label-text">Adresse Email</span></label>
                    <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" class="input input-bordered w-full" required />
                </div>

                <button type="submit" class="btn btn-primary w-full">Envoyer le lien</button>
            </form>
            <div class="text-center mt-4">
                <a href="/login" class="link link-hover text-sm"><span class="material-icons text-sm">arrow_back</span> Retour à la connexion</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- =================== FORMULAIRE RÉINITIALISER MOT DE PASSE =================== -->
    <?php if ($form === 'reset_password'): ?>
        <div id="reset-password-form" class="animate-fade-in">
            <h1 class="text-2xl font-bold text-center mb-2">Réinitialiser votre mot de passe</h1>
            <p class="text-center text-base-content/60 mb-6">Choisissez un nouveau mot de passe sécurisé.</p>

            <form action="/reset-password/<?= e($token) ?>" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="form-control w-full mb-4">
                    <label class="label" for="nouveau_mot_de_passe"><span class="label-text">Nouveau mot de passe</span></label>
                    <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="input input-bordered w-full" required />
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label" for="confirmation_mot_de_passe"><span class="label-text">Confirmer le mot de passe</span></label>
                    <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" class="input input-bordered w-full" required />
                </div>

                <button type="submit" class="btn btn-primary w-full">Réinitialiser le mot de passe</button>
            </form>
        </div>
    <?php endif; ?>
</div>
