<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class SupervisionController extends BaseController
{
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(
        ServiceAuthentication   $authService,
        ServicePermissions      $permissionService,
        FormValidator           $validator,
        ServiceSupervisionAdmin $supervisionService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->supervisionService = $supervisionService;
    }

    /**
     * Affiche le tableau de bord de supervision (vue principale des journaux, workflows, maintenance).
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_ACCEDER'); // Permission générale d'accès à la supervision

        try {
            // Obtenir les statistiques globales des rapports
            $globalRapportsStats = $this->supervisionService->obtenirStatistiquesGlobalesRapports();

            $data = [
                'page_title' => 'Supervision du Système',
                'global_rapports_stats' => $globalRapportsStats,
                'last_audit_logs' => $this->supervisionService->consulterJournauxActionsUtilisateurs([], 10), // 10 derniers logs
                'last_access_traces' => $this->supervisionService->consulterTracesAccesFonctionnalites([], 10), // 10 dernières traces
                // Ajoutez des liens vers les sous-sections de supervision
                'sections' => [
                    ['label' => 'Journaux d\'Audit', 'url' => '/dashboard/admin/supervision/journaux-audit'],
                    ['label' => 'Suivi des Workflows', 'url' => '/dashboard/admin/supervision/suivi-workflows'],
                    ['label' => 'Maintenance', 'url' => '/dashboard/admin/supervision/maintenance'],
                ]
            ];
            $this->render('Administration/Supervision/index', $data); // Vue principale pour la supervision
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de la supervision: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    /**
     * Affiche les journaux d'audit des actions utilisateurs.
     */
    public function showAuditLogs(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_JOURNAUX_AUDIT_VOIR'); // Permission spécifique pour les journaux d'audit

        try {
            $page = (int) $this->getRequestData('page', 1);
            $limit = 50;
            $filters = [
                'numero_utilisateur' => $this->getRequestData('user_id', null),
                'id_action' => $this->getRequestData('action_id', null),
                'date_action' => $this->getRequestData('date_filter', null)
            ];
            $filters = array_filter($filters); // Supprimer les filtres vides

            $auditLogs = $this->supervisionService->consulterJournauxActionsUtilisateurs($filters, $limit, ($page - 1) * $limit);
            // Vous pouvez aussi récupérer le total pour la pagination
            // $totalLogs = $this->supervisionService->countJournauxActionsUtilisateurs($filters); // Méthode à créer si besoin

            $data = [
                'page_title' => 'Journaux d\'Audit des Actions',
                'logs' => $auditLogs,
                'current_page' => $page,
                'items_per_page' => $limit,
                'filters' => $filters,
                // 'total_items' => $totalLogs,
            ];
            $this->render('Administration/Supervision/journaux_audit', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des journaux d'audit: " . $e->getMessage());
            $this->redirect('/dashboard/admin/supervision');
        }
    }

    /**
     * Affiche le suivi des workflows (traces d'accès aux fonctionnalités).
     */
    public function showWorkflowTraces(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_SUIVI_WORKFLOWS_VOIR'); // Permission spécifique

        try {
            $page = (int) $this->getRequestData('page', 1);
            $limit = 50;
            $filters = [
                'numero_utilisateur' => $this->getRequestData('user_id', null),
                'id_traitement' => $this->getRequestData('traitement_id', null),
                'date_pister' => $this->getRequestData('date_filter', null)
            ];
            $filters = array_filter($filters);

            $accessTraces = $this->supervisionService->consulterTracesAccesFonctionnalites($filters, $limit, ($page - 1) * $limit);
            // $totalTraces = $this->supervisionService->countTracesAccesFonctionnalites($filters); // Méthode à créer

            $data = [
                'page_title' => 'Suivi des Workflows (Traces d\'Accès)',
                'traces' => $accessTraces,
                'current_page' => $page,
                'items_per_page' => $limit,
                'filters' => $filters,
                // 'total_items' => $totalTraces,
            ];
            $this->render('Administration/Supervision/suivi_workflows', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des traces d'accès: " . $e->getMessage());
            $this->redirect('/dashboard/admin/supervision');
        }
    }

    /**
     * Affiche les outils de maintenance du système.
     */
    public function showMaintenanceTools(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_MAINTENANCE_ACCEDER'); // Permission spécifique

        try {
            $pvEligiblesArchivage = $this->supervisionService->listerPvEligiblesArchivage(1); // PV de plus d'1 an

            $data = [
                'page_title' => 'Outils de Maintenance',
                'pv_eligibles_archivage' => $pvEligiblesArchivage,
                // Ajoutez d'autres informations sur l'état du système (ex: taille des logs, utilisation espace disque si pertinents)
            ];
            $this->render('Administration/Supervision/maintenance', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des outils de maintenance: " . $e->getMessage());
            $this->redirect('/dashboard/admin/supervision');
        }
    }

    /**
     * Déclenche l'archivage des PV.
     */
    public function archivePv(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_ARCHIVAGE_PV'); // Permission spécifique pour l'archivage

        $idPvToArchive = $this->getRequestData('id_pv_to_archive'); // Si on archive un PV spécifique depuis un formulaire
        $archiveAllEligible = (bool)$this->getRequestData('archive_all_eligible', false); // Option pour archiver tous les PV éligibles

        try {
            if ($idPvToArchive) {
                $this->supervisionService->archiverPv($idPvToArchive);
                $this->setFlashMessage('success', "Le PV {$idPvToArchive} a été archivé avec succès.");
            } elseif ($archiveAllEligible) {
                $eligiblePvs = $this->supervisionService->listerPvEligiblesArchivage(1);
                $countArchived = 0;
                foreach ($eligiblePvs as $pv) {
                    try {
                        if ($this->supervisionService->archiverPv($pv['id_compte_rendu'])) {
                            $countArchived++;
                        }
                    } catch (\Exception $e) {
                        $this->setFlashMessage('warning', "Échec de l'archivage du PV {$pv['id_compte_rendu']}: " . $e->getMessage());
                    }
                }
                $this->setFlashMessage('success', "{$countArchived} PV(s) éligible(s) ont été archivé(s).");
            } else {
                throw new OperationImpossibleException("Aucun PV spécifique ou option 'archiver tout' sélectionnée.");
            }
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible d\'archiver : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/supervision/maintenance');
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer
    // car les fonctionnalités spécifiques sont traitées par des méthodes dédiées.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}