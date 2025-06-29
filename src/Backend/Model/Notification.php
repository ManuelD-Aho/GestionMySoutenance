<?php
namespace App\Backend\Model;

use PDO;

class Notification extends BaseModel
{
    public string $table = 'notification';
    public string|array $primaryKey = 'id_notification';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}