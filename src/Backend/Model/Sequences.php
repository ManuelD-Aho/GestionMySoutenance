<?php
namespace App\Backend\Model;

use PDO;

class Sequences extends BaseModel
{
    protected string $table = 'sequences';
    protected string|array $primaryKey = ['nom_sequence', 'annee'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}