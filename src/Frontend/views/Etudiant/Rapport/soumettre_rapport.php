<!-- src/Frontend/views/Etudiant/Rapport/soumettre_rapport.php -->
<div class="container-fluid">
    <h2><?php echo $page_title; ?></h2>

    <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action_type" id="action_type" value="save_draft"> <!-- Pour distinguer sauvegarde/soumission -->

        <div class="form-group">
            <label for="libelle_rapport_etudiant">Titre du Rapport :</label>
            <input type="text" id="libelle_rapport_etudiant" name="libelle_rapport_etudiant" value="<?php echo htmlspecialchars($rapport['libelle_rapport_etudiant'] ?? ''); ?>" required maxlength="255">
        </div>
        <div class="form-group">
            <label for="theme">Thème du Rapport :</label>
            <input type="text" id="theme" name="theme" value="<?php echo htmlspecialchars($rapport['theme'] ?? ''); ?>" required maxlength="255">
        </div>
        <div class="form-group">
            <label for="resume">Résumé :</label>
            <textarea id="resume" name="resume" rows="5" required minlength="20" maxlength="1000"><?php echo htmlspecialchars($rapport['resume'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="nombre_pages">Nombre de pages :</label>
            <input type="number" id="nombre_pages" name="nombre_pages" value="<?php echo htmlspecialchars($rapport['nombre_pages'] ?? ''); ?>" required min="1">
        </div>
        <div class="form-group">
            <label for="numero_attestation_stage">Numéro d'attestation de stage :</label>
            <input type="text" id="numero_attestation_stage" name="numero_attestation_stage" value="<?php echo htmlspecialchars($rapport['numero_attestation_stage'] ?? ''); ?>" required maxlength="100">
        </div>

        <!-- Sections du rapport (via WYSIWYG) -->
        <fieldset>
            <legend>Contenu du Rapport (Sections)</legend>
            <?php
            // Assumer que $rapport['sections'] est un tableau associatif avec nom_section => contenu
            // Ou itérer sur un tableau ordonné
            $sectionsExistantes = [];
            if (isset($rapport['sections']) && is_array($rapport['sections'])) {
                foreach ($rapport['sections'] as $section) {
                    $sectionsExistantes[$section['nom_section']] = $section['contenu'];
                }
            }
            ?>
            <div class="form-group">
                <label for="section_introduction">Introduction :</label>
                <textarea id="section_introduction" name="section_introduction" class="wysiwyg-editor" rows="10"><?php echo htmlspecialchars($sectionsExistantes['introduction'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="section_corps_rapport">Corps du Rapport :</label>
                <textarea id="section_corps_rapport" name="section_corps_rapport" class="wysiwyg-editor" rows="20"><?php echo htmlspecialchars($sectionsExistantes['corps_rapport'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="section_conclusion">Conclusion :</label>
                <textarea id="section_conclusion" name="section_conclusion" class="wysiwyg-editor" rows="10"><?php echo htmlspecialchars($sectionsExistantes['conclusion'] ?? ''); ?></textarea>
            </div>
            <!-- Intégrer ici un éditeur WYSIWYG comme TinyMCE ou Quill.js -->
            <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
            <script>
                tinymce.init({
                    selector: '.wysiwyg-editor',
                    plugins: 'advlist autolink lists link image charmap print preview anchor',
                    toolbar_mode: 'floating',
                });
            </script>
        </fieldset>

        <button type="submit" name="action_type_btn" value="save_draft" class="btn btn-secondary" onclick="document.getElementById('action_type').value='save_draft';">Sauvegarder Brouillon</button>
        <?php
        // Afficher le bouton de soumission finale si l'étudiant est éligible
        if (isset($is_eligible_for_submission) && $is_eligible_for_submission) :
            ?>
            <button type="submit" name="action_type_btn" value="submit_final" class="btn btn-primary" onclick="document.getElementById('action_type').value='submit_final';">Soumettre Rapport Final</button>
        <?php else : ?>
            <p class="alert alert-warning">Vous n'êtes pas éligible à la soumission finale du rapport. Veuillez vérifier votre inscription, stage et pénalités.</p>
        <?php endif; ?>
    </form>
</div>