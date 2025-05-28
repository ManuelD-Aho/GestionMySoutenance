<?php

namespace App\Backend\Service\Permissions;

use PDO;
use PDOException;
use DateTime;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\PermissionException;
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

    public function creerGroupeUtilisateur(string $libelle, ?string $description, ?string $codeGroupe = null): int
    {
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du groupe ne peut être vide.");
        }
        if ($codeGroupe !== null && empty(trim($codeGroupe))) {
            $codeGroupe = null;
        }

        $sqlCheck = "SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE lib_groupe_utilisateur = :libelle";
        $paramsCheck = [':libelle' => $libelle];
        if ($codeGroupe !== null) {
            $sqlCheck .= " OR code_groupe_utilisateur = :code";
            $paramsCheck[':code'] = $codeGroupe;
        }
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un groupe avec ce libellé ou code existe déjà.");
        }

        $sql = "INSERT INTO groupe_utilisateur (lib_groupe_utilisateur, description_groupe_utilisateur, code_groupe_utilisateur) VALUES (:libelle, :description, :code)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeGroupe);

        try {
            $stmt->execute();
            $id = (int)$this->db->lastInsertId();
            $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'CREATION_GROUPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'GroupeUtilisateur', (string)$id, ['libelle' => $libelle, 'code' => $codeGroupe]);
            return $id;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function modifierGroupeUtilisateur(int $idGroupe, string $libelle, ?string $description, ?string $codeGroupe = null): bool
    {
        $this->recupererGroupeUtilisateurParId($idGroupe);
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du groupe ne peut être vide.");
        }
        if ($codeGroupe !== null && empty(trim($codeGroupe))) {
            $codeGroupe = null;
        }

        $sqlCheck = "SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE (lib_groupe_utilisateur = :libelle OR (:code_is_not_null AND code_groupe_utilisateur = :code)) AND id_groupe_utilisateur != :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(':libelle', $libelle);
        $stmtCheck->bindValue(':code_is_not_null', $codeGroupe !== null);
        $stmtCheck->bindParam(':code', $codeGroupe);
        $stmtCheck->bindParam(':id', $idGroupe, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un autre groupe avec ce libellé ou code existe déjà.");
        }

        $sql = "UPDATE groupe_utilisateur SET lib_groupe_utilisateur = :libelle, description_groupe_utilisateur = :description, code_groupe_utilisateur = :code WHERE id_groupe_utilisateur = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeGroupe);
        $stmt->bindParam(':id', $idGroupe, PDO::PARAM_INT);

        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'MODIFICATION_GROUPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'GroupeUtilisateur', (string)$idGroupe, ['libelle' => $libelle, 'code' => $codeGroupe]);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la modification du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supprimerGroupeUtilisateur(int $idGroupe): bool
    {
        $this->recupererGroupeUtilisateurParId($idGroupe);
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_groupe_utilisateur = :id_groupe");
        $stmtCheck->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe car il est assigné à des utilisateurs.");
        }

        $stmt = $this->db->prepare("DELETE FROM groupe_utilisateur WHERE id_groupe_utilisateur = :id");
        $stmt->bindParam(':id', $idGroupe, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'SUPPRESSION_GROUPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'GroupeUtilisateur', (string)$idGroupe, []);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du groupe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function recupererGroupeUtilisateurParId(int $idGroupe): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM groupe_utilisateur WHERE id_groupe_utilisateur = :id");
        $stmt->bindParam(':id', $idGroupe, PDO::PARAM_INT);
        $stmt->execute();
        $groupe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe d'utilisateurs non trouvé avec l'ID: " . $idGroupe);
        }
        return $groupe;
    }

    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM groupe_utilisateur WHERE code_groupe_utilisateur = :code");
        $stmt->bindParam(':code', $codeGroupe);
        $stmt->execute();
        $groupe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe d'utilisateurs non trouvé avec le code: " . $codeGroupe);
        }
        return $groupe;
    }

    public function listerGroupesUtilisateur(): array
    {
        $stmt = $this->db->query("SELECT * FROM groupe_utilisateur ORDER BY lib_groupe_utilisateur ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creerTypeUtilisateur(string $libelle, ?string $description, ?string $codeType = null): int
    {
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du type d'utilisateur ne peut être vide.");
        }
        if ($codeType !== null && empty(trim($codeType))) {
            $codeType = null;
        }

        $sqlCheck = "SELECT id_type_utilisateur FROM type_utilisateur WHERE lib_type_utilisateur = :libelle";
        $paramsCheck = [':libelle' => $libelle];
        if ($codeType !== null) {
            $sqlCheck .= " OR code_type_utilisateur = :code";
            $paramsCheck[':code'] = $codeType;
        }
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un type d'utilisateur avec ce libellé ou code existe déjà.");
        }

        $sql = "INSERT INTO type_utilisateur (lib_type_utilisateur, description_type_utilisateur, code_type_utilisateur) VALUES (:libelle, :description, :code)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeType);
        try {
            $stmt->execute();
            $id = (int)$this->db->lastInsertId();
            $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'CREATION_TYPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'TypeUtilisateur', (string)$id, ['libelle' => $libelle, 'code' => $codeType]);
            return $id;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du type d'utilisateur: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function modifierTypeUtilisateur(int $idType, string $libelle, ?string $description, ?string $codeType = null): bool
    {
        $this->recupererTypeUtilisateurParId($idType);
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du type d'utilisateur ne peut être vide.");
        }
        if ($codeType !== null && empty(trim($codeType))) {
            $codeType = null;
        }

        $sqlCheck = "SELECT id_type_utilisateur FROM type_utilisateur WHERE (lib_type_utilisateur = :libelle OR (:code_is_not_null AND code_type_utilisateur = :code)) AND id_type_utilisateur != :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(':libelle', $libelle);
        $stmtCheck->bindValue(':code_is_not_null', $codeType !== null);
        $stmtCheck->bindParam(':code', $codeType);
        $stmtCheck->bindParam(':id', $idType, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un autre type d'utilisateur avec ce libellé ou code existe déjà.");
        }

        $sql = "UPDATE type_utilisateur SET lib_type_utilisateur = :libelle, description_type_utilisateur = :description, code_type_utilisateur = :code WHERE id_type_utilisateur = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeType);
        $stmt->bindParam(':id', $idType, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'MODIFICATION_TYPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'TypeUtilisateur', (string)$idType, ['libelle' => $libelle, 'code' => $codeType]);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la modification du type d'utilisateur: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supprimerTypeUtilisateur(int $idType): bool
    {
        $this->recupererTypeUtilisateurParId($idType);
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_type_utilisateur = :id_type");
        $stmtCheck->bindParam(':id_type', $idType, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le type car il est assigné à des utilisateurs.");
        }

        $stmt = $this->db->prepare("DELETE FROM type_utilisateur WHERE id_type_utilisateur = :id");
        $stmt->bindParam(':id', $idType, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'SUPPRESSION_TYPE_UTILISATEUR', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'TypeUtilisateur', (string)$idType, []);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du type d'utilisateur: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function recupererTypeUtilisateurParId(int $idType): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM type_utilisateur WHERE id_type_utilisateur = :id");
        $stmt->bindParam(':id', $idType, PDO::PARAM_INT);
        $stmt->execute();
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$type) {
            throw new ElementNonTrouveException("Type d'utilisateur non trouvé avec l'ID: " . $idType);
        }
        return $type;
    }

    public function recupererTypeUtilisateurParCode(string $codeType): ?array {
        $stmt = $this->db->prepare("SELECT * FROM type_utilisateur WHERE code_type_utilisateur = :code");
        $stmt->bindParam(':code', $codeType);
        $stmt->execute();
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$type) {
            throw new ElementNonTrouveException("Type d'utilisateur non trouvé avec le code: " . $codeType);
        }
        return $type;
    }

    public function listerTypesUtilisateur(): array
    {
        $stmt = $this->db->query("SELECT * FROM type_utilisateur ORDER BY lib_type_utilisateur ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creerNiveauAcces(string $libelle, ?string $description, ?string $codeNiveauAcces = null): int
    {
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du niveau d'accès ne peut être vide.");
        }
        if ($codeNiveauAcces !== null && empty(trim($codeNiveauAcces))) {
            $codeNiveauAcces = null;
        }

        $sqlCheck = "SELECT id_niveau_acces_donne FROM niveau_acces_donne WHERE lib_niveau_acces_donne = :libelle";
        $paramsCheck = [':libelle' => $libelle];
        if ($codeNiveauAcces !== null) {
            $sqlCheck .= " OR code_niveau_acces = :code";
            $paramsCheck[':code'] = $codeNiveauAcces;
        }
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un niveau d'accès avec ce libellé ou code existe déjà.");
        }

        $sql = "INSERT INTO niveau_acces_donne (lib_niveau_acces_donne, description_niveau_acces_donne, code_niveau_acces) VALUES (:libelle, :description, :code)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeNiveauAcces);
        try {
            $stmt->execute();
            $id = (int)$this->db->lastInsertId();
            $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'CREATION_NIVEAU_ACCES', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'NiveauAccesDonne', (string)$id, ['libelle' => $libelle, 'code' => $codeNiveauAcces]);
            return $id;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du niveau d'accès: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function modifierNiveauAcces(int $idNiveau, string $libelle, ?string $description, ?string $codeNiveauAcces = null): bool
    {
        $this->recupererNiveauAccesParId($idNiveau);
        if (empty(trim($libelle))) {
            throw new ValidationException("Le libellé du niveau d'accès ne peut être vide.");
        }
        if ($codeNiveauAcces !== null && empty(trim($codeNiveauAcces))) {
            $codeNiveauAcces = null;
        }

        $sqlCheck = "SELECT id_niveau_acces_donne FROM niveau_acces_donne WHERE (lib_niveau_acces_donne = :libelle OR (:code_is_not_null AND code_niveau_acces = :code)) AND id_niveau_acces_donne != :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(':libelle', $libelle);
        $stmtCheck->bindValue(':code_is_not_null', $codeNiveauAcces !== null);
        $stmtCheck->bindParam(':code', $codeNiveauAcces);
        $stmtCheck->bindParam(':id', $idNiveau, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un autre niveau d'accès avec ce libellé ou code existe déjà.");
        }

        $sql = "UPDATE niveau_acces_donne SET lib_niveau_acces_donne = :libelle, description_niveau_acces_donne = :description, code_niveau_acces = :code WHERE id_niveau_acces_donne = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $codeNiveauAcces);
        $stmt->bindParam(':id', $idNiveau, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'MODIFICATION_NIVEAU_ACCES', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'NiveauAccesDonne', (string)$idNiveau, ['libelle' => $libelle, 'code' => $codeNiveauAcces]);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la modification du niveau d'accès: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supprimerNiveauAcces(int $idNiveau): bool
    {
        $this->recupererNiveauAccesParId($idNiveau);
        // Vérifier si utilisé dans `rattacher` ou autre table si applicable.
        // Pour l'instant, on suppose qu'il n'y a pas de dépendance directe bloquante non gérée par FK.
        // Si `niveau_acces_donne` est lié à `rattacher` (ce qui n'est pas le cas dans le schéma typique), il faudrait vérifier.
        // Le schéma fourni n'indique pas de lien direct de `niveau_acces_donne` vers `rattacher`.
        // Il est lié à `utilisateur` via `id_niveau_acces_donne`.
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_niveau_acces_donne = :id_niveau");
        $stmtCheck->bindParam(':id_niveau', $idNiveau, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le niveau d'accès car il est assigné à des utilisateurs.");
        }

        $stmt = $this->db->prepare("DELETE FROM niveau_acces_donne WHERE id_niveau_acces_donne = :id");
        $stmt->bindParam(':id', $idNiveau, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'SUPPRESSION_NIVEAU_ACCES', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'NiveauAccesDonne', (string)$idNiveau, []);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du niveau d'accès: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function recupererNiveauAccesParId(int $idNiveau): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM niveau_acces_donne WHERE id_niveau_acces_donne = :id");
        $stmt->bindParam(':id', $idNiveau, PDO::PARAM_INT);
        $stmt->execute();
        $niveau = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$niveau) {
            throw new ElementNonTrouveException("Niveau d'accès non trouvé avec l'ID: " . $idNiveau);
        }
        return $niveau;
    }

    public function recupererNiveauAccesParCode(string $codeNiveauAcces): ?array {
        $stmt = $this->db->prepare("SELECT * FROM niveau_acces_donne WHERE code_niveau_acces = :code");
        $stmt->bindParam(':code', $codeNiveauAcces);
        $stmt->execute();
        $niveau = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$niveau) {
            throw new ElementNonTrouveException("Niveau d'accès non trouvé avec le code: " . $codeNiveauAcces);
        }
        return $niveau;
    }

    public function listerNiveauxAcces(): array
    {
        $stmt = $this->db->query("SELECT * FROM niveau_acces_donne ORDER BY lib_niveau_acces_donne ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creerTraitement(string $libelleTraitement, string $codeTraitement): int
    {
        if (empty(trim($libelleTraitement)) || empty(trim($codeTraitement))) {
            throw new ValidationException("Le libellé et le code du traitement ne peuvent être vides.");
        }
        if (!preg_match('/^[A-Z0-9_]+$/', $codeTraitement)) {
            throw new ValidationException("Le code de traitement doit être en majuscules alphanumériques avec underscores.");
        }

        $stmtCheck = $this->db->prepare("SELECT id_trait FROM traitement WHERE lib_trait = :libelle OR code_traitement = :code");
        $stmtCheck->bindParam(':libelle', $libelleTraitement);
        $stmtCheck->bindParam(':code', $codeTraitement);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un traitement avec ce libellé ou code existe déjà.");
        }

        $sql = "INSERT INTO traitement (lib_trait, code_traitement) VALUES (:libelle, :code)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelleTraitement);
        $stmt->bindParam(':code', $codeTraitement);
        try {
            $stmt->execute();
            $id = (int)$this->db->lastInsertId();
            $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'CREATION_TRAITEMENT', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'Traitement', (string)$id, ['libelle' => $libelleTraitement, 'code' => $codeTraitement]);
            return $id;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du traitement: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function modifierTraitement(int $idTraitement, string $libelleTraitement, string $codeTraitement): bool
    {
        $this->recupererTraitementParId($idTraitement);
        if (empty(trim($libelleTraitement)) || empty(trim($codeTraitement))) {
            throw new ValidationException("Le libellé et le code du traitement ne peuvent être vides.");
        }
        if (!preg_match('/^[A-Z0-9_]+$/', $codeTraitement)) {
            throw new ValidationException("Le code de traitement doit être en majuscules alphanumériques avec underscores.");
        }

        $stmtCheck = $this->db->prepare("SELECT id_trait FROM traitement WHERE (lib_trait = :libelle OR code_traitement = :code) AND id_trait != :id");
        $stmtCheck->bindParam(':libelle', $libelleTraitement);
        $stmtCheck->bindParam(':code', $codeTraitement);
        $stmtCheck->bindParam(':id', $idTraitement, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new DoublonException("Un autre traitement avec ce libellé ou code existe déjà.");
        }

        $sql = "UPDATE traitement SET lib_trait = :libelle, code_traitement = :code WHERE id_trait = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':libelle', $libelleTraitement);
        $stmt->bindParam(':code', $codeTraitement);
        $stmt->bindParam(':id', $idTraitement, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'MODIFICATION_TRAITEMENT', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'Traitement', (string)$idTraitement, ['libelle' => $libelleTraitement, 'code' => $codeTraitement]);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la modification du traitement: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supprimerTraitement(int $idTraitement): bool
    {
        $this->recupererTraitementParId($idTraitement);
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM rattacher WHERE id_trait = :id_trait");
        $stmtCheck->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le traitement car il est rattaché à des groupes.");
        }

        $stmt = $this->db->prepare("DELETE FROM traitement WHERE id_trait = :id");
        $stmt->bindParam(':id', $idTraitement, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'SUPPRESSION_TRAITEMENT', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'Traitement', (string)$idTraitement, []);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du traitement: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function recupererTraitementParId(int $idTraitement): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM traitement WHERE id_trait = :id");
        $stmt->bindParam(':id', $idTraitement, PDO::PARAM_INT);
        $stmt->execute();
        $traitement = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$traitement) {
            throw new ElementNonTrouveException("Traitement non trouvé avec l'ID: " . $idTraitement);
        }
        return $traitement;
    }

    public function recupererTraitementParCode(string $codeTraitement): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM traitement WHERE code_traitement = :code");
        $stmt->bindParam(':code', $codeTraitement);
        $stmt->execute();
        $traitement = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$traitement) {
            throw new ElementNonTrouveException("Traitement non trouvé avec le code: " . $codeTraitement);
        }
        return $traitement;
    }

    public function listerTraitements(): array
    {
        $stmt = $this->db->query("SELECT * FROM traitement ORDER BY lib_trait ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function attribuerPermissionGroupe(int $idGroupe, int $idTraitement): bool
    {
        $this->recupererGroupeUtilisateurParId($idGroupe);
        $this->recupererTraitementParId($idTraitement);

        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM rattacher WHERE id_groupe_utilisateur = :id_groupe AND id_trait = :id_trait");
        $stmtCheck->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
        $stmtCheck->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            throw new DoublonException("Cette permission est déjà attribuée à ce groupe.");
        }

        $sql = "INSERT INTO rattacher (id_groupe_utilisateur, id_trait) VALUES (:id_groupe, :id_trait)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
        $stmt->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'ATTRIBUTION_PERMISSION_GROUPE', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'Rattachement', $idGroupe . '-' . $idTraitement, ['id_groupe' => $idGroupe, 'id_traitement' => $idTraitement]);
            }
            return $success;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de l'attribution de la permission: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function retirerPermissionGroupe(int $idGroupe, int $idTraitement): bool
    {
        $this->recupererGroupeUtilisateurParId($idGroupe);
        $this->recupererTraitementParId($idTraitement);

        $sql = "DELETE FROM rattacher WHERE id_groupe_utilisateur = :id_groupe AND id_trait = :id_trait";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
        $stmt->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                $this->serviceSupervision->enregistrerAction($this->currentUserLogin, 'RETRAIT_PERMISSION_GROUPE', new DateTime(), $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 'Rattachement', $idGroupe . '-' . $idTraitement, ['id_groupe' => $idGroupe, 'id_traitement' => $idTraitement]);
            } elseif (!$success) {
                return false;
            } elseif ($stmt->rowCount() === 0) {
                throw new ElementNonTrouveException("Le rattachement de permission n'existait pas pour ce groupe et traitement.");
            }
            return true;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du retrait de la permission: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function recupererPermissionsPourGroupe(int $idGroupe): array
    {
        $this->recupererGroupeUtilisateurParId($idGroupe);
        $sql = "SELECT t.* FROM traitement t JOIN rattacher r ON t.id_trait = r.id_trait WHERE r.id_groupe_utilisateur = :id_groupe ORDER BY t.lib_trait ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recupererGroupesPourPermission(int $idTraitement): array
    {
        $this->recupererTraitementParId($idTraitement);
        $sql = "SELECT g.* FROM groupe_utilisateur g JOIN rattacher r ON g.id_groupe_utilisateur = r.id_groupe_utilisateur WHERE r.id_trait = :id_trait ORDER BY g.lib_groupe_utilisateur ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function utilisateurPossedePermission(string $numeroUtilisateur, string $codePermission): bool
    {
        $stmtUser = $this->db->prepare("SELECT id_groupe_utilisateur FROM utilisateur WHERE numero_utilisateur = :num_user");
        $stmtUser->bindParam(':num_user', $numeroUtilisateur);
        $stmtUser->execute();
        $idGroupe = $stmtUser->fetchColumn();

        if ($idGroupe === false) {
            throw new ElementNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if ($idGroupe === null) {
            return false;
        }
        return $this->groupePossedePermission((int)$idGroupe, $codePermission);
    }

    public function groupePossedePermission(int $idGroupeUtilisateur, string $codePermission): bool
    {
        $traitement = $this->recupererTraitementParCode($codePermission);
        if (!$traitement) {
            throw new ElementNonTrouveException("Permission (traitement) non trouvée avec le code: " . $codePermission);
        }
        $idTraitement = $traitement['id_trait'];

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rattacher WHERE id_groupe_utilisateur = :id_groupe AND id_trait = :id_trait");
        $stmt->bindParam(':id_groupe', $idGroupeUtilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':id_trait', $idTraitement, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getPermissionsPourUtilisateur(string $numeroUtilisateur): array
    {
        $stmtUser = $this->db->prepare("SELECT id_groupe_utilisateur FROM utilisateur WHERE numero_utilisateur = :num_user");
        $stmtUser->bindParam(':num_user', $numeroUtilisateur);
        $stmtUser->execute();
        $idGroupe = $stmtUser->fetchColumn();

        if ($idGroupe === false) {
            throw new ElementNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if ($idGroupe === null) {
            return [];
        }

        $permissionsGroupe = $this->recupererPermissionsPourGroupe((int)$idGroupe);
        $codesPermissions = [];
        foreach ($permissionsGroupe as $permission) {
            $codesPermissions[] = $permission['code_traitement'];
        }
        return $codesPermissions;
    }
}