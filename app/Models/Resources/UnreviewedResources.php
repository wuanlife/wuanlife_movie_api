<?php

namespace App\Models\Resources;

use Illuminate\Database\Eloquent\Model;

class UnreviewedResources extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resource_id';
    protected $table = 'unreviewed_resources';
    protected $fillable = [
        'resource_id',
    ];

    public function resource()
    {
        return $this->hasOne('App\Models\Resources\Resource', 'resource_id', 'resource_id');
    }
}
