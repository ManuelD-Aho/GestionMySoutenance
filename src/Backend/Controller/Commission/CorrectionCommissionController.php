<?php
namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Rapport\ServiceRapport; // Importer le service
use App\Backend\Service\Commission\ServiceCommission; // Pour les affectations si nécessaire
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class CorrectionCommissionController extends BaseController
{
    private ServiceRapport $rapportService;
    private ServiceCommission $commissionService;
    private ServiceNotification $notificationService;// Pour vérifier les affectations du jury

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator,
        ServiceRapport        $rapportService, // Injection
        ServiceCommission     $commissionService, // Injection
        ServiceNotification   $notificationService  // Si vous avez besoin de notifier les étudiants
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->rapportService = $rapportService;
        $this->commissionService = $commissionService;
        $this->notificationService = $notificationService; // Pour notifier les étudiants
    }

    /**
     * Affiche la liste des rapports en attente de corrections par la commission,
     * ou qui ont été retournés pour corrections.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_CORRECTION_LISTER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroEnseignant = $currentUser['numero_utilisateur'];

            // Récupérer les rapports pour lesquels ce membre est affecté et qui nécessitent une action de correction
            // Statut RAP_EN_COMM (pour les premières évaluations) ou RAP_CORRECT (si corrections demandées par la commission)
            $rapportsEnAttenteCorrection = $this->commissionService->recupererRapportsAssignedToJuryForCorrection($numeroEnseignant); // Nouvelle méthode au service
            // Ou simplement lister les rapports qui ont le statut 'RAP_EN_COMM' ou 'RAP_CORRECT' et que l'utilisateur peut voir

            $data = [
                'page_title' => 'Rapports en Attente de Correction Commission',
                'rapports' => $rapportsEnAttenteCorrection
            ];
            $this->render('Commission/corrections_commission', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des rapports: " . $e->getMessage());
            $this->redirect('/dashboard/commission');
        }
    }

    /**
     * Affiche les détails d'un rapport pour permettre la soumission de corrections (par un membre de la commission).
     * @param string $idRapport L'ID du rapport.
     */
    public function showReportCorrectionForm(string $idRapport): void
    {
        $this->requirePermission('TRAIT_COMMISSION_CORRECTION_MODIFIER'); // Permission de modifier/soumettre correction

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroEnseignant = $currentUser['numero_utilisateur'];

            $rapport = $this->rapportService->recupererInformationsRapportComplet($idRapport);
            if (!$rapport) {
                throw new ElementNonTrouveException("Rapport non trouvé.");
            }

            // Vérifier que l'enseignant est bien un membre du jury affecté à ce rapport et peut le corriger
            // $isAssigned = $this->commissionService->isEnseignantAssignedToRapport($numeroEnseignant, $idRapport); // Nouvelle méthode au service
            // if (!$isAssigned) {
            //     throw new OperationImpossibleException("Vous n'êtes pas autorisé à corriger ce rapport.");
            // }
            // Vérifier le statut du rapport (par exemple, il doit être 'RAP_EN_COMM')
            if (!in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM'])) {
                throw new OperationImpossibleException("Ce rapport n'est pas en attente de corrections de la commission.");
            }


            $data = [
                'page_title' => 'Détails du Rapport et Corrections',
                'rapport' => $rapport,
                'form_action' => "/dashboard/commission/corrections/{$idRapport}/submit"
            ];
            $this->render('Commission/Rapports/details_rapport_commission', $data); // Réutiliser cette vue ou créer une plus spécifique
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement rapport: ' . $e->getMessage());
            $this->redirect('/dashboard/commission/corrections');
        }
    }

    /**
     * Traite la soumission des corrections (commentaires/décisions) par un membre de la commission.
     * @param string $idRapport L'ID du rapport.
     */
    public function submitCorrection(string $idRapport): void
    {
        $this->requirePermission('TRAIT_COMMISSION_CORRECTION_MODIFIER'); // Permission de soumettre correction

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/commission/corrections/{$idRapport}");
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroEnseignant = $currentUser['numero_utilisateur'];

        $decision = $this->getRequestData('decision'); // Ex: 'corrections_requises', 'approuve'
        $commentaire = $this->getRequestData('commentaire');

        $rules = [
            'decision' => 'required|string',
            // 'commentaire' => 'required_if:decision,corrections_requises|string|min:10', // Exemple de règle conditionnelle
        ];
        $this->validator->validate(['decision' => $decision, 'commentaire' => $commentaire], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/commission/corrections/{$idRapport}");
        }

        try {
            // Cette logique devrait être intégrée au ServiceCommission,
            // potentiellement comme une partie de l'enregistrement du vote,
            // ou une action spécifique pour les corrections si ce n'est pas un vote formel.
            // Pour l'exemple, nous allons simuler la mise à jour du statut du rapport.
            $newStatut = 'RAP_EN_COMM'; // Rester en commission
            if ($decision === 'corrections_requises') {
                $newStatut = 'RAP_CORRECT'; // Passer au statut "Corrections Demandées"
            } elseif ($decision === 'approuve_en_etat') {
                // Si l'approbation sans correction, ça pourrait être un vote final ou une étape intermédiaire
                // Pour le vote final, c'est géré par ServiceCommission::enregistrerVotePourRapport
            }

            $this->rapportService->mettreAJourStatutRapport($idRapport, $newStatut);

            if ($newStatut === 'RAP_CORRECT') {
                // Notifier l'étudiant que des corrections sont demandées
                $rapportDetails = $this->rapportService->recupererInformationsRapportComplet($idRapport);
                if ($rapportDetails) {
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $rapportDetails['numero_carte_etudiant'],
                        'CORRECTIONS_DEMANDEES',
                        "Des corrections ont été demandées pour votre rapport '{$rapportDetails['libelle_rapport_etudiant']}'. Commentaire: {$commentaire}"
                    );
                }
            }

            $this->setFlashMessage('success', 'Décision de correction enregistrée avec succès.');
            $this->redirect('/dashboard/commission/corrections');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'enregistrement de la correction: ' . $e->getMessage());
            $this->redirect("/dashboard/commission/corrections/{$idRapport}");
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}