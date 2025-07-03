<?php
// src/Frontend/views/Etudiant/profil_etudiant.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données du profil de l'étudiant (proviennent du ProfilEtudiantController)
//
//
//

$student_profile = $data['student_profile'] ?? [
    'id' => 1,
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'email_principal' => 'jean.dupont@etu.com',
    'email_secondaire' => 'jean.d@example.com',
    'telephone' => '0712345678',
    'adresse' => '123 Rue de l\'Université, Abidjan',
    'date_naissance' => '2000-01-15',
    'matricule_etudiant' => 'ETU-2025-0001',
    'niveau_etude' => 'Master 2',
    'specialite' => 'MIAGE',
    'photo_profil' => '/assets/img/jean_dupont.jpg', // Chemin vers la photo réelle
    '2fa_active' => true, // Statut 2FA
];

// Message de succès/erreur de la session après une mise à jour
$flash_message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Mon Profil</h1>

    <?php if ($flash_message && $flash_message['type'] === 'success'): ?>
        <div class="alert alert-success">
            <span class="material-icons">check_circle</span> <?= e($flash_message['message']); ?>
        </div>
    <?php elseif ($flash_message && $flash_message['type'] === 'error'): ?>
        <div class="alert alert-error">
            <span class="material-icons">error</span> <?= e($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <section class="section-profile-info admin-card">
        <h2 class="section-title">Informations Personnelles et Administratives</h2>
        <div class="profile-header-overview">
            <div class="profile-avatar-display">
                <?php if ($student_profile['photo_profil']): ?>
                    <img src="<?= e($student_profile['photo_profil']); ?>" alt="Photo de profil" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <?= e(strtoupper(substr($student_profile['prenom'], 0, 1))); ?>
                    </div>
                <?php endif; ?>
                <a href="/etudiant/profile/upload-photo" class="btn btn-secondary-gray btn-sm mt-md">
                    <span class="material-icons">edit</span> Modifier Photo
                </a>
            </div>
            <div class="profile-basic-details">
                <h3><?= e($student_profile['prenom']) . ' ' . e($student_profile['nom']); ?></h3>
                <p>Matricule : <strong><?= e($student_profile['matricule_etudiant']); ?></strong></p>
                <p>Niveau d'Étude : <strong><?= e($student_profile['niveau_etude']); ?></strong></p>
                <?php if ($student_profile['specialite']): ?>
                    <p>Spécialité : <strong><?= e($student_profile['specialite']); ?></strong></p>
                <?php endif; ?>
            </div>
        </div>

        <form id="formProfileUpdate" action="/etudiant/profile/update" method="POST" class="mt-xl">
            <fieldset class="form-section">
                <legend>Coordonnées et Contact</legend>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="email_principal">Email Principal :</label>
                        <input type="email" id="email_principal" name="email_principal" value="<?= e($student_profile['email_principal']); ?>" required readonly>
                        <small class="form-help">Adresse email principale (non modifiable directement ici).</small>
                    </div>
                    <div class="form-group">
                        <label for="email_secondaire">Email Secondaire :</label>
                        <input type="email" id="email_secondaire" name="email_secondaire" value="<?= e($student_profile['email_secondaire'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone :</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= e($student_profile['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse :</label>
                        <input type="text" id="adresse" name="adresse" value="<?= e($student_profile['adresse'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date de Naissance :</label>
                        <input type="date" id="date_naissance" name="date_naissance" value="<?= e($student_profile['date_naissance'] ?? ''); ?>" readonly>
                        <small class="form-help">Contactez l'administration pour modifier cette information.</small>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-blue">
                    <span class="material-icons">save</span> Enregistrer les modifications
                </button>
            </div>
        </form>
    </section>

    <section class="section-security-settings admin-card mt-xl">
        <h2 class="section-title">Paramètres de Sécurité du Compte</h2>
        <div class="security-options-grid">
            <div class="security-option-item">
                <h3>Mot de Passe</h3>
                <p>Modifiez votre mot de passe pour renforcer la sécurité de votre compte.</p>
                <a href="/change-password" class="btn btn-secondary-gray btn-sm">
                    <span class="material-icons">vpn_key</span> Modifier mon mot de passe
                </a>
            </div>
            <div class="security-option-item">
                <h3>Authentification à Deux Facteurs (2FA)</h3>
                <?php if ($student_profile['2fa_active']): ?>
                    <p class="text-green"><span class="material-icons">check_circle</span> La 2FA est activée sur votre compte.</p>
                    <a href="/2fa/disable" class="btn btn-accent-red btn-sm">
                        <span class="material-icons">security_off</span> Désactiver la 2FA
                    </a>
                <?php else: ?>
                    <p class="text-muted"><span class="material-icons">warning_amber</span> La 2FA n'est pas activée. Nous recommandons de l'activer.</p>
                    <a href="/2fa/setup" class="btn btn-primary-blue btn-sm">
                        <span class="material-icons">security</span> Activer la 2FA
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formProfileUpdate = document.getElementById('formProfileUpdate');
        if (formProfileUpdate) {
            formProfileUpdate.addEventListener('submit', function(event) {
                const emailSecondaire = document.getElementById('email_secondaire').value.trim();
                const telephone = document.getElementById('telephone').value.trim();
                const adresse = document.getElementById('adresse').value.trim();

                // Simple validation d'email secondaire
                if (emailSecondaire && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailSecondaire)) {
                    alert('Veuillez saisir une adresse email secondaire valide ou laisser vide.');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire de mise à jour de profil soumis.");
            });
        }

        // Pas de gestion de message flash ici, car le layout app.php le gère globalement via DashboardHeader.showNotification
        // Sauf si vous avez des messages flash spécifiques à cette page que vous voulez afficher différemment.
    });
