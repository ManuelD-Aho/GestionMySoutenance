<?php
namespace App\Backend\Model;

use PDO;

class Notification extends BaseModel
{
    protected string $table = 'notification';
    protected string|array $primaryKey = 'id_notification';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // La colonne 'contenu' est maintenant supposée exister dans la DDL et est gérée par BaseModel.
}