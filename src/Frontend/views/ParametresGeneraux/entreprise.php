<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion MySoutenance - Paramètres Entreprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a3a5f;   /* Bleu marine foncé */
            --secondary-color: #2a6db5; /* Bleu royal */
            --accent-color: #1abc9c;    /* Turquoise pour accents */
            --light-color: #e6f0fa;    /* Bleu très clair */
            --dark-color: #0f2a4a;      /* Bleu nuit */
            --success-color: #2ecc71;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e6f0fa, #d1e4f5);
            color: #333;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, var(--primary-color), var(--dark-color));
            color: white;
            transition: all 0.3s;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
        }

        .sidebar-menu a {
            padding: 12px 20px;
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(0,0,0,0.2);
            color: white;
            border-left: 3px solid var(--accent-color);
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #2a6db5, #1a3a5f);
            color: white;
        }

        .page-title {
            font-weight: 600;
            margin: 0;
            text-align: center;
            color: white;
            font-size: 1.8rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 25px;
            background: white;
            border-top: 3px solid var(--secondary-color);
        }

        .card-header {
            background: linear-gradient(to right, #e6f0fa, #d1e4f5);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary-color);
            border-radius: 10px 10px 0 0 !important;
            font-size: 1.2rem;
        }

        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
            width: 200px; /* Largeur réduite du label */
        }

        .btn-primary {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            border: none;
            padding: 8px 20px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #1f5ea0, #152f4f);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-action {
            padding: 5px 10px;
            margin: 0 3px;
            font-size: 0.85rem;
            border-radius: 4px;
        }

        .btn-edit {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .btn-print {
            background: linear-gradient(to right, #1abc9c, #16a085);
            color: white;
        }

        .table th {
            background: linear-gradient(to bottom, #e6f0fa, #d1e4f5);
            color: var(--primary-color);
            font-weight: 600;
            border-top: 1px solid #dee2e6;
        }

        .table th:first-child {
            width: 50px;
        }

        .table th:last-child {
            width: 150px;
        }

        .badge-credit {
            background: linear-gradient(to right, #9b59b6, #8e44ad);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            color: white;
        }

        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .action-icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .action-icons i {
            font-size: 1.2rem;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-icons i:hover {
            color: var(--secondary-color);
        }

        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            background: linear-gradient(to right, white, #f0f7ff);
        }

        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--secondary-color);
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo span {
            color: var(--accent-color);
        }

        .form-control {
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(42, 109, 181, 0.25);
        }

        .icon-bar {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .icon-group {
            display: flex;
            gap: 15px;
        }

        .icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .icon-item:hover {
            transform: translateY(-5px);
        }

        .icon-item i {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .icon-item span {
            font-size: 0.85rem;
            color: #555;
        }

        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .company-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, #2a6db5, #1abc9c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .form-row {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
        }

        .form-button {
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            .form-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">

    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">MISE A JOUR ENTREPRISE</h1>
        </div>

        <!-- Formulaire de mise à jour -->
        <div class="card">

            <div class="card-body">
                <form>
                    <div class="form-row">
                        <div class="col-md-3">
                            <label class="form-label">CODE DE D'ENTREPRISE</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">NOM DE L'ENTREPRISE</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-button">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>VALIDER
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

</div>
</body>
</html>