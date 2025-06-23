<?php
namespace App\Backend\Model;

use PDO;

class StatutPaiementRef extends BaseModel
{
    protected string $table = 'statut_paiement_ref';
    protected string|array $primaryKey = 'id_statut_paiement';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}