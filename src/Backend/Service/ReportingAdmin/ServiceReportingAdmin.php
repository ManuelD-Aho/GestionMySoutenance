<?php

namespace App\Backend\Service\ReportingAdmin;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Pister;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceReportingAdmin implements ServiceReportingAdminInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private CompteRendu $compteRenduModel;
    private Utilisateur $utilisateurModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private Enregistrer $enregistrerModel;
    private Pister $pisterModel;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(
        PDO $db,
        RapportEtudiant $rapportEtudiantModel,
        CompteRendu $compteRenduModel,
        Utilisateur $utilisateurModel,
        AnneeAcademique $anneeAcademiqueModel,
        Enregistrer $enregistrerModel,
        Pister $pisterModel,
        ServiceSupervisionAdminInterface $supervisionService
    ) {
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->enregistrerModel = $enregistrerModel;
        $this->pisterModel = $pisterModel;
        $this->supervisionService = $supervisionService;
    }

    public function genererRapportTauxValidation(?string $idAnneeAcademique = null): array
    {
        $criteres = [];
        if ($idAnneeAcademique) {
            $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
            if (!$annee) {
                throw new ElementNonTrouveException("Année académique non trouvée.");
            }
            $criteres['date_soumission'] = ['operator' => 'BETWEEN', 'values' => ["{$annee['date_debut']} 00:00:00", "{$annee['date_fin']} 23:59:59"]];
        }

        $totalRapports = $this->rapportEtudiantModel->compterParCritere($criteres);
        $rapportsValides = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_VALID']));
        $rapportsRefuses = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_REFUSE']));
        $rapportsEnAttente = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_SOUMIS', 'RAP_EN_COMM', 'RAP_NON_CONF', 'RAP_CORRECT']]]));

        $tauxValidation = ($totalRapports > 0) ? round(($rapportsValides / $totalRapports) * 100, 2) : 0;
        $tauxRefus = ($totalRapports > 0) ? round(($rapportsRefuses / $totalRapports) * 100, 2) : 0;

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_RAPPORT_VALIDATION',
            "Rapport de taux de validation généré" . ($idAnneeAcademique ? " pour l'année {$idAnneeAcademique}." : ".")
        );

        return [
            'total_rapports' => $totalRapports,
            'rapports_valides' => $rapportsValides,
            'rapports_refuses' => $rapportsRefuses,
            'rapports_en_attente' => $rapportsEnAttente,
            'taux_validation' => $tauxValidation,
            'taux_refus' => $tauxRefus,
            'annee_concernee' => $idAnneeAcademique ?? 'Toutes'
        ];
    }

    public function genererRapportDelaisMoyensParEtape(?string $idAnneeAcademique = null): array
    {
        $pdo = $this->enregistrerModel->getDb();
        $sql = "
            SELECT 
                AVG(TIMESTAMPDIFF(SECOND, t1.date_action, t2.date_action)) as delai_soumission_conformite,
                AVG(TIMESTAMPDIFF(SECOND, t2.date_action, t3.date_action)) as delai_conformite_decision
            FROM enregistrer t1
            JOIN enregistrer t2 ON t1.id_entite_concernee = t2.id_entite_concernee AND t2.id_action = 'VERIF_CONFORMITE_RAPPORT'
            JOIN enregistrer t3 ON t1.id_entite_concernee = t3.id_entite_concernee AND t3.id_action = 'FINALISATION_DECISION_RAPPORT'
            WHERE t1.id_action = 'SOUMISSION_RAPPORT'
        ";

        if ($idAnneeAcademique) {
            $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
            if (!$annee) {
                throw new ElementNonTrouveException("Année académique non trouvée.");
            }
            $sql .= " AND t1.date_action BETWEEN '{$annee['date_debut']} 00:00:00' AND '{$annee['date_fin']} 23:59:59'";
        }

        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_RAPPORT_DELAIS',
            "Rapport des délais moyens généré."
        );

        return [
            'soumission_a_conformite_heures' => $result['delai_soumission_conformite'] ? round($result['delai_soumission_conformite'] / 3600, 2) : 0,
            'conformite_a_decision_jours' => $result['delai_conformite_decision'] ? round($result['delai_conformite_decision'] / 86400, 2) : 0,
        ];
    }

    public function genererStatistiquesUtilisation(): array
    {
        $totalUsers = $this->utilisateurModel->compterParCritere([]);
        $usersByRole = $this->utilisateurModel->trouverTout(['id_type_utilisateur']);
        $userCountsByType = array_count_values(array_column($usersByRole, 'id_type_utilisateur'));

        $totalActionsLogged = $this->enregistrerModel->compterParCritere([]);
        $recentLogins = $this->enregistrerModel->compterParCritere(['id_action' => 'SUCCES_LOGIN', 'date_action' => ['operator' => '>', 'value' => (new \DateTime())->modify('-7 days')->format('Y-m-d H:i:s')]]);

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_RAPPORT_UTILISATION',
            "Rapport des statistiques d'utilisation généré."
        );

        return [
            'total_utilisateurs' => $totalUsers,
            'utilisateurs_par_type' => $userCountsByType,
            'total_actions_journalisees' => $totalActionsLogged,
            'connexions_recentes_7j' => $recentLogins,
        ];
    }
}