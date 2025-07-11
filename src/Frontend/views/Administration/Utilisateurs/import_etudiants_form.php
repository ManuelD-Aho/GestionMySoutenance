<!-- src/Frontend/views/Administration/Utilisateurs/import_etudiants.php -->
<?php $this->layout('layouts/layout_admin', ['title' => $title ?? 'Import Étudiants']); ?>

<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Administration</a></li>
            <li class="breadcrumb-item"><a href="/admin/utilisateurs">Utilisateurs</a></li>
            <li class="breadcrumb-item active">Import Étudiants</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-upload text-primary"></i>
                Import Étudiants en Masse
            </h1>
            <p class="text-muted mb-0">
                Création automatisée de comptes étudiants selon l'architecture RBAC de votre système
            </p>
        </div>
        <a href="/admin/utilisateurs" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="row">
    <!-- Formulaire d'Import -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-upload"></i> Uploader un Fichier</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="/admin/utilisateurs/import-etudiants" id="importForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Sélection du fichier -->
                    <div class="mb-4">
                        <label for="fichier_import" class="form-label required">Fichier à importer</label>
                        <div class="input-group">
                            <input type="file"
                                   class="form-control"
                                   id="fichier_import"
                                   name="fichier_import"
                                   accept=".csv,.xlsx,.xls"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" onclick="validateFile()">
                                <i class="fas fa-check"></i> Vérifier
                            </button>
                        </div>
                        <div class="form-text">
                            Formats acceptés : CSV, Excel (.xlsx, .xls). Taille maximum : 5MB
                        </div>
                        <div id="file-preview" class="mt-2"></div>
                    </div>

                    <!-- Paramètres d'import -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="annee_academique" class="form-label">Année académique par défaut</label>
                                <select class="form-select" id="annee_academique" name="annee_academique">
                                    <option value="">Sélectionner une année</option>
                                    <?php foreach ($annees_academiques ?? [] as $annee): ?>
                                        <option value="<?= htmlspecialchars($annee['id_annee_academique']) ?>">
                                            <?= htmlspecialchars($annee['libelle_annee_academique'] ?? $annee['id_annee_academique']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Appliquée aux étudiants sans année spécifiée</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="niveau_etude_defaut" class="form-label">Niveau d'étude par défaut</label>
                                <select class="form-select" id="niveau_etude_defaut" name="niveau_etude_defaut">
                                    <option value="">Sélectionner un niveau</option>
                                    <?php foreach ($niveaux_etude ?? [] as $niveau): ?>
                                        <option value="<?= htmlspecialchars($niveau['id_niveau_etude']) ?>">
                                            <?= htmlspecialchars($niveau['libelle_niveau_etude'] ?? $niveau['id_niveau_etude']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="groupe_defaut" class="form-label">Groupe RBAC par défaut</label>
                                <select class="form-select" id="groupe_defaut" name="groupe_defaut">
                                    <option value="GRP_ETUDIANT" selected>GRP_ETUDIANT (Standard)</option>
                                    <!-- Ajouter d'autres groupes étudiants si nécessaire -->
                                </select>
                                <div class="form-text">Définit les permissions selon l'architecture RBAC</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="niveau_acces_defaut" class="form-label">Niveau d'accès par défaut</label>
                                <select class="form-select" id="niveau_acces_defaut" name="niveau_acces_defaut">
                                    <option value="ACCES_PERSONNEL" selected>ACCES_PERSONNEL</option>
                                    <option value="ACCES_DEPARTEMENT">ACCES_DEPARTEMENT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Options avancées -->
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Options Avancées</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generer_logins" name="generer_logins" value="1" checked>
                                        <label class="form-check-label" for="generer_logins">
                                            Générer automatiquement les logins
                                        </label>
                                        <div class="form-text">Format : prenom.nom</div>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generer_mots_de_passe" name="generer_mots_de_passe" value="1" checked>
                                        <label class="form-check-label" for="generer_mots_de_passe">
                                            Générer des mots de passe aléatoires
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="envoyer_emails" name="envoyer_emails" value="1" checked>
                                        <label class="form-check-label" for="envoyer_emails">
                                            Envoyer des emails de validation
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="mode_test" name="mode_test" value="1">
                                        <label class="form-check-label" for="mode_test">
                                            Mode test (simulation sans création)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between">
                        <a href="/admin/utilisateurs" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>

                        <div>
                            <button type="button" class="btn btn-info me-2" onclick="previewImport()">
                                <i class="fas fa-eye"></i> Prévisualiser
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload"></i> Lancer l'Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Aide et Instructions -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Format du Fichier</h6>
            </div>
            <div class="card-body">
                <p class="small">Le fichier doit contenir les colonnes suivantes :</p>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Colonne</th>
                            <th>Obligatoire</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><code>nom</code></td>
                            <td><span class="badge bg-danger">Oui</span></td>
                        </tr>
                        <tr>
                            <td><code>prenom</code></td>
                            <td><span class="badge bg-danger">Oui</span></td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td><span class="badge bg-danger">Oui</span></td>
                        </tr>
                        <tr>
                            <td><code>numero_etudiant</code></td>
                            <td><span class="badge bg-warning">Optionnel</span></td>
                        </tr>
                        <tr>
                            <td><code>date_naissance</code></td>
                            <td><span class="badge bg-warning">Optionnel</span></td>
                        </tr>
                        <tr>
                            <td><code>telephone</code></td>
                            <td><span class="badge bg-warning">Optionnel</span></td>
                        </tr>
                        <tr>
                            <td><code>niveau_etude</code></td>
                            <td><span class="badge bg-warning">Optionnel</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h6>Exemple CSV :</h6>
                    <pre class="bg-light p-2 small">nom,prenom,email,numero_etudiant
DUPONT,Jean,jean.dupont@email.com,20240001
MARTIN,Marie,marie.martin@email.com,20240002</pre>
                </div>

                <div class="mt-3">
                    <a href="/admin/utilisateurs/template-import" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-download"></i> Télécharger Modèle
                    </a>
                </div>
            </div>
        </div>

        <!-- Informations Architecture RBAC -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Architecture RBAC</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <p><strong>Type créé :</strong> TYPE_ETUDIANT</p>
                    <p><strong>Groupe par défaut :</strong> GRP_ETUDIANT</p>
                    <p><strong>Permissions :</strong> Définies par la table rattacher</p>
                    <p><strong>Niveau d'accès :</strong> ACCES_PERSONNEL (données personnelles uniquement)</p>
                    <hr>
                    <p class="mb-0">
                        Chaque étudiant créé respectera automatiquement l'architecture
                        RBAC documentée dans votre système d'informations.
                    </p>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Prévisualisation -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévisualisation de l'Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Contenu généré dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="confirmImport()">Confirmer l'Import</button>
            </div>
        </div>
    </div>
</div>

<script>
    function validateFile() {
        const fileInput = document.getElementById('fichier_import');
        const file = fileInput.files[0];
        const previewDiv = document.getElementById('file-preview');

        if (!file) {
            previewDiv.innerHTML = '<div class="alert alert-warning">Aucun fichier sélectionné.</div>';
            return;
        }

        // Vérifications de base
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        if (file.size > maxSize) {
            previewDiv.innerHTML = '<div class="alert alert-danger">Fichier trop volumineux (maximum 5MB).</div>';
            return;
        }

        previewDiv.innerHTML = `
        <div class="alert alert-success">
            <strong>Fichier valide :</strong><br>
            <i class="fas fa-file"></i> ${file.name}<br>
            <i class="fas fa-weight"></i> ${(file.size / 1024).toFixed(1)} KB<br>
            <i class="fas fa-check"></i> Prêt pour l'import
        </div>
    `;
    }

    function previewImport() {
        const fileInput = document.getElementById('fichier_import');
        if (!fileInput.files[0]) {
            alert('Veuillez d\'abord sélectionner un fichier.');
            return;
        }

        // Ici vous pourriez implémenter une prévisualisation AJAX
        document.getElementById('previewContent').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Fonctionnalité de prévisualisation en cours de développement.
            Le fichier sera traité selon les paramètres sélectionnés.
        </div>
    `;

        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    function confirmImport() {
        document.getElementById('importForm').submit();
    }

    // Validation du formulaire
    document.getElementById('importForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('fichier_import');
        if (!fileInput.files[0]) {
            e.preventDefault();
            alert('Veuillez sélectionner un fichier à