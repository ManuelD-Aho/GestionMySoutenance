<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/generation_documents_scolarite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les types de documents, étudiants, années académiques, semestres
// (proviennent du ScolariteController).
//
//

$document_types_scolarite = $data['document_types_scolarite'] ?? [
    ['code' => 'ATTESTATION_SCOLARITE', 'libelle' => 'Attestation de Scolarité'],
    ['code' => 'BULLETIN_NOTES_OFFICIEL', 'libelle' => 'Bulletin de Notes Officiel'],
    // Vous pouvez ajouter d'autres documents spécifiques à la scolarité ici
];

$etudiants_disponibles = $data['etudiants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dupont Jean (ETU-2025-0001)'],
    ['id' => 2, 'nom_complet' => 'Curie Marie (ETU-2025-0002)'],
    ['id' => 3, 'nom_complet' => 'Voltaire François (ETU-2025-0003)'],
];

$annees_academiques_disponibles = $data['annees_academiques_disponibles'] ?? [
    ['id' => 1, 'libelle' => '2023-2024'],
    ['id' => 2, 'libelle' => '2024-2025'],
];

$semestres_disponibles = [
    ['id' => 'S1', 'libelle' => 'Semestre 1'],
    ['id' => 'S2', 'libelle' => 'Semestre 2'],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Génération de Documents de Scolarité</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Sélectionner les Critères de Génération</h2>
        <form id="generationScolariteDocumentForm" action="/personnel/scolarite/generate-documents" method="POST">
            <div class="form-group">
                <label for="document_type">Type de Document :</label>
                <select id="document_type" name="document_type" required>
                    <option value="">Sélectionner un type de document</option>
                    <?php foreach ($document_types_scolarite as $type): ?>
                        <option value="<?= e($type['code']); ?>"><?= e($type['libelle']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="dynamic_fields_container_scolarite">
                <div class="dynamic-field-group" id="fields_ATTESTATION_SCOLARITE" style="display:none;">
                    <div class="form-group">
                        <label for="attestation_etudiant_id">Étudiant :</label>
                        <select id="attestation_etudiant_id" name="attestation_etudiant_id">
                            <option value="">Sélectionner un étudiant</option>
                            <?php foreach ($etudiants_disponibles as $etudiant): ?>
                                <option value="<?= e($etudiant['id']); ?>"><?= e($etudiant['nom_complet']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="attestation_annee_academique_id">Année Académique :</label>
                        <select id="attestation_annee_academique_id" name="attestation_annee_academique_id">
                            <option value="">Sélectionner une année</option>
                            <?php foreach ($annees_academiques_disponibles as $annee): ?>
                                <option value="<?= e($annee['id']); ?>"><?= e($annee['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="dynamic-field-group" id="fields_BULLETIN_NOTES_OFFICIEL" style="display:none;">
                    <div class="form-group">
                        <label for="bulletin_etudiant_id">Étudiant (pour génération individuelle) :</label>
                        <select id="bulletin_etudiant_id" name="bulletin_etudiant_id">
                            <option value="">Sélectionner un étudiant</option>
                            <?php foreach ($etudiants_disponibles as $etudiant): ?>
                                <option value="<?= e($etudiant['id']); ?>"><?= e($etudiant['nom_complet']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bulletin_annee_academique_id">Année Académique :</label>
                        <select id="bulletin_annee_academique_id" name="bulletin_annee_academique_id">
                            <option value="">Sélectionner une année</option>
                            <?php foreach ($annees_academiques_disponibles as $annee): ?>
                                <option value="<?= e($annee['id']); ?>"><?= e($annee['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bulletin_semestre_id">Semestre (facultatif) :</label>
                        <select id="bulletin_semestre_id" name="bulletin_semestre_id">
                            <option value="">Tous les semestres</option>
                            <?php foreach ($semestres_disponibles as $semestre): ?>
                                <option value="<?= e($semestre['id']); ?>"><?= e($semestre['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="bulletin_generation_masse" name="bulletin_generation_masse">
                        <label for="bulletin_generation_masse">Génération en masse (pour toute l'année académique sélectionnée) :</label>
                        <small class="form-help">Si coché, la génération se fera pour tous les étudiants de l'année et du semestre sélectionnés.</small>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-green">
                    <span class="material-icons">picture_as_pdf</span> Générer le Document(s)
                </button>
                <a href="/personnel/documents/liste-generes" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">list_alt</span> Voir les documents générés
                </a>
            </div>
        </form>
        <div id="generationStatus" class="mt-lg"></div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const documentTypeSelect = document.getElementById('document_type');
        const dynamicFieldsContainer = document.getElementById('dynamic_fields_container_scolarite');
        const generationScolariteDocumentForm = document.getElementById('generationScolariteDocumentForm');
        const generationStatus = document.getElementById('generationStatus');

        const dynamicFieldsMap = {
            'ATTESTATION_SCOLARITE': ['attestation_etudiant_id', 'attestation_annee_academique_id'],
            'BULLETIN_NOTES_OFFICIEL': ['bulletin_etudiant_id', 'bulletin_annee_academique_id', 'bulletin_semestre_id'],
        };

        function toggleDynamicFields() {
            const selectedType = documentTypeSelect.value;
            document.querySelectorAll('.dynamic-field-group').forEach(group => {
                group.style.display = 'none';
                group.querySelectorAll('input, select, textarea').forEach(field => {
                    field.removeAttribute('required');
                });
            });

            if (selectedType && dynamicFieldsMap[selectedType]) {
                const activeGroup = document.getElementById('fields_' + selectedType);
                if (activeGroup) {
                    activeGroup.style.display = 'block';
                    // Ajouter l'attribut required aux champs du groupe actif, sauf pour la génération en masse
                    dynamicFieldsMap[selectedType].forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            if (selectedType === 'BULLETIN_NOTES_OFFICIEL' && field.id === 'bulletin_etudiant_id') {
                                // Le champ étudiant n'est pas requis si la génération en masse est cochée
                                if (!document.getElementById('bulletin_generation_masse').checked) {
                                    field.setAttribute('required', 'required');
                                }
                            } else {
                                field.setAttribute('required', 'required');
                            }
                        }
                    });
                }
            }
        }

        if (documentTypeSelect) {
            documentTypeSelect.addEventListener('change', toggleDynamicFields);
            toggleDynamicFields(); // Initialiser à l'ouverture de la page

            // Gérer le changement de l'état de la checkbox de génération en masse pour les bulletins
            const bulletinGenerationMasseCheckbox = document.getElementById('bulletin_generation_masse');
            if (bulletinGenerationMasseCheckbox) {
                bulletinGenerationMasseCheckbox.addEventListener('change', function() {
                    const bulletinEtudiantIdField = document.getElementById('bulletin_etudiant_id');
                    if (this.checked) {
                        bulletinEtudiantIdField.removeAttribute('required');
                    } else {
                        bulletinEtudiantIdField.setAttribute('required', 'required');
                    }
                });
            }
        }

        if (generationScolariteDocumentForm) {
            generationScolariteDocumentForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const selectedType = documentTypeSelect.value;
                let isValid = true;

                // Effectuer des validations spécifiques avant l'envoi AJAX
                if (selectedType === 'BULLETIN_NOTES_OFFICIEL') {
                    const bulletinMasse = document.getElementById('bulletin_generation_masse').checked;
                    const bulletinEtudiantId = document.getElementById('bulletin_etudiant_id').value;
                    const bulletinAnneeId = document.getElementById('bulletin_annee_academique_id').value;

                    if (!bulletinAnneeId) {
                        alert('L\'année académique est obligatoire pour la génération de bulletins.');
                        isValid = false;
                    }
                    if (!bulletinMasse && !bulletinEtudiantId) {
                        alert('Veuillez sélectionner un étudiant ou cocher la génération en masse pour les bulletins.');
                        isValid = false;
                    }
                } else if (selectedType === 'ATTESTATION_SCOLARITE') {
                    const attestationEtudiantId = document.getElementById('attestation_etudiant_id').value;
                    const attestationAnneeId = document.getElementById('attestation_annee_academique_id').value;
                    if (!attestationEtudiantId || !attestationAnneeId) {
                        alert('L\'étudiant et l\'année académique sont obligatoires pour l\'attestation de scolarité.');
                        isValid = false;
                    }
                }
                // Ajoutez d'autres validations spécifiques ici pour d'autres types de documents si besoin

                if (!isValid) return;


                generationStatus.innerHTML = '<div class="alert alert-info"><span class="material-icons">hourglass_empty</span> Génération en cours...</div>';

                const formData = new FormData(this);
                // S'assurer que les checkboxes envoient leur état même si non cochées
                if (!document.getElementById('bulletin_generation_masse')?.checked) {
                    // Si la checkbox n'existe pas ou n'est pas cochée, ajouter un champ caché pour indiquer false
                    // Ceci est une astuce pour s'assurer qu'un paramètre est toujours envoyé si le serveur s'attend à un booléen
                    // et ne gère pas l'absence du paramètre pour signifier false.
                    // Attention: formData.append écrase la valeur si la clé existe déjà, utiliser au cas par cas.
                    // Pour checkbox, si non cochée, le navigateur n'envoie rien. Le serveur doit gérer l'absence.
                }


                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    // headers: { 'X-CSRF-TOKEN': 'votre_token_csrf_ici' } // Si vous utilisez CSRF
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            generationStatus.innerHTML = `<div class="alert alert-success"><span class="material-icons">check_circle</span> ${e(data.message || 'Document(s) généré(s) avec succès !')} ${data.download_url ? '<p><a href="'+e(data.download_url)+'" target="_blank" download class="link-secondary">Télécharger le document</a></p>' : ''}</div>`;
                            generationScolariteDocumentForm.reset();
                            toggleDynamicFields(); // Masquer les champs dynamiques et réinitialiser leur required state
                        } else {
                            generationStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur de génération : ${e(data.message || 'Erreur inconnue.')}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX génération document:', error);
                        generationStatus.innerHTML = `<div class="alert alert-error"><span class="material-icons">error</span> Erreur de communication lors de la génération.</div>`;
                    });
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
    /* Styles spécifiques pour generation_documents_scolarite.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Taille adaptée */
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

    /* Formulaires - réutilisation */
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

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Groupes de champs dynamiques */
    .dynamic-field-group {
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin-top: var(--spacing-lg);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
    }

    /* Styles pour les checkboxes dans les groupes de formulaires */
    .form-group.checkbox-group {
        flex-direction: row;
        align-items: center;
        gap: var(--spacing-md);
    }

    .form-group.checkbox-group label {
        margin-bottom: 0;
        flex-grow: 1;
        cursor: pointer;
    }

    .form-group.checkbox-group input[type="checkbox"] {
        width: auto;
        margin: 0;
        transform: scale(1.2);
    }


    /* Boutons d'action */
    .form-actions {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-top: var(--spacing-xl);
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
        text-decoration: none;
    }

    .btn-primary-green {
        color: var(--text-white);
        background-color: var(--primary-green);
    }

    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }

    .ml-md { margin-left: var(--spacing-md); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }

    /* Message de statut/alerte (réutilisé) */
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
        border: 1px solid;
        background-color: var(--bg-primary); /* Fond blanc pour les alertes dans le contenu */
    }
    .alert-info {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
        border-color: var(--primary-blue-dark);
    }
    .alert-success {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
        border-color: var(--primary-green-dark);
    }
    .alert-error {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        border-color: var(--accent-red-dark);
    }
    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .link-secondary { /* Pour le lien de téléchargement */
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: var(--font-weight-semibold);
        transition: color var(--transition-fast), text-decoration var(--transition-fast);
    }
    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }
</style>