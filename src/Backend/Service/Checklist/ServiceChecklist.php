<?php
namespace App\Backend\Service\Checklist;

use PDO;
use App\Backend\Model\CritereConformiteRef;
use App\Backend\Model\ConformiteRapportDetails;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceChecklist implements ServiceChecklistInterface
{
    private CritereConformiteRef $critereModel;
    private ConformiteRapportDetails $detailsModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->critereModel = new CritereConformiteRef($db);
        $this->detailsModel = new ConformiteRapportDetails($db);
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function creerCritere(array $data): bool { return (bool) $this->critereModel->creer($data); }
    public function modifierCritere(string $idCritere, array $data): bool { return $this->critereModel->mettreAJourParIdentifiant($idCritere, $data); }
    public function supprimerCritere(string $idCritere): bool { return $this->critereModel->supprimerParIdentifiant($idCritere); }
    public function listerCriteresActifs(): array { return $this->critereModel->findAllActifs(); }

    public function enregistrerResultatsChecklist(string $idRapport, string $idVerificateur, array $resultats): bool
    {
        $this->detailsModel->commencerTransaction();
        try {
            foreach ($resultats as $idCritere => $details) {
                $idDetail = $this->idGenerator->generate('conformite_detail');
                $data = [
                    'id_conformite_detail' => $idDetail,
                    'id_rapport_etudiant' => $idRapport,
                    'id_critere' => $idCritere,
                    'statut_validation' => $details['statut'],
                    'commentaire' => $details['commentaire'] ?? null,
                    'date_verification' => date('Y-m-d H:i:s')
                ];
                $this->detailsModel->creer($data);
            }
            $this->detailsModel->validerTransaction();
            return true;
        } catch (\Exception $e) {
            $this->detailsModel->annulerTransaction();
            throw new OperationImpossibleException("Erreur lors de l'enregistrement de la checklist: " . $e->getMessage());
        }
    }

    public function getResultatsChecklistPourRapport(string $idRapport): array
    {
        return $this->detailsModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
    }
}