<?php
// Emplacement: src/Backend/Controller/Administration/ReportingController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Reporting\ServiceReportingInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class ReportingController extends BaseController
{
    private ServiceReportingInterface $reportingService;

    public function __construct(
        ServiceReportingInterface $reportingService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->reportingService = $reportingService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REPORTING_ACCEDER');
        try {
            $this->render('Administration/reporting/index', [
                'title' => 'Reporting et Statistiques',
                'available_reports' => $this->reportingService->getAvailableReports(),
                'recent_reports' => $this->reportingService->getRecentReports(),
                'csrf_token' => $this->generateCsrfToken('reporting_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function generate(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REPORTING_ACCEDER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('reporting_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/reporting');
            return;
        }

        $reportType = $_POST['report_type'] ?? '';
        $format = $_POST['format'] ?? 'html';
        $params = $_POST['params'] ?? [];

        try {
            $reportData = $this->reportingService->generateReport($reportType, $params);
            if ($format === 'html') {
                $this->render('Administration/reporting/view', [
                    'title' => "Rapport: " . $reportData['title'],
                    'report' => $reportData
                ]);
            } else {
                $this->reportingService->exportReport($reportType, $format, $reportData);
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de génération: ' . $e->getMessage());
            $this->redirect('/admin/reporting');
        }
    }
}