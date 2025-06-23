<?php

namespace App\Config;

use Exception;
use App\Backend\Util\FormValidator;
use App\Backend\Util\DatabaseSessionHandler;

// Interfaces des Services
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Checklist\ServiceChecklistInterface;
use App\Backend\Service\Commission\ServiceCommissionInterface;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSystemeInterface;
use App\Backend\Service\Conformite\ServiceConformiteInterface;
use App\Backend\Service\Delegation\ServiceDelegationInterface;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGeneratorInterface;
use App\Backend\Service\Email\ServiceEmailInterface;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademiqueInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Importation\ServiceImportationInterface;
use App\Backend\Service\Messagerie\ServiceMessagerieInterface;
use App\Backend\Service\ModeleRapport\ServiceModeleRapportInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\Permissions\ServicePermissionsInterface;
use App\Backend\Service\Rapport\ServiceRapportInterface;
use App\Backend\Service\Reclamation\ServiceReclamationInterface;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdminInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Transition\ServiceTransitionInterface;

// Implémentations des Services
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Checklist\ServiceChecklist;
use App\Backend\Service\Commission\ServiceCommission;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Service\Conformite\ServiceConformite;
use App\Backend\Service\Delegation\ServiceDelegation;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Service\Importation\ServiceImportation;
use App\Backend\Service\Messagerie\ServiceMessagerie;
use App\Backend\Service\ModeleRapport\ServiceModeleRapport;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\Rapport\ServiceRapport;
use App\Backend\Service\Reclamation\ServiceReclamation;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\Transition\ServiceTransition;

// Contrôleurs
use App\Backend\Controller\HomeController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\DashboardController;
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\Etudiant\DocumentEtudiantController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController;
use App\Backend\Controller\Etudiant\RapportController;
use App\Backend\Controller\Etudiant\ReclamationEtudiantController;
use App\Backend\Controller\Etudiant\RessourcesEtudiantController;
use App\Backend\Controller\PersonnelAdministratif\CommunicationInterneController;
use App\Backend\Controller\PersonnelAdministratif\ConformiteController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;

/**
 * Conteneur d'Injection de Dépendances (DIC).
 * Gère l'instanciation et la résolution des dépendances pour toutes les classes de l'application.
 * Les définitions lient les interfaces aux implémentations concrètes.
 */
