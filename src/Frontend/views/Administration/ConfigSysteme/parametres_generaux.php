<?php
// src/Frontend/views/Administration/ConfigSysteme/parametres_generaux.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les paramètres généraux (proviennent du contrôleur ConfigSystemeController)
// En production, ces données seraient récupérées depuis la base de données via ServiceConfigurationSystemeInterface.
//
//

$parametres_generaux = $data['parametres_generaux'] ?? [
    'date_limite_soumission_rapport' => '2025-07-31', // Exemple d'échéance critique
    'duree_validite_jeton_securite_heures' => 24,    // Durée de validité des jetons
    'seuil_tentatives_connexion_echouees' => 5,      // Seuil avant blocage
    'duree_blocage_compte_minutes' => 30,          // Durée du blocage de compte
    'notifications_email_active' => true,         // Option de processus interne
    'delai_archivage_pv_mois' => 12,              // Option de processus interne
    'penalite_retard_apres_an_1_montant' => 5000, // Paramètre lié aux pénalités
    'penalite_retard_apres_an_2_montant' => 10000,
];

// Les types de pénalités seraient gérés comme un référentiel ou des paramètres spécifiques
$types_penalites = $data['types_penalites'] ?? [
    ['code' => 'FINANCIERE', 'libelle' => 'Pénalité financière'],
    ['code' => 'ADMINISTRATIVE', 'libelle' => 'Restriction administrative'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Paramètres Généraux du Système</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Configuration des Règles Métier et Fonctionnement</h2>
        <form id="formParametresGeneraux" action="/admin/config/parametres-generaux/update" method="POST">
            <h3>Échéances Critiques et Délais</h3>
            <div class="form-group">
                <label for="date_limite_soumission_rapport">Date limite de soumission des rapports :</label>
                <input type="date" id="date_limite_soumission_rapport" name="date_limite_soumission_rapport"
                       value="<?= e($parametres_generaux['date_limite_soumission_rapport'] ?? ''); ?>" required>
                <small class="form-help">Date après laquelle les soumissions peuvent entraîner des pénalités.</small>
            </div>
            <div class="form-group">
                <label for="delai_archivage_pv_mois">Délai d'archivage automatique des PV (en mois) :</label>
                <input type="number" id="delai_archivage_pv_mois" name="delai_archivage_pv_mois"
                       value="<?= e($parametres_generaux['delai_archivage_pv_mois'] ?? ''); ?>" min="1" required>
                <small class="form-help">Procès-verbaux validés seront archivés après cette période.</small>
            </div>

            <h3>Sécurité des Comptes</h3>
            <div class="form-group">
                <label for="duree_validite_jeton_securite_heures">Durée de validité des jetons de sécurité (en heures) :</label>
                <input type="number" id="duree_validite_jeton_securite_heures" name="duree_validite_jeton_securite_heures"
                       value="<?= e($parametres_generaux['duree_validite_jeton_securite_heures'] ?? ''); ?>" min="1" required>
                <small class="form-help">Pour les jetons de réinitialisation de mot de passe, validation d'email, etc.</small>
            </div>
            <div class="form-group">
                <label for="seuil_tentatives_connexion_echouees">Seuil de tentatives de connexion échouées avant blocage :</label>
                <input type="number" id="seuil_tentatives_connexion_echouees" name="seuil_tentatives_connexion_echouees"
                       value="<?= e($parametres_generaux['seuil_tentatives_connexion_echouees'] ?? ''); ?>" min="1" required>
                <small class="form-help">Nombre de tentatives invalides avant un blocage temporaire du compte.</small>
            </div>
            <div class="form-group">
                <label for="duree_blocage_compte_minutes">Durée du blocage temporaire de compte (en minutes) :</label>
                <input type="number" id="duree_blocage_compte_minutes" name="duree_blocage_compte_minutes"
                       value="<?= e($parametres_generaux['duree_blocage_compte_minutes'] ?? ''); ?>" min="1" required>
                <small class="form-help">Durée pendant laquelle un compte reste bloqué après avoir atteint le seuil de tentatives échouées.</small>
            </div>

            <h3>Politique de Pénalités</h3>
            <div class="form-group">
                <label for="penalite_retard_apres_an_1_montant">Montant pénalité si retard > 1 an :</label>
                <input type="number" id="penalite_retard_apres_an_1_montant" name="penalite_retard_apres_an_1_montant"
                       value="<?= e($parametres_generaux['penalite_retard_apres_an_1_montant'] ?? ''); ?>" min="0" required>
                <small class="form-help">Montant de la pénalité financière après 1 an de retard de soumission de rapport.</small>
            </div>
            <div class="form-group">
                <label for="penalite_retard_apres_an_2_montant">Montant pénalité si retard > 2 ans :</label>
                <input type="number" id="penalite_retard_apres_an_2_montant" name="penalite_retard_apres_an_2_montant"
                       value="<?= e($parametres_generaux['penalite_retard_apres_an_2_montant'] ?? ''); ?>" min="0" required>
                <small class="form-help">Montant de la pénalité financière après 2 ans de retard de soumission de rapport.</small>
            </div>
            <div class="form-group">
                <label for="type_penalite_defaut">Type de pénalité par défaut :</label>
                <select id="type_penalite_defaut" name="type_penalite_defaut">
                    <?php foreach ($types_penalites as $type): ?>
                        <option value="<?= e($type['code']); ?>"
                            <?= (isset($parametres_generaux['type_penalite_defaut']) && $parametres_generaux['type_penalite_defaut'] === $type['code']) ? 'selected' : ''; ?>>
                            <?= e($type['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Type de pénalité appliqué par défaut en cas de retard.</small>
            </div>

            <h3>Options de Processus Internes</h3>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="notifications_email_active" name="notifications_email_active"
                    <?= ($parametres_generaux['notifications_email_active'] ?? false) ? 'checked' : ''; ?>>
                <label for="notifications_email_active">Activer l'envoi d'emails de notification :</label>
                <small class="form-help">Désactiver cette option coupera tous les emails envoyés par le système.</small>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="validation_stage_requise" name="validation_stage_requise"
                    <?= ($parametres_generaux['validation_stage_requise'] ?? false) ? 'checked' : ''; ?>>
                <label for="validation_stage_requise">Validation de stage requise avant soumission du rapport :</label>
                <small class="form-help">Exige que le stage soit validé par la scolarité avant toute soumission de rapport.</small>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">save</span>
                Sauvegarder les Paramètres Généraux
            </button>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript pour la gestion des formulaires
        const form = document.getElementById('formParametresGeneraux');
        if (form) {
            form.addEventListener('submit', function(event) {
                // Exemple de validation front-end simple
                const dateLimite = document.getElementById('date_limite_soumission_rapport').value;
                if (!dateLimite) {
                    alert('La date limite de soumission est obligatoire.');
                    event.preventDefault();
                    return;
                }

                const dureeJeton = parseInt(document.getElementById('duree_validite_jeton_securite_heures').value);
                if (isNaN(dureeJeton) || dureeJeton < 1) {
                    alert('La durée de validité des jetons doit être un nombre positif.');
                    event.preventDefault();
                    return;
                }

                // Pour les checkboxes, assurez-vous que la valeur est envoyée correctement
                // Si le checkbox n'est pas coché, son nom ne sera pas envoyé dans FormData.
                // On peut ajouter des inputs hidden pour garantir l'envoi d'une valeur 'false'
                // ou gérer côté serveur que l'absence signifie false.
                // Pour cet exemple simple, nous laissons tel quel, le contrôleur PHP devrait gérer l'absence.

                console.log("Formulaire Paramètres Généraux soumis.");
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
    /* Styles spécifiques pour parametres_generaux.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés des vues précédentes */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Légèrement plus étroit pour ce type de formulaire */
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

    /* Titres de sous-sections dans le formulaire */
    .section-form h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-top: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        border-bottom: 1px dashed var(--border-medium);
        padding-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
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
    .form-group input[type="number"],
    .form-group input[type="date"],
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%; /* S'assurer qu'ils prennent toute la largeur */
        max-width: 400px; /* Limiter la largeur des inputs */
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

    /* Styles spécifiques pour les groupes de checkbox */
    .form-group.checkbox-group {
        flex-direction: row; /* Aligne label et checkbox sur la même ligne */
        align-items: center;
        gap: var(--spacing-md); /* Espacement entre checkbox et label */
        margin-bottom: var(--spacing-lg); /* Plus d'espace pour ces groupes */
    }

    .form-group.checkbox-group label {
        margin-bottom: 0;
        flex-grow: 1; /* Permet à l'étiquette de prendre l'espace restant */
        cursor: pointer; /* Indique qu'on peut cliquer sur le label pour la checkbox */
    }

    .form-group.checkbox-group input[type="checkbox"] {
        width: auto; /* Laisser le navigateur gérer la largeur du checkbox */
        margin: 0;
        transform: scale(1.2); /* Rendre le checkbox un peu plus visible */
    }

    /* Boutons de soumission */
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

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }