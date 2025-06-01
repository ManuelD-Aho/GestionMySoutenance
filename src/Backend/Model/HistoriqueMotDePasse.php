<?php

namespace App\Backend\Model;

use PDO;
use PDOException;

class HistoriqueMotDePasse extends BaseModel
{
    protected string $table = 'historique_mot_de_passe';
    protected string $clePrimaire = 'id_historique_mdp';

    public ?string $id_historique_mdp = null;
    public string $numero_utilisateur;
    public string $mot_de_passe_hache;
    public ?string $date_changement = null;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function recupererHistoriquePourUtilisateur(string $numeroUtilisateur, int $limite = 5, array $colonnes = ['mot_de_passe_hache', 'date_changement', 'id_historique_mdp']): array
    {
        if ($limite <= 0) return [];
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` 
                WHERE `numero_utilisateur` = :numero_utilisateur 
                ORDER BY `date_changement` DESC 
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de l'historique des mots de passe pour l'utilisateur {$numeroUtilisateur}: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supprimerPlusieursParIdentifiants(array $idsHistorique): bool
    {
        if (empty($idsHistorique)) return false;
        $placeholders = implode(',', array_fill(0, count($idsHistorique), '?'));
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->clePrimaire}` IN ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($idsHistorique);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de plusieurs entrées d'historique: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function compterPourUtilisateur(string $numeroUtilisateur): int
    {
        return $this->compterParCritere(['numero_utilisateur' => $numeroUtilisateur]);
    }
}