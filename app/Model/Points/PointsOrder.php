<?php

namespace App\Model\Points;

use Illuminate\Database\Eloquent\Model;

class PointsOrder extends Model
{
    public $timestamps = false;
    protected $table = 'points_order';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'points_alert',
    ];
}
