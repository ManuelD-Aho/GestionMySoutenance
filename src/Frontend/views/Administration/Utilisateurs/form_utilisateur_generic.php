<?php
// src/Frontend/views/Administration/Utilisateurs/form_utilisateur_generic.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données de l'utilisateur (si mode édition)
$user = $data['user'] ?? null;
$isEdit = !empty($user);

// ✅ Récupération des données depuis la base de données (passées par le contrôleur)
$types = $data['types'] ?? [];
$groupes = $data['groupes'] ?? [];
$niveauxAcces = $data['niveauxAcces'] ?? [];
$niveaux_etude = $data['niveaux_etude'] ?? [];
$annees_academiques = $data['annees_academiques'] ?? [];
$grades = $data['grades'] ?? [];
$specialites = $data['specialites'] ?? [];
$fonctions = $data['fonctions'] ?? [];

// Données du formulaire (en cas d'erreur)
$form_data = $data['form_data'] ?? [];
$form_errors = $data['form_errors'] ?? [];
$csrf_token = $data['csrf_token'] ?? '';
?>

    <div class="page-header mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
                <li class="breadcrumb-item"><a href="/admin/utilisateurs">Utilisateurs</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Modifier' : 'Créer' ?> Utilisateur</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>
                    <i class="fas fa-user-plus text-primary"></i>
                    <?= $isEdit ? 'Modifier un Utilisateur' : 'Créer un Nouvel Utilisateur' ?>
                </h1>
                <p class="text-muted mb-0">
                    Formulaire générique pour créer tout type d'utilisateur selon l'architecture RBAC
                </p>
            </div>
            <a href="/admin/utilisateurs" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Messages d'erreur globaux -->
