<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Message extends BaseModel
{
    protected string $table = 'message';
    protected string $clePrimaire = 'id_message';
}