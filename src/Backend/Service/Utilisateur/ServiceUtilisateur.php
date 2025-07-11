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
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, DoublonException};
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Service gérant la logique métier complexe liée aux utilisateurs et aux entités.
 * Implémente le cycle de vie complet : création de profil (entité), activation de compte (utilisateur),
 * mises à jour, délégations, et processus d'import/transition.
 */
class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private GenericModel $etudiantModel;
    private GenericModel $enseignantModel;
    private GenericModel $personnelAdminModel;
    private Delegation $delegationModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;
    private ?ServiceCommunicationInterface $communicationService = null;
    private ?ServiceDocumentInterface $documentService = null;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        GenericModel $etudiantModel,
        GenericModel $enseignantModel,
        GenericModel $personnelAdminModel,
        Delegation $delegationModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->etudiantModel = $etudiantModel;
        $this->enseignantModel = $enseignantModel;
        $this->personnelAdminModel = $personnelAdminModel;
        $this->delegationModel = $delegationModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    public function setCommunicationService(ServiceCommunicationInterface $communicationService): void
    {
        $this->communicationService = $communicationService;
    }

    public function setDocumentService(ServiceDocumentInterface $documentService): void
    {
        $this->documentService = $documentService;
    }

    public function creerEntite(string $typeEntite, array $donneesProfil): string
    {
        $prefixeMap = ['etudiant' => 'ETU', 'enseignant' => 'ENS', 'personnel' => 'ADM'];
        $typeEntiteLower = strtolower($typeEntite);

        if (!isset($prefixeMap[$typeEntiteLower])) {
            throw new InvalidArgumentException("Type d'entité '{$typeEntite}' non reconnu.");
        }

        $model = $this->getModelForType($typeEntiteLower);
        $pkCol = $model->getClePrimaire();

        $numeroEntite = $this->systemeService->genererIdentifiantUnique($prefixeMap[$typeEntiteLower]);
        $donneesProfil[$pkCol] = $numeroEntite;
        $donneesProfil['numero_utilisateur'] = null; // Conformément au flux "entité d'abord"

        if (!$model->creer($donneesProfil)) {
            throw new OperationImpossibleException("Échec de la création de l'entité {$typeEntite}.");
        }

        $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CREATE_ENTITE', $numeroEntite, ucfirst($typeEntite), $donneesProfil);
        return $numeroEntite;
    }

    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool
    {
        $this->db->beginTransaction();
        try {
            if ($this->utilisateurModel->trouverParIdentifiant($numeroEntite)) throw new DoublonException("Un compte utilisateur existe déjà pour l'entité '{$numeroEntite}'.");
            if ($this->utilisateurModel->loginExiste($donneesCompte['login_utilisateur'])) throw new DoublonException("Ce login est déjà utilisé.");
            if ($this->utilisateurModel->emailExiste($donneesCompte['email_principal'])) throw new DoublonException("Cet email est déjà utilisé.");

            $typeEntitePrefix = explode('-', $numeroEntite)[0];
            $modelEntite = $this->getModelForType(strtolower($typeEntitePrefix));
            $entite = $modelEntite->trouverParIdentifiant($numeroEntite);

            if (!$entite) throw new ElementNonTrouveException("L'entité métier '{$numeroEntite}' n'existe pas.");
            if (isset($entite['numero_utilisateur']) && !is_null($entite['numero_utilisateur'])) throw new OperationImpossibleException("Cette entité est déjà liée à un compte.");

            $typeUtilisateur = match ($typeEntitePrefix) { 'ETU' => 'TYPE_ETUD', 'ENS' => 'TYPE_ENS', 'ADM' => 'TYPE_PERS_ADMIN', default => throw new InvalidArgumentException("Préfixe d'entité non géré.") };
            $tokenClair = bin2hex(random_bytes(32));
            $userData = [
                'numero_utilisateur' => $numeroEntite,
                'login_utilisateur' => $donneesCompte['login_utilisateur'],
                'email_principal' => $donneesCompte['email_principal'],
                'mot_de_passe' => password_hash($donneesCompte['mot_de_passe'], PASSWORD_BCRYPT),
                'id_groupe_utilisateur' => $donneesCompte['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $donneesCompte['id_niveau_acces_donne'],
                'id_type_utilisateur' => $typeUtilisateur,
                'statut_compte' => 'en_attente_validation',
                'token_validation_email' => hash('sha256', $tokenClair),
                'date_expiration_token_reset' => date('Y-m-d H:i:s', time() + 86400) // 24h
            ];
            $this->utilisateurModel->creer($userData);

            $modelEntite->mettreAJourParIdentifiant($numeroEntite, ['numero_utilisateur' => $numeroEntite]);

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'ACTIVATION_COMPTE', $numeroEntite, 'Utilisateur');

            if ($envoyerEmailValidation && $this->communicationService) {
                $this->communicationService->envoyerEmail($donneesCompte['email_principal'], 'VALIDATE_EMAIL', ['validation_link' => $_ENV['APP_URL'] . "/validate-email/{$tokenClair}"]);
            }
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createAdminUser(string $login, string $email, string $password): string
    {
        if ($this->utilisateurModel->loginExiste($login)) throw new DoublonException("Ce login est déjà utilisé.");
        if ($this->utilisateurModel->emailExiste($email)) throw new DoublonException("Cet email est déjà utilisé.");

        $numeroUtilisateur = $this->systemeService->genererIdentifiantUnique('SYS');
        $data = [
            'numero_utilisateur' => $numeroUtilisateur, 'login_utilisateur' => $login, 'email_principal' => $email,
            'mot_de_passe' => password_hash($password, PASSWORD_BCRYPT),
            'id_niveau_acces_donne' => 'ACCES_TOTAL', 'id_groupe_utilisateur' => 'GRP_ADMIN_SYS',
            'id_type_utilisateur' => 'TYPE_ADMIN', 'email_valide' => 1, 'statut_compte' => 'actif',
        ];
        if (!$this->utilisateurModel->creer($data)) throw new OperationImpossibleException("Échec de la création du compte administrateur.");

        $this->supervisionService->enregistrerAction('SYSTEM', 'CREATE_ADMIN_USER', $numeroUtilisateur, 'Utilisateur');
        return $numeroUtilisateur;
    }

    public function listerUtilisateursComplets(array $filtres = []): array
    {
        $sql = "SELECT u.*, g.libelle_groupe, t.libelle_type_utilisateur,
                COALESCE(e.nom, en.nom, pa.nom) as nom,
                COALESCE(e.prenom, en.prenom, pa.prenom) as prenom
                FROM utilisateur u
                LEFT JOIN groupe_utilisateur g ON u.id_groupe_utilisateur = g.id_groupe_utilisateur
                LEFT JOIN type_utilisateur t ON u.id_type_utilisateur = t.id_type_utilisateur
                LEFT JOIN etudiant e ON u.numero_utilisateur = e.numero_carte_etudiant
                LEFT JOIN enseignant en ON u.numero_utilisateur = en.numero_enseignant
                LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_personnel_administratif";

        // Construction dynamique de la clause WHERE pour les filtres
        $params = [];
        $whereParts = [];
        if (!empty($filtres)) {
            foreach ($filtres as $key => $value) {
                if (empty($value)) continue;
                if ($key === 'search') {
                    $whereParts[] = "(u.login_utilisateur LIKE :search OR u.email_principal LIKE :search OR e.nom LIKE :search OR e.prenom LIKE :search OR en.nom LIKE :search OR pa.nom LIKE :search)";
                    $params[':search'] = '%' . $value . '%';
                } else {
                    // Pour éviter les ambiguïtés avec les alias de table, spécifier la table pour les filtres sur 'u'
                    if (in_array($key, ['id_groupe_utilisateur', 'id_type_utilisateur', 'statut_compte'])) {
                        $whereParts[] = "u.`{$key}` = :{$key}";
                    } else {
                        $whereParts[] = "`{$key}` = :{$key}";
                    }
                    $params[":{$key}"] = $value;
                }
            }
        }
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(" AND ", $whereParts);
        }
        $sql .= " ORDER BY nom ASC, prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lireUtilisateurComplet(string $id): ?array
    {
        $result = $this->listerUtilisateursComplets(['numero_utilisateur' => $id]);
        return $result[0] ?? null;
    }

    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesProfil, array $donneesCompte): bool
    {
        $this->db->beginTransaction();
        try {
            if (!empty($donneesCompte)) {
                if (isset($donneesCompte['mot_de_passe']) && !empty($donneesCompte['mot_de_passe'])) {
                    $donneesCompte['mot_de_passe'] = password_hash($donneesCompte['mot_de_passe'], PASSWORD_BCRYPT);
                } else {
                    unset($donneesCompte['mot_de_passe']);
                }
                $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesCompte);
            }

            if (!empty($donneesProfil)) {
                $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['id_type_utilisateur']);
                if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé pour la mise à jour du profil.");

                $profileType = strtolower(str_replace('TYPE_', '', $user['id_type_utilisateur']));
                if ($profileType !== 'admin') { // Les admins n'ont pas d'entité métier associée
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

    public function supprimerUtilisateurEtEntite(string $id): bool
    {
        $this->db->beginTransaction();
        try {
            // Vérification des dépendances
            $user = $this->utilisateurModel->trouverParIdentifiant($id);
            if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");

            $typeEntite = strtolower(str_replace('TYPE_', '', $user['id_type_utilisateur']));

            // Vérifications spécifiques avant suppression
            if ($typeEntite === 'etud') {
                if ($this->db->query("SELECT 1 FROM rapport_etudiant WHERE numero_carte_etudiant = '{$id}' LIMIT 1")->fetch()) {
                    throw new OperationImpossibleException("Suppression impossible : l'étudiant est lié à des rapports.");
                }
                if ($this->db->query("SELECT 1 FROM inscrire WHERE numero_carte_etudiant = '{$id}' LIMIT 1")->fetch()) {
                    throw new OperationImpossibleException("Suppression impossible : l'étudiant est lié à des inscriptions.");
                }
                // Ajoutez d'autres vérifications pour étudiant (stages, pénalités, réclamations)
            } elseif ($typeEntite === 'ens') {
                if ($this->db->query("SELECT 1 FROM affecter WHERE numero_enseignant = '{$id}' LIMIT 1")->fetch()) {
                    throw new OperationImpossibleException("Suppression impossible : l'enseignant est lié à des affectations de jury.");
                }
                // Ajoutez d'autres vérifications pour enseignant (fonctions, grades, spécialités)
            } elseif ($typeEntite === 'pers_admin') {
                if ($this->db->query("SELECT 1 FROM approuver WHERE numero_personnel_administratif = '{$id}' LIMIT 1")->fetch()) {
                    throw new OperationImpossibleException("Suppression impossible : le personnel est lié à des approbations de conformité.");
                }
                // Ajoutez d'autres vérifications pour personnel (réclamations traitées)
            }

            // Suppression de l'entité métier si elle existe
            $modelProfil = $this->getModelForType($typeEntite);
            if ($modelProfil->trouverParIdentifiant($id)) {
                $modelProfil->supprimerParIdentifiant($id);
            }
            // Suppression du compte utilisateur
            $this->utilisateurModel->supprimerParIdentifiant($id);

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'DELETE_USER_HARD', $id, 'Utilisateur');
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function changerStatutCompte(string $numeroUtilisateur, string $nouveauStatut): bool
    {
        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);
        if ($success) $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_STATUT_COMPTE', $numeroUtilisateur, 'Utilisateur', ['nouveau_statut' => $nouveauStatut]);
        return $success;
    }

    public function reinitialiserMotDePasseAdmin(string $id): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($id);
        if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");

        $nouveauMotDePasseClair = bin2hex(random_bytes(8)); // Génère un mot de passe aléatoire
        $success = $this->mettreAJourUtilisateur($id, [], ['mot_de_passe' => $nouveauMotDePasseClair]);

        if ($success && $this->communicationService) {
            $this->communicationService->envoyerEmail($user['email_principal'], 'ADMIN_PASSWORD_RESET', ['login' => $user['login_utilisateur'], 'nouveau_mdp' => $nouveauMotDePasseClair]);
            $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'ADMIN_PASSWORD_RESET', $id, 'Utilisateur');
        }
        return $success;
    }

    public function renvoyerEmailValidation(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");
        if ($user['email_valide']) throw new OperationImpossibleException("L'email de cet utilisateur est déjà validé.");

        $tokenClair = bin2hex(random_bytes(32));
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['token_validation_email' => hash('sha256', $tokenClair), 'date_expiration_token_reset' => date('Y-m-d H:i:s', time() + 86400)]);

        if ($this->communicationService) {
            $this->communicationService->envoyerEmail($user['email_principal'], 'VALIDATE_EMAIL', ['validation_link' => $_ENV['APP_URL'] . "/validate-email/{$tokenClair}"]);
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'RESEND_VALIDATION_EMAIL', $numeroUtilisateur, 'Utilisateur');
            return true;
        }
        return false;
    }

    public function telechargerPhotoProfil(string $numeroUtilisateur, array $fileData): string
    {
        if (is_null($this->documentService)) throw new \LogicException("Le service de document n'est pas injecté.");

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $relativePath = $this->documentService->uploadFichierSecurise($fileData, 'profile_pictures', $allowedMimeTypes, $maxSize);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['photo_profil' => $relativePath]);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'UPLOAD_PROFILE_PICTURE', $numeroUtilisateur, 'Utilisateur', ['path' => $relativePath]);

        return $relativePath;
    }

    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string
    {
        $idDelegation = $this->systemeService->genererIdentifiantUnique('DEL');
        $data = ['id_delegation' => $idDelegation, 'id_delegant' => $idDelegant, 'id_delegue' => $idDelegue, 'id_traitement' => $idTraitement, 'date_debut' => $dateDebut, 'date_fin' => $dateFin, 'statut' => 'Active', 'contexte_id' => $contexteId, 'contexte_type' => $contexteType];
        $this->delegationModel->creer($data);
        $this->supervisionService->enregistrerAction($idDelegant, 'CREATION_DELEGATION', $idDelegation, 'Delegation', ['delegue' => $idDelegue, 'traitement' => $idTraitement]);
        return $idDelegation;
    }

    public function revoquerDelegation(string $idDelegation): bool
    {
        if (!$this->delegationModel->trouverParIdentifiant($idDelegation)) throw new ElementNonTrouveException("Délégation non trouvée.");
        $success = $this->delegationModel->mettreAJourParIdentifiant($idDelegation, ['statut' => 'Révoquée']);
        if ($success) $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'REVOCATION_DELEGATION', $idDelegation, 'Delegation');
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

    public function gererTransitionsRoles(string $departingUserId, string $newUserId): array
    {
        $report = [
            'jury_reassignes' => 0, 'pv_reassignes' => 0,
            'delegations_recues_reassignees' => 0, 'delegations_emises_revoquees' => 0,
            'reclamations_reassignees' => 0
        ];
        $this->db->beginTransaction();
        try {
            $sqlJury = "UPDATE affecter SET numero_enseignant = :new_user WHERE numero_enseignant = :old_user AND id_rapport_etudiant IN (SELECT id_rapport_etudiant FROM rapport_etudiant WHERE id_statut_rapport IN ('RAP_CONF', 'RAP_EN_COMMISSION', 'RAP_CORRECT'))";
            $stmtJury = $this->db->prepare($sqlJury);
            $stmtJury->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $report['jury_reassignes'] = $stmtJury->rowCount();

            $sqlPv = "UPDATE compte_rendu SET id_redacteur = :new_user WHERE id_redacteur = :old_user AND id_statut_pv IN ('PV_BROUILLON', 'PV_REJETE')";
            $stmtPv = $this->db->prepare($sqlPv);
            $stmtPv->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $report['pv_reassignes'] = $stmtPv->rowCount();

            $sqlDelegationsTo = "UPDATE delegation SET id_delegue = :new_user WHERE id_delegue = :old_user AND statut = 'Active'";
            $stmtDelegationsTo = $this->db->prepare($sqlDelegationsTo);
            $stmtDelegationsTo->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $report['delegations_recues_reassignees'] = $stmtDelegationsTo->rowCount();

            $sqlDelegationsFrom = "UPDATE delegation SET statut = 'Révoquée' WHERE id_delegant = :old_user AND statut = 'Active'";
            $stmtDelegationsFrom = $this->db->prepare($sqlDelegationsFrom);
            $stmtDelegationsFrom->execute([':old_user' => $departingUserId]);
            $report['delegations_emises_revoquees'] = $stmtDelegationsFrom->rowCount();

            $sqlReclamations = "UPDATE reclamation SET numero_personnel_traitant = :new_user WHERE numero_personnel_traitant = :old_user AND id_statut_reclamation IN ('RECLA_OUVERTE', 'RECLA_EN_COURS')";
            $stmtReclamations = $this->db->prepare($sqlReclamations);
            $stmtReclamations->execute([':new_user' => $newUserId, ':old_user' => $departingUserId]);
            $report['reclamations_reassignees'] = $stmtReclamations->rowCount();

            $this->changerStatutCompte($departingUserId, 'archive');

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'TRANSITION_ROLE', $departingUserId, 'Utilisateur', ['nouvel_utilisateur' => $newUserId, 'rapport_transition' => $report]);
            return $report;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array
    {
        $report = ['succes' => 0, 'echecs' => 0, 'erreurs' => []];
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $headerRow = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1', NULL, TRUE, FALSE)[0];
        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de lire le fichier : " . $e->getMessage());
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowDataRaw = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row, NULL, TRUE, FALSE)[0];
            $rowData = [];
            foreach ($mapping as $colonneFichier => $champDb) {
                $colIndex = array_search($colonneFichier, $headerRow);
                if ($colIndex !== false && isset($rowDataRaw[$colIndex])) {
                    $rowData[$champDb] = $rowDataRaw[$colIndex];
                }
            }

            if (empty($rowData['nom']) || empty($rowData['prenom'])) {
                $report['echecs']++;
                $report['erreurs'][] = "Ligne {$row}: Le nom et le prénom sont obligatoires.";
                continue;
            }

            try {
                $this->creerEntite('etudiant', $rowData);
                $report['succes']++;
            } catch (\Exception $e) {
                $report['echecs']++;
                $report['erreurs'][] = "Ligne {$row}: " . $e->getMessage();
            }
        }
        $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'IMPORT_ETUDIANTS', null, 'Fichier', $report);
        return $report;
    }

    /**
     * Récupère la liste des entités (étudiants, enseignants, personnel) qui n'ont pas encore de compte utilisateur.
     * Utile pour le tableau de bord du RS.
     *
     * @param string $typeEntite Le type d'entité à lister ('etudiant', 'enseignant', 'personnel').
     * @return array La liste des entités sans compte.
     */
    public function listerEntitesSansCompte(string $typeEntite): array
    {
        $model = $this->getModelForType($typeEntite);
        $pkCol = $model->getClePrimaire(); // Assurez-vous que getClePrimaire retourne la PK correcte

        $sql = "SELECT e.*
                FROM `{$model->getTable()}` e
                LEFT JOIN `utilisateur` u ON e.numero_utilisateur = u.numero_utilisateur
                WHERE u.numero_utilisateur IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Méthode privée pour obtenir le modèle approprié en fonction du type d'entité.
     */
    private function getModelForType(string $type): GenericModel
    {
        return match (strtolower($type)) {
            'etudiant', 'etu' => $this->etudiantModel,
            'enseignant', 'ens' => $this->enseignantModel,
            'personnel', 'adm' => $this->personnelAdminModel,
            default => throw new InvalidArgumentException("Type de profil '{$type}' non géré."),
        };
    }
}