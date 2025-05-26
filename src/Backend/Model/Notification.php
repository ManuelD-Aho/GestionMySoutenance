<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Notification extends BaseModel {

    protected string $table = 'notification';
    protected string $primaryKey = 'id_notification';

    // Constructor and basic CRUD methods are inherited from BaseModel.
}