<?php if (!empty($form_errors)): ?>
    <div class="alert alert-danger">
        <h6><i class="fas fa-exclamation-triangle"></i> Erreurs de validation :</h6>
        <ul class="mb-0">
            <?php foreach ($form_errors as $field => $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

    <!-- ✅ FORM CORRIGÉ avec action et token CSRF -->
    <form method="POST" action="/admin/utilisateurs/form" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row">
            <!-- Colonne Principale -->
            <div class="col-lg-8">

                <!-- Section Compte Utilisateur -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle"></i> Informations du Compte</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="login_utilisateur" class="form-label required">Login utilisateur</label>
                                    <input type="text"
                                           class="form-control <?= isset($form_errors['login_utilisateur']) ? 'is-invalid' : '' ?>"
                                           id="login_utilisateur"
                                           name="login_utilisateur"
                                           value="<?= e($form_data['login_utilisateur'] ?? $user['login_utilisateur'] ?? '') ?>"
                                        <?= $isEdit ? 'readonly' : 'required' ?>>
                                    <?php if (isset($form_errors['login_utilisateur'])): ?>
                                        <div class="invalid-feedback"><?= e($form_errors['login_utilisateur']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_principal" class="form-label required">Email principal</label>
                                    <input type="email"
                                           class="form-control <?= isset($form_errors['email_principal']) ? 'is-invalid' : '' ?>"
                                           id="email_principal"
                                           name="email_principal"
                                           value="<?= e($form_data['email_principal'] ?? $user['email_principal'] ?? '') ?>"
                                           required>
                                    <?php if (isset($form_errors['email_principal'])): ?>
                                        <div class="invalid-feedback"><?= e($form_errors['email_principal']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!$isEdit): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label required">Mot de passe initial</label>
                                        <input type="password"
                                               class="form-control <?= isset($form_errors['password']) ? 'is-invalid' : '' ?>"
                                               id="password"
                                               name="password"
                                               required
                                               minlength="8">
                                        <?php if (isset($form_errors['password'])): ?>
                                            <div class="invalid-feedback"><?= e($form_errors['password']) ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Minimum 8 caractères</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label required">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="form-text">Doit être identique au mot de passe</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Informations Personnelles -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Informations Personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label required">Nom</label>
                                    <input type="text"
                                           class="form-control <?= isset($form_errors['nom']) ? 'is-invalid' : '' ?>"
                                           id="nom"
                                           name="nom"
                                           value="<?= e($form_data['nom'] ?? $user['nom'] ?? '') ?>"
                                           required>
                                    <?php if (isset($form_errors['nom'])): ?>
                                        <div class="invalid-feedback"><?= e($form_errors['nom']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom" class="form-label required">Prénom(s)</label>
                                    <input type="text"
                                           class="form-control <?= isset($form_errors['prenom']) ? 'is-invalid' : '' ?>"
                                           id="prenom"
                                           name="prenom"
                                           value="<?= e($form_data['prenom'] ?? $user['prenom'] ?? '') ?>"
                                           required>
                                    <?php if (isset($form_errors['prenom'])): ?>
                                        <div class="invalid-feedback"><?= e($form_errors['prenom']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sexe" class="form-label required">Sexe</label>
                                    <select class="form-select <?= isset($form_errors['sexe']) ? 'is-invalid' : '' ?>"
                                            id="sexe"
                                            name="sexe"
                                            required>
                                        <option value="">Sélectionner</option>
                                        <option value="M" <?= ($form_data['sexe'] ?? $user['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                                        <option value="F" <?= ($form_data['sexe'] ?? $user['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Féminin</option>
                                    </select>
                                    <?php if (isset($form_errors['sexe'])): ?>
                                        <div class="invalid-feedback"><?= e($form_errors['sexe']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <input type="date"
                                           class="form-control"
                                           id="date_naissance"
                                           name="date_naissance"
                                           value="<?= e($form_data['date_naissance'] ?? $user['date_naissance'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel"
                                           class="form-control"
                                           id="telephone"
                                           name="telephone"
                                           value="<?= e($form_data['telephone'] ?? $user['telephone'] ?? '') ?>"
                                           placeholder="+33 6 12 34 56 78">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="adresse_postale" class="form-label">Adresse postale</label>
                                    <input type="text"
                                           class="form-control"
                                           id="adresse_postale"
                                           name="adresse_postale"
                                           value="<?= e($form_data['adresse_postale'] ?? $user['adresse_postale'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text"
                                           class="form-control"
                                           id="ville"
                                           name="ville"
                                           value="<?= e($form_data['ville'] ?? $user['ville'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Latérale -->
            <div class="col-lg-4">

                <!-- Section Type et Accès -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-key"></i> Type et Accès</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="id_type_utilisateur" class="form-label required">Type d'utilisateur</label>
                            <select class="form-select <?= isset($form_errors['id_type_utilisateur']) ? 'is-invalid' : '' ?>"
                                    id="id_type_utilisateur"
                                    name="id_type_utilisateur"
                                    required>
                                <option value="">Sélectionner un type</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= e($type['id_type_utilisateur'] ?? $type['id']) ?>"
                                        <?= ($form_data['id_type_utilisateur'] ?? $user['id_type_utilisateur'] ?? '') === ($type['id_type_utilisateur'] ?? $type['id']) ? 'selected' : '' ?>>
                                        <?= e($type['libelle_type_utilisateur'] ?? $type['libelle'] ?? $type['id_type_utilisateur'] ?? $type['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($form_errors['id_type_utilisateur'])): ?>
                                <div class="invalid-feedback"><?= e($form_errors['id_type_utilisateur']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="id_groupe_utilisateur" class="form-label required">Groupe principal</label>
                            <select class="form-select <?= isset($form_errors['id_groupe_utilisateur']) ? 'is-invalid' : '' ?>"
                                    id="id_groupe_utilisateur"
                                    name="id_groupe_utilisateur"
                                    required>
                                <option value="">Sélectionner un groupe</option>
                                <?php foreach ($groupes as $groupe): ?>
                                    <option value="<?= e($groupe['id_groupe_utilisateur'] ?? $groupe['id']) ?>"
                                        <?= ($form_data['id_groupe_utilisateur'] ?? $user['id_groupe_utilisateur'] ?? '') === ($groupe['id_groupe_utilisateur'] ?? $groupe['id']) ? 'selected' : '' ?>>
                                        <?= e($groupe['libelle_groupe_utilisateur'] ?? $groupe['libelle'] ?? $groupe['id_groupe_utilisateur'] ?? $groupe['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($form_errors['id_groupe_utilisateur'])): ?>
                                <div class="invalid-feedback"><?= e($form_errors['id_groupe_utilisateur']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Détermine les permissions de base</div>
                        </div>

                        <div class="mb-3">
                            <label for="id_niveau_acces_donne" class="form-label required">Niveau d'accès aux données</label>
                            <select class="form-select <?= isset($form_errors['id_niveau_acces_donne']) ? 'is-invalid' : '' ?>"
                                    id="id_niveau_acces_donne"
                                    name="id_niveau_acces_donne"
                                    required>
                                <option value="">Sélectionner un niveau</option>
                                <?php foreach ($niveauxAcces as $niveau): ?>
                                    <option value="<?= e($niveau['id_niveau_acces_donne'] ?? $niveau['id']) ?>"
                                        <?= ($form_data['id_niveau_acces_donne'] ?? $user['id_niveau_acces_donne'] ?? '') === ($niveau['id_niveau_acces_donne'] ?? $niveau['id']) ? 'selected' : '' ?>>
                                        <?= e($niveau['libelle_niveau_acces_donne'] ?? $niveau['libelle'] ?? $niveau['id_niveau_acces_donne'] ?? $niveau['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($form_errors['id_niveau_acces_donne'])): ?>
                                <div class="invalid-feedback"><?= e($form_errors['id_niveau_acces_donne']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sections conditionnelles selon le type -->

                <!-- Section Étudiant -->
                <div class="card mb-4" id="section_etudiant" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Informations Étudiant</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="id_niveau_etude" class="form-label">Niveau d'étude</label>
                            <select class="form-select" id="id_niveau_etude" name="id_niveau_etude">
                                <option value="">Sélectionner un niveau</option>
                                <?php foreach ($niveaux_etude as $niveau): ?>
                                    <option value="<?= e($niveau['id_niveau_etude'] ?? $niveau['id']) ?>"
                                        <?= ($form_data['id_niveau_etude'] ?? $user['id_niveau_etude'] ?? '') === ($niveau['id_niveau_etude'] ?? $niveau['id']) ? 'selected' : '' ?>>
                                        <?= e($niveau['libelle_niveau_etude'] ?? $niveau['libelle'] ?? $niveau['id_niveau_etude'] ?? $niveau['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="annee_academique" class="form-label">Année académique</label>
                            <select class="form-select" id="annee_academique" name="annee_academique">
                                <option value="">Sélectionner une année</option>
                                <?php foreach ($annees_academiques as $annee): ?>
                                    <option value="<?= e($annee['id_annee_academique'] ?? $annee['id']) ?>"
                                        <?= ($form_data['annee_academique'] ?? $user['annee_academique'] ?? '') === ($annee['id_annee_academique'] ?? $annee['id']) ? 'selected' : '' ?>>
                                        <?= e($annee['libelle_annee_academique'] ?? $annee['libelle'] ?? $annee['id_annee_academique'] ?? $annee['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="email_contact_secondaire" class="form-label">Email secondaire</label>
                            <input type="email"
                                   class="form-control"
                                   id="email_contact_secondaire"
                                   name="email_contact_secondaire"
                                   value="<?= e($form_data['email_contact_secondaire'] ?? $user['email_contact_secondaire'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Enseignant -->
                <div class="card mb-4" id="section_enseignant" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Informations Enseignant</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="id_grade" class="form-label">Grade universitaire</label>
                            <select class="form-select" id="id_grade" name="id_grade">
                                <option value="">Sélectionner un grade</option>
                                <?php foreach ($grades as $grade): ?>
                                    <option value="<?= e($grade['id_grade'] ?? $grade['id']) ?>"
                                        <?= ($form_data['id_grade'] ?? $user['id_grade'] ?? '') === ($grade['id_grade'] ?? $grade['id']) ? 'selected' : '' ?>>
                                        <?= e($grade['libelle_grade'] ?? $grade['libelle'] ?? $grade['id_grade'] ?? $grade['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_specialite" class="form-label">Spécialité</label>
                            <select class="form-select" id="id_specialite" name="id_specialite">
                                <option value="">Sélectionner une spécialité</option>
                                <?php foreach ($specialites as $specialite): ?>
                                    <option value="<?= e($specialite['id_specialite'] ?? $specialite['id']) ?>"
                                        <?= ($form_data['id_specialite'] ?? $user['id_specialite'] ?? '') === ($specialite['id_specialite'] ?? $specialite['id']) ? 'selected' : '' ?>>
                                        <?= e($specialite['libelle_specialite'] ?? $specialite['libelle'] ?? $specialite['id_specialite'] ?? $specialite['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Personnel -->
                <div class="card mb-4" id="section_personnel" style="display: none;">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase"></i> Informations Personnel</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="id_fonction" class="form-label">Fonction</label>
                            <select class="form-select" id="id_fonction" name="id_fonction">
                                <option value="">Sélectionner une fonction</option>
                                <?php foreach ($fonctions as $fonction): ?>
                                    <option value="<?= e($fonction['id_fonction'] ?? $fonction['id']) ?>"
                                        <?= ($form_data['id_fonction'] ?? $user['id_fonction'] ?? '') === ($fonction['id_fonction'] ?? $fonction['id']) ? 'selected' : '' ?>>
                                        <?= e($fonction['libelle_fonction'] ?? $fonction['libelle'] ?? $fonction['id_fonction'] ?? $fonction['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="poste" class="form-label">Poste</label>
                            <input type="text"
                                   class="form-control"
                                   id="poste"
                                   name="poste"
                                   value="<?= e($form_data['poste'] ?? $user['poste'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="service_affectation" class="form-label">Service d'affectation</label>
                            <input type="text"
                                   class="form-control"
                                   id="service_affectation"
                                   name="service_affectation"
                                   value="<?= e($form_data['service_affectation'] ?? $user['service_affectation'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-<?= $isEdit ? 'save' : 'user-plus' ?>"></i>
                            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' ?>
                        </button>
                    </div>
                    <div>
                        <a href="/admin/utilisateurs" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('id_type_utilisateur');
            const groupeSelect = document.getElementById('id_groupe_utilisateur');
            const niveauAccesSelect = document.getElementById('id_niveau_acces_donne');

            // Sections conditionnelles
            const sectionEtudiant = document.getElementById('section_etudiant');
            const sectionEnseignant = document.getElementById('section_enseignant');
            const sectionPersonnel = document.getElementById('section_personnel');

            // Champs requis conditionnels
            const niveauEtudeSelect = document.getElementById('id_niveau_etude');
            const gradeSelect = document.getElementById('id_grade');
            const fonctionSelect = document.getElementById('id_fonction');

            function toggleSections() {
                const selectedType = typeSelect.value;

                // Masquer toutes les sections
                sectionEtudiant.style.display = 'none';
                sectionEnseignant.style.display = 'none';
                sectionPersonnel.style.display = 'none';

                // Retirer les attributs required
                niveauEtudeSelect.required = false;
                gradeSelect.required = false;
                fonctionSelect.required = false;

                // Afficher et configurer selon le type
                switch(selectedType) {
                    case 'TYPE_ETUD':
                        sectionEtudiant.style.display = 'block';
                        niveauEtudeSelect.required = true;

                        // Pré-sélectionner des valeurs logiques
                        if (!groupeSelect.value) {
                            setSelectValue(groupeSelect, 'GRP_ETUDIANT');
                        }
                        if (!niveauAccesSelect.value) {
                            setSelectValue(niveauAccesSelect, 'ACCES_ETUDIANT');
                        }
                        break;

                    case 'TYPE_ENS':
                        sectionEnseignant.style.display = 'block';
                        gradeSelect.required = true;

                        if (!groupeSelect.value) {
                            setSelectValue(groupeSelect, 'GRP_ENSEIGNANT');
                        }
                        if (!niveauAccesSelect.value) {
                            setSelectValue(niveauAccesSelect, 'ACCES_DEPARTEMENT');
                        }
                        break;

                    case 'TYPE_PERS_ADMIN':
                        sectionPersonnel.style.display = 'block';
                        fonctionSelect.required = true;

                        if (!groupeSelect.value) {
                            setSelectValue(groupeSelect, 'GRP_PERSONNEL');
                        }
                        if (!niveauAccesSelect.value) {
                            setSelectValue(niveauAccesSelect, 'ACCES_SERVICE');
                        }
                        break;
                }
            }

            function setSelectValue(selectElement, value) {
                // Fonction utilitaire pour sélectionner une valeur si elle existe
                for (let option of selectElement.options) {
                    if (option.value === value || option.value.includes(value)) {
                        option.selected = true;
                        break;
                    }
                }
            }

            // Écouteur d'événement pour le changement de type
            typeSelect.addEventListener('change', toggleSections);

            // Initialisation
            toggleSections();

            // Validation du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                // Validation des mots de passe
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');

                if (password && confirmPassword) {
                    if (password.value !== confirmPassword.value) {
                        alert('Les mots de passe ne correspondent pas.');
                        event.preventDefault();
                        return;
                    }
                }

                // Validation spécifique selon le type
                const selectedType = typeSelect.value;
                if (selectedType === 'TYPE_ETUD' && niveauEtudeSelect.required && !niveauEtudeSelect.value) {
                    alert('Le niveau d\'étude est obligatoire pour un étudiant.');
                    event.preventDefault();
                    return;
                }

                if (selectedType === 'TYPE_ENS' && gradeSelect.required && !gradeSelect.value) {
                    alert('Le grade est obligatoire pour un enseignant.');
                    event.preventDefault();
                    return;
                }

                if (selectedType === 'TYPE_PERS_ADMIN' && fonctionSelect.required && !fonctionSelect.value) {
                    alert('La fonction est obligatoire pour un personnel.');
                    event.preventDefault();
                    return;
                }
            });
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
