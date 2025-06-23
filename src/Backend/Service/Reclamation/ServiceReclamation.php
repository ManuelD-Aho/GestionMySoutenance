<?php
namespace App\Backend\Service\Reclamation;

use PDO;
use App\Backend\Model\Reclamation;
use App\Backend\Model\Etudiant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceReclamation implements ServiceReclamationInterface
{
    private Reclamation $reclamationModel;
    private Etudiant $etudiantModel;
    private PersonnelAdministratif $personnelAdminModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, ServiceNotificationInterface $notificationService, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->reclamationModel = new Reclamation($db);
        $this->etudiantModel = new Etudiant($db);
        $this->personnelAdminModel = new PersonnelAdministratif($db);
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function soumettreReclamation(string $numeroCarteEtudiant, string $sujetReclamation, string $descriptionReclamation): string
    {
        if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
            throw new ElementNonTrouveException("Étudiant non trouvé.");
        }
        $idReclamation = $this->idGenerator->generate('reclamation');
        $data = [
            'id_reclamation' => $idReclamation,
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'sujet_reclamation' => $sujetReclamation,
            'description_reclamation' => $descriptionReclamation,
            'date_soumission' => date('Y-m-d H:i:s'),
            'id_statut_reclamation' => 'RECLAM_RECUE'
        ];
        if (!$this->reclamationModel->creer($data)) {
            throw new OperationImpossibleException("Échec de la soumission de la réclamation.");
        }
        $this->notificationService->sendToGroup('GRP_PERS_ADMIN', 'NOUVELLE_RECLAMATION', ['sujet' => $sujetReclamation]);
        return $idReclamation;
    }

    public function getDetailsReclamation(string $idReclamation): ?array
    {
        return $this->reclamationModel->trouverParIdentifiant($idReclamation);
    }

    public function recupererReclamationsEtudiant(string $numeroCarteEtudiant): array
    {
        return $this->reclamationModel->trouverParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant], ['*'], 'AND', 'date_soumission DESC');
    }

    public function recupererToutesReclamations(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        return $this->reclamationModel->trouverParCritere($criteres, ['*'], 'AND', 'date_soumission ASC', $elementsParPage, $offset);
    }

    public function traiterReclamation(string $idReclamation, string $numeroPersonnelTraitant, string $newStatut, ?string $reponseReclamation): bool
    {
        $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
        if (!$reclamation) throw new ElementNonTrouveException("Réclamation non trouvée.");
        if (!$this->personnelAdminModel->trouverParIdentifiant($numeroPersonnelTraitant)) throw new ElementNonTrouveException("Personnel traitant non trouvé.");
        if ($newStatut === 'RECLAM_REPONDUE' && empty($reponseReclamation)) {
            throw new OperationImpossibleException("Une réponse est obligatoire pour ce statut.");
        }

        $dataToUpdate = [
            'id_statut_reclamation' => $newStatut,
            'numero_personnel_traitant' => $numeroPersonnelTraitant,
            'reponse_reclamation' => $reponseReclamation,
            'date_reponse' => date('Y-m-d H:i:s')
        ];
        $success = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, $dataToUpdate);
        if ($success) {
            $this->notificationService->send($reclamation['numero_carte_etudiant'], 'RECLAMATION_MISE_A_JOUR', ['statut' => $newStatut]);
        }
        return $success;
    }
}