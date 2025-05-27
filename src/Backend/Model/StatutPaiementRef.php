<?php

namespace Backend\Model;

use PDO;

class StatutPaiementRef extends BaseModel
{
    protected string $table = 'statut_paiement_ref';
    protected string $clePrimaire = 'id_statut_paiement';
}