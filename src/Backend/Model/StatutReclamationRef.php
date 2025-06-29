<?php
namespace App\Backend\Model;

use PDO;

class StatutReclamationRef extends BaseModel
{
    protected string $table = 'statut_reclamation_ref';
    protected string|array $primaryKey = 'id_statut_reclamation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}