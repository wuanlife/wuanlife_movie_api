<?php

namespace App\Model\Resources;

use Illuminate\Database\Eloquent\Model;

class UnreviewedResources extends Model
{
    public $timestamps = false;
    protected $table = 'unreviewed_resources';

    public function resource()
    {
        return $this->hasOne('App\Model\Resources\Resource','resource_id','resources_id');
    }
}
