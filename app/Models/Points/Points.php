<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:07
 */

namespace App\Models\Points;


use Illuminate\Database\Eloquent\Model;

class Points extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    protected $table = 'points';
    protected $fillable = [
        'user_id',
        'points',
    ];
}