<?php
namespace App\Backend\Model;

use PDO;

class RapportModeleSection extends BaseModel
{
    protected string $table = 'rapport_modele_section';
    protected string|array $primaryKey = 'id_section_modele';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}