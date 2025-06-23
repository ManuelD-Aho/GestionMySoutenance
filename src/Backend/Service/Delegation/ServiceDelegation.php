<?php
namespace App\Backend\Service\Delegation;

use PDO;
use App\Backend\Model\Delegation;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceDelegation implements ServiceDelegationInterface
{
    private Delegation $delegationModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->delegationModel = new Delegation($db);
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function creerDelegation(string $idDelegant, string $idDelegue, array $idsTraitements, \DateTime $dateDebut, \DateTime $dateFin, ?string $contexteType = null, ?string $contexteId = null): bool
    {
        $this->delegationModel->commencerTransaction();
        try {
            foreach ($idsTraitements as $idTraitement) {
                $idDelegation = $this->idGenerator->generate('delegation');
                $data = [
                    'id_delegation' => $idDelegation,
                    'id_delegant' => $idDelegant,
                    'id_delegue' => $idDelegue,
                    'id_traitement' => $idTraitement,
                    'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
                    'date_fin' => $dateFin->format('Y-m-d H:i:s'),
                    'statut' => 'Active',
                    'contexte_type' => $contexteType,
                    'contexte_id' => $contexteId,
                ];
                $this->delegationModel->creer($data);
            }
            $this->delegationModel->validerTransaction();
            $this->supervisionService->enregistrerAction($idDelegant, 'CREATION_DELEGATION', "Délégation de " . count($idsTraitements) . " tâche(s) à {$idDelegue}", $idDelegue, 'Utilisateur');
            return true;
        } catch (\Exception $e) {
            $this->delegationModel->annulerTransaction();
            throw new OperationImpossibleException("Erreur lors de la création de la délégation: " . $e->getMessage());
        }
    }

    public function revoquerDelegation(string $idDelegation): bool
    {
        return $this->delegationModel->mettreAJourParIdentifiant($idDelegation, ['statut' => 'Révoquée']);
    }

    public function listerDelegationsActivesPourUtilisateur(string $idUtilisateur): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->delegationModel->trouverParCritere([
            'id_delegue' => $idUtilisateur,
            'statut' => 'Active',
            'date_debut' => ['operator' => '<=', 'value' => $now],
            'date_fin' => ['operator' => '>=', 'value' => $now],
        ]);
    }

    public function getPermissionsDeleguees(string $idUtilisateur): array
    {
        $delegationsActives = $this->listerDelegationsActivesPourUtilisateur($idUtilisateur);
        return array_column($delegationsActives, 'id_traitement');
    }
}