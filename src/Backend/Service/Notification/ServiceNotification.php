<?php

namespace App\Backend\Service\Notification;

use App\Backend\Model\Notification as ModeleTypeNotification;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Utilisateur;
use PDO;

class ServiceNotification
{
    private ModeleTypeNotification $modeleTypeNotification;
    private Recevoir $modeleRecevoir;
    private Utilisateur $modeleUtilisateur;
    private PDO $db;

    public function __construct(
        ModeleTypeNotification $modeleTypeNotification,
        Recevoir $modeleRecevoir,
        Utilisateur $modeleUtilisateur,
        PDO $db
    ) {
        $this->modeleTypeNotification = $modeleTypeNotification;
        $this->modeleRecevoir = $modeleRecevoir;
        $this->modeleUtilisateur = $modeleUtilisateur;
        $this->db = $db;
    }

    public function envoyerNotificationUtilisateur(string $numeroUtilisateurDestinataire, int $idTypeNotification, string $messageDetail): bool
    {
        $donneesNotification = [
            'numero_utilisateur' => $numeroUtilisateurDestinataire,
            'id_notification' => $idTypeNotification,
            'message_complementaire' => $messageDetail,
            'date_reception' => date('Y-m-d H:i:s'),
            'lue' => 0
        ];
        return (bool)$this->modeleRecevoir->creer($donneesNotification);
    }

    public function envoyerNotificationGroupe(int $idGroupeUtilisateurDestinataire, int $idTypeNotification, string $messageDetail): bool
    {
        $utilisateursDuGroupe = $this->modeleUtilisateur->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateurDestinataire], ['numero_utilisateur']);
        if (empty($utilisateursDuGroupe)) {
            return false;
        }
        $succesGlobal = true;
        $this->db->beginTransaction();
        try {
            foreach ($utilisateursDuGroupe as $utilisateur) {
                $succes = $this->envoyerNotificationUtilisateur($utilisateur['numero_utilisateur'], $idTypeNotification, $messageDetail);
                if (!$succes) {
                    $succesGlobal = false;
                    break;
                }
            }
            if ($succesGlobal) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            $succesGlobal = false;
        }
        return $succesGlobal;
    }

    public function recupererNotificationsUtilisateur(string $numeroUtilisateur, bool $nonLuesSeulement = false, int $limite = 10): array
    {
        $criteres = ['r.numero_utilisateur' => $numeroUtilisateur];
        if ($nonLuesSeulement) {
            $criteres['r.lue'] = 0;
        }

        $sql = "SELECT r.id_recevoir, r.date_reception, r.lue, r.date_lecture, r.message_complementaire, n.lib_notification 
                FROM recevoir r
                JOIN notification n ON r.id_notification = n.id_notification
                WHERE r.numero_utilisateur = :numero_utilisateur ";
        if ($nonLuesSeulement) {
            $sql .= "AND r.lue = 0 ";
        }
        $sql .= "ORDER BY r.date_reception DESC LIMIT :limite";

        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':limite', $limite, PDO::PARAM_INT);
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function marquerNotificationCommeLue(int $idRecevoirNotification, string $numeroUtilisateurVerif): bool
    {
        $notification = $this->modeleRecevoir->trouverParIdentifiant($idRecevoirNotification);
        if ($notification && $notification['numero_utilisateur'] === $numeroUtilisateurVerif) {
            return $this->modeleRecevoir->mettreAJourParIdentifiant($idRecevoirNotification, [
                'lue' => 1,
                'date_lecture' => date('Y-m-d H:i:s')
            ]);
        }
        return false;
    }

    public function compterNotificationsNonLues(string $numeroUtilisateur): int
    {
        return $this->modeleRecevoir->compterParCritere([
            'numero_utilisateur' => $numeroUtilisateur,
            'lue' => 0
        ]);
    }

    public function archiverAnciennesNotificationsLues(int $joursAvantArchivage = 30): int
    {
        $dateLimite = date('Y-m-d H:i:s', strtotime("-{$joursAvantArchivage} days"));
        $sql = "DELETE FROM recevoir WHERE lue = 1 AND date_lecture < :date_limite";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':date_limite', $dateLimite);
        $declaration->execute();
        return $declaration->rowCount();
    }
}