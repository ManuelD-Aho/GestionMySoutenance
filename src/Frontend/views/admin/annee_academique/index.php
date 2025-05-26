<?php
// src/Frontend/views/admin/annee_academique/index.php
/**
 * @var array $annees
 * @var string $pageTitle
 * @var string $userRole
 * @var array $currentUser
 * @var array $menuItems
 */

// This file will be included as $contentView into layout/app.php by the render method.
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo htmlspecialchars($pageTitle ?? 'Années Académiques'); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Tableau de Bord</a></li>
        <li class="breadcrumb-item active">Gestion des Années Académiques</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Liste des Années Académiques
            <a href="/admin/annees-academiques/create" class="btn btn-primary btn-sm float-end">Ajouter Nouveau</a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Libellé</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($annees)): ?>
                        <?php foreach ($annees as $annee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($annee['id_annee_academique']); ?></td>
                                <td><?php echo htmlspecialchars($annee['lib_annee_academique']); ?></td>
                                <td><?php echo htmlspecialchars($annee['date_debut'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($annee['date_fin'] ?? 'N/A'); ?></td>
                                <td><?php echo $annee['est_active'] ? 'Oui' : 'Non'; ?></td>
                                <td>
                                    <a href="/admin/annees-academiques/edit/<?php echo $annee['id_annee_academique']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                    <a href="/admin/annees-academiques/delete/<?php echo $annee['id_annee_academique']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Aucune année académique trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
