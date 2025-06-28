<?php

namespace App\Config;

use PDO;
use Exception; // Assurez-vous que la classe Exception est importée
use App\Backend\Util\FormValidator;
use App\Backend\Util\DatabaseSessionHandler;

// Importation de tous les modèles (comme dans votre code original)
use App\Backend\Model\Acquerir;
use App\Backend\Model\Action;
use App\Backend\Model\Affecter;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Approuver;
use App\Backend\Model\Attribuer;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\ConformiteRapportDetails;
use App\Backend\Model\Conversation;
use App\Backend\Model\CritereConformiteRef;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\DecisionValidationPvRef;
use App\Backend\Model\DecisionVoteRef;
use App\Backend\Model\Delegation;
use App\Backend\Model\DocumentGenere;
use App\Backend\Model\Ecue;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Enseignant;
use App\Backend\Model\Entreprise;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Fonction;
use App\Backend\Model\Grade;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Inscrire;
use App\Backend\Model\LectureMessage;
use App\Backend\Model\MatriceNotificationRegles;
use App\Backend\Model\MessageChat;
use App\Backend\Model\NiveauAccesDonne;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\Notification;
use App\Backend\Model\Occuper;
use App\Backend\Model\ParametreSysteme;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\Penalite;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Pister;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\RapportModele; // Nouveau modèle, s'il existe
use App\Backend\Model\RapportModeleAssignation; // Nouveau modèle, s'il existe
use App\Backend\Model\RapportModeleSection; // Nouveau modèle, s'il existe
use App\Backend\Model\Rattacher;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Reclamation;
use App\Backend\Model\Rendre;
use App\Backend\Model\SectionRapport;
use App\Backend\Model\Sequences;
use App\Backend\Model\SessionRapport;
use App\Backend\Model\Sessions;
use App\Backend\Model\SessionValidation;
use App\Backend\Model\Specialite;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\StatutJury;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\StatutPenaliteRef;
use App\Backend\Model\StatutPvRef;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\StatutReclamationRef;
use App\Backend\Model\Traitement;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\Ue;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\VoteCommission;

// Importation de tous les services (comme dans votre code original)
use App\Backend\Service\AnneeAcademique\ServiceAnneeAcademique;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Commission\ServiceCommission;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Service\Conformite\ServiceConformite;
use App\Backend\Service\DocumentAdministratif\ServiceDocumentAdministratif;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\Fichier\ServiceFichier;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Service\Logger\ServiceLogger;
use App\Backend\Service\Messagerie\ServiceMessagerie;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\NotificationConfiguration\ServiceNotificationConfiguration;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\ProfilEtudiant\ServiceProfilEtudiant;
use App\Backend\Service\Queue\ServiceQueue;
use App\Backend\Service\Rapport\ServiceRapport;
use App\Backend\Service\Reclamation\ServiceReclamation;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin;
use App\Backend\Service\RessourcesEtudiant\ServiceRessourcesEtudiant;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\TransitionRole\ServiceTransitionRole;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface; // L'interface est bien importée

// Importation de tous les contrôleurs (comme dans votre code original)
use App\Backend\Controller\HomeController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\Admin\AnneeAcademiqueController;
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
use App\Backend\Controller\Administration\NotificationConfigurationController;
use App\Backend\Controller\Administration\TransitionRoleController;
use App\Backend\Controller\Administration\FichierController;
use App\Backend\Controller\Administration\LoggerController;
use App\Backend\Controller\Administration\QueueController;
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\DashboardController;
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
use App\Backend\Controller\PersonnelAdministratif\DocumentAdministratifController;


