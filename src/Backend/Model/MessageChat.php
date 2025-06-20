<?php
namespace App\Backend\Model;

use PDO;

class MessageChat extends BaseModel
{
    protected string $table = 'message_chat';
    protected string|array $primaryKey = 'id_message_chat'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}