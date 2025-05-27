<?php

namespace Backend\Model;

use PDO;

class StatutPvRef extends BaseModel
{
    protected string $table = 'statut_pv_ref';
    protected string $clePrimaire = 'id_statut_pv';
}