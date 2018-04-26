<?php

namespace App\Model\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesDirectors extends Model
{
    public $timestamps = false;
    protected $table = 'movies_directors';

    public function director(){
        return $this->hasOne('App\Model\Human\Directors','id','director_id');
    }
}
