<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Message extends BaseModel {

    protected string $table = 'message';
    protected string $primaryKey = 'id_message';

    // Constructor and basic CRUD methods are inherited from BaseModel.
}
