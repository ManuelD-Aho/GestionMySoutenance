<?php
// Affichage uniquement du formulaire et des erreurs éventuelles
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = isset($error) ? $error : (isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<h1>Connexion</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post" action="/login">
    <label>Login :
        <input type="text" name="login_utilisateur" required>
    </label><br>
    <label>Mot de passe :
        <input type="password" name="mot_de_passe" required>
    </label><br>
    <button type="submit">Se connecter</button>
</form>
</body>
</html>

