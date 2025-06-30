<?php
// src/Frontend/views/Administration/Fichier/upload_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Assumons que le contrôleur FichierController gère l'upload
//
?>

<div class="admin-module-container">
    <h1 class="admin-title">Télécharger un Fichier</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Formulaire de Téléchargement</h2>
        <form id="uploadForm" action="/admin/fichiers/upload" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fichier_a_uploader">Sélectionner un fichier :</label>
                <input type="file" id="fichier_a_uploader" name="fichier_a_uploader" required>
                <small class="form-help">Taille maximale : 10 Mo. Formats acceptés : PDF, DOCX, JPG, PNG.</small>
            </div>
            <div class="form-group">
                <label for="description_fichier">Description (facultatif) :</label>
                <textarea id="description_fichier" name="description_fichier" rows="4" placeholder="Description du contenu du fichier..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary-blue" id="uploadButton">
                <span class="material-icons">cloud_upload</span>
                Lancer le Téléchargement
            </button>
        </form>

        <div id="uploadProgressContainer" class="progress-container mt-lg" style="display: none;">
            <div class="progress-bar" id="uploadProgressBar"></div>
            <div class="progress-text" id="uploadProgressText">0%</div>
        </div>

        <div id="uploadStatus" class="mt-lg"></div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadForm = document.getElementById('uploadForm');
        const uploadButton = document.getElementById('uploadButton');
        const uploadProgressContainer = document.getElementById('uploadProgressContainer');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadProgressText = document.getElementById('uploadProgressText');
        const uploadStatus = document.getElementById('uploadStatus');

        if (uploadForm) {
            uploadForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Empêche la soumission de formulaire par défaut

                const fileInput = document.getElementById('fichier_a_uploader');
                if (fileInput.files.length === 0) {
                    alert('Veuillez sélectionner un fichier à télécharger.');
                    return;
                }

                const formData = new FormData(uploadForm);
                const xhr = new XMLHttpRequest();

                // Gestion de la progression de l'upload
                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        uploadProgressBar.style.width = percent + '%';
                        uploadProgressText.textContent = percent + '%';
                        uploadProgressContainer.style.display = 'block';
                    }
                });

                // Gestion de la fin de l'upload (succès ou erreur)
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            uploadStatus.innerHTML = `<div class="alert alert-success"><span class="material-icons">check_circle</span> Fichier "${e(response.file_name)}" téléchargé avec succès !</div>`;
                            // Optionnel: Réinitialiser le formulaire ou rediriger
                            uploadForm.reset();
                            uploadProgressContainer.style.display = 'none';
                            uploadProgressBar.style.width = '0%';
                            uploadProgressText.textContent = '0%';
                        } else {
                            uploadStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur : ${e(response.message || 'Téléchargement échoué.')}</div>`;
                            uploadProgressContainer.style.display = 'none';
                        }
                    } else {
                        uploadStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur serveur (${xhr.status}). Veuillez réessayer.</div>`;
                        uploadProgressContainer.style.display = 'none';
                    }
                });

                xhr.addEventListener('error', function() {
                    uploadStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur réseau ou de connexion.</div>`;
                    uploadProgressContainer.style.display = 'none';
                });

                // Envoyer la requête
                xhr.open('POST', uploadForm.action);
                // Si vous utilisez un token CSRF, ajoutez-le aux headers:
                // xhr.setRequestHeader('X-CSRF-TOKEN', 'Votre_Token_CSRF_ici');
                xhr.send(formData);

                uploadStatus.innerHTML = ''; // Nettoyer les anciens messages
                uploadButton.disabled = true; // Désactiver le bouton pendant l'upload
            });

            // Réactiver le bouton après chaque tentative (succès ou échec)
            // Ceci est une simplification; une gestion plus fine est nécessaire pour les cas d'échec
            uploadForm.addEventListener('reset', function() {
                uploadButton.disabled = false;
            });
            uploadForm.querySelector('input[type="file"]').addEventListener('change', function() {
                uploadButton.disabled = false; // Réactiver si un nouveau fichier est sélectionné après un échec
                uploadStatus.innerHTML = ''; // Cacher le message de statut précédent
                uploadProgressContainer.style.display = 'none'; // Cacher la barre de progression
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour upload_form.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px;
        margin: var(--spacing-xl) auto;
    }

    .admin-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
    }

    .admin-card {
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Formulaires - réutilisation et adaptation */
    .form-group {
        margin-bottom: var(--spacing-md);
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="file"],
    .form-group textarea {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
    }

    .form-group input[type="file"] {
        /* Style spécifique pour l'input file pour le rendre plus cliquable */
        padding: var(--spacing-md);
        border: 2px dashed var(--border-medium);
        background-color: var(--primary-gray-light);
        cursor: pointer;
    }

    .form-group input[type="file"]:hover {
        border-color: var(--primary-blue-light);
    }

    .form-group input[type="file"]:focus,
    .form-group textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    .btn {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover:not(:disabled) {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn:disabled {
        background-color: var(--primary-gray);
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Styles pour la barre de progression */
    .progress-container {
        width: 100%;
        background-color: var(--border-light);
        border-radius: var(--border-radius-full);
        height: 20px;
        position: relative;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        width: 0%;
        background-color: var(--primary-green);
        border-radius: var(--border-radius-full);
        transition: width 0.3s ease-in-out;
    }

    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: var(--font-size-sm);
        color: var(--text-white);
        font-weight: var(--font-weight-semibold);
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
    }

    /* Styles pour les messages de statut (succès/erreur) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
    }

    .alert-success {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
        border: 1px solid var(--primary-green-dark);
    }

    .alert-error {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        border: 1px solid var(--accent-red-dark);
    }

    .alert .material-icons {
        font-size: var(--font-size-xl);
    }

    .mt-lg { margin-top: var(--spacing-lg); }
</style>