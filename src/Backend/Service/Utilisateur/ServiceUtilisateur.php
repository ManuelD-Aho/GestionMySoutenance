<?php
// src/Backend/Service/Utilisateur/ServiceUtilisateur.php

namespace App\Backend\Service\Utilisateur;

use PDO;
use InvalidArgumentException;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\GenericModel;
use App\Backend\Model\Delegation;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, DoublonException};
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private GenericModel $etudiantModel;
    private GenericModel $enseignantModel;
    private GenericModel $personnelAdminModel;
    private Delegation $delegationModel;
    private GenericModel $rapportModel;
    private GenericModel $voteModel;
    private GenericModel $pvModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;
    private ?ServiceCommunicationInterface $communicationService = null;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        GenericModel $etudiantModel,
        GenericModel $enseignantModel,
        GenericModel $personnelAdminModel,
        Delegation $delegationModel,
        GenericModel $rapportModel,
        GenericModel $voteModel,
        GenericModel $pvModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->etudiantModel = $etudiantModel;
        $this->enseignantModel = $enseignantModel;
        $this->personnelAdminModel = $personnelAdminModel;
        $this->delegationModel = $delegationModel;
        $this->rapportModel = $rapportModel;
        $this->voteModel = $voteModel;
        $this->pvModel = $pvModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    public function setCommunicationService(ServiceCommunicationInterface $communicationService): void
    {
        $this->communicationService = $communicationService;
    }

    // --- CREATE ---
    public function creerEntite(string $typeEntite, array $donneesProfil): string
    {
        $prefixe = match (strtolower($typeEntite)) {
            'etudiant' => 'ETU',
            'enseignant' => 'ENS',
            'personnel' => 'ADM',
            default => throw new InvalidArgumentException("Type d'entité '{$typeEntite}' non reconnu."),
        };

        $model = $this->getModelForType($typeEntite);
        $pkCol = is_array($model->getClePrimaire()) ? $model->getClePrimaire()[0] : $model->getClePrimaire();

        $this->db->beginTransaction();
        try {
            $numeroEntite = $this->systemeService->genererIdentifiantUnique($prefixe);
            $donneesProfil[$pkCol] = $numeroEntite;

            if (!$model->creer($donneesProfil)) {
                throw new OperationImpossibleException("Échec de la création de l'entité {$typeEntite}.");
            }

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CREATE_ENTITE', $numeroEntite, $typeEntite, $donneesProfil);
            return $numeroEntite;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool
    {
        $this->db->beginTransaction();
        try {
            if ($this->utilisateurModel->trouverParIdentifiant($numeroEntite)) throw new DoublonException("Un compte utilisateur existe déjà pour l'entité '{$numeroEntite}'.");
            if ($this->utilisateurModel->loginExiste($donneesCompte['login_utilisateur'])) throw new DoublonException("Ce login est déjà utilisé.");
            if ($this->utilisateurModel->emailExiste($donneesCompte['email_principal'])) throw new DoublonException("Cet email est déjà utilisé.");

            $typeEntitePrefix = explode('-', $numeroEntite)[0];
            $typeUtilisateur = match ($typeEntitePrefix) {
                'ETU' => 'TYPE_ETUD',
                'ENS' => 'TYPE_ENS',
                'ADM' => 'TYPE_PERS_ADMIN',
                // 'SYS' handled by createAdminUser specifically
                default => throw new InvalidArgumentException("Préfixe d'entité non reconnu pour l'activation de compte générique."),
            };

            $userData = [
                'numero_utilisateur' => $numeroEntite,
                'login_utilisateur' => $donneesCompte['login_utilisateur'],
                'email_principal' => $donneesCompte['email_principal'],
                'mot_de_passe' => password_hash($donneesCompte['mot_de_passe'], PASSWORD_BCRYPT),
                'id_niveau_acces_donne' => $donneesCompte['id_niveau_acces_donne'],
                'id_groupe_utilisateur' => $donneesCompte['id_groupe_utilisateur'],
                'id_type_utilisateur' => $typeUtilisateur,
                'date_creation' => date('Y-m-d H:i:s'), // Add explicit creation date
                'statut_compte' => $donneesCompte['statut_compte'] ?? 'en_attente_validation', // Allow override
                'email_valide' => $donneesCompte['email_valide'] ?? 0, // Allow override
            ];

            // Generate token only if email_valide is not explicitly set to 1 or true
            $tokenClair = null;
            if (!($userData['email_valide'] === 1 || $userData['email_valide'] === true)) {
                $tokenClair = bin2hex(random_bytes(32));
                $userData['token_validation_email'] = hash('sha256', $tokenClair);
            } else {
                $userData['token_validation_email'] = null; // No token needed if already valid
            }

            if (!$this->utilisateurModel->creer($userData)) throw new OperationImpossibleException("Échec de la création du compte utilisateur.");

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'ACTIVATION_COMPTE', $numeroEntite, 'Utilisateur');

            if ($envoyerEmailValidation && $this->communicationService && $tokenClair) {
                // Assuming a template for email validation exists
                $this->communicationService->envoyerEmail(
                    $donneesCompte['email_principal'],
                    'VALIDATE_EMAIL', // Replace with actual template ID
                    ['validation_link' => $_ENV['APP_URL'] . "/validate-email/{$tokenClair}"]
                );
            }
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createAdminUser(string $login, string $email, string $password): string
    {
        $this->db->beginTransaction();
        try {
            if ($this->utilisateurModel->loginExiste($login)) {
                throw new DoublonException("Ce login est déjà utilisé.");
            }
            if ($this->utilisateurModel->emailExiste($email)) {
                throw new DoublonException("Cet email est déjà utilisé.");
            }

            $numeroUtilisateur = $this->systemeService->genererIdentifiantUnique('SYS');
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $data = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $login,
                'email_principal' => $email,
                'mot_de_passe' => $hashedPassword,
                'id_niveau_acces_donne' => 'ACCES_TOTAL',
                'id_groupe_utilisateur' => 'GRP_ADMIN_SYS',
                'id_type_utilisateur' => 'TYPE_ADMIN',
                'email_valide' => 1,
                'statut_compte' => 'actif',
                'date_creation' => date('Y-m-d H:i:s')
            ];

            if (!$this->utilisateurModel->creer($data)) {
                throw new OperationImpossibleException("Échec de la création du compte administrateur.");
            }

            $this->db->commit();
            $this->supervisionService->enregistrerAction('SYSTEM', 'CREATE_ADMIN_USER', $numeroUtilisateur, 'Utilisateur', ['login' => $login, 'email' => $email]);
            return $numeroUtilisateur;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    // --- READ ---
    public function listerUtilisateursComplets(array $filtres = []): array
    {
        $sql = "SELECT 
                    u.numero_utilisateur, u.login_utilisateur, u.email_principal, u.statut_compte,
                    g.libelle_groupe_utilisateur,
                    t.libelle_type_utilisateur,
                    COALESCE(e.nom, en.nom, pa.nom) as nom,
                    COALESCE(e.prenom, en.prenom, pa.prenom) as prenom,
                    -- Ajout d'un champ 'details' pour les informations spécifiques au rôle
                    CASE u.id_type_utilisateur
                        WHEN 'TYPE_ETUD' THEN (SELECT libelle_niveau_etude FROM niveau_etude WHERE id_niveau_etude = (SELECT id_niveau_etude FROM inscrire WHERE numero_carte_etudiant = u.numero_utilisateur ORDER BY id_annee_academique DESC LIMIT 1))
                        WHEN 'TYPE_ENS' THEN (SELECT libelle_grade FROM grade WHERE id_grade = (SELECT id_grade FROM acquerir WHERE numero_enseignant = u.numero_utilisateur ORDER BY date_acquisition DESC LIMIT 1))
                        ELSE NULL
                    END as details_role
                FROM utilisateur u
                LEFT JOIN groupe_utilisateur g ON u.id_groupe_utilisateur = g.id_groupe_utilisateur
                LEFT JOIN type_utilisateur t ON u.id_type_utilisateur = t.id_type_utilisateur
                LEFT JOIN etudiant e ON u.numero_utilisateur = e.numero_carte_etudiant
                LEFT JOIN enseignant en ON u.numero_utilisateur = en.numero_enseignant
                LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_personnel_administratif
                ";

        $params = [];
        if (!empty($filtres)) {
            $whereParts = [];
            foreach ($filtres as $key => $value) {
                // Handle search filter separately for LIKE matching
                if ($key === 'search') {
                    $search = '%' . $value . '%';
                    $whereParts[] = "(u.login_utilisateur LIKE :search OR u.email_principal LIKE :search OR e.nom LIKE :search OR e.prenom LIKE :search OR en.nom LIKE :search OR en.prenom LIKE :search OR pa.nom LIKE :search OR pa.prenom LIKE :search)";
                    $params[':search'] = $search;
                } else {
                    $whereParts[] = "u.`{$key}` = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            $sql .= " WHERE " . implode(" AND ", $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lireUtilisateurComplet(string $id): ?array
    {
        $result = $this->listerUtilisateursComplets(['u.numero_utilisateur' => $id]);
        return $result[0] ?? null;
    }

    // --- UPDATE ---
    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesProfil, array $donneesCompte): bool
    {
        $this->db->beginTransaction();
        try {
            // Update the main user account data
            if (!empty($donneesCompte)) {
                // Handle password hashing if 'mot_de_passe' is present
                if (isset($donneesCompte['mot_de_passe']) && !empty($donneesCompte['mot_de_passe'])) {
                    $donneesCompte['mot_de_passe'] = password_hash($donneesCompte['mot_de_passe'], PASSWORD_BCRYPT);
                }
                $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesCompte);
            }

            // Update associated profile data if provided
            if (!empty($donneesProfil)) {
                $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['id_type_utilisateur']);
                if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");
                // Ensure to pass the correct type string to getModelForType
                // id_type_utilisateur is like 'TYPE_ETUD', we need 'etudiant'
                $profileType = strtolower(str_replace('TYPE_', '', $user['id_type_utilisateur']));
                if ($profileType === 'admin') { // Admin users don't have a separate profile table
                    // Their profile data is directly in the utilisateur table.
                    // This case is already handled by $donneesCompte if login/email/etc. are changed
                    // No specific action needed for a separate profile model here.
                } else {
                    $modelProfil = $this->getModelForType($profileType);
                    $modelProfil->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
                }
            }
            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPDATE_UTILISATEUR', $numeroUtilisateur, 'Utilisateur');
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function changerStatutCompte(string $numeroUtilisateur, string $nouveauStatut): bool
    {
        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);
        if ($success) {
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_STATUT_COMPTE', $numeroUtilisateur, 'Utilisateur', ['nouveau_statut' => $nouveauStatut]);
        }
        return $success;
    }

    public function supprimerUtilisateurEtEntite(string $id): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Vérification des dépendances critiques
            if ($this->rapportModel->trouverUnParCritere(['numero_carte_etudiant' => $id])) {
                throw new OperationImpossibleException("Suppression impossible : l'utilisateur est lié à au moins un rapport.");
            }
            if ($this->voteModel->trouverUnParCritere(['numero_enseignant' => $id])) {
                throw new OperationImpossibleException("Suppression impossible : l'utilisateur a émis des votes.");
            }
            if ($this->pvModel->trouverUnParCritere(['id_redacteur' => $id])) {
                throw new OperationImpossibleException("Suppression impossible : l'utilisateur est rédacteur de PV.");
            }

            // 2. Anonymisation des données non critiques (ex: logs)
            // Cette étape est optionnelle mais recommandée pour le RGPD
            $this->db->prepare("UPDATE enregistrer SET numero_utilisateur = 'ANONYMIZED' WHERE numero_utilisateur = :id")->execute([':id' => $id]);

            // 3. Suppression de l'entité métier et du compte utilisateur
            $user = $this->utilisateurModel->trouverParIdentifiant($id);
            if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");

            $profileType = strtolower(str_replace('TYPE_', '', $user['id_type_utilisateur']));
            if ($profileType !== 'admin') { // Admin users don't have a separate profile table
                $modelProfil = $this->getModelForType($profileType);
                $modelProfil->supprimerParIdentifiant($id); // Supprime le profil (étudiant, enseignant...)
            }

            $this->utilisateurModel->supprimerParIdentifiant($id); // Supprime le compte

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'DELETE_USER_HARD', $id, 'Utilisateur');
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function reinitialiserMotDePasseAdmin(string $id): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($id);
        if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");

        // Génération d'un mot de passe aléatoire sécurisé
        $nouveauMotDePasseClair = bin2hex(random_bytes(8)); // 16 caractères
        $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_BCRYPT);

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($id, ['mot_de_passe' => $nouveauMotDePasseHache]);

        if ($success && $this->communicationService) {
            // Need to retrieve user's email again as $user might be outdated or not contain email_principal
            $updatedUser = $this->utilisateurModel->trouverParIdentifiant($id, ['email_principal']);
            if ($updatedUser && !empty($updatedUser['email_principal'])) {
                $this->communicationService->envoyerEmail(
                    $updatedUser['email_principal'],
                    'ADMIN_PASSWORD_RESET', // Assuming this template exists
                    ['login' => $user['login_utilisateur'], 'nouveau_mdp' => $nouveauMotDePasseClair]
                );
            }
            $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'ADMIN_RESET_PASSWORD', $id, 'Utilisateur');
        }
        return $success;
    }

    // --- Gestion des Délégations ---
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string
    {
        $idDelegation = $this->systemeService->genererIdentifiantUnique('DEL');
        $data = [
            'id_delegation' => $idDelegation, 'id_delegant' => $idDelegant, 'id_delegue' => $idDelegue,
            'id_traitement' => $idTraitement, 'date_debut' => $dateDebut, 'date_fin' => $dateFin,
            'statut' => 'Active', 'contexte_id' => $contexteId, 'contexte_type' => $contexteType
        ];
        $this->delegationModel->creer($data);
        $this->supervisionService->enregistrerAction($idDelegant, 'CREATION_DELEGATION', $idDelegation, 'Delegation', ['delegue' => $idDelegue, 'traitement' => $idTraitement]);
        if ($this->communicationService) {
            $this->communicationService->envoyerNotificationInterne($idDelegue, 'NOUVELLE_DELEGATION', "Vous avez reçu une nouvelle délégation de droits.");
        }
        return $idDelegation;
    }

    public function revoquerDelegation(string $idDelegation): bool
    {
        $delegation = $this->delegationModel->trouverParIdentifiant($idDelegation);
        if (!$delegation) throw new ElementNonTrouveException("Délégation non trouvée.");
        $success = $this->delegationModel->mettreAJourParIdentifiant($idDelegation, ['statut' => 'Révoquée']);
        if ($success) {
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'REVOCATION_DELEGATION', $idDelegation, 'Delegation');
        }
        return $success;
    }

    public function listerDelegations(array $filtres = []): array
    {
        return $this->delegationModel->trouverParCritere($filtres, ['*'], 'AND', 'date_debut DESC');
    }

    public function lireDelegation(string $idDelegation): ?array
    {
        return $this->delegationModel->trouverParIdentifiant($idDelegation);
    }

    private function getModelForType(string $type): GenericModel
    {
        return match (strtolower($type)) {
            'etudiant' => $this->etudiantModel,
            'enseignant' => $this->enseignantModel,
            'personnel' => $this->personnelAdminModel,
            default => throw new InvalidArgumentException("Type de profil '{$type}' non géré."),
        };
    }

    // ====================================================================
    // SECTION 4 : Processus Métier
    // ====================================================================

    public function gererTransitionsRoles(string $departingUserId, string $newUserId): array
    {
        $rapport = ['rapports_reassignes' => 0, 'pv_reassignes' => 0];

        $this->db->beginTransaction();
        try {
            // 1. Réassigner les rapports en attente d'évaluation
            $stmtRapports = $this->db->prepare("UPDATE affecter SET numero_enseignant = :new_user WHERE numero_enseignant = :old_user");
            $stmtRapports->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $rapport['rapports_reassignes'] = $stmtRapports->rowCount();

            // 2. Réassigner les PV dont l'utilisateur partant était rédacteur
            $stmtPv = $this->db->prepare("UPDATE compte_rendu SET id_redacteur = :new_user WHERE id_redacteur = :old_user AND id_statut_pv IN ('PV_BROUILLON', 'PV_REJETE')");
            $stmtPv->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $rapport['pv_reassignes'] = $stmtPv->rowCount();

            // 3. Réassigner les délégations reçues par l'utilisateur partant
            $stmtDelegations = $this->db->prepare("UPDATE delegation SET id_delegue = :new_user WHERE id_delegue = :old_user AND statut = 'Active'");
            $stmtDelegations->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $rapport['delegations_recues_reassignees'] = $stmtDelegations->rowCount();

            // 4. Archiver le compte de l'utilisateur partant
            $this->changerStatutCompte($departingUserId, 'archive');

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'TRANSITION_ROLE', $departingUserId, 'Utilisateur', ['nouvel_utilisateur' => $newUserId, 'rapport' => $rapport]);
            return $rapport;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // --- NOUVEAU : IMPORTATION EN MASSE ---
    public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array
    {
        $rapport = ['succes' => 0, 'echecs' => 0, 'erreurs' => []];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de lire le fichier fourni : " . $e->getMessage());
        }

        // La première ligne est l'en-tête, on commence à la deuxième
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            foreach ($mapping as $colonneFichier => $champDb) {
                // PhpSpreadsheet utilise des indices de colonne basés sur 1 (A=1, B=2, etc.)
                // On suppose que le mapping est fait avec les noms des colonnes.
                // Il faut trouver l'index de la colonne à partir de son nom.
                $colIndex = $this->getColumnIndexByName($worksheet, $colonneFichier);
                if ($colIndex) {
                    $colString = Coordinate::stringFromColumnIndex($colIndex);
                    $rowData[$champDb] = $worksheet->getCell($colString . $row)->getValue();
                }
            }

            // Validation simple des données extraites
            if (empty($rowData['nom']) || empty($rowData['prenom'])) {
                $rapport['echecs']++;
                $rapport['erreurs'][] = "Ligne {$row}: Le nom et le prénom sont obligatoires.";
                continue;
            }

            // On utilise une transaction pour chaque étudiant pour garantir l'atomicité
            $this->db->beginTransaction();
            try {
                // On appelle la méthode de création d'entité existante
                $this->creerEntite('etudiant', $rowData);
                $this->db->commit();
                $rapport['succes']++;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $rapport['echecs']++;
                $rapport['erreurs'][] = "Ligne {$row} (Étudiant: {$rowData['prenom']} {$rowData['nom']}): " . $e->getMessage();
            }
        }

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'IMPORT_ETUDIANTS',
            null, null,
            ['succes' => $rapport['succes'], 'echecs' => $rapport['echecs']]
        );

        return $rapport;
    }

    // --- Méthodes privées ---
    /**
     * Méthode utilitaire pour trouver l'index d'une colonne par son nom dans la première ligne.
     */
    private function getColumnIndexByName($worksheet, $columnName): ?int
    {
        $highestColumn = $worksheet->getHighestColumn();
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            if ($worksheet->getCell($col . '1')->getValue() == $columnName) {
                // Convertit la lettre de colonne en index numérique (A=1, B=2...)
                return Coordinate::columnIndexFromString($col);
            }
        }
        return null;
    }
}