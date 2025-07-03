<?php
// src/Frontend/views/Administration/Utilisateurs/form_utilisateur_generic.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données de l'utilisateur à modifier (si mode édition)
// Ces données proviendraient du contrôleur UtilisateurController
//

$utilisateur = $data['utilisateur'] ?? null;
$is_edit_mode = (bool)$utilisateur;

// Exemple de données pour les listes déroulantes (types et groupes)
$types_utilisateur_disponibles = $data['types_utilisateur_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Administrateur Système', 'code' => 'TYPE_ADMIN'],
    ['id' => 2, 'libelle' => 'Étudiant', 'code' => 'TYPE_ETUDIANT'],
    ['id' => 3, 'libelle' => 'Enseignant', 'code' => 'TYPE_ENSEIGNANT'],
    ['id' => 4, 'libelle' => 'Personnel Administratif', 'code' => 'TYPE_PERSONNEL_ADMIN'],
];

$groupes_disponibles = $data['groupes_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'GRP_ADMIN'],
    ['id' => 2, 'libelle' => 'GRP_ETUDIANT'],
    ['id' => 3, 'libelle' => 'GRP_ENSEIGNANT'],
    ['id' => 4, 'libelle' => 'GRP_RS'],
    ['id' => 5, 'libelle' => 'GRP_AGENT_CONFORMITE'],
    ['id' => 6, 'libelle' => 'GRP_COMMISSION'],
];

