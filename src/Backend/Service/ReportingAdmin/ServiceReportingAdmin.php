<?php
namespace App\Backend\Service\ReportingAdmin;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Enregistrer;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceReportingAdmin implements ServiceReportingAdminInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private Utilisateur $utilisateurModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private Enregistrer $enregistrerModel;

    public function __construct(PDO $db)
    {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->enregistrerModel = new Enregistrer($db);
    }

    public function genererRapportTauxValidation(?string $idAnneeAcademique = null): array
    {
        $criteres = [];
        if ($idAnneeAcademique) {
            $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
            if (!$annee) throw new ElementNonTrouveException("Année académique non trouvée.");
            $criteres['date_soumission'] = ['operator' => 'BETWEEN', 'values' => [$annee['date_debut'], $annee['date_fin']]];
        }

        $totalRapports = $this->rapportEtudiantModel->compterParCritere($criteres);
        $rapportsValides = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_VALID']));
        $rapportsRefuses = $this->rapportEtudiantModel->compterParCritere(array_merge($criteres, ['id_statut_rapport' => 'RAP_REFUSE']));

        return [
            'total_rapports' => $totalRapports,
            'rapports_valides' => $rapportsValides,
            'rapports_refuses' => $rapportsRefuses,
            'taux_validation' => ($totalRapports > 0) ? round(($rapportsValides / $totalRapports) * 100, 2) : 0,
        ];
    }

    public function genererRapportDelaisMoyensParEtape(): array
    {
        // Logique complexe de calcul des délais à implémenter en utilisant la table 'enregistrer'
        return ['delai_moyen_conformite' => 'N/A', 'delai_moyen_commission' => 'N/A'];
    }

    public function genererStatistiquesUtilisation(): array
    {
        $totalUsers = $this->utilisateurModel->compterParCritere([]);
        $usersByRole = $this->utilisateurModel->executerRequete("SELECT id_type_utilisateur, COUNT(*) as count FROM utilisateur GROUP BY id_type_utilisateur")->fetchAll();

        return [
            'total_utilisateurs' => $totalUsers,
            'utilisateurs_par_type' => array_column($usersByRole, 'count', 'id_type_utilisateur'),
        ];
    }
}