class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        // --- FONDATIONS ---
        $this->definitions['PDO'] = fn() => Database::getInstance()->getConnection();
        $this->definitions[DatabaseSessionHandler::class] = fn() => new DatabaseSessionHandler();
        $this->definitions[FormValidator::class] = fn() => new FormValidator();

        // --- SERVICES : LIAISON INTERFACE -> IMPLÉMENTATION ---
        $this->definitions[ServiceSupervisionAdminInterface::class] = fn($c) => new ServiceSupervisionAdmin($c->get('PDO'), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[IdentifiantGeneratorInterface::class] = fn($c) => new IdentifiantGenerator($c->get('PDO'));
        $this->definitions[ServiceEmailInterface::class] = fn($c) => new ServiceEmail($c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[ServiceNotificationInterface::class] = fn($c) => new ServiceNotification($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServicePermissionsInterface::class] = fn($c) => new ServicePermissions($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[ServiceAuthenticationInterface::class] = fn($c) => new ServiceAuthentification($c->get('PDO'), $c->get(ServiceEmailInterface::class), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class), $c->get(ServicePermissionsInterface::class));
        $this->definitions[ServiceDocumentGeneratorInterface::class] = fn($c) => new ServiceDocumentGenerator($c->get('PDO'), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceCommissionInterface::class] = fn($c) => new ServiceCommission($c->get('PDO'), $c->get(IdentifiantGeneratorInterface::class), $c->get(ServiceNotificationInterface::class));
        $this->definitions[ServiceConfigurationSystemeInterface::class] = fn($c) => new ServiceConfigurationSysteme($c->get('PDO'));
        $this->definitions[ServiceConformiteInterface::class] = fn($c) => new ServiceConformite($c->get('PDO'), $c->get(ServiceNotificationInterface::class));
        $this->definitions[ServiceGestionAcademiqueInterface::class] = fn($c) => new ServiceGestionAcademique($c->get('PDO'), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceMessagerieInterface::class] = fn($c) => new ServiceMessagerie($c->get('PDO'), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceRapportInterface::class] = fn($c) => new ServiceRapport($c->get('PDO'), $c->get(ServiceNotificationInterface::class), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceReclamationInterface::class] = fn($c) => new ServiceReclamation($c->get('PDO'), $c->get(ServiceNotificationInterface::class), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceReportingAdminInterface::class] = fn($c) => new ServiceReportingAdmin($c->get('PDO'));
        $this->definitions[ServiceChecklistInterface::class] = fn($c) => new ServiceChecklist($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceDelegationInterface::class] = fn($c) => new ServiceDelegation($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class), $c->get(IdentifiantGeneratorInterface::class));
        $this->definitions[ServiceImportationInterface::class] = fn($c) => new ServiceImportation($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[ServiceModeleRapportInterface::class] = fn($c) => new ServiceModeleRapport($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[ServiceTransitionInterface::class] = fn($c) => new ServiceTransition($c->get('PDO'), $c->get(ServiceSupervisionAdminInterface::class));

        // --- CONTRÔLEURS ---
        $this->definitions[HomeController::class] = fn($c) => new HomeController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[AuthentificationController::class] = fn($c) => new AuthentificationController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[AssetController::class] = fn($c) => new AssetController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[DashboardController::class] = fn($c) => new DashboardController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionAdminInterface::class));

        // Administration
        $this->definitions[AdminDashboardController::class] = fn($c) => new AdminDashboardController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceReportingAdminInterface::class), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[ConfigSystemeController::class] = fn($c) => new ConfigSystemeController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[GestionAcadController::class] = fn($c) => new GestionAcadController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[HabilitationController::class] = fn($c) => new HabilitationController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class));
        $this->definitions[ReferentialController::class] = fn($c) => new ReferentialController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class));
        $this->definitions[ReportingController::class] = fn($c) => new ReportingController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceReportingAdminInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[SupervisionController::class] = fn($c) => new SupervisionController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionAdminInterface::class));
        $this->definitions[UtilisateurController::class] = fn($c) => new UtilisateurController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));

        // Commission
        $this->definitions[CommissionDashboardController::class] = fn($c) => new CommissionDashboardController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceCommissionInterface::class), $c->get(ServiceNotificationInterface::class));
        $this->definitions[CommunicationCommissionController::class] = fn($c) => new CommunicationCommissionController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceMessagerieInterface::class));
        $this->definitions[CorrectionCommissionController::class] = fn($c) => new CorrectionCommissionController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceCommissionInterface::class), $c->get(ServiceNotificationInterface::class));
        $this->definitions[PvController::class] = fn($c) => new PvController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceCommissionInterface::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceGestionAcademiqueInterface::class));
        $this->definitions[ValidationRapportController::class] = fn($c) => new ValidationRapportController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceCommissionInterface::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));

        // Common
        $this->definitions[NotificationController::class] = fn($c) => new NotificationController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceNotificationInterface::class));

        // Etudiant
        $this->definitions[DocumentEtudiantController::class] = fn($c) => new DocumentEtudiantController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceDocumentGeneratorInterface::class));
        $this->definitions[EtudiantDashboardController::class] = fn($c) => new EtudiantDashboardController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceNotificationInterface::class), $c->get(ServiceReclamationInterface::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[ProfilEtudiantController::class] = fn($c) => new ProfilEtudiantController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class));
        $this->definitions[RapportController::class] = fn($c) => new RapportController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[ReclamationEtudiantController::class] = fn($c) => new ReclamationEtudiantController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceReclamationInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[RessourcesEtudiantController::class] = fn($c) => new RessourcesEtudiantController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class));

        // Personnel Administratif
        $this->definitions[CommunicationInterneController::class] = fn($c) => new CommunicationInterneController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceMessagerieInterface::class));
        $this->definitions[ConformiteController::class] = fn($c) => new ConformiteController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceConformiteInterface::class), $c->get(ServiceRapportInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
        $this->definitions[PersonnelDashboardController::class] = fn($c) => new PersonnelDashboardController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceConformiteInterface::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceReclamationInterface::class), $c->get(ServiceNotificationInterface::class));
        $this->definitions[ScolariteController::class] = fn($c) => new ScolariteController($c->get(ServiceAuthenticationInterface::class), $c->get(ServicePermissionsInterface::class), $c->get(FormValidator::class), $c->get(ServiceGestionAcademiqueInterface::class), $c->get(ServiceReclamationInterface::class), $c->get(ServiceDocumentGeneratorInterface::class), $c->get(ServiceConfigurationSystemeInterface::class));
    }

    /**
     * Récupère une instance d'une classe définie dans le conteneur.
     * Utilise le lazy loading : l'instance n'est créée que lors du premier appel.
     * @param string $id L'identifiant de la classe (son nom complet, généralement l'interface).
     * @return mixed L'instance de la classe.
     * @throws Exception Si la définition n'est pas trouvée.
     */
    public function get(string $id)
    {
        if (!isset($this->definitions[$id])) {
            throw new Exception("Définition de service ou de classe non trouvée dans le conteneur : " . $id);
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->definitions[$id]($this);
        }

        return $this->instances[$id];
    }
}