<?php
// src/Frontend/views/Etudiant/mes_documents.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les documents de l'étudiant (proviennent du DocumentEtudiantController)
//
//

$student_documents = $data['student_documents'] ?? [
    'releve_notes_provisoire' => [ // Dynamiquement généré
        'url' => null, // Sera rempli par JS après génération
        'last_generated' => null,
    ],
    'bulletins_officiels' => [
        ['id' => 1, 'annee' => '2023-2024', 'semestre' => 'S1', 'date_publication' => '2024-02-15', 'version' => '1.0', 'url' => '/assets/docs/bulletin_2023_2024_S1.pdf'],
        ['id' => 2, 'annee' => '2023-2024', 'semestre' => 'S2', 'date_publication' => '2024-07-01', 'version' => '1.0', 'url' => '/assets/docs/bulletin_2023_2024_S2.pdf'],
    ],
    'pv_validation' => [
        'exist' => true,
        'numero_pv' => 'PV-2025-0010',
        'date_validation' => '2025-07-05',
        'decision' => 'Approuvé en l\'état',
        'url' => '/assets/docs/pv_soutenance_RAP-2025-0045.pdf',
    ],
    'autres_documents' => [
        ['id' => 1, 'nom' => 'Attestation de Scolarité 2024-2025', 'date_emission' => '2024-09-01', 'url' => '/assets/docs/attestation_2024_2025.pdf'],
        ['id' => 2, 'nom' => 'Reçu de paiement Frais Inscription 2024', 'date_emission' => '2024-08-20', 'url' => '/assets/docs/recu_paiement_2024.pdf'],
    ],
];

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Mes Documents Officiels</h1>

    <section class="section-provisoire admin-card">
        <h2 class="section-title">Relevé de Notes Provisoire</h2>
        <p class="section-description">Générez un aperçu non officiel de vos notes à tout moment.</p>
        <div class="document-block">
            <p>Ce document est généré en temps réel et porte un filigrane "PROVISOIRE - DOCUMENT NON OFFICIEL".</p>
            <div class="document-actions-center">
                <button id="generateProvisoireBtn" class="btn btn-primary-blue">
                    <span class="material-icons">description</span> Générer mon Relevé Provisoire
                </button>
            </div>
            <div id="provisoireResult" class="mt-md text-center" style="display:none;">
                <p>Dernier généré : <span id="lastGeneratedDate"></span></p>
                <a id="downloadProvisoireLink" href="#" target="_blank" class="link-secondary">Télécharger le relevé provisoire</a>
            </div>
            <div id="provisoireLoading" class="text-center mt-md" style="display:none;">
                <span class="material-icons rotating-icon">cached</span> Génération en cours...
            </div>
        </div>
    </section>

    <section class="section-officiels admin-card mt-xl">
        <h2 class="section-title">Bulletins Officiels</h2>
        <?php if (!empty($student_documents['bulletins_officiels'])): ?>
            <ul class="document-list">
                <?php foreach ($student_documents['bulletins_officiels'] as $bulletin): ?>
                    <li class="document-item">
                        <span class="material-icons document-icon">picture_as_pdf</span>
                        <div class="document-info">
                            <h3>Bulletin Officiel - <?= e($bulletin['annee']); ?> (<?= e($bulletin['semestre']); ?>)</h3>
                            <p>Publié le : <?= e(date('d/m/Y', strtotime($bulletin['date_publication']))); ?> | Version : <?= e($bulletin['version']); ?></p>
                        </div>
                        <a href="<?= e($bulletin['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger">
                            <span class="material-icons">download</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucun bulletin officiel disponible pour le moment.</p>
        <?php endif; ?>
    </section>

    <section class="section-pv-validation admin-card mt-xl">
        <h2 class="section-title">Procès-Verbal de Validation de Soutenance</h2>
        <?php if ($student_documents['pv_validation']['exist']): ?>
            <div class="document-block">
                <h3>PV N°: <?= e($student_documents['pv_validation']['numero_pv']); ?></h3>
                <p>Validé le : <?= e(date('d/m/Y', strtotime($student_documents['pv_validation']['date_validation']))); ?></p>
                <p>Décision : <strong class="decision-status decision-status-<?= strtolower(str_replace(' ', '-', e($student_documents['pv_validation']['decision']))); ?>"><?= e($student_documents['pv_validation']['decision']); ?></strong></p>
                <div class="document-actions-center">
                    <a href="<?= e($student_documents['pv_validation']['url']); ?>" target="_blank" download class="btn btn-primary-blue">
                        <span class="material-icons">cloud_download</span> Télécharger mon PV
                    </a>
                    <a href="/etudiant/rapport/suivi" class="btn btn-secondary-gray ml-md">
                        <span class="material-icons">track_changes</span> Suivi du Rapport
                    </a>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Votre Procès-Verbal de soutenance n'est pas encore disponible.</p>
            <div class="text-center mt-md">
                <a href="/etudiant/rapport/suivi" class="link-secondary">Vérifier le statut de mon rapport</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-autres-docs admin-card mt-xl">
        <h2 class="section-title">Autres Documents Administratifs</h2>
        <?php if (!empty($student_documents['autres_documents'])): ?>
            <ul class="document-list">
                <?php foreach ($student_documents['autres_documents'] as $doc): ?>
                    <li class="document-item">
                        <span class="material-icons document-icon">insert_drive_file</span>
                        <div class="document-info">
                            <h3><?= e($doc['nom']); ?></h3>
                            <p>Émis le : <?= e(date('d/m/Y', strtotime($doc['date_emission']))); ?></p>
                        </div>
                        <a href="<?= e($doc['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger">
                            <span class="material-icons">download</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucun autre document administratif disponible pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateProvisoireBtn = document.getElementById('generateProvisoireBtn');
        const provisoireResult = document.getElementById('provisoireResult');
        const provisoireLoading = document.getElementById('provisoireLoading');
        const downloadProvisoireLink = document.getElementById('downloadProvisoireLink');
        const lastGeneratedDateSpan = document.getElementById('lastGeneratedDate');

        if (generateProvisoireBtn) {
            generateProvisoireBtn.addEventListener('click', function() {
                provisoireLoading.style.display = 'block';
                provisoireResult.style.display = 'none';

                // Simuler un appel AJAX pour générer le relevé provisoire
                fetch('/api/documents/generate-provisoire', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': 'votre_token_csrf_ici'
                    },
                    body: JSON.stringify({}) // Pas de données spécifiques requises pour la génération simple
                })
                    .then(response => response.json())
                    .then(data => {
                        provisoireLoading.style.display = 'none';
                        if (data.success && data.download_url) {
                            downloadProvisoireLink.href = data.download_url;
                            lastGeneratedDateSpan.textContent = new Date().toLocaleString('fr-FR', {
                                day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                            });
                            provisoireResult.style.display = 'block';
                            alert('Relevé provisoire généré avec succès !');
                        } else {
                            alert('Erreur lors de la génération : ' + (data.message || ''));
                        }
                    })
                    .catch(error => {
                        provisoireLoading.style.display = 'none';
                        console.error('Erreur AJAX génération relevé provisoire:', error);
                        alert('Une erreur de communication est survenue lors de la génération du relevé provisoire.');
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
    /* Styles spécifiques pour mes_documents.php */
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

    .section-description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }

    /* Blocs de documents (pour provisoire, PV) */
    .document-block {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
        text-align: center;
    }

    .document-block p {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-md);
    }

    .document-actions-center {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        flex-wrap: wrap; /* Pour petits écrans */
    }

    /* Boutons */
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

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
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

    /* Listes de documents (officiels, autres) */
    .document-list {
        list-style: none;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .document-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .document-icon {
        font-size: var(--font-size-3xl); /* Grande icône pour le type de doc */
        color: var(--primary-blue);
        flex-shrink: 0;
    }

    .document-info {
        flex-grow: 1;
    }

    .document-info h3 {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin: 0;
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .document-info p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .btn-action.download-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-green);
        font-size: var(--font-size-xl);
        text-decoration: none;
    }
    .btn-action.download-btn:hover {
        background-color: rgba(16, 185, 129, 0.1);
    }

    /* Styles pour le relevé provisoire en cours de génération */
    .rotating-icon {
        animation: rotate 1s linear infinite;
        color: var(--primary-blue);
        font-size: var(--font-size-2xl);
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }

    /* Statut de décision (pour le PV) */
    .decision-status {
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .decision-status-approuvé-en-l-état { color: var(--primary-green-dark); }
    .decision-status-approuvé-sous-réserve-de-corrections-mineures { color: var(--accent-yellow-dark); }
    .decision-status-refusé { color: var(--accent-red-dark); }
</style>