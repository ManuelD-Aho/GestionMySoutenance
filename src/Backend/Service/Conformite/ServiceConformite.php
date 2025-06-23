<?php
namespace App\Backend\Service\Conformite;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Approuver;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceConformite implements ServiceConformiteInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private Approuver $approuverModel;
    private ServiceNotificationInterface $notificationService;

    public function __construct(PDO $db, ServiceNotificationInterface $notificationService)
    {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->approuverModel = new Approuver($db);
        $this->notificationService = $notificationService;
    }

    public function traiterVerificationConformite(string $idRapportEtudiant, string $numeroPersonnelAdministratif, string $idStatutConformite, ?string $commentaireConformite): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) throw new OperationImpossibleException("Rapport non trouvé.");
        if ($idStatutConformite === 'CONF_NOK' && empty($commentaireConformite)) {
            throw new OperationImpossibleException("Un commentaire est requis pour un rapport non conforme.");
        }

        $this->approuverModel->commencerTransaction();
        try {
            $this->approuverModel->creer([
                'numero_personnel_administratif' => $numeroPersonnelAdministratif,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'id_statut_conformite' => $idStatutConformite,
                'commentaire_conformite' => $commentaireConformite,
                'date_verification_conformite' => date('Y-m-d H:i:s')
            ]);
            $newStatutRapport = ($idStatutConformite === 'CONF_OK') ? 'RAP_EN_COMM' : 'RAP_NON_CONF';
            $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutRapport]);
            $this->approuverModel->validerTransaction();

            if ($newStatutRapport === 'RAP_EN_COMM') {
                $this->notificationService->sendToGroup('GRP_COMMISSION', 'NOUVEAU_RAPPORT_A_EVALUER', ['rapport_id' => $idRapportEtudiant]);
            } else {
                $this->notificationService->send($rapport['numero_carte_etudiant'], 'RAPPORT_NON_CONFORME', ['commentaire' => $commentaireConformite]);
            }
            return true;
        } catch (\Exception $e) {
            $this->approuverModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererRapportsEnAttenteDeVerification(): array
    {
        return $this->rapportEtudiantModel->trouverParCritere(['id_statut_rapport' => 'RAP_SOUMIS']);
    }

    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array
    {
        $approbations = $this->approuverModel->trouverParCritere(['numero_personnel_administratif' => $numeroPersonnelAdministratif]);
        $idsRapports = array_column($approbations, 'id_rapport_etudiant');
        if (empty($idsRapports)) return [];
        return $this->rapportEtudiantModel->trouverParCritere(['id_rapport_etudiant' => ['operator' => 'in', 'values' => $idsRapports]]);
    }
}