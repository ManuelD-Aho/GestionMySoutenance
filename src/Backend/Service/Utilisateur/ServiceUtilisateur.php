<?php
// src/Backend/Service/Utilisateur/ServiceUtilisateur.php

namespace App\Backend\Service\Utilisateur;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\GenericModel; // Utilisez GenericModel pour Etudiant, Enseignant, Personnel
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
     * Crée un utilisateur complet (compte + profil associé)
     * Utilise les VRAIES méthodes des modèles et les noms de colonnes corrects
     */
    public function creerUtilisateurComplet(array $userData, array $profileData, string $type): string
    {
        $this->db->beginTransaction();

        try {
            // 1. Validation des données
            $this->validerDonneesUtilisateur($userData, $profileData, $type);

            // 2. Vérifier l'unicité du login et email
            // Correction: Les colonnes dans la DB sont 'login_utilisateur' et 'email_principal'
            $this->verifierUnicite($userData['login_utilisateur'], $userData['email_principal']);

            // 3. Créer l'entité selon le type
            $numeroEntite = $this->creerEntite($type, $profileData);

            // 4. Activer le compte pour cette entité
            // Assurez-vous que les clés passées à activerComptePourEntite correspondent aux noms de colonnes de la table utilisateur
            $userData['numero_entite'] = $numeroEntite; // Ajoutez numero_entite aux données de l'utilisateur
            $success = $this->activerComptePourEntite($numeroEntite, $userData, true);

            if (!$success) {
                throw new OperationImpossibleException("Impossible d'activer le compte utilisateur");
            }

            $this->db->commit();

            // 5. Enregistrer l'action dans l'audit
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATE_ADMIN_USER', // Utiliser l'action appropriée pour la création d'utilisateur
                $numeroEntite, // ID de l'entité créée
                $type, // Type de l'entité (etudiant, enseignant, personnel)
                ['login' => $userData['login_utilisateur'], 'email' => $userData['email_principal']] // Utiliser les noms de colonnes réels
            );

            // 6. Envoyer email de bienvenue si possible
            if ($this->communicationService) {
                // S'assurer que le nom passé est bien le nom de la personne, pas de l'utilisateur.
                $this->envoyerEmailBienvenue($userData['email_principal'], $profileData['nom'] ?? '', $userData['login_utilisateur']);
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
     * Correction: Ajustement des noms de champs si nécessaire.
     * Note: La validation du 'genre' n'est pas cohérente entre les méthodes creerEtudiant, creerEnseignant, creerPersonnelAdministratif.
     * Pour 'personnel', 'poste' et 'service' sont aussi attendus, pas 'genre'.
     */
    private function validerDonneesUtilisateur(array $userData, array $profileData, string $type): void
    {
        // Validation des champs obligatoires utilisateur
        // Correction: 'login' devient 'login_utilisateur', 'email' devient 'email_principal'
        $champsObligatoiresUser = ['login_utilisateur', 'email_principal', 'password', 'id_type_utilisateur', 'id_groupe_utilisateur'];
        foreach ($champsObligatoiresUser as $champ) {
            // Utiliser les noms de clés réels dans $userData
            if (!isset($userData[$champ]) || empty($userData[$champ])) {
                throw new ValidationException("Le champ utilisateur '{$champ}' est obligatoire");
            }
        }

        // Validation des champs obligatoires profil (générique pour les 3 types)
        $champsProfilObligatoiresCommun = ['nom', 'prenom'];
        foreach ($champsProfilObligatoiresCommun as $champ) {
            if (!isset($profileData[$champ]) || empty($profileData[$champ])) {
                throw new ValidationException("Le champ profil '{$champ}' est obligatoire");
            }
        }

        // Validation spécifique au type d'entité pour le profil
        switch (strtolower($type)) {
            case 'etudiant':
                if (empty($profileData['sexe'])) { // 'genre' dans validerDonneesUtilisateur, 'sexe' dans la DB et creerEtudiant
                    throw new ValidationException("Le champ profil 'sexe' est obligatoire pour un étudiant.");
                }
                break;
            case 'enseignant':
                if (empty($profileData['sexe'])) {
                    throw new ValidationException("Le champ profil 'sexe' est obligatoire pour un enseignant.");
                }
                break;
            case 'personnel':
            case 'personnel_administratif':
                if (empty($profileData['sexe'])) {
                    throw new ValidationException("Le champ profil 'sexe' est obligatoire pour un personnel.");
                }
                // Si vous avez d'autres champs obligatoires spécifiques au personnel (ex: poste, service)
                // if (empty($profileData['poste'])) {
                //     throw new ValidationException("Le champ profil 'poste' est obligatoire pour un personnel.");
                // }
                break;
        }


        // Validation email
        if (!filter_var($userData['email_principal'], FILTER_VALIDATE_EMAIL)) { // Correction du nom de champ
            throw new ValidationException("Format d'email invalide");
        }

        // Validation mot de passe
        if (strlen($userData['password']) < 8) {
            throw new ValidationException("Le mot de passe doit contenir au moins 8 caractères");
        }
    }

    /**
     * Vérifie l'unicité du login et de l'email
     * Correction : Utilise les noms de colonnes réels 'login_utilisateur' et 'email_principal'
     */
    private function verifierUnicite(string $login_utilisateur, string $email_principal): void
    {
        $existingUserLogin = $this->utilisateurModel->trouverUnParCritere(['login_utilisateur' => $login_utilisateur]);
        if ($existingUserLogin) {
            throw new DoublonException("Un utilisateur avec ce login existe déjà");
        }

        $existingUserEmail = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $email_principal]);
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

    /**
     * Crée une entité spécifique (étudiant, enseignant, personnel)
     */
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
     * Crée une entité Étudiant
     * Correction: Utilise 'sexe' pour la colonne 'genre' dans $donnees,
     * et s'assure que les champs du modèle 'etudiant' sont bien passés.
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
            'pays_naissance' => $donnees['pays_naissance'] ?? null, // Ajouté, absent dans votre implémentation initiale mais présent dans la DB
            'nationalite' => $donnees['nationalite'] ?? null, // Ajouté
            'sexe' => $donnees['sexe'] ?? null, // Utilise 'sexe' car c'est le nom de la colonne dans la DB 'etudiant'
            'adresse_postale' => $donnees['adresse_postale'] ?? null, // Correction du nom de champ 'adresse' vers 'adresse_postale'
            'ville' => $donnees['ville'] ?? null, // Ajouté
            'code_postal' => $donnees['code_postal'] ?? null, // Ajouté
            'telephone' => $donnees['telephone'] ?? null,
            'email_contact_secondaire' => $donnees['email_contact_secondaire'] ?? null, // Ajouté
            // 'numero_utilisateur' => NULL car lié par l'activation du compte
            'contact_urgence_nom' => $donnees['contact_urgence_nom'] ?? null, // Ajouté
            'contact_urgence_telephone' => $donnees['contact_urgence_telephone'] ?? null, // Ajouté
            'contact_urgence_relation' => $donnees['contact_urgence_relation'] ?? null, // Ajouté
        ];
        // Retiré les champs non présents dans la table `etudiant` selon votre dump SQL
        // 'nom_parent_tuteur' et 'telephone_parent_tuteur' ne sont pas dans `etudiant`
        // 'date_creation' et 'statut_etudiant' ne sont pas dans `etudiant` non plus
        // Il est crucial que les clés de $donneesEtudiant correspondent exactement aux noms de colonnes de votre table `etudiant`.

        $this->etudiantModel->creer($donneesEtudiant);
        return $numeroEtudiant;
    }

    /**
     * Crée une entité Enseignant
     * Correction: Utilise 'sexe' pour la colonne 'genre' dans $donnees,
     * et s'assure que les champs du modèle 'enseignant' sont bien passés.
     * Note: 'specialite' et 'grade' sont des IDs de référence dans votre DB, assurez-vous qu'ils sont gérés comme tels
     * ou qu'il s'agit de simples libellés stockés. Votre table `enseignant` a des colonnes `specialite` et `grade`.
     */
    private function creerEnseignant(array $donnees): string
    {
        $numeroEnseignant = $this->systemeService->genererIdentifiantUnique('ENS');

        $donneesEnseignant = [
            'numero_enseignant' => $numeroEnseignant,
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'telephone_professionnel' => $donnees['telephone_professionnel'] ?? null, // Correction nom de champ
            'email_professionnel' => $donnees['email_professionnel'] ?? null, // Ajouté, absent dans votre implémentation initiale mais présent dans la DB
            // 'numero_utilisateur' => NULL car lié par l'activation du compte
            'date_naissance' => $donnees['date_naissance'] ?? null, // Ajouté
            'lieu_naissance' => $donnees['lieu_naissance'] ?? null, // Ajouté
            'pays_naissance' => $donnees['pays_naissance'] ?? null, // Ajouté
            'nationalite' => $donnees['nationalite'] ?? null, // Ajouté
            'sexe' => $donnees['sexe'] ?? null, // Utilise 'sexe' car c'est le nom de la colonne dans la DB 'enseignant'
            'adresse_postale' => $donnees['adresse_postale'] ?? null, // Ajouté
            'ville' => $donnees['ville'] ?? null, // Ajouté
            'code_postal' => $donnees['code_postal'] ?? null, // Ajouté
            'telephone_personnel' => $donnees['telephone_personnel'] ?? null, // Ajouté
            'email_personnel_secondaire' => $donnees['email_personnel_secondaire'] ?? null, // Ajouté
        ];
        // Les champs 'specialite', 'grade', 'date_recrutement', 'statut_enseignant' ne sont pas des colonnes dans votre table `enseignant`.
        // 'specialite' est dans la table `enseignant`, mais 'grade', 'date_recrutement', 'statut_enseignant' ne le sont pas directement.
        // `grade` est dans la table `acquerir`. `date_recrutement` et `statut_enseignant` n'existent pas dans votre schéma.

        // Si 'specialite' est censé être une colonne directement dans `enseignant` :
        $donneesEnseignant['specialite'] = $donnees['specialite'] ?? null;
        // Si 'grade' est un champ à stocker directement dans `enseignant` (mais ce n'est pas le cas dans votre DB)
        // ou si cela doit être géré via la table `acquerir` séparément :
        // Note: La table `acquerir` lie Enseignant à Grade, ce n'est pas une colonne de la table Enseignant.
        // Si le grade est une donnée du formulaire, il faudra une logique pour l'insérer dans `acquerir`.
        // Exemple d'ajout à `acquerir` si `id_grade` est passé dans $donnees
        // if (isset($donnees['id_grade'])) {
        //     $this->acquerirModel->creer(['id_grade' => $donnees['id_grade'], 'numero_enseignant' => $numeroEnseignant, 'date_acquisition' => date('Y-m-d')]);
        // }


        $this->enseignantModel->creer($donneesEnseignant);
        return $numeroEnseignant;
    }

    /**
     * Crée une entité Personnel Administratif
     * Correction: Utilise 'sexe' pour la colonne 'genre' dans $donnees,
     * et s'assure que les champs du modèle 'personnel_administratif' sont bien passés.
     * Note: 'poste' et 'service' sont des colonnes dans votre table `personnel_administratif`.
     */
    private function creerPersonnelAdministratif(array $donnees): string
    {
        $numeroPersonnel = $this->systemeService->genererIdentifiantUnique('ADM');

        $donneesPersonnel = [
            'numero_personnel_administratif' => $numeroPersonnel,
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'telephone_professionnel' => $donnees['telephone_professionnel'] ?? null, // Ajouté
            'email_professionnel' => $donnees['email_professionnel'] ?? null, // Ajouté
            'date_affectation_service' => $donnees['date_affectation_service'] ?? null, // Ajouté
            'responsabilites_cles' => $donnees['responsabilites_cles'] ?? null, // Ajouté
            // 'numero_utilisateur' => NULL car lié par l'activation du compte
            'date_naissance' => $donnees['date_naissance'] ?? null, // Ajouté
            'lieu_naissance' => $donnees['lieu_naissance'] ?? null, // Ajouté
            'pays_naissance' => $donnees['pays_naissance'] ?? null, // Ajouté
            'nationalite' => $donnees['nationalite'] ?? null, // Ajouté
            'sexe' => $donnees['sexe'] ?? null, // Utilise 'sexe' car c'est le nom de la colonne dans la DB 'personnel_administratif'
            'adresse_postale' => $donnees['adresse_postale'] ?? null, // Ajouté
            'ville' => $donnees['ville'] ?? null, // Ajouté
            'code_postal' => $donnees['code_postal'] ?? null, // Ajouté
            'telephone_personnel' => $donnees['telephone_personnel'] ?? null, // Ajouté
            'email_personnel_secondaire' => $donnees['email_personnel_secondaire'] ?? null, // Ajouté
            // 'poste' et 'service' sont des noms de colonnes dans la DB
            'poste' => $donnees['poste'] ?? 'Agent Administratif',
            'service' => $donnees['service'] ?? 'Administration',
        ];
        // Les champs 'date_embauche' et 'statut_personnel' ne sont pas des colonnes dans votre table `personnel_administratif`.
        // Ils sont 'date_affectation_service' et 'responsabilites_cles'.
        // Votre table `occuper` lie Fonction à Enseignant. C'est une erreur de votre schéma.
        // Si la fonction est gérée via la table `occuper`, il faudrait une logique pour l'insérer ici.
        // Actuellement, 'poste' est une colonne directe dans `personnel_administratif`.

        $this->personnelModel->creer($donneesPersonnel);
        return $numeroPersonnel;
    }

    /**
     * Active un compte utilisateur pour une entité donnée
     * Correction: Utilise les noms de colonnes réels ('login_utilisateur', 'email_principal', 'mot_de_passe')
     */
    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool
    {
        // Générer un ID utilisateur unique
        $numeroUtilisateur = $this->systemeService->genererIdentifiantUnique('SYS'); // Utilise 'SYS' pour les utilisateurs du système/application

        $userData = [
            'numero_utilisateur' => $numeroUtilisateur,
            'login_utilisateur' => $donneesCompte['login_utilisateur'], // Correction du nom de champ
            'email_principal' => $donneesCompte['email_principal'], // Correction du nom de champ
            'mot_de_passe' => password_hash($donneesCompte['password'], PASSWORD_ARGON2ID), // Correction du nom de champ
            'date_creation' => date('Y-m-d H:i:s'),
            'derniere_connexion' => null,
            'token_reset_mdp' => null, // Ajouté si présent dans votre table
            'date_expiration_token_reset' => null, // Ajouté
            'token_validation_email' => $envoyerEmailValidation ? bin2hex(random_bytes(32)) : null,
            'email_valide' => $envoyerEmailValidation ? 0 : 1, // Si email de validation envoyé, alors non validé
            'tentatives_connexion_echouees' => 0,
            'compte_bloque_jusqua' => null, // Ajouté
            'preferences_2fa_active' => 0, // Ajouté
            'secret_2fa' => null, // Ajouté
            'photo_profil' => null, // Ajouté
            'statut_compte' => $envoyerEmailValidation ? 'en_attente_validation' : 'actif',
            'id_niveau_acces_donne' => $donneesCompte['id_niveau_acces_donne'] ?? 'ACCES_PERSONNEL', // Assurez-vous que cette valeur est définie ou a une valeur par défaut logique
            'id_groupe_utilisateur' => $donneesCompte['id_groupe_utilisateur'],
            'id_type_utilisateur' => $donneesCompte['id_type_utilisateur'],
        ];

        // Lier l'utilisateur à l'entité spécifique (étudiant, enseignant, personnel)
        // La table `utilisateur` a une colonne `numero_entite` ou est liée implicitement.
        // Votre schéma ne montre pas `numero_entite` dans la table `utilisateur`.
        // Si `numero_entite` n'existe pas dans la table `utilisateur`, vous devez retirer cette ligne
        // ou l'ajouter à votre schéma `utilisateur` (ce qui est recommandé pour lier clairement les entités).
        // Si elle existe, assurez-vous de la passer ici.
        // Exemple si elle existe: $userData['numero_entite'] = $numeroEntite;
        // Dans votre schéma, il n'y a pas de colonne `numero_entite` dans `utilisateur`.
        // Les jointures se font sur `numero_utilisateur` avec `enseignant`, `etudiant`, `personnel_administratif`.
        // Cela signifie que `numero_utilisateur` dans `enseignant`, `etudiant`, `personnel_administratif` est la FK.
        // Il faut donc mettre à jour l'entité correspondante avec le `numero_utilisateur` nouvellement créé.

        $success = (bool) $this->utilisateurModel->creer($userData);

        if ($success) {
            // Mettre à jour l'entité avec le numero_utilisateur créé
            switch (strtolower($donneesCompte['id_type_utilisateur'])) { // Utilisez id_type_utilisateur de $donneesCompte
                case 'type_etud':
                    $this->etudiantModel->mettreAJourParIdentifiant($numeroEntite, ['numero_utilisateur' => $numeroUtilisateur]);
                    break;
                case 'type_ens':
                    $this->enseignantModel->mettreAJourParIdentifiant($numeroEntite, ['numero_utilisateur' => $numeroUtilisateur]);
                    break;
                case 'type_pers_admin':
                    $this->personnelModel->mettreAJourParIdentifiant($numeroEntite, ['numero_utilisateur' => $numeroUtilisateur]);
                    break;
            }
        }
        return $success;
    }

    /**
     * Liste les utilisateurs complets avec leurs profils associés.
     * Correction principale: Remplacement de `u.login` par `u.login_utilisateur`.
     * Correction secondaire: `gu.nom_groupe` doit être `gu.libelle_groupe` selon votre schéma.
     */
    public function listerUtilisateursComplets(array $filtres = []): array
    {
        $sql = "
            SELECT
                u.numero_utilisateur,
                u.login_utilisateur, -- Correction: Nom de colonne réel
                u.email_principal, -- Correction: Nom de colonne réel
                u.statut_compte,
                u.date_creation,
                u.derniere_connexion,
                u.tentatives_connexion_echouees,
                tu.libelle_type_utilisateur,
                gu.libelle_groupe, -- Correction: Nom de colonne réel
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
            LEFT JOIN etudiant e ON u.numero_utilisateur = e.numero_utilisateur -- Jointure correcte avec la table etudiant
            LEFT JOIN enseignant ens ON u.numero_utilisateur = ens.numero_utilisateur  -- Jointure correcte avec la table enseignant
            LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_utilisateur -- Jointure correcte avec la table personnel_administratif
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
            // Correction: Utilise `u.login_utilisateur` et `u.email_principal`
            $sql .= " AND (u.login_utilisateur LIKE ? OR u.email_principal LIKE ? OR COALESCE(e.nom, ens.nom, pa.nom) LIKE ? OR COALESCE(e.prenom, ens.prenom, pa.prenom) LIKE ?)";
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

        // La ligne 356 du log est dans le prepare, donc ça peut être ici si $this->db->prepare est appelé directement
        // ou si la chaîne $sql est construite ici avant d'être passée à un autre service ou modèle.
        // Assurez-vous que l'objet PDO est bien injecté et que $this->db->prepare est la méthode qui échoue.
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lit un utilisateur complet avec toutes ses données de profil.
     * Correction: Utilise les noms de colonnes réels ('login_utilisateur', 'email_principal', etc.)
     * Note: La jointure `u.numero_entite` n'existe pas dans votre schéma `utilisateur`.
     * Les jointures devraient se faire sur `u.numero_utilisateur` avec les FK dans les tables `etudiant`, `enseignant`, `personnel_administratif`.
     */
    public function lireUtilisateurComplet(string $id): ?array
    {
        $sql = "
            SELECT
                u.numero_utilisateur, u.login_utilisateur, u.email_principal, u.mot_de_passe, u.date_creation,
                u.derniere_connexion, u.token_reset_mdp, u.date_expiration_token_reset, u.token_validation_email,
                u.email_valide, u.tentatives_connexion_echouees, u.compte_bloque_jusqua, u.preferences_2fa_active,
                u.secret_2fa, u.photo_profil, u.statut_compte, u.id_niveau_acces_donne,
                u.id_groupe_utilisateur, u.id_type_utilisateur,
                tu.libelle_type_utilisateur,
                gu.libelle_groupe, -- Correction du nom de colonne
                COALESCE(e.nom, ens.nom, pa.nom) as nom,
                COALESCE(e.prenom, ens.prenom, pa.prenom) as prenom,
                -- Colonnes spécifiques à etudiant
                e.numero_carte_etudiant, e.date_naissance AS etudiant_date_naissance, e.lieu_naissance AS etudiant_lieu_naissance,
                e.pays_naissance AS etudiant_pays_naissance, e.nationalite AS etudiant_nationalite,
                e.sexe AS etudiant_sexe, e.adresse_postale AS etudiant_adresse_postale, e.ville AS etudiant_ville,
                e.code_postal AS etudiant_code_postal, e.telephone AS etudiant_telephone,
                e.email_contact_secondaire AS etudiant_email_contact_secondaire,
                e.contact_urgence_nom, e.contact_urgence_telephone, e.contact_urgence_relation,
                -- Colonnes spécifiques à enseignant
                ens.numero_enseignant, ens.telephone_professionnel AS enseignant_tel_pro,
                ens.email_professionnel AS enseignant_email_pro, ens.date_naissance AS enseignant_date_naissance,
                ens.lieu_naissance AS enseignant_lieu_naissance, ens.pays_naissance AS enseignant_pays_naissance,
                ens.nationalite AS enseignant_nationalite, ens.sexe AS enseignant_sexe,
                ens.adresse_postale AS enseignant_adresse_postale, ens.ville AS enseignant_ville,
                ens.code_postal AS enseignant_code_postal, ens.telephone_personnel AS enseignant_tel_perso,
                ens.email_personnel_secondaire AS enseignant_email_perso, ens.specialite,
                -- Colonnes spécifiques à personnel_administratif
                pa.numero_personnel_administratif, pa.telephone_professionnel AS personnel_tel_pro,
                pa.email_professionnel AS personnel_email_pro, pa.date_affectation_service, pa.responsabilites_cles,
                pa.date_naissance AS personnel_date_naissance, pa.lieu_naissance AS personnel_lieu_naissance,
                pa.pays_naissance AS personnel_pays_naissance, pa.nationalite AS personnel_nationalite,
                pa.sexe AS personnel_sexe, pa.adresse_postale AS personnel_adresse_postale, pa.ville AS personnel_ville,
                pa.code_postal AS personnel_code_postal, pa.telephone_personnel AS personnel_tel_perso,
                pa.email_personnel_secondaire AS personnel_email_perso, pa.poste, pa.service,

                CASE
                    WHEN e.numero_carte_etudiant IS NOT NULL THEN 'etudiant'
                    WHEN ens.numero_enseignant IS NOT NULL THEN 'enseignant'
                    WHEN pa.numero_personnel_administratif IS NOT NULL THEN 'personnel'
                    ELSE 'unknown'
                END as type_entite,
                -- Récupérer le grade de l'enseignant via la table acquerir et grade
                -- Correction: Joindre acquerir et grade si c'est un enseignant
                (SELECT gr.libelle_grade FROM acquerir ac JOIN grade gr ON ac.id_grade = gr.id_grade WHERE ac.numero_enseignant = ens.numero_enseignant ORDER BY ac.date_acquisition DESC LIMIT 1) as grade_enseignant,
                -- Récupérer la fonction du personnel via la table occuper et fonction
                -- Correction: Joindre occuper et fonction si c'est un personnel
                -- ATTENTION: Votre table `occuper` lie `fonction` à `enseignant`, pas `personnel_administratif`!
                -- Si cette erreur n'est pas corrigée dans votre DB, cette sous-requête échouera pour le personnel.
                (SELECT fct.libelle_fonction FROM occuper occ JOIN fonction fct ON occ.id_fonction = fct.id_fonction WHERE occ.numero_personnel_administratif = pa.numero_personnel_administratif ORDER BY occ.date_debut_occupation DESC LIMIT 1) as fonction_personnel

            FROM utilisateur u
            LEFT JOIN type_utilisateur tu ON u.id_type_utilisateur = tu.id_type_utilisateur
            LEFT JOIN groupe_utilisateur gu ON u.id_groupe_utilisateur = gu.id_groupe_utilisateur
            LEFT JOIN etudiant e ON u.numero_utilisateur = e.numero_utilisateur
            LEFT JOIN enseignant ens ON u.numero_utilisateur = ens.numero_utilisateur
            LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_utilisateur
            WHERE u.numero_utilisateur = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Met à jour un utilisateur (compte et profil associé).
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
            // Correction: Utilisez 'email_principal' pour la colonne email
            if (isset($donneesCompte['email_principal']) && $donneesCompte['email_principal'] !== $user['email_principal']) {
                // Vérifier unicité email
                $existingUser = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $donneesCompte['email_principal']]);
                if ($existingUser && $existingUser['numero_utilisateur'] !== $numeroUtilisateur) {
                    throw new DoublonException("Cet email est déjà utilisé");
                }
                $updateUserData['email_principal'] = $donneesCompte['email_principal'];
            }

            if (isset($donneesCompte['statut_compte'])) {
                $updateUserData['statut_compte'] = $donneesCompte['statut_compte'];
            }

            if (isset($donneesCompte['id_groupe_utilisateur'])) { // Correction du nom de champ
                $updateUserData['id_groupe_utilisateur'] = $donneesCompte['id_groupe_utilisateur'];
            }

            if (!empty($updateUserData)) {
                $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $updateUserData);
            }

            // 3. Mettre à jour le profil selon le type d'entité
            // Correction: On passe directement le type d'entité récupéré de `lireUtilisateurComplet`
            $this->mettreAJourProfilEntite($user['type_entite'], $user['numero_utilisateur'], $donneesProfil); // Passer numero_utilisateur

            $this->db->commit();

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'UPDATE_USER', // Action plus spécifique si disponible, sinon MODIFICATION_UTILISATEUR
                $numeroUtilisateur,
                'Utilisateur'
            );

            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur mise à jour utilisateur complet: " . $e->getMessage()); // Ajout du log de l'erreur
            throw $e;
        }
    }

    /**
     * Met à jour le profil d'une entité spécifique (étudiant, enseignant, personnel)
     * Correction: Utilise l'ID réel de l'entité et le modèle approprié.
     */
    private function mettreAJourProfilEntite(string $typeEntite, string $numeroUtilisateur, array $donneesProfil): void
    {
        // Récupérer le numéro de l'entité liée à cet utilisateur
        $stmt = $this->db->prepare("SELECT numero_carte_etudiant, numero_enseignant, numero_personnel_administratif FROM utilisateur WHERE numero_utilisateur = ?");
        $stmt->execute([$numeroUtilisateur]);
        $linkedEntity = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$linkedEntity) {
            throw new ElementNonTrouveException("Entité liée à l'utilisateur introuvable.");
        }

        $numeroEntite = null;
        switch (strtolower($typeEntite)) {
            case 'etudiant':
                $numeroEntite = $linkedEntity['numero_carte_etudiant'];
                if (!$numeroEntite) throw new ElementNonTrouveException("Numéro d'étudiant introuvable pour l'utilisateur.");
                $this->etudiantModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
            case 'enseignant':
                $numeroEntite = $linkedEntity['numero_enseignant'];
                if (!$numeroEntite) throw new ElementNonTrouveException("Numéro d'enseignant introuvable pour l'utilisateur.");
                $this->enseignantModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
            case 'personnel':
                $numeroEntite = $linkedEntity['numero_personnel_administratif'];
                if (!$numeroEntite) throw new ElementNonTrouveException("Numéro de personnel administratif introuvable pour l'utilisateur.");
                $this->personnelModel->mettreAJourParIdentifiant($numeroEntite, $donneesProfil);
                break;
            default:
                throw new ValidationException("Type d'entité non reconnu pour la mise à jour du profil : {$typeEntite}");
        }
    }

    /**
     * Supprime un utilisateur et son entité associée.
     */
    public function supprimerUtilisateurEtEntite(string $id): bool
    {
        $this->db->beginTransaction();

        try {
            $user = $this->lireUtilisateurComplet($id);
            if (!$user) {
                throw new ElementNonTrouveException("Utilisateur introuvable");
            }

            // Récupérer le numéro de l'entité liée à cet utilisateur pour suppression
            $numeroEntiteLiee = null;
            switch (strtolower($user['id_type_utilisateur'])) { // Utiliser id_type_utilisateur du user complet
                case 'type_etud':
                    $numeroEntiteLiee = $user['numero_carte_etudiant'];
                    break;
                case 'type_ens':
                    $numeroEntiteLiee = $user['numero_enseignant'];
                    break;
                case 'type_pers_admin':
                    $numeroEntiteLiee = $user['numero_personnel_administratif'];
                    break;
            }

            if (!$numeroEntiteLiee) {
                throw new OperationImpossibleException("Impossible de déterminer l'entité liée à l'utilisateur pour suppression.");
            }

            // 1. Supprimer l'utilisateur (cela devrait cascader la suppression via FK dans etudiant/enseignant/personnel)
            // Correction: La suppression de l'utilisateur devrait entraîner la suppression de l'entité liée
            // si la clé étrangère dans etudiant/enseignant/personnel_administratif a ON DELETE CASCADE.
            // Si ce n'est pas le cas, l'ordre doit être : supprimer l'entité D'ABORD, puis l'utilisateur.

            // Vérifions votre schéma : les FK dans etudiant, enseignant, personnel_administratif vers utilisateur
            // ont ON DELETE CASCADE. Donc, il faut supprimer l'utilisateur en premier.
            $this->utilisateurModel->supprimerParIdentifiant($id);


            $this->db->commit();

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'DELETE_USER_HARD', // Action spécifique pour suppression définitive
                $id,
                'Utilisateur'
            );

            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur suppression utilisateur complet: " . $e->getMessage()); // Ajout du log de l'erreur
            throw $e;
        }
    }

    // Implémentation des autres méthodes requises par l'interface
    public function createAdminUser(string $login, string $email, string $password): string
    {
        $userData = [
            'login_utilisateur' => $login, // Correction du nom de champ
            'email_principal' => $email,   // Correction du nom de champ
            'password' => $password,
            'id_type_utilisateur' => 'TYPE_ADMIN', // Utiliser l'ID réel du type d'utilisateur
            'id_groupe_utilisateur' => 'GRP_ADMIN_SYS', // Utiliser l'ID réel du groupe
            'id_niveau_acces_donne' => 'ACCES_TOTAL', // Ajouté, car requis par la table utilisateur
        ];

        $profileData = [
            'nom' => 'Administrateur',
            'prenom' => 'Système',
            'sexe' => 'Autre', // Ajouté 'sexe' pour la validation générique du profil
            'poste' => 'Administrateur Système',
            'service' => 'IT'
        ];

        return $this->creerUtilisateurComplet($userData, $profileData, 'personnel'); // 'personnel' pour l'entité physique
    }

    /**
     * Change le statut du compte d'un utilisateur.
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
     * Réinitialise le mot de passe d'un utilisateur par l'admin.
     */
    public function reinitialiserMotDePasseAdmin(string $id): bool
    {
        $nouveauMotDePasse = bin2hex(random_bytes(8)); // Générer un mot de passe aléatoire
        $hash = password_hash($nouveauMotDePasse, PASSWORD_ARGON2ID);

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($id, [
            'mot_de_passe' => $hash, // Correction du nom de colonne
            // 'force_changement_mot_de_passe' => 1 // Cette colonne n'existe pas dans votre schéma `utilisateur`
        ]);

        // TODO: Envoyer le nouveau mot de passe par email
        if ($success && $this->communicationService) {
            $user = $this->utilisateurModel->trouverParIdentifiant($id);
            if ($user) {
                $this->communicationService->envoyerEmail(
                    $user['email_principal'],
                    'ADMIN_PASSWORD_RESET',
                    ['nouveau_mdp' => $nouveauMotDePasse]
                );
            }
        }

        return $success;
    }

    public function renvoyerEmailValidation(string $numeroUtilisateur): bool
    {
        // Récupérer l'utilisateur pour son email et son token actuel
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user) {
            throw new ElementNonTrouveException("Utilisateur introuvable.");
        }

        if ($user['email_valide'] == 1) {
            throw new OperationImpossibleException("L'email de cet utilisateur est déjà validé.");
        }

        // Générer un nouveau token si nécessaire ou utiliser l'existant
        $token = $user['token_validation_email'] ?: bin2hex(random_bytes(32));
        if (!$user['token_validation_email']) {
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['token_validation_email' => $token]);
        }

        // Construire le lien de validation (ajustez l'URL de base selon votre application)
        $validationLink = 'http://localhost:8080/validate-email?token=' . $token; // Exemple d'URL, à adapter

        try {
            if ($this->communicationService) {
                $this->communicationService->envoyerEmail(
                    $user['email_principal'],
                    'VALIDATE_EMAIL',
                    ['validation_link' => $validationLink]
                );
                $this->supervisionService->enregistrerAction(
                    $_SESSION['user_id'] ?? 'SYSTEM',
                    'RESEND_VALIDATION_EMAIL',
                    $numeroUtilisateur,
                    'Utilisateur'
                );
                return true;
            }
        } catch (\Exception $e) {
            error_log("Erreur envoi email de validation: " . $e->getMessage());
        }
        return false;
    }

    public function telechargerPhotoProfil(string $numeroUtilisateur, array $fileData): string
    {
        // Récupérer le chemin de base pour les uploads
        $basePath = $this->systemeService->getParametreSysteme('UPLOADS_PATH_BASE');
        $profilePicturesPath = $this->systemeService->getParametreSysteme('UPLOADS_PATH_PROFILE_PICTURES');

        // Assurez-vous que le répertoire existe
        $uploadDir = $basePath . $profilePicturesPath;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new OperationImpossibleException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = $numeroUtilisateur . '_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . $fileName;

        // Déplacer le fichier téléchargé
        if (move_uploaded_file($fileData['tmp_name'], $destination)) {
            // Mettre à jour la base de données avec le chemin de la photo
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['photo_profil' => $profilePicturesPath . $fileName]);

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'UPLOAD_PROFILE_PICTURE',
                $numeroUtilisateur,
                'Utilisateur',
                ['file_name' => $fileName]
            );
            return $profilePicturesPath . $fileName;
        } else {
            throw new OperationImpossibleException("Échec de l'upload de la photo de profil.");
        }
    }


    // Méthodes de délégation - à implémenter selon les besoins
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string
    {
        $idDelegation = $this->systemeService->genererIdentifiantUnique('DEL');

        $delegationData = [
            'id_delegation' => $idDelegation,
            'id_delegant' => $idDelegant, // Correction du nom de colonne
            'id_delegue' => $idDelegue,   // Correction du nom de colonne
            'id_traitement' => $idTraitement,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'contexte_id' => $contexteId,
            'contexte_type' => $contexteType,
            'statut' => 'Active', // Correction: Utilise 'statut' au lieu de 'statut_delegation'
            // 'date_creation' n'est pas une colonne dans la table `delegation` selon votre dump,
            // elle est définie par DEFAULT CURRENT_TIMESTAMP.
        ];

        // Le modèle de délégation doit avoir une méthode `creer` qui correspond à la signature de GenericModel
        $this->delegationModel->creer($delegationData);
        return $idDelegation;
    }

    /**
     * Révoque une délégation existante.
     */
    public function revoquerDelegation(string $idDelegation): bool
    {
        return $this->delegationModel->mettreAJourParIdentifiant($idDelegation, [
            'statut' => 'Révoquée', // Correction: Utilise 'statut' au lieu de 'statut_delegation'
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
        // Cela impliquerait de transférer certaines responsabilités ou données d'un utilisateur à un autre.
        return [];
    }

    public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array
    {
        // TODO: Implémenter l'import d'étudiants depuis fichier
        // Lire le fichier CSV/Excel, valider les données, puis appeler creerUtilisateurComplet pour chaque étudiant.
        return [];
    }

    public function listerEntitesSansCompte(string $typeEntite): array
    {
        // TODO: Implémenter la liste des entités sans compte
        // Cela nécessiterait des requêtes pour trouver les entrées dans etudiant, enseignant, personnel_administratif
        // qui n'ont PAS de `numero_utilisateur` lié à la table `utilisateur`.
        return [];
    }

    public function setDocumentService($get)
    {
        // Méthode vide, peut être supprimée si non utilisée.
    }
}