<?php
namespace App\Backend\Service\Conformite;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Approuver;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Notification\ServiceNotification; // Pour les notifications
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceConformite implements ServiceConformiteInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private Approuver $approuverModel;
    private StatutConformiteRef $statutConformiteRefModel;
    private PersonnelAdministratif $personnelAdministratifModel;
    private ServiceNotification $notificationService;
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(PDO $db, ServiceNotification $notificationService, ServiceSupervisionAdmin $supervisionService)
    {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->approuverModel = new Approuver($db);
        $this->statutConformiteRefModel = new StatutConformiteRef($db);
        $this->personnelAdministratifModel = new PersonnelAdministratif($db);
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
    }

    /**
     * Traite la vérification de conformité d'un rapport étudiant par un membre du personnel administratif.
     * Met à jour le statut du rapport et notifie l'étudiant ou la commission.
     * @param string $idRapportEtudiant L'ID du rapport étudiant (VARCHAR).
     * @param string $numeroPersonnelAdministratif Le numéro du personnel effectuant la vérification (VARCHAR).
     * @param string $idStatutConformite L'ID du statut de conformité ('CONF_OK' ou 'CONF_NOK') (VARCHAR).
     * @param string|null $commentaireConformite Le commentaire du vérificateur.
     * @return bool Vrai si le traitement a réussi.
     * @throws ElementNonTrouveException Si le rapport, le personnel ou le statut de conformité n'est pas trouvé.
     * @throws OperationImpossibleException Si le rapport n'est pas dans un état vérifiable, ou si un commentaire est requis mais absent.
     * @throws DoublonException Si le même personnel tente de vérifier le même rapport plusieurs fois.
     */
    public function traiterVerificationConformite(string $idRapportEtudiant, string $numeroPersonnelAdministratif, string $idStatutConformite, ?string $commentaireConformite): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }
        $personnel = $this->personnelAdministratifModel->trouverParIdentifiant($numeroPersonnelAdministratif);
        if (!$personnel) {
            throw new ElementNonTrouveException("Personnel administratif non trouvé.");
        }
        $statutConformite = $this->statutConformiteRefModel->trouverParIdentifiant($idStatutConformite);
        if (!$statutConformite) {
            throw new ElementNonTrouveException("Statut de conformité non reconnu.");
        }

        // Vérifier si le rapport est dans un état où la conformité peut être vérifiée (ex: 'RAP_SOUMIS' ou 'RAP_NON_CONF' après corrections)
        if (!in_array($rapport['id_statut_rapport'], ['RAP_SOUMIS', 'RAP_NON_CONF'])) {
            throw new OperationImpossibleException("Le rapport '{$rapport['id_rapport_etudiant']}' n'est pas dans un état permettant la vérification de conformité.");
        }

        // Si non conforme, le commentaire est obligatoire
        if ($idStatutConformite === 'CONF_NOK' && empty($commentaireConformite)) {
            throw new OperationImpossibleException("Un commentaire est obligatoire si le rapport est non conforme.");
        }

        $this->approuverModel->commencerTransaction();
        try {
            // Enregistrer l'approbation/vérification
            // Utiliser mettreAJour ou creer en fonction de si la vérification a déjà été faite par cet agent.
            // Ici, on suppose qu'un agent ne vérifie qu'une fois un rapport. Si plusieurs agents vérifient, il faudrait ajuster.
            // Le modèle 'approuver' est une table de liaison, il gérera la création.
            $successApprobation = $this->approuverModel->creer([
                'numero_personnel_administratif' => $numeroPersonnelAdministratif,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'id_statut_conformite' => $idStatutConformite,
                'commentaire_conformite' => $commentaireConformite,
                'date_verification_conformite' => date('Y-m-d H:i:s')
            ]);

            if (!$successApprobation) {
                throw new OperationImpossibleException("Échec de l'enregistrement de la vérification de conformité.");
            }

            // Mettre à jour le statut du rapport en fonction de la décision de conformité
            $newStatutRapport = ($idStatutConformite === 'CONF_OK') ? 'RAP_CONF' : 'RAP_NON_CONF';
            $successRapportUpdate = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutRapport]);

            if (!$successRapportUpdate) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport après vérification.");
            }

            $this->approuverModel->validerTransaction();

            // Notifier les parties prenantes
            if ($idStatutConformite === 'CONF_OK') {
                // Notifier la commission
                $this->notificationService->envoyerNotificationGroupe(
                    'GRP_COMMISSION', // Groupe des membres de la commission
                    'RAPPORT_CONFORME',
                    "Le rapport '{$rapport['libelle_rapport_etudiant']}' (ID: {$idRapportEtudiant}) est maintenant conforme et disponible pour évaluation par la commission."
                );
                // Notifier l'étudiant que son rapport est conforme et transmis à la commission
                $this->notificationService->envoyerNotificationUtilisateur(
                    $rapport['numero_carte_etudiant'],
                    'RAPPORT_CONFORME_ETUDIANT',
                    "Votre rapport '{$rapport['libelle_rapport_etudiant']}' est conforme et a été transmis à la commission."
                );
            } else { // CONF_NOK
                // Notifier l'étudiant qu'il y a des corrections à faire
                $this->notificationService->envoyerNotificationUtilisateur(
                    $rapport['numero_carte_etudiant'],
                    'RAPPORT_NON_CONFORME',
                    "Votre rapport '{$rapport['libelle_rapport_etudiant']}' est non conforme. Des corrections sont nécessaires. " . ($commentaireConformite ? "Commentaire: {$commentaireConformite}" : "")
                );
            }

            $this->supervisionService->enregistrerAction(
                $numeroPersonnelAdministratif,
                'VERIF_CONFORMITE_RAPPORT',
                "Rapport '{$idRapportEtudiant}' traité: {$statutConformite['libelle_statut_conformite']}",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            return true;
        } catch (DoublonException $e) {
            $this->approuverModel->annulerTransaction();
            throw new OperationImpossibleException("Ce rapport a déjà été vérifié par ce membre du personnel.");
        } catch (\Exception $e) {
            $this->approuverModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelAdministratif,
                'ECHEC_VERIF_CONFORMITE_RAPPORT',
                "Erreur traitement conformité rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère la liste des rapports en attente de vérification de conformité.
     * @return array Liste des rapports au statut 'Soumis' ou 'Non Conforme' (après corrections).
     */
    public function recupererRapportsEnAttenteDeVerification(): array
    {
        // Les rapports sont en attente si leur statut est 'Soumis' ou 'Non Conforme' (après des tentatives de correction)
        return $this->rapportEtudiantModel->trouverParCritere([
            'id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_SOUMIS', 'RAP_NON_CONF']]
        ]);
    }

    /**
     * Récupère la liste des rapports traités par un agent de conformité spécifique.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel.
     * @return array Liste des rapports traités par cet agent.
     */
    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array
    {
        // Récupérer les ID des rapports que cet agent a approuvés/vérifiés
        $approbations = $this->approuverModel->trouverParCritere(['numero_personnel_administratif' => $numeroPersonnelAdministratif], ['id_rapport_etudiant']);
        $idsRapports = array_column($approbations, 'id_rapport_etudiant');

        if (empty($idsRapports)) {
            return [];
        }

        // Récupérer les détails des rapports correspondants
        return $this->rapportEtudiantModel->trouverParCritere([
            'id_rapport_etudiant' => ['operator' => 'in', 'values' => $idsRapports]
        ]);
    }

    /**
     * Récupère une vérification de conformité spécifique par l'agent et le rapport.
     * @param string $numeroPersonnelAdministratif Le numéro de l'agent.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return array|null Les détails de la vérification ou null si non trouvée.
     */
    public function getVerificationByAgentAndRapport(string $numeroPersonnelAdministratif, string $idRapportEtudiant): ?array
    {
        // Utilise la méthode trouverApprobationParCles du modèle Approuver
        return $this->approuverModel->trouverApprobationParCles($numeroPersonnelAdministratif, $idRapportEtudiant);
    }
}