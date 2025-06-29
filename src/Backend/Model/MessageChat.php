<?php
namespace App\Backend\Model;

use PDO;

class MessageChat extends BaseModel
{
    public string $table = 'message_chat';
    public string|array $primaryKey = 'id_message_chat';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}