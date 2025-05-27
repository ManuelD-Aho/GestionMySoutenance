<?php

namespace Backend\Model;

use PDO;

class StatutConformiteRef extends BaseModel
{
    protected string $table = 'statut_conformite_ref';
    protected string $clePrimaire = 'id_statut_conformite';
}