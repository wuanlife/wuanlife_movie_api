<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Resource extends Model
{
    use Notifiable;
    protected $table = 'resources';
    /**
     * 指定是否模型应该被戳记时间。
     */
    public $timestamps = true;
    /**
     * 模型的日期字段保存格式。
     */
    protected $dateFormat = 'Y-m-d H:i:s';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'movies_id','resource_id','resource_type', 'title', 'instruction','sharer','url','password',
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
