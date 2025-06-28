<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion MySoutenance - Paramètres Année Académique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a3a5f;
            --secondary-color: #2a6db5;
            --accent-color: #1abc9c;
            --light-color: #e6f0fa;
            --dark-color: #0f2a4a;
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
            font-size: 0.9rem;
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

        .form-control {
            border: 1px solid #ced4da;
            transition: all 0.3s;
            height: 38px;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(42, 109, 181, 0.25);
        }

        .compact-form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .compact-form-group {
            flex: 1;
            min-width: 180px;
        }

        .form-button-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .compact-form-row {
                flex-direction: column;
            }

            .compact-form-group {
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
            <h1 class="page-title">MISE A JOUR ANNEE ACADEMIQUE</h1>
        </div>

        <!-- Formulaire de mise à jour -->
        <div class="card">
            <div class="card-body">
                <form>
                    <!-- Tous les champs sur une seule ligne -->
                    <div class="compact-form-row">
                        <div class="compact-form-group">
                            <label class="form-label">CODE ANNEE ACADEMIQUE</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="compact-form-group">
                            <label class="form-label">DATE_DEBUT</label>
                            <input type="date" class="form-control">
                        </div>

                        <div class="compact-form-group">
                            <label class="form-label">DATE_FIN</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>

                    <!-- Bouton en bas à droite -->
                    <div class="form-button-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>VALIDER
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>