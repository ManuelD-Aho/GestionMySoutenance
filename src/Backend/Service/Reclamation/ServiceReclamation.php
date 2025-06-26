<?php

namespace App\Backend\Service\Reclamation;

use PDO;
use App\Backend\Model\Reclamation;
use App\Backend\Model\StatutReclamationRef;
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
    private StatutReclamationRef $statutReclamationRefModel;
    private Etudiant $etudiantModel;
    private PersonnelAdministratif $personnelAdministratifModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Reclamation $reclamationModel,
        StatutReclamationRef $statutReclamationRefModel,
        Etudiant $etudiantModel,
        PersonnelAdministratif $personnelAdministratifModel,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->reclamationModel = $reclamationModel;
        $this->statutReclamationRefModel = $statutReclamationRefModel;
        $this->etudiantModel = $etudiantModel;
        $this->personnelAdministratifModel = $personnelAdministratifModel;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function soumettreReclamation(string $numeroCarteEtudiant, string $sujetReclamation, string $descriptionReclamation): string
    {
        $this->reclamationModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant '{$numeroCarteEtudiant}' non trouvé.");
            }
            if (!$this->statutReclamationRefModel->trouverParIdentifiant('RECLAM_RECUE')) {
                throw new OperationImpossibleException("Statut de réclamation 'RECLAM_RECUE' non défini.");
            }

            $idReclamation = $this->idGenerator->genererIdentifiantUnique('RECL');

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

            $this->reclamationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCarteEtudiant,
                'SOUMISSION_RECLAMATION',
                "Réclamation '{$idReclamation}' soumise par {$numeroCarteEtudiant} (Sujet: {$sujetReclamation}).",
                $idReclamation,
                'Reclamation'
            );
            $this->notificationService->envoyerNotificationGroupe(
                'GRP_RS',
                'NOUVELLE_RECLAMATION',
                "Nouvelle réclamation de {$numeroCarteEtudiant} (Sujet: {$sujetReclamation})."
            );
            return $idReclamation;
        } catch (\Exception $e) {
            $this->reclamationModel->annulerTransaction();
            throw $e;
        }
    }

    public function getDetailsReclamation(string $idReclamation): ?array
    {
        $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
        if (!$reclamation) {
            return null;
        }

        $etudiant = $this->etudiantModel->trouverParIdentifiant($reclamation['numero_carte_etudiant']);
        $statutRef = $this->statutReclamationRefModel->trouverParIdentifiant($reclamation['id_statut_reclamation']);
        $personnelTraitant = null;
        if ($reclamation['numero_personnel_traitant']) {
            $personnelTraitant = $this->personnelAdministratifModel->trouverParIdentifiant($reclamation['numero_personnel_traitant']);
        }

        $reclamation['etudiant_details'] = $etudiant;
        $reclamation['statut_libelle'] = $statutRef['libelle_statut_reclamation'] ?? 'Statut inconnu';
        $reclamation['personnel_traitant_details'] = $personnelTraitant;

        return $reclamation;
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
        if (!$reclamation) {
            throw new ElementNonTrouveException("Réclamation '{$idReclamation}' non trouvée.");
        }
        if (!$this->personnelAdministratifModel->trouverParIdentifiant($numeroPersonnelTraitant)) {
            throw new ElementNonTrouveException("Personnel traitant '{$numeroPersonnelTraitant}' non trouvé.");
        }
        if (!$this->statutReclamationRefModel->trouverParIdentifiant($newStatut)) {
            throw new OperationImpossibleException("Statut de réclamation '{$newStatut}' non valide.");
        }
        if ($newStatut === 'RECLAM_REPONDUE' && empty($reponseReclamation)) {
            throw new OperationImpossibleException("Une réponse est obligatoire pour marquer la réclamation comme 'Répondue'.");
        }

        $this->reclamationModel->commencerTransaction();
        try {
            $dataToUpdate = [
                'id_statut_reclamation' => $newStatut,
                'numero_personnel_traitant' => $numeroPersonnelTraitant
            ];
            if ($newStatut === 'RECLAM_REPONDUE' || $newStatut === 'RECLAM_CLOTUREE') {
                $dataToUpdate['reponse_reclamation'] = $reponseReclamation;
                $dataToUpdate['date_reponse'] = date('Y-m-d H:i:s');
            }

            $success = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, $dataToUpdate);
            if (!$success) {
                throw new OperationImpossibleException("Échec du traitement de la réclamation.");
            }

            $this->reclamationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelTraitant,
                'TRAITEMENT_RECLAMATION',
                "Réclamation '{$idReclamation}' traitée par {$numeroPersonnelTraitant}. Nouveau statut: '{$newStatut}'.",
                $idReclamation,
                'Reclamation'
            );
            $this->notificationService->envoyerNotificationUtilisateur(
                $reclamation['numero_carte_etudiant'],
                'RECLAMATION_MISE_A_JOUR',
                "Votre réclamation '{$reclamation['sujet_reclamation']}' a été mise à jour. Nouveau statut: {$this->statutReclamationRefModel->trouverParIdentifiant($newStatut)['libelle_statut_reclamation']}" . ($reponseReclamation ? " Réponse: {$reponseReclamation}" : "")
            );
            return true;
        } catch (\Exception $e) {
            $this->reclamationModel->annulerTransaction();
            throw $e;
        }
    }
}