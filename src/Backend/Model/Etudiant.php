<?php

namespace App\Backend\Model;

class Etudiant extends BaseModel
{
    protected string $table = 'etudiant';
    protected string $clePrimaire = 'numero_carte_etudiant';

    public function trouverParNumeroCarteEtudiant(string $numeroCarteEtudiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroCarteEtudiant, $colonnes);
    }
}