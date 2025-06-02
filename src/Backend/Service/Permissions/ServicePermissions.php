<?php

namespace App\Backend\Service\Permissions;

use PDO;
use PDOException;
use DateTime;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
// Supposons que vos exceptions sont toujours pertinentes
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class ServicePermissions implements ServicePermissionsInterface
{
    private PDO $db;
    private ServiceSupervisionAdminInterface $serviceSupervision;
    private string $currentUserLogin;

    public function __construct(PDO $db, ServiceSupervisionAdminInterface $serviceSupervision)
    {
        $this->db = $db;
        $this->serviceSupervision = $serviceSupervision;
        $this->currentUserLogin = $_SESSION['login_utilisateur'] ?? 'SYSTEME';
    }

    /**
     * Crée un nouveau groupe d'utilisateurs.
     * L'ID est fourni car il est de type VARCHAR.
     */
    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle): string
    {
        if (empty(trim($idGroupeUtilisateur))) {
            throw new ValidationException("L'identifiant du groupe ne peut être vide.");
        }
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du groupe ne peut être vide.");
        }

        // Vérifier les doublons sur l'ID ou le libellé
        $sqlCheck = "SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE id_groupe_utilisateur = :id_check OR lib_groupe_utilisateur = :libelle_check";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_check', $idGroupeUtilisateur);
        $stmtCheck->bindParam(':libelle_check', $libelle);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un groupe avec cet identifiant ou libellé existe déjà.");
        }

        // Insertion avec seulement id_groupe_utilisateur et libelle_groupe_utilisateur
        $sql = "INSERT INTO groupe_utilisateur (id_groupe_utilisateur, lib_groupe_utilisateur) VALUES (:id_insert, :libelle_insert)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_insert', $idGroupeUtilisateur);
        $stmt->bindParam(':libelle_insert', $libelle);

        try {
            $stmt->execute();
            // Enregistrer l'action de supervision
            $this->serviceSupervision->enregistrerAction(
                $this->currentUserLogin,
                'CREATION_GROUPE_UTILISATEUR',
                new DateTime(),
                $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'GroupeUtilisateur',
                $idGroupeUtilisateur, // L'ID est la chaîne fournie
                ['id_groupe_utilisateur' => $idGroupeUtilisateur, 'libelle' => $libelle]
            );
            return $idGroupeUtilisateur; // Retourner l'ID VARCHAR fourni
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Modifie le libellé d'un groupe d'utilisateurs existant.
     * L'ID est une chaîne (VARCHAR).
     */
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle): bool
    {
        // Vérifier d'abord si le groupe existe
        $this->recupererGroupeUtilisateurParId($idGroupeUtilisateur); // S'assure que l'élément existe ou lance une exception

        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du groupe ne peut être vide.");
        }

        // Vérifier si le nouveau libellé est déjà utilisé par un AUTRE groupe
        $sqlCheck = "SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE lib_groupe_utilisateur = :libelle_check AND id_groupe_utilisateur != :id_current";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(':libelle_check', $libelle);
        $stmtCheck->bindParam(':id_current', $idGroupeUtilisateur);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un autre groupe avec ce libellé existe déjà.");
        }

        // Mise à jour du libellé uniquement
        $sql = "UPDATE groupe_utilisateur SET lib_groupe_utilisateur = :libelle_update WHERE id_groupe_utilisateur = :id_update";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle_update', $libelle);
        $stmt->bindParam(':id_update', $idGroupeUtilisateur);

        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction(
                    $this->currentUserLogin,
                    'MODIFICATION_GROUPE_UTILISATEUR',
                    new DateTime(),
                    $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                    'GroupeUtilisateur',
                    $idGroupeUtilisateur,
                    ['libelle' => $libelle]
                );
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la modification du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Supprime un groupe d'utilisateurs.
     * L'ID est une chaîne (VARCHAR).
     */
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool
    {
        // Vérifier d'abord si le groupe existe
        $this->recupererGroupeUtilisateurParId($idGroupeUtilisateur);

        // Vérifier si le groupe est assigné à des utilisateurs
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_groupe_utilisateur = :id_groupe");
        // Assurez-vous que la liaison se fait correctement pour un VARCHAR. PDO le gère souvent bien.
        $stmtCheck->bindParam(':id_groupe', $idGroupeUtilisateur);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe car il est assigné à des utilisateurs.");
        }

        $stmt = $this->db->prepare("DELETE FROM groupe_utilisateur WHERE id_groupe_utilisateur = :id_delete");
        $stmt->bindParam(':id_delete', $idGroupeUtilisateur);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction(
                    $this->currentUserLogin,
                    'SUPPRESSION_GROUPE_UTILISATEUR',
                    new DateTime(),
                    $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                    'GroupeUtilisateur',
                    $idGroupeUtilisateur,
                    []
                );
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Récupère un groupe d'utilisateurs par son ID (chaîne VARCHAR).
     */
    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array
    {
        // Sélectionner uniquement les colonnes existantes
        $stmt = $this->db->prepare("SELECT id_groupe_utilisateur, lib_groupe_utilisateur FROM groupe_utilisateur WHERE id_groupe_utilisateur = :id");
        $stmt->bindParam(':id', $idGroupeUtilisateur); // PDO::PARAM_STR est implicite ou peut être ajouté
        $stmt->execute();
        $groupe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe d'utilisateurs non trouvé avec l'ID: " . $idGroupeUtilisateur);
        }
        return $groupe;
    }

    /**
     * Supprimez cette méthode si la colonne code_groupe_utilisateur n'existe plus.
     * Si elle existe sous un autre nom ou si une recherche par un "code" est toujours nécessaire
     * sur une autre colonne, adaptez cette méthode.
     * Actuellement, elle va générer une erreur SQL si la colonne code_groupe_utilisateur est absente.
     */
    // public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array
    // {
    //     // SI LA COLONNE N'EXISTE PLUS, CETTE MÉTHODE EST INVALIDE
    //     $stmt = $this->db->prepare("SELECT id_groupe_utilisateur, lib_groupe_utilisateur FROM groupe_utilisateur WHERE code_groupe_utilisateur = :code");
    //     $stmt->bindParam(':code', $codeGroupe);
    //     $stmt->execute();
    //     $groupe = $stmt->fetch(PDO::FETCH_ASSOC);
    //     if (!$groupe) {
    //         throw new ElementNonTrouveException("Groupe d'utilisateurs non trouvé avec le code: " . $codeGroupe);
    //     }
    //     return $groupe;
    // }

    public function listerGroupesUtilisateur(): array
    {
        // Sélectionner uniquement les colonnes existantes
        $stmt = $this->db->query("SELECT id_groupe_utilisateur, lib_groupe_utilisateur FROM groupe_utilisateur ORDER BY lib_groupe_utilisateur ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ... (Le reste de vos méthodes pour TypeUtilisateur, NiveauAcces, Traitement, etc.)
    // Vous devrez vérifier si des ajustements similaires sont nécessaires pour ces autres entités
    // si leurs tables respectives ont également changé.
    // Par exemple, les méthodes recuperer...ParCode() pourraient être concernées.

    // Continuez avec les autres méthodes ici...
    // Par exemple, la méthode `utilisateurPossedePermission` utilise `recupererTraitementParCode`.
    // Assurez-vous que la table `traitement` a bien une colonne `code_traitement`.
    // La méthode `groupePossedePermission` utilise également `recupererTraitementParCode`.

    // COLLER LE RESTE DE VOS MÉTHODES DE LA CLASSE ICI, EN VÉRIFIANT LEUR LOGIQUE PAR RAPPORT À LA STRUCTURE DE LA DB
    // CI-DESSOUS UN EXEMPLE POUR MONTRER OÙ REPRENDRE VOTRE CODE ORIGINAL:

    public function creerTypeUtilisateur(string $libelle, ?string $description, ?string $codeType = null): int
    {
        // ... (votre code existant, vérifiez si `description_type_utilisateur` et `code_type_utilisateur` existent)
        // Si la table type_utilisateur a aussi changé, adaptez cette méthode.
        // Par exemple, si id_type_utilisateur est un VARCHAR, le retour devrait être string et lastInsertId() ne serait pas utilisé.
        // Cette section est un placeholder pour votre code existant.
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du type d'utilisateur ne peut être vide.");
        }
        if ($codeType !== null && empty(trim($codeType))) {
            $codeType = null;
        }

        $sqlCheck = "SELECT id_type_utilisateur FROM type_utilisateur WHERE lib_type_utilisateur = :libelle";
        $paramsCheck = [':libelle' => $libelle];
        if ($codeType !== null) {
            $sqlCheck .= " OR code_type_utilisateur = :code"; // Vérifiez si cette colonne existe
            $paramsCheck[':code'] = $codeType;
        }
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un type d'utilisateur avec ce libellé ou code existe déjà.");
        }

        $sql = "INSERT INTO type_utilisateur (lib_type_utilisateur, description_type_utilisateur, code_type_utilisateur) VALUES (:libelle, :description, :code)"; // Vérifiez ces colonnes
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description); // Vérifiez si cette colonne existe
        $stmt->bindParam(':code', $codeType); // Vérifiez si cette colonne existe
        try {
            $stmt->execute();
            $id = (int)$this->db->lastInsertId(); // Si id_type_utilisateur est un INT AI
            $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'CREATION_TYPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'TypeUtilisateur', (string)$id, ['libelle' => $libelle, 'code' => $codeType]);
            return $id;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du type d'utilisateur: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
    // ... Intégrez et vérifiez toutes vos autres méthodes ici
    // (modifierTypeUtilisateur, supprimerTypeUtilisateur, recupererTypeUtilisateurParId, recupererTypeUtilisateurParCode, listerTypesUtilisateur,
    // creerNiveauAcces, modifierNiveauAcces, supprimerNiveauAcces, recupererNiveauAccesParId, recupererNiveauAccesParCode, listerNiveauxAcces,
    // creerTraitement, modifierTraitement, supprimerTraitement, recupererTraitementParId, recupererTraitementParCode, listerTraitements,
    // attribuerPermissionGroupe, retirerPermissionGroupe, recupererPermissionsPourGroupe, recupererGroupesPourPermission,
    // utilisateurPossedePermission, groupePossedePermission, getPermissionsPourUtilisateur)

    // Exemple pour recupererGroupeUtilisateurParCode, qui devrait être supprimé si la colonne n'existe plus :
    /*
    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array
    {
        // Cette méthode est à supprimer si 'code_groupe_utilisateur' n'existe pas.
        // Si vous l'avez commentée plus haut, c'est parfait.
        // Sinon, le code ci-dessous provoquerait une erreur SQL.
        // $stmt = $this->db->prepare("SELECT * FROM groupe_utilisateur WHERE code_groupe_utilisateur = :code");
        // $stmt->bindParam(':code', $codeGroupe);
        // $stmt->execute();
        // $groupe = $stmt->fetch(PDO::FETCH_ASSOC);
        // if (!$groupe) {
        //     throw new ElementNonTrouveException("Groupe d'utilisateurs non trouvé avec le code: " . $codeGroupe);
        // }
        // return $groupe;
        throw new \LogicException("La méthode recupererGroupeUtilisateurParCode n'est plus valide car la colonne code_groupe_utilisateur n'existe pas/plus.");
    }
    */

    // ASSUREZ-VOUS D'INCLURE TOUTES LES AUTRES MÉTHODES DE VOTRE CLASSE ICI,
    // EN APPLIQUANT DES MODIFICATIONS SIMILAIRES SI NÉCESSAIRE.
    // Pour l'instant, je vais ajouter des placeholders pour les méthodes restantes mentionnées dans le fichier original que vous avez fourni.
    // Vous devrez les vérifier et les adapter.

    public function modifierTypeUtilisateur(int $idType, string $libelle, ?string $description, ?string $codeType = null): bool { /* ... à vérifier ... */ return false; }
    public function supprimerTypeUtilisateur(int $idType): bool { /* ... à vérifier ... */ return false; }
    public function recupererTypeUtilisateurParId(int $idType): ?array { /* ... à vérifier ... */ return null; }
    public function recupererTypeUtilisateurParCode(string $codeType): ?array { /* ... à vérifier, probablement à supprimer ou modifier si pas de code_type_utilisateur ... */ return null; }
    public function listerTypesUtilisateur(): array { /* ... à vérifier ... */ return []; }

    public function creerNiveauAcces(string $libelle, ?string $description, ?string $codeNiveauAcces = null): int { /* ... à vérifier ... */ return 0; }
    public function modifierNiveauAcces(int $idNiveau, string $libelle, ?string $description, ?string $codeNiveauAcces = null): bool { /* ... à vérifier ... */ return false; }
    public function supprimerNiveauAcces(int $idNiveau): bool { /* ... à vérifier ... */ return false; }
    public function recupererNiveauAccesParId(int $idNiveau): ?array { /* ... à vérifier ... */ return null; }
    public function recupererNiveauAccesParCode(string $codeNiveauAcces): ?array { /* ... à vérifier, probablement à supprimer ou modifier si pas de code_niveau_acces ... */ return null; }
    public function listerNiveauxAcces(): array { /* ... à vérifier ... */ return []; }

    public function creerTraitement(string $libelleTraitement, string $codeTraitement): int { /* ... à vérifier ... */ return 0; }
    public function modifierTraitement(int $idTraitement, string $libelleTraitement, string $codeTraitement): bool { /* ... à vérifier ... */ return false; }
    public function supprimerTraitement(int $idTraitement): bool { /* ... à vérifier ... */ return false; }
    public function recupererTraitementParId(int $idTraitement): ?array { /* ... à vérifier ... */ return null; }
    public function recupererTraitementParCode(string $codeTraitement): ?array { /* ... à vérifier ... */ return null; } // Cette méthode semble valide si la table traitement a un code_traitement
    public function listerTraitements(): array { /* ... à vérifier ... */ return []; }

    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, int $idTraitement): bool { /* idGroupeUtilisateur doit être string ici */ return false; } // Changé le premier paramètre en string
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, int $idTraitement): bool { /* idGroupeUtilisateur doit être string ici */ return false; } // Changé le premier paramètre en string
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array { /* idGroupeUtilisateur doit être string ici */ return []; } // Changé le premier paramètre en string

    public function recupererGroupesPourPermission(int $idTraitement): array { /* ... à vérifier ... */ return []; }

    public function utilisateurPossedePermission(string $numeroUtilisateur, string $codePermission): bool
    {
        $stmtUser = $this->db->prepare("SELECT id_groupe_utilisateur FROM utilisateur WHERE numero_utilisateur = :num_user");
        $stmtUser->bindParam(':num_user', $numeroUtilisateur);
        $stmtUser->execute();
        $idGroupe = $stmtUser->fetchColumn(); // $idGroupe sera un VARCHAR ici

        if ($idGroupe === false) {
            throw new ElementNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if ($idGroupe === null) { // Peut arriver si la colonne est nullable et pas de groupe assigné
            return false;
        }
        // groupePossedePermission attend un string pour l'ID du groupe désormais
        return $this->groupePossedePermission((string)$idGroupe, $codePermission);
    }

    public function groupePossedePermission(string $idGroupeUtilisateur, string $codePermission): bool // Changé le premier paramètre en string
    {
        $traitement = $this->recupererTraitementParCode($codePermission); // S'assurer que recupererTraitementParCode est correct
        if (!$traitement) {
            // Si un code permission invalide ne doit pas lever d'exception mais retourner false :
            // return false;
            throw new ElementNonTrouveException("Permission (traitement) non trouvée avec le code: " . $codePermission);
        }
        $idTraitement = $traitement['id_trait'];

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rattacher WHERE id_groupe_utilisateur = :id_groupe AND id_trait = :id_trait");
        $stmt->bindParam(':id_groupe', $idGroupeUtilisateur); // id_groupe_utilisateur est maintenant une chaîne
        $stmt->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getPermissionsPourUtilisateur(string $numeroUtilisateur): array
    {
        $stmtUser = $this->db->prepare("SELECT id_groupe_utilisateur FROM utilisateur WHERE numero_utilisateur = :num_user");
        $stmtUser->bindParam(':num_user', $numeroUtilisateur);
        $stmtUser->execute();
        $idGroupe = $stmtUser->fetchColumn(); // $idGroupe sera un VARCHAR

        if ($idGroupe === false) {
            throw new ElementNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if ($idGroupe === null) {
            return [];
        }

        // recupererPermissionsPourGroupe attend un string pour l'ID du groupe
        $permissionsGroupe = $this->recupererPermissionsPourGroupe((string)$idGroupe);
        $codesPermissions = [];
        foreach ($permissionsGroupe as $permission) {
            if (isset($permission['code_traitement'])) { // Vérifier que la clé existe
                $codesPermissions[] = $permission['code_traitement'];
            }
        }
        return $codesPermissions;
    }

}