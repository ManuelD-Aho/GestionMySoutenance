<!-- src/Frontend/views/Administration/Utilisateurs/form_etudiant.php -->
<?php
$this->layout('layouts/layout_admin', ['title' => $title ?? 'Formulaire Étudiant']);
$isEdit = !empty($user);
?>

<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
            <li class="breadcrumb-item"><a href="/admin/utilisateurs">Utilisateurs</a></li>
            <li class="breadcrumb-item active"><?= $isEdit ? 'Modifier' : 'Créer' ?> Étudiant</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-user-graduate text-info"></i>
                <?= $isEdit ? 'Modifier un Étudiant' : 'Créer un Étudiant' ?>
            </h1>
            <p class="text-muted mb-0">
                Formulaire spécialisé pour la création/modification des comptes étudiants selon l'architecture RBAC
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
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= htmlspecialchars($action_url) ?>" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="row">
        <!-- Colonne Principale -->
        <div class="col-lg-8">

            <!-- Section Compte Utilisateur -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
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
                                       value="<?= htmlspecialchars($form_data['login_utilisateur'] ?? $user['login_utilisateur'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : 'required' ?>
                                       placeholder="ex: jean.dupont">
                                <?php if (isset($form_errors['login_utilisateur'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['login_utilisateur']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Identifiant unique de connexion</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numero_etudiant" class="form-label required">Numéro étudiant</label>
                                <input type="text"
                                       class="form-control <?= isset($form_errors['numero_etudiant']) ? 'is-invalid' : '' ?>"
                                       id="numero_etudiant"
                                       name="numero_etudiant"
                                       value="<?= htmlspecialchars($form_data['numero_etudiant'] ?? $user['numero_etudiant'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : 'required' ?>
                                       placeholder="ex: 20240001">
                                <?php if (isset($form_errors['numero_etudiant'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['numero_etudiant']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Identifiant unique généré automatiquement</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email_principal" class="form-label required">Email principal</label>
                                <input type="email"
                                       class="form-control <?= isset($form_errors['email_principal']) ? 'is-invalid' : '' ?>"
                                       id="email_principal"
                                       name="email_principal"
                                       value="<?= htmlspecialchars($form_data['email_principal'] ?? $user['email_principal'] ?? '') ?>"
                                       required
                                       placeholder="prenom.nom@email.com">
                                <?php if (isset($form_errors['email_principal'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['email_principal']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label <?= $isEdit ? '' : 'required' ?>">
                                    Mot de passe <?= $isEdit ? '(laisser vide pour ne pas changer)' : '' ?>
                                </label>
                                <input type="password"
                                       class="form-control <?= isset($form_errors['mot_de_passe']) ? 'is-invalid' : '' ?>"
                                       id="mot_de_passe"
                                       name="mot_de_passe"
                                    <?= $isEdit ? '' : 'required' ?>
                                       minlength="8">
                                <?php if (isset($form_errors['mot_de_passe'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['mot_de_passe']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Minimum 8 caractères</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Identité -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-id-card"></i> Identité Personnelle</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label required">Nom de famille</label>
                                <input type="text"
                                       class="form-control <?= isset($form_errors['nom']) ? 'is-invalid' : '' ?>"
                                       id="nom"
                                       name="nom"
                                       value="<?= htmlspecialchars($form_data['nom'] ?? $user['nom'] ?? '') ?>"
                                       required
                                       placeholder="DUPONT">
                                <?php if (isset($form_errors['nom'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['nom']) ?></div>
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
                                       value="<?= htmlspecialchars($form_data['prenom'] ?? $user['prenom'] ?? '') ?>"
                                       required
                                       placeholder="Jean">
                                <?php if (isset($form_errors['prenom'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_naissance"
                                       name="date_naissance"
                                       value="<?= htmlspecialchars($form_data['date_naissance'] ?? $user['date_naissance'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel"
                                       class="form-control"
                                       id="telephone"
                                       name="telephone"
                                       value="<?= htmlspecialchars($form_data['telephone'] ?? $user['telephone'] ?? '') ?>"
                                       placeholder="+33 6 12 34 56 78">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Informations Académiques -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Informations Académiques</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_niveau_etude" class="form-label required">Niveau d'étude</label>
                                <select class="form-select <?= isset($form_errors['id_niveau_etude']) ? 'is-invalid' : '' ?>"
                                        id="id_niveau_etude"
                                        name="id_niveau_etude"
                                        required>
                                    <option value="">Sélectionner un niveau</option>
                                    <?php foreach ($niveaux_etude ?? [] as $niveau): ?>
                                        <option value="<?= htmlspecialchars($niveau['id_niveau_etude']) ?>"
                                            <?= ($form_data['id_niveau_etude'] ?? $user['id_niveau_etude'] ?? '') === $niveau['id_niveau_etude'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($niveau['libelle_niveau_etude'] ?? $niveau['id_niveau_etude']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($form_errors['id_niveau_etude'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($form_errors['id_niveau_etude']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="annee_academique" class="form-label">Année académique</label>
                                <select class="form-select" id="annee_academique" name="annee_academique">
                                    <option value="">Sélectionner une année</option>
                                    <?php foreach ($annees_academiques ?? [] as $annee): ?>
                                        <option value="<?= htmlspecialchars($annee['id_annee_academique']) ?>"
                                            <?= ($form_data['annee_academique'] ?? $user['annee_academique'] ?? '') === $annee['id_annee_academique'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($annee['libelle_annee_academique'] ?? $annee['id_annee_academique']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email_professionnel" class="form-label">Email étudiant (optionnel)</label>
                        <input type="email"
                               class="form-control"
                               id="email_professionnel"
                               name="email_professionnel"
                               value="<?= htmlspecialchars($form_data['email_professionnel'] ?? $user['email_professionnel'] ?? '') ?>"
                               placeholder="prenom.nom@etu.universite.fr">
                        <div class="form-text">Email institutionnel si différent de l'email principal</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Latérale -->
        <div class="col-lg-4">

            <!-- Section RBAC et Permissions -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Contrôle d'Accès (RBAC)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="id_groupe_utilisateur" class="form-label required">Groupe d'utilisateur</label>
                        <select class="form-select <?= isset($form_errors['id_groupe_utilisateur']) ? 'is-invalid' : '' ?>"
                                id="id_groupe_utilisateur"
                                name="id_groupe_utilisateur"
                                required>
                            <option value="">Sélectionner un groupe</option>
                            <?php foreach ($groupes ?? [] as $groupe): ?>
                                <?php
                                // Filtrer pour ne montrer que les groupes étudiants par défaut
                                if (stripos($groupe['id_groupe_utilisateur'], 'ETUDIANT') === false &&
                                    !$isEdit &&
                                    $groupe['id_groupe_utilisateur'] !== 'GRP_ETUDIANT') continue;
                                ?>
                                <option value="<?= htmlspecialchars($groupe['id_groupe_utilisateur']) ?>"
                                    <?= ($form_data['id_groupe_utilisateur'] ?? $user['id_groupe_utilisateur'] ?? 'GRP_ETUDIANT') === $groupe['id_groupe_utilisateur'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($groupe['libelle_groupe_utilisateur'] ?? $groupe['id_groupe_utilisateur']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($form_errors['id_groupe_utilisateur'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($form_errors['id_groupe_utilisateur']) ?></div>
                        <?php endif; ?>
                        <div class="form-text">Détermine les permissions et rôles dans l'application</div>
                    </div>

                    <div class="mb-3">
                        <label for="id_niveau_acces_donne" class="form-label required">Niveau d'accès aux données</label>
                        <select class="form-select <?= isset($form_errors['id_niveau_acces_donne']) ? 'is-invalid' : '' ?>"
                                id="id_niveau_acces_donne"
                                name="id_niveau_acces_donne"
                                required>
                            <option value="">Sélectionner un niveau</option>
                            <?php foreach ($niveauxAcces ?? [] as $niveau): ?>
                                <option value="<?= htmlspecialchars($niveau['id_niveau_acces_donne']) ?>"
                                    <?= ($form_data['id_niveau_acces_donne'] ?? $user['id_niveau_acces_donne'] ?? 'ACCES_PERSONNEL') === $niveau['id_niveau_acces_donne'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($niveau['libelle_niveau_acces_donne'] ?? $niveau['id_niveau_acces_donne']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($form_errors['id_niveau_acces_donne'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($form_errors['id_niveau_acces_donne']) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="mb-3">
                            <label for="statut_compte" class="form-label">Statut du compte</label>
                            <select class="form-select" id="statut_compte" name="statut_compte">
                                <?php
                                $statuts = [
                                    'actif' => 'Actif',
                                    'inactif' => 'Inactif',
                                    'bloque' => 'Bloqué',
                                    'en_attente_validation' => 'En attente de validation',
                                    'archive' => 'Archivé'
                                ];
                                foreach ($statuts as $value => $label):
                                    ?>
                                    <option value="<?= $value ?>"
                                        <?= ($user['statut_compte'] ?? 'en_attente_validation') === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations sur l'Architecture RBAC -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Architecture RBAC</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <p><strong>Type :</strong> TYPE_ETUDIANT</p>
                        <p><strong>Rôle :</strong> Déterminé par le groupe sélectionné</p>
                        <p><strong>Permissions :</strong> Définies via la table "rattacher"</p>
                        <hr>
                        <p class="mb-0">
                            Le système RBAC dissocie les utilisateurs de leurs permissions
                            en utilisant le groupe comme pivot central selon votre
                            architecture documentée.
                        </p>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="/admin/utilisateurs" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>

                <div>
                    <?php if ($isEdit): ?>
                        <button type="button" class="btn btn-warning me-2" onclick="generatePassword()">
                            <i class="fas fa-random"></i> Générer mot de passe
                        </button>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $isEdit ? 'Mettre à jour l\'étudiant' : 'Créer l\'étudiant' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Auto-génération du login basé sur nom/prénom
    document.addEventListener('DOMContentLoaded', function() {
        const nom = document.getElementById('nom');
        const prenom = document.getElementById('prenom');
        const login = document.getElementById('login_utilisateur');

        function generateLogin() {
            if (login.readOnly) return;

            const nomValue = nom.value.toLowerCase().trim();
            const prenomValue = prenom.value.toLowerCase().trim();

            if (nomValue && prenomValue) {
                login.value = prenomValue + '.' + nomValue;
            }
        }

        nom.addEventListener('input', generateLogin);
        prenom.addEventListener('input', generateLogin);
    });

    // Génération de mot de passe aléatoire
    function generatePassword() {
        const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('mot_de_passe').value = password;
        alert('Mot de passe généré : ' + password + '\nN\'oubliez pas de le communiquer à l\'étudiant !');
    }

    // Validation côté client
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

<style>
    .required::after {
        content: " *";
        color: red;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }

    .form-text {
        font-size: 0.875em;
    }

    .card-header.bg-info,
    .card-header.bg-primary,
    .card-header.bg-warning {
        border-bottom: 2px solid rgba(255,255,255,0.2);
    }
</style>