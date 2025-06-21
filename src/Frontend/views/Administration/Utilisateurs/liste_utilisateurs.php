<!-- src/Frontend/views/Administration/Utilisateurs/liste_utilisateurs.php -->
<div class="container-fluid">
    <h2><?php echo $page_title; ?></h2>
    <div class="action-bar">
        <a href="/dashboard/admin/utilisateurs/create/etudiant" class="btn btn-success">Ajouter Étudiant</a>
        <a href="/dashboard/admin/utilisateurs/create/enseignant" class="btn btn-success">Ajouter Enseignant</a>
        <a href="/dashboard/admin/utilisateurs/create/personnel" class="btn btn-success">Ajouter Personnel</a>
        <a href="/dashboard/admin/utilisateurs/create/admin" class="btn btn-success">Ajouter Admin</a>
        <a href="/dashboard/admin/utilisateurs/import-students" class="btn btn-info">Importer Étudiants</a>
    </div>

    <?php
    // Affichage des erreurs d'importation si elles existent
    if (isset($_SESSION['import_errors']) && !empty($_SESSION['import_errors'])) {
        echo '<div class="alert alert-error"><h3>Erreurs d\'importation :</h3><ul>';
        foreach ($_SESSION['import_errors'] as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
        unset($_SESSION['import_errors']); // Effacer après affichage
    }
    ?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Numéro</th>
            <th>Login</th>
            <th>Email Principal</th>
            <th>Nom Complet</th>
            <th>Type</th>
            <th>Groupe</th>
            <th>Statut Compte</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($utilisateurs)) : ?>
            <?php foreach ($utilisateurs as $user) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['numero_utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($user['login_utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($user['email_principal']); ?></td>
                    <td><?php echo htmlspecialchars(($user['profil']['prenom'] ?? '') . ' ' . ($user['profil']['nom'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($user['id_type_utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($user['id_groupe_utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($user['statut_compte']); ?></td>
                    <td>
                        <a href="/dashboard/admin/utilisateurs/<?php echo htmlspecialchars($user['numero_utilisateur']); ?>/edit" class="btn btn-primary btn-sm">Modifier</a>
                        <form action="/dashboard/admin/utilisateurs/<?php echo htmlspecialchars($user['numero_utilisateur']); ?>/delete" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                        <form action="/dashboard/admin/utilisateurs/<?php echo htmlspecialchars($user['numero_utilisateur']); ?>/change-status" method="POST" style="display:inline;">
                            <select name="new_status" onchange="this.form.submit()">
                                <option value="">Changer statut</option>
                                <option value="actif" <?php echo ($user['statut_compte'] == 'actif' ? 'selected' : ''); ?>>Actif</option>
                                <option value="inactif" <?php echo ($user['statut_compte'] == 'inactif' ? 'selected' : ''); ?>>Inactif</option>
                                <option value="bloque" <?php echo ($user['statut_compte'] == 'bloque' ? 'selected' : ''); ?>>Bloqué</option>
                                <option value="archive" <?php echo ($user['statut_compte'] == 'archive' ? 'selected' : ''); ?>>Archivé</option>
                            </select>
                        </form>
                        <form action="/dashboard/admin/utilisateurs/<?php echo htmlspecialchars($user['numero_utilisateur']); ?>/reset-password" method="POST" style="display:inline;" onsubmit="return prompt('Entrez le nouveau mot de passe :');">
                            <button type="submit" class="btn btn-warning btn-sm">Reset MDP</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="8">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <!-- Ajout de pagination si nécessaire -->
    <?php
    /*
    if (isset($total_items) && $total_items > $items_per_page) {
        $totalPages = ceil($total_items / $items_per_page);
        echo '<div class="pagination">';
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="/dashboard/admin/utilisateurs?page=' . $i . '" class="' . ($i == $current_page ? 'active' : '') . '">' . $i . '</a>';
        }
        echo '</div>';
    }
    */
    ?>
</div>