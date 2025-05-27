<?php

namespace Backend\Model;

use PDO;

class NiveauAccesDonne extends BaseModel
{
    protected string $table = 'niveau_acces_donne';
    protected string $clePrimaire = 'id_niveau_acces_donne';
}