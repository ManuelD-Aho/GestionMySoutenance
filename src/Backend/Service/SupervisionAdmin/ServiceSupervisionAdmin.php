<?php
namespace App\Backend\Service\SupervisionAdmin;

use PDO;
use App\Backend\Model\Action;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Pister;
use App\Backend\Model\Utilisateur; // Pour récupérer les détails de l'utilisateur
use App\Backend\Model\RapportEtudiant; // Pour les détails des entités concernées
use App\Backend\Model\CompteRendu; // Pour les détails des entités concernées
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\DoublonException; // Pour gerer les doublons potentiels (bien que rares ici)

class ServiceSupervisionAdmin implements ServiceSupervisionAdminInterface
{
    private Action $actionModel;
    private Enregistrer $enregistrerModel;
    private Pister $pisterModel;
    private Utilisateur $utilisateurModel;
    private RapportEtudiant $rapportEtudiantModel;
    private CompteRendu $compteRenduModel;

    public function __construct(PDO $db)
    {
        $this->actionModel = new Action($db);
        $this->enregistrerModel = new Enregistrer($db);
        $this->pisterModel = new Pister($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->compteRenduModel = new CompteRendu($db);
    }

    /**
     * Enregistre une action système dans le journal d'audit.
     * Cette méthode est appelée par d'autres services pour journaliser leurs opérations.
     * @param string $numeroUtilisateur L'ID de l'utilisateur qui a effectué l'action (VARCHAR).
     * @param string $libelleAction Le code ou libellé de l'action (ex: 'SUCCES_LOGIN', 'CREATION_COMPTE') (VARCHAR).
     * @param string $detailsAction Description détaillée de l'action.
     * @param string|null $idEntiteConcernee L'ID de l'entité principale concernée par l'action (ex: ID rapport, ID utilisateur) (VARCHAR).
     * @param string|null $typeEntiteConcernee Le type de l'entité concernée (ex: 'RapportEtudiant', 'Utilisateur') (VARCHAR).
     * @param array $detailsJson Données supplémentaires à stocker en JSON (ex: modifications, anciens/nouveaux statuts).
     * @return bool Vrai si l'action a été enregistrée.
     */
    public function enregistrerAction(string $numeroUtilisateur, string $libelleAction, string $detailsAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool
    {
        try {
            // S'assurer que l'action est enregistrée dans la table 'action' (si ce n'est pas déjà un référentiel statique)
            // Idéalement, les 'id_action' seraient des IDs de référence pré-existants.
            // Ici, nous nous assurons qu'il existe ou le créons si non.
            $idAction = $this->recupererOuCreerIdActionParLibelle($libelleAction);

            $data = [
                'numero_utilisateur' => $numeroUtilisateur,
                'id_action' => $idAction,
                'date_action' => date('Y-m-d H:i:s'), // Timestamp exact de l'action
                'adresse_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'id_entite_concernee' => $idEntiteConcernee,
                'type_entite_concernee' => $typeEntiteConcernee,
                'details_action' => !empty($detailsJson) ? json_encode($detailsJson) : null,
                'session_id_utilisateur' => session_id() // Enregistrer l'ID de session si active
            ];

            $success = $this->enregistrerModel->creer($data);
            return (bool) $success; // Retourne true ou false
        } catch (\Exception $e) {
            // Log l'erreur interne de journalisation, sans la relancer pour ne pas bloquer l'opération principale
            error_log("Erreur lors de l'enregistrement de l'action {$libelleAction} pour {$numeroUtilisateur}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Méthode interne pour récupérer ou créer l'ID d'une action.
     * @param string $libelleAction Le libellé de l'action.
     * @return string L'ID de l'action.
     */
    public function recupererOuCreerIdActionParLibelle(string $libelleAction): string
    {
        $action = $this->actionModel->trouverUnParCritere(['libelle_action' => $libelleAction]);
        if ($action) {
            return $action['id_action'];
        }
        // Créer l'action si elle n'existe pas (par exemple, pour les libellés dynamiques)
        // Ou s'assurer que toutes les actions sont pré-enregistrées comme référentiels
        try {
            $newIdAction = strtoupper(str_replace([' ', '-'], '_', $libelleAction)); // Générer un ID simple
            if (strlen($newIdAction) > 50) $newIdAction = substr($newIdAction, 0, 50); // Tronquer si trop long

            // Prévenir les doublons si l'ID généré existe déjà
            $suffix = 0;
            $originalId = $newIdAction;
            while($this->actionModel->trouverParIdentifiant($newIdAction)){
                $suffix++;
                $newIdAction = $originalId . '_' . $suffix;
                if (strlen($newIdAction) > 50) $newIdAction = substr($originalId, 0, 50 - strlen('_' . $suffix)) . '_' . $suffix;
            }

            $this->actionModel->creer([
                'id_action' => $newIdAction,
                'libelle_action' => $libelleAction,
                'categorie_action' => 'Dynamique' // Ou une catégorie par défaut
            ]);
            return $newIdAction;
        } catch (DoublonException $e) {
            // Si une autre transaction a créé l'ID entre-temps, le récupérer
            $action = $this->actionModel->trouverUnParCritere(['libelle_action' => $libelleAction]);
            if ($action) return $action['id_action'];
            // Si toujours en échec, relancer ou gérer
            error_log("Erreur inattendue lors de la création d'ID d'action: " . $e->getMessage());
            return $libelleAction; // Fallback, pourrait causer des problèmes si non existant
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération/création d'ID d'action: " . $e->getMessage());
            return $libelleAction; // Retourne le libellé brut comme ID en cas d'erreur grave
        }
    }

    /**
     * Récupère les statistiques globales des rapports (nombre total, par statut).
     * @return array Statistiques agrégées.
     */
    public function obtenirStatistiquesGlobalesRapports(): array
    {
        return [
            'total_rapports_soumis' => $this->rapportEtudiantModel->compterParCritere([]),
            'rapports_en_attente_conformite' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => 'RAP_SOUMIS']),
            'rapports_en_attente_commission' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => 'RAP_EN_COMM']),
            'rapports_finalises' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_VALID', 'RAP_REFUSE']]]),
            // Ajoutez d'autres statistiques pertinentes
        ];
    }

    /**
     * Consulte les journaux des actions utilisateurs.
     * @param array $filtres Critères de filtrage (ex: numero_utilisateur, id_action).
     * @param int $limit Limite de résultats.
     * @param int $offset Offset pour la pagination.
     * @return array Liste des actions journalisées.
     */
    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limit = 50, int $offset = 0): array
    {
        // Récupérer les enregistrements, potentiellement avec jointures sur utilisateur et action pour les libellés
        $logs = $this->enregistrerModel->trouverParCritere($filtres, ['*'], 'AND', 'date_action DESC', $limit, $offset);

        foreach ($logs as &$log) {
            $user = $this->utilisateurModel->trouverParIdentifiant($log['numero_utilisateur'], ['login_utilisateur', 'email_principal']);
            $actionRef = $this->actionModel->trouverParIdentifiant($log['id_action'], ['libelle_action']);
            $log['login_utilisateur'] = $user['login_utilisateur'] ?? 'Inconnu';
            $log['libelle_action_ref'] = $actionRef['libelle_action'] ?? 'Action inconnue';
            // Décode les détails JSON si existants
            if ($log['details_action']) {
                $log['details_action_decoded'] = json_decode($log['details_action'], true);
            }
        }
        return $logs;
    }

