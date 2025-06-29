<?php
namespace App\Backend\Model;

use PDO;

class TypeDocumentRef extends BaseModel
{
    public string $table = 'type_document_ref';
    public string|array $primaryKey = 'id_type_document';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}