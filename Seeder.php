<?php
// Seeder.php

// Définir ROOT_PATH si non déjà défini (par exemple, dans index.php ou un autre fichier d'entrée)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Container;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\ParcoursAcademique\ServiceParcoursAcademiqueInterface;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\OperationImpossibleException;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(ROOT_PATH, '.env.dev');
$dotenv->load();

echo "=================================================\n";
echo " Démarrage du Seeder de \"GestionMySoutenance\" \n";
echo " Peuplement complet de la base de données (Contexte Afrique)\n";
echo "=================================================\n\n";

try {
    $container = new Container();
    $pdo = $container->get('PDO'); // Get the PDO instance for manual transaction control if needed

    /** @var ServiceUtilisateurInterface $serviceUtilisateur */
    $serviceUtilisateur = $container->get(ServiceUtilisateurInterface::class);
    /** @var ServiceParcoursAcademiqueInterface $serviceParcoursAcademique */
    $serviceParcoursAcademique = $container->get(ServiceParcoursAcademiqueInterface::class);
    /** @var ServiceWorkflowSoutenanceInterface $serviceWorkflowSoutenance */
    $serviceWorkflowSoutenance = $container->get(ServiceWorkflowSoutenanceInterface::class);
    /** @var ServiceDocumentInterface $serviceDocument */
    $serviceDocument = $container->get(ServiceDocumentInterface::class);
    /** @var ServiceCommunicationInterface $serviceCommunication */
    $serviceCommunication = $container->get(ServiceCommunicationInterface::class);
    /** @var ServiceSystemeInterface $serviceSysteme */
    $serviceSysteme = $container->get(ServiceSystemeInterface::class);

    // Set communication service in ServiceUtilisateur (it needs it for emails)
    $serviceUtilisateur->setCommunicationService($serviceCommunication);

    // --- 0. Pré-nettoyage des données pour éviter les doublons (pour les tests de seeding répétés) ---
    // C'est une mesure de sécurité si vous lancez le seeder plusieurs fois sans down -v
    // Dans un vrai script de seeding, down -v est suffisant.
    echo "0. Vérification et nettoyage initial...\n";
    try {
        $pdo->exec("DELETE FROM sessions WHERE user_id IS NOT NULL");
        $pdo->exec("DELETE FROM historique_mot_de_passe");
        $pdo->exec("DELETE FROM vote_commission");
        $pdo->exec("DELETE FROM affecter");
        $pdo->exec("DELETE FROM conformite_rapport_details");
        $pdo->exec("DELETE FROM approuver");
        $pdo->exec("DELETE FROM section_rapport");
        $pdo->exec("DELETE FROM pv_session_rapport");
        $pdo->exec("DELETE FROM compte_rendu");
        $pdo->exec("DELETE FROM session_rapport");
        $pdo->exec("DELETE FROM session_validation");
        $pdo->exec("DELETE FROM rapport_etudiant");
        $pdo->exec("DELETE FROM inscrire");
        $pdo->exec("DELETE FROM etudiant");
        $pdo->exec("DELETE FROM enseignant");
        $pdo->exec("DELETE FROM personnel_administratif");
        $pdo->exec("DELETE FROM utilisateur");
        $pdo->exec("UPDATE sequences SET valeur_actuelle = 0"); // Réinitialiser les séquences

        // Insérer ou mettre à jour l'année académique active si elle n'existe pas ou n'est pas active
        $anneeAcademiqueLibelle = '2025-2026';
        $idAnnee = 'ANNEE-2025-2026';
        $annee = $serviceSysteme->lireAnneeAcademique($idAnnee);
        if (!$annee) {
            $serviceSysteme->creerAnneeAcademique($anneeAcademiqueLibelle, '2025-09-01', '2026-08-31', true);
            echo "   -> Année académique {$anneeAcademiqueLibelle} créée et activée.\n";
        } elseif ($annee['est_active'] == 0) {
            $serviceSysteme->setAnneeAcademiqueActive($idAnnee);
            echo "   -> Année académique {$anneeAcademiqueLibelle} activée.\n";
        } else {
            echo "   -> Année académique {$anneeAcademiqueLibelle} déjà active.\n";
        }
    } catch (Exception $e) {
        echo "   -> Avertissement: Problème lors du nettoyage initial. Cela peut indiquer des FKs ou des données existantes: " . $e->getMessage() . "\n";
    }

    echo "\n-------------------------------------------------\n";
    echo "1. Scénario 1 : Le Parcours de l'Étudiant (Sophie Martin)\n";
    echo "-------------------------------------------------\n";

    // 1.1. Intégration et Configuration du Compte
    echo "1.1. Intégration et Configuration du Compte (Sophie Martin)\n";
    $sophieMartinProfile = [
        'nom' => 'Martin',
        'prenom' => 'Sophie',
        'date_naissance' => '2002-05-15',
        'lieu_naissance' => 'Douala',
        'pays_naissance' => 'Cameroun',
        'nationalite' => 'Camerounaise',
        'sexe' => 'Féminin',
        'adresse_postale' => '12 Rue des Flamboyants',
        'ville' => 'Yaoundé',
        'code_postal' => 'BP 123',
        'telephone' => '+237677889900',
        'email_contact_secondaire' => 'sophie.martin.secondaire@example.com',
        'contact_urgence_nom' => 'Jean Martin',
        'contact_urgence_telephone' => '+237699001122',
        'contact_urgence_relation' => 'Père'
    ];
    $sophieMartinId = $serviceUtilisateur->creerEntite('etudiant', $sophieMartinProfile);
    echo "   - Entité Étudiant 'Sophie Martin' créée avec l'ID: {$sophieMartinId}\n";

    $sophieMartinAccount = [
        'login_utilisateur' => 'sophie.martin',
        'email_principal' => 'sophie.martin@example.com',
        'mot_de_passe' => 'Motdepasse@123', // Mdp temporaire
        'id_niveau_acces_donne' => 'ACCES_PERSONNEL',
        'id_groupe_utilisateur' => 'GRP_ETUDIANT',
        'statut_compte' => 'actif', // Simuler l'activation par le RS
        'email_valide' => 1, // Simuler la validation d'email
    ];
    $serviceUtilisateur->activerComptePourEntite($sophieMartinId, $sophieMartinAccount, false);
    echo "   - Compte 'sophie.martin' activé et email validé.\n";

    // Simuler l'inscription (nécessite Niveau d'Étude, Statut Paiement)
    // On doit ajouter ces données de référence si elles ne sont pas déjà dans mysoutenance.sql
    // Pour cet exemple, je suppose qu'elles sont déjà là ou seront insérées manuellement si le seeder plante.
    $idNiveauMaster2 = 'NIV_M2';
    $idStatutPaiementOK = 'PAIE_OK';
    $serviceSysteme->gererReferentiel('create', 'niveau_etude', null, ['id_niveau_etude' => $idNiveauMaster2, 'libelle_niveau_etude' => 'Master 2']);
    $serviceSysteme->gererReferentiel('create', 'statut_paiement_ref', null, ['id_statut_paiement' => $idStatutPaiementOK, 'libelle_statut_paiement' => 'Paiement Effectué']);
    echo "   - Données de référence Niveau_Etude et Statut_Paiement ajoutées/vérifiées.\n";

    $inscriptionId = $serviceParcoursAcademique->creerInscription([
        'numero_carte_etudiant' => $sophieMartinId,
        'id_niveau_etude' => $idNiveauMaster2,
        'id_annee_academique' => $idAnnee,
        'montant_inscription' => 150000.00, // CFA
        'date_inscription' => date('Y-m-d H:i:s', strtotime('-6 months')),
        'id_statut_paiement' => $idStatutPaiementOK,
        'date_paiement' => date('Y-m-d H:i:s', strtotime('-5 months')),
        'numero_recu_paiement' => $serviceSysteme->genererIdentifiantUnique('REC')
    ]);
    echo "   - Inscription de Sophie Martin pour l'année {$anneeAcademiqueLibelle} créée.\n";

    // Simuler l'enregistrement et validation du stage
    $entrepriseId = $serviceSysteme->genererIdentifiantUnique('ENT');
    $serviceSysteme->gererReferentiel('create', 'entreprise', null, [
        'id_entreprise' => $entrepriseId,
        'libelle_entreprise' => 'AfriTech Solutions',
        'secteur_activite' => 'Informatique',
        'adresse_entreprise' => '10 Rue de l\'Innovation, Yaoundé',
        'contact_nom' => 'Mme Diallo',
        'contact_email' => 'contact@afritech.com',
        'contact_telephone' => '+237222334455'
    ]);
    echo "   - Entreprise 'AfriTech Solutions' créée.\n";

    $serviceParcoursAcademique->creerStage([
        'id_entreprise' => $entrepriseId,
        'numero_carte_etudiant' => $sophieMartinId,
        'date_debut_stage' => '2025-01-15',
        'date_fin_stage' => '2025-06-15',
        'sujet_stage' => 'Développement d\'une plateforme de gestion de projets agile',
        'nom_tuteur_entreprise' => 'Mme Diallo'
    ]);
    echo "   - Stage de Sophie Martin créé.\n";
    $serviceParcoursAcademique->validerStage($sophieMartinId, $entrepriseId);
    echo "   - Stage de Sophie Martin validé par la scolarité.\n";


    // 1.2. Rédaction et Soumission du Rapport
    echo "\n1.2. Rédaction et Soumission du Rapport (Sophie Martin)\n";
    $rapportMetadonnees = [
        'libelle_rapport_etudiant' => 'Optimisation des Processus de Soutenance: Étude de Cas d\'une Implémentation Agile',
        'theme' => 'Gestion de Projet et Digitalisation',
        'resume' => '<p>Ce rapport explore les méthodologies agiles appliquées à l\'optimisation des processus de soutenance académiques...</p>',
        'nombre_pages' => 75
    ];
    $rapportSections = [
        'Introduction' => '<p>L\'introduction présente le contexte du stage et les objectifs du rapport.</p>',
        'Contexte de l\'Entreprise' => '<p>AfriTech Solutions est un leader en innovation logicielle en Afrique...</p>',
        'Problématique et Objectifs' => '<p>La gestion traditionnelle des soutenances posait des défis...</p>',
        'Méthodologie Agile Adoptée' => '<p>Nous avons implémenté Scrum et Kanban...</p>',
        'Analyse des Résultats' => '<p>Les bénéfices incluent une réduction des délais de 20%...</p>',
        'Conclusion et Perspectives' => '<p>En conclusion, l\'approche agile est viable pour l\'académique...</p>',
        'Bibliographie' => '<p>Référence 1, Référence 2...</p>'
    ];
    $rapportId = $serviceWorkflowSoutenance->creerOuMettreAJourBrouillon($sophieMartinId, $rapportMetadonnees, $rapportSections);
    echo "   - Brouillon du rapport de Sophie Martin créé/mis à jour avec l'ID: {$rapportId}\n";

    $serviceWorkflowSoutenance->soumettreRapport($rapportId, $sophieMartinId);
    echo "   - Rapport de Sophie Martin soumis. Statut: RAP_SOUMIS.\n";

    // 1.3. Cycle de Correction et de Validation
    echo "\n1.3. Cycle de Correction et de Validation\n";
    // Création de l'Agent de Conformité
    $agentConformiteProfile = [
        'nom' => 'Conformiste',
        'prenom' => 'Cécile',
        'telephone_professionnel' => '+237688776655',
        'email_professionnel' => 'cecile.conformiste.pro@example.com'
    ];
    $agentConformiteId = $serviceUtilisateur->creerEntite('personnel', $agentConformiteProfile);
    $agentConformiteAccount = [
        'login_utilisateur' => 'agent.conformite',
        'email_principal' => 'cecile.conformiste@example.com',
        'mot_de_passe' => 'Personnel@2025',
        'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT',
        'id_groupe_utilisateur' => 'GRP_AGENT_CONFORMITE',
        'statut_compte' => 'actif',
        'email_valide' => 1,
    ];
    $serviceUtilisateur->activerComptePourEntite($agentConformiteId, $agentConformiteAccount, false);
    echo "   - Agent de Conformité 'Cécile Conformiste' créé avec l'ID: {$agentConformiteId}\n";

    // Traitement de la vérification de conformité par l'agent
    $conformiteChecklist = [
        ['id' => 'PAGE_GARDE', 'statut' => 'Conforme', 'commentaire' => ''],
        ['id' => 'PRESENCE_RESUME', 'statut' => 'Conforme', 'commentaire' => ''],
        ['id' => 'PAGINATION', 'statut' => 'Non Conforme', 'commentaire' => 'Pagination commence trop tôt.'],
        ['id' => 'BIBLIO_FORMAT', 'statut' => 'Conforme', 'commentaire' => ''],
        ['id' => 'VALIDITE_STAGE', 'statut' => 'Conforme', 'commentaire' => 'Stage validé par RS.']
    ];
    $serviceWorkflowSoutenance->traiterVerificationConformite($rapportId, $agentConformiteId, false, $conformiteChecklist, 'Des ajustements de mise en page sont nécessaires, notamment la pagination.');
    echo "   - Rapport de Sophie Martin jugé NON Conforme par l'agent. Statut: RAP_NON_CONF.\n";

    // Simuler corrections et re-soumission
    $rapportSections['Introduction'] = '<p>L\'introduction a été révisée pour mieux clarifier le cadre. La pagination a été ajustée.</p>';
    $serviceWorkflowSoutenance->soumettreCorrections($rapportId, $sophieMartinId, $rapportSections, 'Ajustement de la pagination et clarification de l\'introduction.');
    echo "   - Sophie Martin a soumis ses corrections. Statut: RAP_VALID (simulé après corrections).\n";


    echo "\n-------------------------------------------------\n";
    echo "2. Scénario 2 : Le Travail du Personnel Administratif\n";
    echo "-------------------------------------------------\n";

    // Création du Responsable Scolarité (Si pas déjà fait)
    $rsProfileData = [
        'nom' => 'Scolaris',
        'prenom' => 'Marc',
        'telephone_professionnel' => '0987654321',
        'email_professionnel' => 'marc.scolaris.pro@example.com',
        'date_affectation_service' => '2020-01-01',
        'responsabilites_cles' => 'Gestion inscriptions, notes, stages, pénalités.'
    ];
    $rsId = $serviceUtilisateur->creerEntite('personnel', $rsProfileData);
    $rsAccountData = [
        'login_utilisateur' => 'pers.rs',
        'email_principal' => 'marc.scolaris@example.com',
        'mot_de_passe' => 'Personnel@2025',
        'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT',
        'id_groupe_utilisateur' => 'GRP_RS',
        'statut_compte' => 'actif',
        'email_valide' => 1,
    ];
    $serviceUtilisateur->activerComptePourEntite($rsId, $rsAccountData, false);
    echo "2.1. Responsable Scolarité 'Marc Scolaris' créé avec l'ID: {$rsId}\n";

    // Simuler la saisie de notes pour Sophie
    $idUE1 = 'UE_FOND'; $idECUE1 = 'ECUE_MATH';
    $idUE2 = 'UE_APPL'; $idECUE2 = 'ECUE_INFO';

    $serviceSysteme->gererReferentiel('create', 'ue', null, ['id_ue' => $idUE1, 'libelle_ue' => 'Fondamentaux Scientifiques', 'credits_ue' => 10]);
    $serviceSysteme->gererReferentiel('create', 'ecue', null, ['id_ecue' => $idECUE1, 'libelle_ecue' => 'Mathématiques Appliquées', 'id_ue' => $idUE1, 'credits_ecue' => 5]);
    $serviceSysteme->gererReferentiel('create', 'ue', null, ['id_ue' => $idUE2, 'libelle_ue' => 'Applications Numériques', 'credits_ue' => 15]);
    $serviceSysteme->gererReferentiel('create', 'ecue', null, ['id_ecue' => $idECUE2, 'libelle_ecue' => 'Programmation Avancée', 'id_ue' => $idUE2, 'credits_ecue' => 8]);
    echo "   - Données de référence UE/ECUE ajoutées/vérifiées.\n";

    $serviceParcoursAcademique->creerOuMettreAJourNote([
        'numero_carte_etudiant' => $sophieMartinId,
        'id_ecue' => $idECUE1,
        'id_annee_academique' => $idAnnee,
        'note' => 14.5
    ]);
    $serviceParcoursAcademique->creerOuMettreAJourNote([
        'numero_carte_etudiant' => $sophieMartinId,
        'id_ecue' => $idECUE2,
        'id_annee_academique' => $idAnnee,
        'note' => 16.0
    ]);
    echo "   - Notes de Sophie Martin saisies par le RS.\n";

    $moyennes = $serviceParcoursAcademique->calculerMoyennes($sophieMartinId, $idAnnee);
    echo "   - Moyenne générale de Sophie Martin pour {$idAnnee}: {$moyennes['moyenne_generale']}\n";

    // Génération du bulletin de notes officiel (PDF)
    // Pour que le document PDF soit réellement créé, le chemin UPLOADS_PATH_BASE doit être configuré dans .env.dev
    // et exister dans le conteneur (voir Dockerfile permissions).
    // Exemple de chemin à ajouter dans .env.dev : UPLOADS_PATH_BASE=/var/www/html/Public/uploads/
    $bulletinId = $serviceDocument->genererBulletinNotes($sophieMartinId, $idAnnee);
    echo "   - Bulletin de notes officiel de Sophie Martin généré (ID document: {$bulletinId}).\n";

    echo "\n-------------------------------------------------\n";
    echo "3. Scénario 3 : La Délibération de la Commission\n";
    echo "-------------------------------------------------\n";

    // Création du Président de Commission
    $presidentProfile = [
        'nom' => 'Dupont',
        'prenom' => 'Jean',
        'telephone_professionnel' => '+237699887766',
        'email_professionnel' => 'jean.dupont.pro@example.com'
    ];
    $presidentId = $serviceUtilisateur->creerEntite('enseignant', $presidentProfile);
    $presidentAccount = [
        'login_utilisateur' => 'prof.dupont',
        'email_principal' => 'jean.dupont@example.com',
        'mot_de_passe' => 'Enseignant@2025',
        'id_niveau_acces_donne' => 'ACCES_TOTAL', // President often has high access
        'id_groupe_utilisateur' => 'GRP_COMMISSION',
        'statut_compte' => 'actif',
        'email_valide' => 1,
    ];
    $serviceUtilisateur->activerComptePourEntite($presidentId, $presidentAccount, false);
    echo "3.1. Président de Commission 'Jean Dupont' créé avec l'ID: {$presidentId}\n";

    // Création d'un autre membre de la commission
    $memberProfile = [
        'nom' => 'Ndiaye',
        'prenom' => 'Fatou',
        'telephone_professionnel' => '+237678123456',
        'email_professionnel' => 'fatou.ndiaye.pro@example.com'
    ];
    $memberId = $serviceUtilisateur->creerEntite('enseignant', $memberProfile);
    $memberAccount = [
        'login_utilisateur' => 'prof.ndiaye',
        'email_principal' => 'fatou.ndiaye@example.com',
        'mot_de_passe' => 'Enseignant@2025',
        'id_niveau_acces_donne' => 'ACCES_DEPARTEMENT',
        'id_groupe_utilisateur' => 'GRP_COMMISSION',
        'statut_compte' => 'actif',
        'email_valide' => 1,
    ];
    $serviceUtilisateur->activerComptePourEntite($memberId, $memberAccount, false);
    echo "   - Membre de commission 'Fatou Ndiaye' créé avec l'ID: {$memberId}\n";

    // Création d'une session de validation
    $sessionData = [
        'nom_session' => 'Session de Validation - Master 2 Juin 2025',
        'date_debut_session' => '2025-06-28 09:00:00',
        'date_fin_prevue' => '2025-06-28 17:00:00',
        'mode_session' => 'presentiel',
        'nombre_votants_requis' => 3 // President + 2 membres
    ];
    $sessionId = $serviceWorkflowSoutenance->creerSession($presidentId, $sessionData);
    echo "   - Session de validation créée par Jean Dupont avec l'ID: {$sessionId}\n";

    // Composition de la session (ajouter le rapport de Sophie)
    $serviceWorkflowSoutenance->composerSession($sessionId, [$rapportId]);
    echo "   - Rapport de Sophie Martin ajouté à la session.\n";

    // Démarrage de la session (facultatif pour le seeder)
    $serviceWorkflowSoutenance->demarrerSession($sessionId);
    echo "   - Session démarrée.\n";

    // Simulation des votes
    $serviceSysteme->gererReferentiel('create', 'decision_vote_ref', null, ['id_decision_vote' => 'VOTE_APPROUVE', 'libelle_decision_vote' => 'Approuvé']);
    $serviceSysteme->gererReferentiel('create', 'decision_vote_ref', null, ['id_decision_vote' => 'VOTE_REFUSE', 'libelle_decision_vote' => 'Refusé']);
    $serviceSysteme->gererReferentiel('create', 'decision_vote_ref', null, ['id_decision_vote' => 'VOTE_APPROUVE_RESERVE', 'libelle_decision_vote' => 'Approuvé sous réserve']);
    echo "   - Données de référence Decision_Vote ajoutées/vérifiées.\n";


    $serviceWorkflowSoutenance->enregistrerVote($rapportId, $sessionId, $presidentId, 'VOTE_APPROUVE', 'Très bon travail, bien structuré.');
    echo "   - Vote du Président Dupont enregistré (Approuvé).\n";
    $serviceWorkflowSoutenance->enregistrerVote($rapportId, $sessionId, $memberId, 'VOTE_APPROUVE', 'Analyse pertinente, mérite validation.');
    echo "   - Vote du membre Ndiaye enregistré (Approuvé).\n";
    $serviceWorkflowSoutenance->enregistrerVote($rapportId, $sessionId, $agentConformiteId, 'VOTE_APPROUVE', 'Conforme et académiquement solide.');
    echo "   - Vote de l\'Agent de Conformité (simulé comme membre) enregistré (Approuvé).\n";


    // Initiation et approbation du PV
    $pvId = $serviceWorkflowSoutenance->initierRedactionPv($sessionId, $presidentId);
    echo "   - PV de session initié par le Président Dupont avec l'ID: {$pvId}\n";
    $contenuPv = "Procès-verbal de la Session de Validation du Master 2, Juin 2025.\n";
    $contenuPv .= "Rapport de Sophie Martin: Thème 'Optimisation des Processus de Soutenance'. Décision: Approuvé à l'unanimité.\n";
    $serviceWorkflowSoutenance->mettreAJourContenuPv($pvId, $contenuPv);
    echo "   - Contenu du PV mis à jour.\n";
    $serviceWorkflowSoutenance->soumettrePvPourApprobation($pvId);
    echo "   - PV soumis pour approbation.\n";
    $serviceWorkflowSoutenance->approuverPv($pvId, $presidentId); // President approves his own PV for simplicity
    echo "   - PV approuvé par le Président Dupont.\n";

    // Génération du PV au format PDF
    $pvDocumentId = $serviceDocument->genererPvValidation($pvId);
    echo "   - PV de validation généré au format PDF (ID document: {$pvDocumentId}).\n";

    echo "\n-------------------------------------------------\n";
    echo "4. Scénario 4 : Le Contrôle de l'Administrateur Système (aho.sys)\n";
    echo "-------------------------------------------------\n";

    // Configuration de l'interface (ex: menus)
    // Les menus sont déjà dans mysoutenance.sql, mais nous pouvons simuler une modification
    $traitementListUser = 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER';
    $serviceSysteme->gererReferentiel('update', 'traitement', $traitementListUser, [
        'icone_class' => 'fas fa-users-cog',
        'url_associee' => '/admin/utilisateurs' // Correction pour coller aux routes.txt
    ]);
    echo "   - Traitement '{$traitementListUser}' mis à jour (icône, URL).\n";

    // Simuler une réclamation (pour tester la vue admin)
    $reclamationId = $serviceWorkflowSoutenance->creerReclamation(
        $sophieMartinId,
        'Problème de Notes',
        'Erreur sur la moyenne du bulletin',
        'La moyenne de mon bulletin de notes semble incorrecte pour le module de programmation avancée.'
    );
    echo "   - Réclamation créée par Sophie Martin (ID: {$reclamationId}).\n";

    // Admin peut voir les statistiques (appel simple du service)
    $adminStats = $serviceSysteme->getContainer()->get(\App\Backend\Service\Supervision\ServiceSupervisionInterface::class)->genererStatistiquesDashboardAdmin();
    echo "   - Statistiques du tableau de bord admin générées.\n";
    // var_dump($adminStats); // Décommenter pour voir les stats complètes

    echo "\n=================================================\n";
    echo " Seeding terminé avec succès pour tous les scénarios.\n";
    echo " Vérifiez votre base de données et les chemins d'uploads pour les PDFs.\n";
    echo "=================================================\n";

} catch (DoublonException $e) {
    echo "\nErreur de Doublon: " . $e->getMessage() . "\n";
    echo "Il semble que certaines données que le seeder essaie de créer existent déjà.\n";
    echo "Considérez un 'docker-compose down -v' avant de relancer le seeder pour repartir d'une base propre.\n";
} catch (OperationImpossibleException $e) {
    echo "\nErreur d'Opération Impossible: " . $e->getMessage() . "\n";
    echo "Une action métier n'a pas pu être effectuée. Vérifiez les prérequis ou les logs.\n";
} catch (Exception $e) {
    echo "\nErreur fatale inattendue lors de l'exécution du seeder: " . $e->getMessage() . "\n";
    echo "Trace: \n" . $e->getTraceAsString() . "\n"; // Afficher la trace complète
}