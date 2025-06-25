<?php
namespace App\Config;

use PDO;
use Exception;
use App\Backend\Util\FormValidator;
use App\Backend\Util\DatabaseSessionHandler;

// Exceptions personnalisées (s'assurer qu'elles existent dans votre projet)
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\EmailNonValideException;
use App\Backend\Exception\IdentifiantsInvalidesException;
// use App\Backend\Exception\ModeleNonTrouveException; // Commenté si non utilisée
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\PermissionException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
// use App\Backend\Exception\UtilisateurNonTrouveException; // Commenté si non utilisée
use App\Backend\Exception\ValidationException;


// Modèles (tous vos modèles, assurez-vous qu'ils existent)
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
use App\Backend\Model\MessageChat;
use App\Backend\Model\NiveauAccesDonne;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\Notification;
use App\Backend\Model\Occuper;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\Penalite;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Pister;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Model\RapportEtudiant;
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


// Services (assurez-vous qu'ils existent et que leurs interfaces sont correctes)
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Commission\ServiceCommission;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme;
use App\Backend\Service\Conformite\ServiceConformite;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Service\Messagerie\ServiceMessagerie;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\Rapport\ServiceRapport;
use App\Backend\Service\Reclamation\ServiceReclamation;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;

// Contrôleurs (tous vos contrôleurs)
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


class Container
{
    private array $definitions = [];
    private array $instances = [];

