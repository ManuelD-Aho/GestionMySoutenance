<?php
namespace App\Backend\Model;

use PDO;

class RapportModeleSection extends BaseModel
{
    public string $table = 'rapport_modele_section';
    public string|array $primaryKey = 'id_section_modele';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}