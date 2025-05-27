<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Recevoir extends BaseModel
{
    protected string $table = 'recevoir';

    public function trouverReceptionParCles(string $numeroUtilisateur, int $idNotification, string $dateReception, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_notification = :id_notification AND date_reception = :date_reception";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_notification', $idNotification, PDO::PARAM_INT);
        $declaration->bindParam(':date_reception', $dateReception, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourReceptionParCles(string $numeroUtilisateur, int $idNotification, string $dateReception, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_utilisateur = :numero_utilisateur_condition AND id_notification = :id_notification_condition AND date_reception = :date_reception_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_utilisateur_condition'] = $numeroUtilisateur;
        $parametres['id_notification_condition'] = $idNotification;
        $parametres['date_reception_condition'] = $dateReception;

        return $declaration->execute($parametres);
    }

    public function supprimerReceptionParCles(string $numeroUtilisateur, int $idNotification, string $dateReception): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_notification = :id_notification AND date_reception = :date_reception";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_notification', $idNotification, PDO::PARAM_INT);
        $declaration->bindParam(':date_reception', $dateReception, PDO::PARAM_STR);
        return $declaration->execute();
    }
}