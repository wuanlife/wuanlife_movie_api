<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UsersAuth extends Model
{
    public $timestamps = false;
    protected $table = 'users_auth';
    protected $fillable = [
        'id',
        'auth',
    ];

    public function detail()
    {
        return $this->belongsTo('App\Models\Users\UsersAuthDetail', 'auth', 'id');
    }
}
