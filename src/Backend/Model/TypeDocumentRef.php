<?php

namespace Backend\Model;

use PDO;

class TypeDocumentRef extends BaseModel
{
    protected string $table = 'type_document_ref';
    protected string $clePrimaire = 'id_type_document';
}