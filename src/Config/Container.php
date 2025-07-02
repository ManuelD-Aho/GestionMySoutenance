<?php
// src/Config/Container.php

namespace App\Config;

use PDO;
use App\Backend\Model\{
    Utilisateur, HistoriqueMotDePasse, Sessions, Delegation, RapportEtudiant, Reclamation, GenericModel
};
use App\Backend\Service\Communication\{ServiceCommunication, ServiceCommunicationInterface};
use App\Backend\Service\Document\{ServiceDocument, ServiceDocumentInterface};
use App\Backend\Service\ParcoursAcademique\{ServiceParcoursAcademique, ServiceParcoursAcademiqueInterface};
use App\Backend\Service\Securite\{ServiceSecurite, ServiceSecuriteInterface};
use App\Backend\Service\Supervision\{ServiceSupervision, ServiceSupervisionInterface};
use App\Backend\Service\Systeme\{ServiceSysteme, ServiceSystemeInterface};
use App\Backend\Service\Utilisateur\{ServiceUtilisateur, ServiceUtilisateurInterface};
use App\Backend\Service\WorkflowSoutenance\{ServiceWorkflowSoutenance, ServiceWorkflowSoutenanceInterface};
use App\Backend\Util\FormValidator;

use App\Backend\Controller\{
    HomeController, AuthentificationController, DashboardController, AssetController, BaseController
};
use App\Backend\Controller\Administration\{
    AdminDashboardController, ConfigurationController, SupervisionController, UtilisateurController
};
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
        $this->defineDatabase();
        $this->defineModels();
        $this->defineUtilities();
        $this->defineServices();
        $this->defineControllers();
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
            throw new \InvalidArgumentException("Service ou modèle non défini: {$id}");
        }
        $definition = $this->definitions[$id];
        $instance = $definition($this);
        $this->instances[$id] = $instance;
        return $instance;
    }

    public function getModelForTable(string $tableName, string|array $primaryKey = 'id'): GenericModel
    {
        $id = GenericModel::class . '_' . $tableName;
        if (!isset($this->definitions[$id])) {
            $this->set($id, function($c) use ($tableName, $primaryKey) {
                return new GenericModel($c->get(PDO::class), $tableName, $primaryKey);
            });
        }
        return $this->get($id);
    }

    private function defineDatabase(): void
    {
        $this->set(PDO::class, function($c) {
            $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
            $dbName = $_ENV['DB_DATABASE'] ?? 'mysoutenance';
            $dbUser = $_ENV['DB_USER'] ?? 'root';
            $dbPass = $_ENV['DB_PASSWORD'] ?? '';
            $dbCharset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
            return new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        });
    }

    private function defineModels(): void
    {
        $pdo = $this->get(PDO::class);
        $this->set(Utilisateur::class, fn($c) => new Utilisateur($pdo));
        $this->set(HistoriqueMotDePasse::class, fn($c) => new HistoriqueMotDePasse($pdo));
        $this->set(Sessions::class, fn($c) => new Sessions($pdo));
        $this->set(Delegation::class, fn($c) => new Delegation($pdo));
        $this->set(RapportEtudiant::class, fn($c) => new RapportEtudiant($pdo));
        $this->set(Reclamation::class, fn($c) => new Reclamation($pdo));
    }

    private function defineUtilities(): void
    {
        $this->set(FormValidator::class, fn($c) => new FormValidator());
    }


    private function defineServices(): void
    {
        $this->set(ServiceSystemeInterface::class, function($c) {
            return new ServiceSysteme(
                $c->get(PDO::class),
                $c->getModelForTable('parametres_systeme', 'cle'),
                $c->getModelForTable('annee_academique', 'id_annee_academique'),
                $c->getModelForTable('sequences', ['nom_sequence', 'annee']),
                $c->get(ServiceSupervisionInterface::class),
                $c
            );
        });

        $this->set(ServiceSupervisionInterface::class, function($c) {
            return new ServiceSupervision(
                $c->get(PDO::class),
                $c->getModelForTable('enregistrer', 'id_enregistrement'),
                $c->getModelForTable('pister', 'id_piste'),
                $c->getModelForTable('action', 'id_action'),
                $c->getModelForTable('queue_jobs', 'id'),
                $c->get(Utilisateur::class),
                $c->get(RapportEtudiant::class)
            );
        });

        $this->set(ServiceSecuriteInterface::class, function($c) {
            return new ServiceSecurite(
                $c->get(PDO::class),
                $c->get(Utilisateur::class),
                $c->get(HistoriqueMotDePasse::class),
                $c->get(Sessions::class),
                $c->getModelForTable('rattacher', ['id_groupe_utilisateur', 'id_traitement']),
                $c->getModelForTable('traitement', 'id_traitement'),
                $c->get(Delegation::class),
                $c->get(ServiceSupervisionInterface::class)
            );
        });

        $this->set(ServiceCommunicationInterface::class, function($c) {
            return new ServiceCommunication(
                $c->get(PDO::class),
                $c->getModelForTable('notification', 'id_notification'),
                $c->getModelForTable('recevoir', 'id_reception'),
                $c->getModelForTable('conversation', 'id_conversation'),
                $c->getModelForTable('message_chat', 'id_message_chat'),
                $c->getModelForTable('participant_conversation', ['id_conversation', 'numero_utilisateur']),
                $c->getModelForTable('matrice_notification_regles', 'id_regle'),
                $c->get(Utilisateur::class),
                $c->get(ServiceSystemeInterface::class),
                $c->get(ServiceSupervisionInterface::class)
            );
        });

        $this->set(ServiceDocumentInterface::class, function($c) {
            return new ServiceDocument(
                $c->get(PDO::class),
                $c->getModelForTable('document_genere', 'id_document_genere'),
                $c->getModelForTable('rapport_modele', 'id_modele'),
                $c->getModelForTable('etudiant', 'numero_carte_etudiant'),
                $c->getModelForTable('inscrire', ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique']),
                $c->getModelForTable('evaluer', ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique']),
                $c->getModelForTable('compte_rendu', 'id_compte_rendu'),
                $c->getModelForTable('annee_academique', 'id_annee_academique'),
                $c->get(RapportEtudiant::class),
                $c->getModelForTable('section_rapport', ['id_rapport_etudiant', 'titre_section']),
                $c->get(ServiceSystemeInterface::class),
                $c->get(ServiceSupervisionInterface::class)
            );
        });

        $this->set(ServiceParcoursAcademiqueInterface::class, function($c) {
            return new ServiceParcoursAcademique(
                $c->get(PDO::class),
                $c->getModelForTable('inscrire', ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique']),
                $c->getModelForTable('evaluer', ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique']),
                $c->getModelForTable('faire_stage', ['id_entreprise', 'numero_carte_etudiant']),
                $c->getModelForTable('penalite', 'id_penalite'),
                $c->getModelForTable('ue', 'id_ue'),
                $c->getModelForTable('ecue', 'id_ecue'),
                $c->get(ServiceSystemeInterface::class),
                $c->get(ServiceSupervisionInterface::class)
            );
        });

        $this->set(ServiceUtilisateurInterface::class, function($c) {
            $service = new ServiceUtilisateur(
                $c->get(PDO::class),
                $c->get(Utilisateur::class),
                $c->getModelForTable('etudiant', 'numero_carte_etudiant'),
                $c->getModelForTable('enseignant', 'numero_enseignant'),
                $c->getModelForTable('personnel_administratif', 'numero_personnel_administratif'),
                $c->get(Delegation::class),
                $c->get(RapportEtudiant::class),
                $c->getModelForTable('vote_commission', 'id_vote'),
                $c->getModelForTable('compte_rendu', 'id_compte_rendu'),
                $c->get(ServiceSystemeInterface::class),
                $c->get(ServiceSupervisionInterface::class)
            );
            $service->setCommunicationService($c->get(ServiceCommunicationInterface::class));
            $service->setDocumentService($c->get(ServiceDocumentInterface::class));
            return $service;
        });

        $this->set(ServiceWorkflowSoutenanceInterface::class, function($c) {
            return new ServiceWorkflowSoutenance(
                $c->get(PDO::class),
                $c->get(RapportEtudiant::class),
                $c->get(Reclamation::class),
                $c->getModelForTable('section_rapport', ['id_rapport_etudiant', 'titre_section']),
                $c->getModelForTable('approuver', ['numero_personnel_administratif', 'id_rapport_etudiant']),
                $c->getModelForTable('conformite_rapport_details', 'id_conformite_detail'),
                $c->getModelForTable('vote_commission', 'id_vote'),
                $c->getModelForTable('compte_rendu', 'id_compte_rendu'),
                $c->getModelForTable('session_validation', 'id_session'),
                $c->getModelForTable('session_rapport', ['id_session', 'id_rapport_etudiant']),
                $c->getModelForTable('affecter', ['numero_enseignant', 'id_rapport_etudiant', 'id_statut_jury']),
                $c->get(ServiceCommunicationInterface::class),
                $c->get(ServiceDocumentInterface::class),
                $c->get(ServiceSupervisionInterface::class),
                $c->get(ServiceSystemeInterface::class)
            );
        });
    }

    private function defineControllers(): void
    {
        // ✅ CORRECTION : Tous les contrôleurs sont instanciés de la même manière simple.
        $this->set(BaseController::class, fn($c) => new class($c) extends BaseController {});
        $this->set(HomeController::class, fn($c) => new HomeController($c));
        $this->set(AuthentificationController::class, fn($c) => new AuthentificationController($c));
        $this->set(DashboardController::class, fn($c) => new DashboardController($c));
        $this->set(AssetController::class, fn($c) => new AssetController($c));
        $this->set(AdminDashboardController::class, fn($c) => new AdminDashboardController($c));
        $this->set(ConfigurationController::class, fn($c) => new ConfigurationController($c));
        $this->set(SupervisionController::class, fn($c) => new SupervisionController($c));
        $this->set(UtilisateurController::class, fn($c) => new UtilisateurController($c));
        $this->set(CommissionDashboardController::class, fn($c) => new CommissionDashboardController($c));
        $this->set(WorkflowCommissionController::class, fn($c) => new WorkflowCommissionController($c));
        $this->set(EtudiantDashboardController::class, fn($c) => new EtudiantDashboardController($c));
        $this->set(ProfilEtudiantController::class, fn($c) => new ProfilEtudiantController($c));
        $this->set(RapportController::class, fn($c) => new RapportController($c));
        $this->set(PersonnelDashboardController::class, fn($c) => new PersonnelDashboardController($c));
        $this->set(ScolariteController::class, fn($c) => new ScolariteController($c));
    }
}