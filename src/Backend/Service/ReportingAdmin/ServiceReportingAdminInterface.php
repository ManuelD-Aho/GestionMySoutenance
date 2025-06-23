<?php
namespace App\Backend\Service\ReportingAdmin;

interface ServiceReportingAdminInterface
{
    public function genererRapportTauxValidation(?string $idAnneeAcademique = null): array;
    public function genererRapportDelaisMoyensParEtape(): array;
    public function genererStatistiquesUtilisation(): array;
}