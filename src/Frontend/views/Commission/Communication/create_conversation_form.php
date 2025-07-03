<?php
// src/Frontend/views/Commission/Communication/create_conversation_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les utilisateurs disponibles (proviennent du contrôleur CommunicationCommissionController)
//

$utilisateurs_disponibles = $data['utilisateurs_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dr. Dupont Jean (Membre Commission A)'],
    ['id' => 2, 'nom_complet' => 'Pr. Martin Sophie (Président Commission)'],
    ['id' => 3, 'nom_complet' => 'Mme. Leclerc Anne (Agent Scolarité)'],
    ['id' => 4, 'nom_complet' => 'M. Bernard Paul (Membre Commission B)'],
    ['id' => 5, 'nom_complet' => 'Mlle. Dubois Marie (Étudiant)'],
];

// Assumons que l'utilisateur actuel ne peut pas s'ajouter lui-même ou est déjà géré par le contrôleur
$current_user_id = $_SESSION['user_id'] ?? null;

?>

<div class="admin-module-container">
    <h1 class="admin-title">Créer une Nouvelle Conversation</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Informations de la Conversation</h2>
        <form id="createConversationForm" action="/commission/communication/create-conversation" method="POST">
            <div class="form-group">
                <label for="titre_conversation">Titre de la Conversation :</label>
                <input type="text" id="titre_conversation" name="titre_conversation" required placeholder="Ex: Discussion sur les PV de juin">
            </div>
            <div class="form-group">
                <label for="description_conversation">Description (facultatif) :</label>
                <textarea id="description_conversation" name="description_conversation" rows="4" placeholder="Objectif de la conversation..."></textarea>
            </div>
            <div class="form-group">
                <label for="participants">Sélectionner les Participants :</label>
                <select id="participants" name="participants[]" multiple required size="8">
                    <?php foreach ($utilisateurs_disponibles as $user): ?>
                        <?php // Exclure l'utilisateur courant s'il ne peut pas s'ajouter lui-même et s'il est déjà implicitement partie prenante
                        // if ($user['id'] !== $current_user_id):
                        ?>
                        <option value="<?= e($user['id']); ?>"><?= e($user['nom_complet']); ?></option>
                        <?php // endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs participants.</small>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">chat</span> Créer la Conversation
            </button>
            <a href="/commission/communication/dashboard" class="btn btn-secondary-gray ml-md">
                <span class="material-icons">cancel</span> Annuler
            </a>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createConversationForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const titreConversation = document.getElementById('titre_conversation').value.trim();
                const participantsSelect = document.getElementById('participants');

                if (!titreConversation) {
                    alert('Veuillez saisir un titre pour la conversation.');
                    event.preventDefault();
                    return;
                }

                if (participantsSelect.selectedOptions.length === 0) {
                    alert('Veuillez sélectionner au moins un participant pour la conversation.');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire de création de conversation soumis.");
                // Si vous utilisez AJAX, prévenez la soumission par défaut et faites l'appel ici
                // fetch('/commission/communication/create-conversation', { ... });
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
    /* Styles spécifiques pour create_conversation_form.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px; /* Largeur adaptée au formulaire */
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

    .form-group input[type="text"],
    .form-group textarea,
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
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* Style spécifique pour le select multiple */
    .form-group select[multiple] {
        height: auto;
        min-height: 180px; /* Plus grand pour la sélection de participants */
        overflow-y: auto;
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
</style>