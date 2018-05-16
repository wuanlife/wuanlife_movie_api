<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:07
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $primaryKey = 'user_id';
    protected $table = 'point';
    public $timestamps = false;
}