<?php

namespace App\Backend\Service\NotificationConfiguration;

use PDO;
use App\Backend\Model\MatriceNotificationRegles;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\Action;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceNotificationConfiguration implements ServiceNotificationConfigurationInterface
{
    private MatriceNotificationRegles $matriceModel;
    private Utilisateur $utilisateurModel;
    private Action $actionModel;
    private GroupeUtilisateur $groupeUtilisateurModel;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;

    public function __construct(
        PDO $db,
        MatriceNotificationRegles $matriceModel,
        Utilisateur $utilisateurModel,
        Action $actionModel,
        GroupeUtilisateur $groupeUtilisateurModel,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->matriceModel = $matriceModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->actionModel = $actionModel;
        $this->groupeUtilisateurModel = $groupeUtilisateurModel;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function configurerMatriceDiffusion(string $idActionDeclencheur, string $idGroupeDestinataire, string $canalNotification, bool $estActive): bool
    {
        if (!$this->actionModel->trouverParIdentifiant($idActionDeclencheur)) {
            throw new ElementNonTrouveException("Action déclencheur '{$idActionDeclencheur}' non trouvée.");
        }
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeDestinataire)) {
            throw new ElementNonTrouveException("Groupe destinataire '{$idGroupeDestinataire}' non trouvé.");
        }

        $this->matriceModel->commencerTransaction();
        try {
            $existingRule = $this->matriceModel->trouverUnParCritere([
                'id_action_declencheur' => $idActionDeclencheur,
                'id_groupe_destinataire' => $idGroupeDestinataire
            ]);

            $data = [
                'canal_notification' => $canalNotification,
                'est_active' => $estActive ? 1 : 0
            ];

            if ($existingRule) {
                $success = $this->matriceModel->mettreAJourParIdentifiant($existingRule['id_regle'], $data);
            } else {
                $data['id_regle'] = $this->idGenerator->genererIdentifiantUnique('REG');
                $data['id_action_declencheur'] = $idActionDeclencheur;
                $data['id_groupe_destinataire'] = $idGroupeDestinataire;
                $success = $this->matriceModel->creer($data);
            }

            if (!$success) {
                throw new OperationImpossibleException("Échec de la configuration de la matrice de diffusion.");
            }

            $this->matriceModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CONFIG_MATRICE_NOTIF',
                "Règle de notification pour '{$idActionDeclencheur}' et groupe '{$idGroupeDestinataire}' mise à jour.",
                $existingRule ? $existingRule['id_regle'] : $data['id_regle'],
                'MatriceNotificationRegles'
            );
            return true;
        } catch (\Exception $e) {
            $this->matriceModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererMatriceDiffusion(): array
    {
        return $this->matriceModel->trouverTout();
    }

    public function definirPreferencesNotificationUtilisateur(string $numeroUtilisateur, array $preferences): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['preferences_notifications']);
        if (!$user) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }

        $currentPreferences = json_decode($user['preferences_notifications'] ?? '[]', true);
        $criticalNotifications = $this->listerNotificationsCritiques();

        foreach ($preferences as $notificationCode => $prefs) {
            if (!in_array($notificationCode, $criticalNotifications)) {
                $currentPreferences[$notificationCode] = $prefs;
            }
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
            'preferences_notifications' => json_encode($currentPreferences)
        ]);

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateur,
                'MAJ_PREFERENCES_NOTIF',
                "Préférences de notification mises à jour.",
                $numeroUtilisateur,
                'Utilisateur'
            );
        }
        return $success;
    }

    public function recupererPreferencesNotificationUtilisateur(string $numeroUtilisateur): array
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['preferences_notifications']);
        if (!$user) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }
        return json_decode($user['preferences_notifications'] ?? '[]', true);
    }

    public function listerNotificationsCritiques(): array
    {
        return [
            'CHANGEMENT_MDP',
            'COMPTE_BLOQUE',
            'DEMANDE_RESET_MDP',
            'RAPPORT_NON_CONFORME',
            'CORRECTIONS_DEMANDEES',
            'CONVOCATION_SESSION'
        ];
    }
}