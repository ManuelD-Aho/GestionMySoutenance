<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\ParametreSysteme;
use App\Backend\Service\Interface\ParametrageServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceParametrage implements ParametrageServiceInterface
{
    private PDO $pdo;
    private ParametreSysteme $parametreModel;
    private AuditServiceInterface $auditService;
    private array $parametresParDefaut;
    private string $currentUserLogin;

    public function __construct(PDO $pdo, ParametreSysteme $parametreModel, AuditServiceInterface $auditService)
    {
        $this->pdo = $pdo;
        $this->parametreModel = $parametreModel;
        $this->auditService = $auditService;

        // Récupération dynamique de l'utilisateur. Conforme aux bonnes pratiques.
        $this->currentUserLogin = $_SESSION['user_id'] ?? 'SYSTEM_SCRIPT';

        // Correction DDL: Suppression de la colonne 'categorie' qui n'existe plus.
        // Alignement avec la table `parametres_systeme` finale.
        $this->parametresParDefaut = [
            ['cle' => 'LOCKOUT_TIME_MINUTES', 'valeur' => '30', 'type' => 'integer'],
            ['cle' => 'MAX_LOGIN_ATTEMPTS', 'valeur' => '5', 'type' => 'integer'],
            ['cle' => 'PASSWORD_MIN_LENGTH', 'valeur' => '8', 'type' => 'integer'],
            ['cle' => 'SMTP_HOST', 'valeur' => 'smtp.example.com', 'type' => 'string'],
        ];
    }

    public function getParametre(string $cle): ?string
    {
        $parametre = $this->parametreModel->trouverParIdentifiant($cle);
        return $parametre['valeur'] ?? null;
    }

    public function setParametre(string $cle, string $valeur): bool
    {
        $parametreExistant = $this->parametreModel->trouverParIdentifiant($cle);

        $this->pdo->beginTransaction();
        try {
            if ($parametreExistant) {
                $resultat = $this->parametreModel->mettreAJourParIdentifiant($cle, ['valeur' => $valeur]);
            } else {
                // Correction DDL: Le type 'string' par défaut est une approche robuste.
                $resultat = (bool)$this->parametreModel->creer(['cle' => $cle, 'valeur' => $valeur, 'type' => 'string']);
            }

            $this->auditService->enregistrerAction($this->currentUserLogin, 'PARAMETER_SET', $cle, 'ParametreSysteme', ['nouvelle_valeur' => $valeur]);
            $this->pdo->commit();

            return $resultat;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getAllParametres(): array
    {
        return $this->parametreModel->trouverTout();
    }

    public function getParametresParCategorie(string $categorie): array
    {
        // Correction DDL: La méthode est obsolète car la colonne `categorie` n'existe plus.
        // Retourner un tableau vide est le comportement le plus sûr.
        return [];
    }

    public function reinitialiserParametresParDefaut(): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Utilisation de TRUNCATE pour une réinitialisation propre et rapide.
            $this->pdo->exec("TRUNCATE TABLE parametres_systeme");
            foreach ($this->parametresParDefaut as $param) {
                $this->parametreModel->creer($param);
            }
            $this->auditService->enregistrerAction($this->currentUserLogin, 'PARAMETERS_RESET_TO_DEFAULT', null, 'ParametreSysteme');
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}