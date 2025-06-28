<?php
namespace App\Backend\Model;

use PDO;

class QueueJobs extends BaseModel
{
    protected string $table = 'queue_jobs';
    protected string|array $primaryKey = 'id';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}