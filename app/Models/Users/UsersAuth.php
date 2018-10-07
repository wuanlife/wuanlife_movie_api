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

    public static function checkAuth($user_id)
    {
        $auth = UsersAuth::withCount(['detail' => function($query) {
            $query->whereIn('identity', [
                '管理员',
                '最高管理员',
            ]);
        }])->where('users_auth.id', $user_id)->first();
        if (!$auth->detail) {
            return false;
        }
        return true;
    }
}
