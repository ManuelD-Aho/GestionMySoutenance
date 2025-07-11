<!-- src/Frontend/views/Administration/Utilisateurs/liste_utilisateurs.php -->
<?php
$this->layout('layouts/app', ['title' => $title ?? 'Gestion des Utilisateurs']);

// Helper pour afficher les statuts
function getStatusBadge($statut) {
    $badges = [
        'actif' => 'success',
        'inactif' => 'secondary',
        'bloque' => 'danger',
        'en_attente_validation' => 'warning',
        'archive' => 'dark'
    ];
    $class = $badges[$statut] ?? 'secondary';
    return "<span class=\"badge bg-{$class}\">" . ucfirst($statut) . "</span>";
}
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-users text-primary"></i> <?= htmlspecialchars($title) ?></h1>
        <p class="text-muted mb-0">Gestion centralisée des comptes utilisateurs selon l'architecture RBAC</p>
    </div>

    <!-- Menu Actions Principales -->
    <div class="btn-group">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-plus"></i> Créer Utilisateur
        </button>
        <ul class="dropdown-menu">
            <li><h6 class="dropdown-header">Types d'Utilisateurs</h6></li>
            <li><a class="dropdown-item" href="/admin/utilisateurs/etudiant/form">
                    <i class="fas fa-user-graduate text-info"></i> Étudiant
                </a></li>
            <li><a class="dropdown-item" href="/admin/utilisateurs/enseignant/form">
                    <i class="fas fa-chalkboard-teacher text-success"></i> Enseignant
                </a></li>
            <li><a class="dropdown-item" href="/admin/utilisateurs/personnel/form">
                    <i class="fas fa-user-tie text-warning"></i> Personnel Administratif
                </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/admin/utilisateurs/form">
                    <i class="fas fa-user-edit text-secondary"></i> Formulaire Générique
                </a></li>
        </ul>
    </div>
</div>

<!-- Statistiques Rapides -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['id_type_utilisateur'] ?? '') === 'TYPE_ETUD')) ?></h5>
                <p class="card-text">Étudiants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['id_type_utilisateur'] ?? '') === 'TYPE_ENS')) ?></h5>
                <p class="card-text">Enseignants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['id_type_utilisateur'] ?? '') === 'TYPE_PERS_ADMIN')) ?></h5>
                <p class="card-text">Personnel Admin</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-user-shield fa-2x text-primary mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['id_type_utilisateur'] ?? '') === 'TYPE_ADMIN')) ?></h5>
                <p class="card-text">Administrateurs</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtres et Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtres et Actions</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?= htmlspecialchars($current_filters['search'] ?? '') ?>"
                       placeholder="Nom, prénom, login, email...">
            </div>

            <div class="col-md-2">
                <label for="groupe" class="form-label">Groupe</label>
                <select class="form-select" id="groupe" name="groupe">
                    <option value="">Tous les groupes</option>
                    <?php foreach ($groupes ?? [] as $groupe): ?>
                        <option value="<?= htmlspecialchars($groupe['id_groupe_utilisateur']) ?>"
                            <?= ($current_filters['groupe'] ?? '') === $groupe['id_groupe_utilisateur'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($groupe['libelle_groupe_utilisateur'] ?? $groupe['id_groupe_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statuts ?? [] as $statut): ?>
                        <option value="<?= $statut ?>" <?= ($current_filters['statut'] ?? '') === $statut ? 'selected' : '' ?>>
                            <?= ucfirst($statut) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Tous les types</option>
                    <?php foreach ($types ?? [] as $type): ?>
                        <option value="<?= htmlspecialchars($type['id_type_utilisateur']) ?>"
                            <?= ($current_filters['type'] ?? '') === $type['id_type_utilisateur'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['libelle_type_utilisateur'] ?? $type['id_type_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                <a href="/admin/utilisateurs" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
                <a href="/admin/utilisateurs/import-etudiants" class="btn btn-info">
                    <i class="fas fa-upload"></i> Import
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des Utilisateurs -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Utilisateurs (<?= count($users) ?> résultats)</h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" onclick="exportUsers('csv')">
                <i class="fas fa-file-csv"></i> CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="exportUsers('excel')">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Utilisateur</th>
                    <th>Type</th>
                    <th>Groupe</th>
                    <th>Statut</th>
                    <th>Connexion</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                            Aucun utilisateur trouvé avec ces critères
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <?= strtoupper(substr($user['prenom'] ?? '', 0, 1) . substr($user['nom'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($user['login_utilisateur'] ?? '') ?></small>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($user['email_principal'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $typeIcons = [
                                    'TYPE_ETUD' => 'fas fa-user-graduate text-info',
                                    'TYPE_ENS' => 'fas fa-chalkboard-teacher text-success',
                                    'TYPE_PERS_ADMIN' => 'fas fa-user-tie text-warning',
                                    'TYPE_ADMIN' => 'fas fa-user-shield text-primary'
                                ];
                                $iconClass = $typeIcons[$user['id_type_utilisateur'] ?? ''] ?? 'fas fa-user text-secondary';
                                ?>
                                <i class="<?= $iconClass ?>"></i>
                                <?= htmlspecialchars($user['libelle_type_utilisateur'] ?? $user['id_type_utilisateur'] ?? '') ?>
                            </td>
                            <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($user['id_groupe_utilisateur'] ?? '') ?>
                                    </span>
                            </td>
                            <td><?= getStatusBadge($user['statut_compte'] ?? 'inactif') ?></td>
                            <td>
                                <?php if (!empty($user['derniere_connexion'])): ?>
                                    <small><?= date('d/m/Y H:i', strtotime($user['derniere_connexion'])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Jamais connecté</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/edit"
                                       class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown" title="Plus d'actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" action="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/actions" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                    <input type="hidden" name="action" value="reset_password">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-key text-warning"></i> Réinitialiser MDP
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/actions" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                    <input type="hidden" name="action" value="impersonate">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-user-secret text-info"></i> Impersonnaliser
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/actions" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function exportUsers(format) {
        const url = `/admin/utilisateurs/export/${format}`;
        const params = new URLSearchParams(window.location.search);
        window.location.href = url + '?' + params.toString();
    }
</script>