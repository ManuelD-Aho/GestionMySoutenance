<!-- src/Frontend/views/Auth/login.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion à GestionMySoutenance</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Votre CSS principal -->
    <style>
        /* Styles spécifiques à la page de login */
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        .login-container h1 { margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input[type="text"],
        .form-group input[type="password"] { width: calc(100% - 20px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn-primary { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 18px; width: 100%; margin-top: 20px; }
        .btn-primary:hover { background-color: #0056b3; }
        .links { margin-top: 20px; }
        .links a { color: #007bff; text-decoration: none; font-size: 14px; }
        .links a:hover { text-decoration: underline; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Connexion</h1>

    <?php
    // Affichage des messages flash (passés par BaseController)
    if (isset($flash_messages) && is_array($flash_messages)) {
        foreach ($flash_messages as $type => $message) {
            echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
        }
    }
    ?>

    <form action="/login" method="POST">
        <div class="form-group">
            <label for="login_email">Login ou Email :</label>
            <input type="text" id="login_email" name="login_email" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-primary">Se connecter</button>
    </form>
    <div class="links">
        <a href="/forgot-password">Mot de passe oublié ?</a>
    </div>
</div>
</body>
</html>