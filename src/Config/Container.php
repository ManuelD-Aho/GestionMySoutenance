<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use Exception;
use ReflectionClass;
use ReflectionNamedType;
use App\Backend\Util\FormValidator;
use App\Backend\Util\DatabaseSessionHandler;

// --- Importation des Interfaces de Service ---
// C'est la bonne pratique : les dépendances doivent se baser sur des contrats (interfaces).
use App\Backend\Service\Interface\AdministrationRBACServiceInterface;
use App\Backend\Service\Interface\AnneeAcademiqueServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\CommissionServiceInterface;
use App\Backend\Service\Interface\CompteUtilisateurServiceInterface;
use App\Backend\Service\Interface\ConformiteServiceInterface;
use App\Backend\Service\Interface\CursusServiceInterface;
use App\Backend\Service\Interface\DocumentAdministratifServiceInterface;
use App\Backend\Service\Interface\DocumentGeneratorServiceInterface;
use App\Backend\Service\Interface\EmailServiceInterface;
use App\Backend\Service\Interface\FichierServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Service\Interface\InscriptionServiceInterface;
use App\Backend\Service\Interface\LoggerServiceInterface;
use App\Backend\Service\Interface\MessagerieServiceInterface;
use App\Backend\Service\Interface\NotationServiceInterface;
use App\Backend\Service\Interface\NotificationConfigurationServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\ParametrageServiceInterface;
use App\Backend\Service\Interface\PenaliteServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Service\Interface\PersonnelAcademiqueServiceInterface;
use App\Backend\Service\Interface\ProcesVerbalServiceInterface;
use App\Backend\Service\Interface\ProfilEtudiantServiceInterface;
use App\Backend\Service\Interface\QueueServiceInterface;
use App\Backend\Service\Interface\RapportServiceInterface;
use App\Backend\Service\Interface\ReclamationServiceInterface;
use App\Backend\Service\Interface\ReferentielServiceInterface;
use App\Backend\Service\Interface\ReportingServiceInterface;
use App\Backend\Service\Interface\StageServiceInterface;
use App\Backend\Service\Interface\SupervisionAdminServiceInterface;
use App\Backend\Service\Interface\TransitionRoleServiceInterface;

// --- Importation des Implémentations de Service ---
use App\Backend\Service\AdministrationRBACService;
use App\Backend\Service\ServiceAnneeAcademique;
use App\Backend\Service\ServiceAudit;
use App\Backend\Service\ServiceAuthentication;
use App\Backend\Service\ServiceCommission;
use App\Backend\Service\ServiceCompteUtilisateur;
use App\Backend\Service\ServiceConformite;
use App\Backend\Service\ServiceCursus;
use App\Backend\Service\ServiceDocumentAdministratif;
use App\Backend\Service\ServiceDocumentGenerator;
use App\Backend\Service\ServiceEmail;
use App\Backend\Service\ServiceFichier;
use App\Backend\Service\IdentifiantGenerator;
use App\Backend\Service\ServiceInscription;
use App\Backend\Service\ServiceLogger;
use App\Backend\Service\ServiceMessagerie;
use App\Backend\Service\ServiceNotation;
use App\Backend\Service\ServiceNotification;
use App\Backend\Service\ServiceNotificationConfiguration;
use App\Backend\Service\ServiceParametrage;
use App\Backend\Service\ServicePenalite;
use App\Backend\Service\ServicePermissions;
use App\Backend\Service\ServicePersonnelAcademique;
use App\Backend\Service\ServiceProcesVerbal;
use App\Backend\Service\ServiceProfilEtudiant;
use App\Backend\Service\ServiceQueue;
use App\Backend\Service\ServiceRapport;
use App\Backend\Service\ServiceReclamation;
use App\Backend\Service\ServiceReferentiel;
use App\Backend\Service\ServiceReportingAdmin;
use App\Backend\Service\ServiceStage;
use App\Backend\Service\ServiceSupervisionAdmin;
use App\Backend\Service\ServiceTransitionRole;

