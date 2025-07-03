<?php
// Vue : Formulaire de création/édition d'un utilisateur
// Données attendues : $utilisateur (si édition), $roles
?>
<div class="container p-md">
    <h1 class="text-2xl font-bold mb-lg"><?= isset($utilisateur) ? 'Modifier' : 'Créer' ?> un Utilisateur</h1>

    <form class="card shadow-xl p-lg" method="POST" action="<?= isset($utilisateur) ? '/admin/utilisateurs/update/' . $utilisateur['id'] : '/admin/utilisateurs/store' ?>">
        <div class="grid md:grid-cols-2 gap-md mb-md">
            <div class="form-group">
                <label for="nom" class="font-semibold">Nom</label>
                <input type="text" id="nom" name="nom" class="form-input w-full mt-sm" value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom" class="font-semibold">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="form-input w-full mt-sm" value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-md mb-md">
            <div class="form-group">
                <label for="email" class="font-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-input w-full mt-sm" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="role" class="font-semibold">Rôle</label>
                <select id="role" name="id_type_utilisateur" class="form-select w-full mt-sm" required>
                    <option value="">Sélectionner un rôle</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= (isset($utilisateur) && $utilisateur['id_type_utilisateur'] == $role['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group mb-lg">
            <label for="password" class="font-semibold">Mot de passe</label>
            <input type="password" id="password" name="password" class="form-input w-full mt-sm" <?= isset($utilisateur) ? '' : 'required' ?>>
            <?php if (isset($utilisateur)): ?>
                <small class="text-light">Laissez vide pour ne pas changer.</small>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <a href="/admin/utilisateurs" class="btn btn-secondary mr-md">Annuler</a>
            <button type="submit" class="btn btn-primary"><?= isset($utilisateur) ? 'Mettre à jour' : 'Enregistrer' ?></button>
        </div>
    </form>
</div>

<style>
    /* Améliorations CSS pour _forms.css */
    .form-input, .form-select {
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
    }
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    .btn {
        padding: var(--spacing-sm) var(--spacing-lg);
        border-radius: var(--border-radius-md);
        font-weight: var(--font-weight-semibold);
        cursor: pointer;
        transition: background-color var(--transition-fast);
    }
    .btn-primary {
        background-color: var(--primary-blue);
        color: var(--text-white);
        border: none;
    }
    .btn-primary:hover {
        background-color: var(--primary-blue-dark);
    }
    .btn-secondary {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-medium);
    }
    .btn-secondary:hover {
        background-color: var(--border-medium);
    }
</style>