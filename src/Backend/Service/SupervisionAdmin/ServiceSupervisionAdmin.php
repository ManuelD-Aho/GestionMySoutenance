<?php

namespace App\Backend\Service\SupervisionAdmin;

use PDO;
use App\Backend\Model\Action;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Pister;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Traitement;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceSupervisionAdmin implements ServiceSupervisionAdminInterface
{
    private Action $actionModel;
    private Enregistrer $enregistrerModel;
    private Pister $pisterModel;
    private Utilisateur $utilisateurModel;
    private RapportEtudiant $rapportEtudiantModel;
    private CompteRendu $compteRenduModel;
    private Traitement $traitementModel;

    public function __construct(
        PDO $db,
        Action $actionModel,
        Enregistrer $enregistrerModel,
        Pister $pisterModel,
        Utilisateur $utilisateurModel,
        RapportEtudiant $rapportEtudiantModel,
        CompteRendu $compteRenduModel,
        Traitement $traitementModel
    ) {
        $this->actionModel = $actionModel;
        $this->enregistrerModel = $enregistrerModel;
        $this->pisterModel = $pisterModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->traitementModel = $traitementModel;
    }

    public function enregistrerAction(string $numeroUtilisateur, string $libelleAction, string $detailsAction, ?string $idEntiteConcernee = null, ?string $typeEntiteConcernee = null, array $detailsJson = []): bool
    {
        try {
            $idAction = $this->recupererOuCreerIdActionParLibelle($libelleAction);

            $data = [
                'numero_utilisateur' => $numeroUtilisateur,
                'id_action' => $idAction,
                'date_action' => date('Y-m-d H:i:s'),
                'adresse_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'id_entite_concernee' => $idEntiteConcernee,
                'type_entite_concernee' => $typeEntiteConcernee,
                'details_action' => !empty($detailsJson) ? json_encode($detailsJson) : null,
                'session_id_utilisateur' => session_id()
            ];

            $success = $this->enregistrerModel->creer($data);
            return (bool) $success;
        } catch (\Exception $e) {
            error_log("Erreur lors de l'enregistrement de l'action {$libelleAction} pour {$numeroUtilisateur}: " . $e->getMessage());
            return false;
        }
    }

    public function recupererOuCreerIdActionParLibelle(string $libelleAction): string
    {
        $action = $this->actionModel->trouverUnParCritere(['libelle_action' => $libelleAction]);
        if ($action) {
            return $action['id_action'];
        }

        try {
            $newIdAction = strtoupper(str_replace([' ', '-'], '_', $libelleAction));
            if (strlen($newIdAction) > 50) $newIdAction = substr($newIdAction, 0, 50);

            $suffix = 0;
            $originalId = $newIdAction;
            while($this->actionModel->trouverParIdentifiant($newIdAction)){
                $suffix++;
                $newIdAction = substr($originalId, 0, 50 - strlen('_' . $suffix)) . '_' . $suffix;
            }

            $this->actionModel->creer([
                'id_action' => $newIdAction,
                'libelle_action' => $libelleAction,
                'categorie_action' => 'Dynamique'
            ]);
            return $newIdAction;
        } catch (DoublonException $e) {
            $action = $this->actionModel->trouverUnParCritere(['libelle_action' => $libelleAction]);
            if ($action) return $action['id_action'];
            error_log("Erreur inattendue lors de la création d'ID d'action: " . $e->getMessage());
            return $libelleAction;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération/création d'ID d'action: " . $e->getMessage());
            return $libelleAction;
        }
    }

    public function obtenirStatistiquesGlobalesRapports(): array
    {
        return [
            'total_rapports_soumis' => $this->rapportEtudiantModel->compterParCritere([]),
            'rapports_en_attente_conformite' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => 'RAP_SOUMIS']),
            'rapports_en_attente_commission' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => 'RAP_EN_COMM']),
            'rapports_finalises' => $this->rapportEtudiantModel->compterParCritere(['id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_VALID', 'RAP_REFUSE']]]),
        ];
    }

    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limit = 50, int $offset = 0): array
    {
        $logs = $this->enregistrerModel->trouverParCritere($filtres, ['*'], 'AND', 'date_action DESC', $limit, $offset);

        foreach ($logs as &$log) {
            $user = $this->utilisateurModel->trouverParIdentifiant($log['numero_utilisateur'], ['login_utilisateur', 'email_principal']);
            $actionRef = $this->actionModel->trouverParIdentifiant($log['id_action'], ['libelle_action']);
            $log['login_utilisateur'] = $user['login_utilisateur'] ?? 'Inconnu';
            $log['libelle_action_ref'] = $actionRef['libelle_action'] ?? 'Action inconnue';
            if ($log['details_action']) {
                $log['details_action_decoded'] = json_decode($log['details_action'], true);
            }
        }
        return $logs;
    }

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

    public function listerPvEligiblesArchivage(int $anneesAnciennete = 1): array
    {
        $dateLimite = (new \DateTime())->modify("-{$anneesAnciennete} years")->format('Y-m-d H:i:s');
        return $this->compteRenduModel->trouverParCritere([
            'id_statut_pv' => 'PV_VALID',
            'date_creation_pv' => ['operator' => '<', 'value' => $dateLimite]
        ]);
    }

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
            $success = $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_ARCHIVE']);
            if (!$success) {
                throw new OperationImpossibleException("Échec de l'archivage du PV {$idCompteRendu}.");
            }

            $this->compteRenduModel->validerTransaction();
            $this->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ARCHIVAGE_PV',
                "PV '{$idCompteRendu}' archivé.",
                $idCompteRendu,
                'CompteRendu'
            );
            return true;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            $this->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_ARCHIVAGE_PV',
                "Erreur archivage PV {$idCompteRendu}: " . $e->getMessage()
            );
            throw $e;
        }
    }
}