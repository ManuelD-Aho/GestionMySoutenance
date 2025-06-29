<?php
namespace App\Backend\Model;

use PDO;

class DocumentGenere extends BaseModel
{
    public string $table = 'document_genere';
    public string|array $primaryKey = 'id_document_genere';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}