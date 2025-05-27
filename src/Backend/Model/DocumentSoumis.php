<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class DocumentSoumis extends BaseModel
{
    protected string $table = 'document_soumis';
    protected string $clePrimaire = 'id_document';
}