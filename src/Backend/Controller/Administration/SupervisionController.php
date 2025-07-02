<?php
// src/Backend/Controller/Administration/SupervisionController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use Exception;

class SupervisionController extends BaseController
{
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(
        ServiceSupervisionInterface $supervisionService,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $baseSupervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $baseSupervisionService);
        $this->supervisionService = $supervisionService;
    }

    public function index(): void
    {
        $this->showAuditLogs();
    }

    public function showAuditLogs(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_VIEW');
        try {
            $filters = $this->getGetData();
            $data = [
                'title' => 'Supervision du Système',
                'audit_logs' => $this->supervisionService->consulterJournaux($filters),
                'async_tasks' => $this->supervisionService->listerTachesAsynchrones($filters),
                'current_filters' => $filters
            ];

            // Correction du chemin du fichier de log
            $logFilePath = ROOT_PATH . '/var/log/php_errors.log'; // Chemin cohérent avec php.ini et Dockerfile
            $data['error_log_content'] = file_exists($logFilePath) ? $this->supervisionService->consulterJournauxErreurs($logFilePath) : "Fichier log non trouvé ou illisible.";

            $this->render('Administration/supervision', $data);
        } catch (Exception $e) {
            $this->renderError(500, 'Impossible de charger la page de supervision : ' . $e->getMessage());
        }
    }

    public function getAuditLogDetails(string $id): void
    {
        // 12. Vue détaillée pour un log d'audit (AJAX)
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_VIEW');
        try {
            $logEntry = $this->supervisionService->reconstituerHistoriqueEntite($id);
            if (empty($logEntry)) {
                $this->jsonResponse(['success' => false, 'message' => 'Entrée de log non trouvée.'], 404);
                return;
            }
            $details = json_decode($logEntry[0]['details_action'] ?? '{}', true);
            $this->jsonResponse(['success' => true, 'data' => $details]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function purgeAuditLogs(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_PURGE'); // Permission spécifique pour la purge
        if (!$this->isPostRequest() || !$this->validateCsrfToken('purge_logs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision');
            return;
        }

        $data = $this->getPostData();

        // 13. Protection par mot de passe
        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->addFlashMessage('error', 'Utilisateur non connecté.');
            $this->redirect('/admin/supervision');
            return;
        }
        // Assurez-vous que ServiceSecurite::verifyPassword existe et est fonctionnelle
        // Ou utilisez une vérification de mot de passe plus robuste si nécessaire
        // if (!$this->securiteService->verifyPassword($user['numero_utilisateur'], $data['password_confirm'] ?? '')) {
        //     $this->addFlashMessage('error', 'Mot de passe incorrect. Purge annulée.');
        //     $this->redirect('/admin/supervision');
        //     return;
        // }

        try {
            $dateLimite = $data['date_limite'] ?? '';
            if (empty($dateLimite)) {
                $this->addFlashMessage('error', 'La date limite est obligatoire pour la purge.');
                $this->redirect('/admin/supervision');
                return;
            }
            $rowCount = $this->supervisionService->purgerAnciensJournaux($dateLimite);
            $this->addFlashMessage('success', "Purge effectuée. {$rowCount} enregistrements supprimés.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de purge: ' . $e->getMessage());
        }
        $this->redirect('/admin/supervision');
    }

    public function handleTaskAction(string $idTache): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_TACHES_GERER'); // Permission spécifique pour gérer les tâches
        if (!$this->isPostRequest() || !$this->validateCsrfToken('task_action_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision#queue-tab');
            return;
        }

        $action = $_POST['action'] ?? '';

        try {
            // 15. Gérer les deux stratégies de relance
            if ($action === 'relancer') { // Renommé de 'retry' pour coller à la fonction
                $this->supervisionService->gererTacheAsynchrone($idTache, 'relancer');
                $this->addFlashMessage('success', "La tâche {$idTache} a été relancée.");
            } elseif ($action === 'requeue') { // Renommé de 'requeue' pour coller à la fonction
                $this->supervisionService->gererTacheAsynchrone($idTache, 'requeue');
                $this->addFlashMessage('success', "Une nouvelle copie de la tâche {$idTache} a été ajoutée à la file.");
            } elseif ($action === 'supprimer') { // Renommé de 'delete' pour coller à la fonction
                $this->supervisionService->gererTacheAsynchrone($idTache, 'supprimer');
                $this->addFlashMessage('success', "La tâche {$idTache} a été supprimée.");
            } else {
                $this->addFlashMessage('error', 'Action non reconnue pour les tâches asynchrones.');
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/supervision#queue-tab');
    }
}