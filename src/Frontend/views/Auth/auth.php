<div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
        <div class="text-center mb-4">
            <h1 class="text-2xl font-bold text-primary"><?= htmlspecialchars($pageTitle ?? 'GestionMySoutenance') ?></h1>
            <p class="text-base-content/70"><?= htmlspecialchars($pageSubtitle ?? 'Veuillez vous connecter') ?></p>
        </div>

        <!-- Zone pour les alertes globales (erreurs de session, succès) -->
        <?php if (!empty($alerts)): ?>
            <div id="global-alerts" class="space-y-2">
                <?php foreach ($alerts as $alert): ?>
                    <div role="alert" class="alert <?= $alert['type'] === 'error' ? 'alert-error' : 'alert-success' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2 2m2-2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?= htmlspecialchars($alert['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Zone pour les messages d'erreur/succès dynamiques du JS -->
        <div id="form-feedback" class="hidden"></div>

        <?php // =================== FORMULAIRE DE CONNEXION =================== ?>
        <?php if ($page === 'login'): ?>
            <form id="login-form" action="/login" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']['value'] ?? '') ?>">

                <div class="form-control">
                    <label class="label" for="login">
                        <span class="label-text">Identifiant ou Email</span>
                    </label>
                    <input type="text" id="login" name="login" placeholder="votre.identifiant" class="input input-bordered w-full" value="<?= htmlspecialchars($loginValue ?? '') ?>" required>
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Mot de passe</span>
                    </label>
                    <input type="password" id="password" name="password" placeholder="••••••••" class="input input-bordered w-full" required>
                    <label class="label">
                        <a href="/forgot-password" class="label-text-alt link link-hover">Mot de passe oublié ?</a>
                    </label>
                </div>

                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">
                        <span class="loading loading-spinner hidden"></span>
                        Connexion
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE 2FA =================== ?>
        <?php elseif ($page === '2fa'): ?>
            <form id="2fa-form" action="/verify-2fa" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']['value'] ?? '') ?>">
                <p class="text-center">Un code de vérification a été envoyé. Veuillez le saisir ci-dessous.</p>
                <div class="form-control">
                    <label class="label" for="2fa_code">
                        <span class="label-text">Code de vérification</span>
                    </label>
                    <input type="text" id="2fa_code" name="2fa_code" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="input input-bordered w-full text-center tracking-[1em]" required>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">
                        <span class="loading loading-spinner hidden"></span>
                        Vérifier
                    </button>
                </div>
            </form>

            <?php // =================== FORMULAIRE MOT DE PASSE OUBLIÉ =================== ?>
        <?php elseif ($page === 'forgot-password'): ?>
            <form id="forgot-password-form" action="/forgot-password" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']['value'] ?? '') ?>">
                <p class="text-center">Saisissez votre adresse email pour recevoir un lien de réinitialisation.</p>
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Adresse Email</span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="votre.email@example.com" class="input input-bordered w-full" required>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">
                        <span class="loading loading-spinner hidden"></span>
                        Envoyer le lien
                    </button>
                </div>
                <div class="text-center">
                    <a href="/login" class="link link-hover">Retour à la connexion</a>
                </div>
            </form>

            <?php // =================== FORMULAIRE DE RÉINITIALISATION DE MOT DE PASSE =================== ?>
        <?php elseif ($page === 'reset-password'): ?>
            <form id="reset-password-form" action="/reset-password" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']['value'] ?? '') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                <p class="text-center">Veuillez saisir votre nouveau mot de passe.</p>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Nouveau mot de passe</span>
                    </label>
                    <input type="password" id="password" name="password" class="input input-bordered w-full" required>
                </div>

                <div class="form-control">
                    <label class="label" for="password_confirm">
                        <span class="label-text">Confirmer le mot de passe</span>
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" class="input input-bordered w-full" required>
                </div>

                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">
                        <span class="loading loading-spinner hidden"></span>
                        Réinitialiser
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>