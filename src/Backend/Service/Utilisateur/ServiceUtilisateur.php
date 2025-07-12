<?php
// src/Backend/Service/Utilisateur/ServiceUtilisateur.php

namespace App\Backend\Service\Utilisateur;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\GenericModel;
use App\Backend\Model\Delegation;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, DoublonException, ValidationException};

class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private GenericModel $etudiantModel;
    private GenericModel $enseignantModel;
    private GenericModel $personnelModel;
    private Delegation $delegationModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;
    private ?ServiceCommunicationInterface $communicationService = null;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        GenericModel $etudiantModel,
        GenericModel $enseignantModel,
        GenericModel $personnelModel,
        Delegation $delegationModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->etudiantModel = $etudiantModel;
        $this->enseignantModel = $enseignantModel;
        $this->personnelModel = $personnelModel;
        $this->delegationModel = $delegationModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    /**
     * Injection optionnelle du service de communication
     */
    public function setCommunicationService(ServiceCommunicationInterface $communicationService): void
    {
        $this->communicationService = $communicationService;
    }

    /**
     * ✅ CORRECTION PRINCIPALE : Implémentation de la méthode manquante
     * Utilise les VRAIES méthodes des modèles
     */
    public function creerUtilisateurComplet(array $userData, array $profileData, string $type): string
    {
        $this->db->beginTransaction();

        try {
            // 1. Validation des données
            $this->validerDonneesUtilisateur($userData, $profileData, $type);

            // 2. Vérifier l'unicité du login et email
            $this->verifierUnicite($userData['login'], $userData['email']);

            // 3. Créer l'entité selon le type
            $numeroEntite = $this->creerEntite($type, $profileData);

            // 4. Activer le compte pour cette entité
            $userData['numero_entite'] = $numeroEntite;
            $success = $this->activerComptePourEntite($numeroEntite, $userData, true);

            if (!$success) {
                throw new OperationImpossibleException("Impossible d'activer le compte utilisateur");
            }

            $this->db->commit();

            // 5. Enregistrer l'action dans l'audit
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_UTILISATEUR_COMPLET',
                $numeroEntite,
                $type,
                ['login' => $userData['login'], 'email' => $userData['email']]
            );

            // 6. Envoyer email de bienvenue si possible
            if ($this->communicationService) {
                $this->envoyerEmailBienvenue($userData['email'], $profileData['nom'] ?? '', $userData['login']);
            }

            return $numeroEntite;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur création utilisateur complet: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validation des données utilisateur
     */
    private function validerDonneesUtilisateur(array $userData, array $profileData, string $type): void
    {
        // Validation des champs obligatoires utilisateur
        $champsObligatoires = ['login', 'email', 'password', 'type_utilisateur', 'groupe_utilisateur'];
        foreach ($champsObligatoires as $champ) {
            if (empty($userData[$champ])) {
                throw new ValidationException("Le champ '{$champ}' est obligatoire");
            }
        }

        // Validation des champs obligatoires profil
        $champsProfilObligatoires = ['nom', 'prenom', 'genre'];
        foreach ($champsProfilObligatoires as $champ) {
            if (empty($profileData[$champ])) {
                throw new ValidationException("Le champ profil '{$champ}' est obligatoire");
            }
        }

        // Validation email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Format d'email invalide");
        }

        // Validation mot de passe
        if (strlen($userData['password']) < 8) {
            throw new ValidationException("Le mot de passe doit contenir au moins 8 caractères");
        }
    }

    /**
     * ✅ CORRECTION : Utilise trouverUnParCritere() au lieu de readByField()
     */
    private function verifierUnicite(string $login, string $email): void
    {
        $existingUserLogin = $this->utilisateurModel->trouverUnParCritere(['login' => $login]);
        if ($existingUserLogin) {
            throw new DoublonException("Un utilisateur avec ce login existe déjà");
        }

        $existingUserEmail = $this->utilisateurModel->trouverUnParCritere(['email' => $email]);
        if ($existingUserEmail) {
            throw new DoublonException("Un utilisateur avec cet email existe déjà");
        }
    }

    /**
     * Envoi d'email de bienvenue
     */
    private function envoyerEmailBienvenue(string $email, string $nom, string $login): void
    {
        try {
            if ($this->communicationService) {
                $this->communicationService->envoyerEmail(
                    $email,
                    'TPL_BIENVENUE_UTILISATEUR',
                    [
                        'nom' => $nom,
                        'login' => $login,
                        'date' => date('d/m/Y H:i')
                    ]
                );
            }
        } catch (\Exception $e) {
            error_log("Erreur envoi email bienvenue: " . $e->getMessage());
        }
    }

    public function creerEntite(string $typeEntite, array $donneesProfil): string
    {
        switch (strtolower($typeEntite)) {
            case 'etudiant':
                return $this->creerEtudiant($donneesProfil);
            case 'enseignant':
                return $this->creerEnseignant($donneesProfil);
            case 'personnel':
            case 'personnel_administratif':
                return $this->creerPersonnelAdministratif($donneesProfil);
            default:
                throw new ValidationException("Type d'entité non reconnu : {$typeEntite}");
        }
    }

    /**
     * ✅ CORRECTION : Utilise genererIdentifiantUnique() et creer()
     */
    private function creerEtudiant(array $donnees): string
    {
        $numeroEtudiant = $this->systemeService->genererIdentifiantUnique('ETU');

        $donneesEtudiant = [
            'numero_carte_etudiant' => $numeroEtudiant,
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'date_naissance' => $donnees['date_naissance'] ?? null,
            'lieu_naissance' => $donnees['lieu_naissance'] ?? null,
            'genre' => $donnees['genre'],
            'telephone' => $donnees['telephone'] ?? null,
            'adresse' => $donnees['adresse'] ?? null,
            'nom_parent_tuteur' => $donnees['nom_parent_tuteur'] ?? null,
            'telephone_parent_tuteur' => $donnees['telephone_parent_tuteur'] ?? null,
            'date_creation' => date('Y-m-d H:i:s'),
            'statut_etudiant' => 'actif'
        ];

        $this->etudiantModel->creer($donneesEtudiant);
        return $numeroEtudiant;
    }

    /**
     * ✅ CORRECTION : Utilise genererIdentifiantUnique() et creer()
     */
    private function creerEnseignant(array $donnees): string
    {
        $numeroEnseignant = $this->systemeService->genererIdentifiantUnique('ENS');

        $donneesEnseignant = [
            'numero_enseignant' => $numeroEnseignant,
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'genre' => $donnees['genre'],
            'telephone' => $donnees['telephone'] ?? null,
            'specialite' => $donnees['specialite'] ?? null,
            'grade' => $donnees['grade'] ?? 'Assistant',
            'date_recrutement' => $donnees['date_recrutement'] ?? date('Y-m-d'),
            'statut_enseignant' => 'actif'
        ];

        $this->enseignantModel->creer($donneesEnseignant);
        return $numeroEnseignant;
    }

    /**
     * ✅ CORRECTION : Utilise genererIdentifiantUnique() et creer()
     */
    private function creerPersonnelAdministratif(array $donnees): string
    {
        $numeroPersonnel = $this->systemeService->genererIdentifiantUnique('ADM');

        $donneesPersonnel = [
            'numero_personnel_administratif' => $numeroPersonnel,
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'genre' => $donnees['genre'],
            'telephone' => $donnees['telephone'] ?? null,
            'poste' => $donnees['poste'] ?? 'Agent Administratif',
            'service' => $donnees['service'] ?? 'Administration',
            'date_embauche' => $donnees['date_embauche'] ?? date('Y-m-d'),
            'statut_personnel' => 'actif'
        ];

        $this->personnelModel->creer($donneesPersonnel);
        return $numeroPersonnel;
    }

    /**
     * ✅ CORRECTION : Utilise genererIdentifiantUnique() et creer()
     */
    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool
    {
        // Générer un ID utilisateur unique
        $numeroUtilisateur = $this->systemeService->genererIdentifiantUnique('USR');

        $userData = [
            'numero_utilisateur' => $numeroUtilisateur,
            'numero_entite' => $numeroEntite,
            'id_type_utilisateur' => $donneesCompte['type_utilisateur'],
            'id_groupe_utilisateur' => $donneesCompte['groupe_utilisateur'],
            'login' => $donneesCompte['login'],
            'mot_de_passe_hash' => password_hash($donneesCompte['password'], PASSWORD_ARGON2ID),
            'email' => $donneesCompte['email'],
            'statut_compte' => $envoyerEmailValidation ? 'en_attente_validation' : 'actif',
            'date_creation' => date('Y-m-d H:i:s'),
            'derniere_connexion' => null,
            'tentatives_connexion_echouees' => 0,
            'token_validation_email' => $envoyerEmailValidation ? bin2hex(random_bytes(32)) : null
        ];

        return (bool) $this->utilisateurModel->creer($userData);
    }

    public function listerUtilisateursComplets(array $filtres = []): array
    {
        $sql = "
            SELECT 
                u.numero_utilisateur,
                u.login,
                u.email,
                u.statut_compte,
                u.date_creation,
                u.derniere_connexion,
                u.tentatives_connexion_echouees,
                tu.libelle_type_utilisateur,
                gu.nom_groupe,
                COALESCE(e.nom, ens.nom, pa.nom) as nom,
                COALESCE(e.prenom, ens.prenom, pa.prenom) as prenom,
                CASE 
                    WHEN e.numero_carte_etudiant IS NOT NULL THEN 'etudiant'
                    WHEN ens.numero_enseignant IS NOT NULL THEN 'enseignant'
                    WHEN pa.numero_personnel_administratif IS NOT NULL THEN 'personnel'
                    ELSE 'unknown'
                END as type_entite
            FROM utilisateur u
            LEFT JOIN type_utilisateur tu ON u.id_type_utilisateur = tu.id_type_utilisateur
            LEFT JOIN groupe_utilisateur gu ON u.id_groupe_utilisateur = gu.id_groupe_utilisateur
            LEFT JOIN etudiant e ON u.numero_entite = e.numero_carte_etudiant
            LEFT JOIN enseignant ens ON u.numero_entite = ens.numero_enseignant  
            LEFT JOIN personnel_administratif pa ON u.numero_entite = pa.numero_personnel_administratif
            WHERE 1=1
        ";

        $params = [];

        // Appliquer les filtres
        if (!empty($filtres['statut'])) {
            $sql .= " AND u.statut_compte = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['type'])) {
            $sql .= " AND u.id_type_utilisateur = ?";
            $params[] = $filtres['type'];
        }

        if (!empty($filtres['groupe'])) {
            $sql .= " AND u.id_groupe_utilisateur = ?";
            $params[] = $filtres['groupe'];
        }

        if (!empty($filtres['search'])) {
            $sql .= " AND (u.login LIKE ? OR u.email LIKE ? OR COALESCE(e.nom, ens.nom, pa.nom) LIKE ? OR COALESCE(e.prenom, ens.prenom, pa.prenom) LIKE ?)";
            $searchTerm = '%' . $filtres['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY u.date_creation DESC";

        // Ajouter pagination si spécifiée
        if (isset($filtres['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filtres['limit'];

            if (isset($filtres['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filtres['offset'];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lireUtilisateurComplet(string $id): ?array
    {
        $sql = "
            SELECT 
                u.*,
                tu.libelle_type_utilisateur,
                gu.nom_groupe,
                COALESCE(e.nom, ens.nom, pa.nom) as nom,
                COALESCE(e.prenom, ens.prenom, pa.prenom) as prenom,
                e.date_naissance, e.lieu_naissance, e.telephone as telephone_etudiant,
                e.adresse, e.nom_parent_tuteur, e.telephone_parent_tuteur, e.statut_etudiant,
                ens.specialite, ens.grade, ens.date_recrutement, ens.statut_enseignant,
                pa.poste, pa.service, pa.date_embauche, pa.statut_personnel,
                CASE 
                    WHEN e.numero_carte_etudiant IS NOT NULL THEN 'etudiant'
                    WHEN ens.numero_enseignant IS NOT NULL THEN 'enseignant'
                    WHEN pa.numero_personnel_administratif IS NOT NULL THEN 'personnel'
                    ELSE 'unknown'
                END as type_entite
            FROM utilisateur u
            LEFT JOIN type_utilisateur tu ON u.id_type_utilisateur = tu.id_type_utilisateur
            LEFT JOIN groupe_utilisateur gu ON u.id_groupe_utilisateur = gu.id_groupe_utilisateur
            LEFT JOIN etudiant e ON u.numero_entite = e.numero_carte_etudiant
            LEFT JOIN enseignant ens ON u.numero_entite = ens.numero_enseignant  
            LEFT JOIN personnel_administratif pa ON u.numero_entite = pa.numero_personnel_administratif
            WHERE u.numero_utilisateur = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * ✅ CORRECTION : Utilise mettreAJourParIdentifiant()
     */
    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesProfil, array $donneesCompte): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Récupérer l'utilisateur actuel
            $user = $this->lireUtilisateurComplet($numeroUtilisateur);
            if (!$user) {
                throw new ElementNonTrouveException("Utilisateur introuvable");
            }

            // 2. Mettre à jour le compte utilisateur
            $updateUserData = [];
            if (isset($donneesCompte['email']) && $donneesCompte['email'] !== $user['email']) {
                // Vérifier unicité email
                $existingUser = $this->utilisateurModel->trouverUnParCritere(['email' => $donneesCompte['email']]);
                if ($existingUser && $existingUser['numero_utilisateur'] !== $numeroUtilisateur) {
                    throw new DoublonException("Cet email est déjà utilisé");
                }
                $updateUserData['email'] = $donneesCompte['email'];
            }

            if (isset($donneesCompte['statut_compte'])) {
                $updateUserData['statut_compte'] = $donneesCompte['statut_compte'];
            }

            if (isset($donneesCompte['groupe_utilisateur'])) {
                $updateUserData['id_groupe_utilisateur'] = $donneesCompte['groupe_utilisateur'];
            }

            if (!empty($updateUserData)) {
                $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $updateUserData);
            }

            // 3. Mettre à jour le profil selon le type d'entité
            $this->mettreAJourProfilEntite($user['numero_entite'], $user['type_entite'], $donneesProfil);

            $this->db->commit();

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIFICATION_UTILISATEUR',
                $numeroUtilisateur,
                'Utilisateur'
            );

            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ✅ CORRECTION : Utilise mettreAJourParIdentifiant()
     */
    private function mettreAJourProfilEntite(string $numeroEntite, string $typeEntite, array $donneesProfil): void
    {
        switch ($typeEntite) {
            case 'etudiant':
                $this->etudiantModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
            case 'enseignant':
                $this->enseignantModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
            case 'personnel':
                $this->personnelModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
        }
    }

    /**
     * ✅ CORRECTION : Utilise supprimerParIdentifiant()
     */
    public function supprimerUtilisateurEtEntite(string $id): bool
    {
        $this->db->beginTransaction();

        try {
            $user = $this->lireUtilisateurComplet($id);
            if (!$user) {
                throw new ElementNonTrouveException("Utilisateur introuvable");
            }

            // 1. Supprimer l'utilisateur
            $this->utilisateurModel->supprimerParIdentifiant($id);

            // 2. Supprimer l'entité associée
            switch ($user['type_entite']) {
                case 'etudiant':
                    $this->etudiantModel->supprimerParIdentifiant($user['numero_entite']);
                    break;
                case 'enseignant':
                    $this->enseignantModel->supprimerParIdentifiant($user['numero_entite']);
                    break;
                case 'personnel':
                    $this->personnelModel->supprimerParIdentifiant($user['numero_entite']);
                    break;
            }

            $this->db->commit();

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_UTILISATEUR',
                $id,
                'Utilisateur'
            );

            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Implémentation des autres méthodes requises par l'interface
    public function createAdminUser(string $login, string $email, string $password): string
    {
        $userData = [
            'login' => $login,
            'email' => $email,
            'password' => $password,
            'type_utilisateur' => 'ADMIN',
            'groupe_utilisateur' => 'GRP_ADMIN_SYS'
        ];

        $profileData = [
            'nom' => 'Administrateur',
            'prenom' => 'Système',
            'genre' => 'M',
            'poste' => 'Administrateur Système',
            'service' => 'IT'
        ];

        return $this->creerUtilisateurComplet($userData, $profileData, 'personnel');
    }

    /**
     * ✅ CORRECTION : Utilise mettreAJourParIdentifiant()
     */
    public function changerStatutCompte(string $numeroUtilisateur, string $nouveauStatut): bool
    {
        $statutsValides = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
        if (!in_array($nouveauStatut, $statutsValides)) {
            throw new ValidationException("Statut non valide");
        }

        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);
    }

    /**
     * ✅ CORRECTION : Utilise mettreAJourParIdentifiant()
     */
    public function reinitialiserMotDePasseAdmin(string $id): bool
    {
        $nouveauMotDePasse = bin2hex(random_bytes(8));
        $hash = password_hash($nouveauMotDePasse, PASSWORD_ARGON2ID);

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($id, [
            'mot_de_passe_hash' => $hash,
            'force_changement_mot_de_passe' => 1
        ]);

        // TODO: Envoyer le nouveau mot de passe par email

        return $success;
    }

    public function renvoyerEmailValidation(string $numeroUtilisateur): bool
    {
        // TODO: Implémenter l'envoi d'email de validation
        return true;
    }

    public function telechargerPhotoProfil(string $numeroUtilisateur, array $fileData): string
    {
        // TODO: Implémenter l'upload de photo de profil
        return '';
    }

    // Méthodes de délégation - à implémenter selon les besoins
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string
    {
        $idDelegation = $this->systemeService->genererIdentifiantUnique('DEL');

        $delegationData = [
            'id_delegation' => $idDelegation,
            'numero_utilisateur_delegant' => $idDelegant,
            'numero_utilisateur_delegue' => $idDelegue,
            'id_traitement' => $idTraitement,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'contexte_id' => $contexteId,
            'contexte_type' => $contexteType,
            'statut_delegation' => 'active',
            'date_creation' => date('Y-m-d H:i:s')
        ];

        $this->delegationModel->creer($delegationData);
        return $idDelegation;
    }

    /**
     * ✅ CORRECTION : Utilise mettreAJourParIdentifiant()
     */
    public function revoquerDelegation(string $idDelegation): bool
    {
        return $this->delegationModel->mettreAJourParIdentifiant($idDelegation, [
            'statut_delegation' => 'revoquee',
            'date_revocation' => date('Y-m-d H:i:s')
        ]);
    }

    public function listerDelegations(array $filtres = []): array
    {
        return $this->delegationModel->trouverParCritere($filtres);
    }

    public function lireDelegation(string $idDelegation): ?array
    {
        return $this->delegationModel->trouverParIdentifiant($idDelegation);
    }

    public function gererTransitionsRoles(string $departingUserId, string $newUserId): array
    {
        // TODO: Implémenter la gestion des transitions de rôles
        return [];
    }

    public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array
    {
        // TODO: Implémenter l'import d'étudiants depuis fichier
        return [];
    }

    public function listerEntitesSansCompte(string $typeEntite): array
    {
        // TODO: Implémenter la liste des entités sans compte
        return [];
    }

    public function setDocumentService($get)
    {
    }
}