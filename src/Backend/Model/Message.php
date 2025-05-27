<?php

namespace Backend\Model;

use PDO;

class Message extends BaseModel
{
    protected string $table = 'message';
    protected string $clePrimaire = 'id_message';
}