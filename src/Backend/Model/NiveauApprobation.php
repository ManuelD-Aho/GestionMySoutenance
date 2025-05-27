<?php

namespace Backend\Model;

use PDO;

class NiveauApprobation extends BaseModel
{
    protected string $table = 'niveau_approbation';
    protected string $clePrimaire = 'id_niveau_approbation';
}