<?php
namespace App\Backend\Service\ReportingAdmin;

interface ServiceReportingAdminInterface
{
    /**
     * Génère un rapport sur les taux de validation des rapports étudiants.
     * @param string|null $idAnneeAcademique L'ID de l'année académique pour filtrer.
     * @return array Rapport agrégé.
     */
    public function genererRapportTauxValidation(?string $idAnneeAcademique = null): array;

    /**
     * Génère un rapport sur les délais moyens par étape du workflow de rapport.
     * @return array Tableau des délais moyens.
     */
    public function genererRapportDelaisMoyensParEtape(): array;

    /**
     * Génère des statistiques globales d'utilisation du système.
     * @return array Statistiques d'utilisation.
     */
    public function genererStatistiquesUtilisation(): array;
}