?>

    <div class="admin-module-container">
        <h1 class="admin-title"><?= $is_edit_mode ? 'Modifier l\'Utilisateur Générique' : 'Ajouter un Nouvel Utilisateur (Générique)'; ?></h1>

        <section class="section-form admin-card">
            <h2 class="section-title">Informations de l'Utilisateur</h2>
            <form id="formUtilisateurGeneric" action="/admin/utilisateurs/generic/<?= $is_edit_mode ? 'update/' . e($utilisateur['id']) : 'create'; ?>" method="POST">

                <fieldset class="form-section">
                    <legend>Informations de Compte et Personnelles</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" value="<?= e($utilisateur['nom'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom(s) :</label>
                            <input type="text" id="prenom" name="prenom" value="<?= e($utilisateur['prenom'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email :</label>
                            <input type="email" id="email" name="email" value="<?= e($utilisateur['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone :</label>
                            <input type="tel" id="telephone" name="telephone" value="<?= e($utilisateur['telephone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="adresse">Adresse :</label>
                            <input type="text" id="adresse" name="adresse" value="<?= e($utilisateur['adresse'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_naissance">Date de Naissance :</label>
                            <input type="date" id="date_naissance" name="date_naissance" value="<?= e($utilisateur['date_naissance'] ?? ''); ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section mt-xl">
                    <legend>Rôle et Groupes</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_utilisateur_id">Type d'Utilisateur :</label>
                            <select id="type_utilisateur_id" name="type_utilisateur_id" required>
                                <option value="">Sélectionner un type</option>
                                <?php foreach ($types_utilisateur_disponibles as $type): ?>
                                    <option value="<?= e($type['id']); ?>"
                                        <?= ($utilisateur['type_utilisateur_id'] ?? '') == $type['id'] ? 'selected' : ''; ?>>
                                        <?= e($type['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="groupe_utilisateur_id">Groupe Principal :</label>
                            <select id="groupe_utilisateur_id" name="groupe_utilisateur_id" required>
                                <option value="">Sélectionner un groupe</option>
                                <?php foreach ($groupes_disponibles as $groupe): ?>
                                    <option value="<?= e($groupe['id']); ?>"
                                        <?= ($utilisateur['groupe_utilisateur_id'] ?? '') == $groupe['id'] ? 'selected' : ''; ?>>
                                        <?= e($groupe['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <?php if (!$is_edit_mode): ?>
                    <fieldset class="form-section mt-xl">
                        <legend>Mot de Passe Initial</legend>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password">Mot de passe initial :</label>
                                <input type="password" id="password" name="password" required>
                                <small class="form-help">Le mot de passe temporaire pour la première connexion de l'utilisateur.</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le mot de passe :</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </fieldset>
                <?php endif; ?>

                <div class="form-actions mt-xl">
                    <button type="submit" class="btn btn-primary-blue">
                        <span class="material-icons"><?= $is_edit_mode ? 'save' : 'person_add'; ?></span>
                        <?= $is_edit_mode ? 'Enregistrer les modifications' : 'Ajouter l\'utilisateur'; ?>
                    </button>
                    <a href="/admin/utilisateurs/liste" class="btn btn-secondary-gray ml-md">
                        <span class="material-icons">cancel</span> Annuler
                    </a>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formUtilisateurGeneric');
            if (form) {
                form.addEventListener('submit', function(event) {
                    // Validation des champs communs
                    const nom = document.getElementById('nom').value.trim();
                    const prenom = document.getElementById('prenom').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const typeUtilisateurId = document.getElementById('type_utilisateur_id').value;
                    const groupeUtilisateurId = document.getElementById('groupe_utilisateur_id').value;

                    if (!nom || !prenom || !email || !typeUtilisateurId || !groupeUtilisateurId) {
                        alert('Veuillez remplir tous les champs obligatoires (Nom, Prénom, Email, Type d\'Utilisateur, Groupe Principal).');
                        event.preventDefault();
                        return;
                    }

                    // Validation de l'email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        alert('Veuillez saisir une adresse email valide.');
                        event.preventDefault();
                        return;
                    }

                    // Validation spécifique au mode création (mots de passe)
                    <?php if (!$is_edit_mode): ?>
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;

                    if (!password || !confirmPassword) {
                        alert('Veuillez définir et confirmer le mot de passe initial.');
                        event.preventDefault();
                        return;
                    }
                    if (password !== confirmPassword) {
                        alert('Les mots de passe ne correspondent pas.');
                        event.preventDefault();
                        return;
                    }
                    // Validation de la complexité du mot de passe (peut être plus stricte)
                    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}\[\]:;"'<>,.?\/\\-]).{8,}$/;
                    if (!passwordRegex.test(password)) {
                        alert("Le mot de passe doit contenir au moins 8 caractères, incluant une majuscule, une minuscule, un chiffre et un caractère spécial.");
                        event.preventDefault();
                        return;
                    }
                    <?php endif; ?>

                    console.log("Formulaire Utilisateur Générique soumis.");
                });
            }

            // Gestion de l'affichage des messages flash
            const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
            if (flashMessage) {
                console.log("Message Flash:", flashMessage);
                <?php unset($_SESSION['flash_message']); ?>
            }
        });
    </script>

    <style>
        /* Styles spécifiques pour form_utilisateur_generic.php */
        /* Réutilisation des classes de root.css et admin_module.css */

        /* Conteneur et titres principaux - réutilisés */
        .admin-module-container {
            padding: var(--spacing-lg);
            background-color: var(--bg-primary);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            max-width: 900px; /* Largeur adaptée au formulaire */
            margin: var(--spacing-xl) auto;
        }

        .admin-title {
            font-size: var(--font-size-2xl);
            color: var(--text-primary);
            margin-bottom: var(--spacing-xl);
            text-align: center;
            font-weight: var(--font-weight-semibold);
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid var(--border-light);
        }

        .admin-card {
            background-color: var(--bg-secondary);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .section-title {
            font-size: var(--font-size-xl);
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
            font-weight: var(--font-weight-medium);
            border-bottom: 1px solid var(--border-medium);
            padding-bottom: var(--spacing-sm);
        }

        /* Formulaires - réutilisation et adaptation */
        .form-section {
            border: 1px solid var(--border-light);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            background-color: var(--primary-white);
        }

        .form-section legend {
            font-size: var(--font-size-lg);
            color: var(--primary-blue-dark);
            font-weight: var(--font-weight-semibold);
            padding: 0 var(--spacing-xs);
            margin-left: var(--spacing-sm);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-md);
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xs);
            font-weight: var(--font-weight-medium);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="password"],
        .form-group select {
            padding: var(--spacing-sm);
            border: 1px solid var(--border-medium);
            border-radius: var(--border-radius-sm);
            font-size: var(--font-size-base);
            color: var(--text-primary);
            background-color: var(--primary-white);
            transition: border-color var(--transition-fast);
            width: 100%;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .form-help {
            font-size: var(--font-size-xs);
            color: var(--text-light);
            margin-top: var(--spacing-xs);
        }

        /* Actions du formulaire */
        .form-actions {
            display: flex;
            justify-content: center;
            gap: var(--spacing-md);
            margin-top: var(--spacing-xl);
        }

        /* Boutons - réutilisation des styles existants */
        .btn {
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            text-decoration: none;
        }

        .btn-primary-blue {
            color: var(--text-white);
            background-color: var(--primary-blue);
        }

        .btn-primary-blue:hover {
            background-color: var(--primary-blue-dark);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary-gray {
            color: var(--text-primary);
            background-color: var(--primary-gray-light);
            border: 1px solid var(--border-medium);
        }

        .btn-secondary-gray:hover {
            background-color: var(--border-medium);
            box-shadow: var(--shadow-sm);
        }

        .ml-md { margin-left: var(--spacing-md); }
        .mt-xl { margin-top: var(--spacing-xl); }
    </style><?php
// form_utilisateur_generic.php
