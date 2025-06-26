<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - GestionMySoutenance</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',system-ui,sans-serif}
        body{background:linear-gradient(135deg,#e6f2ff,#c2d9ff);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:15px}
        .login-container{background:white;border-radius:12px;box-shadow:0 10px 25px rgba(50,120,220,0.15);padding:30px;width:100%;max-width:400px;position:relative}
        .login-container::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#3b82f6,#60a5fa);z-index:2}
        .login-header{text-align:center;margin-bottom:20px}
        .login-header h1{font-size:22px;font-weight:700;color:#1e3a8a}
        .alert{padding:12px 15px;border-radius:8px;margin-bottom:15px;font-size:14px;position:relative}
        .alert::before{content:'';position:absolute;top:0;left:0;height:100%;width:5px}
        .alert-error{background:rgba(231,76,60,0.1);border-left:4px solid #e74c3c;color:#c0392b}
        .alert-success{background:rgba(46,204,113,0.1);border-left:4px solid #2ecc71;color:#166534}
        .alert-warning{background:rgba(243,156,18,0.1);border-left:4px solid #f39c12;color:#b45309}
        .alert-info{background:rgba(52,152,219,0.1);border-left:4px solid #3498db;color:#1d4ed8}
        .form-group{margin-bottom:15px}
        .form-group label{display:block;margin-bottom:8px;font-weight:600;font-size:14px;color:#1e293b}
        .form-input-wrapper{position:relative}
        .form-input{width:100%;padding:12px 15px;border:1px solid #dbeafe;border-radius:8px;font-size:14px;background:#f8fafc;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
        .form-input:focus{outline:none;border-color:#93c5fd;background:white;box-shadow:0 0 0 3px rgba(147,197,253,0.25)}
        .password-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:18px}
        .btn-primary{background:linear-gradient(to right,#3b82f6,#2563eb);color:white;border:none;border-radius:8px;padding:12px;font-size:15px;font-weight:600;width:100%;cursor:pointer;margin-top:5px;box-shadow:0 3px 10px rgba(59,130,246,0.3)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(59,130,246,0.4)}
        .links{margin-top:15px;text-align:center}
        .links a{color:#3b82f6;font-weight:600;font-size:13px;text-decoration:none;padding:5px 10px;border-radius:6px}
        .links a:hover{color:#1e40af;background:rgba(59,130,246,0.1)}
        .login-footer{margin-top:20px;color:#64748b;font-size:12px;text-align:center}
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h1>Connexion</h1>
    </div>

    <?php
    if (isset($flash_messages) && is_array($flash_messages)) {
        foreach ($flash_messages as $type => $message) {
            if ($message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            }
        }
    }
    ?>

    <form action="/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="login_email">Login ou Email :</label>
            <div class="form-input-wrapper">
                <input class="form-input" type="text" id="login_email" name="login_email" required
                       autocomplete="username" placeholder="Login ou email">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <div class="form-input-wrapper">
                <input class="form-input" type="password" id="password" name="password" required
                       autocomplete="current-password" placeholder="Mot de passe">
                <span class="password-toggle" onclick="togglePasswordVisibility('password')">👁️</span>
            </div>
        </div>

        <button type="submit" class="btn-primary">Se connecter</button>
    </form>

    <div class="links">
        <a href="/forgot-password">Mot de passe oublié ?</a>
    </div>

    <div class="login-footer">
        <p>&copy;<?= date('Y') ?> GestionMySoutenance</p>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const toggle = field.nextElementSibling;
        field.type = field.type === "password" ? "text" : "password";
        toggle.textContent = field.type === "password" ? "👁️" : "🔒";
    }
</script>
</body>
</html>