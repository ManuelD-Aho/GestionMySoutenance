<?php
namespace App\Backend\Service\ConfigurationSysteme;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Notification; // Remplacer 'Message' par 'Notification'
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException; // Pour les cas de doublons (ex: année académique)

class ServiceConfigurationSysteme implements ServiceConfigurationSystemeInterface
{
    private AnneeAcademique $anneeAcademiqueModel;
    private Notification $notificationModel; // Remplacer MessageModel
    private TypeDocumentRef $typeDocumentRefModel;
    private ServiceSupervisionAdmin $supervisionService;

    // Simulation d'une table de configuration générique si pas de modèle dédié
    // private array $parametresGeneraux = []; // Ou un modèle Configuraton/Parametre

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService)
    {
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->notificationModel = new Notification($db); // Initialiser NotificationModel
        $this->typeDocumentRefModel = new TypeDocumentRef($db);
        $this->supervisionService = $supervisionService;

        // Charger les paramètres généraux depuis la base de données ou un fichier de config
        // Idéalement, il y aurait une table 'parametres_systeme' et un modèle pour cela.
        // Pour l'instant, on simule ou on charge via une méthode dédiée.
    }

    /**
     * Définit l'année académique active.
     * Toutes les autres années seront désactivées.
     * @param string $idAnneeAcademique L'ID de l'année académique à activer (VARCHAR).
     * @return bool Vrai si l'année a été activée avec succès.
     * @throws ElementNonTrouveException Si l'année académique n'existe pas.
     * @throws OperationImpossibleException Si une erreur survient lors de la mise à jour.
     */
    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool
    {
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$annee) {
            throw new ElementNonTrouveException("Année académique non trouvée : {$idAnneeAcademique}");
        }

        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            // Désactiver toutes les autres années
            $this->anneeAcademiqueModel->mettreAJourParCritere(['est_active' => 1], ['est_active' => 0]);

            // Activer l'année spécifiée
            $success = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'activation de l'année académique.");
            }

            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CONFIG_ANNEE_ACTIVE',
                "Année académique '{$idAnneeAcademique}' définie comme active",
                $idAnneeAcademique,
                'AnneeAcademique'
            );
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CONFIG_ANNEE_ACTIVE',
                "Erreur activation année académique {$idAnneeAcademique}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Met à jour les paramètres généraux du système.
     * Ces paramètres devraient idéalement être stockés dans une table 'parametres_systeme'.
     * @param array $parametres Tableau associatif des paramètres à mettre à jour.
     * @return bool Vrai si la mise à jour réussit.
     * @throws OperationImpossibleException En cas d'erreur de mise à jour.
     */
    public function mettreAJourParametresGeneraux(array $parametres): bool
    {
        // Implémentation : Nécessite un modèle ou une structure pour les paramètres système.
        // Par exemple, une table `parametres_systeme` avec `cle VARCHAR(50) PK`, `valeur TEXT`, `description TEXT`.
        // Pour l'instant, c'est une fonctionnalité à développer.
        // Simulation :
        // $parametresSystemeModel = new ParametreSysteme($this->db);
        // $this->anneeAcademiqueModel->commencerTransaction(); // Utilisez la transaction du modèle de paramètres
        // try {
        //     foreach ($parametres as $cle => $valeur) {
        //         $param = $parametresSystemeModel->trouverParIdentifiant($cle);
        //         if ($param) {
        //             $parametresSystemeModel->mettreAJourParIdentifiant($cle, ['valeur' => $valeur]);
        //         } else {
        //             $parametresSystemeModel->creer(['cle' => $cle, 'valeur' => $valeur]);
        //         }
        //     }
        //     $this->anneeAcademiqueModel->validerTransaction();
        //     $this->supervisionService->enregistrerAction(
        //         $_SESSION['user_id'] ?? 'SYSTEM',
        //         'MISE_AJOUR_PARAM_SYSTEME',
        //         "Paramètres système mis à jour"
        //     );
        //     return true;
        // } catch (\Exception $e) {
        //     $this->anneeAcademiqueModel->annulerTransaction();
        //     $this->supervisionService->enregistrerAction(
        //         $_SESSION['user_id'] ?? 'SYSTEM',
        //         'ECHEC_MISE_AJOUR_PARAM_SYSTEME',
        //         "Erreur mise à jour paramètres système: " . $e->getMessage()
        //     );
        //     throw $e;
        // }

        // Pour l'instant, retourner true/false comme un stub.
        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'MISE_AJOUR_PARAM_SYSTEME',
            "Tentative de mise à jour des paramètres système (fonctionnalité à implémenter)"
        );
        return true;
    }

    /**
     * Récupère les paramètres généraux du système.
     * @return array Tableau associatif des paramètres.
     */
    public function recupererParametresGeneraux(): array
    {
        // Implémentation : Lire depuis la table 'parametres_systeme'.
        // Par exemple: return $parametresSystemeModel->trouverTout();
        return [
            'max_login_attempts' => 5,
            'lockout_time_minutes' => 30,
            'password_min_length' => 8,
            'pv_validation_quorum' => 4, // Nombre de validateurs de PV requis
            'default_year_prefix' => 'ANNEE', // Pour ID Generator
            // ... autres paramètres
        ];
    }

    /**
     * Gère la création ou la mise à jour des modèles de notification/email.
     * Utilise le modèle Notification (ex-Message).
     * @param string|null $idNotification L'ID de la notification si mise à jour.
     * @param array $donnees Les données du modèle de notification (libelle_notification, contenu, etc.).
     * @return string L'ID de la notification créée ou mise à jour.
     * @throws DoublonException Si le libellé de notification existe déjà.
     * @throws OperationImpossibleException En cas d'erreur.
     */
    public function gererModeleNotificationEmail(?string $idNotification, array $donnees): string
    {
        $this->notificationModel->commencerTransaction();
        try {
            if ($idNotification) {
                $existingNotification = $this->notificationModel->trouverParIdentifiant($idNotification);
                if (!$existingNotification) {
                    throw new ElementNonTrouveException("Modèle de notification non trouvé pour la mise à jour.");
                }
                if (!$this->notificationModel->mettreAJourParIdentifiant($idNotification, $donnees)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du modèle de notification.");
                }
                $actionType = 'MISE_AJOUR_MODELE_NOTIFICATION';
                $actionDetails = "Modèle de notification '{$idNotification}' mis à jour.";
            } else {
                // Pour la création, générer un ID si non fourni par la logique métier
                $newIdNotification = $this->idGenerator->genererIdentifiantUnique('NOTIF'); // Ou un ID significatif (ex: 'LOGIN_SUCCESS_EMAIL')
                $donnees['id_notification'] = $newIdNotification;
                if (!$this->notificationModel->creer($donnees)) {
                    throw new OperationImpossibleException("Échec de la création du modèle de notification.");
                }
                $idNotification = $newIdNotification;
                $actionType = 'CREATION_MODELE_NOTIFICATION';
                $actionDetails = "Modèle de notification '{$idNotification}' créé.";
            }

            $this->notificationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                $actionType,
                $actionDetails,
                $idNotification,
                'Notification'
            );
            return $idNotification;
        } catch (DoublonException $e) {
            $this->notificationModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->notificationModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_GERER_MODELE_NOTIFICATION',
                "Erreur gestion modèle notification: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Liste toutes les années académiques.
     * @return array
     */
    public function listerAnneesAcademiques(): array
    {
        return $this->anneeAcademiqueModel->trouverTout();
    }

    /**
     * Liste tous les types de documents de référence.
     * @return array
     */
    public function listerTypesDocument(): array
    {
        return $this->typeDocumentRefModel->trouverTout();
    }
}