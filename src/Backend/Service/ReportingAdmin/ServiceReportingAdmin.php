<?php
namespace App\Backend\Service\ReportingAdmin;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Enregistrer; // Pour les statistiques d'actions
use App\Backend\Model\Pister; // Pour les traces d'accès
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Exception\OperationImpossibleException;

class ServiceReportingAdmin implements ServiceReportingAdminInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private CompteRendu $compteRenduModel;
    private Utilisateur $utilisateurModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private Enregistrer $enregistrerModel; // Nouveau pour audit
    private Pister $pisterModel; // Nouveau pour traces
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService)
    {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->compteRenduModel = new CompteRendu($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->enregistrerModel = new Enregistrer($db); // Initialisation
        $this->pisterModel = new Pister($db); // Initialisation
        $this->supervisionService = $supervisionService;
    }

    /**
     * Génère un rapport sur les taux de validation des rapports étudiants.
     * Inclut le nombre total de rapports, validés, refusés, en attente, etc.
     * @param string|null $idAnneeAcademique L'ID de l'année académique pour filtrer.
     * @return array Rapport agrégé.
     */
    public function genererRapportTauxValidation(?string $idAnneeAcademique = null): array
    {
        $criteres = [];
        if ($idAnneeAcademique) {
            // Si les rapports ont une FK vers annee_academique, ajoutez le critère ici.
            // Sinon, la date_soumission du rapport doit être utilisée avec les dates de l'année académique.
            $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
            if (!$annee) {
                throw new ElementNonTrouveException("Année académique non trouvée.");
            }
            $criteres['date_soumission'] = ['operator' => 'BETWEEN', 'values' => ["{$annee['date_debut']} 00:00:00", "{$annee['date_fin']} 23:59:59"]];
        }

        $totalRapports = $this->rapportEtudiantModel->compterParCritere($criteres);
        $rapportsValides = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_VALID']));
        $rapportsRefuses = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_REFUSE']));
        $rapportsEnAttente = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_SOUMIS', 'RAP_EN_COMM', 'RAP_NON_CONF', 'RAP_CORRECT']] ]));

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

    /**
     * Génère un rapport sur les délais moyens par étape du workflow de rapport.
     * @return array Tableau des délais moyens.
     */
    public function genererRapportDelaisMoyensParEtape(): array
    {
        // Cette fonctionnalité nécessite une logique complexe de calcul des durées entre les changements de statut.
        // La table `enregistrer` (audit) ou des champs de date dédiés dans `rapport_etudiant` seraient nécessaires.
        // Exemples de calculs:
        // - Délai Soumission -> Conformité (date_soumission de rapport_etudiant vs date_verification_conformite de approuver)
        // - Délai Conformité -> Décision Commission (date_verification_conformite de approuver vs date_vote du dernier vote de vote_commission)
        // - Délai Décision Commission -> PV Validé (date du dernier vote de vote_commission vs date_creation_pv de compte_rendu + date_validation de validation_pv)

        // Simulé pour l'exemple
        $delais = [
            'soumission_a_conformite_jours' => 'Non calculé', // Rapports en attente
            'conformite_a_decision_jours' => 'Non calculé',
            'decision_a_pv_valide_jours' => 'Non calculé'
        ];

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_RAPPORT_DELAIS',
            "Rapport des délais moyens généré."
        );

        return $delais;
    }

    /**
     * Génère des statistiques globales d'utilisation du système.
     * Ex: nombre d'utilisateurs par type, nombre d'actions journalisées, activités récentes.
     * @return array Statistiques d'utilisation.
     */
    public function genererStatistiquesUtilisation(): array
    {
        $totalUsers = $this->utilisateurModel->compterParCritere([]);
        $usersByRole = $this->utilisateurModel->trouverTout(['id_type_utilisateur']);
        $userCountsByType = array_count_values(array_column($usersByRole, 'id_type_utilisateur'));

        $totalActionsLogged = $this->enregistrerModel->compterParCritere([]);
        $recentLogins = $this->enregistrerModel->compterParCritere(['libelle_action' => 'SUCCES_LOGIN', 'date_action' => ['operator' => '>', 'value' => (new \DateTime())->modify('-7 days')->format('Y-m-d H:i:s')]]);

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