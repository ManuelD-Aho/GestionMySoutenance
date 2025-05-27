<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutPaiementRef extends BaseModel
{
    protected string $table = 'statut_paiement_ref';
    protected string $clePrimaire = 'id_statut_paiement';
}