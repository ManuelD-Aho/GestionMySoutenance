<?php
namespace App\Backend\Model;

use PDO;

class MatriceNotificationRegles extends BaseModel
{
    public string $table = 'matrice_notification_regles';
    public string|array $primaryKey = 'id_regle';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}