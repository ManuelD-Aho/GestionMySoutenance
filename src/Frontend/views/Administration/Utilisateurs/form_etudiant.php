<!-- src/Frontend/views/Administration/Utilisateurs/form_etudiant.php -->
<div class="container-fluid">
    <h2><?php echo $page_title; ?></h2>

    <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data">
        <!-- Informations du compte utilisateur (génériques) -->
        <fieldset>
            <legend>Informations du Compte</legend>
            <div class="form-group">
                <label for="login_utilisateur">Login :</label>
                <input type="text" id="login_utilisateur" name="login_utilisateur" value="<?php echo htmlspecialchars($utilisateur['login_utilisateur'] ?? ''); ?>" required minlength="3" maxlength="100">
            </div>
            <div class="form-group">
                <label for="email_principal">Email Principal :</label>
                <input type="email" id="email_principal" name="email_principal" value="<?php echo htmlspecialchars($utilisateur['email_principal'] ?? ''); ?>" required maxlength="255">
            </div>
            <?php if (!isset($utilisateur)) : // Seulement à la création ?>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_mot_de_passe">Confirmer mot de passe :</label>
                    <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required minlength="8">
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="id_niveau_acces_donne">Niveau d'Accès aux Données :</label>
                <select id="id_niveau_acces_donne" name="id_niveau_acces_donne" required>
                    <?php foreach ($niveaux_acces_ref as $niveau) : ?>
                        <option value="<?php echo htmlspecialchars($niveau['id_niveau_acces_donne']); ?>"
                            <?php echo (isset($utilisateur['id_niveau_acces_donne']) && $utilisateur['id_niveau_acces_donne'] == $niveau['id_niveau_acces_donne']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($niveau['libelle_niveau_acces_donne']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_groupe_utilisateur">Groupe d'Utilisateur :</label>
                <select id="id_groupe_utilisateur" name="id_groupe_utilisateur" required>
                    <?php foreach ($groupes_utilisateur_ref as $groupe) : ?>
                        <option value="<?php echo htmlspecialchars($groupe['id_groupe_utilisateur']); ?>"
                            <?php echo (isset($utilisateur['id_groupe_utilisateur']) && $utilisateur['id_groupe_utilisateur'] == $groupe['id_groupe_utilisateur']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($groupe['libelle_groupe_utilisateur']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="photo_profil">Photo de Profil :</label>
                <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                <?php if (isset($utilisateur['photo_profil']) && $utilisateur['photo_profil']) : ?>
                    <img src="<?php echo htmlspecialchars($utilisateur['photo_profil']); ?>" alt="Photo de profil" style="width: 100px; height: 100px; border-radius: 50%;">
                <?php endif; ?>
            </div>
        </fieldset>

        <!-- Informations spécifiques à l'ÉTUDIANT -->
        <fieldset>
            <legend>Informations Étudiant</legend>
            <div class="form-group">
                <label for="numero_carte_etudiant">Numéro Carte Étudiant :</label>
                <input type="text" id="numero_carte_etudiant" name="numero_carte_etudiant" value="<?php echo htmlspecialchars($utilisateur['profil']['numero_carte_etudiant'] ?? ''); ?>" required maxlength="50" <?php echo isset($utilisateur) ? 'readonly' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['profil']['nom'] ?? ''); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($utilisateur['profil']['prenom'] ?? ''); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de Naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($utilisateur['profil']['date_naissance'] ?? ''); ?>">
            </div>
            <!-- Ajoutez d'autres champs spécifiques à l'étudiant -->
        </fieldset>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>