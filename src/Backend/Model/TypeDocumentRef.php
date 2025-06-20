<?php
namespace App\Backend\Model;

use PDO;

class TypeDocumentRef extends BaseModel
{
    protected string $table = 'type_document_ref';
    protected string|array $primaryKey = 'id_type_document'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}