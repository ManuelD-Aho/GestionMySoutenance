<?php
// Assurer que la variable $form est définie pour éviter les erreurs
$form = $form ?? 'login';
?>
<div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
        <div class="text-center mb-4">
            <h1 class="text-2xl font-bold text-primary">GestionMySoutenance</h1>
            <p class="text-base-content/70">
                <?php
                echo [
                    'login' => 'Veuillez vous connecter à votre compte',
                    '2fa' => 'Vérification à deux facteurs',
                    'forgot_password' => 'Récupération de mot de passe',
                    'reset_password' => 'Choisissez un nouveau mot de passe'
                ][$form] ?? 'Authentification';
                ?>
            </p>
        </div>

        <!-- Zone pour les messages flash (erreurs, succès) -->
        <?php if (!empty($flash)): ?>
            <div id="global-alerts" class="space-y-2">
                <?php foreach ($flash as $type => $message): ?>
                    <div role="alert" class="alert <?= $type === 'error' ? 'alert-error' : 'alert-success' ?> shadow-md">
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Zone pour les retours dynamiques du JS -->
        <div id="form-feedback" class="hidden mt-4"></div>

        <?php // =================== FORMULAIRE DE CONNEXION =================== ?>
        <?php if ($form === 'login'): ?>
            <form id="login-form" action="/login" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <div class="form-control">
                    <label class="label" for="identifiant"><span class="label-text">Identifiant ou Email</span></label>
                    <input type="text" id="identifiant" name="identifiant" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="mot_de_passe"><span class="label-text">Mot de passe</span></label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="input input-bordered w-full" required>
                    <label class="label"><a href="/forgot-password" class="label-text-alt link link-hover">Mot de passe oublié ?</a></label>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="loading loading-spinner"></span><span>Connexion</span>
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE 2FA =================== ?>
        <?php elseif ($form === '2fa'): ?>
            <form id="2fa-form" action="/login/2fa" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <p class="text-center text-sm">Saisissez le code à 6 chiffres de votre application d'authentification.</p>
                <div class="form-control">
                    <label class="label" for="code_2fa"><span class="label-text">Code de vérification</span></label>
                    <input type="text" id="code_2fa" name="code_2fa" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="input input-bordered w-full text-center text-lg tracking-[0.5em]" required>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="loading loading-spinner"></span><span>Vérifier</span>
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE MOT DE PASSE OUBLIÉ =================== ?>
        <?php elseif ($form === 'forgot_password'): ?>
            <form id="forgot-password-form" action="/forgot-password" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <p class="text-center text-sm">Saisissez votre adresse email pour recevoir un lien de réinitialisation.</p>
                <div class="form-control">
                    <label class="label" for="email"><span class="label-text">Adresse Email</span></label>
                    <input type="email" id="email" name="email" class="input input-bordered w-full" required>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="loading loading-spinner"></span><span>Envoyer le lien</span>
                    </button>
                </div>
                <div class="text-center mt-4"><a href="/login" class="link link-hover">Retour à la connexion</a></div>
            </form>

            <?php // =================== FORMULAIRE DE RÉINITIALISATION DE MOT DE PASSE =================== ?>
        <?php elseif ($form === 'reset_password'): ?>
            <form id="reset-password-form" action="/reset-password" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                <div class="form-control">
                    <label class="label" for="nouveau_mot_de_passe"><span class="label-text">Nouveau mot de passe</span></label>
                    <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="confirmer_mot_de_passe"><span class="label-text">Confirmer le mot de passe</span></label>
                    <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="input input-bordered w-full" required>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="loading loading-spinner"></span><span>Réinitialiser</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>