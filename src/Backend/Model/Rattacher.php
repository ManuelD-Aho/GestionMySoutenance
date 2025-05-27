<?php

namespace Backend\Model;

use PDO;

class Rattacher extends BaseModel
{
    protected string $table = 'rattacher';

    public function trouverParCleComposite(int $idGroupeUtilisateur, int $idTraitement, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_groupe_utilisateur = :id_groupe_utilisateur AND id_traitement = :id_traitement";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_groupe_utilisateur', $idGroupeUtilisateur, PDO::PARAM_INT);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_INT);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function supprimerParCleComposite(int $idGroupeUtilisateur, int $idTraitement): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_groupe_utilisateur = :id_groupe_utilisateur AND id_traitement = :id_traitement";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_groupe_utilisateur', $idGroupeUtilisateur, PDO::PARAM_INT);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_INT);
        return $declaration->execute();
    }

    public function trouverTraitementsParIdGroupe(int $idGroupeUtilisateur, array $colonnes = ['id_traitement']): array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_groupe_utilisateur = :id_groupe_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_groupe_utilisateur', $idGroupeUtilisateur, PDO::PARAM_INT);
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}