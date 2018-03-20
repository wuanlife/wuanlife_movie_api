<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Resource extends Model
{
    use Notifiable;
    protected $table = 'resource';
    /**
     * 指定是否模型应该被戳记时间。
     */
    public $timestamps = false;
    /**
     * 模型的日期字段保存格式。
     */
    protected $dateFormat = 'U';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'movies_id','resource_id','resource_type', 'title', 'instruction','sharer','url','password','create_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
//        'password', 'remember_token',
    ];
}
