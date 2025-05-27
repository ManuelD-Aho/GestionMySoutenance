<?php

namespace Backend\Model;

use PDO;

class StatutJury extends BaseModel
{
    protected string $table = 'statut_jury';
    protected string $clePrimaire = 'id_statut_jury';
}