<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Notification extends BaseModel
{
    protected string $table = 'notification';
    protected string $clePrimaire = 'id_notification';
}