<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\MatriceNotificationRegles;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\Interface\NotificationConfigurationServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceNotificationConfiguration implements NotificationConfigurationServiceInterface
{
    private PDO $pdo;
    private MatriceNotificationRegles $matriceModel;
    private Utilisateur $utilisateurModel;
    private AuditServiceInterface $auditService;

    public function __construct(
        PDO $pdo,
        MatriceNotificationRegles $matriceModel,
        Utilisateur $utilisateurModel,
        AuditServiceInterface $auditService
    ) {
        $this->pdo = $pdo;
        $this->matriceModel = $matriceModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->auditService = $auditService;
    }

    public function creerRegle(array $donnees): string
    {
        $this->pdo->beginTransaction();
        try {
            $this->matriceModel->creer($donnees);
            $idRegle = $this->pdo->lastInsertId();
            $this->auditService->enregistrerAction($_SESSION['user_id'], 'NOTIFICATION_RULE_CREATED', $idRegle, 'MatriceNotificationRegles', $donnees);
            $this->pdo->commit();
            return $idRegle;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function mettreAJourRegle(string $idRegle, array $donnees): bool
    {
        $regle = $this->recupererRegleOuEchouer($idRegle);
        $resultat = $this->matriceModel->mettreAJourParIdentifiant($idRegle, $donnees);
        $this->auditService->enregistrerAction($_SESSION['user_id'], 'NOTIFICATION_RULE_UPDATED', $idRegle, 'MatriceNotificationRegles', ['anciennes_valeurs' => $regle, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function listerRegles(): array
    {
        return $this->matriceModel->trouverTout();
    }

    public function mettreAJourPreferencesUtilisateur(string $idUtilisateur, array $preferences): bool
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($idUtilisateur)) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }

        $donnees = [
            'preferences_notification_json' => json_encode($preferences)
        ];

        $resultat = $this->utilisateurModel->mettreAJourParIdentifiant($idUtilisateur, $donnees);
        $this->auditService->enregistrerAction($idUtilisateur, 'USER_NOTIFICATION_PREFS_UPDATED', $idUtilisateur, 'Utilisateur', $preferences);
        return $resultat;
    }

    private function recupererRegleOuEchouer(string $idRegle): array
    {
        $regle = $this->matriceModel->trouverParIdentifiant($idRegle);
        if (!$regle) {
            throw new ElementNonTrouveException("La règle de notification avec l'ID '{$idRegle}' n'a pas été trouvée.");
        }
        return $regle;
    }
}