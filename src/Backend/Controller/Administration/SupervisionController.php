<?php
// src/Backend/Controller/Administration/SupervisionController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator; // Assurez-vous que cette ligne est présente
use Exception;

class SupervisionController extends BaseController
{
    // Suppression de la déclaration de propriété $supervisionService
    // car elle est déjà disponible via BaseController::$supervisionService

    public function __construct(
        ServiceSupervisionInterface $supervisionService, // Injecté pour BaseController
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        FormValidator $validator // Ajout du FormValidator ici
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        // Pas besoin de réassigner $this->supervisionService ici
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

            $logFilePath = ROOT_PATH . '/var/log/php_errors.log';
            $data['error_log_content'] = file_exists($logFilePath) ? $this->supervisionService->consulterJournauxErreurs($logFilePath) : "Fichier log non trouvé ou illisible.";

            $this->render('Administration/supervision', $data);
        } catch (Exception $e) {
            $this->renderError(500, 'Impossible de charger la page de supervision : ' . $e->getMessage());
        }
    }

    public function getAuditLogDetails(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_VIEW');
        try {
            $logEntry = $this->supervisionService->reconstituerHistoriqueEntite($id);
            if (empty($logEntry)) {
                $this->jsonResponse(['success' => false, 'message' => 'Entrée de log non trouvée.'], 404);
                return; // Suppression de l'instruction inaccessible
            }
            $details = json_decode($logEntry[0]['details_action'] ?? '{}', true);
            $this->jsonResponse(['success' => true, 'data' => $details]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function purgeAuditLogs(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_PURGE');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('purge_logs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision');
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();

        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->addFlashMessage('error', 'Utilisateur non connecté.');
            $this->redirect('/admin/supervision');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $dateLimite = $data['date_limite'] ?? '';
            if (empty($dateLimite)) {
                $this->addFlashMessage('error', 'La date limite est obligatoire pour la purge.');
                $this->redirect('/admin/supervision');
                return; // Suppression de l'instruction inaccessible
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
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_TACHES_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('task_action_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision#queue-tab');
            return; // Suppression de l'instruction inaccessible
        }

        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'relancer') {
                $this->supervisionService->gererTacheAsynchrone($idTache, 'relancer');
                $this->addFlashMessage('success', "La tâche {$idTache} a été relancée.");
            } elseif ($action === 'requeue') {
                $this->supervisionService->gererTacheAsynchrone($idTache, 'requeue');
                $this->addFlashMessage('success', "Une nouvelle copie de la tâche {$idTache} a été ajoutée à la file.");
            } elseif ($action === 'supprimer') {
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