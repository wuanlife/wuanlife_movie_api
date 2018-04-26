<?php

namespace App\Model\Resources;

use Illuminate\Database\Eloquent\Model;

class ResourceTypeDetails extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'type_id';
    protected $table = 'resources_type_details';
}
