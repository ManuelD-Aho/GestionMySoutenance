<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\DoublonException;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ConfigSystemeController extends BaseController
{
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->configService = $configService;
    }

    /**
     * Affiche la page principale de configuration du système.
     * Peut afficher les paramètres généraux et les liens vers d'autres sections de configuration.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ACCEDER'); // Exiger la permission d'accéder à la config système

        try {
            $parametresGeneraux = $this->configService->recupererParametresGeneraux();
            $modelesNotifications = $this->configService->listerModelesNotificationEmail(); // Supposons cette méthode existe

            $data = [
                'page_title' => 'Configuration du Système',
                'parametres_generaux' => $parametresGeneraux,
                'modeles_notifications' => $modelesNotifications,
                'annee_academique_menu_items' => [], // Sera géré par AnneeAcademiqueController
                // 'annee_academique' => $this->configService->listerAnneesAcademiques(), // Ou rediriger vers AnneeAcademiqueController
            ];
            $this->render('Administration/ConfigSysteme/parametres_generaux', $data); // Peut être une vue de tableau de bord pour la config
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de la configuration: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    /**
     * Gère la mise à jour des paramètres généraux du système.
     */
    public function updateGeneralParameters(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_PARAM_MAJ'); // Exiger la permission de modifier les paramètres

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/admin/config');
        }

        $parametresData = [
            'max_login_attempts' => $this->getRequestData('max_login_attempts', null),
            'lockout_time_minutes' => $this->getRequestData('lockout_time_minutes', null),
            'password_min_length' => $this->getRequestData('password_min_length', null),
            // Ajoutez tous les paramètres que vous gérez via ce formulaire
        ];

        // Règles de validation pour les paramètres
        $rules = [
            'max_login_attempts' => 'required|integer|min:1',
            'lockout_time_minutes' => 'required|integer|min:1',
            'password_min_length' => 'required|integer|min:8',
        ];

        $this->validator->validate($parametresData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/config');
        }

        try {
            $this->configService->mettreAJourParametresGeneraux($parametresData);
            $this->setFlashMessage('success', 'Paramètres généraux mis à jour avec succès.');
            $this->redirect('/dashboard/admin/config');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config');
        }
    }

    /**
     * Affiche la page de gestion des modèles de documents/notifications.
     */
    public function showDocumentTemplates(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_LISTER'); // Exiger la permission

        try {
            $modeles = $this->configService->listerModelesNotificationEmail(); // Récupère tous les modèles
            $typesDocuments = $this->configService->listerTypesDocument(); // Pour les listes déroulantes

            $data = [
                'page_title' => 'Modèles de Documents & Notifications',
                'modeles' => $modeles,
                'types_documents' => $typesDocuments
            ];
            $this->render('Administration/ConfigSysteme/modeles_documents', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des modèles: " . $e->getMessage());
            $this->redirect('/dashboard/admin/config');
        }
    }

    /**
     * Gère la création ou la modification d'un modèle de document/notification.
     * @param string|null $id L'ID du modèle à modifier, ou null pour une création.
     */
    public function handleDocumentTemplate(string $id = null): void
    {
        $permission = $id ? 'TRAIT_ADMIN_CONFIG_MODELES_DOC_MODIFIER' : 'TRAIT_ADMIN_CONFIG_MODELES_DOC_CREER';
        $this->requirePermission($permission);

        if (!$this->isPostRequest()) {
            $this->setFlashMessage('error', 'Méthode non autorisée.');
            $this->redirect('/dashboard/admin/config/templates'); // Rediriger vers la liste
        }

        $modeleData = [
            'id_notification' => $id ?: $this->getRequestData('id_notification'), // Peut être l'ID généré si pas d'ID auto-gen DB
            'libelle_notification' => $this->getRequestData('libelle_notification'),
            'contenu' => $this->getRequestData('contenu'), // Champ pour le corps du modèle (HTML/texte)
            // Ajouter d'autres champs pertinents du modèle Notification, ex: 'sujet' si séparé du libellé
        ];

        // Règles de validation
        $rules = [
            'id_notification' => 'required|string|max:50',
            'libelle_notification' => 'required|string|max:100',
            'contenu' => 'required|string',
        ];
        $this->validator->validate($modeleData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/config/templates' . ($id ? "/edit/{$id}" : '/create'));
        }

        try {
            $this->configService->gererModeleNotificationEmail($id, $modeleData);
            $this->setFlashMessage('success', 'Modèle de document/notification ' . ($id ? 'mis à jour' : 'créé') . ' avec succès.');
            $this->redirect('/dashboard/admin/config/templates');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/templates' . ($id ? "/edit/{$id}" : '/create'));
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/templates' . ($id ? "/edit/{$id}" : '/create'));
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/templates' . ($id ? "/edit/{$id}" : '/create'));
        }
    }

    /**
     * Supprime un modèle de document/notification.
     * @param string $id L'ID du modèle à supprimer.
     */
    public function deleteDocumentTemplate(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_SUPPRIMER'); // Exiger la permission

        try {
            // Cette méthode doit être ajoutée au ServiceConfigurationSysteme ou utiliser une méthode générique de NotificationModel
            // Par exemple: $this->configService->supprimerModeleNotificationEmail($id);
            // Si la suppression passe par le modèle Notification directement:
            $notificationModel = $this->configService->getNotificationModel(); // Assumons que configService a un getter pour notificationModel
            if (!$notificationModel) {
                // Si pas de getter, instancier ici (moins idéal)
                $pdo = $this->authService->getUtilisateurModel()->getDb(); // Accéder à PDO via un modèle existant
                $notificationModel = new \App\Backend\Model\Notification($pdo);
            }

            if (!$notificationModel->supprimerParIdentifiant($id)) {
                throw new OperationImpossibleException("Échec de la suppression du modèle de notification.");
            }
            $this->setFlashMessage('success', 'Modèle de document/notification supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer le modèle : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/config/templates');
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer
    // car les fonctionnalités spécifiques (paramètres, templates) sont traitées par des méthodes dédiées.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}