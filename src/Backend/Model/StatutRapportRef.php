<?php

namespace Backend\Model;

use PDO;

class StatutRapportRef extends BaseModel
{
    protected string $table = 'statut_rapport_ref';
    protected string $clePrimaire = 'id_statut_rapport';
}