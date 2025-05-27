<?php

namespace Backend\Model;

use PDO;

class DocumentSoumis extends BaseModel
{
    protected string $table = 'document_soumis';
    protected string $clePrimaire = 'id_document';
}