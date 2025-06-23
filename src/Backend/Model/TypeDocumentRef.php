<?php
namespace App\Backend\Model;

use PDO;

class TypeDocumentRef extends BaseModel
{
    protected string $table = 'type_document_ref';
    protected string|array $primaryKey = 'id_type_document';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}