<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class MessageChat extends BaseModel
{
    protected string $table = 'message_chat';
    protected string $clePrimaire = 'id_message_chat';
}