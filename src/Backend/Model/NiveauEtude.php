<?php

namespace Backend\Model;

use PDO;

class NiveauEtude extends BaseModel
{
    protected string $table = 'niveau_etude';
    protected string $clePrimaire = 'id_niveau_etude';
}