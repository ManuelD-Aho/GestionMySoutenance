<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Conversation extends BaseModel
{
    protected string $table = 'conversation';
    protected string $clePrimaire = 'id_conversation';
}