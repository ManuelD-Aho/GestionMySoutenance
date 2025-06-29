<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Enregistrer;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;

class ServiceAudit implements AuditServiceInterface
{
    private PDO $pdo;
    private Enregistrer $enregistrerModel;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(PDO $pdo, Enregistrer $enregistrerModel, IdentifiantGeneratorInterface $identifiantGenerator)
    {
        $this->pdo = $pdo;
        $this->enregistrerModel = $enregistrerModel;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    /**
     * @inheritdoc
     */
    public function enregistrerAction(
        string $numeroUtilisateur,
        string $codeAction,
        ?string $idEntiteConcernee = null,
        ?string $typeEntiteConcernee = null,
        array $detailsAction = []
    ): void {
        $donnees = [
            'id_enregistrement' => $this->identifiantGenerator->generer('AUDIT'),
            'numero_utilisateur' => $numeroUtilisateur,
            'id_action' => $codeAction,
            'date_action' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            'adresse_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'id_entite_concernee' => $idEntiteConcernee,
            'type_entite_concernee' => $typeEntiteConcernee,
            'details_action' => json_encode($detailsAction, JSON_UNESCAPED_UNICODE),
            'session_id_utilisateur' => session_id() ?: null,
        ];

        $this->enregistrerModel->creer($donnees);
    }

    /**
     * @inheritdoc
     */
    public function listerLogs(int $limite = 50, int $offset = 0, array $filtres = []): array
    {
        $sql = "SELECT e.*, u.login_utilisateur 
                FROM enregistrer e
                JOIN utilisateur u ON e.numero_utilisateur = u.numero_utilisateur";

        $conditions = [];
        $params = [];
        if (!empty($filtres['numero_utilisateur'])) {
            $conditions[] = "e.numero_utilisateur = :user_id";
            $params[':user_id'] = $filtres['numero_utilisateur'];
        }
        if (!empty($filtres['id_action'])) {
            $conditions[] = "e.id_action = :action_id";
            $params[':action_id'] = $filtres['id_action'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY e.date_action DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @inheritdoc
     */
    public function getHistoriquePourEntite(string $idEntite, string $typeEntite): array
    {
        return $this->enregistrerModel->trouverParCritere(
            ['id_entite_concernee' => $idEntite, 'type_entite_concernee' => $typeEntite],
            ['*'],
            'AND',
            'date_action DESC'
        );
    }
}