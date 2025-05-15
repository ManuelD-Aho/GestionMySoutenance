<?php
session_start();
require_once __DIR__ . '/../../../Config/Database.php';
require_once __DIR__ . '/../../../Backend/Model/Utilisateurs.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login_utilisateur'];
    $password = $_POST['mot_de_passe'];
    $pdo = Database::getInstance()->getConnection();
    $model = new authentification($pdo);
    $user = $model->authenticate($login, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: /dashboard');
        exit;
    } else {
        $error = "Login ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="/e.css">
</head>
<body>
<h1>Connexion</h1>
<?php if (isset($error) && $error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post" action="../../../Backend/Controller/authentification.php">
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