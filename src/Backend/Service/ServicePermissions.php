<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\Rattacher;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Service\Interface\TransitionRoleServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;

class ServicePermissions implements PermissionsServiceInterface
{
    private PDO $pdo;
    private Utilisateur $utilisateurModel;
    private Rattacher $rattacherModel;
    private TransitionRoleServiceInterface $transitionRoleService;

    public function __construct(
        PDO $pdo,
        Utilisateur $utilisateurModel,
        Rattacher $rattacherModel,
        TransitionRoleServiceInterface $transitionRoleService
    ) {
        $this->pdo = $pdo;
        $this->utilisateurModel = $utilisateurModel;
        $this->rattacherModel = $rattacherModel;
        $this->transitionRoleService = $transitionRoleService;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsPourSession(string $numeroUtilisateur): array
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$utilisateur) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }

        // 1. Permissions de base du groupe
        $permissionsDeBase = $this->rattacherModel->trouverParCritere(
            ['id_groupe_utilisateur' => $utilisateur['id_groupe_utilisateur']]
        );
        $permissions = array_column($permissionsDeBase, 'id_traitement');

        // 2. Permissions déléguées
        $permissionsDeleguees = $this->transitionRoleService->getPermissionsDeleguees($numeroUtilisateur);

        // 3. Fusion et déduplication
        $permissionsEffectives = array_unique(array_merge($permissions, $permissionsDeleguees));

        return $permissionsEffectives;
    }

    /**
     * @inheritdoc
     */
    public function utilisateurPossedePermission(string $numeroUtilisateur, string $idTraitement): bool
    {
        if (isset($_SESSION['user_permissions']) && $_SESSION['user_id'] === $numeroUtilisateur) {
            return in_array($idTraitement, $_SESSION['user_permissions']);
        }

        $permissions = $this->getPermissionsPourSession($numeroUtilisateur);
        return in_array($idTraitement, $permissions);
    }

    /**
     * @inheritdoc
     */
    public function synchroniserPermissionsPourSessionsActives(string $numeroUtilisateur): void
    {
        $sql = "SELECT session_id, session_data FROM sessions WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $numeroUtilisateur]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sessions)) {
            return;
        }

        $nouvellesPermissions = $this->getPermissionsPourSession($numeroUtilisateur);

        $updateSql = "UPDATE sessions SET session_data = :session_data WHERE session_id = :session_id";
        $updateStmt = $this->pdo->prepare($updateSql);

        foreach ($sessions as $session) {
            session_id($session['session_id']);
            @session_start();

            $_SESSION['user_permissions'] = $nouvellesPermissions;
            $nouvellesDonneesSession = session_encode();

            session_write_close();

            $updateStmt->execute([
                ':session_data' => $nouvellesDonneesSession,
                ':session_id' => $session['session_id']
            ]);
        }
        // Restaurer la session courante
        session_id($_COOKIE['PHPSESSID'] ?? '');
        @session_start();
    }
}