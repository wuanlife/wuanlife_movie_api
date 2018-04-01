<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoviesActors extends Model
{
    public $timestamps = false;
    protected $table = 'movies_actors';

    public function actor(){
        return $this->hasOne('App\Actors','id','actor_id');
    }
}
