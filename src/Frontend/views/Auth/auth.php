<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - GestionMySoutenance</title>
    <link rel="stylesheet" href="/assets/css/roots.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <!-- Carrousel d'images - Côté gauche -->
    <div class="carousel-section">
        <div class="carousel-wrapper">
            <div class="carousel-container">
                <div class="carousel-slide active">
                    <img src="/assets/img/ufhb.jpeg" alt="Étudiant présentant sa soutenance">
                    <div class="slide-content">
                        <h3>Excellence Académique</h3>
                        <p>Accompagnez vos étudiants vers la réussite de leurs soutenances</p>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="/assets/img/student2.jpg" alt="Jury de soutenance">
                    <div class="slide-content">
                        <h3>Gestion Simplifiée</h3>
                        <p>Organisez efficacement vos soutenances et jurys</p>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="/assets/img/student3.jpg" alt="Étudiant travaillant">
                    <div class="slide-content">
                        <h3>Suivi Personnalisé</h3>
                        <p>Suivez le progrès de chaque étudiant en temps réel</p>
                    </div>
                </div>
                <div class="carousel-indicators">
                    <button class="indicator active" data-slide="0"></button>
                    <button class="indicator" data-slide="1"></button>
                    <button class="indicator" data-slide="2"></button>
                </div>
            </div>

        </div>
    </div>

    <!-- Section des formulaires - Côté droit -->
    <div class="forms-section">
        <div class="form-container">
            <!-- Header avec logo -->
            <div class="form-header">
                <div class="logo">
                    <h1>GestionMySoutenance</h1>
                    <p>Plateforme de gestion des soutenances</p>
                </div>
            </div>

            <!-- Formulaire de connexion -->
            <form id="loginForm" class="auth-form active" method="POST" action="/login">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <h2>Connexion</h2>
                <div class="form-group">
                    <label for="login_email">Login ou Email :</label>
                    <input type="text" id="login_email" name="identifiant" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="mot_de_passe" required>
                </div>
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember_me">
                        <span class="checkmark"></span>
                        Se souvenir de moi
                    </label>
                    <a href="#" class="forgot-link" onclick="showForm('forgotForm')">Mot de passe oublié ?</a>
                </div>
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>

            <!-- Formulaire mot de passe oublié -->
            <form id="forgotForm" class="auth-form" method="POST" action="/forgot_password">
                <h2>Mot de passe oublié</h2>
                <p class="form-description">Entrez votre email pour recevoir un lien de réinitialisation</p>
                <div class="form-group">
                    <label for="email_principal">Email</label>
                    <input type="email" id="email_principal" name="email_principal" required>
                </div>
                <button type="submit" class="btn-primary">Envoyer le lien</button>
                <div class="form-footer">
                    <p><a href="#" onclick="showForm('loginForm')">← Retour à la connexion</a></p>
                </div>
            </form>

            <!-- Formulaire réinitialisation mot de passe -->
            <form id="resetForm" class="auth-form" method="POST" action="/reset_password">
                <h2>Nouveau mot de passe</h2>
                <p class="form-description">Choisissez un nouveau mot de passe sécurisé</p>
                <input type="hidden" name="token" id="reset_token">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-primary">Réinitialiser</button>
                <div class="form-footer">
                    <p><a href="#" onclick="showForm('loginForm')">← Retour à la connexion</a></p>
                </div>
            </form>

            <!-- Formulaire 2FA -->
            <form id="twoFactorForm" class="auth-form" method="POST" action="/2fa">
                <h2>Authentification à deux facteurs</h2>
                <p class="form-description">Entrez le code de vérification envoyé sur votre téléphone</p>
                <div class="form-group">
                    <label for="verification_code">Code de vérification</label>
                    <input type="text" id="code_2fa" name="code_2fa" maxlength="6" required>
                </div>
                <button type="submit" class="btn-primary">Vérifier</button>
                <div class="form-footer">
                    <p><a href="#" onclick="showForm('loginForm')">← Retour à la connexion</a></p>
                    <p><a href="#" onclick="resend2FA()">Renvoyer le code</a></p>
                </div>
            </form>

            <!-- Messages d'erreur/succès -->
            <div id="message" class="message"></div>
        </div>
    </div>
</div>

<script src="/assets/js/auth.js"></script>
</body>
</html>
