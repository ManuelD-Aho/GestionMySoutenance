<?php
namespace App\Backend\Model;

use PDO;

class Conversation extends BaseModel
{
    public string $table = 'conversation';
    public string|array $primaryKey = 'id_conversation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}