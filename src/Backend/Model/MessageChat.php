<?php

namespace Backend\Model;

use PDO;

class MessageChat extends BaseModel
{
    protected string $table = 'message_chat';
    protected string $clePrimaire = 'id_message_chat';
}