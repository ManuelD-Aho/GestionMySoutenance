<?php

namespace App\Config;

use PDO;

// Imports des modèles
use App\Backend\Model\{
    Utilisateur, HistoriqueMotDePasse, Sessions, Delegation, RapportEtudiant,
    Reclamation, GenericModel
};

// Imports des services
use App\Backend\Service\Communication\{ServiceCommunication, ServiceCommunicationInterface};
use App\Backend\Service\Document\{ServiceDocument, ServiceDocumentInterface};
use App\Backend\Service\ParcoursAcademique\{ServiceParcoursAcademique, ServiceParcoursAcademiqueInterface};
use App\Backend\Service\Securite\{ServiceSecurite, ServiceSecuriteInterface};
use App\Backend\Service\Supervision\{ServiceSupervision, ServiceSupervisionInterface};
use App\Backend\Service\Systeme\{ServiceSysteme, ServiceSystemeInterface};
use App\Backend\Service\Utilisateur\{ServiceUtilisateur, ServiceUtilisateurInterface};
use App\Backend\Service\WorkflowSoutenance\{ServiceWorkflowSoutenance, ServiceWorkflowSoutenanceInterface};
use App\Backend\Service\Delegation\{ServiceDelegation, ServiceDelegationInterface};
use App\Backend\Service\Fichier\{ServiceFichier, ServiceFichierInterface};
use App\Backend\Service\Logger\{ServiceLogger, ServiceLoggerInterface};
use App\Backend\Service\Queue\{ServiceQueue, ServiceQueueInterface};
use App\Backend\Service\Reporting\{ServiceReporting, ServiceReportingInterface};

// Imports des utilitaires
use App\Backend\Util\DatabaseSessionHandler;
use App\Backend\Util\FormValidator;

// Imports des contrôleurs
use App\Backend\Controller\{
    HomeController, AuthentificationController, DashboardController, AssetController
};
use App\Backend\Controller\Administration\{AdminDashboardController,
    AnneeAcademiqueController,
    ConfigSystemeController,
    FichierController,
    GestionAcadController,
    HabilitationController,
    LoggerController,
    NotificationConfigurationController,
    QueueController,
    ReferentialController,
    ReportingController,
    SupervisionController,
    TransitionRoleController,
    UtilisateurController};
use App\Backend\Controller\Commission\{
    CommissionDashboardController, WorkflowCommissionController
};
use App\Backend\Controller\Etudiant\{
    EtudiantDashboardController, ProfilEtudiantController, RapportController
};
use App\Backend\Controller\PersonnelAdministratif\{
    PersonnelDashboardController, ScolariteController
};

