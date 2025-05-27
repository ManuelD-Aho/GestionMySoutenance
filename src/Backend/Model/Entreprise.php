<?php

namespace Backend\Model;

use PDO;

class Entreprise extends BaseModel
{
    protected string $table = 'entreprise';
    protected string $clePrimaire = 'id_entreprise';
}