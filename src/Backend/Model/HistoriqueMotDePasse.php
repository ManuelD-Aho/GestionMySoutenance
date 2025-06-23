<?php
namespace App\Backend\Model;

use PDO;

class HistoriqueMotDePasse extends BaseModel
{
    protected string $table = 'historique_mot_de_passe';
    protected string|array $primaryKey = 'id_historique_mdp';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public static function checkPasswordAgainstHistory(string $password, array $history): bool
    {
        foreach ($history as $entry) {
            if (password_verify($password, $entry['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }
}