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
use App\Backend\Util\DatabaseSessionHandler;
use App\Backend\Util\FormValidator;

use App\Backend\Controller\{
    HomeController, AuthentificationController, DashboardController, AssetController
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

/**
 * Classe Container pour l'Injection de Dépendances (DI).
 * Gère l'instanciation et la fourniture de toutes les dépendances de l'application (services, modèles, contrôleurs).
 * Utilise le pattern Singleton pour les instances de service afin d'optimiser les performances.
 */
class Container
{
    /** @var array Stocke les définitions (closures) pour créer les services. */
    private array $definitions = [];

    /** @var array Cache les instances déjà créées pour éviter de les reconstruire. */
    private array $instances = [];

    public function __construct()
    {
        $this->registerDefinitions();
    }

    /**
     * Enregistre une définition de service dans le conteneur.
     *
     * @param string $id L'identifiant unique du service (généralement le nom de la classe/interface).
     * @param callable $definition Une closure qui prend le conteneur en paramètre et retourne l'instance du service.
     */
    public function set(string $id, callable $definition): void
    {
        $this->definitions[$id] = $definition;
    }

    /**
     * Récupère une instance de service depuis le conteneur.
     * Si l'instance n'existe pas, elle est créée, mise en cache et retournée.
     *
     * @param string $id L'identifiant du service à récupérer.
     * @return mixed L'instance du service.
     * @throws \InvalidArgumentException Si le service n'est pas défini.
     */
    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->definitions[$id])) {
            throw new \InvalidArgumentException("Service ou dépendance non défini : {$id}");
        }

        $definition = $this->definitions[$id];
        $this->instances[$id] = $definition($this);

        return $this->instances[$id];
    }

    /**
     * Méthode d'usine pour créer des instances de GenericModel à la volée pour n'importe quelle table.
     * Cela évite de devoir créer une classe de modèle pour chaque table de référence simple.
     *
     * @param string $tableName Le nom de la table.
     * @param string|array $primaryKey La ou les clés primaires de la table.
     * @return GenericModel Une instance de GenericModel configurée pour la table spécifiée.
     */
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

    /**
     * Enregistre toutes les définitions de dépendances de l'application.
     * C'est le point central de la configuration de l'injection de dépendances.
     */
    private function registerDefinitions(): void
    {
        // --- 1. Dépendances Fondamentales (Base de données, Session) ---
        $this->set(PDO::class, function() {
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

        $this->set(DatabaseSessionHandler::class, function($c) {
            return new DatabaseSessionHandler($c->get(PDO::class));
        });

        // --- 2. Modèles Spécifiques ---
        $this->set(Utilisateur::class, fn($c) => new Utilisateur($c->get(PDO::class)));
        $this->set(HistoriqueMotDePasse::class, fn($c) => new HistoriqueMotDePasse($c->get(PDO::class)));
        $this->set(Sessions::class, fn($c) => new Sessions($c->get(PDO::class)));
        $this->set(Delegation::class, fn($c) => new Delegation($c->get(PDO::class)));
        $this->set(RapportEtudiant::class, fn($c) => new RapportEtudiant($c->get(PDO::class)));
        $this->set(Reclamation::class, fn($c) => new Reclamation($c->get(PDO::class)));

        // --- 3. Utilitaires ---
        $this->set(FormValidator::class, fn() => new FormValidator());

        // --- 4. Services (Couche Métier) ---
        $this->registerServices();

        // --- 5. Contrôleurs (Couche de Présentation) ---
        $this->registerControllers();
    }

    /**
     * Enregistre les définitions de tous les services de l'application.
     */
    private function registerServices(): void
    {
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

        $this->set(ServiceSystemeInterface::class, function($c) {
            return new ServiceSysteme(
                $c->get(PDO::class),
                $c->getModelForTable('parametres_systeme', 'cle'),
                $c->getModelForTable('annee_academique', 'id_annee_academique'),
                $c->getModelForTable('sequences', ['nom_sequence', 'annee']),
                $c->get(ServiceSupervisionInterface::class),
                $c // Passage du conteneur pour getModelForTable dans ServiceSysteme
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
                $c->get(ServiceSystemeInterface::class),
                $c->get(ServiceSupervisionInterface::class)
            );
            // Injection par "setter" pour les dépendances optionnelles ou circulaires
            $service->setCommunicationService($c->get(ServiceCommunicationInterface::class));
            $service->setDocumentService($c->get(ServiceDocumentInterface::class)); // Correction: Injection de la dépendance manquante
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

    /**
     * Enregistre les définitions de tous les contrôleurs de l'application.
     * ✅ CORRECTION #2: Passage à l'injection de dépendances explicites dans les constructeurs
     * au lieu du pattern "Service Locator" (injection du conteneur entier).
     * Cela rend les dépendances de chaque contrôleur claires et le code plus maintenable.
     */
    private function registerControllers(): void
    {
        // Contrôleurs de base
        $this->set(HomeController::class, fn($c) => new HomeController(
            $c->get(ServiceSystemeInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class)
        ));
        $this->set(AuthentificationController::class, fn($c) => new AuthentificationController(
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceCommunicationInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSupervisionInterface::class)
        ));
        $this->set(DashboardController::class, fn($c) => new DashboardController(
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class)
        ));
        $this->set(AssetController::class, fn($c) => new AssetController(
            $c->get(ServiceDocumentInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class)
        ));

        // Contrôleurs d'Administration
        $this->set(AdminDashboardController::class, fn($c) => new AdminDashboardController(
            $c->get(ServiceSupervisionInterface::class),
            $c->get(ServiceSystemeInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(ConfigurationController::class, fn($c) => new ConfigurationController(
            $c->get(ServiceSystemeInterface::class),
            $c->get(ServiceDocumentInterface::class),
            $c->get(ServiceCommunicationInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class),
            $c // Pour getModelForTable dans ConfigurationController
        ));
        $this->set(SupervisionController::class, fn($c) => new SupervisionController(
            $c->get(ServiceSupervisionInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(UtilisateurController::class, fn($c) => new UtilisateurController(
            $c->get(ServiceUtilisateurInterface::class),
            $c->get(ServiceSystemeInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));

        // Contrôleurs de Commission
        $this->set(CommissionDashboardController::class, fn($c) => new CommissionDashboardController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(WorkflowCommissionController::class, fn($c) => new WorkflowCommissionController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));

        // Contrôleurs Étudiant
        $this->set(EtudiantDashboardController::class, fn($c) => new EtudiantDashboardController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(ServiceParcoursAcademiqueInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(ProfilEtudiantController::class, fn($c) => new ProfilEtudiantController(
            $c->get(ServiceUtilisateurInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(RapportController::class, fn($c) => new RapportController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));

        // Contrôleurs Personnel Administratif
        $this->set(PersonnelDashboardController::class, fn($c) => new PersonnelDashboardController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(ServiceUtilisateurInterface::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
        $this->set(ScolariteController::class, fn($c) => new ScolariteController(
            $c->get(ServiceWorkflowSoutenanceInterface::class),
            $c->get(ServiceUtilisateurInterface::class),
            $c->get(ServiceParcoursAcademiqueInterface::class),
            $c->get(ServiceSystemeInterface::class),
            $c->get(ServiceDocumentInterface::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSecuriteInterface::class),
            $c->get(ServiceSupervisionInterface::class) // BaseController
        ));
    }
}