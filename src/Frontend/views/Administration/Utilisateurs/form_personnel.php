<!-- src/Frontend/views/Administration/Utilisateurs/form_personnel.php -->
<?php
$isEdit = !empty($user);
?>

<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
            <li class="breadcrumb-item"><a href="/admin/utilisateurs">Utilisateurs</a></li>
            <li class="breadcrumb-item active"><?= $isEdit ? 'Modifier' : 'Créer' ?> Personnel</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-user-tie text-warning"></i>
                <?= $isEdit ? 'Modifier un Personnel Administratif' : 'Créer un Personnel Administratif' ?>
            </h1>
            <p class="text-muted mb-0">
                Formulaire pour la gestion des comptes du personnel administratif selon l'architecture RBAC
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

            <!-- Section Compte -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Informations du Compte</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numero_personnel" class="form-label required">Numéro personnel</label>
                                <input type="text"
                                       class="form-control"
                                       id="numero_personnel"
                                       name="numero_personnel"
                                       value="<?= htmlspecialchars($form_data['numero_personnel'] ?? $user['numero_personnel'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : 'required' ?>
                                       placeholder="ex: PERS-2024-001">
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
                                    <?= $isEdit ? 'readonly' : 'required' ?>>
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
                </div>
            </div>

            <!-- Section Fonction et Responsabilités -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-briefcase"></i> Fonction et Responsabilités</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_fonction" class="form-label required">Fonction</label>
                                <select class="form-select" id="id_fonction" name="id_fonction" required>
                                    <option value="">Sélectionner une fonction</option>
                                    <?php foreach ($fonctions ?? [] as $fonction): ?>
                                        <option value="<?= htmlspecialchars($fonction['id_fonction']) ?>"
                                            <?= ($form_data['id_fonction'] ?? $user['id_fonction'] ?? '') === $fonction['id_fonction'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($fonction['libelle_fonction'] ?? $fonction['id_fonction']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_embauche" class="form-label">Date d'embauche</label>
                                <input type="date" class="form-control" id="date_embauche" name="date_embauche"
                                       value="<?= htmlspecialchars($form_data['date_embauche'] ?? $user['date_embauche'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone professionnel</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone"
                                       value="<?= htmlspecialchars($form_data['telephone'] ?? $user['telephone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email_professionnel" class="form-label">Email professionnel</label>
                                <input type="email" class="form-control" id="email_professionnel" name="email_professionnel"
                                       value="<?= htmlspecialchars($form_data['email_professionnel'] ?? $user['email_professionnel'] ?? '') ?>">
                            </div>
                        </div>
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
                        <label for="id_groupe_utilisateur" class="form-label required">Groupe/Rôle</label>
                        <select class="form-select" id="id_groupe_utilisateur" name="id_groupe_utilisateur" required>
                            <option value="">Sélectionner un rôle</option>

                            <!-- Groupes spécifiques au personnel administratif -->
                            <optgroup label="Personnel Administratif">
                                <?php
                                $groupesPersonnel = [
                                    'GRP_RS' => 'Responsable Scolarité',
                                    'GRP_AGENT_CONFORMITE' => 'Agent de Conformité',
                                    'GRP_PERS_ADMIN' => 'Personnel Administratif',
                                    'GRP_GESTIONNAIRE_SCOL' => 'Gestionnaire Scolarité'
                                ];

                                foreach ($groupesPersonnel as $id => $label): ?>
                                    <option value="<?= $id ?>"
                                        <?= ($form_data['id_groupe_utilisateur'] ?? $user['id_groupe_utilisateur'] ?? '') === $id ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <?php if ($isEdit): ?>
                                <optgroup label="Autres Groupes">
                                    <?php foreach ($groupes ?? [] as $groupe): ?>
                                        <?php if (!array_key_exists($groupe['id_groupe_utilisateur'], $groupesPersonnel)): ?>
                                            <option value="<?= htmlspecialchars($groupe['id_groupe_utilisateur']) ?>"
                                                <?= ($form_data['id_groupe_utilisateur'] ?? $user['id_groupe_utilisateur'] ?? '') === $groupe['id_groupe_utilisateur'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($groupe['libelle_groupe_utilisateur'] ?? $groupe['id_groupe_utilisateur']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Détermine les responsabilités et permissions dans l'application</div>
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
                </div>
            </div>

            <!-- Responsabilités selon le rôle -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-tasks"></i> Responsabilités Typiques</h6>
                </div>
                <div class="card-body">
                    <div id="role-responsibilities">
                        <small class="text-muted">
                            Sélectionnez un groupe pour voir les responsabilités associées.
                        </small>
                    </div>
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

                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i>
                    <?= $isEdit ? 'Mettre à jour le personnel' : 'Créer le personnel' ?>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // Affichage des responsabilités selon le groupe sélectionné
    document.getElementById('id_groupe_utilisateur').addEventListener('change', function() {
        const responsibilitiesDiv = document.getElementById('role-responsibilities');
        const selectedGroup = this.value;

        const responsibilities = {
            'GRP_RS': `
            <strong>Responsable Scolarité :</strong>
            <ul class="small">
                <li>Activation des comptes étudiants</li>
                <li>Gestion des inscriptions</li>
                <li>Validation administrative</li>
                <li>Gestion des pénalités</li>
            </ul>
        `,
            'GRP_AGENT_CONFORMITE': `
            <strong>Agent de Conformité :</strong>
            <ul class="small">
                <li>Vérification conformité des rapports</li>
                <li>Contrôle qualité documentaire</li>
                <li>Première validation technique</li>
            </ul>
        `,
            'GRP_PERS_ADMIN': `
            <strong>Personnel Administratif :</strong>
            <ul class="small">
                <li>Gestion administrative courante</li>
                <li>Support aux étudiants</li>
                <li>Traitement des dossiers</li>
            </ul>
        `,
            'GRP_GESTIONNAIRE_SCOL': `
            <strong>Gestionnaire Scolarité :</strong>
            <ul class="small">
                <li>Suivi académique</li>
                <li>Gestion des notes</li>
                <li>Édition de documents officiels</li>
            </ul>
        `
        };

        responsibilitiesDiv.innerHTML = responsibilities[selectedGroup] ||
            '<small class="text-muted">Groupe sélectionné sans responsabilités définies.</small>';
    });

    // Déclencher l'affichage initial si un groupe est déjà sélectionné
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('id_groupe_utilisateur').dispatchEvent(new Event('change'));
    });
</script>