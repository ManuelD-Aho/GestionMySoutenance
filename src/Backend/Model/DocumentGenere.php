<?php
namespace App\Backend\Model;

use PDO;

class DocumentGenere extends BaseModel
{
    protected string $table = 'document_genere';
    protected string|array $primaryKey = 'id_document';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getTypeDocument(): ?array
    {
        if (!isset($this->id_type_document)) return null;
        $typeDocModel = new TypeDocumentRef($this->db);
        return $typeDocModel->trouverParIdentifiant($this->id_type_document);
    }

    public function getEntiteConcernee(): ?array
    {
        if (!isset($this->id_entite_concernee) || !isset($this->type_entite_concernee)) return null;

        $modelClass = 'App\\Backend\\Model\\' . $this->type_entite_concernee;
        if (!class_exists($modelClass)) return null;

        $model = new $modelClass($this->db);
        return $model->trouverParIdentifiant($this->id_entite_concernee);
    }
}