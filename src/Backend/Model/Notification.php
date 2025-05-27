<?php

namespace Backend\Model;

use PDO;

class Notification extends BaseModel
{
    protected string $table = 'notification';
    protected string $clePrimaire = 'id_notification';
}