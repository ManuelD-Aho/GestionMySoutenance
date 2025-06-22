<?php
namespace App\Backend\Service\ConfigurationSysteme;

use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Notification; // Remplacer 'Message' par 'Notification'
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\NiveauEtude; // <-- NOUVEL IMPORT
use App\Backend\Model\StatutPaiementRef; // <-- NOUVEL IMPORT
use App\Backend\Model\DecisionPassageRef; // <-- NOUVEL IMPORT
use App\Backend\Model\Ecue; // <-- NOUVEL IMPORT
use App\Backend\Model\Grade; // <-- NOUVEL IMPORT
use App\Backend\Model\Fonction; // <-- NOUVEL IMPORT
use App\Backend\Model\Specialite; // <-- NOUVEL IMPORT
use App\Backend\Model\StatutReclamationRef; // <-- NOUVEL IMPORT
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface; // Pour générer des IDs
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException; // Pour les cas de doublons (ex: année académique)

class ServiceConfigurationSysteme implements ServiceConfigurationSystemeInterface
{
    private AnneeAcademique $anneeAcademiqueModel;
    private Notification $notificationModel; // Remplacer MessageModel
    private TypeDocumentRef $typeDocumentRefModel;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator; // Pour générer des IDs uniques
    private NiveauEtude $niveauEtudeModel; // <-- NOUVELLE PROPRIÉTÉ
    private StatutPaiementRef $statutPaiementRefModel; // <-- NOUVELLE PROPRIÉTÉ
    private DecisionPassageRef $decisionPassageRefModel; // <-- NOUVELLE PROPRIÉTÉ
    private Ecue $ecueModel; // <-- NOUVELLE PROPRIÉTÉ
    private Grade $gradeModel; // <-- NOUVELLE PROPRIÉTÉ
    private Fonction $fonctionModel; // <-- NOUVELLE PROPRIÉTÉ
    private Specialite $specialiteModel; // <-- NOUVELLE PROPRIÉTÉ
    private StatutReclamationRef $statutReclamationRefModel; // <-- NOUVELLE PROPRIÉTÉ
    private StatutConformiteRef $statutConformiteRefModel; // <-- NOUVELLE PROPRIÉTÉ