</script>

<style>
    /* Styles spécifiques pour profil_etudiant.php */
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

    /* Alertes (réutilisées) */
    .alert { /* Réutilisé des autres vues, y compris auth.css via style.css */
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
        background-color: var(--primary-white); /* Fond blanc pour les alertes dans le contenu */
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

    /* Profil Header Overview (Photo et infos principales) */
    .profile-header-overview {
        display: flex;
        flex-direction: column; /* Par défaut, pour les petits écrans */
        align-items: center;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px dashed var(--border-light);
    }

    @media (min-width: 600px) { /* Sur les écrans plus grands, mettez en ligne */
        .profile-header-overview {
            flex-direction: row;
            justify-content: flex-start;
        }
    }

    .profile-avatar-display {
        width: 120px;
        height: 120px;
        border-radius: var(--border-radius-full);
        overflow: hidden;
        border: 4px solid var(--primary-blue-light);
        box-shadow: var(--shadow-md);
        flex-shrink: 0; /* Empêche l'avatar de se compresser */
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--primary-white);
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
        color: var(--text-white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-4xl); /* Très grande taille pour l'initiale */
        font-weight: var(--font-weight-bold);
    }

    .profile-basic-details {
        text-align: center; /* Par défaut pour les petits écrans */
        flex-grow: 1;
    }

    @media (min-width: 600px) {
        .profile-basic-details {
            text-align: left;
        }
    }

    .profile-basic-details h3 {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
    }

    .profile-basic-details p {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }

    .profile-basic-details strong {
        color: var(--text-primary);
    }

    /* Formulaires de mise à jour (informations personnelles) */
    fieldset.form-section { /* Réutilisé des formulaires admin */
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        background-color: var(--primary-white);
    }

    fieldset.form-section legend { /* Réutilisé */
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        font-weight: var(--font-weight-semibold);
        padding: 0 var(--spacing-xs);
        margin-left: var(--spacing-sm);
    }

    .form-grid { /* Réutilisé */
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
    }

    .form-group { /* Réutilisé */
        display: flex;
        flex-direction: column;
    }

    .form-group label { /* Réutilisé */
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="tel"],
    .form-group input[type="date"] { /* Réutilisé */
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .form-group input:focus { /* Réutilisé */
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-group input[readonly] {
        background-color: var(--primary-gray-light);
        cursor: not-allowed;
    }

    .form-help { /* Réutilisé */
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    .form-actions { /* Réutilisé */
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-top: var(--spacing-xl);
    }

    .btn { /* Réutilisé */
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

    .btn-primary-blue { /* Réutilisé */
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover { /* Réutilisé */
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    /* Paramètres de sécurité */
    .security-options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
        margin-top: var(--spacing-lg);
    }

    .security-option-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .security-option-item h3 {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
    }

    .security-option-item p {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-md);
        line-height: var(--line-height-normal);
    }
    .security-option-item p .material-icons {
        font-size: var(--font-size-base);
        vertical-align: middle;
        margin-right: var(--spacing-xs);
    }

    .text-green { color: var(--primary-green-dark); }
    .text-muted .material-icons { color: var(--text-light); }

    .btn-sm { /* Réutilisé */
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: var(--font-size-sm);
    }

    .btn-secondary-gray { /* Réutilisé */
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }
    .btn-secondary-gray:hover { /* Réutilisé */
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }

    .btn-accent-red { /* Pour désactiver 2FA */
        color: var(--text-white);
        background-color: var(--accent-red);
    }
    .btn-accent-red:hover {
        background-color: var(--accent-red-dark);
        box-shadow: var(--shadow-sm);
    }


    /* Utilitaires */
    .text-center { text-align: center; }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>