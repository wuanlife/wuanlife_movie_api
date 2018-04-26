<?php

namespace App\Model\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesActors extends Model
{
    public $timestamps = false;
    protected $table = 'movies_actors';

    public function actor(){
        return $this->hasOne('App\Model\Human\Actors','id','actor_id');
    }
}
