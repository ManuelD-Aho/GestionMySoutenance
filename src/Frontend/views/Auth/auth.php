<?php
// src/Frontend/views/Auth/auth.php

// Assurer que la variable $form est définie pour éviter les erreurs
$form = $form ?? 'login';
// Assurer que $csrf_token est défini
$csrf_token = $csrf_token ?? '';
// Assurer que $flash_messages est défini (vient de BaseController->render)
$flash_messages = $flash_messages ?? [];
// Assurer que $token est défini pour le formulaire de réinitialisation
$token = $token ?? '';
?>
<div class="card w-full max-w-md bg-base-100 shadow-2xl rounded-xl p-6 lg:p-8 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
    <div class="card-body p-0">
        <div class="text-center mb-6">
            <h1 class="text-4xl font-extrabold text-primary mb-2 font-montserrat">GestionMySoutenance</h1>
            <p class="text-base-content/80 text-lg">
                <?php
                echo [
                    'login' => 'Connectez-vous à votre espace sécurisé',
                    '2fa' => 'Vérification à deux facteurs requise',
                    'forgot_password' => 'Récupération de votre mot de passe',
                    'reset_password' => 'Définissez un nouveau mot de passe'
                ][$form] ?? 'Authentification';
                ?>
            </p>
        </div>

        <!-- Zone pour les messages flash (erreurs, succès) -->
        <?php if (!empty($flash_messages)): ?>
            <div id="global-alerts" class="space-y-3 mb-6">
                <?php foreach ($flash_messages as $msg): ?>
                    <div role="alert" class="alert alert-<?= htmlspecialchars($msg['type']) ?> shadow-md rounded-lg animate-fade-in">
                        <i class="fas fa-<?= $msg['type'] === 'error' ? 'times-circle' : ($msg['type'] === 'success' ? 'check-circle' : ($msg['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?> text-xl"></i>
                        <span class="font-medium"><?= htmlspecialchars($msg['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Zone pour les retours dynamiques du JS (ex: validation côté client) -->
        <div id="form-feedback" class="hidden mt-4"></div>

        <?php // =================== FORMULAIRE DE CONNEXION =================== ?>
        <?php if ($form === 'login'): ?>
            <form id="login-form" action="/login" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-control">
                    <label class="label" for="identifiant"><span class="label-text text-base font-medium">Identifiant ou Email</span></label>
                    <input type="text" id="identifiant" name="identifiant" placeholder="Votre identifiant ou email" class="input input-bordered input-primary w-full text-base focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required autocomplete="username">
                </div>
                <div class="form-control">
                    <label class="label" for="mot_de_passe"><span class="label-text text-base font-medium">Mot de passe</span></label>
                    <div class="relative w-full">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe" class="input input-bordered input-primary w-full pr-12 text-base focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required autocomplete="current-password">
                        <button type="button" data-toggle-password="mot_de_passe" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Afficher/Masquer le mot de passe">
                            <i class="fas fa-eye-slash password-toggle-icon"></i>
                        </button>
                    </div>
                    <label class="label"><a href="/forgot-password" class="label-text-alt link link-hover text-sm text-primary hover:text-primary-focus transition-colors duration-200">Mot de passe oublié ?</a></label>
                </div>
                <div class="form-control mt-7">
                    <button type="submit" class="btn btn-primary w-full text-lg font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-0.5">
                        <span class="loading loading-spinner loading-sm hidden"></span><span class="button-text">Connexion</span>
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE 2FA =================== ?>
        <?php elseif ($form === '2fa'): ?>
            <form id="2fa-form" action="/2fa" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <p class="text-center text-base text-base-content/80">Saisissez le code à 6 chiffres de votre application d'authentification.</p>
                <div class="form-control">
                    <label class="label" for="code_totp"><span class="label-text text-base font-medium">Code de vérification</span></label>
                    <input type="text" id="code_totp" name="code_totp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="XXXXXX" class="input input-bordered input-primary w-full text-center text-2xl tracking-[0.5em] font-mono focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required>
                </div>
                <div class="form-control mt-7">
                    <button type="submit" class="btn btn-primary w-full text-lg font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-0.5">
                        <span class="loading loading-spinner loading-sm hidden"></span><span class="button-text">Vérifier</span>
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE MOT DE PASSE OUBLIÉ =================== ?>
        <?php elseif ($form === 'forgot_password'): ?>
            <form id="forgot-password-form" action="/forgot-password" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <p class="text-center text-base text-base-content/80">Saisissez votre adresse email pour recevoir un lien de réinitialisation.</p>
                <div class="form-control">
                    <label class="label" for="email"><span class="label-text text-base font-medium">Adresse Email</span></label>
                    <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" class="input input-bordered input-primary w-full text-base focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required autocomplete="email">
                </div>
                <div class="form-control mt-7">
                    <button type="submit" class="btn btn-primary w-full text-lg font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-0.5">
                        <span class="loading loading-spinner loading-sm hidden"></span><span class="button-text">Envoyer le lien</span>
                    </button>
                </div>
                <div class="text-center mt-5"><a href="/login" class="link link-hover text-primary hover:text-primary-focus transition-colors duration-200">Retour à la connexion</a></div>
            </form>

            <?php // =================== FORMULAIRE DE RÉINITIALISATION DE MOT DE PASSE =================== ?>
        <?php elseif ($form === 'reset_password'): ?>
            <form id="reset-password-form" action="/reset-password" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-control">
                    <label class="label" for="nouveau_mot_de_passe"><span class="label-text text-base font-medium">Nouveau mot de passe</span></label>
                    <div class="relative w-full">
                        <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" placeholder="Minimum 8 caractères, maj/min/chiffre" class="input input-bordered input-primary w-full pr-12 text-base focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required autocomplete="new-password">
                        <button type="button" data-toggle-password="nouveau_mot_de_passe" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Afficher/Masquer le mot de passe">
                            <i class="fas fa-eye-slash password-toggle-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label" for="confirmation_mot_de_passe"><span class="label-text text-base font-medium">Confirmer le mot de passe</span></label>
                    <div class="relative w-full">
                        <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" placeholder="Confirmez votre nouveau mot de passe" class="input input-bordered input-primary w-full pr-12 text-base focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200" required autocomplete="new-password">
                        <button type="button" data-toggle-password="confirmation_mot_de_passe" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Afficher/Masquer le mot de passe">
                            <i class="fas fa-eye-slash password-toggle-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="form-control mt-7">
                    <button type="submit" class="btn btn-primary w-full text-lg font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-0.5">
                        <span class="loading loading-spinner loading-sm hidden"></span><span class="button-text">Réinitialiser</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Script pour afficher/masquer le mot de passe
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.togglePassword;
            const targetInput = document.getElementById(targetId);
            const icon = button.querySelector('.password-toggle-icon');

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

    // Script pour afficher le spinner sur les boutons de soumission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                const spinner = submitButton.querySelector('.loading-spinner');
                const buttonText = submitButton.querySelector('.button-text');
                if (spinner) spinner.classList.remove('hidden');
                if (buttonText) buttonText.classList.add('hidden');
                submitButton.setAttribute('disabled', 'disabled'); // Désactive le bouton pour éviter les soumissions multiples
            }
        });
    });

    // Animation des messages flash
    document.addEventListener('DOMContentLoaded', () => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            gsap.from(alert, {
                opacity: 0,
                y: -20,
                duration: 0.5,
                ease: "power2.out",
                onComplete: () => {
                    // Optionnel: faire disparaître les messages après un certain temps
                    gsap.to(alert, {
                        opacity: 0,
                        y: -20,
                        delay: 5, // Disparaît après 5 secondes
                        duration: 0.5,
                        ease: "power2.in",
                        onComplete: () => alert.remove()
                    });
                }
            });
        });
    });
</script>