    /**
     * Consulte les traces d'accès aux fonctionnalités (via la table `pister`).
     * @param array $filtres Critères de filtrage (ex: numero_utilisateur, id_traitement).
     * @param int $limit Limite de résultats.
     * @param int $offset Offset pour la pagination.
     * @return array Liste des traces d'accès.
     */
    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limit = 50, int $offset = 0): array
    {
        $traces = $this->pisterModel->trouverParCritere($filtres, ['*'], 'AND', 'date_pister DESC', $limit, $offset);

        foreach ($traces as &$trace) {
            $user = $this->utilisateurModel->trouverParIdentifiant($trace['numero_utilisateur'], ['login_utilisateur']);
            $traitement = $this->traitementModel->trouverParIdentifiant($trace['id_traitement'], ['libelle_traitement']);
            $trace['login_utilisateur'] = $user['login_utilisateur'] ?? 'Inconnu';
            $trace['libelle_traitement_ref'] = $traitement['libelle_traitement'] ?? 'Traitement inconnu';
        }
        return $traces;
    }

    /**
     * Liste les PV éligibles à l'archivage (ex: PV validés depuis plus d'un an).
     * @param int $anneesAnciennete Le nombre d'années après lequel un PV est éligible à l'archivage.
     * @return array Liste des PV éligibles.
     */
    public function listerPvEligiblesArchivage(int $anneesAnciennete = 1): array
    {
        $dateLimite = (new \DateTime())->modify("-{$anneesAnciennete} years")->format('Y-m-d H:i:s');
        // Trouver les PV validés avant la date limite
        return $this->compteRenduModel->trouverParCritere([
            'id_statut_pv' => 'PV_VALID',
            'date_creation_pv' => ['operator' => '<', 'value' => $dateLimite]
        ]);
    }

    /**
     * Implémente la logique d'archivage d'un PV (déplacement de données, compression, etc.).
     * @param string $idCompteRendu L'ID du PV à archiver.
     * @return bool Vrai si l'archivage a réussi.
     * @throws ElementNonTrouveException Si le PV n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'erreur d'archivage.
     */
    public function archiverPv(string $idCompteRendu): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv) {
            throw new ElementNonTrouveException("PV non trouvé pour archivage.");
        }
        if ($pv['id_statut_pv'] !== 'PV_VALID') {
            throw new OperationImpossibleException("Seuls les PV validés peuvent être archivés.");
        }

        $this->compteRenduModel->commencerTransaction();
        try {
            // Logique d'archivage:
            // 1. Déplacer les données du PV vers une table d'archive (`compte_rendu_archive`)
            // 2. Supprimer le PV de la table principale `compte_rendu`
            // 3. Optionnel: Compresser le fichier PDF associé ou le déplacer vers un stockage froid.

            // Pour l'exemple, nous allons juste "marquer" comme archivé ou simuler la suppression.
            // Dans une vraie implémentation, vous inséreriez dans une table d'archive avant de supprimer.
            $success = $this->compteRenduModel->supprimerParIdentifiant($idCompteRendu); // Simule l'archivage par suppression

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'archivage du PV {$idCompteRendu}.");
            }

            $this->compteRenduModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ARCHIVAGE_PV',
                "PV '{$idCompteRendu}' archivé.",
                $idCompteRendu,
                'CompteRendu'
            );
            return true;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_ARCHIVAGE_PV',
                "Erreur archivage PV {$idCompteRendu}: " . $e->getMessage()
            );
            throw $e;
        }
    }
}