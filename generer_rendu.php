<?php
/**
 * Script pour créer l'arborescence de dossiers et fichiers PHP conforme à la structure fournie.
 * Chaque fichier est créé s'il n'existe pas déjà, avec la balise PHP d'ouverture et rien d'autre.
 */

$files = [
    'src/Frontend/views/layout/app.php',
    'src/Frontend/views/layout/layout_auth.php',

    'src/Frontend/views/_partials/_header.php',
    'src/Frontend/views/_partials/_sidebar.php',
    'src/Frontend/views/_partials/_flash_messages.php',
    'src/Frontend/views/_partials/_pagination.php',
    'src/Frontend/views/_partials/_modal_confirm.php',
    'src/Frontend/views/_partials/_breadcrumb.php',

    'src/Frontend/views/Auth/login.php',
    'src/Frontend/views/Auth/forgot_password.php',
    'src/Frontend/views/Auth/reset_password.php',
    'src/Frontend/views/Auth/setup_2fa.php',
    'src/Frontend/views/Auth/verify_2fa.php',

    'src/Frontend/views/common/dashboard_redirect.php',
    'src/Frontend/views/common/profile.php',
    'src/Frontend/views/common/notifications.php',
    'src/Frontend/views/common/chat.php',

    'src/Frontend/views/Administration/dashboard.php',

    'src/Frontend/views/Administration/Utilisateurs/index.php',
    'src/Frontend/views/Administration/Utilisateurs/form.php',
    'src/Frontend/views/Administration/Utilisateurs/import.php',

    'src/Frontend/views/Administration/Habilitations/index.php',
    'src/Frontend/views/Administration/Habilitations/_tab_groupes.php',
    'src/Frontend/views/Administration/Habilitations/_tab_traitements.php',
    'src/Frontend/views/Administration/Habilitations/_tab_rattachements.php',

    'src/Frontend/views/Administration/Configuration/index.php',
    'src/Frontend/views/Administration/Configuration/_tab_parametres.php',
    'src/Frontend/views/Administration/Configuration/_tab_annees.php',
    'src/Frontend/views/Administration/Configuration/_tab_modeles_docs.php',

    'src/Frontend/views/Administration/Referentiels/index.php',
    'src/Frontend/views/Administration/Referentiels/crud.php',

    'src/Frontend/views/Administration/Supervision/index.php',
    'src/Frontend/views/Administration/Supervision/_tab_audit.php',
    'src/Frontend/views/Administration/Supervision/_tab_logs.php',
    'src/Frontend/views/Administration/Supervision/_tab_queue.php',

    'src/Frontend/views/Administration/Reporting/index.php',
    'src/Frontend/views/Administration/Reporting/view.php',

    'src/Frontend/views/Administration/TransitionRole/index.php',
    'src/Frontend/views/Administration/TransitionRole/_tab_delegations.php',
    'src/Frontend/views/Administration/TransitionRole/_tab_taches_orphelines.php',

    'src/Frontend/views/Etudiant/dashboard.php',

    'src/Frontend/views/Etudiant/Rapport/choix_modele.php',
    'src/Frontend/views/Etudiant/Rapport/redaction.php',
    'src/Frontend/views/Etudiant/Rapport/suivi.php',

    'src/Frontend/views/Etudiant/Reclamation/form.php',
    'src/Frontend/views/Etudiant/Reclamation/suivi.php',

    'src/Frontend/views/Commission/dashboard.php',
    'src/Frontend/views/Commission/liste_rapports.php',
    'src/Frontend/views/Commission/details_rapport.php',
    'src/Frontend/views/Commission/redaction_pv.php',

    'src/Frontend/views/PersonnelAdministratif/dashboard.php',

    'src/Frontend/views/PersonnelAdministratif/Conformite/liste_rapports.php',
    'src/Frontend/views/PersonnelAdministratif/Conformite/verification.php',

    'src/Frontend/views/PersonnelAdministratif/Scolarite/gestion_dossiers.php',
    'src/Frontend/views/PersonnelAdministratif/Scolarite/_details_dossier.php',

    'src/Frontend/views/errors/403.php',
    'src/Frontend/views/errors/404.php',
    'src/Frontend/views/errors/500.php',
];

foreach ($files as $file) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    if (!file_exists($file)) {
        file_put_contents($file, "<?php\n");
    }
}

echo "Arborescence créée avec succès.\n";