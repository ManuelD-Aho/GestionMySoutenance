<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            padding: 40px;
            width: 400px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
        }

        .form-input {
            width: 100%;
            padding: 10px 0;
            font-size: 16px;
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            background: transparent;
        }

        .form-label {
            position: absolute;
            top: 10px;
            left: 0;
            font-size: 16px;
            color: #999;
            pointer-events: none;
            transition: 0.3s ease all;
        }

        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            top: -20px;
            font-size: 12px;
            color: #6e8efb;
        }

        .form-input:focus ~ .form-underline:before {
            transform: scaleX(1);
        }

        .form-underline {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
        }

        .form-underline:before {
            content: "";
            position: absolute;
            height: 100%;
            width: 100%;
            background: #6e8efb;
            transform: scaleX(0);
            transition: 0.3s ease all;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease all;
        }

        .btn:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #6e8efb;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Connexion</h1>
    <form>
        <div class="form-group">
            <input type="email" class="form-input" id="email" placeholder=" ">
            <div class="form-underline"></div>
            <label class="form-label" for="email">Adresse e-mail</label>
        </div>

        <div class="form-group">
            <input type="password" class="form-input" id="password" placeholder=" ">
            <div class="form-underline"></div>
            <label class="form-label" for="password">Mot de passe</label>
        </div>

        <button type="submit" class="btn">Se connecter</button>

        <div class="forgot-password">
            <a href="#">Mot de passe oublié ?</a>
        </div>
    </form>
</div>
</body>
</html>