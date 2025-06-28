<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\AuthenticationException; // Si requireLogin() renvoie des exceptions
use App\Backend\Service\Notification\ServiceNotification;

class DashboardController extends BaseController
{
    // Les services authService et permissionService sont déjà dans BaseController, pas besoin de les redéclarer ici
    // à moins d'avoir besoin de les réassigner pour un accès plus direct.

    private ServiceNotification $notificationService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator,
        ServiceNotification $notificationService // Injecter le service
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->notificationService = $notificationService; // Assigner le service
    }

    /**
     * Affiche le tableau de bord général, en adaptant le contenu et le menu au rôle de l'utilisateur.
     */
    public function index(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté pour accéder au tableau de bord

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet();
            if (!$currentUser) { // Ne devrait pas arriver si requireLogin() fonctionne
                throw new AuthenticationException("Utilisateur non connecté ou session invalide.");
            }

            $userType = $currentUser['id_type_utilisateur']; // Ex: 'TYPE_ADMIN', 'TYPE_ETUD', 'TYPE_ENS', 'TYPE_PERS_ADMIN'
            $userRoleLabel = $this->getUserRoleLabel($userType);

            // --- Stockage des informations clés dans la session pour le header ---
            $_SESSION['user_data'] = $currentUser; // Données complètes de l'utilisateur
            $_SESSION['user_role_label'] = $userRoleLabel; // Libellé du rôle pour l'affichage

            // Récupérer le nombre de notifications non lues
            $notificationCount = $this->notificationService->compterNotificationsNonLues($currentUser['numero_utilisateur']);
            $_SESSION['notification_count'] = $notificationCount; // Compte des notifications pour le header
            // --- Fin du stockage pour le header ---

            // Récupérer les éléments de menu dynamiquement basés sur les permissions de l'utilisateur
            $menuItems = $this->getMenuItemsForUserPermissions();

            // Récupérer le contenu et les données spécifiques au rôle
            $dashboardContentData = $this->getDashboardDataForRole($userType, $currentUser);
            $dashboardContentView = $this->getDashboardContentViewForRole($userType);

            $data = [
                'page_title' => "Tableau de Bord " . $userRoleLabel,
                'current_user' => $currentUser, // Passer les données de l'utilisateur à la vue/layout
                'menu_items' => $menuItems,
                'dashboard_content_view' => $dashboardContentView, // Chemin de la sous-vue spécifique au rôle
                'dashboard_specific_data' => $dashboardContentData, // Données à passer à la sous-vue
            ];

            // Le layout principal (app.php) va inclure le menu et le header, puis le contenu spécifique du dashboard.
            $this->render('common/dashboard', $data); // La vue 'common/dashboard' agira comme un conteneur
        } catch (AuthenticationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login'); // Rediriger vers la page de login en cas de problème d'authentification
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors du chargement de votre tableau de bord: ' . $e->getMessage());
            error_log("Dashboard error for user " . ($currentUser['numero_utilisateur'] ?? 'N/A') . ": " . $e->getMessage());
            $this->redirect('/login'); // Rediriger en cas d'erreur grave
        }
    }

    /**
     * Retourne le libellé du rôle de l'utilisateur pour l'affichage.
     * @param string $userType L'ID du type d'utilisateur.
     * @return string Le libellé du rôle.
     */
    private function getUserRoleLabel(string $userType): string
    {
        $typeUser = $this->permissionService->recupererTypeUtilisateurParCode($userType);
        return $typeUser['libelle_type_utilisateur'] ?? 'Utilisateur';
    }

    /**
     * Récupère les éléments de menu pertinents en fonction des permissions de l'utilisateur connecté.
     * @return array Tableau des éléments de menu.
     */
    private function getMenuItemsForUserPermissions(): array
    {
        $menu = [];
        // Accéder aux permissions de l'utilisateur stockées en session
        $userPermissions = $_SESSION['user_permissions'] ?? [];

        // Définir les éléments de menu conditionnellement
        // Admin
        if (in_array('TRAIT_ADMIN_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Tableau de Bord Admin', 'url' => '/dashboard/admin', 'icon' => 'fas fa-cogs'];
        }
        if (in_array('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Gestion Utilisateurs', 'url' => '/dashboard/admin/utilisateurs', 'icon' => 'fas fa-users'];
        }
        if (in_array('TRAIT_ADMIN_HABILITATIONS_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Habilitations', 'url' => '/dashboard/admin/habilitations', 'icon' => 'fas fa-shield-alt'];
        }
        if (in_array('TRAIT_ADMIN_CONFIG_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Configuration Système', 'url' => '/dashboard/admin/config', 'icon' => 'fas fa-wrench'];
        }
        if (in_array('TRAIT_ADMIN_GESTION_ACAD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Gestion Académique', 'url' => '/dashboard/admin/gestion-acad', 'icon' => 'fas fa-graduation-cap'];
        }
        if (in_array('TRAIT_ADMIN_SUPERVISION_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Supervision', 'url' => '/dashboard/admin/supervision', 'icon' => 'fas fa-eye'];
        }
        if (in_array('TRAIT_ADMIN_REPORTING_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Reporting', 'url' => '/dashboard/admin/reporting', 'icon' => 'fas fa-chart-bar'];
        }

        // Étudiant
        if (in_array('TRAIT_ETUDIANT_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Mon Tableau de Bord', 'url' => '/dashboard/etudiant', 'icon' => 'fas fa-user-graduate'];
        }
        if (in_array('TRAIT_ETUDIANT_PROFIL_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Mon Profil', 'url' => '/dashboard/profile', 'icon' => 'fas fa-id-card']; // Lien générique vers le profil
        }
        if (in_array('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', $userPermissions) || in_array('TRAIT_ETUDIANT_RAPPORT_SUIVRE', $userPermissions)) {
            $menu[] = ['label' => 'Mon Rapport', 'url' => '/dashboard/etudiant/rapport', 'icon' => 'fas fa-file-alt'];
        }
        if (in_array('TRAIT_ETUDIANT_RECLAMATION_CREER', $userPermissions) || in_array('TRAIT_ETUDIANT_RECLAMATION_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Mes Réclamations', 'url' => '/dashboard/etudiant/reclamation', 'icon' => 'fas fa-exclamation-circle'];
        }
        if (in_array('TRAIT_ETUDIANT_DOCUMENTS_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Mes Documents', 'url' => '/dashboard/etudiant/documents', 'icon' => 'fas fa-folder-open'];
        }
        if (in_array('TRAIT_ETUDIANT_RESSOURCES_CONSULTER', $userPermissions)) {
            $menu[] = ['label' => 'Ressources & Aide', 'url' => '/dashboard/etudiant/ressources', 'icon' => 'fas fa-book'];
        }

        // Personnel Administratif
        if (in_array('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Tableau de Bord Personnel', 'url' => '/dashboard/personnel-admin', 'icon' => 'fas fa-user-tie'];
        }
        if (in_array('TRAIT_PERS_ADMIN_CONFORMITE_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Vérification Conformité', 'url' => '/dashboard/personnel-admin/conformite', 'icon' => 'fas fa-check-double'];
        }
        if (in_array('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Gestion Scolarité', 'url' => '/dashboard/personnel-admin/scolarite', 'icon' => 'fas fa-user-graduate'];
        }
        if (in_array('TRAIT_PERS_ADMIN_COMMUNICATION_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Messagerie Interne', 'url' => '/dashboard/personnel-admin/communication', 'icon' => 'fas fa-comments'];
        }

        // Commission
        if (in_array('TRAIT_COMMISSION_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Tableau de Bord Commission', 'url' => '/dashboard/commission', 'icon' => 'fas fa-gavel'];
        }
        if (in_array('TRAIT_COMMISSION_VALIDATION_RAPPORT_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Rapports à Valider', 'url' => '/dashboard/commission/rapports', 'icon' => 'fas fa-file-signature'];
        }
        if (in_array('TRAIT_COMMISSION_PV_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Gestion PV', 'url' => '/dashboard/commission/pv', 'icon' => 'fas fa-scroll'];
        }
        if (in_array('TRAIT_COMMISSION_CORRECTION_LISTER', $userPermissions)) {
            $menu[] = ['label' => 'Corrections Rapports', 'url' => '/dashboard/commission/corrections', 'icon' => 'fas fa-pencil-alt'];
        }
        if (in_array('TRAIT_COMMISSION_COMMUNICATION_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Messagerie Commission', 'url' => '/dashboard/commission/communication', 'icon' => 'fas fa-comments'];
        }
        if (in_array('TRAIT_COMMISSION_HISTORIQUE_CONSULTER', $userPermissions)) {
            $menu[] = ['label' => 'Historique Commission', 'url' => '/dashboard/commission/historique', 'icon' => 'fas fa-history'];
        }
        // Pour les enseignants, un lien direct vers le profil également
        // Note: Cette condition doit être basée sur le type d'utilisateur de l'enseignant, pas directement sur les permissions de TRAIT_ETUDIANT_PROFIL_ACCEDER
        $currentUserType = $this->authService->getUtilisateurConnecteComplet()['id_type_utilisateur'] ?? '';
        if ($currentUserType === 'TYPE_ENS') {
            $menu[] = ['label' => 'Mon Profil', 'url' => '/dashboard/profile', 'icon' => 'fas fa-id-card']; // Lien générique vers le profil
        }
        // Ajoutez d'autres éléments de menu basés sur d'autres permissions
        // Ex: if (in_array('TRAIT_COMMISSION_PV_LISTER', $userPermissions)) { $menu[] = ['label' => 'Gérer les PV', 'url' => '/dashboard/commission/pv']; }
        // C'est une simplification. Un système de menu plus élaboré peut être configuré dans un fichier séparé
        // et être filtré ici.

        return $menu;
    }

    /**
     * Récupère les données spécifiques au tableau de bord pour un rôle donné.
     * @param string $userType L'ID du type d'utilisateur.
     * @param array $currentUser Les données de l'utilisateur connecté.
     * @return array Les données spécifiques au tableau de bord.
     */
    private function getDashboardDataForRole(string $userType, array $currentUser): array
    {
        $data = [];
        // Exemple d'injection de services spécifiques ici si nécessaire, ou les contrôleurs spécifiques s'en chargeront
        // Pour des données globales ou simples, on peut appeler ici.

        switch ($userType) {
            case 'TYPE_ADMIN_SYS': // Utilisez 'TYPE_ADMIN_SYS' comme type exact pour l'admin système
                // Les services de Reporting et Supervision sont accessibles via $this->reportingService et $this->supervisionService
                // Assurez-vous que ces services sont injectés dans le constructeur de DashboardController et assignés.
                // Exemple : $data['statistiques_rapports'] = $this->reportingService->genererRapportTauxValidation(date('Y'));
                // $data['global_rapports_stats'] = $this->supervisionService->obtenirStatistiquesGlobalesRapports();
                break;
            case 'TYPE_ETUD':
                // Simuler l'appel à ServiceRapport
                // $rapportService = $this->container->get(ServiceRapport::class);
                // $data['current_rapport_status'] = $rapportService->recupererInformationsRapportComplet($currentUser['numero_utilisateur']);
                break;
            case 'TYPE_PERS_ADMIN':
                // $data['rapports_a_verifier_conformite'] = $this->conformiteService->recupererRapportsEnAttenteDeVerification();
                // $data['reclamations_en_attente'] = $this->reclamationService->recupererReclamationsEnAttente();
                break;
            case 'TYPE_ENS':
                // $data['rapports_a_traiter_commission'] = $this->commissionService->recupererRapportsAssignedToJury($currentUser['numero_utilisateur']);
                // $data['pv_a_valider'] = $this->commissionService->listerPvEnAttenteDeValidationParEnseignant($currentUser['numero_utilisateur']);
                break;
            // ... autres rôles
        }
        return $data;
    }

    /**
     * Retourne le chemin de la vue de contenu spécifique au rôle.
     * @param string $userType L'ID du type d'utilisateur.
     * @return string Le chemin de la sous-vue (ex: 'Administration/dashboard_admin').
     */
    private function getDashboardContentViewForRole(string $userType): string
    {
        return match($userType) {
            'TYPE_ADMIN' => 'Administration/dashboard_admin',
            'TYPE_ETUD' => 'Etudiant/dashboard_etudiant',
            'TYPE_PERS_ADMIN' => 'PersonnelAdministratif/dashboard_personnel',
            'TYPE_ENS' => 'Commission/dashboard_commission', // Si les enseignants sont uniquement membres de commission
            default => 'common/default_dashboard_content' // Vue par défaut si le rôle n'est pas géré explicitement
        };
    }
}