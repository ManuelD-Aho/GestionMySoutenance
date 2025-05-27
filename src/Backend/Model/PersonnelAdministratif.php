<?php

namespace Backend\Model;

use PDO;

class PersonnelAdministratif extends BaseModel
{
    protected string $table = 'personnel_administratif';
    protected string $clePrimaire = 'numero_personnel_administratif';
}