<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class PersonnelAdministratif extends BaseModel
{
    protected string $table = 'personnel_administratif';
    protected string $clePrimaire = 'numero_personnel_administratif';

    public function trouverParNumeroPersonnelAdministratif(string $numeroPersonnelAdministratif, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroPersonnelAdministratif, $colonnes);
    }
}