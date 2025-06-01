<?php

namespace App\Backend\Model;

class PersonnelAdministratif extends BaseModel
{
    protected string $table = 'personnel_administratif';
    protected string $clePrimaire = 'numero_personnel_administratif';

    public function trouverParNumeroPersonnelAdministratif(string $numeroPersonnelAdministratif, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroPersonnelAdministratif, $colonnes);
    }
}