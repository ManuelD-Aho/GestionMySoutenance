<?php
// src/Frontend/views/Administration/Utilisateurs/import_etudiants_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Assumons que le contrôleur UtilisateurController gère l'importation
//
?>

<div class="admin-module-container">
    <h1 class="admin-title">Importation en Masse d'Étudiants</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Téléverser un Fichier d'Étudiants</h2>
        <p class="section-description">Utilisez ce formulaire pour importer plusieurs comptes étudiants à partir d'un fichier. <br>Le processus d'importation est avancé et inclut le mappage des colonnes et la correction en ligne, comme décrit dans la documentation.</p>

        <form id="importEtudiantsForm" action="/admin/utilisateurs/import-etudiants" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fichier_etudiants">Sélectionner un fichier (CSV, XLSX) :</label>
                <input type="file" id="fichier_etudiants" name="fichier_etudiants" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
                <small class="form-help">Formats acceptés : CSV, Excel (XLSX). Assurez-vous que la première ligne contient les en-têtes de colonne (ex: nom, prenom, email, matricule, niveau).</small>
            </div>

            <button type="submit" class="btn btn-primary-blue" id="importerEtudiantsBtn">
                <span class="material-icons">cloud_upload</span>
                Importer les Étudiants
            </button>
        </form>

        <div id="importStatusContainer" class="progress-container mt-lg" style="display: none;">
            <div class="progress-bar" id="importProgressBar"></div>
            <div class="progress-text" id="importProgressText">0%</div>
        </div>

        <div id="importResult" class="mt-lg">
        </div>

        <div class="mt-xl text-center">
            <p class="text-muted">Pour un aperçu détaillé de l'outil d'importation avancé (mappage interactif, correction en ligne, gestion asynchrone), veuillez consulter la documentation du système.</p>
            <a href="/docs/outil-importation" class="link-secondary">Consulter la Documentation sur l'Outil d'Importation</a>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const importForm = document.getElementById('importEtudiantsForm');
        const importerEtudiantsBtn = document.getElementById('importerEtudiantsBtn');
        const importStatusContainer = document.getElementById('importStatusContainer');
        const importProgressBar = document.getElementById('importProgressBar');
        const importProgressText = document.getElementById('importProgressText');
        const importResult = document.getElementById('importResult');

        if (importForm) {
            importForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Empêche la soumission de formulaire par défaut

                const fileInput = document.getElementById('fichier_etudiants');
                if (fileInput.files.length === 0) {
                    alert('Veuillez sélectionner un fichier à importer.');
                    return;
                }

                const formData = new FormData(importForm);
                const xhr = new XMLHttpRequest();

                // Gestion de la progression (pour l'upload du fichier)
                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        importProgressBar.style.width = percent + '%';
                        importProgressText.textContent = percent + '%';
                        importStatusContainer.style.display = 'block';
                    }
                });

                // Gestion de la réponse après traitement du fichier sur le serveur
                xhr.addEventListener('load', function() {
                    importerEtudiantsBtn.disabled = false; // Réactiver le bouton
                    importProgressBar.style.width = '0%'; // Réinitialiser la barre
                    importProgressText.textContent = '0%';

                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            importResult.innerHTML = `
                            <div class="alert alert-success">
                                <span class="material-icons">check_circle</span>
                                Importation lancée avec succès ! ${e(response.message || 'Le traitement se fera en arrière-plan.')}
                                ${response.report_link ? `<p><a href="${e(response.report_link)}" class="link-secondary">Voir le rapport d'importation</a></p>` : ''}
                            </div>
                        `;
                            importForm.reset();
                            importStatusContainer.style.display = 'none'; // Cacher la barre une fois l'upload terminé
                        } else {
                            importResult.innerHTML = `
                            <div class="alert alert-error">
                                <span class="material-icons">error</span>
                                Erreur lors de l'importation : ${e(response.message || 'Échec du traitement du fichier.')}
                            </div>
                        `;
                        }
                    } else {
                        importResult.innerHTML = `
                        <div class="alert alert-error">
                            <span class="material-icons">error</span>
                            Erreur serveur (${xhr.status}). Veuillez réessayer.
                        </div>
                    `;
                    }
                });

                xhr.addEventListener('error', function() {
                    importerEtudiantsBtn.disabled = false;
                    importResult.innerHTML = `
                    <div class="alert alert-error">
                        <span class="material-icons">error</span>
                        Erreur réseau ou de connexion lors de l'envoi du fichier.
                    </div>
                `;
                    importStatusContainer.style.display = 'none';
                });

                // Lancer la requête
                importerEtudiantsBtn.disabled = true; // Désactiver le bouton pendant l'upload
                importResult.innerHTML = ''; // Nettoyer les anciens messages
                importStatusContainer.style.display = 'block'; // Afficher la barre de progression
                xhr.open('POST', importForm.action);
                // Si vous utilisez un token CSRF, ajoutez-le aux headers:
                // xhr.setRequestHeader('X-CSRF-TOKEN', 'Votre_Token_CSRF_ici');
                xhr.send(formData);
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
    /* Styles spécifiques pour import_etudiants_form.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Largeur adaptée au formulaire */
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

    .section-description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
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

    .form-group input[type="file"] {
        padding: var(--spacing-md);
        border: 2px dashed var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
        cursor: pointer;
    }

    .form-group input[type="file"]:hover {
        border-color: var(--primary-blue-light);
    }

    .form-group input[type="file"]:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons - réutilisation des styles existants */
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

    /* Styles pour la progression de l'importation */
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
        margin-bottom: var(--spacing-md); /* Ajustement pour espacement */
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

    .link-secondary {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color var(--transition-fast);
        font-weight: var(--font-weight-medium);
    }

    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>