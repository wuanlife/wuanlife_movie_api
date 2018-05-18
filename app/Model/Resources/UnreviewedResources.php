<?php

namespace App\Model\Resources;

use Illuminate\Database\Eloquent\Model;

class UnreviewedResources extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resources_id';
    protected $table = 'unreviewed_resources';
    protected $fillable = [
        'resources_id',
    ];

    public function resource()
    {
        return $this->hasOne('App\Model\Resources\Resource', 'resource_id', 'resources_id');
    }
}
