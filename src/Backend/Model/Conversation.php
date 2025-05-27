<?php

namespace App\Backend\Model;
use App\Backend\Model\BaseModel;
use PDO;

class Conversation extends BaseModel
{
    protected string $table = 'conversation';
    protected string $clePrimaire = 'id_conversation';
}