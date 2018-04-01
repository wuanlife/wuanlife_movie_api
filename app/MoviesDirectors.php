<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoviesDirectors extends Model
{
    public $timestamps = false;
    protected $table = 'movies_directors';

    public function director(){
        return $this->hasOne('App\Directors','id','director_id');
    }
}
