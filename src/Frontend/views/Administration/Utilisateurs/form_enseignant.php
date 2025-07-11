<!-- src/Frontend/views/Administration/Utilisateurs/form_enseignant.php -->
<?php
$this->layout('layouts/layout_admin', ['title' => $title ?? 'Formulaire Enseignant']);
$isEdit = !empty($user);
?>

<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
            <li class="breadcrumb-item"><a href="/admin/utilisateurs">Utilisateurs</a></li>
            <li class="breadcrumb-item active"><?= $isEdit ? 'Modifier' : 'Créer' ?> Enseignant</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-chalkboard-teacher text-success"></i>
                <?= $isEdit ? 'Modifier un Enseignant' : 'Créer un Enseignant' ?>
            </h1>
            <p class="text-muted mb-0">
                Formulaire spécialisé pour la gestion des comptes enseignants selon l'architecture RBAC
            </p>
        </div>
        <a href="/admin/utilisateurs" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<form method="POST" action="<?= htmlspecialchars($action_url) ?>" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="row">
        <div class="col-lg-8">

            <!-- Section Compte et Identité -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Informations du Compte</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numero_enseignant" class="form-label required">Numéro enseignant</label>
                                <input type="text"
                                       class="form-control"
                                       id="numero_enseignant"
                                       name="numero_enseignant"
                                       value="<?= htmlspecialchars($form_data['numero_enseignant'] ?? $user['numero_enseignant'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : 'required' ?>
                                       placeholder="ex: ENS-2024-001">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="login_utilisateur" class="form-label required">Login utilisateur</label>
                                <input type="text"
                                       class="form-control"
                                       id="login_utilisateur"
                                       name="login_utilisateur"
                                       value="<?= htmlspecialchars($form_data['login_utilisateur'] ?? $user['login_utilisateur'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : 'required' ?>
                                       placeholder="ex: prenom.nom">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nom" class="form-label required">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom"
                                       value="<?= htmlspecialchars($form_data['nom'] ?? $user['nom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="prenom" class="form-label required">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom"
                                       value="<?= htmlspecialchars($form_data['prenom'] ?? $user['prenom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email_principal" class="form-label required">Email principal</label>
                                <input type="email" class="form-control" id="email_principal" name="email_principal"
                                       value="<?= htmlspecialchars($form_data['email_principal'] ?? $user['email_principal'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <?php if (!$isEdit): ?>
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label required">Mot de passe initial</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required minlength="8">
                            <div class="form-text">Sera communiqué à l'enseignant pour sa première connexion</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section Informations Académiques -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Informations Académiques et Professionnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_grade" class="form-label required">Grade universitaire</label>
                                <select class="form-select" id="id_grade" name="id_grade" required>
                                    <option value="">Sélectionner un grade</option>
                                    <?php foreach ($grades ?? [] as $grade): ?>
                                        <option value="<?= htmlspecialchars($grade['id_grade']) ?>"
                                            <?= ($form_data['id_grade'] ?? $user['id_grade'] ?? '') === $grade['id_grade'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($grade['libelle_grade'] ?? $grade['id_grade']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_specialite" class="form-label required">Spécialité</label>
                                <select class="form-select" id="id_specialite" name="id_specialite" required>
                                    <option value="">Sélectionner une spécialité</option>
                                    <?php foreach ($specialites ?? [] as $specialite): ?>
                                        <option value="<?= htmlspecialchars($specialite['id_specialite']) ?>"
                                            <?= ($form_data['id_specialite'] ?? $user['id_specialite'] ?? '') === $specialite['id_specialite'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($specialite['libelle_specialite'] ?? $specialite['id_specialite']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_prise_fonction" class="form-label">Date de prise de fonction</label>
                                <input type="date" class="form-control" id="date_prise_fonction" name="date_prise_fonction"
                                       value="<?= htmlspecialchars($form_data['date_prise_fonction'] ?? $user['date_prise_fonction'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone professionnel</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone"
                                       value="<?= htmlspecialchars($form_data['telephone'] ?? $user['telephone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email_professionnel" class="form-label">Email professionnel (si différent)</label>
                        <input type="email" class="form-control" id="email_professionnel" name="email_professionnel"
                               value="<?= htmlspecialchars($form_data['email_professionnel'] ?? $user['email_professionnel'] ?? '') ?>"
                               placeholder="prenom.nom@universite.fr">
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Latérale -->
        <div class="col-lg-4">
            <!-- Section RBAC -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Contrôle d'Accès (RBAC)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="id_groupe_utilisateur" class="form-label required">Groupe d'utilisateur</label>
                        <select class="form-select" id="id_groupe_utilisateur" name="id_groupe_utilisateur" required>
                            <option value="">Sélectionner un groupe</option>
                            <?php foreach ($groupes ?? [] as $groupe): ?>
                                <?php
                                // Filtrer pour les groupes enseignants
                                if (stripos($groupe['id_groupe_utilisateur'], 'ENSEIGNANT') === false &&
                                    stripos($groupe['id_groupe_utilisateur'], 'ENS') === false &&
                                    !$isEdit) continue;
                                ?>
                                <option value="<?= htmlspecialchars($groupe['id_groupe_utilisateur']) ?>"
                                    <?= ($form_data['id_groupe_utilisateur'] ?? $user['id_groupe_utilisateur'] ?? 'GRP_ENSEIGNANT') === $groupe['id_groupe_utilisateur'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($groupe['libelle_groupe_utilisateur'] ?? $groupe['id_groupe_utilisateur']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_niveau_acces_donne" class="form-label required">Niveau d'accès</label>
                        <select class="form-select" id="id_niveau_acces_donne" name="id_niveau_acces_donne" required>
                            <option value="">Sélectionner un niveau</option>
                            <?php foreach ($niveauxAcces ?? [] as $niveau): ?>
                                <option value="<?= htmlspecialchars($niveau['id_niveau_acces_donne']) ?>"
                                    <?= ($form_data['id_niveau_acces_donne'] ?? $user['id_niveau_acces_donne'] ?? 'ACCES_DEPARTEMENT') === $niveau['id_niveau_acces_donne'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($niveau['libelle_niveau_acces_donne'] ?? $niveau['id_niveau_acces_donne']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="mb-3">
                            <label for="statut_compte" class="form-label">Statut du compte</label>
                            <select class="form-select" id="statut_compte" name="statut_compte">
                                <option value="actif" <?= ($user['statut_compte'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="inactif" <?= ($user['statut_compte'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                <option value="bloque" <?= ($user['statut_compte'] ?? '') === 'bloque' ? 'selected' : '' ?>>Bloqué</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Rôles et Responsabilités</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <p><strong>Type :</strong> TYPE_ENS (Enseignant)</p>
                        <p><strong>Permissions :</strong> Définies par le groupe sélectionné</p>
                        <p><strong>Accès typiques :</strong></p>
                        <ul class="small">
                            <li>Validation des rapports (si membre commission)</li>
                            <li>Gestion pédagogique</li>
                            <li>Consultation données étudiants</li>
                        </ul>
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

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    <?= $isEdit ? 'Mettre à jour l\'enseignant' : 'Créer l\'enseignant' ?>
                </button>
            </div>
        </div>
    </div>
</form>