    public function __construct()
    {
        // Définition de la connexion à la base de données
        $this->definitions['PDO'] = function () {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_DATABASE') ?: 'mysoutenance';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo;
            } catch (\PDOException $e) {
                // Log l'erreur de connexion à la DB et lance une exception générale
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                throw new Exception("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
            }
        };

        // Définition du gestionnaire de session personnalisé
        $this->definitions[DatabaseSessionHandler::class] = function ($container) {
            return new DatabaseSessionHandler($container->get('PDO'));
        };

        // Utilitaires
        $this->definitions[FormValidator::class] = function () {
            return new FormValidator();
        };

        // --- Modèles ---
        // Tous les modèles reçoivent la connexion PDO
        $this->definitions[Acquerir::class] = fn ($c) => new Acquerir($c->get('PDO'));
        $this->definitions[Action::class] = fn ($c) => new Action($c->get('PDO'));
        $this->definitions[Affecter::class] = fn ($c) => new Affecter($c->get('PDO'));
        $this->definitions[AnneeAcademique::class] = fn ($c) => new AnneeAcademique($c->get('PDO'));
        $this->definitions[Approuver::class] = fn ($c) => new Approuver($c->get('PDO'));
        $this->definitions[Attribuer::class] = fn ($c) => new Attribuer($c->get('PDO'));
        $this->definitions[CompteRendu::class] = fn ($c) => new CompteRendu($c->get('PDO'));
        $this->definitions[Conversation::class] = fn ($c) => new Conversation($c->get('PDO'));
        $this->definitions[DecisionPassageRef::class] = fn ($c) => new DecisionPassageRef($c->get('PDO'));
        $this->definitions[DecisionValidationPvRef::class] = fn ($c) => new DecisionValidationPvRef($c->get('PDO'));
        $this->definitions[DecisionVoteRef::class] = fn ($c) => new DecisionVoteRef($c->get('PDO'));
        $this->definitions[DocumentGenere::class] = fn ($c) => new DocumentGenere($c->get('PDO'));
        $this->definitions[Ecue::class] = fn ($c) => new Ecue($c->get('PDO'));
        $this->definitions[Enregistrer::class] = fn ($c) => new Enregistrer($c->get('PDO'));
        $this->definitions[Enseignant::class] = fn ($c) => new Enseignant($c->get('PDO'));
        $this->definitions[Entreprise::class] = fn ($c) => new Entreprise($c->get('PDO'));
        $this->definitions[Etudiant::class] = fn ($c) => new Etudiant($c->get('PDO'));
        $this->definitions[Evaluer::class] = fn ($c) => new Evaluer($c->get('PDO'));
        $this->definitions[FaireStage::class] = fn ($c) => new FaireStage($c->get('PDO'));
        $this->definitions[Fonction::class] = fn ($c) => new Fonction($c->get('PDO'));
        $this->definitions[Grade::class] = fn ($c) => new Grade($c->get('PDO'));
        $this->definitions[GroupeUtilisateur::class] = fn ($c) => new GroupeUtilisateur($c->get('PDO'));
        $this->definitions[HistoriqueMotDePasse::class] = fn ($c) => new HistoriqueMotDePasse($c->get('PDO'));
        $this->definitions[Inscrire::class] = fn ($c) => new Inscrire($c->get('PDO'));
        $this->definitions[LectureMessage::class] = fn ($c) => new LectureMessage($c->get('PDO'));
        $this->definitions[MessageChat::class] = fn ($c) => new MessageChat($c->get('PDO'));
        $this->definitions[NiveauAccesDonne::class] = fn ($c) => new NiveauAccesDonne($c->get('PDO'));
        $this->definitions[NiveauEtude::class] = fn ($c) => new NiveauEtude($c->get('PDO'));
        $this->definitions[Notification::class] = fn ($c) => new Notification($c->get('PDO'));
        $this->definitions[Occuper::class] = fn ($c) => new Occuper($c->get('PDO'));
        $this->definitions[ParticipantConversation::class] = fn ($c) => new ParticipantConversation($c->get('PDO'));
        $this->definitions[Penalite::class] = fn ($c) => new Penalite($c->get('PDO'));
        $this->definitions[PersonnelAdministratif::class] = fn ($c) => new PersonnelAdministratif($c->get('PDO'));
        $this->definitions[Pister::class] = fn ($c) => new Pister($c->get('PDO'));
        $this->definitions[PvSessionRapport::class] = fn ($c) => new PvSessionRapport($c->get('PDO'));
        $this->definitions[RapportEtudiant::class] = fn ($c) => new RapportEtudiant($c->get('PDO'));
        $this->definitions[Rattacher::class] = fn ($c) => new Rattacher($c->get('PDO'));
        $this->definitions[Recevoir::class] = fn ($c) => new Recevoir($c->get('PDO'));
        $this->definitions[Reclamation::class] = fn ($c) => new Reclamation($c->get('PDO'));
        $this->definitions[Rendre::class] = fn ($c) => new Rendre($c->get('PDO'));
        $this->definitions[SectionRapport::class] = fn ($c) => new SectionRapport($c->get('PDO'));
        $this->definitions[Sequences::class] = fn ($c) => new Sequences($c->get('PDO'));
        $this->definitions[SessionRapport::class] = fn ($c) => new SessionRapport($c->get('PDO'));
        $this->definitions[Sessions::class] = fn ($c) => new Sessions($c->get('PDO'));
        $this->definitions[SessionValidation::class] = fn ($c) => new SessionValidation($c->get('PDO'));
        $this->definitions[Specialite::class] = fn ($c) => new Specialite($c->get('PDO'));
        $this->definitions[StatutConformiteRef::class] = fn ($c) => new StatutConformiteRef($c->get('PDO'));
        $this->definitions[StatutJury::class] = fn ($c) => new StatutJury($c->get('PDO'));
        $this->definitions[StatutPaiementRef::class] = fn ($c) => new StatutPaiementRef($c->get('PDO'));
        $this->definitions[StatutPenaliteRef::class] = fn ($c) => new StatutPenaliteRef($c->get('PDO'));
        $this->definitions[StatutPvRef::class] = fn ($c) => new StatutPvRef($c->get('PDO'));
        $this->definitions[StatutRapportRef::class] = fn ($c) => new StatutRapportRef($c->get('PDO'));
        $this->definitions[StatutReclamationRef::class] = fn ($c) => new StatutReclamationRef($c->get('PDO'));
        $this->definitions[Traitement::class] = fn ($c) => new Traitement($c->get('PDO'));
        $this->definitions[TypeDocumentRef::class] = fn ($c) => new TypeDocumentRef($c->get('PDO'));
        $this->definitions[TypeUtilisateur::class] = fn ($c) => new TypeUtilisateur($c->get('PDO'));
        $this->definitions[Ue::class] = fn ($c) => new Ue($c->get('PDO'));
        $this->definitions[Utilisateur::class] = fn ($c) => new Utilisateur($c->get('PDO'));
        $this->definitions[ValidationPv::class] = fn ($c) => new ValidationPv($c->get('PDO'));
        $this->definitions[VoteCommission::class] = fn ($c) => new VoteCommission($c->get('PDO'));


        // --- Services ---
        // L'ordre est important ici, les dépendances doivent être définies avant d'être injectées
        $this->definitions[ServiceSupervisionAdmin::class] = fn ($c) => new ServiceSupervisionAdmin($c->get('PDO'));

        $this->definitions[ServiceNotification::class] = fn ($c) => new ServiceNotification(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[ServiceEmail::class] = fn ($c) => new ServiceEmail(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[IdentifiantGenerator::class] = fn ($c) => new IdentifiantGenerator(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[ServiceAuthentification::class] = fn ($c) => new ServiceAuthentification(
            $c->get('PDO'),
            $c->get(ServiceEmail::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceCommission::class] = fn ($c) => new ServiceCommission(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceDocumentGenerator::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceConfigurationSysteme::class] = fn ($c) => new ServiceConfigurationSysteme(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceConformite::class] = fn ($c) => new ServiceConformite(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[ServiceDocumentGenerator::class] = fn ($c) => new ServiceDocumentGenerator(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class),
            $c->get(PvSessionRapport::class)
        );

        $this->definitions[ServiceGestionAcademique::class] = fn ($c) => new ServiceGestionAcademique(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceMessagerie::class] = fn ($c) => new ServiceMessagerie(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServicePermissions::class] = fn ($c) => new ServicePermissions(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class)
        // Les modèles Utilisateur, GroupeUtilisateur, TypeUtilisateur sont instanciés à l'intérieur de ServicePermissions
        // pour éviter les boucles de dépendance avec ServiceAuthentification.
        );

        $this->definitions[ServiceRapport::class] = fn ($c) => new ServiceRapport(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceReclamation::class] = fn ($c) => new ServiceReclamation(
            $c->get('PDO'),
            $c->get(ServiceNotification::class),
            $c->get(ServiceSupervisionAdmin::class),
            $c->get(IdentifiantGenerator::class)
        );

        $this->definitions[ServiceReportingAdmin::class] = fn ($c) => new ServiceReportingAdmin(
            $c->get('PDO'),
            $c->get(ServiceSupervisionAdmin::class)
        );

        // --- Contrôleurs ---
        // Chaque contrôleur reçoit ses dépendances nécessaires

        $this->definitions[HomeController::class] = fn ($c) => new HomeController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        $this->definitions[AuthentificationController::class] = fn ($c) => new AuthentificationController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        $this->definitions[AssetController::class] = fn ($c) => new AssetController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSupervisionAdmin::class) // Pour la journalisation des assets
        );

        // Contrôleurs de l'Administration
        $this->definitions[AnneeAcademiqueController::class] = fn ($c) => new AnneeAcademiqueController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[AdminDashboardController::class] = fn ($c) => new AdminDashboardController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceReportingAdmin::class),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[ConfigSystemeController::class] = fn ($c) => new ConfigSystemeController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[GestionAcadController::class] = fn ($c) => new GestionAcadController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceGestionAcademique::class),
            $c->get(ServiceConfigurationSysteme::class) // Pour les listes de référence
        );

        $this->definitions[HabilitationController::class] = fn ($c) => new HabilitationController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        $this->definitions[ReferentialController::class] = fn ($c) => new ReferentialController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        $this->definitions[ReportingController::class] = fn ($c) => new ReportingController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceReportingAdmin::class),
            $c->get(ServiceConfigurationSysteme::class) // Pour les filtres par année académique
        );

        $this->definitions[SupervisionController::class] = fn ($c) => new SupervisionController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceSupervisionAdmin::class)
        );

        $this->definitions[UtilisateurController::class] = fn ($c) => new UtilisateurController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceGestionAcademique::class), // Pour lister grades, fonctions etc.
            $c->get(ServiceConfigurationSysteme::class) // Pour lister années académiques, niveaux d'étude etc.
        );

        // Contrôleurs de la Commission
        $this->definitions[CommissionDashboardController::class] = fn ($c) => new CommissionDashboardController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceCommission::class),
            $c->get(ServiceNotification::class)
        );

        $this->definitions[CommunicationCommissionController::class] = fn ($c) => new CommunicationCommissionController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceMessagerie::class)
        );

        $this->definitions[CorrectionCommissionController::class] = fn ($c) => new CorrectionCommissionController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceCommission::class),
            $c->get(ServiceNotification::class)
        );

        $this->definitions[PvController::class] = fn ($c) => new PvController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceCommission::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceGestionAcademique::class) // Pour lister etudiants, etc.
        );

        $this->definitions[ValidationRapportController::class] = fn ($c) => new ValidationRapportController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceCommission::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceConfigurationSysteme::class) // Pour les décisions de vote
        );

        // Contrôleurs Communs
        $this->definitions[NotificationController::class] = fn ($c) => new NotificationController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceNotification::class)
        );

        $this->definitions[DashboardController::class] = fn ($c) => new DashboardController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        // Contrôleurs des Étudiants
        $this->definitions[DocumentEtudiantController::class] = fn ($c) => new DocumentEtudiantController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceDocumentGenerator::class)
        );

        $this->definitions[EtudiantDashboardController::class] = fn ($c) => new EtudiantDashboardController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceNotification::class),
            $c->get(ServiceReclamation::class),
            $c->get(ServiceGestionAcademique::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[ProfilEtudiantController::class] = fn ($c) => new ProfilEtudiantController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        $this->definitions[RapportController::class] = fn ($c) => new RapportController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceGestionAcademique::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[ReclamationEtudiantController::class] = fn ($c) => new ReclamationEtudiantController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceReclamation::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[RessourcesEtudiantController::class] = fn ($c) => new RessourcesEtudiantController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class)
        );

        // Contrôleurs du Personnel Administratif
        $this->definitions[CommunicationInterneController::class] = fn ($c) => new CommunicationInterneController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceMessagerie::class)
        );

        $this->definitions[ConformiteController::class] = fn ($c) => new ConformiteController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceConformite::class),
            $c->get(ServiceRapport::class),
            $c->get(ServiceConfigurationSysteme::class)
        );

        $this->definitions[PersonnelDashboardController::class] = fn ($c) => new PersonnelDashboardController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceConformite::class),
            $c->get(ServiceGestionAcademique::class),
            $c->get(ServiceReclamation::class),
            $c->get(ServiceNotification::class)
        );

        $this->definitions[ScolariteController::class] = fn ($c) => new ScolariteController(
            $c->get(ServiceAuthentification::class),
            $c->get(ServicePermissions::class),
            $c->get(FormValidator::class),
            $c->get(ServiceGestionAcademique::class),
            $c->get(ServiceReclamation::class),
            $c->get(ServiceDocumentGenerator::class),
            $c->get(ServiceConfigurationSysteme::class)
        );
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