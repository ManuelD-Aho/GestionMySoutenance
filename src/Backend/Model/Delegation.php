<?php
// src/Backend/Model/Delegation.php

namespace App\Backend\Model;

use PDO;

class Delegation extends BaseModel
{
    protected string $table = 'delegation';
    protected string|array $primaryKey = 'id_delegation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve toutes les délégations actives pour un utilisateur donné.
     * Une délégation est active si son statut est 'Active' et si la date actuelle
     * est comprise entre la date de début et la date de fin.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur délégué.
     * @return array La liste des délégations actives.
     */
    public function trouverDelegationActivePourUtilisateur(string $numeroUtilisateur): array
    {
        return $this->trouverParCritere([
            'id_delegue' => $numeroUtilisateur,
            'statut' => 'Active',
            'date_debut' => ['operator' => '<=', 'value' => date('Y-m-d H:i:s')],
            'date_fin' => ['operator' => '>=', 'value' => date('Y-m-d H:i:s')]
        ]);
    }
}