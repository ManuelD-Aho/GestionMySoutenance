<?php
namespace App\Backend\Service\ConfigurationSysteme;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\Ecue;
use App\Backend\Model\Grade;
use App\Backend\Model\Fonction;
use App\Backend\Model\Specialite;
use App\Backend\Model\StatutReclamationRef;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceConfigurationSysteme implements ServiceConfigurationSystemeInterface
{
    private AnneeAcademique $anneeAcademiqueModel;
    private TypeDocumentRef $typeDocumentRefModel;
    private NiveauEtude $niveauEtudeModel;
    private StatutPaiementRef $statutPaiementRefModel;
    private DecisionPassageRef $decisionPassageRefModel;
    private Ecue $ecueModel;
    private Grade $gradeModel;
    private Fonction $fonctionModel;
    private Specialite $specialiteModel;
    private StatutReclamationRef $statutReclamationRefModel;
    private StatutConformiteRef $statutConformiteRefModel;

    public function __construct(PDO $db)
    {
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->typeDocumentRefModel = new TypeDocumentRef($db);
        $this->niveauEtudeModel = new NiveauEtude($db);
        $this->statutPaiementRefModel = new StatutPaiementRef($db);
        $this->decisionPassageRefModel = new DecisionPassageRef($db);
        $this->ecueModel = new Ecue($db);
        $this->gradeModel = new Grade($db);
        $this->fonctionModel = new Fonction($db);
        $this->specialiteModel = new Specialite($db);
        $this->statutReclamationRefModel = new StatutReclamationRef($db);
        $this->statutConformiteRefModel = new StatutConformiteRef($db);
    }

    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool
    {
        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            $this->anneeAcademiqueModel->executerRequete("UPDATE annee_academique SET est_active = 0 WHERE est_active = 1");
            $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);
            $this->anneeAcademiqueModel->validerTransaction();
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        }
    }

    public function mettreAJourParametresGeneraux(array $parametres): bool { return true; }
    public function recupererParametresGeneraux(): array { return []; }
    public function listerAnneesAcademiques(): array { return $this->anneeAcademiqueModel->trouverTout(); }
    public function listerTypesDocument(): array { return $this->typeDocumentRefModel->trouverTout(); }
    public function listerNiveauxEtude(): array { return $this->niveauEtudeModel->trouverTout(); }
    public function listerStatutsPaiement(): array { return $this->statutPaiementRefModel->trouverTout(); }
    public function listerDecisionsPassage(): array { return $this->decisionPassageRefModel->trouverTout(); }
    public function listerEcues(): array { return $this->ecueModel->trouverTout(); }
    public function listerGrades(): array { return $this->gradeModel->trouverTout(); }
    public function listerFonctions(): array { return $this->fonctionModel->trouverTout(); }
    public function listerSpecialites(): array { return $this->specialiteModel->trouverTout(); }
    public function listerStatutsReclamation(): array { return $this->statutReclamationRefModel->trouverTout(); }
    public function listerStatutsConformite(): array { return $this->statutConformiteRefModel->trouverTout(); }

    public function creerAnneeAcademique(string $idAnneeAcademique, string $libelleAnneeAcademique, string $dateDebut, string $dateFin, bool $estActive): bool
    {
        return (bool) $this->anneeAcademiqueModel->creer(compact('idAnneeAcademique', 'libelleAnneeAcademique', 'dateDebut', 'dateFin', 'estActive'));
    }

    public function modifierAnneeAcademique(string $idAnneeAcademique, array $donnees): bool
    {
        return $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, $donnees);
    }

    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool
    {
        return $this->anneeAcademiqueModel->supprimerParIdentifiant($idAnneeAcademique);
    }

    public function recupererAnneeAcademiqueParId(string $idAnneeAcademique): ?array
    {
        return $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
    }
}