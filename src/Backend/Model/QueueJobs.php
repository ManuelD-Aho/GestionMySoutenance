<?php
namespace App\Backend\Model;

use PDO;

class QueueJobs extends BaseModel
{
    public string $table = 'queue_jobs';
    public string|array $primaryKey = 'id';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}