// --- Importation de tous les Modèles ---
use App\Backend\Model\{
    Acquerir, Action, Affecter, AnneeAcademique, Approuver, Attribuer, CompteRendu,
    ConformiteRapportDetails, Conversation, CritereConformiteRef, DecisionPassageRef,
    DecisionValidationPvRef, DecisionVoteRef, Delegation, DocumentGenere, Ecue, Enregistrer,
    Enseignant, Entreprise, Etudiant, Evaluer, FaireStage, Fonction, Grade, GroupeUtilisateur,
    HistoriqueMotDePasse, Inscrire, LectureMessage, MatriceNotificationRegles, MessageChat,
    NiveauAccesDonne, NiveauEtude, Notification, Occuper, ParametreSysteme, ParticipantConversation,
    Penalite, PersonnelAdministratif, Pister, PvSessionRapport, RapportEtudiant, RapportModele,
    Rattacher, Recevoir, Reclamation, Rendre, SectionRapport, Sequences, SessionRapport,
    Sessions, SessionValidation, Specialite, StatutConformiteRef, StatutJury, StatutPaiementRef,
    StatutPenaliteRef, StatutPvRef, StatutRapportRef, StatutReclamationRef, Traitement,
    TypeDocumentRef, TypeUtilisateur, Ue, Utilisateur, ValidationPv, VoteCommission
};

// --- Importation de tous les Contrôleurs ---
use App\Backend\Controller\{
    HomeController, AuthentificationController, AssetController, DashboardController
};
use App\Backend\Controller\Admin\{
    AnneeAcademiqueController, AdminDashboardController, ConfigSystemeController, GestionAcadController,
    HabilitationController, ReferentialController, ReportingController, SupervisionController,
//    UtilisateurController, // Remplacé par HabilitationController qui gère les utilisateurs
    NotificationConfigurationController, TransitionRoleController, FichierController,
    LoggerController, QueueController
};
use App\Backend\Controller\Commission\{
    CommissionDashboardController, CommunicationCommissionController, CorrectionCommissionController,
    PvController, ValidationRapportController
};
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\Etudiant\{
    DocumentEtudiantController, EtudiantDashboardController, ProfilEtudiantController, RapportController,
    ReclamationEtudiantController, RessourcesEtudiantController
};
use App\Backend\Controller\PersonnelAdministratif\{
    CommunicationInterneController, ConformiteController, PersonnelDashboardController, ScolariteController,
    DocumentAdministratifController
};

