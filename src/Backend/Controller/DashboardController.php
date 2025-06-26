<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\AuthenticationException; // Si requireLogin() renvoie des exceptions

class DashboardController extends BaseController
{
    // Les services authService et permissionService sont déjà dans BaseController, pas besoin de les redéclarer ici
    // à moins d'avoir besoin de les réassigner pour un accès plus direct.

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
    }

    /**
     * Affiche le tableau de bord général, en adaptant le contenu et le menu au rôle de l'utilisateur.
     */
    public function index(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté pour accéder au tableau de bord

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { // Ne devrait pas arriver si requireLogin() fonctionne
                throw new AuthenticationException("Utilisateur non connecté ou session invalide.");
            }

            $userType = $currentUser['id_type_utilisateur']; // Ex: 'TYPE_ADMIN', 'TYPE_ETUD', 'TYPE_ENS', 'TYPE_PERS_ADMIN'
            $userRoleLabel = $this->getUserRoleLabel($userType);

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
        if (in_array('TRAIT_ADMIN_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Admin Dashboard', 'url' => '/dashboard/admin', 'icon' => 'fas fa-cogs'];
        }
        if (in_array('TRAIT_ETUDIANT_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Mon Espace Étudiant', 'url' => '/dashboard/etudiant', 'icon' => 'fas fa-user-graduate'];
        }
        if (in_array('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Mon Espace Personnel', 'url' => '/dashboard/personnel-admin', 'icon' => 'fas fa-user-tie'];
        }
        if (in_array('TRAIT_COMMISSION_DASHBOARD_ACCEDER', $userPermissions)) {
            $menu[] = ['label' => 'Mon Espace Commission', 'url' => '/dashboard/commission', 'icon' => 'fas fa-users-cog'];
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
            case 'TYPE_ADMIN':
                // $reportingService = $this->container->get(ServiceReportingAdmin::class); // Si le conteneur était accessible ici
                // $data['global_stats'] = $reportingService->genererStatistiquesUtilisation();
                // Pour cet exemple, les contrôleurs spécifiques de Dashboard (AdminDashboardController) récupéreront leurs propres données.
                // Ici, nous ne retournons que les données qui sont partagées ou triviales.
                break;
            case 'TYPE_ETUD':
                // Simuler l'appel à ServiceRapport
                // $rapportService = $this->container->get(ServiceRapport::class);
                // $data['current_rapport_status'] = $rapportService->recupererInformationsRapportComplet($currentUser['numero_utilisateur']);
                // Encore une fois, les contrôleurs spécifiques (EtudiantDashboardController) feront le travail.
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