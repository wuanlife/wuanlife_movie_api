<?php

namespace App\Models\Human;

use Illuminate\Database\Eloquent\Model;

class Actors extends Model
{
    protected $table = 'actors';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'id'
    ];
}
