<?php
namespace App\Backend\Service\Reclamation;

use PDO;
use App\Backend\Model\Reclamation;
use App\Backend\Model\StatutReclamationRef;
use App\Backend\Model\Etudiant; // Pour vérifier l'étudiant
use App\Backend\Model\PersonnelAdministratif; // Pour le personnel traitant
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceReclamation implements ServiceReclamationInterface
{
    private Reclamation $reclamationModel;
    private StatutReclamationRef $statutReclamationRefModel;
    private Etudiant $etudiantModel;
    private PersonnelAdministratif $personnelAdministratifModel;
    private ServiceNotification $notificationService;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;

    public function __construct(
        PDO $db,
        ServiceNotification $notificationService,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->reclamationModel = new Reclamation($db);
        $this->statutReclamationRefModel = new StatutReclamationRef($db);
        $this->etudiantModel = new Etudiant($db);
        $this->personnelAdministratifModel = new PersonnelAdministratif($db);
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Soumet une nouvelle réclamation par un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant soumettant la réclamation.
     * @param string $sujetReclamation Le sujet de la réclamation.
     * @param string $descriptionReclamation La description détaillée de la réclamation.
     * @return string L'ID de la réclamation créée.
     * @throws ElementNonTrouveException Si l'étudiant n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'échec de la soumission.
     */
    public function soumettreReclamation(string $numeroCarteEtudiant, string $sujetReclamation, string $descriptionReclamation): string
    {
        $this->reclamationModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant '{$numeroCarteEtudiant}' non trouvé.");
            }
            // Vérifier que le statut initial 'RECLAM_RECUE' existe
            if (!$this->statutReclamationRefModel->trouverParIdentifiant('RECLAM_RECUE')) {
                throw new OperationImpossibleException("Statut de réclamation 'RECLAM_RECUE' non défini.");
            }

            $idReclamation = $this->idGenerator->genererIdentifiantUnique('RECL'); // RECL-AAAA-SSSS

            $data = [
                'id_reclamation' => $idReclamation,
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'sujet_reclamation' => $sujetReclamation,
                'description_reclamation' => $descriptionReclamation,
                'date_soumission' => date('Y-m-d H:i:s'),
                'id_statut_reclamation' => 'RECLAM_RECUE' // Statut initial
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
            // Notifier le personnel administratif (RS) de la nouvelle réclamation
            $this->notificationService->envoyerNotificationGroupe(
                'GRP_PERS_ADMIN', // Supposons que le RS fait partie de ce groupe ou créez un GRP_RS
                'NOUVELLE_RECLAMATION',
                "Nouvelle réclamation de {$numeroCarteEtudiant} (Sujet: {$sujetReclamation})."
            );
            return $idReclamation;
        } catch (\Exception $e) {
            $this->reclamationModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCarteEtudiant,
                'ECHEC_SOUMISSION_RECLAMATION',
                "Erreur soumission réclamation pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère les détails d'une réclamation spécifique par son ID.
     * @param string $idReclamation L'ID de la réclamation.
     * @return array|null Les détails de la réclamation ou null si non trouvée.
     */
    public function getDetailsReclamation(string $idReclamation): ?array
    {
        // Récupérer la réclamation de base
        $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
        if (!$reclamation) {
            return null;
        }

        // Optionnel : Joindre des informations supplémentaires si nécessaire pour l'affichage
        // Ex: détails de l'étudiant, du personnel traitant, libellé du statut
        $etudiant = $this->etudiantModel->trouverParIdentifiant($reclamation['numero_carte_etudiant']);
        $statutRef = $this->statutReclamationRefModel->trouverParIdentifiant($reclamation['id_statut_reclamation']);
        $personnelTraitant = null;
        if ($reclamation['numero_personnel_traitant']) {
            $personnelTraitant = $this->personnelAdministratifModel->trouverParIdentifiant($reclamation['numero_personnel_traitant']);
        }

        // Enrichir la réclamation avec ces données
        $reclamation['etudiant_details'] = $etudiant;
        $reclamation['statut_libelle'] = $statutRef['libelle_statut_reclamation'] ?? 'Statut inconnu';
        $reclamation['personnel_traitant_details'] = $personnelTraitant;

        return $reclamation;
    }

    /**
     * Récupère toutes les réclamations pour un étudiant donné.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @return array Liste des réclamations de l'étudiant.
     */
    public function recupererReclamationsEtudiant(string $numeroCarteEtudiant): array
    {
        // Peut ajouter des jointures avec statut_reclamation_ref pour avoir le libellé du statut
        return $this->reclamationModel->trouverParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant], ['*'], 'AND', 'date_soumission DESC');
    }

    /**
     * Récupère toutes les réclamations du système, avec filtres et pagination (pour le personnel).
     * @param array $criteres Critères de recherche (ex: ['id_statut_reclamation' => 'RECLAM_RECUE']).
     * @param int $page Numéro de page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Liste des réclamations.
     */
    public function recupererToutesReclamations(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        // Idéalement, jointure avec étudiant et personnel_administratif pour afficher les noms
        return $this->reclamationModel->trouverParCritere($criteres, ['*'], 'AND', 'date_soumission ASC', $elementsParPage, $offset);
    }

    /**
     * Traite une réclamation par un membre du personnel administratif.
     * @param string $idReclamation L'ID de la réclamation.
     * @param string $numeroPersonnelTraitant Le numéro du personnel qui traite la réclamation.
     * @param string $newStatut L'ID du nouveau statut de la réclamation (ex: 'RECLAM_EN_COURS', 'RECLAM_REPONDUE', 'RECLAM_CLOTUREE').
     * @param string|null $reponseReclamation La réponse textuelle à la réclamation.
     * @return bool Vrai si le traitement a réussi.
     * @throws ElementNonTrouveException Si la réclamation ou le personnel n'est pas trouvé.
     * @throws OperationImpossibleException Si le statut n'est pas valide ou si la réponse est manquante pour un statut 'REPONDUE'.
     */
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
            // Notifier l'étudiant du changement de statut de sa réclamation
            $this->notificationService->envoyerNotificationUtilisateur(
                $reclamation['numero_carte_etudiant'],
                'RECLAMATION_MISE_A_JOUR',
                "Votre réclamation '{$reclamation['sujet_reclamation']}' a été mise à jour. Nouveau statut: {$this->statutReclamationRefModel->trouverParIdentifiant($newStatut)['libelle_statut_reclamation']}" . ($reponseReclamation ? " Réponse: {$reponseReclamation}" : "")
            );
            return true;
        } catch (\Exception $e) {
            $this->reclamationModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelTraitant,
                'ECHEC_TRAITEMENT_RECLAMATION',
                "Erreur traitement réclamation {$idReclamation}: " . $e->getMessage()
            );
            throw $e;
        }
    }
}