<!-- src/Frontend/views/Administration/Utilisateurs/liste_utilisateurs.php -->
<?php
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
    $label = ucfirst(str_replace('_', ' ', $statut));
    return "<span class=\"badge bg-{$class}\">{$label}</span>";
}

// Helper pour déterminer le type d'utilisateur
function getUserTypeInfo($user) {
    $types = [
        'etudiant' => ['label' => 'Étudiant', 'icon' => 'fa-user-graduate', 'color' => 'info'],
        'enseignant' => ['label' => 'Enseignant', 'icon' => 'fa-chalkboard-teacher', 'color' => 'success'],
        'personnel' => ['label' => 'Personnel', 'icon' => 'fa-user-tie', 'color' => 'warning'],
        'admin' => ['label' => 'Administrateur', 'icon' => 'fa-user-shield', 'color' => 'danger']
    ];

    $userType = $user['type_entite'] ?? 'admin';
    return $types[$userType] ?? $types['admin'];
}
?>

<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
            <li class="breadcrumb-item active">Gestion des Utilisateurs</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-users text-primary"></i> <?= htmlspecialchars($title ?? 'Gestion des Utilisateurs') ?></h1>
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
</div>

<!-- Statistiques Rapides -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['type_entite'] ?? '') === 'etudiant')) ?></h5>
                <p class="card-text">Étudiants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['type_entite'] ?? '') === 'enseignant')) ?></h5>
                <p class="card-text">Enseignants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['type_entite'] ?? '') === 'personnel')) ?></h5>
                <p class="card-text">Personnel</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-user-shield fa-2x text-danger mb-2"></i>
                <h5 class="card-title"><?= count(array_filter($users, fn($u) => ($u['statut_compte'] ?? '') === 'actif')) ?></h5>
                <p class="card-text">Comptes Actifs</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtres et Recherche -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-filter"></i> Filtres et Recherche</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="/admin/utilisateurs" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text"
                       class="form-control"
                       id="search"
                       name="search"
                       value="<?= htmlspecialchars($current_filters['search'] ?? '') ?>"
                       placeholder="Nom, prénom, login, email...">
            </div>

            <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-control" id="statut" name="statut">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statuts ?? [] as $statut): ?>
                        <option value="<?= htmlspecialchars($statut) ?>"
                            <?= ($current_filters['statut'] ?? '') === $statut ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $statut)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="groupe" class="form-label">Groupe</label>
                <select class="form-control" id="groupe" name="groupe">
                    <option value="">Tous les groupes</option>
                    <?php foreach ($groupes ?? [] as $groupe): ?>
                        <option value="<?= htmlspecialchars($groupe['id_groupe_utilisateur']) ?>"
                            <?= ($current_filters['groupe'] ?? '') === $groupe['id_groupe_utilisateur'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($groupe['nom_groupe'] ?? $groupe['id_groupe_utilisateur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-control" id="type" name="type">
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

<!-- Actions en Lot -->
<form id="bulkActionForm" method="POST" action="/admin/utilisateurs/bulk-actions">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <!-- Tableau des Utilisateurs -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des Utilisateurs (<?= count($users) ?> résultats)</h5>
            <div class="d-flex gap-2">
                <!-- Actions en lot -->
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" id="bulkActionsBtn" disabled>
                        <i class="fas fa-cogs"></i> Actions en lot
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction('activate')">
                                <i class="fas fa-check text-success"></i> Activer les comptes
                            </button></li>
                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction('deactivate')">
                                <i class="fas fa-times text-warning"></i> Désactiver les comptes
                            </button></li>
                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction('block')">
                                <i class="fas fa-ban text-danger"></i> Bloquer les comptes
                            </button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction('reset_password')">
                                <i class="fas fa-key text-info"></i> Réinitialiser mots de passe
                            </button></li>
                    </ul>
                </div>

                <!-- Export -->
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportUsers('csv')">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="exportUsers('excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Utilisateur</th>
                        <th>Type</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                        <th>Dernière Connexion</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                Aucun utilisateur trouvé avec ces critères
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php $typeInfo = getUserTypeInfo($user); ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input user-checkbox"
                                               type="checkbox"
                                               name="user_ids[]"
                                               value="<?= htmlspecialchars($user['numero_utilisateur']) ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-<?= $typeInfo['color'] ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? 'X', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">
                                                <a href="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/detail" class="text-decoration-none">
                                                    <?= htmlspecialchars($user['prenom'] ?? 'N/A') ?> <?= htmlspecialchars($user['nom'] ?? 'N/A') ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($user['login'] ?? 'N/A') ?> •
                                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas <?= $typeInfo['icon'] ?> text-<?= $typeInfo['color'] ?>"></i>
                                    <?= $typeInfo['label'] ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($user['nom_groupe'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <?= getStatusBadge($user['statut_compte'] ?? 'inactif') ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php if (!empty($user['derniere_connexion'])): ?>
                                            <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($user['derniere_connexion'])) ?>
                                        <?php else: ?>
                                            <span class="text-warning">Jamais connecté</span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/detail"
                                           class="btn btn-outline-info"
                                           title="Voir le détail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/edit"
                                           class="btn btn-outline-primary"
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown"
                                                title="Plus d'actions">
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="resetPassword('<?= htmlspecialchars($user['numero_utilisateur']) ?>')">
                                                    <i class="fas fa-key text-warning"></i> Réinitialiser mot de passe
                                                </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="toggleStatus('<?= htmlspecialchars($user['numero_utilisateur']) ?>', '<?= $user['statut_compte'] ?>')">
                                                    <i class="fas fa-power-off text-info"></i>
                                                    <?= $user['statut_compte'] === 'actif' ? 'Désactiver' : 'Activer' ?> le compte
                                                </a></li>
                                            <li><a class="dropdown-item" href="/admin/utilisateurs/<?= htmlspecialchars($user['numero_utilisateur']) ?>/impersonate">
                                                    <i class="fas fa-user-secret text-secondary"></i> Se connecter en tant que
                                                </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('<?= htmlspecialchars($user['numero_utilisateur']) ?>')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a></li>
                                        </ul>
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
</form>

<!-- Pagination (si nécessaire) -->
<?php if (!empty($pagination)): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?= $pagination ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la sélection en lot
        const selectAllCheckbox = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActionsBtn = document.getElementById('bulkActionsBtn');

        // Sélection/déselection en lot
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsButton();
        });

        // Mise à jour du bouton d'actions en lot
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsButton);
        });

        function updateBulkActionsButton() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            bulkActionsBtn.disabled = checkedBoxes.length === 0;

            // Mettre à jour l'état du selectAll
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedBoxes.length === userCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
    });

    function submitBulkAction(action) {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Veuillez sélectionner au moins un utilisateur.');
            return;
        }

        const actionNames = {
            'activate': 'activer',
            'deactivate': 'désactiver',
            'block': 'bloquer',
            'reset_password': 'réinitialiser le mot de passe de'
        };

        const actionName = actionNames[action] || action;
        if (confirm(`Êtes-vous sûr de vouloir ${actionName} ${checkedBoxes.length} utilisateur(s) ?`)) {
            // Ajouter un champ hidden pour l'action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;

            const form = document.getElementById('bulkActionForm');
            form.appendChild(actionInput);
            form.submit();
        }
    }

    function resetPassword(userId) {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')) {
            fetch(`/admin/utilisateurs/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Le mot de passe a été réinitialisé. Un email a été envoyé à l\'utilisateur.');
                    } else {
                        alert('Erreur lors de la réinitialisation : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur de connexion');
                });
        }
    }

    function toggleStatus(userId, currentStatus) {
        const newStatus = currentStatus === 'actif' ? 'inactif' : 'actif';
        const action = newStatus === 'actif' ? 'activer' : 'désactiver';

        if (confirm(`Êtes-vous sûr de vouloir ${action} ce compte ?`)) {
            fetch(`/admin/utilisateurs/${userId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
                },
                body: JSON.stringify({ status: newStatus })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur lors du changement de statut : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur de connexion');
                });
        }
    }

    function confirmDelete(userId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/utilisateurs/${userId}/delete`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = 'csrf_token';
            csrfToken.value = document.querySelector('[name="csrf_token"]').value;

            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function exportUsers(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('export', format);
        window.location.href = `/admin/utilisateurs/export?${params.toString()}`;
    }
</script>