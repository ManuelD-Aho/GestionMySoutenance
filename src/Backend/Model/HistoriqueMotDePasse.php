<?php
namespace App\Backend\Model;

use PDO;

class HistoriqueMotDePasse extends BaseModel
{
    protected string $table = 'historique_mot_de_passe';
    protected string|array $primaryKey = 'id_historique_mdp'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Récupère l'historique des mots de passe pour un utilisateur donné.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param int $limite Le nombre maximum d'entrées à récupérer.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array La liste des entrées d'historique.
     */
    public function recupererHistoriquePourUtilisateur(string $numeroUtilisateur, int $limite = 5, array $colonnes = ['mot_de_passe_hache', 'date_changement', 'id_historique_mdp']): array
    {
        return $this->trouverParCritere(
            ['numero_utilisateur' => $numeroUtilisateur],
            $colonnes,
            'AND',
            'date_changement DESC',
            $limite
        );
    }

    /**
     * Supprime plusieurs entrées de l'historique par leurs identifiants.
     * @param array $idsHistorique Un tableau d'IDs d'historique à supprimer (string).
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerPlusieursParIdentifiants(array $idsHistorique): bool
    {
        if (empty($idsHistorique)) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($idsHistorique), '?'));
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` IN ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($idsHistorique);
    }

    /**
     * Compte le nombre d'entrées d'historique pour un utilisateur donné.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return int Le nombre d'entrées d'historique.
     */
    public function compterPourUtilisateur(string $numeroUtilisateur): int
    {
        return $this->compterParCritere(['numero_utilisateur' => $numeroUtilisateur]);
    }
}