class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        $this->registerDefinitions();
    }

    public function set(string $id, callable $definition): void
    {
        $this->definitions[$id] = $definition;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (!isset($this->definitions[$id])) {
            throw new \InvalidArgumentException("Service ou dépendance non défini : {$id}");
        }
        $this->instances[$id] = $this->definitions[$id]($this);
        return $this->instances[$id];
    }

    public function getModelForTable(string $tableName, string|array $primaryKey = 'id'): GenericModel
    {
        $id = GenericModel::class . '_' . $tableName;
        if (!isset($this->definitions[$id])) {
            $this->set($id, fn($c) => new GenericModel($c->get(PDO::class), $tableName, $primaryKey));
        }
        return $this->get($id);
    }

    private function registerDefinitions(): void
    {
        // --- 1. Dépendances Fondamentales ---
        $this->set(PDO::class, fn() => Database::getInstance()->getConnection());
        $this->set(DatabaseSessionHandler::class, fn($c) => new DatabaseSessionHandler());
        $this->set(FormValidator::class, fn() => new FormValidator());

        // --- 2. Modèles Spécifiques ---
        $this->set(Utilisateur::class, fn($c) => new Utilisateur($c->get(PDO::class)));
        $this->set(HistoriqueMotDePasse::class, fn($c) => new HistoriqueMotDePasse($c->get(PDO::class)));
        $this->set(Sessions::class, fn($c) => new Sessions($c->get(PDO::class)));
        $this->set(Delegation::class, fn($c) => new Delegation($c->get(PDO::class)));
        $this->set(RapportEtudiant::class, fn($c) => new RapportEtudiant($c->get(PDO::class)));
        $this->set(Reclamation::class, fn($c) => new Reclamation($c->get(PDO::class)));

        // --- 3. Services ---
        $this->registerServices();

        // --- 4. Contrôleurs ---
        $this->registerControllers();
    }

    private function registerServices(): void
    {
        // Services existants
        $this->set(ServiceSupervisionInterface::class, fn($c) => new ServiceSupervision($c->get(PDO::class), $c->getModelForTable('enregistrer', 'id_enregistrement'), $c->getModelForTable('pister', 'id_piste'), $c->getModelForTable('action', 'id_action'), $c->getModelForTable('queue_jobs', 'id'), $c->get(Utilisateur::class), $c->get(RapportEtudiant::class)));
        $this->set(ServiceSystemeInterface::class, fn($c) => new ServiceSysteme($c->get(PDO::class), $c->getModelForTable('parametres_systeme', 'cle'), $c->getModelForTable('annee_academique', 'id_annee_academique'), $c->getModelForTable('sequences', ['nom_sequence', 'annee']), $c->get(ServiceSupervisionInterface::class), $c));
        $this->set(ServiceSecuriteInterface::class, fn($c) => new ServiceSecurite($c->get(PDO::class), $c->get(Utilisateur::class), $c->get(HistoriqueMotDePasse::class), $c->get(Sessions::class), $c->getModelForTable('rattacher', ['id_groupe_utilisateur', 'id_traitement']), $c->getModelForTable('traitement', 'id_traitement'), $c->get(Delegation::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceCommunicationInterface::class, fn($c) => new ServiceCommunication($c->get(PDO::class), $c->getModelForTable('notification', 'id_notification'), $c->getModelForTable('recevoir', 'id_reception'), $c->getModelForTable('conversation', 'id_conversation'), $c->getModelForTable('message_chat', 'id_message_chat'), $c->getModelForTable('participant_conversation', ['id_conversation', 'numero_utilisateur']), $c->getModelForTable('matrice_notification_regles', 'id_regle'), $c->get(Utilisateur::class), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceDocumentInterface::class, fn($c) => new ServiceDocument($c->get(PDO::class), $c->getModelForTable('document_genere', 'id_document_genere'), $c->getModelForTable('rapport_modele', 'id_modele'), $c->getModelForTable('etudiant', 'numero_carte_etudiant'), $c->getModelForTable('inscrire', ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique']), $c->getModelForTable('evaluer', ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique']), $c->getModelForTable('compte_rendu', 'id_compte_rendu'), $c->getModelForTable('annee_academique', 'id_annee_academique'), $c->get(RapportEtudiant::class), $c->getModelForTable('section_rapport', ['id_rapport_etudiant', 'titre_section']), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceParcoursAcademiqueInterface::class, fn($c) => new ServiceParcoursAcademique($c->get(PDO::class), $c->getModelForTable('inscrire', ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique']), $c->getModelForTable('evaluer', ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique']), $c->getModelForTable('faire_stage', ['id_entreprise', 'numero_carte_etudiant']), $c->getModelForTable('penalite', 'id_penalite'), $c->getModelForTable('ue', 'id_ue'), $c->getModelForTable('ecue', 'id_ecue'), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceUtilisateurInterface::class, function($c) { $service = new ServiceUtilisateur($c->get(PDO::class), $c->get(Utilisateur::class), $c->getModelForTable('etudiant', 'numero_carte_etudiant'), $c->getModelForTable('enseignant', 'numero_enseignant'), $c->getModelForTable('personnel_administratif', 'numero_personnel_administratif'), $c->get(Delegation::class), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSupervisionInterface::class)); $service->setCommunicationService($c->get(ServiceCommunicationInterface::class)); $service->setDocumentService($c->get(ServiceDocumentInterface::class)); return $service; });
        $this->set(ServiceWorkflowSoutenanceInterface::class, fn($c) => new ServiceWorkflowSoutenance($c->get(PDO::class), $c->get(RapportEtudiant::class), $c->get(Reclamation::class), $c->getModelForTable('section_rapport', ['id_rapport_etudiant', 'titre_section']), $c->getModelForTable('approuver', ['numero_personnel_administratif', 'id_rapport_etudiant']), $c->getModelForTable('conformite_rapport_details', 'id_conformite_detail'), $c->getModelForTable('vote_commission', 'id_vote'), $c->getModelForTable('compte_rendu', 'id_compte_rendu'), $c->getModelForTable('session_validation', 'id_session'), $c->getModelForTable('session_rapport', ['id_session', 'id_rapport_etudiant']), $c->getModelForTable('affecter', ['numero_enseignant', 'id_rapport_etudiant', 'id_statut_jury']), $c->get(ServiceCommunicationInterface::class), $c->get(ServiceDocumentInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(ServiceSystemeInterface::class)));

        // Nouveaux services
        $this->set(ServiceDelegationInterface::class, fn($c) => new ServiceDelegation($c->get(PDO::class), $c->get(ServiceSupervisionInterface::class), $c->get(ServiceSecuriteInterface::class)));
        $this->set(ServiceFichierInterface::class, fn($c) => new ServiceFichier($c->get(PDO::class), $c->get(ServiceSupervisionInterface::class), $c->get(ServiceSecuriteInterface::class)));
        $this->set(ServiceLoggerInterface::class, fn($c) => new ServiceLogger($c->get(PDO::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceQueueInterface::class, fn($c) => new ServiceQueue($c->get(PDO::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(ServiceReportingInterface::class, fn($c) => new ServiceReporting($c->get(PDO::class), $c->get(ServiceSupervisionInterface::class)));
    }

    private function registerControllers(): void
    {
        // Contrôleurs de base
        $this->set(HomeController::class, fn($c) => new HomeController($c->get(ServiceSystemeInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(AuthentificationController::class, fn($c) => new AuthentificationController($c->get(ServiceSecuriteInterface::class), $c->get(ServiceCommunicationInterface::class), $c->get(FormValidator::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(DashboardController::class, fn($c) => new DashboardController($c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(AssetController::class, fn($c) => new AssetController($c->get(ServiceDocumentInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));

        // Contrôleurs d'Administration (nouveaux et anciens)
        $this->set(AdminDashboardController::class, fn($c) => new AdminDashboardController($c->get(ServiceSupervisionInterface::class), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(FormValidator::class)));
        $this->set(AnneeAcademiqueController::class, fn($c) => new AnneeAcademiqueController($c->get(ServiceSystemeInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(ConfigSystemeController::class, fn($c) => new ConfigSystemeController($c->get(ServiceSystemeInterface::class), $c->get(ServiceDocumentInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(FichierController::class, fn($c) => new FichierController($c->get(ServiceFichierInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(GestionAcadController::class, fn($c) => new GestionAcadController($c->get(ServiceParcoursAcademiqueInterface::class), $c->get(ServiceUtilisateurInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(HabilitationController::class, fn($c) => new HabilitationController($c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(LoggerController::class, fn($c) => new LoggerController($c->get(ServiceLoggerInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(NotificationConfigurationController::class, fn($c) => new NotificationConfigurationController($c->get(ServiceCommunicationInterface::class), $c->get(ServiceSystemeInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(QueueController::class, fn($c) => new QueueController($c->get(ServiceQueueInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(ReferentialController::class, fn($c) => new ReferentialController($c->get(ServiceSystemeInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(ReportingController::class, fn($c) => new ReportingController($c->get(ServiceReportingInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(SupervisionController::class, fn($c) => new SupervisionController($c->get(ServiceSupervisionInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(FormValidator::class)));
        $this->set(TransitionRoleController::class, fn($c) => new TransitionRoleController($c->get(ServiceDelegationInterface::class), $c->get(ServiceUtilisateurInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(UtilisateurController::class, fn($c) => new UtilisateurController($c->get(ServiceUtilisateurInterface::class), $c->get(ServiceSystemeInterface::class), $c->get(FormValidator::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class)));

        // Contrôleurs des autres sections
        $this->set(CommissionDashboardController::class, fn($c) => new CommissionDashboardController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(WorkflowCommissionController::class, fn($c) => new WorkflowCommissionController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(FormValidator::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(EtudiantDashboardController::class, fn($c) => new EtudiantDashboardController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(ServiceParcoursAcademiqueInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(ProfilEtudiantController::class, fn($c) => new ProfilEtudiantController($c->get(ServiceUtilisateurInterface::class), $c->get(FormValidator::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(RapportController::class, fn($c) => new RapportController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(FormValidator::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class)));
        $this->set(PersonnelDashboardController::class, fn($c) => new PersonnelDashboardController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(ServiceUtilisateurInterface::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class), $c->get(FormValidator::class)));
        $this->set(ScolariteController::class, fn($c) => new ScolariteController($c->get(ServiceWorkflowSoutenanceInterface::class), $c->get(ServiceUtilisateurInterface::class), $c->get(ServiceParcoursAcademiqueInterface::class), $c->get(ServiceSystemeInterface::class), $c->get(ServiceDocumentInterface::class), $c->get(FormValidator::class), $c->get(ServiceSecuriteInterface::class), $c->get(ServiceSupervisionInterface::class)));
    }
}