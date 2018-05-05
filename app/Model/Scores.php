<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:07
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Scores extends Model
{
    protected $primaryKey = 'user_id';
    protected $table = 'scores';
    public $timestamps = false;
}