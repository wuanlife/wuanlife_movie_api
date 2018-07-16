<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UsersAuthDetail extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'auth_detail';
    protected $fillable = [
        'id',
        'identity',
    ];
}
