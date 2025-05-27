<?php

namespace App\Backend\Model;
use PDO;

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string $clePrimaire = 'numero_utilisateur';

    public function trouverParNumeroUtilisateur(string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroUtilisateur, $colonnes);
    }
}