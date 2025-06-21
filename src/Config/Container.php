<?php
namespace App\Config;

use PDO;
use Exception;
use App\Backend\Util\FormValidator; // Utilitaire pour la validation des formulaires
use App\Backend\Util\DatabaseSessionHandler; // Nouveau: pour la gestion de session en DB

// Exceptions personnalisées
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\EmailNonValideException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\ModeleNonTrouveException; // Assurez-vous que cette exception existe si vous l'utilisez
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\PermissionException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\UtilisateurNonTrouveException; // Assurez-vous que cette exception existe si vous l'utilisez
use App\Backend\Exception\ValidationException;


// Modèles
use App\Backend\Model\Acquerir;
use App\Backend\Model\Action;
use App\Backend\Model\Affecter;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Approuver;
use App\Backend\Model\Attribuer;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Conversation;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\DecisionValidationPvRef;
use App\Backend\Model\DecisionVoteRef;
use App\Backend\Model\DocumentGenere; // Nouveau
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
use App\Backend\Model\MessageChat;
use App\Backend\Model\NiveauAccesDonne;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\Notification;
use App\Backend\Model\Occuper;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\Penalite; // Nouveau
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Pister;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Rattacher;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Reclamation;
use App\Backend\Model\Rendre;
use App\Backend\Model\SectionRapport; // Nouveau
use App\Backend\Model\Sequences; // Nouveau
use App\Backend\Model\SessionRapport; // Nouveau
use App\Backend\Model\Sessions; // Nouveau
use App\Backend\Model\SessionValidation; // Nouveau
use App\Backend\Model\Specialite;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\StatutJury;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\StatutPenaliteRef; // Nouveau
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


// Services
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Commission\ServiceCommission;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Service\Conformite\ServiceConformite;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator; // Nouveau service
use App\Backend\Service\Messagerie\ServiceMessagerie;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\Rapport\ServiceRapport;
use App\Backend\Service\Reclamation\ServiceReclamation; // Nouveau service
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;


