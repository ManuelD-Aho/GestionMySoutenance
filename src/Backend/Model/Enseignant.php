<?php

namespace App\Backend\Model;

class Enseignant extends BaseModel
{
    protected string $table = 'enseignant';
    protected string $clePrimaire = 'numero_enseignant';

    public function trouverParNumeroEnseignant(string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroEnseignant, $colonnes);
    }
}