<?php
// Protection pour s'assurer que la vue est appelée par le contrôleur
if (!isset($title)) {
    header("Location: /");
    exit();
}
?>

    <h1><?= htmlspecialchars($title) ?></h1>
    <p>Liste de tous les utilisateurs enregistrés dans le système.</p>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Login</th>
                <th>Email Principal</th>
                <th>Nom du Profil</th>
                <th>Type</th>
                <th>Groupe</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($utilisateurs)): ?>
                <tr>
                    <td colspan="7" class="text-center">Aucun utilisateur trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?= htmlspecialchars($utilisateur->login_utilisateur) ?></td>
                        <td><?= htmlspecialchars($utilisateur->email_principal) ?></td>
                        <td><?= htmlspecialchars(($utilisateur->nom_profil ?? '') . ' ' . ($utilisateur->prenom_profil ?? '')) ?></td>
                        <td><?= htmlspecialchars($utilisateur->libelle_type_utilisateur) ?></td>
                        <td><?= htmlspecialchars($utilisateur->libelle_groupe_utilisateur) ?></td>
                        <td>
                            <span class="badge bg-<?= $utilisateur->statut_compte === 'actif' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($utilisateur->statut_compte) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/admin/utilisateurs/voir/<?= htmlspecialchars($utilisateur->numero_utilisateur) ?>" class="btn btn-sm btn-info">Voir</a>
                            <a href="/admin/utilisateurs/modifier/<?= htmlspecialchars($utilisateur->numero_utilisateur) ?>" class="btn btn-sm btn-warning">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page_actuelle) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>