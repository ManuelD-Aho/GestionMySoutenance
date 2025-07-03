<?php
// src/Frontend/views/Etudiant/Profile/upload_photo_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données de la photo de profil actuelle de l'étudiant (proviennent du ProfilEtudiantController)
//

$current_photo_url = $data['current_photo_url'] ?? '/assets/img/default_avatar.png'; // Image par défaut
$max_file_size_mb = 5; // Taille maximale autorisée en Mo

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Modifier votre Photo de Profil</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Télécharger une Nouvelle Photo</h2>
        <form id="uploadPhotoForm" action="/etudiant/profile/upload-photo" method="POST" enctype="multipart/form-data">
            <div class="form-group text-center">
                <label for="profile_photo" class="photo-upload-label">
                    <div class="photo-preview-area">
                        <img id="currentPhotoPreview" src="<?= e($current_photo_url); ?>" alt="Photo de profil actuelle" class="current-photo">
                        <span class="material-icons upload-icon">add_a_photo</span>
                        <p class="upload-text">Cliquez pour sélectionner une photo</p>
                    </div>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png, image/webp" style="display: none;">
                </label>
                <small class="form-help">Formats acceptés : JPG, PNG, WEBP. Taille maximale : <?= e($max_file_size_mb); ?> Mo.</small>
            </div>

            <button type="submit" class="btn btn-primary-blue btn-full-width mt-lg" id="uploadPhotoBtn">
                <span class="material-icons">cloud_upload</span> Télécharger la Photo
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
        const uploadPhotoForm = document.getElementById('uploadPhotoForm');
        const profilePhotoInput = document.getElementById('profile_photo');
        const currentPhotoPreview = document.getElementById('currentPhotoPreview');
        const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
        const uploadProgressContainer = document.getElementById('uploadProgressContainer');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadProgressText = document.getElementById('uploadProgressText');
        const uploadStatus = document.getElementById('uploadStatus');

        const MAX_FILE_SIZE_MB = <?= json_encode($max_file_size_mb); ?>;
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

        // Aperçu de l'image avant téléchargement
        if (profilePhotoInput) {
            profilePhotoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    if (!ALLOWED_TYPES.includes(file.type)) {
                        alert('Format de fichier non autorisé. Veuillez choisir une image JPG, PNG ou WEBP.');
                        profilePhotoInput.value = ''; // Réinitialiser le champ
                        currentPhotoPreview.src = '<?= e($current_photo_url); ?>'; // Retour à l'ancienne image ou par défaut
                        return;
                    }
                    if (file.size > MAX_FILE_SIZE_MB * 1024 * 1024) {
                        alert('La taille du fichier dépasse la limite de ' + MAX_FILE_SIZE_MB + ' Mo.');
                        profilePhotoInput.value = '';
                        currentPhotoPreview.src = '<?= e($current_photo_url); ?>';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        currentPhotoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    currentPhotoPreview.src = '<?= e($current_photo_url); ?>'; // Retour à l'image par défaut si rien n'est sélectionné
                }
            });
        }

        // Soumission du formulaire via AJAX
        if (uploadPhotoForm) {
            uploadPhotoForm.addEventListener('submit', function(event) {
                event.preventDefault();

                if (profilePhotoInput.files.length === 0) {
                    alert('Veuillez sélectionner une photo à télécharger.');
                    return;
                }

                const formData = new FormData(uploadPhotoForm);
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        uploadProgressBar.style.width = percent + '%';
                        uploadProgressText.textContent = percent + '%';
                        uploadProgressContainer.style.display = 'block';
                    }
                });

                xhr.addEventListener('load', function() {
                    uploadPhotoBtn.disabled = false;
                    uploadProgressBar.style.width = '0%';
                    uploadProgressText.textContent = '0%';

                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            uploadStatus.innerHTML = `<div class="alert alert-success"><span class="material-icons">check_circle</span> ${e(response.message || 'Photo de profil mise à jour avec succès !')}</div>`;
                            // Mettre à jour l'image de profil dans le header/menu si le chemin est fourni
                            if (response.new_photo_url && window.DashboardHeader && typeof window.DashboardHeader.updateUserInfo === 'function') {
                                // Assurez-vous que DashboardHeader.updateUserInfo peut prendre le chemin de l'avatar
                                window.DashboardHeader.updateUserInfo(
                                    "<?= e($_SESSION['user_data']['prenom'] ?? '') . ' ' . e($_SESSION['user_data']['nom'] ?? ''); ?>", // Nom actuel
                                    "<?= e($_SESSION['user_role_label'] ?? ''); ?>", // Rôle actuel
                                    response.new_photo_url // Nouveau chemin de la photo
                                );
                            }
                            profilePhotoInput.value = ''; // Réinitialiser le champ de fichier
                            uploadProgressContainer.style.display = 'none';
                            // Optionnel: Recharger la page ou rediriger si nécessaire
                            // setTimeout(() => window.location.reload(), 1500);
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
                    uploadPhotoBtn.disabled = false;
                    uploadStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur réseau ou de connexion.</div>`;
                    uploadProgressContainer.style.display = 'none';
                });

                uploadPhotoBtn.disabled = true;
                uploadStatus.innerHTML = '';
                xhr.open('POST', uploadPhotoForm.action);
                xhr.send(formData);
            });
        }
    });
</script>

<style>
    /* Styles spécifiques pour upload_photo_form.php */
    /* Réutilisation des classes de root.css et des styles généraux de style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 700px; /* Plus petit pour un formulaire de photo */
        margin: var(--spacing-xl) auto;
    }

    .dashboard-title { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px solid var(--border-light);
    }

    .admin-card { /* Réutilisé des modules d'administration */
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Photo upload specific styles */
    .photo-upload-label {
        display: block; /* Prend toute la largeur disponible */
        cursor: pointer;
        border: 2px dashed var(--border-medium);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        background-color: var(--primary-gray-light);
        transition: all var(--transition-fast);
        text-align: center;
        position: relative;
        overflow: hidden; /* Assurer que l'image ne dépasse pas */
    }

    .photo-upload-label:hover {
        border-color: var(--primary-blue);
        background-color: var(--primary-blue-light);
        color: var(--text-white);
    }

    .photo-upload-label:hover .upload-icon,
    .photo-upload-label:hover .upload-text {
        color: var(--text-white);
    }

    .photo-preview-area {
        width: 180px; /* Taille pour l'aperçu */
        height: 180px;
        border-radius: var(--border-radius-full);
        overflow: hidden;
        margin: 0 auto var(--spacing-md);
        border: 3px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--primary-white);
        position: relative;
    }

    .photo-preview-area img.current-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .photo-preview-area .upload-icon {
        position: absolute;
        font-size: var(--font-size-4xl); /* Grande icône */
        color: var(--primary-gray);
        transition: color var(--transition-fast);
    }

    .photo-upload-label:hover .upload-icon {
        color: var(--text-white);
    }

    .upload-text {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-top: var(--spacing-md);
        transition: color var(--transition-fast);
    }

    .form-help { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons - réutilisés */
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
        text-decoration: none;
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

    .btn-full-width {
        width: 100%;
    }

    .mt-lg { margin-top: var(--spacing-lg); }

    /* Styles pour la barre de progression (réutilisés de upload_form.php) */
    .progress-container {
        width: 100%;
        background-color: var(--border-light);
        border-radius: var(--border-radius-full);
        height: 25px;
        position: relative;
        overflow: hidden;
        margin-top: var(--spacing-md);
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .progress-bar {
        height: 100%;
        width: 0%;
        background-color: var(--primary-green);
        border-radius: var(--border-radius-full);
        transition: width 0.3s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .progress-text {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-sm);
        color: var(--text-white);
        font-weight: var(--font-weight-semibold);
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
    }

    /* Styles pour les messages de résultat (réutilisés) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        text-align: left;
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
        flex-shrink: 0;
    }

    .text-center { text-align: center; }
</style>