class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        // Définition de la connexion à la base de données
        $this->definitions['PDO'] = function () {
            $host = $_ENV['DB_HOST'];
            $db = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASSWORD'];
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo;
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        };

        // Définition du gestionnaire de session personnalisé
        $this->definitions[DatabaseSessionHandler::class] = function ($container) {
            return new DatabaseSessionHandler($container->get('PDO'));
        };

        // Initialiser le gestionnaire de session
        // Ceci doit être appelé une seule fois au démarrage de l'application
        // Idéalement dans votre Public/index.php ou un fichier de bootstrap
        // session_set_save_handler($this->get(DatabaseSessionHandler::class), true);
        // session_start();


        // Utilitaires
        $this->definitions[FormValidator::class] = function () {
            return new FormValidator();
        };

        // Modèles - Tous les modèles reçoivent la connexion PDO
        // Modèles existants
        $this->definitions[Acquerir::class] = function ($container) { return new Acquerir($container->get('PDO')); };
        $this->definitions[Action::class] = function ($container) { return new Action($container->get('PDO')); };
        $this->definitions[Affecter::class] = function ($container) { return new Affecter($container->get('PDO')); };
        $this->definitions[AnneeAcademique::class] = function ($container) { return new AnneeAcademique($container->get('PDO')); };
        $this->definitions[Approuver::class] = function ($container) { return new Approuver($container->get('PDO')); };
        $this->definitions[Attribuer::class] = function ($container) { return new Attribuer($container->get('PDO')); };
        $this->definitions[CompteRendu::class] = function ($container) { return new CompteRendu($container->get('PDO')); };
        $this->definitions[Conversation::class] = function ($container) { return new Conversation($container->get('PDO')); };
        $this->definitions[DecisionPassageRef::class] = function ($container) { return new DecisionPassageRef($container->get('PDO')); };
        $this->definitions[DecisionValidationPvRef::class] = function ($container) { return new DecisionValidationPvRef($container->get('PDO')); };
        $this->definitions[DecisionVoteRef::class] = function ($container) { return new DecisionVoteRef($container->get('PDO')); };
        $this->definitions[Ecue::class] = function ($container) { return new Ecue($container->get('PDO')); };
        $this->definitions[Enregistrer::class] = function ($container) { return new Enregistrer($container->get('PDO')); };
        $this->definitions[Enseignant::class] = function ($container) { return new Enseignant($container->get('PDO')); };
        $this->definitions[Entreprise::class] = function ($container) { return new Entreprise($container->get('PDO')); };
        $this->definitions[Etudiant::class] = function ($container) { return new Etudiant($container->get('PDO')); };
        $this->definitions[Evaluer::class] = function ($container) { return new Evaluer($container->get('PDO')); };
        $this->definitions[FaireStage::class] = function ($container) { return new FaireStage($container->get('PDO')); };
        $this->definitions[Fonction::class] = function ($container) { return new Fonction($container->get('PDO')); };
        $this->definitions[Grade::class] = function ($container) { return new Grade($container->get('PDO')); };
        $this->definitions[GroupeUtilisateur::class] = function ($container) { return new GroupeUtilisateur($container->get('PDO')); };
        $this->definitions[HistoriqueMotDePasse::class] = function ($container) { return new HistoriqueMotDePasse($container->get('PDO')); };
        $this->definitions[Inscrire::class] = function ($container) { return new Inscrire($container->get('PDO')); };
        $this->definitions[LectureMessage::class] = function ($container) { return new LectureMessage($container->get('PDO')); };
        $this->definitions[MessageChat::class] = function ($container) { return new MessageChat($container->get('PDO')); };
        $this->definitions[NiveauAccesDonne::class] = function ($container) { return new NiveauAccesDonne($container->get('PDO')); };
        $this->definitions[NiveauEtude::class] = function ($container) { return new NiveauEtude($container->get('PDO')); };
        $this->definitions[Notification::class] = function ($container) { return new Notification($container->get('PDO')); };
        $this->definitions[Occuper::class] = function ($container) { return new Occuper($container->get('PDO')); };
        $this->definitions[ParticipantConversation::class] = function ($container) { return new ParticipantConversation($container->get('PDO')); };
        $this->definitions[PersonnelAdministratif::class] = function ($container) { return new PersonnelAdministratif($container->get('PDO')); };
        $this->definitions[Pister::class] = function ($container) { return new Pister($container->get('PDO')); };
        $this->definitions[PvSessionRapport::class] = function ($container) { return new PvSessionRapport($container->get('PDO')); };
        $this->definitions[RapportEtudiant::class] = function ($container) { return new RapportEtudiant($container->get('PDO')); };
        $this->definitions[Rattacher::class] = function ($container) { return new Rattacher($container->get('PDO')); };
        $this->definitions[Recevoir::class] = function ($container) { return new Recevoir($container->get('PDO')); };
        $this->definitions[Reclamation::class] = function ($container) { return new Reclamation($container->get('PDO')); };
        $this->definitions[Rendre::class] = function ($container) { return new Rendre($container->get('PDO')); };
        $this->definitions[Specialite::class] = function ($container) { return new Specialite($container->get('PDO')); };
        $this->definitions[StatutConformiteRef::class] = function ($container) { return new StatutConformiteRef($container->get('PDO')); };
        $this->definitions[StatutJury::class] = function ($container) { return new StatutJury($container->get('PDO')); };
        $this->definitions[StatutPaiementRef::class] = function ($container) { return new StatutPaiementRef($container->get('PDO')); };
        $this->definitions[StatutPvRef::class] = function ($container) { return new StatutPvRef($container->get('PDO')); };
        $this->definitions[StatutRapportRef::class] = function ($container) { return new StatutRapportRef($container->get('PDO')); };
        $this->definitions[StatutReclamationRef::class] = function ($container) { return new StatutReclamationRef($container->get('PDO')); };
        $this->definitions[Traitement::class] = function ($container) { return new Traitement($container->get('PDO')); };
        $this->definitions[TypeDocumentRef::class] = function ($container) { return new TypeDocumentRef($container->get('PDO')); };
        $this->definitions[TypeUtilisateur::class] = function ($container) { return new TypeUtilisateur($container->get('PDO')); };
        $this->definitions[Ue::class] = function ($container) { return new Ue($container->get('PDO')); };
        $this->definitions[Utilisateur::class] = function ($container) { return new Utilisateur($container->get('PDO')); };
        $this->definitions[ValidationPv::class] = function ($container) { return new ValidationPv($container->get('PDO')); };
        $this->definitions[VoteCommission::class] = function ($container) { return new VoteCommission($container->get('PDO')); };

        // Nouveaux modèles
        $this->definitions[DocumentGenere::class] = function ($container) { return new DocumentGenere($container->get('PDO')); };
        $this->definitions[Penalite::class] = function ($container) { return new Penalite($container->get('PDO')); };
        $this->definitions[StatutPenaliteRef::class] = function ($container) { return new StatutPenaliteRef($container->get('PDO')); };
        $this->definitions[SectionRapport::class] = function ($container) { return new SectionRapport($container->get('PDO')); };
        $this->definitions[Sequences::class] = function ($container) { return new Sequences($container->get('PDO')); };
        $this->definitions[SessionRapport::class] = function ($container) { return new SessionRapport($container->get('PDO')); };
        $this->definitions[SessionValidation::class] = function ($container) { return new SessionValidation($container->get('PDO')); };
        $this->definitions[Sessions::class] = function ($container) { return new Sessions($container->get('PDO')); }; // Pour la gestion de session DB

        // Services - Les services reçoivent leurs propres dépendances via le conteneur
        $this->definitions[ServiceSupervisionAdmin::class] = function ($container) {
            return new ServiceSupervisionAdmin($container->get('PDO'));
        };

        $this->definitions[ServiceNotification::class] = function ($container) {
            return new ServiceNotification(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };

        $this->definitions[ServiceEmail::class] = function ($container) {
            return new ServiceEmail(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };

        $this->definitions[IdentifiantGenerator::class] = function ($container) {
            return new IdentifiantGenerator(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };

        $this->definitions[ServiceAuthentification::class] = function ($container) {
            return new ServiceAuthentification(
                $container->get('PDO'),
                $container->get(ServiceEmail::class),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceCommission::class] = function ($container) {
            return new ServiceCommission(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceDocumentGenerator::class), // Dépend de DocumentGenerator
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceConfigurationSysteme::class] = function ($container) {
            return new ServiceConfigurationSysteme(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };

        $this->definitions[ServiceConformite::class] = function ($container) {
            return new ServiceConformite(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };

        $this->definitions[ServiceDocumentGenerator::class] = function ($container) {
            return new ServiceDocumentGenerator(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceGestionAcademique::class] = function ($container) {
            return new ServiceGestionAcademique(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceMessagerie::class] = function ($container) {
            return new ServiceMessagerie(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServicePermissions::class] = function ($container) {
            return new ServicePermissions(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            // Les modèles Utilisateur, GroupeUtilisateur, TypeUtilisateur sont instanciés à l'intérieur
            // du ServicePermissions pour simplifier l'injection ici, ou peuvent être injectés
            // s'ils sont déjà instanciés comme singleton par le conteneur.
            // Pour éviter les boucles, il est préférable que ServicePermissions n'injecte pas ServiceAuthentification
            // car ServiceAuthentification injecte déjà ServicePermissions.
            );
        };

        $this->definitions[ServiceRapport::class] = function ($container) {
            return new ServiceRapport(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceReclamation::class] = function ($container) {
            return new ServiceReclamation(
                $container->get('PDO'),
                $container->get(ServiceNotification::class),
                $container->get(ServiceSupervisionAdmin::class),
                $container->get(IdentifiantGenerator::class)
            );
        };

        $this->definitions[ServiceReportingAdmin::class] = function ($container) {
            return new ServiceReportingAdmin(
                $container->get('PDO'),
                $container->get(ServiceSupervisionAdmin::class)
            );
        };
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
}