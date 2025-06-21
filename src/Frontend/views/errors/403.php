<!-- src/Frontend/views/errors/403.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé (403)</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Un CSS de base pour le style -->
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error-container { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 20px; border-radius: 8px; max-width: 600px; margin: 50px auto; }
        h1 { color: #dc3545; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
<div class="error-container">
    <h1>Accès Refusé - Erreur 403</h1>
    <p>Désolé, vous n'avez pas la permission d'accéder à cette ressource.</p>
    <?php if (isset($flash_messages['error'])) : ?>
        <p><strong>Message:</strong> <?php echo htmlspecialchars($flash_messages['error']); ?></p>
    <?php endif; ?>
    <p><a href="/dashboard">Retour au Tableau de Bord</a></p>
    <p><a href="/">Retour à l'accueil</a></p>
</div>
</body>
</html>