<?php
namespace App\Backend\Model;

use PDO;

class LectureMessage extends BaseModel
{
    protected string $table = 'lecture_message';
    protected string|array $primaryKey = ['id_message_chat', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}