class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        // Enregistrement de l'instance PDO selon votre convention ('PDO' comme clé string)
        $this->definitions['PDO'] = fn () => Database::getInstance()->getConnection();
        $this->definitions[FormValidator::class] = fn () => new FormValidator();
        $this->definitions[DatabaseSessionHandler::class] = fn ($c) => new DatabaseSessionHandler();

        // Enregistrement de tous les modèles
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
            Rattacher::class, Recevoir::class, Reclamation::class, Rendre::class, SectionRapport::class,
            Sequences::class, SessionRapport::class, Sessions::class, SessionValidation::class,
            Specialite::class, StatutConformiteRef::class, StatutJury::class, StatutPaiementRef::class,
            StatutPenaliteRef::class, StatutPvRef::class, StatutRapportRef::class, StatutReclamationRef::class,
            Traitement::class, TypeDocumentRef::class, TypeUtilisateur::class, Ue::class, Utilisateur::class,
            ValidationPv::class, VoteCommission::class
        ];
        foreach ($models as $model) {
            $this->definitions[$model] = fn ($c) => new $model($c->get('PDO'));
        }

        // Enregistrement des services
        // Correction ici: Définition de ServiceSupervisionAdmin avec toutes ses dépendances réelles
        $this->definitions[ServiceSupervisionAdmin::class] = fn ($c) => new ServiceSupervisionAdmin(
            $c->get('PDO'),
            $c->get(Action::class),
            $c->get(Enregistrer::class),
            $c->get(Pister::class),
            $c->get(Utilisateur::class),
            $c->get(RapportEtudiant::class),
            $c->get(CompteRendu::class),
            $c->get(Traitement::class)
        );
        // Lier l'interface ServiceSupervisionAdminInterface à son implémentation
        $this->alias(ServiceSupervisionAdminInterface::class, ServiceSupervisionAdmin::class);

        // Définition des autres services (comme dans votre code original, en utilisant la clé 'PDO')
        $this->definitions[IdentifiantGenerator::class] = fn ($c) => new IdentifiantGenerator($c->get('PDO'), $c->get(Sequences::class), $c->get(AnneeAcademique::class), $c->get(ServiceSupervisionAdmin::class));
        $this->definitions[ServiceConfigurationSysteme::class] = fn ($c) => new ServiceConfigurationSysteme($c->get('PDO'), $c->get(ParametreSysteme::class), $c->get(TypeDocumentRef::class), $c->get(NiveauEtude::class), $c->get(StatutPaiementRef::class), $c->get(DecisionPassageRef::class), $c->get(Ecue::class), $c->get(Grade::class), $c->get(Fonction::class), $c->get(Specialite::class), $c->get(StatutReclamationRef::class), $c->get(StatutConformiteRef::class), $c->get(Ue::class), $c->get(Notification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceEmail::class] = fn ($c) => new ServiceEmail($c->get('PDO'), $c->get(Notification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(ServiceConfigurationSysteme::class));
        $this->definitions[ServiceNotificationConfiguration::class] = fn ($c) => new ServiceNotificationConfiguration($c->get('PDO'), $c->get(MatriceNotificationRegles::class), $c->get(Utilisateur::class), $c->get(Action::class), $c->get(GroupeUtilisateur::class),$c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class) );
        $this->definitions[ServiceNotification::class] = fn ($c) => new ServiceNotification($c->get('PDO'), $c->get(Notification::class), $c->get(Recevoir::class), $c->get(Utilisateur::class), $c->get(GroupeUtilisateur::class), $c->get(ServiceSupervisionAdmin::class), $c->get(ServiceEmail::class), $c->get(ServiceNotificationConfiguration::class));

        // Définition de ServicePermissions (utilisant l'interface de SupervisionAdmin)
        $this->definitions[ServicePermissions::class] = fn ($c) => new ServicePermissions(
            $c->get('PDO'), // Utilisation de la clé string 'PDO'
            $c->get(GroupeUtilisateur::class),
            $c->get(TypeUtilisateur::class),
            $c->get(NiveauAccesDonne::class),
            $c->get(Traitement::class),
            $c->get(Rattacher::class),
            $c->get(Utilisateur::class),
            $c->get(ServiceSupervisionAdminInterface::class) // Utilisation de l'interface pour la dépendance
        );

        $this->definitions[ServiceAuthentication::class] = fn ($c) => new ServiceAuthentication($c->get('PDO'), $c->get(Utilisateur::class), $c->get(HistoriqueMotDePasse::class), $c->get(TypeUtilisateur::class), $c->get(GroupeUtilisateur::class), $c->get(Enseignant::class), $c->get(Etudiant::class), $c->get(PersonnelAdministratif::class), $c->get(Sessions::class), $c->get(ServiceEmail::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class), $c->get(ServicePermissions::class));
        $this->definitions[ServiceAnneeAcademique::class] = fn ($c) => new ServiceAnneeAcademique($c->get('PDO'), $c->get(AnneeAcademique::class), $c->get(Inscrire::class), $c->get(RapportEtudiant::class), $c->get(ParametreSysteme::class), $c->get(ServiceSupervisionAdmin::class));
        $this->definitions[ServiceDocumentGenerator::class] = fn ($c) => new ServiceDocumentGenerator($c->get('PDO'), $c->get(CompteRendu::class), $c->get(RapportEtudiant::class), $c->get(Etudiant::class), $c->get(Inscrire::class), $c->get(Evaluer::class), $c->get(AnneeAcademique::class), $c->get(DocumentGenere::class), $c->get(PvSessionRapport::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class), $c->get(ServiceConfigurationSysteme::class));
        $this->definitions[ServiceFichier::class] = fn ($c) => new ServiceFichier($c->get(ServiceSupervisionAdmin::class), $c->get(ServiceConfigurationSysteme::class));
        $this->definitions[ServiceLogger::class] = fn ($c) => new ServiceLogger($c->get(ServiceSupervisionAdmin::class));
        $this->definitions[ServiceDocumentAdministratif::class] = fn ($c) => new ServiceDocumentAdministratif($c->get('PDO'), $c->get(ServiceDocumentGenerator::class), $c->get(ServiceSupervisionAdmin::class), $c->get(DocumentGenere::class), $c->get(Inscrire::class));
        $this->definitions[ServiceQueue::class] = fn ($c) => new ServiceQueue($c->get('PDO'), $c->get(ServiceSupervisionAdmin::class), $c->get(ServiceEmail::class), $c->get(ServiceDocumentAdministratif::class));
        $this->definitions[ServiceMessagerie::class] = fn ($c) => new ServiceMessagerie($c->get('PDO'), $c->get(Conversation::class), $c->get(MessageChat::class), $c->get(ParticipantConversation::class), $c->get(LectureMessage::class), $c->get(Utilisateur::class), $c->get(ServiceNotification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceReclamation::class] = fn ($c) => new ServiceReclamation($c->get('PDO'), $c->get(Reclamation::class), $c->get(StatutReclamationRef::class), $c->get(Etudiant::class), $c->get(PersonnelAdministratif::class), $c->get(ServiceNotification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceRapport::class] = fn ($c) => new ServiceRapport($c->get('PDO'), $c->get(RapportEtudiant::class), $c->get(StatutRapportRef::class), $c->get(Utilisateur::class), $c->get(SectionRapport::class), $c->get(DocumentGenere::class), $c->get(Approuver::class), $c->get(VoteCommission::class), $c->get(CompteRendu::class), $c->get(ServiceNotification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceConformite::class] = fn ($c) => new ServiceConformite($c->get('PDO'), $c->get(RapportEtudiant::class), $c->get(Approuver::class), $c->get(StatutConformiteRef::class), $c->get(PersonnelAdministratif::class), $c->get(ServiceNotification::class), $c->get(ServiceSupervisionAdmin::class));
        $this->definitions[ServiceGestionAcademique::class] = fn ($c) => new ServiceGestionAcademique($c->get('PDO'), $c->get(Inscrire::class), $c->get(Evaluer::class), $c->get(FaireStage::class), $c->get(Acquerir::class), $c->get(Occuper::class), $c->get(Attribuer::class), $c->get(Etudiant::class), $c->get(NiveauEtude::class), $c->get(AnneeAcademique::class), $c->get(StatutPaiementRef::class), $c->get(DecisionPassageRef::class), $c->get(Enseignant::class), $c->get(Ecue::class), $c->get(Entreprise::class), $c->get(Grade::class), $c->get(Fonction::class), $c->get(Specialite::class), $c->get(Penalite::class), $c->get(StatutPenaliteRef::class), $c->get(PersonnelAdministratif::class), $c->get(ServiceNotification::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceCommission::class] = fn ($c) => new ServiceCommission($c->get('PDO'), $c->get(Affecter::class), $c->get(VoteCommission::class), $c->get(CompteRendu::class), $c->get(PvSessionRapport::class), $c->get(ValidationPv::class), $c->get(RapportEtudiant::class), $c->get(DecisionVoteRef::class), $c->get(DecisionValidationPvRef::class), $c->get(StatutRapportRef::class), $c->get(SessionValidation::class), $c->get(SessionRapport::class), $c->get(Utilisateur::class), $c->get(ServiceNotification::class), $c->get(ServiceDocumentGenerator::class), $c->get(ServiceSupervisionAdmin::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceReportingAdmin::class] = fn ($c) => new ServiceReportingAdmin($c->get('PDO'), $c->get(RapportEtudiant::class), $c->get(CompteRendu::class), $c->get(Utilisateur::class), $c->get(AnneeAcademique::class), $c->get(Enregistrer::class), $c->get(Pister::class), $c->get(ServiceSupervisionAdmin::class));
        $this->definitions[ServiceTransitionRole::class] = fn ($c) => new ServiceTransitionRole($c->get('PDO'), $c->get(Delegation::class), $c->get(Utilisateur::class), $c->get(VoteCommission::class), $c->get(Approuver::class), $c->get(CompteRendu::class), $c->get(ValidationPv::class), $c->get(Reclamation::class), $c->get(ServiceSupervisionAdmin::class), $c->get(ServiceNotification::class), $c->get(ServicePermissions::class), $c->get(IdentifiantGenerator::class));
        $this->definitions[ServiceProfilEtudiant::class] = fn ($c) => new ServiceProfilEtudiant($c->get('PDO'), $c->get(Etudiant::class), $c->get(Utilisateur::class), $c->get(ServiceSupervisionAdmin::class), $c->get(ServiceFichier::class));
        $this->definitions[ServiceRessourcesEtudiant::class] = fn ($c) => new ServiceRessourcesEtudiant($c->get('PDO'), $c->get(ServiceSupervisionAdmin::class), $c->get(CritereConformiteRef::class), $c->get(ParametreSysteme::class));

        // Enregistrement des contrôleurs (pas de changement ici, votre logique est bonne)
        $controllers = [
            HomeController::class, AuthentificationController::class, AssetController::class,
            AnneeAcademiqueController::class, AdminDashboardController::class, ConfigSystemeController::class,
            GestionAcadController::class, HabilitationController::class, ReferentialController::class,
            ReportingController::class, SupervisionController::class, UtilisateurController::class,
            NotificationConfigurationController::class, TransitionRoleController::class, FichierController::class,
            LoggerController::class, QueueController::class, CommissionDashboardController::class,
            CommunicationCommissionController::class, CorrectionCommissionController::class, PvController::class,
            ValidationRapportController::class, NotificationController::class, DashboardController::class,
            DocumentEtudiantController::class, EtudiantDashboardController::class, ProfilEtudiantController::class,
            RapportController::class, ReclamationEtudiantController::class, RessourcesEtudiantController::class,
            CommunicationInterneController::class, ConformiteController::class, PersonnelDashboardController::class,
            ScolariteController::class, DocumentAdministratifController::class
        ];
        foreach ($controllers as $controller) {
            $this->definitions[$controller] = function ($c) use ($controller) {
                $reflection = new \ReflectionClass($controller);
                $constructor = $reflection->getConstructor();
                $dependencies = [];
                if ($constructor) {
                    foreach ($constructor->getParameters() as $param) {
                        $type = $param->getType();
                        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                            $dependencies[] = $c->get($type->getName());
                        } else {
                            throw new Exception("Unsupported constructor parameter type: " . $type->getName() . " in " . $controller);
                        }
                    }
                }
                return new $controller(...$dependencies);
            };
        }
    }

    public function get(string $id)
    {
        if (!isset($this->definitions[$id])) {
            throw new Exception("Service or class not found: " . $id);
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->definitions[$id]($this);
        }

        return $this->instances[$id];
    }

    // La méthode alias est nécessaire pour lier les interfaces aux implémentations
    public function alias(string $alias, string $concrete): void
    {
        $this->definitions[$alias] = function ($c) use ($concrete) {
            return $c->get($concrete);
        };
    }
}