    // Simulation d'une table de configuration générique si pas de modèle dédié
    // private array $parametresGeneraux = []; // Ou un modèle Configuraton/Parametre

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService, IdentifiantGenerator $idGenerator)
    {
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->notificationModel = new Notification($db); // Initialiser NotificationModel
        $this->typeDocumentRefModel = new TypeDocumentRef($db);
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator; // Injecter le générateur d'ID

        // Charger les paramètres généraux depuis la base de données ou un fichier de config
        // Idéalement, il y aurait une table 'parametres_systeme' et un modèle pour cela.
        // Pour l'instant, on simule ou on charge via une méthode dédiée.
        $this->niveauEtudeModel = new NiveauEtude($db); // <-- INITIALISATION
        $this->statutPaiementRefModel = new StatutPaiementRef($db); // <-- INITIALISATION
        $this->decisionPassageRefModel = new DecisionPassageRef($db); // <-- INITIALISATION
        $this->ecueModel = new Ecue($db); // <-- INITIALISATION
        $this->gradeModel = new Grade($db); // <-- INITIALISATION
        $this->fonctionModel = new Fonction($db); // <-- INITIALISATION
        $this->specialiteModel = new Specialite($db); // <-- INITIALISATION
        $this->statutReclamationRefModel = new StatutReclamationRef($db); // <-- INITIALISATION
        $this->statutConformiteRefModel = new StatutConformiteRef($db); // <-- INITIALISATION
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

    /**
     * Crée une nouvelle année académique.
     * @param string $idAnneeAcademique L'ID unique de l'année académique (ex: 'ANNEE-2024-2025').
     * @param string $libelleAnneeAcademique Le libellé de l'année académique (ex: '2024-2025').
     * @param string $dateDebut Date de début (YYYY-MM-DD).
     * @param string $dateFin Date de fin (YYYY-MM-DD).
     * @param bool $estActive Indique si cette année est active.
     * @return bool Vrai si la création a réussi.
     * @throws DoublonException Si l'ID ou le libellé de l'année existe déjà.
     * @throws OperationImpossibleException En cas d'échec de la création.
     */
    public function creerAnneeAcademique(string $idAnneeAcademique, string $libelleAnneeAcademique, string $dateDebut, string $dateFin, bool $estActive): bool
    {
        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            // Vérifier les doublons
            if ($this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique)) {
                throw new DoublonException("L'ID d'année académique '{$idAnneeAcademique}' existe déjà.");
            }
            if ($this->anneeAcademiqueModel->trouverUnParCritere(['libelle_annee_academique' => $libelleAnneeAcademique])) {
                throw new DoublonException("Le libellé d'année académique '{$libelleAnneeAcademique}' existe déjà.");
            }
            // Logique pour s'assurer qu'il n'y a qu'une seule année active si 'estActive' est true
            if ($estActive) {
                // Cette logique est déjà dans definirAnneeAcademiqueActive(), donc on peut l'appeler après la création
                // Ou désactiver toutes les autres manuellement avant la création si 'estActive' est true
                $this->anneeAcademiqueModel->mettreAJourParCritere(['est_active' => 1], ['est_active' => 0]);
            }


            $data = [
                'id_annee_academique' => $idAnneeAcademique,
                'libelle_annee_academique' => $libelleAnneeAcademique,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'est_active' => $estActive
            ];

            $success = $this->anneeAcademiqueModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création de l'année académique.");
            }

            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_ANNEE_ACADEMIQUE',
                "Année académique '{$libelleAnneeAcademique}' (ID: {$idAnneeAcademique}) créée.",
                $idAnneeAcademique,
                'AnneeAcademique'
            );
            return true;
        } catch (DoublonException $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_ANNEE_ACADEMIQUE',
                "Erreur création année académique '{$idAnneeAcademique}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Modifie une année académique existante.
     * @param string $idAnneeAcademique L'ID de l'année académique à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws ElementNonTrouveException Si l'année académique n'est pas trouvée.
     * @throws DoublonException Si le nouveau libellé existe déjà.
     * @throws OperationImpossibleException En cas d'échec de la mise à jour.
     */
    public function modifierAnneeAcademique(string $idAnneeAcademique, array $donnees): bool
    {
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$annee) {
            throw new ElementNonTrouveException("Année académique '{$idAnneeAcademique}' non trouvée.");
        }

        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            // Vérifier les doublons de libellé si le libellé est modifié
            if (isset($donnees['libelle_annee_academique']) && $donnees['libelle_annee_academique'] !== $annee['libelle_annee_academique']) {
                if ($this->anneeAcademiqueModel->trouverUnParCritere(['libelle_annee_academique' => $donnees['libelle_annee_academique']])) {
                    throw new DoublonException("Le libellé d'année académique '{$donnees['libelle_annee_academique']}' existe déjà.");
                }
            }

            // Si l'année est marquée comme active, désactiver les autres.
            // La méthode definirAnneeAcademiqueActive() gère déjà cela atomiquement si elle est appelée ensuite.
            // Donc, il suffit de mettre à jour le statut, et le contrôleur appellera definirAnneeAcademiqueActive si besoin.

            $success = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour de l'année académique.");
            }
            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_ANNEE_ACADEMIQUE',
                "Année académique '{$idAnneeAcademique}' modifiée.",
                $idAnneeAcademique,
                'AnneeAcademique'
            );
            return true;
        } catch (DoublonException $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MODIF_ANNEE_ACADEMIQUE',
                "Erreur modification année académique '{$idAnneeAcademique}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Supprime une année académique.
     * @param string $idAnneeAcademique L'ID de l'année académique à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws ElementNonTrouveException Si l'année académique n'est pas trouvée.
     * @throws OperationImpossibleException Si l'année est active ou a des dépendances (inscriptions, etc.).
     */
    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool
    {
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$annee) {
            throw new ElementNonTrouveException("Année académique '{$idAnneeAcademique}' non trouvée.");
        }
        if ($annee['est_active']) {
            throw new OperationImpossibleException("Impossible de supprimer une année académique active.");
        }

        // Vérifier les dépendances (inscriptions, etc.) avant de supprimer.
        // Cela peut nécessiter des appels à d'autres modèles (ex: InscrireModel)
        $pdo = $this->anneeAcademiqueModel->getDb(); // Accès à la connexion PDO via le modèle
        $inscrireModel = new \App\Backend\Model\Inscrire($pdo);
        if ($inscrireModel->compterParCritere(['id_annee_academique' => $idAnneeAcademique]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer l'année académique : des inscriptions y sont rattachées.");
        }
        // Ajoutez d'autres vérifications de dépendance si nécessaire (rapports, PV...)

        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            $success = $this->anneeAcademiqueModel->supprimerParIdentifiant($idAnneeAcademique);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la suppression de l'année académique.");
            }
            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_ANNEE_ACADEMIQUE',
                "Année académique '{$idAnneeAcademique}' supprimée.",
                $idAnneeAcademique,
                'AnneeAcademique'
            );
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SUPPRESSION_ANNEE_ACADEMIQUE',
                "Erreur suppression année académique '{$idAnneeAcademique}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère une année académique par son ID.
     * (Méthode utilitaire pour le contrôleur AnneeAcademiqueController)
     * @param string $idAnneeAcademique
     * @return array|null
     */
    public function recupererAnneeAcademiqueParId(string $idAnneeAcademique): ?array
    {
        return $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
    }

    /**
     * Liste tous les modèles de notification/email disponibles.
     * @return array Liste des modèles de notification.
     */
    public function listerModelesNotificationEmail(): array
    {
        return $this->notificationModel->trouverTout();
    }

    /**
     * Retourne l'instance du modèle Notification.
     * Utile pour les contrôleurs ou d'autres services qui ont besoin d'interagir directement
     * avec les notifications mais via ce service de configuration.
     * @return Notification
     */
    public function getNotificationModel(): Notification
    {
        return $this->notificationModel;
    }

    /**
     * Liste tous les niveaux d'étude.
     * @return array
     */
    public function listerNiveauxEtude(): array
    {
        return $this->niveauEtudeModel->trouverTout();
    }

    /**
     * Liste tous les statuts de paiement.
     * @return array
     */
    public function listerStatutsPaiement(): array
    {
        return $this->statutPaiementRefModel->trouverTout();
    }

    /**
     * Liste toutes les décisions de passage.
     * @return array
     */
    public function listerDecisionsPassage(): array
    {
        return $this->decisionPassageRefModel->trouverTout();
    }

    /**
     * Liste tous les ECUEs (Éléments Constitutifs d'Unités d'Enseignement).
     * @return array
     */
    public function listerEcues(): array
    {
        return $this->ecueModel->trouverTout();
    }

    /**
     * Liste tous les grades.
     * @return array
     */
    public function listerGrades(): array
    {
        return $this->gradeModel->trouverTout();
    }

    /**
     * Liste toutes les fonctions.
     * @return array
     */
    public function listerFonctions(): array
    {
        return $this->fonctionModel->trouverTout();
    }

    /**
     * Liste toutes les spécialités.
     * @return array
     */
    public function listerSpecialites(): array
    {
        return $this->specialiteModel->trouverTout();
    }

    /**
     * Liste tous les statuts de réclamation.
     * @return array
     */
    public function listerStatutsReclamation(): array
    {
        return $this->statutReclamationRefModel->trouverTout();
    }

    /**
     * Récupère l'année académique actuellement active.
     * @return array|null Les données de l'année académique active ou null si aucune n'est trouvée.
     */
    public function recupererAnneeAcademiqueActive(): ?array
    {
        return $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
    }

    /**
     * Liste tous les statuts de conformité.
     * @return array
     */
    public function listerStatutsConformite(): array
    {
        return $this->statutConformiteRefModel->trouverTout();
    }
}