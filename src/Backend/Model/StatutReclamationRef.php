<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutReclamationRef extends BaseModel
{
    protected string $table = 'statut_reclamation_ref';
    protected string $clePrimaire = 'id_statut_reclamation';
}