class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        $this->registerInfrastructure();
        $this->registerModels();
        $this->registerServices();
        $this->registerControllers();
    }

    /**
     * Enregistre l'infrastructure de base (BDD, Session, etc.).
     */
    private function registerInfrastructure(): void
    {
        $this->set('PDO', fn () => Database::getInstance()->getConnection());
        $this->set(DatabaseSessionHandler::class, fn () => new DatabaseSessionHandler());
        $this->set(FormValidator::class, fn () => new FormValidator());
    }

    /**
     * Enregistre tous les modèles de données.
     * Chaque modèle dépend uniquement de la connexion PDO.
     */
    private function registerModels(): void
    {
        $models = [
            Acquerir::class, Action::class, Affecter::class, AnneeAcademique::class, Approuver::class,
            Attribuer::class, CompteRendu::class, ConformiteRapportDetails::class, Conversation::class,
            CritereConformiteRef::class, DecisionPassageRef::class, DecisionValidationPvRef::class,
            DecisionVoteRef::class, Delegation::class, DocumentGenere::class, Ecue::class,
            Enregistrer::class, Enseignant::class, Entreprise::class, Etudiant::class, Evaluer::class,
            FaireStage::class, Fonction::class, Grade::class, GroupeUtilisateur::class,
            HistoriqueMotDePasse::class, Inscrire::class, LectureMessage::class, MatriceNotificationRegles::class,
            MessageChat::class, NiveauAccesDonne::class, NiveauEtude::class, Notification::class,
            Occuper::class, ParametreSysteme::class, ParticipantConversation::class, Penalite::class,
            PersonnelAdministratif::class, Pister::class, PvSessionRapport::class, RapportEtudiant::class,
            RapportModele::class, Rattacher::class, Recevoir::class, Reclamation::class, Rendre::class,
            SectionRapport::class, Sequences::class, SessionRapport::class, Sessions::class,
            SessionValidation::class, Specialite::class, StatutConformiteRef::class, StatutJury::class,
            StatutPaiementRef::class, StatutPenaliteRef::class, StatutPvRef::class, StatutRapportRef::class,
            StatutReclamationRef::class, Traitement::class, TypeDocumentRef::class, TypeUtilisateur::class,
            Ue::class, Utilisateur::class, ValidationPv::class, VoteCommission::class
        ];

        foreach ($models as $model) {
            $this->set($model, fn ($c) => new $model($c->get('PDO')));
        }
    }

    /**
     * Enregistre tous les services applicatifs et leurs dépendances.
     * L'ordre est important pour résoudre les dépendances.
     */
    private function registerServices(): void
    {
        // --- Services Fondamentaux (peu ou pas de dépendances de services) ---
        $this->set(ServiceLogger::class, fn () => new ServiceLogger());
        $this->alias(LoggerServiceInterface::class, ServiceLogger::class);

        $this->set(IdentifiantGenerator::class, fn ($c) => new IdentifiantGenerator($c->get('PDO'), $c->get(AnneeAcademique::class)));
        $this->alias(IdentifiantGeneratorInterface::class, IdentifiantGenerator::class);

        $this->set(ServiceAudit::class, fn ($c) => new ServiceAudit($c->get('PDO'), $c->get(Enregistrer::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(AuditServiceInterface::class, ServiceAudit::class);

        $this->set(ServiceQueue::class, fn ($c) => new ServiceQueue($c->get('PDO'), $c->get(QueueJobs::class), $c->get(AuditServiceInterface::class)));
        $this->alias(QueueServiceInterface::class, ServiceQueue::class);

        $this->set(ServiceEmail::class, fn ($c) => new ServiceEmail($c->get(Notification::class), $c->get(QueueJobs::class)));
        $this->alias(EmailServiceInterface::class, ServiceEmail::class);

        $this->set(ServiceSupervisionAdmin::class, fn ($c) => new ServiceSupervisionAdmin($c->get('PDO'), $c->get(LoggerServiceInterface::class)));
        $this->alias(SupervisionAdminServiceInterface::class, ServiceSupervisionAdmin::class);

        $this->set(ServiceTransitionRole::class, fn ($c) => new ServiceTransitionRole($c->get('PDO'), $c->get(Delegation::class)));
        $this->alias(TransitionRoleServiceInterface::class, ServiceTransitionRole::class);

        // --- Services Métier (dépendent souvent des services fondamentaux) ---
        $this->set(ServicePermissions::class, fn ($c) => new ServicePermissions($c->get('PDO'), $c->get(Utilisateur::class), $c->get(Rattacher::class), $c->get(TransitionRoleServiceInterface::class)));
        $this->alias(PermissionsServiceInterface::class, ServicePermissions::class);

        $this->set(AdministrationRBACService::class, fn ($c) => new AdministrationRBACService($c->get('PDO'), $c->get(GroupeUtilisateur::class), $c->get(Traitement::class), $c->get(Rattacher::class), $c->get(PermissionsServiceInterface::class), $c->get(AuditServiceInterface::class)));
        $this->alias(AdministrationRBACServiceInterface::class, AdministrationRBACService::class);

        $this->set(ServiceAuthentication::class, fn ($c) => new ServiceAuthentication($c->get('PDO'), $c->get(Utilisateur::class), $c->get(Sessions::class), $c->get(EmailServiceInterface::class), $c->get(AuditServiceInterface::class)));
        $this->alias(AuthenticationServiceInterface::class, ServiceAuthentication::class);

        $this->set(ServiceNotification::class, fn ($c) => new ServiceNotification($c->get('PDO'), $c->get(Notification::class), $c->get(Recevoir::class), $c->get(Utilisateur::class), $c->get(GroupeUtilisateur::class), $c->get(EmailServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(NotificationServiceInterface::class, ServiceNotification::class);

        $this->set(ServiceFichier::class, fn ($c) => new ServiceFichier($c->get(AuditServiceInterface::class)));
        $this->alias(FichierServiceInterface::class, ServiceFichier::class);

        $this->set(ServiceAnneeAcademique::class, fn ($c) => new ServiceAnneeAcademique($c->get('PDO'), $c->get(AnneeAcademique::class), $c->get(Inscrire::class), $c->get(RapportEtudiant::class), $c->get(AuditServiceInterface::class)));
        $this->alias(AnneeAcademiqueServiceInterface::class, ServiceAnneeAcademique::class);

        $this->set(ServiceCommission::class, fn ($c) => new ServiceCommission($c->get('PDO'), $c->get(SessionValidation::class), $c->get(SessionRapport::class), $c->get(RapportEtudiant::class), $c->get(Affecter::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(CommissionServiceInterface::class, ServiceCommission::class);

        $this->set(ServiceCompteUtilisateur::class, fn ($c) => new ServiceCompteUtilisateur($c->get('PDO'), $c->get(Utilisateur::class), $c->get(HistoriqueMotDePasse::class), $c->get(EmailServiceInterface::class), $c->get(AuditServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(CompteUtilisateurServiceInterface::class, ServiceCompteUtilisateur::class);

        $this->set(ServiceConformite::class, fn ($c) => new ServiceConformite($c->get('PDO'), $c->get(RapportEtudiant::class), $c->get(ConformiteRapportDetails::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class)));
        $this->alias(ConformiteServiceInterface::class, ServiceConformite::class);

        $this->set(ServiceCursus::class, fn ($c) => new ServiceCursus($c->get('PDO'), $c->get(Ue::class), $c->get(Ecue::class), $c->get(AuditServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(CursusServiceInterface::class, ServiceCursus::class);

        $this->set(ServiceDocumentGenerator::class, fn ($c) => new ServiceDocumentGenerator($c->get(RapportModele::class)));
        $this->alias(DocumentGeneratorServiceInterface::class, ServiceDocumentGenerator::class);

        $this->set(ServiceDocumentAdministratif::class, fn ($c) => new ServiceDocumentAdministratif($c->get('PDO'), $c->get(Etudiant::class), $c->get(Inscrire::class), $c->get(Evaluer::class), $c->get(DocumentGenere::class), $c->get(DocumentGeneratorServiceInterface::class), $c->get(AuditServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(DocumentAdministratifServiceInterface::class, ServiceDocumentAdministratif::class);

        $this->set(ServiceInscription::class, fn ($c) => new ServiceInscription($c->get('PDO'), $c->get(Inscrire::class), $c->get(Etudiant::class), $c->get(NiveauEtude::class), $c->get(Penalite::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class)));
        $this->alias(InscriptionServiceInterface::class, ServiceInscription::class);

        $this->set(ServiceMessagerie::class, fn ($c) => new ServiceMessagerie($c->get('PDO'), $c->get(Conversation::class), $c->get(MessageChat::class), $c->get(ParticipantConversation::class), $c->get(LectureMessage::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(MessagerieServiceInterface::class, ServiceMessagerie::class);

        $this->set(ServiceNotation::class, fn ($c) => new ServiceNotation($c->get('PDO'), $c->get(Evaluer::class), $c->get(Etudiant::class), $c->get(Ecue::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class)));
        $this->alias(NotationServiceInterface::class, ServiceNotation::class);

        $this->set(ServiceNotificationConfiguration::class, fn ($c) => new ServiceNotificationConfiguration($c->get('PDO'), $c->get(MatriceNotificationRegles::class), $c->get(Utilisateur::class), $c->get(AuditServiceInterface::class)));
        $this->alias(NotificationConfigurationServiceInterface::class, ServiceNotificationConfiguration::class);

        $this->set(ServiceParametrage::class, fn ($c) => new ServiceParametrage($c->get('PDO'), $c->get(ParametreSysteme::class), $c->get(AuditServiceInterface::class)));
        $this->alias(ParametrageServiceInterface::class, ServiceParametrage::class);

        $this->set(ServicePenalite::class, fn ($c) => new ServicePenalite($c->get('PDO'), $c->get(Penalite::class), $c->get(RapportEtudiant::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(PenaliteServiceInterface::class, ServicePenalite::class);

        $this->set(ServicePersonnelAcademique::class, fn ($c) => new ServicePersonnelAcademique($c->get(Enseignant::class), $c->get(PersonnelAdministratif::class), $c->get(Attribuer::class), $c->get(Occuper::class), $c->get(Acquerir::class)));
        $this->alias(PersonnelAcademiqueServiceInterface::class, ServicePersonnelAcademique::class);

        $this->set(ServiceProcesVerbal::class, fn ($c) => new ServiceProcesVerbal($c->get('PDO'), $c->get(CompteRendu::class), $c->get(ValidationPv::class), $c->get(Affecter::class), $c->get(AuditServiceInterface::class), $c->get(DocumentGeneratorServiceInterface::class), $c->get(NotificationServiceInterface::class)));
        $this->alias(ProcesVerbalServiceInterface::class, ServiceProcesVerbal::class);

        $this->set(ServiceProfilEtudiant::class, fn ($c) => new ServiceProfilEtudiant($c->get('PDO'), $c->get(Etudiant::class), $c->get(FichierServiceInterface::class), $c->get(AuditServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class), $c->get(InscriptionServiceInterface::class), $c->get(NotationServiceInterface::class), $c->get(StageServiceInterface::class)));
        $this->alias(ProfilEtudiantServiceInterface::class, ServiceProfilEtudiant::class);

        $this->set(ServiceRapport::class, fn ($c) => new ServiceRapport($c->get('PDO'), $c->get(RapportEtudiant::class), $c->get(SectionRapport::class), $c->get(Rendre::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(RapportServiceInterface::class, ServiceRapport::class);

        $this->set(ServiceReclamation::class, fn ($c) => new ServiceReclamation($c->get('PDO'), $c->get(Reclamation::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(ReclamationServiceInterface::class, ServiceReclamation::class);

        $this->set(ServiceReferentiel::class, fn ($c) => new ServiceReferentiel($c->get('PDO'), $c->get(AuditServiceInterface::class)));
        $this->alias(ReferentielServiceInterface::class, ServiceReferentiel::class);

        $this->set(ServiceReportingAdmin::class, fn ($c) => new ServiceReportingAdmin($c->get('PDO'), $c->get(DocumentGeneratorServiceInterface::class)));
        $this->alias(ReportingServiceInterface::class, ServiceReportingAdmin::class);

        $this->set(ServiceStage::class, fn ($c) => new ServiceStage($c->get('PDO'), $c->get(FaireStage::class), $c->get(AuditServiceInterface::class), $c->get(NotificationServiceInterface::class), $c->get(IdentifiantGeneratorInterface::class)));
        $this->alias(StageServiceInterface::class, ServiceStage::class);
    }

    /**
     * Enregistre tous les contrôleurs en résolvant automatiquement leurs dépendances.
     */
    private function registerControllers(): void
    {
        $controllers = [
            HomeController::class, AuthentificationController::class, AssetController::class,
            AnneeAcademiqueController::class, AdminDashboardController::class, ConfigSystemeController::class,
            GestionAcadController::class, HabilitationController::class, ReferentialController::class,
            ReportingController::class, SupervisionController::class, /*UtilisateurController::class,*/
            NotificationConfigurationController::class, TransitionRoleController::class, FichierController::class,
            LoggerController::class, QueueController::class, CommissionDashboardController::class,
            CommunicationCommissionController::class, CorrectionCommissionController::class, PvController::class,
            ValidationRapportController::class, NotificationController::class, DashboardController::class,
            DocumentEtudiantController::class, EtudiantDashboardController::class, ProfilEtudiantController::class,
            RapportController::class, ReclamationEtudiantController::class, //RessourcesEtudiantController::class,
            CommunicationInterneController::class, ConformiteController::class, PersonnelDashboardController::class,
            ScolariteController::class, DocumentAdministratifController::class
        ];

        foreach ($controllers as $controller) {
            $this->set($controller, function ($c) use ($controller) {
                $reflection = new ReflectionClass($controller);
                $constructor = $reflection->getConstructor();
                $dependencies = [];

                if ($constructor) {
                    foreach ($constructor->getParameters() as $param) {
                        $type = $param->getType();
                        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                            // Vérifie si le service existe avant de tenter de le récupérer
                            if (!$c->has($type->getName())) {
                                throw new Exception("Dépendance introuvable pour le contrôleur " . $controller . ": " . $type->getName());
                            }
                            $dependencies[] = $c->get($type->getName());
                        } else {
                            throw new Exception("Paramètre de constructeur non supporté ou non typé : " . $param->getName() . " dans " . $controller);
                        }
                    }
                }
                return new $controller(...$dependencies);
            });
        }
    }

    /**
     * Récupère une instance de service ou de classe.
     * Crée l'instance si elle n'existe pas (lazy loading).
     *
     * @param string $id L'identifiant de la classe ou de l'alias.
     * @return mixed L'instance demandée.
     * @throws Exception Si la définition n'est pas trouvée.
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new Exception("Service ou classe introuvable dans le conteneur : " . $id);
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->definitions[$id]($this);
        }

        return $this->instances[$id];
    }

    /**
     * Vérifie si une définition existe dans le conteneur.
     *
     * @param string $id L'identifiant de la classe ou de l'alias.
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Définit une nouvelle entrée dans le conteneur.
     *
     * @param string $id L'identifiant.
     * @param callable $factory La fonction qui crée l'instance.
     */
    private function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    /**
     * Crée un alias pour une définition concrète.
     * Utile pour lier une interface à son implémentation.
     *
     * @param string $alias L'interface ou l'alias.
     * @param string $concrete La classe concrète.
     */
    private function alias(string $alias, string $concrete): void
    {
        $this->set($alias, fn ($c) => $c->get($concrete));
    }
}