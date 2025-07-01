<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès Refusé</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f4f7f9; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .error-container { text-align: center; background-color: #fff; padding: 50px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); max-width: 600px; border-top: 5px solid #c0392b; }
        .error-code { font-family: 'Lora', serif; font-size: 8rem; font-weight: 700; color: #c0392b; margin: 0; line-height: 1; }
        .error-title { font-family: 'Lora', serif; font-size: 2rem; font-weight: 700; color: #2c3e50; margin: 10px 0; }
        .error-message { font-size: 1.1rem; color: #555; margin-bottom: 30px; }
        .error-details { font-size: 0.9rem; color: #7f8c8d; background-color: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; border-radius: 4px; margin-top: 15px; }
        .home-link { display: inline-block; margin-top: 30px; padding: 12px 25px; background-color: #2c3e50; color: #fff; text-decoration: none; border-radius: 5px; font-weight: 600; transition: background-color 0.3s; }
        .home-link:hover { background-color: #34495e; }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-code">403</div>
    <div class="error-title">Accès Refusé</div>
    <p class="error-message">Vous n'avez pas les autorisations nécessaires pour accéder à cette page ou à cette ressource.</p>
    <?php if (isset($error_message) && !empty($error_message)): ?>
        <div class="error-details">
            <strong>Détail :</strong> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    <a href="/dashboard" class="home-link">Retour au Tableau de Bord</a>
</div>
</body>
</html>